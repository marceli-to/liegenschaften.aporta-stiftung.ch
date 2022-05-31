<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\Estate;
use App\Models\Apartment;
use App\Http\Requests\ApartmentStoreRequest;
use Illuminate\Http\Request;

class ApartmentController extends Controller
{
  /**
   * Get a list of apartments
   * 
   * @return \Illuminate\Http\Response
   */
  public function get()
  { 
    return response()->json(Estate::with('buildings.apartments', 'floors', 'rooms')->findOrFail(env('ESTATE_ID')));
  }


  /**
   * Get a filtered ist of apartments
   * 
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function filter(Request $request)
  { 
  }

  /**
   * Get a single apartment
   * 
   * @param Apartment $apartment
   * @return \Illuminate\Http\Response
   */
  public function find(Apartment $apartment)
  {
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
