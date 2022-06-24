<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Base;

class Collection extends Base
{
  use SoftDeletes;
  
  protected $casts = [
    'valid_until' => 'date:d.m.Y',
    'created_at' => 'date:d.m.Y',
    'updated_at' => 'date:d.m.Y',
    'deleted_at' => 'date:d.m.Y',
  ];

  protected $fillable = [
    'uuid',
    'salutation',
    'firstname',
    'name',
    'email',
    'valid_until',
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

  /**
   * Local scope for valid collections
   */
	public function scopeValid($query)
	{
		$constraint = date('Y-m-d', 
			strtotime(\Config::get('client.deadline_offer'))
		);
		return $query->where('valid_until', '>=', $constraint);
	}

  /**
   * Attribute for valid collections
   * 
   * @return Boolean
   */
  public function valid()
  {
    return date('Y-m-d', time()) <= $this->valid_until;
  }

}
