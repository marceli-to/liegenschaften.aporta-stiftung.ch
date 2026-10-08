<?php

namespace Tests\Feature;

use App\Models\Collection;
use App\Models\MailQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * What the candidate sees: the offer page and the public user-collection API
 */
class OfferTest extends TestCase
{
    use RefreshDatabase;

    private Collection $offer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->states();
        $estate = $this->estate();
        $house = $this->building($estate);
        $floor = $this->floor($estate);
        $room = $this->room($estate);

        $this->offer = $this->collection($estate, [
            $this->apartment($house, $floor, $room, ['number' => '1.01', 'available_at' => '01.11.2026']),
            $this->apartment($house, $floor, $room, ['number' => '1.02', 'size_balcony' => null]),
            $this->apartment($house, $floor, $room, ['number' => '1.03']),
        ]);
    }

    private function item(int $index)
    {
        return $this->offer->items[$index];
    }

    public function testOfferPageOnBothDomains()
    {
        $this->get("/angebot/{$this->offer->uuid}")->assertOk()->assertSee('Wohnüberbauung Eglistrasse, 8004 Zürich');
        $this->get("https://eglistrasse.aporta-stiftung.ch/angebot/{$this->offer->uuid}")->assertOk();
        $this->get("https://eglistrasse.aporta-stiftung.ch/angebot/{$this->offer->uuid}/detail/{$this->item(0)->uuid}")->assertOk();
        $this->get('/angebot/nope')->assertNotFound();
    }

    public function testTheMailLinkMarksTheItemsAsRead()
    {
        $this->travelTo('2026-10-08 10:00:00');

        $this->get("/angebot/{$this->offer->uuid}/" . md5('someone@example.invalid'))->assertOk();
        $this->assertSame(0, $this->offer->items()->whereNotNull('read_at')->count());

        $this->get("/angebot/{$this->offer->uuid}/" . md5('anna@example.invalid'))->assertOk();
        $this->assertSame(3, $this->offer->items()->whereNotNull('read_at')->count());

        // The first visit counts
        $this->travelTo('2026-10-09 10:00:00');
        $this->get("/angebot/{$this->offer->uuid}/" . md5('anna@example.invalid'));
        $this->assertSame('2026-10-08', $this->item(0)->fresh()->read_at->format('Y-m-d'));
    }

    public function testList()
    {
        $response = $this->getJson("/api/user-collection/{$this->offer->uuid}")
            ->assertOk()
            ->assertJsonPath('uuid', $this->offer->uuid)
            ->assertJsonPath('valid', true)
            ->assertJsonPath('estate', [
                'description' => 'Wohnüberbauung Eglistrasse, 8004 Zürich',
                'maps' => 'https://maps.example.invalid',
                'exteriors' => ['terrace' => 'Terrasse', 'patio' => 'Sitzplatz', 'balcony' => 'Balkon'],
            ])
            ->assertJsonCount(3, 'items')
            ->assertJsonPath('items.1.size_balcony', '–')
            ->assertJsonPath('items.1.available_at', '–');

        // Loose: decimals are strings on MySQL, numbers on SQLite
        $this->assertEquals([
            'uuid' => $this->item(0)->uuid,
            'number' => '1.01',
            'street' => 'Eglistrasse 1',
            'city' => '8004 Zürich',
            'description' => '1. OG links',
            'tenant' => ' ', // withDefault: an empty tenant
            'rooms' => '3.5',
            'room_description' => '3.5 Zimmer',
            'rent_gross' => '1700',
            'size' => '85.5',
            'size_terrace' => '–',
            'size_patio' => '–',
            'size_balcony' => '12',
            'size_loggia' => '–',
            'available_at' => '01.11.2026',
        ], $response->json('items.0'));

        $this->getJson('/api/user-collection/nope')->assertNotFound();
    }

    public function testAnEstateWithLoggias()
    {
        $koro = $this->estate(['domain' => 'kornhaus-roetelstrasse', 'description' => 'Kornhaus-/Rötelstrasse']);
        $offer = $this->collection($koro, [
            $this->apartment($this->building($koro), $this->floor($koro), $this->room($koro), ['number' => 'H1_101', 'size_loggia' => '6.3']),
        ]);

        $list = $this->getJson("/api/user-collection/{$offer->uuid}")
            ->assertJsonPath('estate.exteriors', ['balcony' => 'Balkon', 'loggia' => 'Loggia', 'patio' => 'Sitzplatz']);
        $item = $this->getJson("/api/user-collection/{$offer->uuid}/item/{$offer->items[0]->uuid}");

        // Loose: decimals are strings on MySQL, numbers on SQLite
        $this->assertEquals('6.3', $list->json('items.0.size_loggia'));
        $this->assertEquals('6.3', $item->json('item.size_loggia'));
    }

    public function testShowWithPagination()
    {
        $url = fn (int $i) => "/api/user-collection/{$this->offer->uuid}/item/{$this->item($i)->uuid}";

        $item = $this->getJson($url(0))
            ->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('item.number', '1.01')
            ->assertJsonPath('item.estate', 'Wohnüberbauung Eglistrasse, 8004 Zürich')
            ->assertJsonPath('item.has_reply', false)
            ->assertJsonPath('pagination', ['index' => 1, 'count' => 3, 'prev' => $this->item(2)->uuid, 'next' => $this->item(1)->uuid])
            ->json('item');
        $this->assertEquals([1500, 200, 1700], [$item['rent_net'], $item['additional_cost'], $item['rent_gross']]);

        $this->getJson($url(1))->assertJsonPath('pagination', ['index' => 2, 'count' => 3, 'prev' => $this->item(0)->uuid, 'next' => $this->item(2)->uuid]);
        $this->getJson($url(2))->assertJsonPath('pagination', ['index' => 3, 'count' => 3, 'prev' => $this->item(1)->uuid, 'next' => $this->item(0)->uuid]);

        $this->getJson("/api/user-collection/{$this->offer->uuid}/item/nope")->assertNotFound();
    }

    public function testExpiredOffers()
    {
        $this->offer->update(['valid_until' => now()->subDay()]);

        $this->getJson("/api/user-collection/{$this->offer->uuid}")->assertOk()->assertExactJson(['valid' => false]);
        $this->getJson("/api/user-collection/{$this->offer->uuid}/item/{$this->item(0)->uuid}")->assertOk()->assertExactJson(['valid' => false]);
    }

    public function testOffersAreValidOnTheLastDay()
    {
        $this->offer->update(['valid_until' => now()]);

        $this->getJson("/api/user-collection/{$this->offer->uuid}")->assertJsonPath('valid', true);
    }

    public function testReplyQueuesTwoMails()
    {
        $this->travelTo('2026-10-09 10:00:00');
        $item = $this->item(1);

        $this->postJson('/api/user-collection', ['uuid' => $item->uuid, 'accepted' => 1, 'parking' => 1, 'comment' => 'Gerne'])
            ->assertOk()->assertExactJson(['success']);

        $item->refresh();
        $this->assertEquals(1, $item->accepted);
        $this->assertEquals(1, $item->parking);
        $this->assertSame('Gerne', $item->comment);
        $this->assertSame('2026-10-09', $item->replied_at->format('Y-m-d'));

        $queue = MailQueue::orderBy('id')->get();
        $this->assertSame(['reply', 'confirmation'], $queue->pluck('type')->all());
        $data = json_decode($queue[0]->data);
        $this->assertSame('1.02', $data->apartment->number);
        $this->assertSame('anna@example.invalid', $data->collection->email);
        $this->assertSame('Eglistrasse', $data->collection->estate->description);

        $this->getJson("/api/user-collection/{$this->offer->uuid}/item/{$item->uuid}")->assertJsonPath('item.has_reply', true);
        $this->postJson('/api/user-collection', ['uuid' => 'nope'])->assertNotFound();
    }
}
