<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base;

class CollectionItem extends Base
{
  protected $fillable = [
    'collection_id',
    'apartment_id',
  ];

	public function apartment()
	{
		return $this->hasOne(Apartment::class, 'id', 'apartment_id');
	}

	public function collection()
	{
		return $this->belongsTo(Collection::class, 'collection_id', 'id');
	}
}
