<?php

namespace Tests\Feature\Api;

use App\Models\Apartment;
use App\Models\CollectionItem;
use App\Models\State;
use App\Models\Tenant;

class ApartmentApiTest extends ApiTestCase
{
    private function numbers($response): array
    {
        return collect($response->assertOk()->json('data'))->pluck('number')->all();
    }

    public function testListIsTheCurrentEstateByBuilding()
    {
        $back = $this->building($this->eglistrasse, ['street' => 'Eglistrasse 3', 'order' => 2]);
        $this->apartment($back, $this->firstFloor, $this->threeRooms, ['number' => '3.01', 'order' => 9]);
        $this->flat(['number' => '1.01', 'order' => 1]);
        $this->flat(['number' => '1.02', 'order' => 2]);

        $koro = $this->otherEstate();
        $this->apartment($this->building($koro), $this->firstFloor, $this->threeRooms, ['number' => 'KO.01']);

        $response = $this->getJson('/api/apartments');
        $this->assertSame(['1.02', '1.01', '3.01'], $this->numbers($response));
        $response->assertJsonPath('data.0.building.street', 'Eglistrasse 1')
            ->assertJsonPath('data.0.room.abbreviation', '3.5')
            ->assertJsonPath('data.0.tenant.firstname', null)
            ->assertJsonPath('data.0.size_terrace', '–')
            ->assertJsonPath('data.0.sortable_rent', 1700);
    }

    public function testFetchBySelection()
    {
        $a = $this->flat(['number' => '1.01']);
        $this->flat(['number' => '1.02']);
        $c = $this->flat(['number' => '1.03']);

        $response = $this->postJson('/api/apartments', ['items' => [$a->uuid, $c->uuid]]);
        $this->assertSame(['1.01', '1.03'], $this->numbers($response));
    }

    public function testFilter()
    {
        $back = $this->building($this->eglistrasse, ['street' => 'Eglistrasse 3', 'order' => 2]);
        $ground = $this->floor($this->eglistrasse, ['description' => 'Erdgeschoss', 'abbreviation' => 'EG', 'order' => 0]);
        $small = $this->room($this->eglistrasse, ['description' => '2.5 Zimmer', 'abbreviation' => '2.5']);

        $a = $this->flat(['number' => 'A', 'order' => 1, 'rent_gross' => '900']);
        $b = $this->flat(['number' => 'B', 'order' => 2, 'rent_gross' => '1500', 'state_id' => State::RENTED, 'room_id' => $small->id]);
        $c = $this->apartment($back, $ground, $this->threeRooms, ['number' => 'C', 'order' => 3, 'rent_gross' => '2400']);
        $this->collection($this->eglistrasse, [$b]);

        $filter = fn (array $input) => $this->numbers($this->postJson('/api/apartments/filter', $input));

        $this->assertSame(['A', 'B', 'C'], $filter([]));
        $this->assertSame(['C'], $filter(['buildings' => [$back->id]]));
        $this->assertSame(['B'], $filter(['rooms' => [$small->id]]));
        $this->assertSame(['C'], $filter(['floors' => [$ground->id]]));
        $this->assertSame(['A', 'C'], $filter(['states' => [State::FREE]]));
        $this->assertSame(['A'], $filter(['rent' => 'lt:1000']));
        $this->assertSame(['B'], $filter(['rent' => '1000:1501']));
        $this->assertSame(['C'], $filter(['rent' => 'gt:2000']));
        $this->assertSame(['B'], $filter(['collections' => true]));
        $this->assertSame(['A'], $filter(['buildings' => [$this->house->id], 'states' => [State::FREE]]));
    }

    public function testFind()
    {
        $apartment = $this->flat();
        $this->collection($this->eglistrasse, [$apartment]);

        $this->getJson("/api/apartment/{$apartment->uuid}")
            ->assertOk()
            ->assertJsonPath('uuid', $apartment->uuid)
            ->assertJsonPath('estate.domain', 'eglistrasse')
            ->assertJsonPath('collection_items.0.collection.email', 'anna@example.invalid');

        $this->getJson('/api/apartment/nope')->assertNotFound();
    }

    public function testUpdateAddsATenant()
    {
        $apartment = $this->flat();

        $this->putJson("/api/apartment/{$apartment->uuid}", [
            'state_id' => State::RENTED,
            'available_at' => '01.11.2026',
            'rent_gross' => '1800',
            'rent_net' => '1550',
            'additional_cost' => '250',
            'tenant' => ['firstname' => 'Erika', 'name' => 'Muster', 'email' => 'erika@example.invalid', 'phone' => '044'],
        ])->assertOk()->assertExactJson(['successfully updated']);

        $apartment->refresh();
        $this->assertEquals(State::RENTED, $apartment->state_id);
        $this->assertSame('2026-11-01', $apartment->getRawOriginal('available_at'));
        $this->assertSame('1800', (string) $apartment->rent_gross);
        $this->assertSame('Erika Muster', $apartment->tenant->full_name);
    }

