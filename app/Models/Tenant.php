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

	public function apartment()
	{
		return $this->belongsTo(Apartment::class, 'id', 'tenant_id');
	}
}
