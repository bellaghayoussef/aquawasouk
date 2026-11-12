<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ReviewController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login/google', [AuthController::class, 'loginWithGoogle']);

// Public API Routes
Route::prefix('v1')->group(function () {

    // Authentication Routes (with brute-force protection)
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/auth/google', [AuthController::class, 'loginWithGoogle'])->middleware('throttle:10,1');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:3,1');

    // Public Product Routes
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/allproducts', [ProductController::class, 'allproducts']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);
    Route::get('/featured/products', [ProductController::class, 'featured']);
    Route::get('/products/search', [ProductController::class, 'search']);

    // Public Category Routes
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);
    Route::get('/categories/{slug}/products', [CategoryController::class, 'products']);

    // Public Vendor Routes
    Route::get('/vendors', [\App\Http\Controllers\Api\VendorApiController::class, 'index']);
    Route::get('/vendors/{slug}', [\App\Http\Controllers\Api\VendorApiController::class, 'show']);
    Route::get('/vendors/{slug}/products', [\App\Http\Controllers\Api\VendorApiController::class, 'products']);

    // Public Reviews
    Route::get('/products/{product}/reviews', [ReviewController::class, 'index']);

    // Direct Order (Supports both guests and authenticated customers, throttled against spam)
    Route::post('/orders/direct', [OrderController::class, 'directOrder'])->middleware('throttle:10,1');

    // Protected Routes (Require Authentication)
    Route::middleware('auth:sanctum')->group(function () {

        // Auth User Routes
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::put('/user', [AuthController::class, 'updateProfile']);
        Route::put('/user/password', [AuthController::class, 'updatePassword']);
        Route::delete('/user', [AuthController::class, 'deleteAccount']);

        // User Cart Routes
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index']);
            Route::post('/add', [CartController::class, 'add']);
            Route::put('/update/{cartItem}', [CartController::class, 'update']);
            Route::delete('/remove/{cartItem}', [CartController::class, 'remove']);
            Route::delete('/clear', [CartController::class, 'clear']);
            Route::get('/count', [CartController::class, 'count']);
        });

        // User Order Routes
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::get('/{order}', [OrderController::class, 'show']);
            Route::post('/create', [OrderController::class, 'create']);
            Route::post('/{order}/cancel', [OrderController::class, 'cancel']);
            Route::get('/{order}/invoice', [OrderController::class, 'invoice']);
            Route::get('/{order}/track', [OrderController::class, 'track']);
        });

        // User Review Routes
        Route::prefix('reviews')->group(function () {
            Route::post('/products/{product}', [ReviewController::class, 'store']);
            Route::put('/{review}', [ReviewController::class, 'update']);
            Route::delete('/{review}', [ReviewController::class, 'destroy']);
            Route::get('/my-reviews', [ReviewController::class, 'myReviews']);
        });

        // User Wishlist Routes (if you want to add this feature)
        Route::prefix('wishlist')->group(function () {
            Route::get('/', [UserController::class, 'wishlist']);
            Route::post('/add/{product}', [UserController::class, 'addToWishlist']);
            Route::delete('/remove/{product}', [UserController::class, 'removeFromWishlist']);
        });

        // User Addresses Routes (if you want to add this feature)
        Route::prefix('addresses')->group(function () {
            Route::get('/', [UserController::class, 'addresses']);
            Route::post('/', [UserController::class, 'storeAddress']);
            Route::put('/{address}', [UserController::class, 'updateAddress']);
            Route::delete('/{address}', [UserController::class, 'deleteAddress']);
            Route::post('/{address}/set-default', [UserController::class, 'setDefaultAddress']);
        });

        // Internal Chat Routes (Customer <-> Vendor)
        Route::prefix('chat')->group(function () {
            Route::get('/conversations', [\App\Http\Controllers\Api\ChatController::class, 'index']);
            Route::post('/conversations/start', [\App\Http\Controllers\Api\ChatController::class, 'start']);
            Route::get('/conversations/{conversation}', [\App\Http\Controllers\Api\ChatController::class, 'show']);
            Route::post('/conversations/{conversation}/send', [\App\Http\Controllers\Api\ChatController::class, 'sendMessage']);
        });

        // Vendor Routes (Only for vendors)
        Route::middleware('vendor')->prefix('vendor')->group(function () {

            // Vendor Dashboard
            Route::get('/dashboard', [VendorController::class, 'dashboard']);
            Route::get('/statistics', [VendorController::class, 'statistics']);
            Route::get('/sales-chart', [VendorController::class, 'salesChart']);

            // Vendor Profile
            Route::get('/profile', [VendorController::class, 'profile']);
            Route::put('/profile', [VendorController::class, 'updateProfile']);
            Route::post('/profile/logo', [VendorController::class, 'updateLogo']);
            Route::post('/profile/banner', [VendorController::class, 'updateBanner']);

            // Vendor Products
            Route::prefix('products')->group(function () {
                Route::get('/', [VendorController::class, 'products']);
                Route::post('/', [VendorController::class, 'storeProduct']);
                Route::get('/{product}', [VendorController::class, 'showProduct']);
                Route::put('/{product}', [VendorController::class, 'updateProduct']);
                Route::delete('/{product}', [VendorController::class, 'deleteProduct']);
                Route::post('/{product}/images', [VendorController::class, 'addProductImage']);
                Route::delete('/images/{image}', [VendorController::class, 'deleteProductImage']);
                Route::put('/{product}/toggle-status', [VendorController::class, 'toggleProductStatus']);
                Route::put('/{product}/toggle-featured', [VendorController::class, 'toggleProductFeatured']);
            });

            // Vendor Orders
            Route::prefix('orders')->group(function () {
                Route::get('/', [VendorController::class, 'orders']);
                Route::get('/{orderItem}', [VendorController::class, 'showOrder']);
                Route::put('/{orderItem}/status', [VendorController::class, 'updateOrderStatus']);
                Route::get('/{orderItem}/invoice', [VendorController::class, 'generateInvoice']);
                Route::get('/{orderItem}/waybill', [VendorController::class, 'generateWaybill']);
            });

            // Vendor Analytics
            Route::prefix('analytics')->group(function () {
                Route::get('/revenue', [VendorController::class, 'revenue']);
                Route::get('/top-products', [VendorController::class, 'topProducts']);
                Route::get('/recent-orders', [VendorController::class, 'recentOrders']);
                Route::get('/customers', [VendorController::class, 'customers']);
            });

            // Vendor Reviews
            Route::prefix('reviews')->group(function () {
                Route::get('/', [VendorController::class, 'reviews']);
                Route::put('/{review}/reply', [VendorController::class, 'replyToReview']);
            });

            // Vendor Notifications
            Route::prefix('notifications')->group(function () {
                Route::get('/', [VendorController::class, 'notifications']);
                Route::put('/{notification}/read', [VendorController::class, 'markNotificationAsRead']);
                Route::put('/mark-all-read', [VendorController::class, 'markAllNotificationsAsRead']);
            });
        });

        // Admin Routes (Exclusively for Admins)
        Route::middleware('admin')->prefix('admin')->group(function () {
            Route::prefix('vendors')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\AdminVendorController::class, 'index']);
                Route::get('/stats', [\App\Http\Controllers\Api\AdminVendorController::class, 'stats']);
                Route::get('/{vendor}', [\App\Http\Controllers\Api\AdminVendorController::class, 'show']);
                Route::put('/{vendor}/status', [\App\Http\Controllers\Api\AdminVendorController::class, 'updateStatus']);
            });
        });
    });
});

// Meta Webhook for Instagram Direct & Facebook Messenger (Social Commerce Bot)
Route::get('/webhook/meta', [\App\Http\Controllers\SocialBotWebhookController::class, 'verify']);
Route::post('/webhook/meta', [\App\Http\Controllers\SocialBotWebhookController::class, 'handle']);
Route::post('/social-bot/simulate', [\App\Http\Controllers\SocialBotWebhookController::class, 'simulate']);
Route::post('/social-bot/reset', [\App\Http\Controllers\SocialBotWebhookController::class, 'resetSimulation']);
