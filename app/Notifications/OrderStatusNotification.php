<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderStatusNotification extends Notification
{
    use Queueable;

    public Order $order;
    public string $oldStatus;
    public string $newStatus;

    public function __construct(Order $order, string $oldStatus, string $newStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $statusLabels = [
            'pending' => 'En attente',
            'processing' => 'Confirmée & En préparation',
            'shipped' => 'Expédiée',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
        ];

        $label = $statusLabels[$this->newStatus] ?? ucfirst($this->newStatus);

        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->newStatus,
            'title' => "Mise à jour de votre commande {$this->order->order_number}",
            'message' => "Le statut de votre commande est maintenant : {$label}.",
            'url' => route('orders.track', ['order' => $this->order->order_number]),
            'icon' => match ($this->newStatus) {
                'shipped' => 'truck',
                'delivered' => 'check-circle',
                'cancelled' => 'x-circle',
                default => 'package',
            },
        ];
    }
}
