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

  public function show(Collection $collection, $hash = NULL)
  {
    $collection = $collection->valid()->with('estate')->findOrFail($collection->id);
    
    // Update collection item 'read_at' state if 
    // collection is viewed by the intended user
    if ($hash && $hash == md5($collection->email))
    {
      $this->update($collection);
    }
    return view($this->viewPath . 'collection', ['collection' => $collection]);
  }

  /**
   * Update a bunch of collection items
   * 
   * @param Collection $collection
   * @return \Illuminate\Http\Response
   */

  protected function update(Collection $collection)
  {
    $ids = $collection->items->whereNull('read_at')->pluck('id');
    if ($ids->all())
    {
      return CollectionItem::whereIn('id', $ids->all())->update(['read_at' => \Carbon\Carbon::now()]);
    }
    return;
  }
}
