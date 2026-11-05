<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient',
        'message',
        'status',
        'recipients_count',
        'error_message',
        'response_payload',
        'sender_admin_id',
    ];

    protected $casts = [
        'response_payload' => 'array',
        'recipients_count' => 'integer',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_admin_id');
    }
}
