<?php

namespace Tests\Feature\Api;

use App\Models\Building;
use App\Models\Estate;
use App\Models\Floor;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The admin API on in-memory SQLite, logged in as an admin, with the
 * current estate (eglistrasse) and one building, floor and room
 */
abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Estate $eglistrasse;
    protected Building $house;
    protected Floor $firstFloor;
    protected Room $threeRooms;

    protected function setUp(): void
    {
        parent::setUp();

        $this->states();
        $this->eglistrasse = $this->estate();
        $this->house = $this->building($this->eglistrasse);
        $this->firstFloor = $this->floor($this->eglistrasse);
        $this->threeRooms = $this->room($this->eglistrasse);

        $this->admin = $this->user();
        $this->actingAs($this->admin, 'sanctum');
    }

    /**
     * An apartment of the current estate
     */
    protected function flat(array $attributes = [])
    {
        return $this->apartment($this->house, $this->firstFloor, $this->threeRooms, $attributes);
    }

    /**
     * A second estate (KORO)
     */
    protected function otherEstate(): Estate
    {
        return $this->estate(['domain' => 'koro', 'description' => 'KORO']);
    }
}
