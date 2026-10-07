<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;

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
| Demo (unauthenticated) invoice builder → PDF.
|   Used from the landing page "Get started free" CTA.
|   No DB write — just validate and stream PDF.
|--------------------------------------------------------------------------
*/
Route::get('/invoice/create',  [InvoiceController::class, 'create'])      ->name('invoice.create');
Route::post('/invoice/download', [InvoiceController::class, 'downloadLegacy'])->name('invoice.download');

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

    // Clients resource
    Route::resource('clients', ClientController::class)->except(['show']);

    // Invoices resource + extra actions
    Route::resource('invoices', InvoiceController::class);

    // Mark as paid (single status flip)
    Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markPaid'])
        ->name('invoices.mark-paid');

    // Stream PDF for a *persisted* invoice (uses DB data, scoped via policy)
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])
        ->name('invoices.download');
});
