<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Base;
use App\Models\State;

class Apartment extends Base
{
  use SoftDeletes;
  
  protected $fillable = [
    'uuid',
    'number',
    'description',
    'size',
    'size_terrace',
    'size_patio',
    'size_balcony',
    'order',
    'publish',
    'room_id' ,
    'state_id' ,
    'estate_id' ,
    'floor_id' ,
    'building_id' ,
    'tenant_id',
  ];

  /**
   * Relationships
   */

	public function room()
	{
		return $this->hasOne(Room::class, 'id', 'room_id');
	}

  public function floor()
	{
		return $this->hasOne(Floor::class, 'id', 'floor_id');
	}

	public function state()
	{
		return $this->hasOne(State::class, 'id', 'state_id');
	}

	public function estate()
	{
		return $this->hasOne(Estate::class, 'id', 'estate_id');
	}

	public function building()
	{
		return $this->hasOne(Building::class, 'id', 'building_id');
	}

	public function tenant()
	{
		return $this->hasOne(Tenant::class, 'id', 'tenant_id');
	}

  /**
   * Scopes 
   */

  public function scopeFree($query)
  {
    return $query->where('state_id', State::FREE);
  }

  public function scopeReserved($query)
  {
    return $query->where('state_id', State::RESERVED);
  }

  public function scopeRented($query)
  {
    return $query->where('state_id', State::RENTED);
  }

  public function scopeSold($query)
  {
    return $query->where('state_id', State::SOLD);
  }
}
