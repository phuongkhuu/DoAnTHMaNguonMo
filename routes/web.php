<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\UserCartController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\Auth\SocialLoginController as SocialController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Dashboard (admin only)
Route::get('/dashboard', function () {
    $user = auth()->user();
    $adminEmail = 'thanhphuongkhuu@gmail.com';
    $adminEmail2 = 'anotheradmin@example.com';
    if (! $user || (strtolower($user->email) !== strtolower($adminEmail) && strtolower($user->email) !== strtolower($adminEmail2))) {
        return redirect('/');
    }
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// API: Products
Route::get('products', [ProductController::class,'index']);
Route::get('products/{product}', [ProductController::class,'show']);
Route::middleware(['auth','verified'])->group(function () {
    Route::post('products', [ProductController::class,'store']);
    Route::put('products/{product}', [ProductController::class,'update']);
    Route::delete('products/{product}', [ProductController::class,'destroy']);
    Route::get('/reviews', [ProductReviewController::class, 'all']);
});
Route::get('products/{product}/reviews', [ProductReviewController::class,'index']);
Route::middleware('auth')->group(function () {
    Route::post('products/{product}/reviews', [ProductReviewController::class,'store']);
    Route::put('products/reviews/{review}', [ProductReviewController::class,'update']);
    Route::delete('products/reviews/{review}', [ProductReviewController::class,'destroy']);
});

// API: Categories
Route::get('categories', [CategoryController::class,'index']);
Route::get('categories/{category}', [CategoryController::class,'show']);
Route::get('/categories', [CategoryController::class, 'list']);
Route::middleware(['auth','verified'])->group(function () {
    Route::post('categories', [CategoryController::class,'store']);
    Route::put('categories/{category}', [CategoryController::class,'update']);
    Route::delete('categories/{category}', [CategoryController::class,'destroy']);
});

// API: Posts
Route::get('posts', [PostController::class,'index']);
Route::get('posts/{post}', [PostController::class,'show']);
Route::middleware(['auth','verified'])->group(function () {
    Route::post('posts', [PostController::class,'store']);
    Route::put('posts/{post}', [PostController::class,'update']);
    Route::delete('posts/{post}', [PostController::class,'destroy']);
});

// API: Banners
Route::get('banners', [BannerController::class,'index']);
Route::get('banners/{banner}', [BannerController::class,'show']);
Route::middleware(['auth','verified'])->group(function () {
    Route::post('banners', [BannerController::class,'store']);
    Route::put('banners/{banner}', [BannerController::class,'update']);
    Route::delete('banners/{banner}', [BannerController::class,'destroy']);
});

// Cart + Receipts
Route::middleware('auth')->group(function () {
    Route::get('/user/cart', [UserCartController::class, 'index']);
    Route::post('/user/cart', [UserCartController::class, 'store']);
    Route::put('/user/cart/{id}', [UserCartController::class, 'update']);
    Route::delete('/user/cart/{id}', [UserCartController::class, 'destroy']);

    Route::get('/receipts/active', [ReceiptController::class, 'active'])->name('receipts.active');

    Route::post('/receipts/checkout-momo', [ReceiptController::class, 'checkoutMoMo'])->name('receipts.checkout.momo');

    Route::get('/simulate/momo-qr', [PaymentController::class, 'personalQr'])->name('simulate.momo.qr');

    Route::post('/receipts/checkout', [ReceiptController::class, 'checkout'])->name('receipts.checkout');

    
    Route::get('receipts', [ReceiptController::class,'index']);
    Route::get('receipts/{receipt}', [ReceiptController::class,'show']);
    Route::put('receipts/{receipt}', [ReceiptController::class,'update']);
    Route::delete('receipts/{receipt}', [ReceiptController::class,'destroy']);

});

// Social login
Route::get('auth/{provider}', [SocialController::class,'redirect'])->name('social.redirect');
Route::get('auth/{provider}/callback', [SocialController::class,'callback'])->name('social.callback');

// Inertia SPA routes
Route::get('/giohang', fn() => Inertia::render('CartDetail'))->name('cart')->middleware('auth');
Route::get('/product/{slug}', fn() => Inertia::render('Welcome'));
Route::get('/category/{slug}', fn() => Inertia::render('Welcome'));
Route::get('/blog/{slug}', fn() => Inertia::render('Welcome'));
Route::get('/shop', fn() => Inertia::render('Welcome'));
Route::get('/contact', fn() => Inertia::render('Welcome'));
Route::get('/about', fn() => Inertia::render('Welcome'));

// Catch-all fallback for SPA
Route::get('/{any}', fn() => Inertia::render('Welcome'))->where('any', '.*');

require __DIR__.'/auth.php';
