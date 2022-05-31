<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Base extends Model
{
  use HasFactory;

  protected $casts = [
    'created_at' => 'date:d.m.Y',
    'updated_at' => 'date:d.m.Y',
  ];

	public function scopePublished($query)
	{
		return $query->where('publish', '=', '1');
	}
}