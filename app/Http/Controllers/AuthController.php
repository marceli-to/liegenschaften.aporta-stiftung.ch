<?php
namespace App\Http\Controllers;
use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Login, logout and password reset for the admin (replaces laravel/ui)
 */
class AuthController extends BaseController
{
  /**
   * Failed attempts per e-mail and IP before a one-minute lockout
   */
  private const MAX_ATTEMPTS = 5;

  public function showLogin()
  {
    return view('auth.login');
  }

  public function login(Request $request)
  {
    $credentials = $request->validate([
      'email' => 'required|string|email',
      'password' => 'required|string',
    ]);

    $key = Str::transliterate(Str::lower($credentials['email']) . '|' . $request->ip());

    if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS))
    {
      throw ValidationException::withMessages([
        'email' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)]),
      ]);
    }

    if (!Auth::attempt($credentials))
    {
      RateLimiter::hit($key);
      throw ValidationException::withMessages(['email' => __('auth.failed')]);
    }

    RateLimiter::clear($key);
    $request->session()->regenerate();

    return redirect()->intended('/');
  }

  public function logout(Request $request)
  {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
  }

  public function showForgotPassword()
  {
    return view('auth.passwords.email');
  }

  public function sendResetLink(Request $request)
  {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink($request->only('email'));

    return $status === Password::RESET_LINK_SENT
      ? back()->with('status', __($status))
      : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
  }

  public function showResetPassword(Request $request, string $token)
  {
    return view('auth.passwords.reset', ['token' => $token, 'email' => $request->email]);
  }

  public function resetPassword(Request $request)
  {
    $request->validate([
      'token' => 'required',
      'email' => 'required|email',
      'password' => 'required|confirmed|min:8',
    ]);

    $status = Password::reset(
      $request->only('email', 'password', 'password_confirmation', 'token'),
      function (User $user, string $password) {
        $user->forceFill([
          'password' => Hash::make($password),
          'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));
        Auth::login($user);
      }
    );

    if ($status !== Password::PASSWORD_RESET)
    {
      return back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    }

    $request->session()->regenerate();

    return redirect('/')->with('status', __($status));
  }
}
