<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\Estate;
use App\Models\Building;
use App\Models\Apartment;
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
    $apartment = Apartment::with('building', 'floor', 'room', 'tenant', 'estate')->findOrFail($apartment->id);
    return response()->json($apartment);
  }

  /**
   * Update a apartment
   *
   * @param Apartment $apartment
   * @param  \Illuminate\Http\ApartmentStoreRequest $request
   * @return \Illuminate\Http\Response
   */
  public function update(Apartment $apartment, ApartmentStoreRequest $request)
  {
    $apartment->update($request->all());
    $apartment->save();
    return response()->json('successfully updated');
  }

}
