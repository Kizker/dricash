<?php

use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DailyBudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GrowthTargetController;
use App\Http\Controllers\MonthlyObligationController;
use App\Http\Controllers\QuickEntryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public & Guest Routes
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // Dashboard & Daily Budget
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/daily-budget/settings', [DailyBudgetController::class, 'updateSettings'])->name('daily-budget.settings');

    // Quick Entry & Intervention
    Route::post('/quick-entry', [QuickEntryController::class, 'store'])->name('quick-entry.store');
    Route::post('/quick-entry/check', [QuickEntryController::class, 'checkIntervention'])->name('quick-entry.check');

    // Monthly Obligations (Fixed Expenses & Ring-Fencing)
    Route::get('/obligations', [MonthlyObligationController::class, 'index'])->name('obligations.index');
    Route::post('/obligations', [MonthlyObligationController::class, 'store'])->name('obligations.store');
    Route::put('/obligations/{obligation}', [MonthlyObligationController::class, 'update'])->name('obligations.update');
    Route::delete('/obligations/{obligation}', [MonthlyObligationController::class, 'destroy'])->name('obligations.destroy');
    Route::post('/obligations/{obligation}/toggle', [MonthlyObligationController::class, 'togglePayment'])->name('obligations.toggle');

    // Growth Tracker & Wealth Goals
    Route::get('/growth', [GrowthTargetController::class, 'index'])->name('growth.index');
    Route::post('/growth', [GrowthTargetController::class, 'update'])->name('growth.update');

    // Transactions Ledger
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});

// Admin CMS & Platform Management Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard & Global Analytics
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::put('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.update-role');
    Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Master Default Categories
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
});

