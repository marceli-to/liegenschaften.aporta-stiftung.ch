<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\MailQueue;
use Illuminate\Http\Request;

class UserCollectionController extends Controller
{
  /**
   * Get a collection
   * 
   * @param Collection $collection
   * @return \Illuminate\Http\Response
   */
  public function list(Collection $collection)
  {
    $collection = Collection::with('items.apartment.room', 'items.apartment.building', 'items.apartment.floor', 'estate')->findOrFail($collection->id);
    
    if (!$collection->valid())
    {
      return response()->json(['valid' => FALSE]);
    }

    // Map fields
    $data = [
      'uuid' => $collection->uuid,
      'valid' => $collection->valid(),
      'estate' => [
        'description' => $collection->estate->description_long . ', ' . $collection->estate->city,
        'maps' => $collection->estate->maps
      ],
      'items' => $collection->items->map(function($i) {
        return [
          'uuid' => $i->uuid,
          'number' => $i->apartment->number,
          'street' => $i->apartment->building->street,
          'city' => $i->collection->estate->city,
          'description' => $i->apartment->description,
          'tenant' => $i->apartment->tenant ? $i->apartment->tenant->full_name : '',
          'rooms' => $i->apartment->room->abbreviation,
          'room_description' => $i->apartment->room->description,
          'rent_gross' => $i->apartment->rent_gross,
          'size' => $i->apartment->size,
          'size_terrace' => $i->apartment->size_terrace,
          'size_patio' => $i->apartment->size_patio,
          'size_balcony' => $i->apartment->size_balcony,
          'available_at' => $i->apartment->available_at ? date('d.m.Y', strtotime($i->apartment->available_at)) : '–'
        ];
      })
    ];
    return response()->json($data);
  }

  /**
   * Get a list of collection items
   * 
   * @param Collection $collection
   * @param String $collectionItemUuid
   * @return \Illuminate\Http\Response
   */

  public function show(Collection $collection, $collectionItemUuid = NULL)
  {
    $item = CollectionItem::with('apartment.room', 'apartment.building', 'apartment.floor', 'collection.estate')->where('uuid', $collectionItemUuid)->firstOrFail();

    if (!$item->collection->valid())
    {
      return response()->json(['valid' => FALSE]);
    }

    $data = [
      'uuid' => $item->uuid,
      'estate' => $item->collection->estate->description_long . ', ' . $item->collection->estate->city,
      'city' => $item->collection->estate->city,
      'apartementUuid' => $item->apartment->uuid,
      'number' => $item->apartment->number,
      'street' => $item->apartment->building->street,
      'description' => $item->apartment->description,
      'tenant' => $item->apartment->tenant ? $item->apartment->tenant->full_name : '',
      'rooms' => $item->apartment->room->description,
      'room_description' => $item->apartment->room->description,
      'rent_net' => $item->apartment->rent_net,
      'additional_cost' => $item->apartment->additional_cost,
      'rent_gross' => $item->apartment->rent_gross,
      'size' => $item->apartment->size,
      'size_terrace' => $item->apartment->size_terrace,
      'size_patio' => $item->apartment->size_patio,
      'size_balcony' => $item->apartment->size_balcony,
      'shared_exterior' => $item->apartment->shared_exterior,
      'accepted' => $item->accepted,
      'parking' => $item->parking,
      'has_reply' => $item->replied_at == NULL ? FALSE : TRUE,
      'available_at' => $item->apartment->available_at ? date('d.m.Y', strtotime($item->apartment->available_at)) : '–'
    ];

    return response()->json(['valid' => $item->collection->valid(), 'item' => $data, 'pagination' => $this->getPagination($collection, $collectionItemUuid)]);
  }

  /**
   * Handle a reply on an collection item
   * 
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function reply(Request $request)
  {
    $item = CollectionItem::where('uuid', $request->input('uuid'))->firstOrFail();
    $item->update([
      'comment' => $request->input('comment'),
      'accepted' => $request->input('accepted'),
      'parking' => $request->input('parking'),
      'replied_at'  => \Carbon\Carbon::now()
    ]);

    MailQueue::create([
      'type' => 'reply',
      'data' => CollectionItem::with('collection.estate', 'apartment.building', 'apartment.room')->find($item->id)->toJson()
    ]);

    MailQueue::create([
      'type' => 'confirmation',
      'data' => CollectionItem::with('collection.estate', 'apartment.building', 'apartment.room')->find($item->id)->toJson()
    ]);

    return response()->json('success');
  }

  /**
   * Get the pagination for a set of collectionItems
   * 
   * @param Collection $collection
   * @param String $collectionItemUuid
   * @return \Illuminate\Http\Response
   */

  protected function getPagination(Collection $collection, $collectionItemUuid = NULL)
  { 
    $current = CollectionItem::where('uuid', $collectionItemUuid)->first();
    $items   = CollectionItem::where('collection_id', $collection->id)->get();
    $keys    = [];

    foreach($items as $i)
    {
      $keys[] = (int) $i->id;
    }

    $key = array_search($current->id, $keys);

    if ($key == 0)
    {
      $prevId = end($keys);
      $nextId = isset($keys[$key+1]) ? $keys[$key+1] : NULL;
    }
    else if ($key == count($keys) - 1)
    {
      $prevId = $keys[$key-1];
      $nextId = $keys[0];
    }
    else
    {
      $prevId = $keys[$key-1];
      $nextId = $keys[$key+1];
    }

    $prev = CollectionItem::find($prevId);
    $next = CollectionItem::find($nextId);

    $browse = [
      'index' => $key + 1,
      'count' => $items->count(),
      //'current' => $current->apartment->uuid,
      'prev'  => ($prev) ? $prev->uuid : NULL,
      'next'  => ($next) ? $next->uuid : NULL,
    ];
  
    return $browse;
  }
  
}
