<?php

namespace App\Services\ShoppingAssistant;

use App\Models\AssistantSetting;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartIntegrationService
{
    /**
     * Retrieve or create active cart for the authenticated user or session.
     */
    public function getOrCreateCart(): Cart
    {
        if (Auth::check()) {
            $userCart = Cart::firstOrCreate(['user_id' => Auth::id()]);
            // If there's an existing session cart, merge it into the user's cart
            $sessionId = session()->getId();
            $sessionCart = Cart::where('session_id', $sessionId)->first();
            if ($sessionCart && $sessionCart->id !== $userCart->id) {
                foreach ($sessionCart->items as $sessionItem) {
                    $existingItem = $userCart->items()->where('product_id', $sessionItem->product_id)->first();
                    if ($existingItem) {
                        $existingItem->update([
                            'quantity' => $existingItem->quantity + $sessionItem->quantity,
                        ]);
                    } else {
                        $sessionItem->update(['cart_id' => $userCart->id]);
                    }
                }
                $sessionCart->delete();
            }
            return $userCart;
        }

        $sessionId = session()->getId();
        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Get formatted cart summary.
     */
    public function getCartSummary(): array
    {
        $cart = $this->getOrCreateCart();
        $cart->load(['items.product.vendor', 'items.product.primaryImage']);

        $items = [];
        $subtotal = 0;
        $totalItems = 0;
        $vendorGroups = [];

        foreach ($cart->items as $item) {
            $p = $item->product;
            if (!$p) {
                continue;
            }

            // Always recalculate based on authoritative server price
            $realPrice = (float) ($p->discount_price ?? $p->price);
            $itemTotal = $realPrice * $item->quantity;
            $subtotal += $itemTotal;
            $totalItems += $item->quantity;

            $primaryImg = $p->primaryImage ? $p->primaryImage->image_path : null;
            if ($primaryImg) {
                if (!str_starts_with($primaryImg, 'http')) {
                    $trimmed = ltrim($primaryImg, '/');
                    $primaryImg = str_starts_with($trimmed, 'uploads/') ? '/' . $trimmed : '/uploads/' . $trimmed;
                }
            }

            $vendorId = $p->vendor_id;
            $vendorName = $p->vendor ? $p->vendor->shop_name : 'Boutique';

            $formattedItem = [
                'id' => $item->id,
                'product_id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => $realPrice,
                'quantity' => $item->quantity,
                'item_total' => round($itemTotal, 2),
                'stock' => $p->stock,
                'image' => $primaryImg,
                'vendor_id' => $vendorId,
                'vendor_name' => $vendorName,
            ];

            $items[] = $formattedItem;

            if (!isset($vendorGroups[$vendorId])) {
                $vendorGroups[$vendorId] = [
                    'vendor_id' => $vendorId,
                    'vendor_name' => $vendorName,
                    'checkout_url' => route('orders.checkout', $vendorId),
                    'items_count' => 0,
                    'vendor_subtotal' => 0,
                ];
            }
            $vendorGroups[$vendorId]['items_count'] += $item->quantity;
            $vendorGroups[$vendorId]['vendor_subtotal'] += $itemTotal;
        }

        $currency = AssistantSetting::get('currency', config('shopping_assistant.currency', 'DT'));

        return [
            'cart_id' => $cart->id,
            'items' => $items,
            'total_items' => $totalItems,
            'subtotal' => round($subtotal, 2),
            'currency' => $currency,
            'formatted_subtotal' => sprintf('%.2f %s', $subtotal, $currency),
            'cart_url' => route('cart.index'),
            'vendor_groups' => array_values($vendorGroups),
            'is_empty' => empty($items),
        ];
    }

    /**
     * Add product to cart with strict server-side validation.
     */
    public function addToCart(int $productId, int $quantity = 1): array
    {
        if ($quantity < 1) {
            return [
                'success' => false,
                'message' => 'La quantité demandée doit être au moins de 1.',
            ];
        }

        $product = Product::active()->with('vendor')->find($productId);

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Ce produit est introuvable ou indisponible.',
            ];
        }

        if ($product->vendor && $product->vendor->status !== 'active') {
            return [
                'success' => false,
                'message' => 'La boutique de ce vendeur est temporairement suspendue.',
            ];
        }

        $cart = $this->getOrCreateCart();
        $cartItem = $cart->items()->where('product_id', $product->id)->first();
        $currentInCart = $cartItem ? $cartItem->quantity : 0;
        $newQuantity = $currentInCart + $quantity;

        // Check stock availability
        if ($product->stock < $newQuantity) {
            return [
                'success' => false,
                'message' => sprintf(
                    'Stock insuffisant pour "%s". Stock restant: %d (déjà %d dans votre panier).',
                    $product->name,
                    $product->stock,
                    $currentInCart
                ),
            ];
        }

        // Authoritative server price
        $serverPrice = $product->discount_price ?? $product->price;

        if ($cartItem) {
            $cartItem->update([
                'quantity' => $newQuantity,
                'price' => $serverPrice,
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $serverPrice,
            ]);
        }

        return [
            'success' => true,
            'message' => sprintf('"%s" (x%d) a été ajouté à votre panier avec succès !', $product->name, $quantity),
            'cart' => $this->getCartSummary(),
            'added_product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $serverPrice,
            ],
        ];
    }

    /**
     * Update quantity of an item in the cart.
     */
    public function updateItem(int $cartItemId, int $quantity): array
    {
        $cart = $this->getOrCreateCart();
        $item = $cart->items()->where('id', $cartItemId)->first();

        if (!$item) {
            return ['success' => false, 'message' => 'Article introuvable dans votre panier.'];
        }

        if ($quantity <= 0) {
            $item->delete();
            return [
                'success' => true,
                'message' => 'Article retiré du panier.',
                'cart' => $this->getCartSummary(),
            ];
        }

        $product = $item->product;
        if (!$product || $product->stock < $quantity) {
            return [
                'success' => false,
                'message' => sprintf('Stock insuffisant (stock disponible: %d).', $product ? $product->stock : 0),
            ];
        }

        $item->update(['quantity' => $quantity]);

        return [
            'success' => true,
            'message' => 'Panier mis à jour.',
            'cart' => $this->getCartSummary(),
        ];
    }

    /**
     * Remove item from cart.
     */
    public function removeItem(int $cartItemId): array
    {
        $cart = $this->getOrCreateCart();
        $item = $cart->items()->where('id', $cartItemId)->first();

        if ($item) {
            $item->delete();
        }

        return [
            'success' => true,
            'message' => 'Article supprimé de votre panier.',
            'cart' => $this->getCartSummary(),
        ];
    }
}
