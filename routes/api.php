<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ApartmentController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\CollectionItemController;
use App\Http\Controllers\Api\UserCollectionController;

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

Route::get('/user-collection/{collection:uuid}/item/{collectionItem:uuid}', [UserCollectionController::class, 'show']);
Route::get('/user-collection/{collection:uuid}', [UserCollectionController::class, 'list']);
Route::post('/user-collection', [UserCollectionController::class, 'reply']);

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
  Route::put('apartment/assign/{apartment:uuid}', [ApartmentController::class, 'assign']);
  Route::put('apartment/finalize/{apartment:uuid}', [ApartmentController::class, 'finalize']);
  Route::delete('apartment/{apartment:uuid}', [ApartmentController::class, 'reset']);

  // Settings
  Route::get('settings/buildings', [SettingsController::class, 'buildings']);
  Route::get('settings/rooms', [SettingsController::class, 'rooms']);
  Route::get('settings/floors', [SettingsController::class, 'floors']);
  Route::get('settings/exteriors', [SettingsController::class, 'exteriors']);
  Route::get('settings/rent', [SettingsController::class, 'rent']);
  Route::get('settings/states', [SettingsController::class, 'states']);

  // Collection items
  Route::get('collection-items', [CollectionItemController::class, 'get']);
  Route::get('collection-items/{item}', [CollectionItemController::class, 'find']);
  Route::delete('collection-item/{collectionItem:uuid}', [CollectionItemController::class, 'destroy']);

  // Collections
  Route::get('collections', [CollectionController::class, 'get']);
  Route::get('collection/{collection}', [CollectionController::class, 'find']);
  Route::post('collection', [CollectionController::class, 'store']);

});