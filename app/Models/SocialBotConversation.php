<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialBotConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel',
        'sender_id',
        'sender_name',
        'step',
        'pending_order_data',
        'last_product_id',
        'last_message_at',
    ];

    protected $casts = [
        'pending_order_data' => 'array',
        'last_message_at' => 'datetime',
    ];

    public function lastProduct()
    {
        return $this->belongsTo(Product::class, 'last_product_id');
    }

    /**
     * Helper to update pending order field
     */
    public function setPendingData(string $key, mixed $value): void
    {
        $data = $this->pending_order_data ?? [];
        $data[$key] = $value;
        $this->update(['pending_order_data' => $data]);
    }

    public function getPendingData(?string $key = null, mixed $default = null): mixed
    {
        $data = $this->pending_order_data ?? [];
        if ($key === null) {
            return $data;
        }
        return $data[$key] ?? $default;
    }

    public function resetOrder(): void
    {
        $this->update([
            'step' => 'browsing',
            'pending_order_data' => null,
        ]);
    }
}
