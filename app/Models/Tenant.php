<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Base;

class Tenant extends Base
{
  use SoftDeletes;

  protected $fillable = [
    'uuid',
    'firstname',
    'lastname',
    'publish'
  ];

  protected $appends = ['full_name'];

	public function apartment()
	{
		return $this->belongsTo(Apartment::class, 'id', 'tenant_id');
	}

  /**
   * Get the tenants's full name.
   *
   * @param  string  $value
   * @return string
   */
  public function getFullNameAttribute($value)
  {
    return $this->firstname . ' ' . $this->name;
  }
}
