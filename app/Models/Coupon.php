<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'type', // 'percent' or 'fixed'
        'value',
        'min_order_amount',
        'max_discount',
        'vendor_id',
        'usage_limit',
        'usage_limit_per_user',
        'times_used',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'times_used' => 'integer',
        'usage_limit' => 'integer',
        'usage_limit_per_user' => 'integer',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Check if the coupon is valid for a given order subtotal and user.
     */
    public function isValid(float $subtotal, ?int $userId = null, ?int $vendorId = null): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => __('Ce code promo est inactif.')];
        }

        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) {
            return ['valid' => false, 'message' => __('Ce code promo n\'est pas encore actif.')];
        }

        if ($this->expires_at && $now->gt($this->expires_at)) {
            return ['valid' => false, 'message' => __('Ce code promo a expiré.')];
        }

        if ($this->usage_limit !== null && $this->times_used >= $this->usage_limit) {
            return ['valid' => false, 'message' => __('Ce code promo a atteint sa limite d\'utilisation.')];
        }

        if ($this->min_order_amount !== null && $subtotal < $this->min_order_amount) {
            return [
                'valid' => false,
                'message' => __('Le montant minimum requis pour ce coupon est de :min DT.', ['min' => number_format((float) $this->min_order_amount, 2)])
            ];
        }

        if ($this->vendor_id !== null && $vendorId !== null && (int)$this->vendor_id !== (int)$vendorId) {
            return ['valid' => false, 'message' => __('Ce code promo est réservé à une boutique spécifique.')];
        }

        if ($userId && $this->usage_limit_per_user !== null) {
            $userUsage = $this->usages()->where('user_id', $userId)->count();
            if ($userUsage >= $this->usage_limit_per_user) {
                return ['valid' => false, 'message' => __('Vous avez déjà utilisé ce code promo.')];
            }
        }

        return ['valid' => true, 'message' => __('Code promo valide !')];
    }

    /**
     * Calculate discount amount from a subtotal.
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'percent') {
            $discount = round(($subtotal * (float)$this->value) / 100, 2);
            if ($this->max_discount !== null && $discount > (float)$this->max_discount) {
                $discount = (float)$this->max_discount;
            }
        } else {
            $discount = (float)$this->value;
        }

        return min($discount, $subtotal);
    }
}
