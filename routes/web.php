<?php

use App\Http\Controllers\GatewayController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});


Route::middleware(['auth', 'verified'])->group(function() {

    Route::get('/dashboard', [GatewayController::class, 'index'])->name('dashboard');
    Route::post('/gateway', [GatewayController::class, 'update'])->name('update-gateways');
    Route::get('/transactions/today', [TransactionController::class, 'getTodayTransactions']);
    Route::get('/transactions/weekly', [TransactionController::class, 'getWeeklyTransactions']);
    Route::get('/transactions/monthly', [TransactionController::class, 'getMonthlyTransactions']);
    Route::get('/transactions/yearly', [TransactionController::class, 'getYearlyTransactions']);
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
