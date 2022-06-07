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
    return view($this->viewPath . 'collection');
  }
}
