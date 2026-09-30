<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Livewire\Cart\CartPage;
use App\Livewire\Checkout\CheckoutPage;
use App\Livewire\Shop\ProductIndex;
use App\Livewire\Shop\ProductShow;
use App\Livewire\Wishlist\WishlistPage;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront
|--------------------------------------------------------------------------
| Admin routes live in routes/admin.php and are mounted under /admin by
| bootstrap/app.php with the "admin." name prefix.
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

/* ------------------------------------------------------------------ catalog */

Route::get('/shop', ProductIndex::class)->name('shop');
Route::get('/products/{product}', ProductShow::class)->name('products.show');

// Category and collection links are the shop page with a filter pre-applied.
Route::get('/category/{category:slug}', ProductIndex::class)->name('categories.show');

Route::get('/cart', CartPage::class)->name('cart');

/* -------------------------------------------------------------- static pages */

Route::view('/about', 'pages.about')->name('about');
Route::view('/faq', 'pages.faq')->name('faq');

Route::get('/contact', fn () => view('pages.contact'))->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.send');

/* --------------------------------------------------------------- guest auth */

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:6,1');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// Reachable while "logged out" — the account exists but isn't verified yet.
Route::get('/verify-email', [EmailVerificationController::class, 'notice'])->name('verification.notice');
Route::post('/verify-email', [EmailVerificationController::class, 'verify'])
    ->middleware('throttle:10,1')
    ->name('verification.verify');
Route::post('/verify-email/resend', [EmailVerificationController::class, 'resend'])->name('verification.resend');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/* ------------------------------------------------------------ signed-in only */

Route::middleware('auth')->group(function () {
    Route::get('/wishlist', WishlistPage::class)->name('wishlist');
    Route::get('/checkout', CheckoutPage::class)->name('checkout');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/success', [OrderController::class, 'success'])->name('orders.success');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
});
