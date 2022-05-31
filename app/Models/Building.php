<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base;

class Building extends Base
{
  protected $fillable = [
    'description',
    'street',
    'city',
    'order',
    'publish',
    'estate_id',
  ];

  public function estate()
  {
		return $this->belongsTo(Estate::class, 'estate_id', 'id');
  }

  public function apartments()
  {
    return $this->hasMany(Apartment::class, 'building_id', 'id');
  }

}
