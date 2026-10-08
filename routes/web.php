<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BusinessProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home / Landing page (guest-friendly)
Route::get('/', function () {
    return view('home');
})->name('home');

/*
|--------------------------------------------------------------------------
| Demo (unauthenticated) invoice builder — used from landing page CTA.
| No DB write — guests fill the form, sign up first to save invoices.
|--------------------------------------------------------------------------
*/
Route::get('/invoice/create',  [InvoiceController::class, 'create'])->name('invoice.create');

/*
|--------------------------------------------------------------------------
| Authentication (laravel/ui bootstrap) — Login / Register / Password reset
|--------------------------------------------------------------------------
*/
Auth::routes();

/*
|--------------------------------------------------------------------------
| Authenticated-only application routes.
| All of these go through the "auth" middleware so guests can never reach
| client / invoice data. Queries inside the controllers are additionally
| scoped to the current user, and policies authorize record ownership.
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart', [DashboardController::class, 'chart'])->name('dashboard.chart');

    // Clients resource
    Route::resource('clients', ClientController::class)->except(['show']);

    // Products / Services resource
    Route::resource('products', ProductController::class)->except(['show']);

    // Settings: Business Profile (GET + PUT)
    Route::get('/settings/business-profile', [BusinessProfileController::class, 'edit'])
        ->name('settings.business-profile.edit');
    Route::put('/settings/business-profile', [BusinessProfileController::class, 'update'])
        ->name('settings.business-profile.update');

    // Invoices resource + extra actions
    Route::resource('invoices', InvoiceController::class);

    // Mark as paid (single status flip)
    Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])
        ->name('invoices.mark-paid');

    // Duplicate an invoice (copy with new number + today)
    Route::post('/invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])
        ->name('invoices.duplicate');

    // Stream PDF for a *persisted* invoice (uses DB data, scoped via policy)
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'download'])
        ->name('invoices.pdf');
});
