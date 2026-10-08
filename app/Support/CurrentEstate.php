<?php
namespace App\Support;
use App\Models\Estate;

/**
 * The estate the current request works on.
 *
 * Today that is the estate configured for the domain (estates.current).
 * Once the admin manages several estates, key() is the one place that
 * reads the chosen estate from the session instead.
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
    return config('estates.current');
  }

  /**
   * The current estate
   *
   * @return Estate
   */
  public function get()
  {
    return $this->estate ??= Estate::where('domain', $this->key())->firstOrFail();
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
   * A setting of the current estate (estates.estates.{key}.settings)
   *
   * @param string $name
   * @return mixed
   */
  public function setting($name)
  {
    return config('estates.estates.' . $this->key() . '.settings.' . $name);
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
}
