<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ApartmentController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\CollectionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
  return $request->user();
});

Route::middleware('auth:sanctum')->group(function() {
  Route::get('user', [UserController::class, 'find']);

  // Apartments
  Route::post('apartments/filter', [ApartmentController::class, 'filter']);
  Route::get('apartments', [ApartmentController::class, 'get']);
  Route::post('apartments', [ApartmentController::class, 'fetch']);
  Route::put('apartment/{apartment:uuid}', [ApartmentController::class, 'update']);
  Route::get('apartment/{apartment:uuid}', [ApartmentController::class, 'find']);

  // Settings
  Route::get('settings/buildings', [SettingsController::class, 'buildings']);
  Route::get('settings/rooms', [SettingsController::class, 'rooms']);
  Route::get('settings/floors', [SettingsController::class, 'floors']);
  Route::get('settings/exteriors', [SettingsController::class, 'exteriors']);
  Route::get('settings/states', [SettingsController::class, 'states']);

  // Collections
  Route::get('collections', [CollectionController::class, 'get']);
  Route::get('collection/items', [CollectionController::class, 'getItems']);
  Route::post('collection', [CollectionController::class, 'store']);


});