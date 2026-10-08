<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\CollectionItem;
use App\Support\CurrentEstate;
use Illuminate\Http\Request;

class CollectionItemController extends Controller
{

  /**
   * Get a list of collection items of the current estate
   * 
   * @param CurrentEstate $estate
   * @return \Illuminate\Http\Response
   */
  public function get(CurrentEstate $estate)
  { 
    return new DataCollection(CollectionItem::with('collection.estate', 'apartment.room', 'apartment.floor', 'apartment.building', 'apartment.estate')->whereRelation('collection', 'estate_id', $estate->id())->where('archive', 0)->orderBy('created_at', 'DESC')->get());
  }

  /**
   * Get a single collection item
   * 
   * @param CollectionItem $collectionItem
   * @return \Illuminate\Http\Response
   */
  public function find(CollectionItem $collectionItem)
  {
    return response()->json($collectionItem);
  }

  /**
   * Destroy a collection item
   *  
   * @param CollectionItem $collectionItem
   * @return \Illuminate\Http\Response
   */
  public function destroy(CollectionItem $collectionItem)
  {
    CollectionItem::findOrFail($collectionItem->id)->delete();
    return response()->json('successfully deleted');
  }

}
