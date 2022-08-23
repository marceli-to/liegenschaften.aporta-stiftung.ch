<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\Estate;
use App\Models\Building;
use App\Models\Apartment;
use App\Models\State;
use App\Models\Tenant;
use App\Models\CollectionItem;
use App\Http\Requests\ApartmentStoreRequest;
use Illuminate\Http\Request;

class ApartmentController extends Controller
{
  /**
   * Get a list of apartments
   * 
   * @todo Get estate_id from session
   * @return \Illuminate\Http\Response
   */
  public function get()
  { 
    $data = Apartment::with('building', 'floor', 'room', 'tenant', 'collectionItems')->orderBy('order', 'DESC')->where('estate_id', env('ESTATE_ID'))->get();
    return new DataCollection($data->sortBy('building.order'));
  }

  /**
   * Get a list of selected apartments
   * 
   * @todo Get estate_id from session
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function fetch(Request $request)
  { 
    $data = Apartment::with('building', 'floor', 'room', 'tenant')->orderBy('order')->whereIn('uuid', $request->input('items'))->get();
    return new DataCollection($data->sortBy('building.order'));
  }

  /**
   * Get a filtered ist of apartments
   * 
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function filter(Request $request)
  { 
    // Get all apartments
    $data = Apartment::with('building', 'floor', 'room', 'tenant', 'collectionItems')->where('estate_id', env('ESTATE_ID'))->orderBy('order')->get();

    // Buildings
    if ($request->input('buildings'))
    {
      $filtered = collect($data)->whereIn('building_id', $request->input('buildings'));
      $data = $filtered->all();
    }

    // Rooms
    if ($request->input('rooms'))
    {
      $filtered = collect($data)->whereIn('room_id', $request->input('rooms'));
      $data = $filtered->all();
    }

    // Floors
    if ($request->input('floors'))
    {
      $filtered = collect($data)->whereIn('floor_id', $request->input('floors'));
      $data = $filtered->all();
    }

    // States
    if ($request->input('states'))
    {
      $filtered = collect($data)->whereIn('state_id', $request->input('states'));
      $data = $filtered->all();
    }

    // Rent
    if ($request->input('rent'))
    {
      $data = collect($data);
      $constraint = explode(':', $request->input('rent'));
      if ($constraint[0] == 'lt')
      {
        $filtered = $data->where('rent_gross', '<', $constraint[1]);
        $data = $filtered->all();
      }
      else if ($constraint[0] == 'gt')
      {
        $filtered = $data->where('rent_gross', '>', $constraint[1]);
        $data = $filtered->all();
      }
      else
      {
        $filtered = $data->whereBetween('rent_gross', [$constraint[0], $constraint[1]]);
        $data = $filtered->all();
      }
    }

    // Collections
    if ($request->input('collections'))
    {
      $data_temp = [];
      foreach($data as $d)
      {
        if ($d->collectionItems->count() > 0)
        {
          $data_temp[] = $d;
        }
      }
      $data = collect($data_temp);
    }

    // Exterior
    if ($request->input('exterior'))
    {
      $filtered = collect($data)->where('size_' . $request->input('exterior'), '>', 0);      
      $data = $filtered->all();
    }

    return new DataCollection(collect($data)->sortBy('building.order'));
  }

  /**
   * Get a single apartment
   * 
   * @param Apartment $apartment
   * @return \Illuminate\Http\Response
   */
  public function find(Apartment $apartment)
  {
    $apartment = Apartment::with('building', 'floor', 'room', 'tenant', 'estate', 'collectionItems.collection')->findOrFail($apartment->id);
    return response()->json($apartment);
  }

  /**
   * Update a apartment
   *
   * @param Apartment $apartment
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function update(Apartment $apartment, Request $request)
  {
    $apartment = Apartment::with('tenant')->findOrFail($apartment->id);

    // State
    $apartment->state_id = $request->input('state_id');
    $apartment->save();

    // Date available
    $apartment->available_at = $request->input('available_at') ? $request->input('available_at') : NULL;
    $apartment->save();


    // Tenants
    if (!$request->input('tenant.firstname') && !$request->input('tenant.name'))
    {
      $apartment->tenant_id = null;
      $apartment->save();
      return response()->json('successfully updated');
    }

    if ($request->input('tenant.firstname') && $request->input('tenant.name'))
    {
      $tenant = Tenant::where('firstname', $request->input('tenant.firstname'))
                      ->where('name', $request->input('tenant.name'))
                      ->get()->first();
      if (!$tenant)
      {
        $tenant = Tenant::create([
          'uuid' => \Str::uuid(),
          'firstname' => $request->input('tenant.firstname'),
          'name' => $request->input('tenant.name'),
          'email' => $request->input('tenant.email'),
          'phone' => $request->input('tenant.phone'),
        ]);
        $apartment->tenant_id = $tenant->id;
        $apartment->save();
      }
      else
      {
        $apartment->tenant_id = $tenant->id;
        $apartment->save();
      }
    }

    return response()->json('successfully updated');
  }

  /**
   * Reset all apartment data
   *
   * @param Apartment $apartment
   * @return \Illuminate\Http\Response
   */
  public function reset(Apartment $apartment)
  {
    $apartment->collectionItems()->delete();
    $apartment->tenant_id = NULL;
    $apartment->state_id = State::FREE;
    //$apartment->available_at = NULL;
    $apartment->save();
    return $this->find($apartment);
  }

  /**
   * Assign an apartement (temporarily) 
   *
   * @param Apartment $apartment   
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function assign(Apartment $apartment, Request $request)
  {
    $collectionItem = CollectionItem::with('collection')->where('uuid', $request->input('collectionItemUuid'))->first();
    $collectionItem->lock = 1;
    $collectionItem->save();

    // Create tenant and add to apartment
    $tenant = Tenant::create([
      'uuid' => \Str::uuid(),
      'firstname' => $collectionItem->collection->firstname,
      'name' => $collectionItem->collection->name,
      'email' => $collectionItem->collection->email,
    ]);
    $apartment->tenant_id = $tenant->id;
    $apartment->state_id = State::RESERVED;
    $apartment->save();

    return response()->json('successfully updated');
  }

  /**
   * Finalize an apartement
   *
   * @param Apartment $apartment   
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function finalize(Apartment $apartment, Request $request)
  {
    $apartment->state_id = State::RENTED;
    $apartment->save();
    
    // Clear collection & all items for the accepted tenant
    $collectionItem = CollectionItem::with('collection.items')->where('uuid', $request->input('collectionItemUuid'))->first();
    foreach($collectionItem->collection->items as $item)
    {
      $item->delete();
    }
    $collectionItem->collection->delete();

    // Clear all collection items for this apartment
    $collectionItems = CollectionItem::where('apartment_id', $apartment->id)->get();
    foreach($collectionItems as $item)
    {
      $item->delete();
    }

    return response()->json('successfully updated');
  }

}
