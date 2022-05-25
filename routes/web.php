<?php
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;

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

// Logged in users
Route::middleware('auth:sanctum', 'verified')->group(function() {

  Route::get('/{any?}', function () {
    return view('layout.authenticated');
  })->where('any', '.*')->name('applications');
});


