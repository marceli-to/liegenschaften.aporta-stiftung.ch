<?php

namespace Tests\Feature\Api;

class SettingsApiTest extends ApiTestCase
{
    public function testBuildingsOfTheCurrentEstate()
    {
        $this->building($this->eglistrasse, ['street' => 'Eglistrasse 0', 'order' => 0]);
        $this->building($this->otherEstate(), ['street' => 'Koro 1']);

        $this->assertSame(
            ['Eglistrasse 0', 'Eglistrasse 1'],
            collect($this->getJson('/api/settings/buildings')->assertOk()->json())->pluck('street')->all()
        );
    }

    public function testRoomsAndFloorsOfTheCurrentEstate()
    {
        $this->room($this->eglistrasse, ['abbreviation' => '2.5', 'order' => 0]);
        $this->room($this->otherEstate(), ['abbreviation' => '9.5']);
        $this->floor($this->eglistrasse, ['abbreviation' => 'EG', 'order' => 0]);

        $this->assertSame(['2.5', '3.5'], collect($this->getJson('/api/settings/rooms')->assertOk()->json())->pluck('abbreviation')->values()->all());
        $this->assertSame(['EG', '1.OG'], collect($this->getJson('/api/settings/floors')->assertOk()->json())->pluck('abbreviation')->values()->all());
    }

    public function testStatesRentAndExteriorsFromTheConfig()
    {
        $this->assertSame(['Frei', 'Reserviert', 'Vermietet'], collect($this->getJson('/api/settings/states')->assertOk()->json())->pluck('description')->all());

        $this->getJson('/api/settings/rent')->assertOk()->assertExactJson([
            'lt:1000' => 'bis 1000',
            '1000:1501' => '1000 - 1500',
            '1500:2000' => '1500 - 2000',
            'gt:2000' => 'ab 2000',
        ]);

        $this->getJson('/api/settings/exteriors')->assertOk()->assertExactJson([
            'terrace' => 'Terrasse',
            'patio' => 'Sitzplatz',
            'balcony' => 'Balkon',
        ]);
    }
}
