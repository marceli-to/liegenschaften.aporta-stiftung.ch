<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Room;
use App\Models\Floor;
use App\Models\State;
use App\Support\CurrentEstate;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
  /**
   * Get available rooms
   *
   * @return \Illuminate\Http\Response
   */

  public function buildings(CurrentEstate $estate)
  {
    return response()->json(Building::where('estate_id', $estate->id())->orderBy('order')->get());
  }

  /**
   * Get available rooms
   *
   * @return \Illuminate\Http\Response
   */

  public function rooms(CurrentEstate $estate)
  {
    return response()->json($estate->get()->rooms->sortBy('order'));
  }

  /**
   * Get available floors
   *
   * @return \Illuminate\Http\Response
   */

  public function floors(CurrentEstate $estate)
  {
    return response()->json($estate->get()->floors->sortBy('order'));
  }

  /**
   * Get available exteriors
   *
   * @return \Illuminate\Http\Response
   */

  public function exteriors(CurrentEstate $estate)
  {
    return response()->json($estate->setting('exteriors'));
  }

  /**
   * Get available states
   *
   * @return \Illuminate\Http\Response
   */

  public function states(CurrentEstate $estate)
  {
    return response()->json(State::whereIn('id', $estate->setting('states'))->get());
  }


  /**
   * Get available rent steps
   *
   * @return \Illuminate\Http\Response
   */

  public function rent(CurrentEstate $estate)
  {
    return response()->json($estate->setting('rent_steps'));
  }


}
