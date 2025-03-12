<?php
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\CollectionController;
// use App\Http\Controllers\TestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
*/

// Auth routes
Auth::routes(['verify' => true, 'register' => false]);
Route::get('/logout', 'Auth\LoginController@logout');

// Frontend Routes
Route::get('/angebot/{collection:uuid}/detail/{collectionItem:uuid}', [CollectionController::class, 'show'])->name('offer.show');
Route::get('/angebot/{collection:uuid}/{hash?}', [CollectionController::class, 'show'])->name('offer.list');

if (
  \App::domain() == 'liegenschaften.aporta-stiftung.ch.test' || 
  \App::domain() == 'liegenschaften.aporta-stiftung.ch.marceli.to' || 
  \App::domain() == 'liegenschaften.aporta-stiftung.ch' ||
  \App::domain() == 'liegenschaften.aporta-stiftung.ch.wbg.ch'
)
{
  Route::get('/', [PageController::class, 'index'])->name('home');

  // Logged in users
  Route::middleware('auth:sanctum', 'verified')->group(function() {
    Route::get('/export/objekte', [DownloadController::class, 'exportApartments'])->name('export_apartments');
    Route::get('/export/mieter', [DownloadController::class, 'exportTenants'])->name('export_tenants');
    Route::get('/administration/{any?}', function () {
      return view('layout.authenticated');
    })->where('any', '.*')->middleware('role:admin')->name('applications');
  }); 
}