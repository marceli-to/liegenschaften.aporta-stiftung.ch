<?php
namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
  use Notifiable;
  
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'uuid', 'firstname', 'name', 'email', 'password', 'role'
  ];

  /**
   * The accessors to append to the model's array form.
   *
   * @var array
   */
  protected $appends = ['full_name', 'role'];


  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [
    'password', 'remember_token', 'role'
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
  ];

  /**
   * Role helper for admins
   */

  public function isAdmin()
  {
    return $this->role == 'admin' ? TRUE : FALSE;
  }

  /**
   * Get the user's full name.
   *
   * @param  string  $value
   * @return string
   */
  public function getFullNameAttribute($value)
  {
    return $this->firstname . ' ' . $this->name;
  }
}
