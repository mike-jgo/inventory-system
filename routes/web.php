<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/', function () {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('login');
    });
    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'index'])->name('login');
        Route::post('/login', [LoginController::class, 'attempt'])->name('login.attempt');
        Route::get('/register', [RegisterController::class, 'index'])->name('register');
        Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    });
    Route::middleware('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/items', [ItemController::class, 'index'])->name('items.index');
        Route::post('/items', [ItemController::class, 'store'])->name('items.store');
        Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
        Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
        Route::middleware(['can:view users'])->group(function () {
            Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
            Route::post('/users', [\App\Http\Controllers\UserController::class, 'store'])->name('users.store');
            Route::put('/users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
            Route::delete('/users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
        });
        Route::get('/activity-log', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-log.index');
        Route::post('/activities/{id}/remarks', [\App\Http\Controllers\ActivityLogController::class, 'saveRemarks'])
            ->name('activities.remarks');



        Route::resource('categories', CategoryController::class)
            ->except(['show', 'create', 'edit']);

        // Inventory Management (Superadmin only - authorization checked in controller)
        Route::resource('inventory', InventoryController::class)
            ->except(['show', 'create', 'edit']);

        Route::put('/orders/{order}/complete', [\App\Http\Controllers\OrderController::class, 'complete'])->name('orders.complete');
        Route::resource('orders', \App\Http\Controllers\OrderController::class);
    });
});
