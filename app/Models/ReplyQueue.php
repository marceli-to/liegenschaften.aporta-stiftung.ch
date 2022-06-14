<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Base;

class ReplyQueue extends Base
{
  use HasFactory;

  protected $table = 'reply_queue';

  protected $fillable = [
    'error',
    'processed',
    'collection_item_id',
  ];

	public function item()
	{
		return $this->hasOne(CollectionItem::class, 'id', 'collection_item_id');
	}

	public function scopeUnprocessed($query)
	{
		return $query->where('processed', 0);
	}
}
