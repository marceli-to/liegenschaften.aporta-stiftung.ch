<?php
namespace App\Support;
use App\Models\Estate;

/**
 * The estate the current request works on.
 *
 * On the admin domain the estate chosen in the header (session), else the
 * estate configured for the domain (estates.current).
 */

class CurrentEstate
{
  protected ?Estate $estate = null;

  /**
   * Key of the current estate (matches estates.domain)
   *
   * @return string
   */
  public function key()
  {
    return $this->chosen() ?? config('estates.current');
  }

  /**
   * Choose the estate the admin works on (session)
   *
   * @param string $key
   * @return void
   */
  public function set($key)
  {
    request()->session()->put('estate', $key);
  }

  /**
   * The estates the admin can choose from
   *
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function all()
  {
    return Estate::whereIn('domain', array_keys(config('estates.estates')))->where('publish', 1)->orderBy('id')->get();
  }

  /**
   * The current estate
   *
   * @return Estate
   */
  public function get()
  {
    // Cached per key: the choice can change within the instance's lifetime
    $key = $this->key();
    if ($this->estate?->domain !== $key)
    {
      $this->estate = Estate::where('domain', $key)->firstOrFail();
    }
    return $this->estate;
  }

  /**
   * Id of the current estate
   *
   * @return int
   */
  public function id()
  {
    return $this->get()->id;
  }

  /**
   * A setting of an estate (estates.estates.{key}.settings), the current one
   * by default
   *
   * @param string $name
   * @param string|null $key
   * @return mixed
   */
  public function setting($name, $key = null)
  {
    return config('estates.estates.' . ($key ?? $this->key()) . '.settings.' . $name);
  }

  /**
   * Public url of an estate, the current one by default
   *
   * @param string|null $key
   * @return string
   */
  public function url($key = null)
  {
    return config('estates.estates.' . ($key ?? $this->key()) . '.url');
  }

  /**
   * Key chosen in the admin, if any (and still configured)
   *
   * @return string|null
   */
  protected function chosen()
  {
    $request = request();
    if (!$request->hasSession() || $request->getHost() != config('client.admin_domain'))
    {
      return null;
    }

    $key = $request->session()->get('estate');
    return is_string($key) && config()->has('estates.estates.' . $key) ? $key : null;
  }
}
