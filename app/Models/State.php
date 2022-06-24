<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base;

class State extends Base
{
  protected $fillable = [
    'description',
    'order',
    'publish',
  ];

  public const FREE = 1;
  public const RESERVED = 2;
  public const RENTED = 3;
  public const SOLD = 4;

	public function apartments()
	{
		return $this->belongsToMany(Apartment::class);
	}
}
