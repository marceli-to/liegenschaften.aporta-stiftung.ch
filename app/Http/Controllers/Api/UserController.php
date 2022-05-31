<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class UserController extends Controller
{
  /**
   * Get users info by authenticated user
   */
  public function find()
  {
    $user = User::findOrFail(auth()->user()->id);
    $data = [
      'firstname' => $user->firstname, 
      'name' => $user->name,
      'full_name' => $user->full_name, 
      'email' => $user->email,
    ];

    if ($user->isAdmin())
    {
      $data['admin'] = TRUE;
    }
    
    return response()->json($data);
  }
}
