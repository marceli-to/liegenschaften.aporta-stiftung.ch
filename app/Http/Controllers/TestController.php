<?php
namespace App\Http\Controllers;
use App\Http\Controllers\BaseController;
use App\Models\Tenant;
use App\Models\Apartment;
use App\Models\Building;
use App\Models\Estate;
use App\Models\State;
use App\Models\Floor;
use App\Models\Room;
use App\Models\ReplyQueue;
use App\Tasks\Offer;
use App\Tasks\Reply;

use Illuminate\Http\Request;

class TestController extends BaseController
{
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Show all tenants
   *
   * @return \Illuminate\Http\Response
   */

  public function tenants()
  {
    return response()->json(Tenant::get());
  }

  /**
   * Show a single tenant
   *
   * @param Tenant $tenant
   * @return \Illuminate\Http\Response
   */


  public function tenant(Tenant $tenant)
  {
    return response()->json(Tenant::with('apartment')->findOrFail($tenant->id));
  }

  /**
   * Show all apartments
   *
   * @return \Illuminate\Http\Response
   */

  public function apartments()
  {
    return response()->json(Apartment::get());
  }

  /**
   * Show a single apartment
   *
   * @param Apartment $apartment
   * @return \Illuminate\Http\Response
   */

  public function apartment(Apartment $apartment)
  {
    return response()->json(Apartment::with('tenant', 'room', 'state', 'estate', 'floor', 'building')->findOrFail($apartment->id));
  }

  /**
   * Show all buildings by an estate
   *
   * @param Building $building
   * @param Estate $estate
   * @return \Illuminate\Http\Response
   */

  public function buildings(Estate $estate)
  {
    return response()->json(Building::where('estate_id', $estate->id)->get());
  }

  /**
   * Show a single building
   *
   * @param Building $building
   * @return \Illuminate\Http\Response
   */


  public function building(Building $building)
  {
    return response()->json(Building::with('apartments', 'estate')->findOrFail($building->id));
  }

  /**
   * Show all estates
   *
   * @return \Illuminate\Http\Response
   */

  public function estates()
  {
    return response()->json(Estate::get());
  }

  /**
   * Show a single estate
   *
   * @param Estate $estate
   * @return \Illuminate\Http\Response
   */

  public function estate(Estate $estate)
  {
    return response()->json(Estate::with('buildings.apartments', 'floors', 'rooms')->findOrFail($estate->id));
  }

  /**
   * Show all floors or an estate
   *
   * @param Estate $estate
   * @return \Illuminate\Http\Response
   */

  public function floors(Estate $estate)
  {
    return response()->json($estate->floors);
  }

  /**
   * Show all rooms or an estate
   *
   * @param Estate $estate
   * @return \Illuminate\Http\Response
   */

  public function rooms(Estate $estate)
  {
    return response()->json($estate->rooms);
  }

  public function notify()
  {
    $collection = \App\Models\Collection::with('estate', 'items.apartment.room', 'items.apartment.floor', 'items.apartment.building')->where('processed', 0)->get();
    $collection = collect($collection)->splice(0, 1);
    foreach($collection->all() as $c)
    {
      try {
        \Mail::to($c->email)->send(new \App\Mail\Notification($c));
        $c->processed = 1;
        $c->save();

        foreach($c->items as $item)
        {
          $item->sent_at = \Carbon\Carbon::now();
          $item->save();
        }
      } 
      catch(\Throwable $e) {
        $c->processed = 1;
        $c->error = $e;
        $c->save();
        \Log::error($e);
      }
    }
  }

  public function mailOffer()
  {
    $offer = new Offer();
    return response()->json(true);
  }

  public function mailReply()
  {
    $reply = new Reply();
    return response()->json(true);
  }



  public function apartmentUuid()
  {
    $apts = Apartment::get();
    foreach($apts as $a)
    {
      $a->uuid = \Str::uuid();
      $a->save();
    }
    
    return response()->json(true);
  }


}
