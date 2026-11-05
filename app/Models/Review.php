<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'title',
        'comment',
        'images',
        'is_approved',
        'verified_purchase',
        'vendor_reply',
        'vendor_replied_at',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'verified_purchase' => 'boolean',
        'images' => 'array',
        'vendor_replied_at' => 'datetime',
        'rating' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('verified_purchase', true);
    }
}
