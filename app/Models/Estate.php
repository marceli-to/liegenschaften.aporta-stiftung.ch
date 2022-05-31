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
}
