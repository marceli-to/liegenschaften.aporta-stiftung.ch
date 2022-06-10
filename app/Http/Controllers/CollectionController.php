<?php
namespace App\Http\Controllers;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class CollectionController extends BaseController
{
  protected $viewPath = 'pages.';

  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Show the collection
   *
   * @param Collection $collection
   * @return \Illuminate\Http\Response
   */

  public function show(Collection $collection)
  {
    $collection = Collection::with('items.apartment.room', 'items.apartment.building', 'items.apartment.floor', 'estate')
                            ->findOrFail($collection->id);
    
    // dd($collection);
    return view($this->viewPath . 'collection', ['collection' => $collection]);
  }
}
