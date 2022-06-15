<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\DataCollection;
use App\Models\CollectionItem;
use Illuminate\Http\Request;

class CollectionItemController extends Controller
{

  /**
   * Get a list of collection items
   * 
   * @return \Illuminate\Http\Response
   */
  public function get()
  { 
    return new DataCollection(CollectionItem::with('collection.estate', 'apartment.room', 'apartment.floor', 'apartment.building', 'apartment.estate')->where('archive', 0)->orderBy('created_at', 'DESC')->get());
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
