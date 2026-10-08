<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestApiTest extends TestCase
{
    use RefreshDatabase;

    public function testTheAdminApiNeedsALogin()
    {
        foreach (['/api/user', '/api/users', '/api/apartments', '/api/collections', '/api/collection-items', '/api/tenants', '/api/settings/buildings'] as $uri) {
            $this->getJson($uri)->assertUnauthorized();
        }
        $this->postJson('/api/collection', [])->assertUnauthorized();
        $this->putJson('/api/apartment/x', [])->assertUnauthorized();
        $this->deleteJson('/api/user/1')->assertUnauthorized();
    }

    public function testTheExportsNeedALogin()
    {
        $this->get('/export/objekte')->assertRedirect('/login');
        $this->get('/export/mieter')->assertRedirect('/login');
    }
}
