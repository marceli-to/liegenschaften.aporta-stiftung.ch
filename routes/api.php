<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ApartmentController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\UploadController;

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
  Route::get('apartment/{apartment}', [ApartmentController::class, 'find']);
  Route::put('apartment/update/{apartment}', [ApartmentController::class, 'update']);

  // Settings
  Route::get('settings/buildings', [SettingsController::class, 'buildings']);
  Route::get('settings/rooms', [SettingsController::class, 'rooms']);
  Route::get('settings/floors', [SettingsController::class, 'floors']);
  Route::get('settings/exteriors', [SettingsController::class, 'exteriors']);
  Route::get('settings/states', [SettingsController::class, 'states']);

});