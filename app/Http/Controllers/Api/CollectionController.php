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

    /** temp send */
    $collectionList = \App\Models\Collection::with('estate', 'items.apartment.room', 'items.apartment.floor', 'items.apartment.building')->where('processed', 0)->get();
    $collectionList = collect($collectionList)->splice(0, 1);
    foreach($collectionList->all() as $c)
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
