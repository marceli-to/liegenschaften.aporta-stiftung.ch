<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Support\CurrentEstate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EstateController extends Controller
{
  /**
   * Choose the estate the admin works on
   *
   * @param  \Illuminate\Http\Request $request
   * @param  CurrentEstate $estate
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, CurrentEstate $estate)
  {
    $request->validate([
      'key' => ['required', Rule::in($estate->all()->pluck('domain'))],
    ]);

    $estate->set($request->input('key'));
    return response()->json(['key' => $estate->key()]);
  }
}
