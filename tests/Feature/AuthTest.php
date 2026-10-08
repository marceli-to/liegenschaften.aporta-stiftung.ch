<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The admin page names the current estate
        $this->estate();
    }

    public function testLoginPageRenders()
    {
        $this->get('/login')->assertOk()->assertSee('Anmelden')->assertSee('Passwort vergessen?');
    }

    public function testHomeSendsGuestsToTheLogin()
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/administration/objekte')->assertRedirect('/login');
    }

    public function testLoginLeadsToTheAdmin()
    {
        $user = $this->user();

        $this->post('/login', ['email' => 'admin@example.invalid', 'password' => 'correct-horse'])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        $this->get('/')->assertRedirect('/administration/objekte');
        $this->get('/administration/objekte')->assertOk();
    }

    public function testLoginReturnsToTheRequestedAdminPage()
    {
        $this->user();

        $this->get('/administration/angebote')->assertRedirect('/login');
        $this->post('/login', ['email' => 'admin@example.invalid', 'password' => 'correct-horse'])
            ->assertRedirect('https://liegenschaften.aporta-stiftung.ch/administration/angebote');
    }

    public function testWrongPasswordIsRejected()
    {
        $this->user();

        $this->from('/login')->post('/login', ['email' => 'admin@example.invalid', 'password' => 'wrong'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => __('auth.failed')]);

        $this->assertGuest();
    }

    public function testLoginIsThrottledAfterFiveFailures()
    {
        $this->user();

        foreach (range(1, 5) as $i) {
            $this->post('/login', ['email' => 'admin@example.invalid', 'password' => 'wrong']);
        }

        $this->post('/login', ['email' => 'admin@example.invalid', 'password' => 'correct-horse'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertStringContainsString('Zu viele Loginversuche', session('errors')->first('email'));
    }

    public function testLoggedInUsersSkipTheLogin()
    {
        $this->actingAs($this->user())->get('/login')->assertRedirect('/');
        $this->get('/password/reset')->assertRedirect('/');
    }

    public function testEditorsCanOpenTheAdmin()
    {
        $this->actingAs($this->user(['role' => 'editor']))->get('/administration/objekte')->assertOk();
    }

    public function testOtherRolesCannotOpenTheAdmin()
    {
        $this->actingAs($this->user(['role' => '']))->get('/administration/objekte')->assertForbidden();
        $this->actingAs($this->user(['role' => 'guest', 'email' => 'b@example.invalid']))->get('/administration/objekte')->assertForbidden();
    }

    public function testLogoutOnlyByPost()
    {
        $user = $this->user();

        $this->actingAs($user)->get('/logout')->assertStatus(405);
        $this->assertAuthenticatedAs($user);

        $this->actingAs($user)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function testResetMailIsGerman()
    {
        $user = $this->user();
        $mail = (new ResetPassword('abc'))->toMail($user);
        $html = (string) $mail->render();

        $this->assertSame('Passwort zurücksetzen', $mail->subject);
        $this->assertSame('Passwort zurücksetzen', $mail->actionText);
        $this->assertStringContainsString('Dieser Link ist 60 Minuten gültig.', $html);
        $this->assertStringContainsString('Falls der Button «Passwort zurücksetzen» nicht funktioniert', $html);
        $this->assertStringNotContainsString('Reset', $html);
    }

    public function testForgotPasswordSendsTheResetMail()
    {
        Notification::fake();
        $user = $this->user();

        $this->get('/password/reset')->assertOk();
        $this->from('/password/reset')->post('/password/email', ['email' => 'admin@example.invalid'])
            ->assertRedirect('/password/reset')
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $url = $notification->toMail($user)->actionUrl;
            return str_contains($url, '/password/reset/' . $notification->token);
        });
    }

    public function testForgotPasswordForAnUnknownAddress()
    {
        Notification::fake();

        $this->from('/password/reset')->post('/password/email', ['email' => 'nobody@example.invalid'])
            ->assertSessionHasErrors('email');

        Notification::assertNothingSent();
    }

    public function testResetSetsTheNewPasswordAndLogsIn()
    {
        $user = $this->user();
        $token = Password::createToken($user);

        $this->get("/password/reset/{$token}")->assertOk()->assertSee($token);

        $this->post('/password/reset', [
            'token' => $token,
            'email' => 'admin@example.invalid',
            'password' => 'battery-staple',
            'password_confirmation' => 'battery-staple',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('battery-staple', $user->fresh()->password));
    }

    public function testResetNeedsEightCharacters()
    {
        $user = $this->user();
        $token = Password::createToken($user);

        $this->from("/password/reset/{$token}")->post('/password/reset', [
            'token' => $token,
            'email' => 'admin@example.invalid',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('correct-horse', $user->fresh()->password));
    }

    public function testResetWithABadToken()
    {
        $this->user();

        $this->from('/password/reset/nope')->post('/password/reset', [
            'token' => 'nope',
            'email' => 'admin@example.invalid',
            'password' => 'battery-staple',
            'password_confirmation' => 'battery-staple',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('correct-horse', User::first()->password));
    }

    public function testRemovedRoutesAreGone()
    {
        $this->get('/register')->assertNotFound();
        $this->get('/email/verify')->assertNotFound();
        $this->get('/password/confirm')->assertNotFound();
    }

    public function testAdminRoutesOnlyOnTheAdminDomain()
    {
        $this->actingAs($this->user());

        $this->get('https://eglistrasse.aporta-stiftung.ch/')->assertNotFound();
        $this->get('https://eglistrasse.aporta-stiftung.ch/administration/objekte')->assertNotFound();
        $this->get('https://eglistrasse.aporta-stiftung.ch/export/objekte')->assertNotFound();
    }
}
