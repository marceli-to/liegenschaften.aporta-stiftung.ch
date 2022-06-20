<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Estate;
use App\Models\Building;
use App\Models\Room;
use App\Models\Floor;
use App\Models\State;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
  /**
   * Get available rooms
   *
   * @return \Illuminate\Http\Response
   */

  public function buildings()
  {
    return response()->json(Building::where('estate_id', env('ESTATE_ID'))->orderBy('order')->get());
  }

  /**
   * Get available rooms
   *
   * @return \Illuminate\Http\Response
   */

  public function rooms()
  {
    $estate = Estate::findOrFail(env('ESTATE_ID'));
    return response()->json($estate->rooms->sortBy('order'));
  }

  /**
   * Get available floors
   *
   * @return \Illuminate\Http\Response
   */

  public function floors()
  {
    $estate = Estate::findOrFail(env('ESTATE_ID'));
    return response()->json($estate->floors->sortBy('order'));
  }

  /**
   * Get available exteriors
   *
   * @return \Illuminate\Http\Response
   */

  public function exteriors()
  {
    return response()->json(config(env('ESTATE_DOMAIN_KEY'). '.settings.exteriors'));
  }

  /**
   * Get available states
   *
   * @return \Illuminate\Http\Response
   */

  public function states()
  {
    return response()->json(State::whereIn('id', config(env('ESTATE_DOMAIN_KEY'). '.settings.states'))->get());
  }


  /**
   * Get available rent steps
   *
   * @return \Illuminate\Http\Response
   */

  public function rent()
  {
    return response()->json(config(env('ESTATE_DOMAIN_KEY'). '.settings.rent_steps'));
  }


}
