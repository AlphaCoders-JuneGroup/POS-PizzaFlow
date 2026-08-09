<?php

use App\Enums\UserRole;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryDispatchController;
use App\Http\Controllers\GuestCheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryManagementController;
use App\Http\Controllers\KitchenDisplayController;
use App\Http\Controllers\OrderManagementController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromoCodeController;
use App\Http\Controllers\PromotionManagementController;
use App\Http\Controllers\StaffNotificationController;
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
Route::post('/promotions/apply', [PromoCodeController::class, 'apply'])->name('promotions.apply');

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
});

// Guest ordering (no login required)
Route::get('/guest-checkout', [GuestCheckoutController::class, 'create'])->name('guest.create');
Route::post('/guest-checkout', [GuestCheckoutController::class, 'store'])->name('guest.store');
Route::post('/guest-checkout/order', [GuestCheckoutController::class, 'placeOrder'])->name('guest.order');
Route::post('/guest-checkout/clear', [GuestCheckoutController::class, 'destroy'])->name('guest.destroy');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', LogoutController::class)->name('logout');

    // Staff notifications (dashboard bell)
    Route::post('/dashboard/notifications/read-all', [StaffNotificationController::class, 'markAllRead'])
        ->name('notifications.read-all');
    Route::post('/dashboard/notifications/{notification}/read', [StaffNotificationController::class, 'markRead'])
        ->name('notifications.read');

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
        Route::post('/orders', [\App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');
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

        // Menu & Customization Engine
        Route::get('/dashboard/menu', [\App\Http\Controllers\MenuManagementController::class, 'index'])->name('admin.menu.index');
        Route::post('/dashboard/menu/categories', [\App\Http\Controllers\MenuManagementController::class, 'storeCategory'])->name('admin.menu.categories.store');
        Route::put('/dashboard/menu/categories/{category}', [\App\Http\Controllers\MenuManagementController::class, 'updateCategory'])->name('admin.menu.categories.update');
        Route::delete('/dashboard/menu/categories/{category}', [\App\Http\Controllers\MenuManagementController::class, 'destroyCategory'])->name('admin.menu.categories.destroy');

        Route::post('/dashboard/menu/items', [\App\Http\Controllers\MenuManagementController::class, 'storeMenuItem'])->name('admin.menu.items.store');
        Route::put('/dashboard/menu/items/{item}', [\App\Http\Controllers\MenuManagementController::class, 'updateMenuItem'])->name('admin.menu.items.update');
        Route::delete('/dashboard/menu/items/{item}', [\App\Http\Controllers\MenuManagementController::class, 'destroyMenuItem'])->name('admin.menu.items.destroy');

        Route::post('/dashboard/menu/options/{type}', [\App\Http\Controllers\MenuManagementController::class, 'storeOption'])->name('admin.menu.options.store');
        Route::put('/dashboard/menu/options/{type}/{id}', [\App\Http\Controllers\MenuManagementController::class, 'updateOption'])->name('admin.menu.options.update');
        Route::delete('/dashboard/menu/options/{type}/{id}', [\App\Http\Controllers\MenuManagementController::class, 'destroyOption'])->name('admin.menu.options.destroy');

        // Inventory & Item Control
        Route::get('/dashboard/inventory', [InventoryManagementController::class, 'index'])->name('admin.inventory.index');
        Route::post('/dashboard/inventory', [InventoryManagementController::class, 'store'])->name('admin.inventory.store');
        Route::put('/dashboard/inventory/{ingredient}', [InventoryManagementController::class, 'update'])->name('admin.inventory.update');
        Route::delete('/dashboard/inventory/{ingredient}', [InventoryManagementController::class, 'destroy'])->name('admin.inventory.destroy');
        Route::patch('/dashboard/inventory/{ingredient}/toggle', [InventoryManagementController::class, 'toggleStock'])->name('admin.inventory.toggle');
        Route::post('/dashboard/inventory/{ingredient}/restock', [InventoryManagementController::class, 'restock'])->name('admin.inventory.restock');
        Route::post('/dashboard/inventory/{ingredient}/adjust', [InventoryManagementController::class, 'adjust'])->name('admin.inventory.adjust');

        // Promotions & Offers
        Route::get('/dashboard/promotions', [PromotionManagementController::class, 'index'])->name('promotions.index');
        Route::post('/dashboard/promotions', [PromotionManagementController::class, 'store'])->name('promotions.store');
        Route::put('/dashboard/promotions/{promotion}', [PromotionManagementController::class, 'update'])->name('promotions.update');
        Route::patch('/dashboard/promotions/{promotion}/toggle', [PromotionManagementController::class, 'toggle'])->name('promotions.toggle');
        Route::delete('/dashboard/promotions/{promotion}', [PromotionManagementController::class, 'destroy'])->name('promotions.destroy');
    });

    // Order Management (Admin, Store Manager, Kitchen, Driver)
    Route::middleware('role:'.UserRole::Admin->value.'|'.UserRole::StoreManager->value.'|'.UserRole::KitchenStaff->value.'|'.UserRole::DeliveryDriver->value)
        ->prefix('dashboard/orders')
        ->name('orders.manage.')
        ->group(function () {
            Route::get('/', [OrderManagementController::class, 'index'])->name('index');
            Route::get('/poll', [OrderManagementController::class, 'poll'])->name('poll');
            Route::get('/{order}', [OrderManagementController::class, 'show'])->name('show');
            Route::post('/{order}/advance', [OrderManagementController::class, 'advance'])->name('advance');

            Route::middleware('role:'.UserRole::Admin->value.'|'.UserRole::StoreManager->value)->group(function () {
                Route::get('/{order}/edit', [OrderManagementController::class, 'edit'])->name('edit');
                Route::put('/{order}', [OrderManagementController::class, 'update'])->name('update');
                Route::post('/{order}/cancel', [OrderManagementController::class, 'cancel'])->name('cancel');
            });
        });

    // Kitchen Display System (Admin, Store Manager, Kitchen Staff)
    Route::middleware('role:'.UserRole::Admin->value.'|'.UserRole::StoreManager->value.'|'.UserRole::KitchenStaff->value)
        ->prefix('dashboard/kds')
        ->name('kds.')
        ->group(function () {
            Route::get('/', [KitchenDisplayController::class, 'index'])->name('index');
            Route::get('/poll', [KitchenDisplayController::class, 'poll'])->name('poll');
            Route::post('/{order}/start', [KitchenDisplayController::class, 'start'])->name('start');
            Route::post('/{order}/baking', [KitchenDisplayController::class, 'baking'])->name('baking');
            Route::post('/{order}/complete', [KitchenDisplayController::class, 'complete'])->name('complete');
            Route::post('/{order}/item', [KitchenDisplayController::class, 'itemStatus'])->name('item');
        });

    // Delivery & Dispatch (Admin, Store Manager, Delivery Driver)
    Route::middleware('role:'.UserRole::Admin->value.'|'.UserRole::StoreManager->value.'|'.UserRole::DeliveryDriver->value)
        ->prefix('dashboard/delivery')
        ->name('delivery.')
        ->group(function () {
            Route::get('/', [DeliveryDispatchController::class, 'index'])->name('index');
            Route::get('/{order}', [DeliveryDispatchController::class, 'show'])->name('show');
            Route::put('/{order}/instructions', [DeliveryDispatchController::class, 'updateInstructions'])->name('instructions');
            Route::post('/{order}/start', [DeliveryDispatchController::class, 'start'])->name('start');
            Route::post('/{order}/complete', [DeliveryDispatchController::class, 'complete'])->name('complete');

            Route::middleware('role:'.UserRole::Admin->value.'|'.UserRole::StoreManager->value)->group(function () {
                Route::post('/drivers', [DeliveryDispatchController::class, 'storeDriver'])->name('drivers.store');
                Route::post('/{order}/assign', [DeliveryDispatchController::class, 'assign'])->name('assign');
                Route::post('/{order}/auto-assign', [DeliveryDispatchController::class, 'autoAssign'])->name('auto-assign');
                Route::post('/{order}/unassign', [DeliveryDispatchController::class, 'unassign'])->name('unassign');
            });
        });
});
