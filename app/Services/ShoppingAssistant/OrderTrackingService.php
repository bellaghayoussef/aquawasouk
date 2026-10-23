<?php

namespace App\Services\ShoppingAssistant;

use App\Models\AssistantSetting;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderTrackingService
{
    /**
     * Look up order safely based on authenticated user or guest credentials.
     */
    public function trackOrder(string $orderNumber, ?string $verificationPhone = null): array
    {
        $cleanNumber = trim($orderNumber);
        if (empty($cleanNumber)) {
            return [
                'found' => false,
                'message' => 'Veuillez renseigner un numéro de commande valide (ex: ORD-XXXXXX).',
            ];
        }

        $query = Order::query()->where(function ($q) use ($cleanNumber) {
            $q->where('order_number', $cleanNumber);
            if (is_numeric($cleanNumber)) {
                $q->orWhere('id', (int) $cleanNumber);
            }
        });

        // If user is authenticated, enforce strict user ownership
        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            // For guests, require phone verification to prevent IDOR / enumeration
            if (empty($verificationPhone)) {
                return [
                    'found' => false,
                    'requires_verification' => true,
                    'message' => "Pour des raisons de sécurité et de confidentialité, veuillez fournir votre numéro de téléphone associé à cette commande.",
                ];
            }

            $cleanPhone = preg_replace('/[^0-9]/', '', $verificationPhone);
            $query->where(function ($q) use ($cleanPhone) {
                $q->whereRaw("REPLACE(REPLACE(phone, ' ', ''), '-', '') LIKE ?", ["%{$cleanPhone}%"]);
            });
        }

        $order = $query->with(['items.product.vendor'])->first();

        if (!$order) {
            return [
                'found' => false,
                'message' => Auth::check()
                    ? "Aucune commande avec cette référence n'a été trouvée dans votre compte."
                    : "Commande introuvable ou le numéro de téléphone ne correspond pas.",
            ];
        }

        $statusLabels = [
            'pending' => ['fr' => 'En attente de confirmation', 'ar' => 'في انتظار التأكيد', 'en' => 'Pending confirmation', 'color' => 'amber'],
            'processing' => ['fr' => 'En cours de préparation chez le vendeur', 'ar' => 'قيد التجهيز لدى البائع', 'en' => 'Processing by vendor', 'color' => 'blue'],
            'shipped' => ['fr' => 'Expédiée / En cours de livraison', 'ar' => 'تم الشحن / في الطريق إليك', 'en' => 'Shipped / In transit', 'color' => 'indigo'],
            'delivered' => ['fr' => 'Livrée avec succès', 'ar' => 'تم التسليم بنجاح', 'en' => 'Delivered successfully', 'color' => 'emerald'],
            'cancelled' => ['fr' => 'Annulée', 'ar' => 'ملغاة', 'en' => 'Cancelled', 'color' => 'rose'],
        ];

        $statusInfo = $statusLabels[$order->status] ?? [
            'fr' => ucfirst($order->status),
            'ar' => $order->status,
            'en' => ucfirst($order->status),
            'color' => 'slate',
        ];

        $itemsSummary = [];
        foreach ($order->items as $item) {
            $itemsSummary[] = [
                'name' => $item->product ? $item->product->name : 'Produit',
                'quantity' => $item->quantity,
                'vendor_name' => $item->vendor ? $item->vendor->shop_name : 'Boutique',
                'price' => (float) $item->price,
            ];
        }

        $currency = AssistantSetting::get('currency', config('shopping_assistant.currency', 'DT'));

        return [
            'found' => true,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => $statusInfo['fr'],
            'status_label_ar' => $statusInfo['ar'],
            'status_color' => $statusInfo['color'],
            'date' => $order->created_at ? $order->created_at->format('d/m/Y H:i') : null,
            'city' => $order->shipping_city,
            'total_amount' => (float) $order->total_amount,
            'shipping_cost' => (float) $order->shipping_cost,
            'payment_status' => $order->payment_status === 'paid' ? 'Payé' : 'Paiement à la livraison (COD)',
            'currency' => $currency,
            'items' => $itemsSummary,
            'details_url' => Auth::check() ? route('orders.show', $order->id) : null,
        ];
    }

    /**
     * Get recent orders for current authenticated user.
     */
    public function getRecentUserOrders(int $limit = 3): array
    {
        if (!Auth::check()) {
            return [];
        }

        $orders = Order::where('user_id', Auth::id())
            ->with(['items.product'])
            ->latest()
            ->take($limit)
            ->get();

        $currency = AssistantSetting::get('currency', config('shopping_assistant.currency', 'DT'));

        return $orders->map(function ($o) use ($currency) {
            return [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'status' => $o->status,
                'total_amount' => (float) $o->total_amount,
                'currency' => $currency,
                'date' => $o->created_at->format('d/m/Y'),
                'items_count' => $o->items->sum('quantity'),
                'url' => route('orders.show', $o->id),
            ];
        })->all();
    }
}
