<?php

namespace Tests\Feature\Api;

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\MailQueue;

class CollectionApiTest extends ApiTestCase
{
    private function payload(array $items, array $data = []): array
    {
        return array_merge([
            'candidates' => [
                ['salutation' => 'Frau', 'firstname' => 'Anna', 'name' => 'Beispiel', 'email' => 'anna@example.invalid'],
            ],
            'items' => collect($items)->pluck('uuid')->all(),
            'remarks' => "Besichtigung am Montag\nab 17 Uhr",
        ], $data);
    }

    public function testStoreCreatesAnOfferPerCandidateAndQueuesTheMails()
    {
        $this->travelTo('2026-10-08 10:00:00');
        $a = $this->flat(['number' => '1.01']);
        $b = $this->flat(['number' => '1.02']);

        $id = $this->postJson('/api/collection', $this->payload([$a, $b], [
            'candidates' => [
                ['salutation' => 'Frau', 'firstname' => 'Anna', 'name' => 'Beispiel', 'email' => 'anna@example.invalid'],
                ['salutation' => 'Herr', 'firstname' => 'Bruno', 'name' => 'Muster', 'email' => 'bruno@example.invalid'],
            ],
        ]))->assertOk()->json('collectionId');

        $this->assertSame(2, Collection::count());
        $last = Collection::findOrFail($id);
        $this->assertSame('bruno@example.invalid', $last->email);
        $this->assertSame('Herr', $last->salutation);
        $this->assertEquals($this->eglistrasse->id, $last->estate_id);
        $this->assertSame('2026-10-13', $last->valid_until->format('Y-m-d'));
        $this->assertSame("Besichtigung am Montag\nab 17 Uhr", $last->remarks);
        $this->assertEquals([$a->id, $b->id], $last->items->pluck('apartment_id')->all());

        $queue = MailQueue::orderBy('id')->get();
        $this->assertSame(['offer', 'offer'], $queue->pluck('type')->all());
        $this->assertSame(0, (int) $queue[0]->processed);
        $data = json_decode($queue[1]->data);
        $this->assertSame('bruno@example.invalid', $data->email);
        $this->assertSame('eglistrasse', $data->estate->domain);
        $this->assertSame('1.02', $data->items[1]->apartment->number);
        $this->assertSame('3.5', $data->items[0]->apartment->room->abbreviation);
        $this->assertSame('Eglistrasse 1', $data->items[0]->apartment->building->street);
    }

    public function testStoreValidation()
    {
        $this->postJson('/api/collection', $this->payload([], [
            'candidates' => [['salutation' => 'Frau', 'firstname' => '', 'name' => 'Beispiel', 'email' => 'nope']],
        ]))->assertStatus(422)->assertJsonValidationErrors(['candidates.0.firstname', 'candidates.0.email', 'items']);

        $this->assertSame(0, Collection::count());
        $this->assertSame(0, MailQueue::count());
    }

    public function testUpdateReplacesTheOffer()
    {
        $a = $this->flat(['number' => '1.01']);
        $b = $this->flat(['number' => '1.02']);
        $old = $this->collection($this->eglistrasse, [$a]);

        $id = $this->putJson("/api/collection/{$old->uuid}", $this->payload([$b]))->assertOk()->json('collectionId');

        $this->assertSoftDeleted($old);
        $this->assertSame(0, $old->items()->count());
        $new = Collection::findOrFail($id);
        $this->assertNotSame($old->uuid, $new->uuid);
        $this->assertEquals([$b->id], $new->items->pluck('apartment_id')->all());
        $this->assertSame(1, MailQueue::where('type', 'offer')->count());
    }

    public function testList()
    {
        $apartment = $this->flat();
        $this->collection($this->eglistrasse, [$apartment]);
        $this->collection($this->eglistrasse, [$apartment], ['email' => 'b@example.invalid']);

        $this->getJson('/api/collections')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.estate.domain', 'eglistrasse')
            ->assertJsonPath('data.0.items.0.apartment.number', '1.01')
            ->assertJsonPath('data.0.items.0.apartment.building.street', 'Eglistrasse 1')
            ->assertJsonPath('data.1.email', 'b@example.invalid');
    }

    public function testFind()
    {
        $collection = $this->collection($this->eglistrasse, [$this->flat()]);

        $this->getJson("/api/collection/{$collection->uuid}")
            ->assertOk()
            ->assertJsonPath('uuid', $collection->uuid)
            ->assertJsonPath('items.0.apartment.room.abbreviation', '3.5')
            ->assertJsonPath('items.0.apartment.floor.abbreviation', '1.OG');

        $this->getJson('/api/collection/nope')->assertNotFound();
    }

    public function testItemsListSkipsTheArchive()
    {
        $collection = $this->collection($this->eglistrasse, [$this->flat(['number' => '1.01']), $this->flat(['number' => '1.02'])]);
        $collection->items->last()->update(['archive' => 1]);

        $this->getJson('/api/collection-items')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.apartment.number', '1.01')
            ->assertJsonPath('data.0.collection.estate.domain', 'eglistrasse')
            ->assertJsonPath('data.0.apartment.estate.domain', 'eglistrasse');
    }

    public function testItemDestroy()
    {
        $collection = $this->collection($this->eglistrasse, [$this->flat()]);
        $item = $collection->items->first();

        $this->deleteJson("/api/collection-item/{$item->uuid}")->assertOk()->assertExactJson(['successfully deleted']);

        $this->assertSoftDeleted($item);
        $this->assertNotSoftDeleted($collection);
        $this->assertSame(0, CollectionItem::count());
    }
}
