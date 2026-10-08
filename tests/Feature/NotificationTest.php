<?php

namespace Tests\Feature;

use App\Mail\Confirmation;
use App\Mail\Offer;
use App\Mail\Reply;
use App\Models\Collection;
use App\Models\MailQueue;
use App\Tasks\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The mail queue: filled by the API, sent one mail per minute by the scheduler
 */
class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private Collection $offer;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->states();
        $estate = $this->estate();
        $house = $this->building($estate);
        $floor = $this->floor($estate);
        $room = $this->room($estate);
        $a = $this->apartment($house, $floor, $room, ['number' => '1.01']);
        $b = $this->apartment($house, $floor, $room, ['number' => '1.02']);

        // As the admin does it
        $this->actingAs($this->user(), 'sanctum');
        $id = $this->postJson('/api/collection', [
            'candidates' => [['salutation' => 'Frau', 'firstname' => 'Anna', 'name' => 'Beispiel', 'email' => 'anna@example.invalid']],
            'items' => [$a->uuid, $b->uuid],
            'remarks' => "Besichtigung am Montag\nab 17 Uhr",
        ])->json('collectionId');
        $this->offer = Collection::findOrFail($id);
    }

    private function sendNext(): void
    {
        (new Notification)();
    }

    public function testTheTaskRunsEveryMinute()
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('* * * * *  Closure at: App\\Tasks\\Notification::__invoke')
            ->assertSuccessful();
    }

    public function testOfferMail()
    {
        $this->travelTo('2026-10-08 10:00:00');
        $this->sendNext();

        Mail::assertSent(Offer::class, function (Offer $mail) {
            $mail->build();
            $html = $mail->render();
            $link = 'https://eglistrasse.aporta-stiftung.ch/angebot/' . $this->offer->uuid . '/' . md5('anna@example.invalid');

            $this->assertSame('Wohnungsangebot Eglistrasse – à Porta Stiftung', $mail->subject);
            $this->assertTrue($mail->hasFrom('noreply@example.invalid', 'à Porta Stiftung'));
            $this->assertStringContainsString('Guten Tag Frau Beispiel', $html);
            $this->assertStringContainsString('href="' . $link . '"', $html);
            $this->assertStringContainsString("Besichtigung am Montag<br>\nab 17 Uhr", $html);
            $this->assertSame(
                collect($this->offer->items)->map(fn ($i) => public_path('assets/media/' . $i->apartment->number . '-' . $i->apartment->uuid . '.pdf'))->all(),
                collect($mail->attachments)->pluck('file')->all()
            );

            return $mail->hasTo('anna@example.invalid');
        });

        $this->assertSame(1, (int) MailQueue::first()->processed);
        foreach ($this->offer->items()->get() as $item) {
            $this->assertSame('2026-10-08', $item->sent_at->format('Y-m-d'));
        }
    }

    public function testOfferMailWithFurnishedPlansAndEstateDocuments()
    {
        // A real KORO apartment: its media are in public/assets/media
        $data = json_decode(file_get_contents(base_path('database/data/kornhaus-roetelstrasse.json')), true);
        $uuid = collect($data['apartments'])->firstWhere('number', 'H1_101')['uuid'];
        config(['estates.current' => 'kornhaus-roetelstrasse']);
        $this->app->forgetScopedInstances(); // CurrentEstate still holds the setUp request's estate
        $koro = $this->estate(['domain' => 'kornhaus-roetelstrasse', 'description' => 'Kornhaus-/Rötelstrasse']);
        $apartment = $this->apartment($this->building($koro), $this->floor($koro), $this->room($koro), ['number' => 'H1_101', 'uuid' => $uuid]);
        $this->postJson('/api/collection', [
            'candidates' => [['salutation' => 'Herr', 'firstname' => 'Max', 'name' => 'Muster', 'email' => 'max@example.invalid']],
            'items' => [$apartment->uuid],
        ])->assertOk();
        MailQueue::where('data', 'not like', '%max@example.invalid%')->update(['processed' => 1]);

        $this->sendNext();

        Mail::assertSent(Offer::class, function (Offer $mail) use ($uuid) {
            $mail->build();
            $this->assertSame([
                public_path("assets/media/H1_101-{$uuid}.pdf"),
                public_path("assets/media/H1_101-{$uuid}-moebliert.pdf"),
                public_path('assets/media/estates/kornhaus-roetelstrasse/Ausbaubeschrieb.pdf'),
            ], collect($mail->attachments)->pluck('file')->all());

            return $mail->hasTo('max@example.invalid');
        });
    }

    public function testOneMailPerRun()
    {
        $this->postJson('/api/user-collection', ['uuid' => $this->offer->items->first()->uuid, 'accepted' => 1, 'parking' => 0, 'comment' => null]);
        $this->assertSame(3, MailQueue::unprocessed()->count());

        $this->sendNext();
        Mail::assertSentCount(1);
        Mail::assertSent(Offer::class);

        $this->sendNext();
        $this->sendNext();
        Mail::assertSentCount(3);
        $this->assertSame(0, MailQueue::unprocessed()->count());

        $this->sendNext();
        Mail::assertSentCount(3);
    }

    public function testReplyAndConfirmationMails()
    {
        $item = $this->offer->items->last();
        MailQueue::query()->update(['processed' => 1]);

        $this->postJson('/api/user-collection', ['uuid' => $item->uuid, 'accepted' => 0, 'parking' => 0, 'comment' => 'Zu teuer']);
        $this->sendNext();
        $this->sendNext();

        Mail::assertSent(Reply::class, function (Reply $mail) use ($item) {
            $mail->build();
            $html = $mail->render();

            $this->assertSame('Antwort Wohnungsangebot Eglistrasse – à Porta Stiftung', $mail->subject);
            $this->assertStringContainsString('Unser Angebot für das Objekt 1.02 (3.5-Zimmer, Eglistrasse 1, 1. OG links) wurde beantwortet.', $html);
            $this->assertStringContainsString('Anna Beispiel', $html);
            $this->assertStringContainsString('Zu teuer', $html);
            $this->assertStringContainsString('https://liegenschaften.aporta-stiftung.ch/administration/angebote/' . $item->uuid, $html);

            return $mail->hasTo('vermietung@example.invalid');
        });

        Mail::assertSent(Confirmation::class, function (Confirmation $mail) {
            $mail->build();
            $html = $mail->render();

            $this->assertSame('Wohnungsangebot Eglistrasse – à Porta Stiftung', $mail->subject);
            $this->assertStringContainsString('Wohnungsangebot 3.5-Zimmer, Eglistrasse 1', $html);
            $this->assertStringContainsString('Ich habe kein Interesse an diesem Angebot', $html);

            return $mail->hasTo('anna@example.invalid');
        });
    }

    public function testAFailedMailIsMarkedAndLogged()
    {
        Log::spy();
        MailQueue::query()->update(['processed' => 1]);
        $broken = MailQueue::create(['type' => 'confirmation', 'data' => 'null']);

        $this->sendNext();

        Mail::assertNothingSent();
        $broken->refresh();
        $this->assertSame(1, (int) $broken->processed);
        $this->assertStringContainsString('Attempt to read property "collection" on null', $broken->error);
        Log::shouldHaveReceived('error')->once();
    }
}
