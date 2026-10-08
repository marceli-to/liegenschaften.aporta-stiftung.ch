<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Base;

class Estate extends Base
{
  use SoftDeletes;
  
  protected $fillable = [
    'uuid',
    'domain',
    'description',
    'description_long',
    'city',
    'maps',
    'order',
    'publish',
  ];

  public function buildings()
  {
    return $this->hasMany(Building::class, 'estate_id', 'id');
  }

  public function rooms()
  {
    return $this->belongsToMany(Room::class);
  }

  public function floors()
  {
    return $this->belongsToMany(Floor::class);
  }

  /**
   * A setting of this estate (estates.estates.{domain}.settings)
   *
   * @param string $name
   * @return mixed
   */
  public function setting($name)
  {
    return config('estates.estates.' . $this->domain . '.settings.' . $name);
  }
}
