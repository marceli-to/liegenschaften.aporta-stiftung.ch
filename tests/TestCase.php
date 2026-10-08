<?php

namespace Tests;

use App\Models\Apartment;
use App\Models\Building;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Estate;
use App\Models\Floor;
use App\Models\Room;
use App\Models\State;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Builders for the estate data (no factories: the models are plain).
 * APP_URL is the admin domain (phpunit.xml), so paths hit the admin routes.
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function user(array $attributes = []): User
    {
        $user = new User();
        $user->forceFill(array_merge([
            'firstname' => 'Test',
            'name' => 'Admin',
            'email' => 'admin@example.invalid',
            'password' => Hash::make('correct-horse'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ], $attributes))->save();

        return $user;
    }

    /**
     * The states as in production
     */
    protected function states(): void
    {
        foreach ([1 => 'Frei', 2 => 'Reserviert', 3 => 'Vermietet', 4 => 'Verkauft'] as $id => $description) {
            $state = new State(['description' => $description, 'order' => $id, 'publish' => 1]);
            $state->id = $id;
            $state->save();
        }
    }

    protected function estate(array $attributes = []): Estate
    {
        return Estate::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'domain' => 'eglistrasse',
            'description' => 'Eglistrasse',
            'description_long' => 'Wohnüberbauung Eglistrasse',
            'city' => '8004 Zürich',
            'maps' => 'https://maps.example.invalid',
            'publish' => 1,
        ], $attributes));
    }

    protected function building(Estate $estate, array $attributes = []): Building
    {
        return Building::create(array_merge([
            'description' => 'Haus A',
            'street' => 'Eglistrasse 1',
            'city' => 'Zürich',
            'order' => 1,
            'publish' => 1,
            'estate_id' => $estate->id,
        ], $attributes));
    }

    protected function floor(Estate $estate, array $attributes = []): Floor
    {
        $floor = Floor::create(array_merge(['description' => '1. Obergeschoss', 'abbreviation' => '1.OG', 'order' => 1, 'publish' => 1], $attributes));
        $estate->floors()->attach($floor);

        return $floor;
    }

    protected function room(Estate $estate, array $attributes = []): Room
    {
        $room = Room::create(array_merge(['description' => '3.5 Zimmer', 'abbreviation' => '3.5', 'order' => 1, 'publish' => 1], $attributes));
        $estate->rooms()->attach($room);

        return $room;
    }

    protected function apartment(Building $building, Floor $floor, Room $room, array $attributes = []): Apartment
    {
        return Apartment::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'number' => '1.01',
            'description' => '1. OG links',
            'size' => '85.5',
            'size_terrace' => null,
            'size_patio' => null,
            'size_balcony' => '12',
            'order' => 1,
            'publish' => 1,
            'room_id' => $room->id,
            'state_id' => State::FREE,
            'estate_id' => $building->estate_id,
            'floor_id' => $floor->id,
            'building_id' => $building->id,
            'rent_net' => '1500',
            'additional_cost' => '200',
            'rent_gross' => '1700',
        ], $attributes));
    }

    protected function tenant(array $attributes = []): Tenant
    {
        return Tenant::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'firstname' => 'Erika',
            'name' => 'Muster',
            'email' => 'erika@example.invalid',
            'phone' => '044 000 00 00',
            'publish' => 1,
        ], $attributes));
    }

    /**
     * An offer with one item per apartment
     */
    protected function collection(Estate $estate, array $apartments, array $attributes = []): Collection
    {
        $collection = Collection::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'salutation' => 'Frau',
            'firstname' => 'Anna',
            'name' => 'Beispiel',
            'email' => 'anna@example.invalid',
            'valid_until' => now()->addDays(5),
            'estate_id' => $estate->id,
        ], $attributes));

        foreach ($apartments as $apartment) {
            CollectionItem::create([
                'uuid' => (string) Str::uuid(),
                'collection_id' => $collection->id,
                'apartment_id' => $apartment->id,
            ]);
        }

        return $collection;
    }
}
