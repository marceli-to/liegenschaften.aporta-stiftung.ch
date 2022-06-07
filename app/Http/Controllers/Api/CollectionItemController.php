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
    return new DataCollection(CollectionItem::with('collection.estate', 'apartment.room', 'apartment.floor', 'apartment.building')->where('archive', 0)->orderBy('created_at', 'DESC')->get());
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
   * Destroy collection items
   *  
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function destroy(Request $request)
  {
    if ($request->input('items'))
    {
      foreach($request->input('items') as $item)
      {
        $collectionItem = CollectionItem::where('uuid', $item)->first();
        if ($collectionItem)
        {
          $collectionItem->delete();
        }
      }
    }
    return response()->json('successfully deleted');
  }

  /**
   * Archive collection items
   *  
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function archive(Request $request)
  {
    if ($request->input('items'))
    {
      foreach($request->input('items') as $item)
      {
        $collectionItem = CollectionItem::where('uuid', $item)->first();
        if ($collectionItem)
        {
          $collectionItem->archive = 1;
          $collectionItem->save();
        }
      }
    }
    return response()->json('successfully deleted');
  }
}
