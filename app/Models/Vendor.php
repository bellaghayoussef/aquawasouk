<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shop_name',
        'slug',
        'description',
        'logo',
        'banner',
        'phone',
        'address',
        'city',
        'country',
        'postal_code',
        'status',
        'commission_rate',
        'shipping_price',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'shipping_price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vendor) {
            if (!$vendor->slug) {
                $vendor->slug = Str::slug($vendor->shop_name);
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function profitMargins()
    {
        return $this->hasMany(VendorProfitMargin::class)->orderBy('min_order_price', 'asc');
    }

    public function payouts()
    {
        return $this->hasMany(VendorPayout::class)->latest('payout_date')->latest('id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Calculate admin profit margin in TND based on order/item total amount
     */
    public function calculateAdminMargin(float $orderAmount): float
    {
        $rule = $this->profitMargins()
            ->where('min_order_price', '<=', $orderAmount)
            ->where(function ($query) use ($orderAmount) {
                $query->whereNull('max_order_price')
                      ->orWhere('max_order_price', '>=', $orderAmount);
            })
            ->orderBy('min_order_price', 'desc')
            ->first();

        if ($rule) {
            return (float) $rule->margin_amount;
        }

        // Fallback to percentage commission if configured
        if ($this->commission_rate > 0) {
            return round(($orderAmount * (float)$this->commission_rate) / 100, 2);
        }

        return 0.00;
    }

    /**
     * Total revenue collected by vendor from delivered orders only (Cash on Delivery)
     */
    public function getTotalSales(): float
    {
        return (float) $this->orderItems()
            ->whereHas('order', function ($q) {
                $q->where('status', 'delivered');
            })
            ->selectRaw('SUM(price * quantity) as total')
            ->value('total') ?? 0.00;
    }

    /**
     * Total admin profit margin due from DELIVERED orders only (retirable profit)
     */
    public function getTotalAdminProfit(): float
    {
        return (float) $this->orderItems()
            ->whereHas('order', function ($q) {
                $q->where('status', 'delivered');
            })
            ->sum('commission') ?? 0.00;
    }

    /**
     * Pending admin profit margin from non-delivered orders (en cours de livraison)
     */
    public function getPendingAdminProfit(): float
    {
        return (float) $this->orderItems()
            ->whereHas('order', function ($q) {
                $q->whereNotIn('status', ['delivered', 'cancelled']);
            })
            ->sum('commission') ?? 0.00;
    }

    /**
     * Net revenue due to vendor from delivered orders (Total Delivered Sales - Admin Profit)
     */
    public function getVendorNetEarnings(): float
    {
        return max(0.00, $this->getTotalSales() - $this->getTotalAdminProfit());
    }

    /**
     * Total profit withdrawals / payments collected by admin from this vendor
     */
    public function getTotalPayouts(): float
    {
        return (float) $this->payouts()->sum('amount') ?? 0.00;
    }

    /**
     * Remaining profit margin that this vendor still owes to admin
     * (Total Admin Profit - Total Retraits encaissés par l'admin)
     */
    public function getRemainingProfitDue(): float
    {
        return round($this->getTotalAdminProfit() - $this->getTotalPayouts(), 2);
    }

    /**
     * Remaining cash balance of profit owed by vendor to admin
     */
    public function getCashBalance(): float
    {
        return $this->getRemainingProfitDue();
    }
}
