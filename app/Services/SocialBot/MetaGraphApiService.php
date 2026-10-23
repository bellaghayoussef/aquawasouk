<?php

namespace App\Services\SocialBot;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaGraphApiService
{
    /**
     * Send text message back to Facebook Messenger or Instagram Direct
     */
    public static function sendTextMessage(string $senderId, string $message, string $channel = 'facebook'): bool
    {
        // Don't call Meta for internal simulator
        if ($channel === 'simulator') {
            return true;
        }

        $token = self::getAccessToken($channel);
        if (empty($token)) {
            Log::info("MetaGraphApiService: No access token configured for channel '{$channel}'. Message logged for {$senderId}: {$message}");
            return false;
        }

        try {
            $response = Http::timeout(10)->post("https://graph.facebook.com/v21.0/me/messages", [
                'access_token' => $token,
                'recipient' => [
                    'id' => $senderId,
                ],
                'message' => [
                    'text' => $message,
                ],
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error("Meta Graph API error for sender {$senderId}: " . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error("Meta Graph API request exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get configured token from environment or database settings
     */
    public static function getAccessToken(string $channel = 'facebook'): ?string
    {
        if ($channel === 'instagram') {
            return config('services.meta.instagram_access_token') 
                ?? env('INSTAGRAM_ACCESS_TOKEN', env('META_PAGE_ACCESS_TOKEN'));
        }
        return config('services.meta.facebook_page_token') 
            ?? env('FACEBOOK_PAGE_ACCESS_TOKEN', env('META_PAGE_ACCESS_TOKEN'));
    }

    /**
     * Get webhook verification token
     */
    public static function getVerifyToken(): string
    {
        return config('services.meta.verify_token')
            ?? env('META_WEBHOOK_VERIFY_TOKEN', 'aquawasouk_social_token_2026');
    }
}
