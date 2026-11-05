<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use App\Models\Banner;
use App\Models\Cart;
use Illuminate\Http\Request;

class BladeTestController extends Controller
{
    /**
     * Get or create active shopping cart for session/user
     */
    protected function getCart()
    {
        if (auth()->check()) {
            return Cart::firstOrCreate(['user_id' => auth()->id()]);
        }

        $sessionId = session()->getId();
        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Total items count in cart for layout navbar
     */
    protected function getCartCount()
    {
        $cart = $this->getCart();
        return (int) $cart->items()->sum('quantity');
    }

    /**
     * Common categories for navbar
     */
    protected function getNavCategories()
    {
        return Category::where('parent_id', null)->take(8)->get();
    }

    /**
     * Blade Home / Hub Landing Page
     */
    public function home()
    {
        $banners = Banner::where('is_active', true)
            ->where('page', 'home')
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $featuredProducts = Product::with(['vendor', 'category', 'primaryImage'])
            ->whereHas('vendor', function ($q) {
                $q->active();
            })
            ->active()
            ->latest()
            ->take(8)
            ->get();

        // Dynamic Hero Spotlight Products (discounted or top featured)
        $heroProducts = Product::with(['vendor', 'category', 'primaryImage'])
            ->whereHas('vendor', function ($q) {
                $q->active();
            })
            ->active()
            ->whereNotNull('discount_price')
            ->whereColumn('discount_price', '<', 'price')
            ->latest()
            ->take(4)
            ->get();

        if ($heroProducts->isEmpty()) {
            $heroProducts = $featuredProducts->take(4);
        }

        $categories = Category::where('parent_id', null)->take(12)->get();

        $vendors = Vendor::active()
            ->withCount('products')
            ->take(6)
            ->get();

        return view('blade.home', [
            'banners' => $banners,
            'featuredProducts' => $featuredProducts,
            'heroProducts' => $heroProducts,
            'categories' => $categories,
            'vendors' => $vendors,
            'navCategories' => $this->getNavCategories(),
            'cartCount' => $this->getCartCount(),
        ]);
    }

    /**
     * Blade Products Catalog with Filtering, Sorting & Search
     */
    public function products(Request $request)
    {
        $query = Product::with(['vendor', 'category', 'primaryImage'])
            ->active()
            ->whereHas('vendor', function ($q) {
                $q->active();
            });

        // Filter by category slug
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->query('category'));
            });
        }

        // Search by keyword
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by Min Price
        if ($request->filled('min_price') && is_numeric($request->min_price)) {
            $minPrice = (float) $request->min_price;
            $query->where(function ($q) use ($minPrice) {
                $q->where(function ($sub) use ($minPrice) {
                    $sub->whereNotNull('discount_price')->where('discount_price', '>=', $minPrice);
                })->orWhere(function ($sub) use ($minPrice) {
                    $sub->whereNull('discount_price')->where('price', '>=', $minPrice);
                });
            });
        }

        // Filter by Max Price
        if ($request->filled('max_price') && is_numeric($request->max_price)) {
            $maxPrice = (float) $request->max_price;
            $query->where(function ($q) use ($maxPrice) {
                $q->where(function ($sub) use ($maxPrice) {
                    $sub->whereNotNull('discount_price')->where('discount_price', '<=', $maxPrice);
                })->orWhere(function ($sub) use ($maxPrice) {
                    $sub->whereNull('discount_price')->where('price', '<=', $maxPrice);
                });
            });
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_low':
                $query->orderByRaw('COALESCE(discount_price, price) asc');
                break;
            case 'price_high':
                $query->orderByRaw('COALESCE(discount_price, price) desc');
                break;
            case 'popular':
                $query->orderBy('views', 'desc');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::all();

        return view('blade.products.index', [
            'products' => $products,
            'categories' => $categories,
            'navCategories' => $this->getNavCategories(),
            'cartCount' => $this->getCartCount(),
        ]);
    }

    /**
     * Blade Product Details Page
     */
    public function productShow($slug)
    {
        $product = Product::with([
            'vendor.user',
            'category',
            'images',
            'primaryImage',
            'variations',
            'reviews' => function ($q) {
                $q->approved()->latest()->with('user');
            },
        ])
        ->where('slug', $slug)
        ->whereHas('vendor', function ($q) {
            $q->active();
        })
        ->firstOrFail();

        // Increment views safely
        $product->increment('views');

        $relatedProducts = Product::with(['vendor', 'category', 'primaryImage'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->whereHas('vendor', function ($q) {
                $q->active();
            })
            ->active()
            ->take(4)
            ->get();

        $isFavorited = auth()->check() ? $product->isFavoritedBy(auth()->user()) : false;
        
        $hasPurchased = false;
        if (auth()->check()) {
            $hasPurchased = \App\Models\Order::where('user_id', auth()->id())
                ->where(function ($q) {
                    $q->where('status', 'delivered')
                      ->orWhere('payment_status', 'paid');
                })
                ->whereHas('items', function ($q) use ($product) {
                    $q->where('product_id', $product->id);
                })
                ->exists();
        }

        return view('blade.products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'isFavorited' => $isFavorited,
            'hasPurchased' => $hasPurchased,
            'navCategories' => $this->getNavCategories(),
            'cartCount' => $this->getCartCount(),
        ]);
    }

    /**
     * Blade Cart Page
     */
    public function cart()
    {
        $cart = $this->getCart();
        $cart->load(['items.product.vendor', 'items.product.primaryImage', 'items.product.images']);

        $itemsByVendor = $cart->items->groupBy(function ($item) {
            return $item->product && $item->product->vendor ? $item->product->vendor_id : 0;
        });

        $appliedCoupon = session('applied_coupon');

        return view('blade.cart.index', [
            'cart' => $cart,
            'itemsByVendor' => $itemsByVendor,
            'appliedCoupon' => $appliedCoupon,
            'navCategories' => $this->getNavCategories(),
            'cartCount' => $this->getCartCount(),
        ]);
    }

    /**
     * Blade Vendors Directory
     */
    public function vendors(Request $request)
    {
        $query = Vendor::active()->withCount('products');

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where('shop_name', 'like', "%{$search}%");
        }

        $vendors = $query->latest()->paginate(12)->withQueryString();

        return view('blade.vendors.index', [
            'vendors' => $vendors,
            'navCategories' => $this->getNavCategories(),
            'cartCount' => $this->getCartCount(),
        ]);
    }

    /**
     * Blade Vendor Shop Details
     */
    public function vendorShow(Request $request, $slug)
    {
        $vendor = Vendor::where('slug', $slug)->active()->firstOrFail();

        $query = $vendor->products()
            ->with(['category', 'primaryImage'])
            ->active();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->input('category'));
            });
        }

        switch ($request->input('sort')) {
            case 'price_low':
                $query->orderByRaw('COALESCE(discount_price, price) asc');
                break;
            case 'price_high':
                $query->orderByRaw('COALESCE(discount_price, price) desc');
                break;
            case 'popular':
                $query->orderBy('views', 'desc');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();

        $vendorCategories = Category::whereHas('products', function ($q) use ($vendor) {
            $q->where('vendor_id', $vendor->id)->active();
        })->get();

        return view('blade.vendors.show', [
            'vendor' => $vendor,
            'products' => $products,
            'vendorCategories' => $vendorCategories,
            'navCategories' => $this->getNavCategories(),
            'cartCount' => $this->getCartCount(),
        ]);
    }

    /**
     * Blade About Page
     */
    public function about()
    {
        return view('blade.about', [
            'navCategories' => $this->getNavCategories(),
            'cartCount' => $this->getCartCount(),
        ]);
    }

    /**
     * Blade Contact Page
     */
    public function contact()
    {
        return view('blade.contact', [
            'navCategories' => $this->getNavCategories(),
            'cartCount' => $this->getCartCount(),
        ]);
    }

    /**
     * Blade Become Vendor Page
     */
    public function becomeVendor()
    {
        return view('blade.become-vendor', [
            'navCategories' => $this->getNavCategories(),
            'cartCount' => $this->getCartCount(),
        ]);
    }

    /**
     * Blade Privacy Policy Page
     */
    public function privacyPolicy()
    {
        return view('blade.legal.privacy', [
            'navCategories' => $this->getNavCategories(),
            'cartCount' => $this->getCartCount(),
        ]);
    }

    /**
     * Blade Terms of Service Page
     */
    public function termsOfService()
    {
        return view('blade.legal.terms', [
            'navCategories' => $this->getNavCategories(),
            'cartCount' => $this->getCartCount(),
        ]);
    }
}
