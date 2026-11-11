<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Middleware\SanctumTokenFromQuery;
use Illuminate\Support\Facades\Route;

// Public Auth Endpoints
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected Corporate Analytics & Financial Routes
Route::middleware([SanctumTokenFromQuery::class])->group(function () {
    // Current User & Logout
    Route::prefix('auth')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // Dashboard Analytics
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats']);
    });

    // Exchange Rates
    Route::get('/currencies', [CurrencyController::class, 'index']);

    // PDF Export
    Route::get('/reports/pdf', [ReportController::class, 'pdf']);

    // Operations / Transactions
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/', [TransactionController::class, 'store']);
        Route::delete('/{id}', [TransactionController::class, 'destroy']);
        Route::get('/export-csv', [TransactionController::class, 'exportCsv']);
        Route::get('/csv-template', [TransactionController::class, 'csvTemplate']);
        Route::post('/import-csv', [TransactionController::class, 'importCsv']);
    });
});
