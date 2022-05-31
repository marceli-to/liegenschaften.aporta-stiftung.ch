<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base;

class Room extends Base
{
  protected $fillable = [
    'abbreviation',
    'description',
    'order',
    'publish',
  ];

  public function estate()
  {
    return $this->belongsToMany(Estate::class);
  }
}
