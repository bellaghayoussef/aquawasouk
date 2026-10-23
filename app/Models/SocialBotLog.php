<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialBotLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel',
        'sender_id',
        'direction',
        'message',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
