<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\ManagerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Account routes
    Route::apiResource('accounts', AccountController::class);
    Route::post('/accounts/{account}/deposit', [AccountController::class, 'deposit']);
    Route::post('/accounts/{account}/withdraw', [AccountController::class, 'withdraw']);
    Route::post('/accounts/transfer', [AccountController::class, 'transfer']);

    // Transaction routes
    Route::apiResource('transactions', TransactionController::class)->except(['store', 'update', 'destroy']);

    // Customer routes
    Route::get('/customer/profile', [CustomerController::class, 'profile']);
    Route::put('/customer/profile', [CustomerController::class, 'updateProfile']);

    // Admin routes
    Route::middleware(['role:admin'])->group(function () {
        // Dashboard
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);

        // Reports
        Route::get('/admin/reports/daily-transactions', [AdminController::class, 'dailyTransactionsReport']);
        Route::get('/admin/reports/account-summaries', [AdminController::class, 'accountSummariesReport']);
        Route::get('/admin/reports/audit-logs', [AdminController::class, 'auditLogsReport']);

        // Account management
        Route::get('/admin/accounts', [AdminController::class, 'listAllAccounts']);

        // Pending transactions listing
        Route::get('/admin/transactions/pending', [AdminController::class, 'pendingTransactions']);

        // Transactions approval/rejection
        Route::post('/admin/approve-transaction/{transaction}', [AdminController::class, 'approveTransaction']);
        Route::post('/admin/reject-transaction/{transaction}', [AdminController::class, 'rejectTransaction']);

        // Account state management (admin only)
        Route::post('/admin/accounts/{account}/state', [AdminController::class, 'changeAccountState']);
    });

    // Manager routes
    Route::middleware(['role:manager'])->group(function () {
        Route::get('/manager/dashboard', [AdminController::class, 'managerDashboard']);
        Route::get('/manager/transactions/pending', [ManagerController::class, 'pendingTransactions']);
        Route::post('/manager/approve-transaction/{transaction}', [ManagerController::class, 'approveTransaction']);
        Route::post('/manager/reject-transaction/{transaction}', [ManagerController::class, 'rejectTransaction']);
    });
});
