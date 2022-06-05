<?php
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;

use App\Http\Controllers\TestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
*/

// Auth routes
Auth::routes(['verify' => true, 'register' => false]);
Route::get('/logout', 'Auth\LoginController@logout');
Route::get('/', [PageController::class, 'index'])->name('home');

// Routes for testing
Route::get('/notify', [TestController::class, 'notify']);
Route::get('/tenants', [TestController::class, 'tenants']);
Route::get('/tenant/{tenant}', [TestController::class, 'tenant']);
Route::get('/apartments', [TestController::class, 'apartments']);
Route::get('/apartment/{apartment}', [TestController::class, 'apartment']);
Route::get('/buildings/{estate}', [TestController::class, 'buildings']);
Route::get('/building/{building}', [TestController::class, 'building']);
Route::get('/estates', [TestController::class, 'estates']);
Route::get('/estate/{estate}', [TestController::class, 'estate']);
Route::get('/floors/{estate}', [TestController::class, 'floors']);
Route::get('/rooms/{estate}', [TestController::class, 'rooms']);
Route::get('/buildings/{estate}', [TestController::class, 'buildings']);

// Logged in users
Route::middleware('auth:sanctum', 'verified')->group(function() {
  Route::get('/administration/{any?}', function () {
    return view('layout.authenticated');
  })->where('any', '.*')->middleware('role:admin')->name('applications');
});