    public function testUpdateChangesTheTenant()
    {
        $tenant = $this->tenant();
        $apartment = $this->flat(['tenant_id' => $tenant->id]);

        $this->putJson("/api/apartment/{$apartment->uuid}", [
            'state_id' => State::RENTED,
            'rent_gross' => '1700', 'rent_net' => '1500', 'additional_cost' => '200',
            'tenant' => ['uuid' => $tenant->uuid, 'firstname' => 'Erika', 'name' => 'Neu', 'email' => 'neu@example.invalid', 'phone' => '079'],
        ])->assertOk();

        $this->assertSame(1, Tenant::count());
        $this->assertSame('Neu', $tenant->fresh()->name);
        $this->assertSame('neu@example.invalid', $tenant->fresh()->email);
        $this->assertNull($apartment->fresh()->getRawOriginal('available_at'));
    }

    public function testUpdateRemovesTheTenant()
    {
        $apartment = $this->flat(['tenant_id' => $this->tenant()->id]);

        $this->putJson("/api/apartment/{$apartment->uuid}", [
            'state_id' => State::FREE,
            'rent_gross' => '1700', 'rent_net' => '1500', 'additional_cost' => '200',
            'tenant' => ['firstname' => null, 'name' => null],
        ])->assertOk();

        $this->assertNull($apartment->fresh()->tenant_id);
    }

    public function testUpdateValidation()
    {
        $apartment = $this->flat();

        $this->putJson("/api/apartment/{$apartment->uuid}", ['state_id' => State::RENTED])
            ->assertStatus(422)
            ->assertJsonPath('errors.rent_gross.0', 'Mietzins Brutto wird benötigt!')
            ->assertJsonPath('errors.rent_net.0', 'Mietzins Netto wird benötigt!')
            ->assertJsonPath('errors.additional_cost.0', 'Nebenkosten werden benötigt!');

        $this->assertEquals(State::FREE, $apartment->fresh()->state_id);
    }

    public function testAssignReservesForTheCandidate()
    {
        $apartment = $this->flat();
        $collection = $this->collection($this->eglistrasse, [$apartment]);
        $item = $collection->items->first();
        $item->update(['parking' => 1]);

        $this->putJson("/api/apartment/assign/{$apartment->uuid}", ['collectionItemUuid' => $item->uuid])
            ->assertOk()->assertExactJson(['successfully updated']);

        $apartment->refresh();
        $this->assertEquals(State::RESERVED, $apartment->state_id);
        $this->assertSame('Anna Beispiel', $apartment->tenant->full_name);
        $this->assertSame('anna@example.invalid', $apartment->tenant->email);
        $this->assertEquals(1, $apartment->tenant->parking);
        $this->assertEquals(1, $item->fresh()->lock);
    }

    public function testFinalizeRentsAndClearsTheOffers()
    {
        $apartment = $this->flat();
        $other = $this->flat(['number' => '1.02']);
        $accepted = $this->collection($this->eglistrasse, [$apartment, $other]);
        $competing = $this->collection($this->eglistrasse, [$apartment], ['email' => 'b@example.invalid']);
        $untouched = $this->collection($this->eglistrasse, [$other], ['email' => 'c@example.invalid']);

        $this->putJson("/api/apartment/finalize/{$apartment->uuid}", ['collectionItemUuid' => $accepted->items->first()->uuid])
            ->assertOk();

        $this->assertEquals(State::RENTED, $apartment->fresh()->state_id);
        $this->assertSoftDeleted($accepted);
        $this->assertSame(0, $accepted->items()->count());
        $this->assertSame(0, CollectionItem::where('apartment_id', $apartment->id)->count());
        $this->assertNotSoftDeleted($competing);
        $this->assertSame(1, $untouched->items()->count());
    }

    public function testResetFreesTheApartment()
    {
        $apartment = $this->flat(['tenant_id' => $this->tenant()->id, 'state_id' => State::RENTED]);
        $this->collection($this->eglistrasse, [$apartment]);

        $this->deleteJson("/api/apartment/{$apartment->uuid}")
            ->assertOk()
            ->assertJsonPath('state_id', State::FREE)
            ->assertJsonPath('tenant_id', null)
            ->assertJsonPath('collection_items', []);

        $this->assertNotSoftDeleted($apartment);
    }
}
