<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\CollectionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
*/

// Auth routes
Auth::routes(['verify' => true, 'register' => false]);
Route::get('/logout', [LoginController::class, 'logout']);

// Frontend Routes
Route::get('/angebot/{collection:uuid}/detail/{collectionItem:uuid}', [CollectionController::class, 'show'])->name('offer.show');
Route::get('/angebot/{collection:uuid}/{hash?}', [CollectionController::class, 'show'])->name('offer.list');

// Admin, on the admin domain only
Route::domain(config('client.admin_domain'))->group(function() {
  Route::get('/', [PageController::class, 'index'])->name('home');

  // Logged in users
  Route::middleware('auth:sanctum', 'verified')->group(function() {
    Route::get('/export/objekte', [DownloadController::class, 'exportApartments'])->name('export_apartments');
    Route::get('/export/mieter', [DownloadController::class, 'exportTenants'])->name('export_tenants');
    Route::get('/administration/{any?}', function () {
      return view('layout.authenticated');
    })->where('any', '.*')->middleware('role:admin')->name('applications');
  });
});
