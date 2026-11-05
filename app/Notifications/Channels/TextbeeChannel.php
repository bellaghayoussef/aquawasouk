<?php

namespace App\Notifications\Channels;

use App\Services\SmsService;
use Illuminate\Notifications\Notification;

class TextbeeChannel
{
    public function __construct(private SmsService $sms)
    {
    }

    public function send(mixed $notifiable, Notification $notification): void
    {
        $phone = $notifiable->routeNotificationFor('textbee')
            ?? $notifiable->phone
            ?? null;

        if (!$phone) {
            return;
        }

        if (method_exists($notification, 'toTextbee')) {
            $message = $notification->toTextbee($notifiable);
            if (!empty($message)) {
                $this->sms->send($phone, $message);
            }
        }
    }
}
