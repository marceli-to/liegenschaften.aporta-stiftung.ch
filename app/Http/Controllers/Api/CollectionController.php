<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Http\Requests\CollectionStoreRequest;
use App\Models\Apartment;
use App\Models\MailQueue;
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
        'salutation' => $candidate['salutation'],
        'name' => $candidate['name'],
        'firstname' => $candidate['firstname'],
        'email' => $candidate['email'],
        'valid_until' => \Carbon\Carbon::now()->addDays(5),
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

      // Add collection data to mail_queue
      MailQueue::create([
        'type' => 'offer',
        'data' => Collection::with('estate', 'items.apartment.room', 'items.apartment.floor', 'items.apartment.building')->find($collection->id)->toJson()
      ]);
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
