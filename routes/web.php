<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;

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

// Home / Landing page
Route::get('/', function () {
    return view('home');
})->name('home');

// Login placeholder
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Register placeholder
Route::get('/register', function () {
    return view('auth.register');
})->name('register');

/*
|--------------------------------------------------------------------------
| Invoice feature (no auth / no DB in this version)
|--------------------------------------------------------------------------
*/
Route::get('/invoice/create', [InvoiceController::class, 'create'])->name('invoice.create');
Route::post('/invoice/download', [InvoiceController::class, 'download'])->name('invoice.download');
