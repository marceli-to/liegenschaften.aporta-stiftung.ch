<?php

namespace Tests\Feature\Api;

class TenantApiTest extends ApiTestCase
{
    private function names($response): array
    {
        return collect($response->assertOk()->json('data'))->pluck('full_name')->values()->all();
    }

    public function testListHasTenantsWithAnApartmentByBuilding()
    {
        $back = $this->building($this->eglistrasse, ['street' => 'Seitenweg 7', 'order' => 2]);
        $this->apartment($back, $this->firstFloor, $this->threeRooms, ['tenant_id' => $this->tenant(['firstname' => 'Anna', 'name' => 'Aebi'])->id]);
        $this->flat(['tenant_id' => $this->tenant(['firstname' => 'Erika', 'name' => 'Muster'])->id]);
        $this->tenant(['firstname' => 'Ohne', 'name' => 'Wohnung']);

        $response = $this->getJson('/api/tenants');
        $this->assertSame(['Erika Muster', 'Anna Aebi'], $this->names($response));
        $response->assertJsonPath('data.0.apartment.building.street', 'Eglistrasse 1')
            ->assertJsonPath('data.0.apartment.room.abbreviation', '3.5');
    }

    public function testSearch()
    {
        $back = $this->building($this->eglistrasse, ['street' => 'Seitenweg 7', 'order' => 2]);
        $this->apartment($back, $this->firstFloor, $this->threeRooms, ['tenant_id' => $this->tenant(['firstname' => 'Anna', 'name' => 'Aebi', 'email' => 'anna@example.invalid', 'phone' => '079 111'])->id]);
        $this->flat(['tenant_id' => $this->tenant(['firstname' => 'Erika', 'name' => 'Muster'])->id]);

        $this->assertSame(['Erika Muster'], $this->names($this->getJson('/api/tenants/Must')));
        $this->assertSame(['Anna Aebi'], $this->names($this->getJson('/api/tenants/anna@')));
        $this->assertSame(['Anna Aebi'], $this->names($this->getJson('/api/tenants/079')));
        $this->assertSame(['Anna Aebi'], $this->names($this->getJson('/api/tenants/Seitenweg')));
        $this->assertSame([], $this->names($this->getJson('/api/tenants/niemand')));
    }
}
