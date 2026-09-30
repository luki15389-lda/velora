<?php

use App\Http\Controllers\Admin\Auth\AdminAuthenticatedSessionController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Back office
|--------------------------------------------------------------------------
| Mounted at /admin with the "admin." name prefix. "backoffice" is admin-only
| — there is no staff role in this build, by design (see README).
|
| Admin sign-in is its own page, separate from the customer /login — see
| AdminLoginRequest, which refuses a valid customer password here just as
| the customer LoginRequest refuses a valid admin password on its side.
|
| "auth" and "guest" below are pinned to the "admin" guard explicitly
| (auth:admin / guest:admin) rather than left bare. Laravel's middleware
| priority list always runs Authenticate before SubstituteBindings, which
| drags it ahead of UseAdminGuard too — so a bare "auth"/"guest" here would
| silently check the "web" guard instead, before UseAdminGuard ever gets a
| chance to switch it. Pinning the guard removes that ordering dependency.
*/

Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [AdminAuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AdminAuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.attempt');
});

Route::post('/logout', [AdminAuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth:admin')
    ->name('logout');

Route::middleware(['auth:admin', 'backoffice'])->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('products', ProductController::class)->except(['show']);
    Route::resource('categories', CategoryController::class)->except(['show']);

    Route::resource('promotions', PromotionController::class)->except(['show']);
    Route::patch('promotions/{promotion}/toggle', [PromotionController::class, 'toggle'])->name('promotions.toggle');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
});