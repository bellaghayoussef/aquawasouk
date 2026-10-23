<?php

namespace App\Services\ShoppingAssistant;

use App\Models\AssistantSetting;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductSearchService
{
    /**
     * Search products based on normalized criteria.
     */
    public function search(array $filters = []): array
    {
        $limit = (int) ($filters['limit'] ?? AssistantSetting::get('max_recommendations', config('shopping_assistant.max_recommendations', 6)));
        $limit = max(1, min($limit, 12));

        $query = Product::query()
            ->active()
            ->whereHas('vendor', function ($q) {
                $q->where('status', 'active');
            })
            ->with([
                'vendor:id,shop_name,slug,logo,rating,shipping_price',
                'category:id,name,slug',
                'primaryImage:id,product_id,image_path,is_primary',
            ]);

        // Keyword search (name, description, sku)
        if (!empty($filters['query'])) {
            $term = trim($filters['query']);
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhere('sku', 'like', "%{$term}%")
                  ->orWhereHas('category', function ($cq) use ($term) {
                      $cq->where('name', 'like', "%{$term}%");
                  });
            });
        }

        // Category filter (id or slug or name)
        if (!empty($filters['category'])) {
            $cat = $filters['category'];
            $query->whereHas('category', function ($q) use ($cat) {
                if (is_numeric($cat)) {
                    $q->where('id', $cat);
                } else {
                    $q->where('slug', $cat)->orWhere('name', 'like', "%{$cat}%");
                }
            });
        }

        // Vendor filter
        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        // Budget / Price filters (considering discount_price when present)
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $min = (float) $filters['min_price'];
            $query->where(function ($q) use ($min) {
                $q->where(function ($sub) use ($min) {
                    $sub->whereNotNull('discount_price')->where('discount_price', '>=', $min);
                })->orWhere(function ($sub) use ($min) {
                    $sub->whereNull('discount_price')->where('price', '>=', $min);
                });
            });
        }

        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $max = (float) $filters['max_price'];
            $query->where(function ($q) use ($max) {
                $q->where(function ($sub) use ($max) {
                    $sub->whereNotNull('discount_price')->where('discount_price', '<=', $max);
                })->orWhere(function ($sub) use ($max) {
                    $sub->whereNull('discount_price')->where('price', '<=', $max);
                });
            });
        }

        // In-stock only filter
        if (!empty($filters['in_stock_only'])) {
            $query->where('stock', '>', 0);
        }

        // Sorting
        $sort = $filters['sort'] ?? 'popularity';
        switch ($sort) {
            case 'price_asc':
                $query->orderByRaw('COALESCE(discount_price, price) ASC');
                break;
            case 'price_desc':
                $query->orderByRaw('COALESCE(discount_price, price) DESC');
                break;
            case 'newest':
                $query->latest();
                break;
            case 'featured':
                $query->where('is_featured', true)->latest();
                break;
            case 'popularity':
            default:
                // Sort by views desc, then latest
                $query->orderBy('views', 'desc')->latest();
                break;
        }

        $products = $query->take($limit)->get();

        return $products->map(fn ($p) => $this->formatProductForAssistant($p))->values()->all();
    }

    /**
     * Find best selling / popular products.
     */
    public function getBestSellers(int $limit = 6): array
    {
        return $this->search([
            'sort' => 'popularity',
            'in_stock_only' => true,
            'limit' => $limit,
        ]);
    }

    /**
     * Find featured products.
     */
    public function getFeatured(int $limit = 6): array
    {
        return $this->search([
            'sort' => 'featured',
            'in_stock_only' => true,
            'limit' => $limit,
        ]);
    }

    /**
     * Find available categories for quick browsing.
     */
    public function getCategories(int $limit = 10): array
    {
        return Category::query()
            ->withCount(['products' => function ($q) {
                $q->active()->where('stock', '>', 0);
            }])
            ->having('products_count', '>', 0)
            ->orderBy('products_count', 'desc')
            ->take($limit)
            ->get(['id', 'name', 'slug', 'image'])
            ->toArray();
    }

    /**
     * Find alternatives for an out-of-stock product.
     */
    public function getAlternatives(int $productId, int $limit = 3): array
    {
        $product = Product::find($productId);
        if (!$product) {
            return [];
        }

        return Product::query()
            ->active()
            ->where('id', '!=', $productId)
            ->where('category_id', $product->category_id)
            ->where('stock', '>', 0)
            ->whereHas('vendor', fn($q) => $q->where('status', 'active'))
            ->with([
                'vendor:id,shop_name,slug,logo,shipping_price',
                'category:id,name,slug',
                'primaryImage:id,product_id,image_path,is_primary',
            ])
            ->take($limit)
            ->get()
            ->map(fn ($p) => $this->formatProductForAssistant($p))
            ->values()
            ->all();
    }

    /**
     * Compare two or more products by ID or slug.
     */
    public function compareProducts(array $productIds): array
    {
        $products = Product::query()
            ->active()
            ->whereIn('id', $productIds)
            ->with([
                'vendor:id,shop_name,slug,shipping_price',
                'category:id,name,slug',
                'primaryImage:id,product_id,image_path,is_primary',
            ])
            ->get();

        return $products->map(fn ($p) => $this->formatProductForAssistant($p))->values()->all();
    }

    /**
     * Cleanly format product model for safe API & chatbot consumption.
     *
     * @param Product|mixed $p
     */
    public function formatProductForAssistant(mixed $p): array
    {
        $primaryImg = $p->primaryImage ? $p->primaryImage->image_path : null;
        if ($primaryImg) {
            if (!str_starts_with($primaryImg, 'http')) {
                $trimmed = ltrim($primaryImg, '/');
                $primaryImg = str_starts_with($trimmed, 'uploads/') ? '/' . $trimmed : '/uploads/' . $trimmed;
            }
        }

        $currentPrice = $p->discount_price ?? $p->price;
        $hasDiscount = $p->discount_price !== null && $p->discount_price < $p->price;
        $discountPercent = $hasDiscount && $p->price > 0 
            ? round((($p->price - $p->discount_price) / $p->price) * 100) 
            : 0;

        return [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'url' => route('products.show', $p->slug),
            'description' => \Illuminate\Support\Str::limit(strip_tags($p->description ?? ''), 160),
            'price' => (float) $p->price,
            'discount_price' => $p->discount_price ? (float) $p->discount_price : null,
            'current_price' => (float) $currentPrice,
            'currency' => AssistantSetting::get('currency', config('shopping_assistant.currency', 'DT')),
            'has_discount' => $hasDiscount,
            'discount_percent' => $discountPercent,
            'stock' => (int) $p->stock,
            'in_stock' => $p->stock > 0,
            'sku' => $p->sku,
            'image' => $primaryImg,
            'vendor_id' => $p->vendor_id,
            'vendor_name' => $p->vendor ? $p->vendor->shop_name : 'Boutique Partenaire',
            'vendor_shipping' => $p->vendor ? (float) $p->vendor->shipping_price : 7.00,
            'category_name' => $p->category ? $p->category->name : 'Général',
            'category_slug' => $p->category ? $p->category->slug : null,
            'specifications' => $p->specifications ?? [],
        ];
    }
}
