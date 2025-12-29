<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\{
    ProfileController,
    ProductController,
    CategoryController,
    PostController,
    BannerController,
    UserCartController,
    ReceiptController,
    PaymentController,
    ProductReviewController
};
use App\Http\Controllers\Auth\SocialLoginController as SocialController;

/*
|--------------------------------------------------------------------------
| Public pages
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

/*
|--------------------------------------------------------------------------
| Dashboard (admin)
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    $user = auth()->user();
    $admins = [
        'thanhphuongkhuu@gmail.com',
        'anotheradmin@example.com',
        'chodichomoive@gmail.com',
    ];

    if (!$user || !in_array(strtolower($user->email), array_map('strtolower', $admins))) {
        return redirect('/');
    }

    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| PRODUCTS
|--------------------------------------------------------------------------
*/

// Public (slug)
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product:slug}', [ProductController::class, 'show']);
Route::get('products/{product:slug}/reviews', [ProductReviewController::class, 'index']);

// Admin (ID)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('products', [ProductController::class, 'store']);
    Route::put('products/{product:id}', [ProductController::class, 'update']);
    Route::delete('products/{product:id}', [ProductController::class, 'destroy']);

    Route::get('reviews', [ProductReviewController::class, 'all']);
    Route::post('products/{product:id}/reviews', [ProductReviewController::class, 'store']);
    Route::put('products/reviews/{review:id}', [ProductReviewController::class, 'update']);
    Route::delete('products/reviews/{review:id}', [ProductReviewController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| CATEGORIES
|--------------------------------------------------------------------------
*/

Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category:slug}', [CategoryController::class, 'show']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('categories', [CategoryController::class, 'store']);
    Route::put('categories/{category:id}', [CategoryController::class, 'update']);
    Route::delete('categories/{category:id}', [CategoryController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| POSTS
|--------------------------------------------------------------------------
*/

Route::get('posts', [PostController::class, 'index']);
Route::get('posts/{post:slug}', [PostController::class, 'show']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('posts', [PostController::class, 'store']);
    Route::put('posts/{post:id}', [PostController::class, 'update']);
    Route::delete('posts/{post:id}', [PostController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| BANNERS
|--------------------------------------------------------------------------
*/

Route::get('banners', [BannerController::class, 'index']);
Route::get('banners/{banner:id}', [BannerController::class, 'show']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('banners', [BannerController::class, 'store']);
    Route::put('banners/{banner:id}', [BannerController::class, 'update']);
    Route::delete('banners/{banner:id}', [BannerController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| CART & RECEIPTS
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Cart
    Route::get('/user/cart', [UserCartController::class, 'index']);
    Route::post('/user/cart', [UserCartController::class, 'store']);
    Route::put('/user/cart/{id}', [UserCartController::class, 'update']);
    Route::delete('/user/cart/{id}', [UserCartController::class, 'destroy']);

    // ✅ IMPORTANT: static route FIRST
    Route::get('/receipts/active', [ReceiptController::class, 'active'])->name('receipts.active');

    // Checkout
    Route::post('/receipts/checkout', [ReceiptController::class, 'checkout']);
    Route::post('/receipts/checkout-momo', [ReceiptController::class, 'checkoutMoMo']);

    // MoMo QR simulation
    Route::get('/simulate/momo-qr', [PaymentController::class, 'personalQr']);

    // Receipts CRUD
    Route::get('receipts', [ReceiptController::class, 'index']);
    Route::get('receipts/{receipt:id}', [ReceiptController::class, 'show']);
    Route::put('receipts/{receipt:id}', [ReceiptController::class, 'update']);
    Route::delete('receipts/{receipt:id}', [ReceiptController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Social login
|--------------------------------------------------------------------------
*/

Route::get('auth/{provider}', [SocialController::class, 'redirect'])->name('social.redirect');
Route::get('auth/{provider}/callback', [SocialController::class, 'callback'])->name('social.callback');

/*
|--------------------------------------------------------------------------
| SPA routes
|--------------------------------------------------------------------------
*/

Route::get('/giohang', fn () => Inertia::render('CartDetail'))->middleware('auth');

Route::get('/product/{slug}', fn () => Inertia::render('Welcome'));
Route::get('/category/{slug}', fn () => Inertia::render('Welcome'));
Route::get('/blog/{slug}', fn () => Inertia::render('Welcome'));
Route::get('/shop', fn () => Inertia::render('Welcome'));
Route::get('/contact', fn () => Inertia::render('Welcome'));
Route::get('/about', fn () => Inertia::render('Welcome'));

/*
|--------------------------------------------------------------------------
| Catch-all (SPA fallback)
|--------------------------------------------------------------------------
*/

Route::get('/{any}', fn () => Inertia::render('Welcome'))
    ->where('any', '.*');

require __DIR__.'/auth.php';
