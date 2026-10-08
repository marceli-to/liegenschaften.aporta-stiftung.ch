<?php

namespace Tests\Feature\Api;

use App\Models\Collection;
use App\Models\Estate;

/**
 * The estate selector: the admin's choice (session) scopes the lists, the
 * offers and the exports
 */
class EstateApiTest extends ApiTestCase
{
    private Estate $koro;

    protected function setUp(): void
    {
        parent::setUp();

        // Sanctum starts the session for requests from the admin
        $this->withHeader('Referer', config('app.url'));
        $this->koro = $this->estate(['domain' => 'kornhaus-roetelstrasse', 'description' => 'Kornhaus-/Rötelstrasse']);
    }

    private function koroFlat(array $attributes = [])
    {
        $building = $this->building($this->koro, ['description' => 'H1', 'street' => 'Kornhausstrasse 48']);

        return $this->apartment($building, $this->firstFloor, $this->threeRooms, $attributes);
    }

    private function onKoro(): static
    {
        return $this->withSession(['estate' => 'kornhaus-roetelstrasse']);
    }

    public function testSwitchStoresTheEstateInTheSession()
    {
        $this->putJson('/api/estate', ['key' => 'kornhaus-roetelstrasse'])
            ->assertOk()->assertJson(['key' => 'kornhaus-roetelstrasse'])
            ->assertSessionHas('estate', 'kornhaus-roetelstrasse');
    }

    public function testSwitchOnlyToAConfiguredEstate()
    {
        // In the DB but not in config/estates.php
        $this->estate(['domain' => 'koro']);

        $this->putJson('/api/estate', ['key' => 'koro'])->assertStatus(422)->assertJsonValidationErrors('key');
        $this->putJson('/api/estate', [])->assertStatus(422)->assertJsonValidationErrors('key');
    }

    public function testSwitchOnlyToAPublishedEstate()
    {
        $this->koro->update(['publish' => 0]);

        $this->putJson('/api/estate', ['key' => 'kornhaus-roetelstrasse'])->assertStatus(422);
    }

    public function testTheAdminPageFollowsTheChoice()
    {
        $this->get('/administration/objekte')
            ->assertSee('data-estate="eglistrasse"', false)
            ->assertSee('&quot;key&quot;:&quot;kornhaus-roetelstrasse&quot;', false);

        $this->onKoro()->get('/administration/objekte')
            ->assertSee('data-estate="kornhaus-roetelstrasse"', false)
            ->assertSee('data-estate-name="Kornhaus-/Rötelstrasse"', false);
    }

    public function testAnUnknownChoiceFallsBackToTheDomainsEstate()
    {
        $this->withSession(['estate' => 'gone'])->get('/administration/objekte')
            ->assertSee('data-estate="eglistrasse"', false);
    }

    /**
     * One offer and one tenant per estate
     */
    private function bothEstates(): void
    {
        $egli = $this->flat(['number' => '1.01', 'tenant_id' => $this->tenant(['name' => 'Egli'])->id]);
        $koro = $this->koroFlat(['number' => 'H1_101', 'tenant_id' => $this->tenant(['name' => 'Koro', 'email' => 'koro@example.invalid'])->id]);
        $this->collection($this->eglistrasse, [$egli], ['name' => 'Egli-Angebot']);
        $this->collection($this->koro, [$koro], ['name' => 'Koro-Angebot']);
    }

    public function testListsOfTheDomainsEstateByDefault()
    {
        $this->bothEstates();

        $this->getJson('/api/apartments')->assertJsonPath('data.*.number', ['1.01']);
        $this->getJson('/api/collections')->assertJsonPath('data.*.name', ['Egli-Angebot']);
        $this->getJson('/api/collection-items')->assertJsonPath('data.*.apartment.number', ['1.01']);
        $this->getJson('/api/tenants')->assertJsonPath('data.*.name', ['Egli']);
        $this->getJson('/api/tenants/example')->assertJsonPath('data.*.name', ['Egli']);
    }

    public function testListsFollowTheChoice()
    {
        $this->bothEstates();

        $this->onKoro();
        $this->getJson('/api/apartments')->assertJsonPath('data.*.number', ['H1_101']);
        $this->getJson('/api/collections')->assertJsonPath('data.*.name', ['Koro-Angebot']);
        $this->getJson('/api/collection-items')->assertJsonPath('data.*.apartment.number', ['H1_101']);
        $this->getJson('/api/tenants')->assertJsonPath('data.*.name', ['Koro']);
        $this->getJson('/api/tenants/example')->assertJsonPath('data.*.name', ['Koro']);
    }

    public function testOffersGetTheChosenEstate()
    {
        $koro = $this->koroFlat();
        $payload = [
            'candidates' => [['salutation' => 'Frau', 'firstname' => 'Anna', 'name' => 'Beispiel', 'email' => 'anna@example.invalid']],
            'items' => [$koro->uuid],
        ];

        $id = $this->onKoro()->postJson('/api/collection', $payload)->assertOk()->json('collectionId');

        $this->assertEquals($this->koro->id, Collection::findOrFail($id)->estate_id);
    }

    public function testOffersOnlyWithApartmentsOfTheirEstate()
    {
        // Picked on Eglistrasse, switched to KORO in another tab
        $egli = $this->flat();
        $payload = [
            'candidates' => [['salutation' => 'Frau', 'firstname' => 'Anna', 'name' => 'Beispiel', 'email' => 'anna@example.invalid']],
            'items' => [$egli->uuid],
        ];

        $this->onKoro()->postJson('/api/collection', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items.0' => 'Die Objekte gehören nicht zur gewählten Liegenschaft.']);
        $this->assertSame(0, Collection::count());
    }

    public function testEditingAnOfferKeepsItsEstate()
    {
        $egli = $this->flat();
        $old = $this->collection($this->eglistrasse, [$egli]);
        $payload = [
            'candidates' => [['salutation' => 'Frau', 'firstname' => 'Anna', 'name' => 'Beispiel', 'email' => 'anna@example.invalid']],
            'items' => [$egli->uuid],
        ];

        $id = $this->onKoro()->putJson("/api/collection/{$old->uuid}", $payload)->assertOk()->json('collectionId');

        $this->assertEquals($this->eglistrasse->id, Collection::findOrFail($id)->estate_id);
    }
}
