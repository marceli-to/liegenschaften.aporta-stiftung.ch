<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\User;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class UserController extends Controller
{

  /**
   * Get a list of users
   * 
   * @return \Illuminate\Http\Response
   */
  public function get()
  { 
    $data = User::orderBy('name')->get();
    return new DataCollection($data);
  }

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

  /**
   * Store a newly created user
   */

  public function create(UserStoreRequest $request)
  {
    $user = User::create([
      'firstname' => $request->input('firstname'),
      'name' => $request->input('name'),
      'email' => $request->input('email'),
      'password' => Hash::make($request->input('password')),
      'email_verified_at' => \Carbon\Carbon::now(),
    ]);

    return response()->json($user);
  }

  /**
   * Update a user
   * 
   * @param UserUpdateRequest $request
   * @param User $user
   */

  public function update(UserUpdateRequest $request, User $user)
  {
    // Validate the email address if it has changed
    if ($request->input('email') !== $user->email)
    {
      $request->validate([
        'email' => 'required|email|unique:users',
      ]);
      $user->update([
        'email' => $request->input('email'),
      ]);
    }

    // Update the password if it is set
    if ($request->input('password'))
    {
      $request->validate([
        'password' => 'required|min:8',
      ]);

      $user->update([
        'password' => Hash::make($request->input('password')),
      ]);
    }

    $user->update([
      'firstname' => $request->input('firstname'),
      'name' => $request->input('name')
    ]);

    return response()->json($user);
  }

  /**
   * Delete a user
   * 
   * @param User $user
   */

  public function destroy(User $user)
  {
    $user->delete();
    return response()->json(true);
  }
}
