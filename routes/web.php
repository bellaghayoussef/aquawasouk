<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\VendorRegistrationController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\BladeTestController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\NotificationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


use Illuminate\Support\Facades\Artisan;

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/clear', function () {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('optimize:clear');
        Artisan::call('storage:link');
        return "Cache and Config cleared successfully!";
    });

    Route::get('/migrate', function () {
        Artisan::call('migrate', [
            '--force' => true,
            '--seed' => true,
        ]);

        return 'Database migrated successfully!';
    });
});

// Google OAuth Routes
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// Language switching
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// SEO
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

// Public routes (Powered by Laravel Blade)
Route::get('/', [BladeTestController::class, 'home'])->name('home');
Route::get('/about', [BladeTestController::class, 'about'])->name('about');
Route::get('/contact', [BladeTestController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/become-vendor', [BladeTestController::class, 'becomeVendor'])->name('become-vendor');
Route::post('/become-vendor', [VendorRegistrationController::class, 'store'])->name('become-vendor.store');

// Legal Pages
Route::get('/privacy-policy', [BladeTestController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms-of-service', [BladeTestController::class, 'termsOfService'])->name('terms-of-service');

// Blog
Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');

// Products
Route::get('/products', [BladeTestController::class, 'products'])->name('products.index');
Route::get('/products/{slug}', [BladeTestController::class, 'productShow'])->name('products.show');

// Vendors
Route::get('/vendors', [BladeTestController::class, 'vendors'])->name('vendors.index');
Route::get('/vendors/{slug}', [BladeTestController::class, 'vendorShow'])->name('vendors.show');

// Cart (accessible to both guests and authenticated users)
Route::get('/cart', [BladeTestController::class, 'cart'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');

// Promo Codes / Coupons
Route::post('/cart/coupon/apply', [CouponController::class, 'apply'])->name('coupon.apply');
Route::post('/cart/coupon/remove', [CouponController::class, 'remove'])->name('coupon.remove');

// Wishlist / Favoris
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/toggle/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

// Customer Reviews
Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('products.reviews.store');

// Live Visual Order Tracking
Route::get('/track-order', [OrderTrackingController::class, 'index'])->name('orders.track');


// Direct order without cart (accessible to guests and authenticated users)
Route::post('/orders/direct', [OrderController::class, 'directStore'])->name('orders.direct');

use App\Http\Controllers\ShoppingAssistantController;

// Shopping Assistant routes (accessible to guests and authenticated users)
Route::prefix('shopping-assistant')->name('shopping-assistant.')->group(function () {
    Route::get('/config', [ShoppingAssistantController::class, 'config'])->name('config');
    Route::post('/chat', [ShoppingAssistantController::class, 'chat'])->name('chat');
    Route::get('/products', [ShoppingAssistantController::class, 'searchProducts'])->name('products');
    Route::get('/cart', [ShoppingAssistantController::class, 'getCart'])->name('cart');
    Route::post('/cart/add', [ShoppingAssistantController::class, 'addToCart'])->name('cart.add');
    Route::patch('/cart/{item}', [ShoppingAssistantController::class, 'updateCartItem'])->name('cart.update');
    Route::delete('/cart/{item}', [ShoppingAssistantController::class, 'removeCartItem'])->name('cart.remove');
    Route::match(['get', 'post'], '/order-tracking', [ShoppingAssistantController::class, 'trackOrder'])->name('order-tracking');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Dashboard - Customer Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Customer\CustomerDashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::match(['patch', 'post'], '/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/checkout/{vendor}', [OrderController::class, 'checkout'])->name('orders.checkout');
    Route::post('/checkout', [OrderController::class, 'store'])->name('orders.store');

    // Internal Chat (Customer <-> Vendor)
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ChatController::class, 'index'])->name('index');
        Route::post('/start', [\App\Http\Controllers\ChatController::class, 'start'])->name('start');
        Route::get('/{conversation}', [\App\Http\Controllers\ChatController::class, 'show'])->name('show');
        Route::post('/{conversation}/send', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('send');
    });

    // In-App Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    // Customer Address Book
    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');
    Route::post('/addresses/{address}/default', [AddressController::class, 'setDefault'])->name('addresses.default');

    // Vendor Review Reply
    Route::post('/vendor/reviews/{review}/reply', [ReviewController::class, 'reply'])->name('vendor.reviews.reply');
});

// Customer Dashboard Routes
use App\Http\Controllers\Customer\CustomerDashboardController;

Route::middleware(['auth'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [CustomerDashboardController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [CustomerDashboardController::class, 'orderShow'])->name('orders.show');
    Route::get('/settings', [CustomerDashboardController::class, 'settings'])->name('settings');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');

    // Users Management
    Route::get('/users', [App\Http\Controllers\Admin\AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\AdminController::class, 'editUser'])->name('users.edit');
    Route::patch('/users/{user}', [App\Http\Controllers\Admin\AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\AdminController::class, 'deleteUser'])->name('users.destroy');

    // Vendors Management
    Route::get('/vendors', [App\Http\Controllers\Admin\AdminController::class, 'vendors'])->name('vendors');
    Route::get('/vendors/{vendor}/edit', [App\Http\Controllers\Admin\AdminController::class, 'editVendor'])->name('vendors.edit');
    Route::patch('/vendors/{vendor}', [App\Http\Controllers\Admin\AdminController::class, 'updateVendor'])->name('vendors.update');
    Route::patch('/vendors/{vendor}/status', [App\Http\Controllers\Admin\AdminController::class, 'updateVendorStatus'])->name('vendors.status');
    Route::post('/vendors/{vendor}/margins', [App\Http\Controllers\Admin\AdminController::class, 'saveVendorMargins'])->name('vendors.margins');
    Route::delete('/vendors/{vendor}', [App\Http\Controllers\Admin\AdminController::class, 'deleteVendor'])->name('vendors.destroy');

    // Cash Register & Vendor Payouts
    Route::get('/cash-register', [App\Http\Controllers\Admin\AdminController::class, 'cashRegister'])->name('cash-register');
    Route::post('/payouts', [App\Http\Controllers\Admin\AdminController::class, 'storePayout'])->name('payouts.store');
    Route::delete('/payouts/{payout}', [App\Http\Controllers\Admin\AdminController::class, 'deletePayout'])->name('payouts.destroy');

    // Products Management
    Route::get('/products', [App\Http\Controllers\Admin\AdminController::class, 'products'])->name('products');
    Route::get('/products/{product}/edit', [App\Http\Controllers\Admin\AdminController::class, 'editProduct'])->name('products.edit');
    Route::patch('/products/{product}', [App\Http\Controllers\Admin\AdminController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{product}', [App\Http\Controllers\Admin\AdminController::class, 'deleteProduct'])->name('products.destroy');

    // Orders Management
    Route::get('/orders', [App\Http\Controllers\Admin\AdminController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [App\Http\Controllers\Admin\AdminController::class, 'showOrder'])->name('orders.show');
    Route::patch('/orders/{order}/status', [App\Http\Controllers\Admin\AdminController::class, 'updateOrderStatus'])->name('orders.status');

    // Categories Management
    Route::get('/categories', [App\Http\Controllers\Admin\AdminController::class, 'categories'])->name('categories');
    Route::get('/categories/create', [App\Http\Controllers\Admin\AdminController::class, 'createCategory'])->name('categories.create');
    Route::post('/categories', [App\Http\Controllers\Admin\AdminController::class, 'storeCategory'])->name('categories.store');
    Route::get('/categories/{category}/edit', [App\Http\Controllers\Admin\AdminController::class, 'editCategory'])->name('categories.edit');
    Route::patch('/categories/{category}', [App\Http\Controllers\Admin\AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [App\Http\Controllers\Admin\AdminController::class, 'deleteCategory'])->name('categories.destroy');

    // Contact Messages Management
    Route::get('/contact-messages', [App\Http\Controllers\Admin\AdminController::class, 'contactMessages'])->name('contact-messages');
    Route::patch('/contact-messages/{message}/status', [App\Http\Controllers\Admin\AdminController::class, 'updateContactStatus'])->name('contact-messages.status');
    Route::delete('/contact-messages/{message}', [App\Http\Controllers\Admin\AdminController::class, 'deleteContactMessage'])->name('contact-messages.destroy');


    // Banners Management
    Route::resource('banners', App\Http\Controllers\Admin\BannerController::class)->except(['show']);

    // Blog Management
    Route::resource('blog', \App\Http\Controllers\Admin\BlogController::class);

    // Shopping Assistant Settings
    Route::get('/shopping-assistant', [ShoppingAssistantController::class, 'adminIndex'])->name('shopping-assistant.index');
    Route::post('/shopping-assistant', [ShoppingAssistantController::class, 'adminUpdate'])->name('shopping-assistant.update');

    // Social Commerce Bot (Instagram & Facebook Messenger)
    Route::get('/social-bot', function () {
        return \Inertia\Inertia::render('Admin/SocialBot/Simulator', [
            'webhookUrl' => url('/api/webhook/meta'),
            'verifyToken' => \App\Services\SocialBot\MetaGraphApiService::getVerifyToken(),
        ]);
    })->name('social-bot.index');

    // TextBee SMS Gateway
    Route::prefix('sms')->name('sms.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SmsController::class, 'index'])->name('index');
        Route::post('/send', [\App\Http\Controllers\Admin\SmsController::class, 'send'])->name('send');
        Route::post('/settings', [\App\Http\Controllers\Admin\SmsController::class, 'updateSettings'])->name('settings');
        Route::post('/test', [\App\Http\Controllers\Admin\SmsController::class, 'testConnection'])->name('test');
    });
});

// Vendor Routes
Route::middleware(['auth', 'vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Vendor\VendorDashboardController::class, 'dashboard'])->name('dashboard');
    // Products Management
    Route::get('/products', [App\Http\Controllers\Vendor\VendorDashboardController::class, 'products'])->name('products');
    Route::get('/products/create', [App\Http\Controllers\Vendor\VendorDashboardController::class, 'createProduct'])->name('products.create');
    Route::post('/products', [App\Http\Controllers\Vendor\VendorDashboardController::class, 'storeProduct'])->name('products.store');
    Route::get('/products/{product}/edit', [App\Http\Controllers\Vendor\VendorDashboardController::class, 'editProduct'])->name('products.edit');
    Route::match(['post', 'patch'], '/products/{product}', [App\Http\Controllers\Vendor\VendorDashboardController::class, 'updateProduct'])->name('products.update');
    Route::delete('/products/{product}', [App\Http\Controllers\Vendor\VendorDashboardController::class, 'deleteProduct'])->name('products.destroy');

    // Orders Management
    Route::get('/orders', [App\Http\Controllers\Vendor\VendorDashboardController::class, 'orders'])->name('orders');
    Route::get('/orders/{orderItem}', [App\Http\Controllers\Vendor\VendorDashboardController::class, 'showOrder'])->name('orders.show');
    Route::get('/orders/{orderItem}/invoice', [App\Http\Controllers\Vendor\VendorDashboardController::class, 'generateInvoice'])->name('orders.invoice');
    Route::get('/orders/{orderItem}/waybill', [App\Http\Controllers\Vendor\VendorDashboardController::class, 'generateWaybill'])->name('orders.waybill');
    Route::patch('/orders/{orderItem}/status', [App\Http\Controllers\Vendor\VendorDashboardController::class, 'updateOrderStatus'])->name('orders.status');
    Route::post('/orders/{orderItem}/sms', [App\Http\Controllers\Vendor\VendorDashboardController::class, 'sendOrderSms'])->name('orders.sms');

    // Settings
    Route::get('/settings', [App\Http\Controllers\Vendor\VendorDashboardController::class, 'settings'])->name('settings');
    Route::match(['post', 'patch'], '/settings', [App\Http\Controllers\Vendor\VendorDashboardController::class, 'updateSettings'])->name('settings.update');
});






// Public Laravel Blade Pages (Demo / Testing user pages without dashboard)
Route::prefix('testblade')->name('blade.')->group(function () {
    Route::get('/', [BladeTestController::class, 'home'])->name('index');
    Route::get('/home', [BladeTestController::class, 'home'])->name('home');
    Route::get('/products', [BladeTestController::class, 'products'])->name('products.index');
    Route::get('/products/{slug}', [BladeTestController::class, 'productShow'])->name('products.show');
    Route::get('/cart', [BladeTestController::class, 'cart'])->name('cart');
    Route::get('/vendors', [BladeTestController::class, 'vendors'])->name('vendors.index');
    Route::get('/vendors/{slug}', [BladeTestController::class, 'vendorShow'])->name('vendors.show');
    Route::get('/about', [BladeTestController::class, 'about'])->name('about');
    Route::get('/contact', [BladeTestController::class, 'contact'])->name('contact');
    Route::get('/become-vendor', [BladeTestController::class, 'becomeVendor'])->name('become-vendor');
    Route::get('/privacy-policy', [BladeTestController::class, 'privacyPolicy'])->name('privacy-policy');
    Route::get('/terms-of-service', [BladeTestController::class, 'termsOfService'])->name('terms-of-service');
});

Route::get('/feed.xml', [SitemapController::class, 'feed']);

require __DIR__ . '/auth.php';
