<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\CollectionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
*/

// Auth
Route::middleware('guest')->group(function() {
  Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
  Route::post('/login', [AuthController::class, 'login']);
  Route::get('/password/reset', [AuthController::class, 'showForgotPassword'])->name('password.request');
  Route::post('/password/email', [AuthController::class, 'sendResetLink'])->middleware('throttle:6,1')->name('password.email');
  Route::get('/password/reset/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
  Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Frontend Routes
Route::get('/angebot/{collection:uuid}/detail/{collectionItem:uuid}', [CollectionController::class, 'show'])->name('offer.show');
Route::get('/angebot/{collection:uuid}/{hash?}', [CollectionController::class, 'show'])->name('offer.list');

// Admin, on the admin domain only
Route::domain(config('client.admin_domain'))->group(function() {
  Route::get('/', [PageController::class, 'index'])->name('home');

  // Logged in users
  Route::middleware('auth:sanctum')->group(function() {
    Route::get('/export/objekte', [DownloadController::class, 'exportApartments'])->name('export_apartments');
    Route::get('/export/mieter', [DownloadController::class, 'exportTenants'])->name('export_tenants');
    Route::get('/administration/{any?}', function () {
      return view('layout.authenticated');
    })->where('any', '.*')->middleware('role:admin,editor')->name('applications');
  });
});
