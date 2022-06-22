<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailQueue extends Model
{
  use HasFactory;

  protected $table = 'mail_queue';

  protected $fillable = [
    'type',
    'data',
    'error',
    'processed',
  ];

	public function scopeUnprocessed($query)
	{
		return $query->where('processed', 0);
	}
}
