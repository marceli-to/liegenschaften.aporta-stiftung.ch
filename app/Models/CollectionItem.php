<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Base;

class CollectionItem extends Base
{
  protected $casts = [
    'created_at' => 'date:d.m.Y',
    'updated_at' => 'date:d.m.Y',
    'deleted_at' => 'date:d.m.Y',
    'sent_at'    => 'date:d.m.Y',
    'read_at'    => 'date:d.m.Y',
    'replied_at' => 'date:d.m.Y'
  ];
  
  protected $fillable = [
    'uuid',
    'sent_at',
    'read_at',
    'replied_at',
    'accepted',
    'comment',
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
