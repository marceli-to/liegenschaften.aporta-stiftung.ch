<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\Http\Requests\UploadRequest;
use App\Services\Media;
use App\Http\Controllers\Controller;

class UploadController extends Controller
{
  /**
   * Upload a file
   * 
   * @param  UploadRequest $request
   * @return \Illuminate\Http\Response
   */

  public function store(UploadRequest $request)
  { 
    $media = (new Media(['force_lowercase' => false]))->store($request);
    return response()->json($media);
  }

  /**
   * Delete a file
   * 
   * @param  String $filename
   * @return \Illuminate\Http\Response
   */

  public function destroy($filename)
  { 
    $media = (new Media())->remove($filename, TRUE);
    return response()->json($media);
  }
}
