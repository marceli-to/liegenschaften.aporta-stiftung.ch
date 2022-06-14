<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Apartment;
use App\Http\Requests\CollectionStoreRequest;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
  /**
   * Get a list of collections
   * 
   * @return \Illuminate\Http\Response
   */
  public function get()
  { 
    return new DataCollection(Collection::with('estate', 'items.apartment.room', 'items.apartment.floor', 'items.apartment.building')->get());
  }

  /**
   * Get a single collection
   * 
   * @param Collection $collection
   * @return \Illuminate\Http\Response
   */
  public function find(Collection $collection)
  {
    return response()->json($collection);
  }

  /**
   * Store a newly created collection
   *
   * @param  \Illuminate\Http\CollectionStoreRequest $request
   * @return \Illuminate\Http\Response
   */
  public function store(CollectionStoreRequest $request)
  {
    foreach($request->input('candidates') as $candidate)
    {
      $collection = Collection::create([
        'uuid' => \Str::uuid(),
        'name' => $candidate['name'],
        'firstname' => $candidate['firstname'],
        'email' => $candidate['email'],
        'estate_id' => env('ESTATE_ID'),
      ]);
      $collection->save();

      foreach($request->input('items') as $item)
      {
        $apartment = Apartment::where('uuid', $item)->first();
        $collectionItem = CollectionItem::create([
          'uuid' => \Str::uuid(),
          'collection_id' => $collection->id,
          'apartment_id'  => $apartment->id
        ]);
        $collectionItem->save();
      }
    }
 
    return response()->json(['collectionId' => $collection->id]);
  }

  /**
   * Update a collection
   *
   * @param Collection $collection
   * @param  \Illuminate\Http\CollectionStoreRequest $request
   * @return \Illuminate\Http\Response
   */
  public function update(Collection $collection, CollectionStoreRequest $request)
  {
    return response()->json('successfully updated');
  }

}
