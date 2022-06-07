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
    return new DataCollection(CollectionItem::with('collection.estate', 'apartment.room', 'apartment.floor', 'apartment.building')->orderBy('created_at', 'DESC')->get());
  }

  /**
   * Get a single collectionItem
   * 
   * @param CollectionItem $collectionItem
   * @return \Illuminate\Http\Response
   */
  public function find(CollectionItem $collectionItem)
  {
    return response()->json($collectionItem);
  }
}
