<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserApiTest extends ApiTestCase
{
    public function testList()
    {
        $this->user(['email' => 'b@example.invalid', 'name' => 'Aaron']);

        $this->assertSame(
            ['Aaron', 'Admin'],
            collect($this->getJson('/api/users')->assertOk()->json('data'))->pluck('name')->all()
        );
        $this->getJson('/api/users')->assertJsonMissingPath('data.0.password');
    }

    public function testCurrentUser()
    {
        $this->getJson('/api/user')->assertOk()->assertExactJson([
            'id' => $this->admin->id,
            'firstname' => 'Test',
            'name' => 'Admin',
            'full_name' => 'Test Admin',
            'email' => 'admin@example.invalid',
            'admin' => true,
        ]);
    }

    public function testCreate()
    {
        $id = $this->postJson('/api/user', [
            'firstname' => 'Camilla',
            'name' => 'Walker',
            'email' => 'camilla@example.invalid',
            'password' => 'battery-staple',
            'role' => 'admin',
        ])->assertOk()->json('id');

        $user = User::findOrFail($id);
        $this->assertTrue(Hash::check('battery-staple', $user->password));
        $this->assertSame('admin', $user->role);
        $this->assertNotNull($user->email_verified_at);
    }

    public function testCreateValidation()
    {
        $this->postJson('/api/user', ['email' => 'admin@example.invalid', 'password' => 'short'])
            ->assertStatus(422)
            ->assertJsonPath('errors.firstname.0', 'Vorname fehlt')
            ->assertJsonPath('errors.name.0', 'Name fehlt')
            ->assertJsonPath('errors.email.0', 'E-Mail bereits vergeben')
            ->assertJsonPath('errors.password.0', 'Passwort zu kurz');
    }

    public function testUpdate()
    {
        $user = $this->user(['email' => 'b@example.invalid']);

        $this->putJson("/api/user/{$user->id}", [
            'firstname' => 'Bea',
            'name' => 'Neu',
            'email' => 'bea@example.invalid',
            'password' => 'battery-staple',
            'role' => 'admin',
        ])->assertOk()->assertJsonPath('email', 'bea@example.invalid');

        $user->refresh();
        $this->assertSame('Bea Neu', $user->full_name);
        $this->assertTrue(Hash::check('battery-staple', $user->password));
    }

    public function testUpdateKeepsThePasswordWhenEmpty()
    {
        $user = $this->user(['email' => 'b@example.invalid']);

        $this->putJson("/api/user/{$user->id}", ['firstname' => 'Bea', 'name' => 'Neu', 'email' => 'b@example.invalid', 'password' => null, 'role' => 'admin'])
            ->assertOk();

        $this->assertTrue(Hash::check('correct-horse', $user->fresh()->password));
    }

    public function testUpdateValidation()
    {
        $user = $this->user(['email' => 'b@example.invalid']);

        $this->putJson("/api/user/{$user->id}", ['firstname' => 'Bea', 'name' => 'Neu', 'email' => 'admin@example.invalid', 'role' => 'admin'])
            ->assertStatus(422)->assertJsonValidationErrors('email');
        $this->putJson("/api/user/{$user->id}", ['firstname' => 'Bea', 'name' => 'Neu', 'email' => 'b@example.invalid', 'password' => 'short', 'role' => 'admin'])
            ->assertStatus(422)->assertJsonValidationErrors('password');
        $this->putJson("/api/user/{$user->id}", ['firstname' => '', 'name' => '', 'email' => 'b@example.invalid'])
            ->assertStatus(422)
            ->assertJsonPath('errors.firstname.0', 'Vorname fehlt')
            ->assertJsonPath('errors.name.0', 'Name fehlt');

        $this->assertSame('b@example.invalid', $user->fresh()->email);
    }

    public function testDestroy()
    {
        $user = $this->user(['email' => 'b@example.invalid']);

        $this->deleteJson("/api/user/{$user->id}")->assertOk()->assertExactJson([true]);
        $this->assertModelMissing($user);
    }
}
