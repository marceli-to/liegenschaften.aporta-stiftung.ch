<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\CollectionItem;
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

    // Map fields
    $data = [
      'uuid' => $collection->uuid,
      'estate' => $collection->estate->description_long . ', ' . $collection->estate->city,
      'items' => $collection->items->map(function($i) {
        return [
          'uuid' => $i->uuid,
          'number' => $i->apartment->number,
          'street' => $i->apartment->building->street,
          'description' => $i->apartment->description,
          'tenant' => $i->apartment->tenant ? $i->apartment->tenant->full_name : '',
          'rooms' => $i->apartment->room->abbreviation,
          'size' => $i->apartment->size,
          'size_terrace' => $i->apartment->size_terrace,
          'size_patio' => $i->apartment->size_patio,
          'size_balcony' => $i->apartment->size_balcony,
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
    $data = [
      'uuid' => $item->uuid,
      'estate' => $item->collection->estate->description_long . ', ' . $item->collection->estate->city,
      'city' => $item->collection->estate->city,
      'number' => $item->apartment->number,
      'street' => $item->apartment->building->street,
      'description' => $item->apartment->description,
      'tenant' => $item->apartment->tenant ? $item->apartment->tenant->full_name : '',
      'rooms' => $item->apartment->room->description,
      'size' => $item->apartment->size,
      'size_terrace' => $item->apartment->size_terrace,
      'size_patio' => $item->apartment->size_patio,
      'size_balcony' => $item->apartment->size_balcony,
    ];

    return response()->json(['item' => $data, 'pagination' => $this->getPagination($collection, $collectionItemUuid)]);
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
      'prev'  => ($prev) ? $prev->uuid : NULL,
      'next'  => ($next) ? $next->uuid : NULL,
    ];
  
    return $browse;
  }
}
