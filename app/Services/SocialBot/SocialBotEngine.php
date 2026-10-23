<?php

namespace App\Services\SocialBot;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\SocialBotConversation;
use App\Models\SocialBotLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SocialBotEngine
{
    protected static array $governorates = [
        'Tunis', 'Ariana', 'Ben Arous', 'Manouba', 'Nabeul', 'Zaghouan',
        'Bizerte', 'Béja', 'Jendouba', 'Le Kef', 'Siliana', 'Sousse',
        'Monastir', 'Mahdia', 'Sfax', 'Kairouan', 'Kasserine', 'Sidi Bouzid',
        'Gabès', 'Medenine', 'Tataouine', 'Gafsa', 'Tozeur', 'Kebili'
    ];

    /**
     * Process an incoming message from a customer and return the bot's response.
     */
    public function handle(string $channel, string $senderId, string $messageText, ?string $senderName = null): string
    {
        $text = trim($messageText);

        // 1. Get or create conversation record
        $conversation = SocialBotConversation::firstOrCreate(
            [
                'channel' => $channel,
                'sender_id' => $senderId,
            ],
            [
                'sender_name' => $senderName,
                'step' => 'browsing',
                'last_message_at' => now(),
            ]
        );

        if ($senderName && !$conversation->sender_name) {
            $conversation->update(['sender_name' => $senderName]);
        }

        // 2. Log inbound message
        SocialBotLog::create([
            'channel' => $channel,
            'sender_id' => $senderId,
            'direction' => 'inbound',
            'message' => $text,
        ]);

        // 3. Process through state machine
        $reply = $this->processStateMachine($conversation, $text);

        // 4. Update last activity
        $conversation->update(['last_message_at' => now()]);

        // 5. Log outbound message
        SocialBotLog::create([
            'channel' => $channel,
            'sender_id' => $senderId,
            'direction' => 'outbound',
            'message' => $reply,
        ]);

        return $reply;
    }

    /**
     * The core conversational state machine.
     */
    protected function processStateMachine(SocialBotConversation $conv, string $text): string
    {
        $normalized = mb_strtolower(trim($text));

        // Global Reset / Cancel command
        if (in_array($normalized, ['annuler', 'cancel', 'batal', 'le', 'non', 'start over', 'recommencer'])) {
            $conv->resetOrder();
            return "❌ Opération annulée avec succès.\nComment puis-je vous aider ? Vous pouvez rechercher un produit ou poser une question sur nos boutiques.";
        }

        switch ($conv->step) {
            case 'collecting_product':
                return $this->handleCollectingProduct($conv, $text);

            case 'collecting_variant':
                return $this->handleCollectingVariant($conv, $text);

            case 'collecting_name':
                return $this->handleCollectingName($conv, $text);

            case 'collecting_phone':
                return $this->handleCollectingPhone($conv, $text);

            case 'collecting_city':
                return $this->handleCollectingCity($conv, $text);

            case 'awaiting_confirmation':
                return $this->handleAwaitingConfirmation($conv, $text);

            case 'browsing':
            default:
                return $this->handleBrowsing($conv, $text);
        }
    }

    /**
     * Handle browsing, search queries and intent detection.
     */
    protected function handleBrowsing(SocialBotConversation $conv, string $text): string
    {
        $normalized = mb_strtolower(trim($text));

        // Greetings only (French & Darija)
        if (preg_match('/^(aslema|ahla|salem|bonjour|bonsoir|salam|coucou|hi|hello)(\s+bik)?[\.!\?]*$/i', $normalized)) {
            $name = $conv->sender_name ? " {$conv->sender_name}" : "";
            return "👋 Marhba bik{$name} sur AquawaSouk !\nJe suis votre assistant shopping intelligent. Vous pouvez me demander un produit, son prix, ou commander directement ici par message !\n\nQuel produit recherchez-vous aujourd'hui ?";
        }

        // Strip greetings to see core intent
        $workingText = trim(preg_replace('/\b(aslema|ahla|salem|bonjour|bonsoir|salam|coucou|hi|hello)(\s+bik)?\b/iu', '', $text));
        if (empty($workingText)) {
            $workingText = $text;
        }

        // Direct Order Intent ("nheb nechri", "commander", "je veux commander", "ab3athli")
        if (preg_match('/\b(commander|nheb ncomandi|nheb nechri|ab3athli|je veux commander|passe commande|acheter|comandi)\b/i', $workingText)) {
            $productQuery = trim(preg_replace('/\b(commander|nheb ncomandi|nheb nechri|ab3athli|je veux commander|passe commande|acheter|comandi)\b/iu', '', $workingText));

            // If product name is in the message itself (e.g. "nheb nechri Guppy")
            if (mb_strlen($productQuery) >= 2) {
                $matched = Product::with(['vendor', 'variations'])
                    ->active()
                    ->where('name', 'like', "%{$productQuery}%")
                    ->first();

                if ($matched) {
                    $conv->setPendingData('product_id', $matched->id);
                    $conv->setPendingData('quantity', 1);
                    $conv->update(['last_product_id' => $matched->id]);
                    return $this->transitionToVariantOrCustomerInfo($conv, $matched);
                }
            }

            // If user previously browsed a product
            if ($conv->last_product_id && ($product = Product::with('variations')->find($conv->last_product_id))) {
                $conv->setPendingData('product_id', $product->id);
                $conv->setPendingData('quantity', 1);

                return $this->transitionToVariantOrCustomerInfo($conv, $product);
            }

            // No product specified and no prior product -> Ask which product
            $conv->update(['step' => 'collecting_product']);
            return "🛍️ Avec grand plaisir ! Quel article souhaitez-vous commander ? (Donnez-moi le nom du produit ou sa référence)";
        }

        // Product Search & Price Queries
        // Strip out generic search words
        $cleanSearch = trim(preg_replace('/\b(b9adech|soum|prix|combien|fama|3andkom|disponible|avez-vous|cherche|recherche)\b/iu', '', $workingText));
        $searchQuery = mb_strlen($cleanSearch) >= 2 ? $cleanSearch : $workingText;

        $products = Product::with(['vendor', 'primaryImage', 'variations'])
            ->active()
            ->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', "%{$searchQuery}%")
                  ->orWhere('description', 'like', "%{$searchQuery}%");
            })
            ->limit(3)
            ->get();

        if ($products->isNotEmpty()) {
            $mainProduct = $products->first();
            $conv->update(['last_product_id' => $mainProduct->id]);

            $response = "🔍 Voici ce que nous avons trouvé pour vous :\n\n";
            foreach ($products as $p) {
                $price = $p->discount_price > 0 ? $p->discount_price : $p->price;
                $oldPrice = $p->discount_price > 0 ? " (au lieu de {$p->price} DT)" : "";
                $stockStatus = $p->stock > 0 ? "✅ En stock" : "⚠️ Rupture temporaire";
                $vendorName = $p->vendor ? $p->vendor->shop_name : "AquawaSouk";

                $response .= "📦 **{$p->name}**\n";
                $response .= "💰 Prix : **{$price} DT**{$oldPrice}\n";
                $response .= "🏪 Boutique : {$vendorName} | {$stockStatus}\n\n";
            }

            $response .= "👉 Pour commander immédiatement cet article et payer à la livraison, répondez simplement **Commander** !";
            return $response;
        }

        // Fallback friendly message
        return "Je suis à votre écoute ! Vous pouvez rechercher un article (ex: *Samsung S24*, *Baskets Nike*, *Machine à Café*), ou envoyer **Commander** pour passer commande.";
    }

    /**
     * Handle product name input.
     */
    protected function handleCollectingProduct(SocialBotConversation $conv, string $text): string
    {
        $product = Product::with(['vendor', 'variations'])
            ->active()
            ->where('name', 'like', "%{$text}%")
            ->first();

        if (!$product) {
            return "Désolé, je n'ai pas trouvé de produit correspondant à \"{$text}\".\nPouvez-vous vérifier le nom ou choisir parmi nos catégories ?";
        }

        $conv->setPendingData('product_id', $product->id);
        $conv->setPendingData('quantity', 1);
        $conv->update(['last_product_id' => $product->id]);

        return $this->transitionToVariantOrCustomerInfo($conv, $product);
    }

    /**
     * Check if product has variations (sizes / colors) and ask or proceed to name.
     */
    protected function transitionToVariantOrCustomerInfo(SocialBotConversation $conv, Product $product): string
    {
        $variations = $product->variations()->where('status', 'active')->where('stock', '>', 0)->get();

        if ($variations->isNotEmpty()) {
            $conv->update(['step' => 'collecting_variant']);

            $sizes = $variations->pluck('size')->filter()->unique()->values();
            $colors = $variations->pluck('color')->filter()->unique()->values();

            $msg = "✨ Choix de votre déclinaison pour **{$product->name}** :\n";
            if ($sizes->isNotEmpty()) {
                $msg .= "📏 Tailles disponibles : " . $sizes->implode(', ') . "\n";
            }
            if ($colors->isNotEmpty()) {
                $msg .= "🎨 Couleurs disponibles : " . $colors->implode(', ') . "\n";
            }
            $msg .= "\nIndiquez-moi votre taille et/ou couleur souhaitée :";
            return $msg;
        }

        // No variations needed -> ask for customer name
        $conv->update(['step' => 'collecting_name']);
        return "📦 Produit sélectionné : **{$product->name}**\n\nPour préparer votre livraison, quel est votre **Nom et Prénom** ?";
    }

    /**
     * Handle variant selection.
     */
    protected function handleCollectingVariant(SocialBotConversation $conv, string $text): string
    {
        $productId = $conv->getPendingData('product_id');
        $product = Product::with('variations')->find($productId);

        if (!$product) {
            $conv->resetOrder();
            return "Une erreur est survenue avec le produit. Veuillez recommencer en tapant 'Commander'.";
        }

        // Try to match size or color from input text
        $variations = $product->variations()->where('status', 'active')->where('stock', '>', 0)->get();
        $matchedVariation = null;

        foreach ($variations as $var) {
            if ($var->size && mb_stripos($text, $var->size) !== false) {
                $matchedVariation = $var;
                break;
            }
            if ($var->color && mb_stripos($text, $var->color) !== false) {
                $matchedVariation = $var;
                break;
            }
        }

        if ($matchedVariation) {
            $conv->setPendingData('variation_id', $matchedVariation->id);
            $conv->setPendingData('size', $matchedVariation->size);
            $conv->setPendingData('color', $matchedVariation->color);
        } else {
            // Save as raw text choice
            $conv->setPendingData('size', trim($text));
        }

        $conv->update(['step' => 'collecting_name']);
        return "C'est noté ! ✅\n\nPour la livraison de votre colis, quel est votre **Nom et Prénom** complet ?";
    }

    /**
     * Handle customer full name input.
     */
    protected function handleCollectingName(SocialBotConversation $conv, string $text): string
    {
        $name = trim(strip_tags($text));
        if (mb_strlen($name) < 3) {
            return "Veuillez saisir votre nom et prénom complet s'il vous plaît :";
        }

        $conv->setPendingData('customer_name', $name);
        $conv->update(['step' => 'collecting_phone']);

        return "Merci {$name} ! 👍\n\nQuel est votre **Numéro de téléphone** joignable pour que le livreur vous contacte ? (8 chiffres en Tunisie)";
    }

    /**
     * Handle phone number validation.
     */
    protected function handleCollectingPhone(SocialBotConversation $conv, string $text): string
    {
        // Extract 8 digits
        $cleanPhone = preg_replace('/[^0-9]/', '', $text);
        if (str_starts_with($cleanPhone, '216') && strlen($cleanPhone) === 11) {
            $cleanPhone = substr($cleanPhone, 3);
        }

        if (strlen($cleanPhone) !== 8 || !in_array($cleanPhone[0], ['2', '4', '5', '9'])) {
            return "⚠️ Veuillez fournir un numéro de téléphone tunisien valide à 8 chiffres (commençant par 2, 4, 5 ou 9).\nExemple : 98 123 456";
        }

        $conv->setPendingData('phone', $cleanPhone);
        $conv->update(['step' => 'collecting_city']);

        return "Parfait ! 📞 {$cleanPhone} enregistré.\n\nDans quel **Gouvernorat et Ville** souhaitez-vous être livré ? (Indiquez également votre adresse exacte : Rue, Bâtiment, etc.)";
    }

    /**
     * Handle governorate and address input.
     */
    protected function handleCollectingCity(SocialBotConversation $conv, string $text): string
    {
        $address = trim(strip_tags($text));
        if (mb_strlen($address) < 4) {
            return "Veuillez préciser votre adresse de livraison complète (Gouvernorat, Ville, Rue...) :";
        }

        // Detect governorate
        $detectedGov = 'Tunis';
        foreach (self::$governorates as $gov) {
            if (mb_stripos($address, $gov) !== false) {
                $detectedGov = $gov;
                break;
            }
        }

        $conv->setPendingData('shipping_city', $detectedGov);
        $conv->setPendingData('shipping_address', $address);
        $conv->update(['step' => 'awaiting_confirmation']);

        // Build summary for confirmation
        return $this->buildOrderSummary($conv);
    }

    /**
     * Build formatted order summary before final confirmation.
     */
    protected function buildOrderSummary(SocialBotConversation $conv): string
    {
        $productId = $conv->getPendingData('product_id');
        $product = Product::with('vendor')->findOrFail($productId);

        $price = $product->discount_price > 0 ? (float) $product->discount_price : (float) $product->price;
        $qty = (int) $conv->getPendingData('quantity', 1);
        $shipping = (float) ($product->vendor->shipping_price ?? 0);
        $subtotal = $price * $qty;
        $total = $subtotal + $shipping;

        $size = $conv->getPendingData('size');
        $color = $conv->getPendingData('color');
        $variantDetails = trim(($size ? "Taille: $size " : "") . ($color ? "Couleur: $color" : ""));

        $conv->setPendingData('unit_price', $price);
        $conv->setPendingData('shipping_cost', $shipping);
        $conv->setPendingData('total_amount', $total);

        $msg = "📋 **RÉCAPITULATIF DE VOTRE COMMANDE** :\n\n";
        $msg .= "• **Article :** {$product->name}\n";
        if ($variantDetails) {
            $msg .= "• **Option :** {$variantDetails}\n";
        }
        $msg .= "• **Quantité :** x{$qty}\n";
        $msg .= "• **Sous-total :** " . number_format($subtotal, 2) . " DT\n";
        $msg .= "• **Livraison :** " . number_format($shipping, 2) . " DT\n";
        $msg .= "• **TOTAL À PAYER (COD) :** **" . number_format($total, 2) . " DT**\n\n";
        $msg .= "👤 **Destinataire :** " . $conv->getPendingData('customer_name') . "\n";
        $msg .= "📞 **Téléphone :** " . $conv->getPendingData('phone') . "\n";
        $msg .= "📍 **Livraison à :** " . $conv->getPendingData('shipping_address') . " (" . $conv->getPendingData('shipping_city') . ")\n";
        $msg .= "💵 **Paiement :** En espèces à la livraison (Cash on Delivery)\n\n";
        $msg .= "👉 **Confirmez-vous cette commande ?**\nRépondez **OUI** pour valider ou **ANNULER** pour annuler.";

        return $msg;
    }

    /**
     * Handle final confirmation and execute real Order creation.
     */
    protected function handleAwaitingConfirmation(SocialBotConversation $conv, string $text): string
    {
        $normalized = mb_strtolower(trim($text));

        if (in_array($normalized, ['oui', 'ok', 'eyh', 'ey', 'd\'accord', 'valider', 'confirmer', 'yes', 'c bon'])) {
            return $this->createRealOrder($conv);
        }

        return "Pour finaliser votre commande, veuillez répondre **OUI** pour confirmer ou **ANNULER** pour annuler.";
    }

    /**
     * Execute the database transaction creating the order.
     */
    protected function createRealOrder(SocialBotConversation $conv): string
    {
        $productId = $conv->getPendingData('product_id');
        $product = Product::with(['vendor', 'variations'])->find($productId);

        if (!$product || $product->stock <= 0) {
            $conv->resetOrder();
            return "Désolé, cet article n'est plus en stock. La commande n'a pas pu être validée.";
        }

        $customerName = $conv->getPendingData('customer_name');
        $phone = $conv->getPendingData('phone');
        $city = $conv->getPendingData('shipping_city');
        $address = $conv->getPendingData('shipping_address');
        $qty = (int) $conv->getPendingData('quantity', 1);
        $size = $conv->getPendingData('size');
        $color = $conv->getPendingData('color');
        $variationId = $conv->getPendingData('variation_id');

        $price = (float) $conv->getPendingData('unit_price', $product->discount_price > 0 ? $product->discount_price : $product->price);
        $shipping = (float) $conv->getPendingData('shipping_cost', $product->vendor->shipping_price ?? 0);
        $total = ($price * $qty) + $shipping;

        // Find or create customer shadow account by phone
        $email = 'client_' . $phone . '@aquawasouk.com';
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $customerName,
                'password' => bcrypt(Str::random(16)),
                'role' => 'customer',
            ]
        );

        DB::beginTransaction();
        try {
            do {
                $orderNumber = 'ORD-' . strtoupper(Str::random(8));
            } while (Order::where('order_number', $orderNumber)->exists());

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'total_amount' => $total,
                'shipping_cost' => $shipping,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'cash_on_delivery',
                'shipping_address' => $address,
                'shipping_city' => $city,
                'shipping_postal_code' => '1000',
                'shipping_country' => 'Tunisie',
                'phone' => $phone,
                'notes' => "Commande passée via Social Bot (" . ucfirst($conv->channel) . ")",
            ]);

            $vendor = $product->vendor;
            $adminMargin = $vendor ? $vendor->calculateAdminMargin((float) ($price * $qty)) : 0;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'vendor_id' => $product->vendor_id,
                'variation_id' => $variationId,
                'size' => $size,
                'color' => $color,
                'quantity' => $qty,
                'price' => $price,
                'commission' => $adminMargin,
                'status' => 'pending',
            ]);

            // Decrement stock
            if ($variationId && ($var = ProductVariation::find($variationId))) {
                $var->decrement('stock', $qty);
            }
            $product->decrement('stock', $qty);

            DB::commit();

            // Reset conversation state
            $conv->resetOrder();

            return "🎉 **FÉLICITATIONS ! VOTRE COMMANDE EST ENREGISTRÉE AVEC SUCCÈS** !\n\n" .
                   "📋 N° de Commande : **{$orderNumber}**\n" .
                   "💰 Total à payer à la livraison : **" . number_format($total, 2) . " DT**\n" .
                   "🚚 Délai de livraison : 24h à 48h sur toute la Tunisie.\n" .
                   "📞 Notre livreur vous contactera au **{$phone}** avant son passage.\n\n" .
                   "Merci pour votre confiance sur AquawaSouk ! 🛍️";
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error("SocialBot order creation failed: " . $e->getMessage());
            return "Une erreur est survenue lors de l'enregistrement de votre commande. Veuillez réessayer ou contacter notre service client.";
        }
    }
}
