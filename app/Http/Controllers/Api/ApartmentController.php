<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\Estate;
use App\Models\Building;
use App\Models\Apartment;
use App\Models\Tenant;
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
    $data = Apartment::with('building', 'floor', 'room', 'tenant')->orderBy('order')->where('estate_id', env('ESTATE_ID'))->get();
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
    // Build search query
    $matches = [];

    // Add ids of 'building, floor, room etc.'
    foreach($request->except('exterior') as $key => $value)
    {
      if ($request->input($key))
      {
        $matches[$key] = $value;
      }
    }

    // Filter
    $data = Apartment::with('building', 'floor', 'room', 'tenant')->where('estate_id', env('ESTATE_ID'))->where($matches)->orderBy('order')->get();

    // Handle exterior
    if ($request->input('exterior'))
    {
      $filtered = $data->where('size_' . $request->input('exterior'), '>', 0);      
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

}
