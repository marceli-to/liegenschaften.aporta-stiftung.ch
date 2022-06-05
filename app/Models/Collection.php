<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Base;

class Collection extends Base
{
  use SoftDeletes;
  
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
    'firstname',
    'name',
    'email',
    'sent_at',
    'read_at',
    'replied_at',
    'accepted',
    'comment',
    'error',
    'estate_id'
  ];

  public function estate()
  {
		return $this->belongsTo(Estate::class, 'estate_id', 'id');
  }

  public function items()
  {
    return $this->hasMany(CollectionItem::class, 'collection_id', 'id');
  }

}
