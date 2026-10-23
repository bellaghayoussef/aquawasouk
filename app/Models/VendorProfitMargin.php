<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorProfitMargin extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'min_order_price',
        'max_order_price',
        'margin_amount',
    ];

    protected $casts = [
        'min_order_price' => 'decimal:2',
        'max_order_price' => 'decimal:2',
        'margin_amount' => 'decimal:2',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
