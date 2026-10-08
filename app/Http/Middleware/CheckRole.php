<?php
namespace App\Http\Middleware;
use Closure;

class CheckRole
{
  /**
   * Only users with one of the given roles (role:admin,editor)
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @param  string  ...$roles
   * @return mixed
   */
  public function handle($request, Closure $next, ...$roles)
  {
    if (!in_array($request->user()?->role, $roles, true))
    {
      abort(403);
    }
    return $next($request);
  }
}
