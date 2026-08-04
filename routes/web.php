<?php

use App\Enums\UserRole;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestCheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — PizzaFlow
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms-of-service', [PageController::class, 'terms'])->name('terms');

/*
|--------------------------------------------------------------------------
| Guest Auth Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/guest-checkout', [GuestCheckoutController::class, 'create'])->name('guest.create');
    Route::post('/guest-checkout', [GuestCheckoutController::class, 'store'])->name('guest.store');
});

Route::post('/guest-checkout/clear', [GuestCheckoutController::class, 'destroy'])
    ->name('guest.destroy');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', LogoutController::class)->name('logout');

    // Customer profile & addresses
    Route::middleware('role:'.UserRole::Customer->value.'|'.UserRole::Admin->value)->group(function () {
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::put('/profile/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.preferences');

        Route::post('/profile/addresses', [ProfileController::class, 'storeAddress'])->name('profile.addresses.store');
        Route::put('/profile/addresses/{address}', [ProfileController::class, 'updateAddress'])->name('profile.addresses.update');
        Route::delete('/profile/addresses/{address}', [ProfileController::class, 'destroyAddress'])->name('profile.addresses.destroy');
        Route::patch('/profile/addresses/{address}/default', [ProfileController::class, 'setDefaultAddress'])->name('profile.addresses.default');

        Route::post('/favorites/{slug}', [\App\Http\Controllers\FavoriteController::class, 'toggle'])->name('favorites.toggle');
        Route::delete('/favorites/{favorite}', [\App\Http\Controllers\FavoriteController::class, 'destroy'])->name('favorites.destroy');
    });

    // Role dashboards
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->middleware('role:'.UserRole::Admin->value)
        ->name('dashboard.admin');

    Route::get('/manager/dashboard', [DashboardController::class, 'manager'])
        ->middleware('role:'.UserRole::StoreManager->value)
        ->name('dashboard.manager');

    Route::get('/kitchen/dashboard', [DashboardController::class, 'kitchen'])
        ->middleware('role:'.UserRole::KitchenStaff->value)
        ->name('dashboard.kitchen');

    Route::get('/driver/dashboard', [DashboardController::class, 'driver'])
        ->middleware('role:'.UserRole::DeliveryDriver->value)
        ->name('dashboard.driver');

    // User & Profile Management (Admin + Store Manager)
    Route::middleware('role:'.UserRole::Admin->value.'|'.UserRole::StoreManager->value)->group(function () {
        Route::get('/dashboard/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/dashboard/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/dashboard/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/dashboard/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/dashboard/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::patch('/dashboard/users/{user}/toggle', [UserManagementController::class, 'toggleStatus'])->name('users.toggle');
        Route::delete('/dashboard/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });
});
