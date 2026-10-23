<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'name',
        'slug',
        'description',
        'specifications',
        'price',
        'discount_price',
        'stock',
        'sku',
        'is_featured',
        'status',
        'views',
    ];

    protected $casts = [
        'specifications' => 'array',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (!$product->slug) {
                $product->slug = Str::slug($product->name);
            }
            if (!$product->sku) {
                $product->sku = Str::random(10);
            }
        });
    }

    // Relationships
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class)->orderBy('id');
    }

    public function activeVariations()
    {
        return $this->hasMany(ProductVariation::class)->where('status', 'active');
    }

    public function hasVariations(): bool
    {
        return $this->variations()->exists();
    }

    public function syncTotalStock(): void
    {
        if ($this->hasVariations()) {
            $this->update(['stock' => $this->variations()->sum('stock')]);
        }
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Helpers
    public function getCurrentPriceAttribute()
    {
        return $this->discount_price ?? $this->price;
    }

    public function hasDiscount()
    {
        return $this->discount_price !== null && $this->discount_price < $this->price;
    }

    public function getDiscountPercentageAttribute()
    {
        if (!$this->hasDiscount()) {
            return 0;
        }
        return round((($this->price - $this->discount_price) / $this->price) * 100);
    }
}
