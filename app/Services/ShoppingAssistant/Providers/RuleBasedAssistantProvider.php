<?php

namespace App\Services\ShoppingAssistant\Providers;

use App\Models\AssistantSetting;
use App\Services\ShoppingAssistant\CartIntegrationService;
use App\Services\ShoppingAssistant\OrderTrackingService;
use App\Services\ShoppingAssistant\ProductSearchService;

class RuleBasedAssistantProvider
{
    protected ProductSearchService $searchService;
    protected CartIntegrationService $cartService;
    protected OrderTrackingService $orderService;

    public function __construct(
        ProductSearchService $searchService,
        CartIntegrationService $cartService,
        OrderTrackingService $orderService
    ) {
        $this->searchService = $searchService;
        $this->cartService = $cartService;
        $this->orderService = $orderService;
    }

    /**
     * Process message deterministically without requiring any external AI.
     */
    public function process(string $message, array $context = []): array
    {
        $normalized = mb_strtolower(trim($message), 'UTF-8');
        $lang = $this->detectLanguage($message);
        $isRtl = $lang === 'ar';

        // 1. Detect Intent: Order Tracking
        if ($this->matchesTracking($normalized)) {
            return $this->handleTrackingIntent($normalized, $lang, $isRtl);
        }

        // 2. Detect Intent: Cart inspection
        if ($this->matchesCart($normalized)) {
            return $this->handleCartIntent($lang, $isRtl);
        }

        // 3. Detect Intent: Shipping policy
        if ($this->matchesShipping($normalized)) {
            return $this->handleShippingIntent($lang, $isRtl);
        }

        // 4. Detect Intent: Return policy
        if ($this->matchesReturns($normalized)) {
            return $this->handleReturnIntent($lang, $isRtl);
        }

        // 5. Detect Intent: Customer Support
        if ($this->matchesSupport($normalized)) {
            return $this->handleSupportIntent($lang, $isRtl);
        }

        // 6. Detect Intent: Categories browsing
        if ($this->matchesCategories($normalized)) {
            return $this->handleCategoriesIntent($lang, $isRtl);
        }

        // 7. Detect Intent: Best Sellers / Popular
        if ($this->matchesBestSellers($normalized)) {
            return $this->handleBestSellersIntent($lang, $isRtl);
        }

        // 8. Detect Intent: Budget / Price filter
        $budget = $this->extractBudget($normalized);
        if ($budget !== null) {
            return $this->handleBudgetSearch($budget, $normalized, $lang, $isRtl);
        }

        // 9. Standard Product Keyword Search
        return $this->handleProductSearch($normalized, $lang, $isRtl);
    }

    /**
     * Detect language: 'ar', 'fr', 'en'
     */
    public function detectLanguage(string $text): string
    {
        // Check Arabic characters
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return 'ar';
        }

        $lower = mb_strtolower($text, 'UTF-8');

        // English keywords
        if (preg_match('/\b(the|is|i want|looking for|cheap|best|price|shipping|order|hello|hi|help)\b/', $lower)) {
            return 'en';
        }

        // French is default for Tunisia e-commerce
        return 'fr';
    }

    protected function matchesTracking(string $text): bool
    {
        return (bool) preg_match('/(suivr|suivi|commande|order|track|weslet|commondi|تبع|وين وصلت|تتبع|طلبي|الطلب)/iu', $text);
    }

    protected function matchesCart(string $text): bool
    {
        return (bool) preg_match('/(panier|cart|9offa|mon panier|voir panier|سلة|السلة)/iu', $text);
    }

    protected function matchesShipping(string $text): bool
    {
        return (bool) preg_match('/(livraison|frais|délai|wa9tech|shipping|delivery|توصيل|شحن|الدفع عند الاستلام|cod)/iu', $text);
    }

    protected function matchesReturns(string $text): bool
    {
        return (bool) preg_match('/(retour|rembours|garantie|return|refund|إرجاع|استرجاع|ضمان|تبديل)/iu', $text);
    }

    protected function matchesSupport(string $text): bool
    {
        return (bool) preg_match('/(support|contact|aide|help|numéro|telephone|service client|اتصال|مساعدة|خدمة العملاء)/iu', $text);
    }

    protected function matchesCategories(string $text): bool
    {
        return (bool) preg_match('/(categorie|catégorie|rayon|departments|types|تصنيف|أقسام|انواع|أصناف)/iu', $text);
    }

    protected function matchesBestSellers(string $text): bool
    {
        return (bool) preg_match('/(meilleur|best|top|populaire|a7san|promo|meilleures ventes|الأكثر مبيعا|أفضل|مميز)/iu', $text);
    }

    protected function extractBudget(string $text): ?float
    {
        // Patterns: "moins de 50 dt", "budget 100 dinars", "< 50", "50dt", "100 dinar", "50 دينار"
        if (preg_match('/(?:budget|moins de|max|inférieur à|under|less than|dans les|mizaniya|mizaniyti|ميزانية|أقل من|في حدود)?\s*(\d+(?:[\.,]\d+)?)\s*(?:dt|dinar|dinars|tnd|d|د|دينار)/iu', $text, $matches)) {
            return (float) str_replace(',', '.', $matches[1]);
        }

        if (preg_match('/(?:<|<=|moins de|under|أقل من)\s*(\d+(?:[\.,]\d+)?)/iu', $text, $matches)) {
            return (float) str_replace(',', '.', $matches[1]);
        }

        return null;
    }

    protected function handleBudgetSearch(float $maxBudget, string $text, string $lang, bool $isRtl): array
    {
        // Extract possible keyword ignoring budget tokens
        $cleaned = preg_replace('/(budget|moins de|max|inférieur à|under|less than|mizaniya|dt|dinar|dinars|tnd|d|د|دينار|\d+)/iu', '', $text);
        $keyword = trim(preg_replace('/\s+/', ' ', $cleaned));

        $products = $this->searchService->search([
            'max_price' => $maxBudget,
            'query' => mb_strlen($keyword) > 2 ? $keyword : null,
            'in_stock_only' => true,
            'sort' => 'price_asc',
            'limit' => 6,
        ]);

        $currency = AssistantSetting::get('currency', config('shopping_assistant.currency', 'DT'));

        if (empty($products)) {
            $msg = match ($lang) {
                'ar' => "عذراً، لم أجد حالياً منتجات متوفرة بسعر أقل من {$maxBudget} {$currency}. هل ترغب في الاطلاع على أفضل العروض المتوفرة؟",
                'en' => "Sorry, I couldn't find in-stock products under {$maxBudget} {$currency}. Would you like to check our best value deals?",
                default => "Désolé, je n'ai pas trouvé d'articles en stock à moins de {$maxBudget} {$currency}. Souhaitez-vous élargir votre budget ou voir nos meilleures ventes ?",
            };

            return [
                'message' => $msg,
                'products' => $this->searchService->getBestSellers(4),
                'quick_actions' => $this->getSuggestedActions($lang),
                'direction' => $isRtl ? 'rtl' : 'ltr',
            ];
        }

        $msg = match ($lang) {
            'ar' => "تفضل! إليك أفضل المنتجات المتوفرة في حدود ميزانيتك ({$maxBudget} {$currency}) من مختلف البائعين المعتمدين:",
            'en' => "Here are great in-stock products matching your budget (under {$maxBudget} {$currency}) from our verified vendors:",
            default => "Voici d'excellents produits en stock correspondant à votre budget (moins de {$maxBudget} {$currency}) chez nos vendeurs partenaires :",
        };

        return [
            'message' => $msg,
            'products' => $products,
            'quick_actions' => [
                ['label' => '💰 Voir le panier', 'action' => 'view_cart'],
                ['label' => '🔥 Meilleures ventes', 'query' => 'meilleures ventes'],
                ['label' => '🚚 Infos livraison', 'query' => 'infos livraison'],
            ],
            'direction' => $isRtl ? 'rtl' : 'ltr',
        ];
    }

    protected function handleBestSellersIntent(string $lang, bool $isRtl): array
    {
        $products = $this->searchService->getBestSellers(6);

        $msg = match ($lang) {
            'ar' => "إليك المنتجات الأكثر طلباً وشعبية على متجرنا اليوم من مختلف المتاجر:",
            'en' => "Here are our trending and most popular products across all vendor stores:",
            default => "Voici les produits les plus populaires et les plus demandés en ce moment sur AquawaSouk :",
        };

        return [
            'message' => $msg,
            'products' => $products,
            'quick_actions' => $this->getSuggestedActions($lang),
            'direction' => $isRtl ? 'rtl' : 'ltr',
        ];
    }

    protected function handleCategoriesIntent(string $lang, bool $isRtl): array
    {
        $categories = $this->searchService->getCategories(8);

        $msg = match ($lang) {
            'ar' => "تصفح الأقسام الرئيسية المتاحة لدينا. اختر أي قسم لاكتشاف منتجاته:",
            'en' => "Browse our main categories. Click or ask about any category to discover products:",
            default => "Voici les principaux rayons disponibles sur AquawaSouk. Quel type de produit recherchez-vous ?",
        };

        $actions = [];
        foreach ($categories as $cat) {
            $actions[] = [
                'label' => $cat['name'],
                'query' => "Catégorie " . $cat['name'],
            ];
        }

        return [
            'message' => $msg,
            'products' => [],
            'quick_actions' => $actions,
            'categories' => $categories,
            'direction' => $isRtl ? 'rtl' : 'ltr',
        ];
    }

    protected function handleCartIntent(string $lang, bool $isRtl): array
    {
        $cartSummary = $this->cartService->getCartSummary();

        if ($cartSummary['is_empty']) {
            $msg = match ($lang) {
                'ar' => "سلة التسوق الخاصة بك فارغة حالياً. هل ترغب في استكشاف بعض المنتجات المميزة؟",
                'en' => "Your shopping cart is currently empty. Would you like to discover our featured products?",
                default => "Votre panier est actuellement vide. Souhaitez-vous découvrir nos meilleures offres du moment ?",
            };

            return [
                'message' => $msg,
                'cart' => $cartSummary,
                'products' => $this->searchService->getFeatured(4),
                'quick_actions' => $this->getSuggestedActions($lang),
                'direction' => $isRtl ? 'rtl' : 'ltr',
            ];
        }

        $msg = match ($lang) {
            'ar' => "لديك {$cartSummary['total_items']} منتج في سلتك بإجمالي {$cartSummary['formatted_subtotal']}. يمكنك متابعة إتمام الطلب مباشرة:",
            'en' => "You have {$cartSummary['total_items']} item(s) in your cart totaling {$cartSummary['formatted_subtotal']}. You can proceed to checkout:",
            default => "Vous avez {$cartSummary['total_items']} article(s) dans votre panier pour un total de {$cartSummary['formatted_subtotal']}. Vous pouvez finaliser votre commande :",
        };

        return [
            'message' => $msg,
            'cart' => $cartSummary,
            'products' => [],
            'quick_actions' => [
                ['label' => '🛍️ Voir mon panier', 'action' => 'open_cart'],
                ['label' => '💳 Passer commande', 'url' => route('cart.index')],
                ['label' => '🔎 Continuer les achats', 'query' => 'meilleures ventes'],
            ],
            'direction' => $isRtl ? 'rtl' : 'ltr',
        ];
    }

    protected function handleTrackingIntent(string $text, string $lang, bool $isRtl): array
    {
        // Try extracting order reference (e.g., ORD-20261021-001 or digits)
        if (preg_match('/(ORD-[\w-]+|\b\d{4,}\b)/i', $text, $m)) {
            $tracking = $this->orderService->trackOrder($m[1]);
            if ($tracking['found']) {
                $msg = match ($lang) {
                    'ar' => "حالة طلبك رقم {$tracking['order_number']}: {$tracking['status_label_ar']}. الإجمالي: {$tracking['total_amount']} {$tracking['currency']}.",
                    'en' => "Status for order #{$tracking['order_number']}: {$tracking['status_label']}. Total: {$tracking['total_amount']} {$tracking['currency']}.",
                    default => "Statut de votre commande #{$tracking['order_number']} : {$tracking['status_label']}. Montant : {$tracking['total_amount']} {$tracking['currency']}.",
                };

                return [
                    'message' => $msg,
                    'order_tracking' => $tracking,
                    'quick_actions' => [
                        ['label' => '📦 Mes commandes', 'url' => route('orders.index')],
                        ['label' => '💬 Contacter le support', 'query' => 'service client'],
                    ],
                    'direction' => $isRtl ? 'rtl' : 'ltr',
                ];
            }
        }

        // Recent orders if logged in
        $recent = $this->orderService->getRecentUserOrders(3);
        if (!empty($recent)) {
            $msg = match ($lang) {
                'ar' => "إليك آخر طلباتك المسجلة في حسابك. انقر على أي طلب لتفاصيله أو اكتب رقم الطلب للتتبع:",
                'en' => "Here are your recent orders. Tap any order or type your order reference to track it:",
                default => "Voici vos dernières commandes. Vous pouvez cliquer sur une commande ou saisir votre numéro de commande pour la suivre :",
            };

            $actions = array_map(fn($o) => [
                'label' => "#{$o['order_number']} ({$o['status']})",
                'query' => "Suivre {$o['order_number']}",
            ], $recent);

            return [
                'message' => $msg,
                'recent_orders' => $recent,
                'quick_actions' => $actions,
                'direction' => $isRtl ? 'rtl' : 'ltr',
            ];
        }

        $msg = match ($lang) {
            'ar' => "لتتبع طلبك، يرجى كتابة رقم الطلب (مثال: ORD-123456) أو رقم هاتفك إذا كنت زائراً.",
            'en' => "To track your order, please enter your order reference (e.g. ORD-123456) or your phone number.",
            default => "Pour suivre votre commande, veuillez indiquer votre référence de commande (ex: ORD-XXXXXX). Si vous avez un compte, vous pouvez aussi consulter votre espace commandes.",
        };

        return [
            'message' => $msg,
            'products' => [],
            'quick_actions' => [
                ['label' => '📋 Mes commandes', 'url' => route('orders.index')],
                ['label' => '💬 Contacter le support', 'query' => 'service client'],
            ],
            'direction' => $isRtl ? 'rtl' : 'ltr',
        ];
    }

    protected function handleShippingIntent(string $lang, bool $isRtl): array
    {
        $policy = config("shopping_assistant.shipping_policy.{$lang}", config('shopping_assistant.shipping_policy.fr'));

        $msg = match ($lang) {
            'ar' => "معلومات الشحن والتوصيل 🚚:\n" . $policy,
            'en' => "Delivery & Shipping Info 🚚:\n" . $policy,
            default => "Informations de livraison 🚚 :\n" . $policy,
        };

        return [
            'message' => $msg,
            'products' => [],
            'quick_actions' => [
                ['label' => '🔥 Voir les produits', 'query' => 'meilleures ventes'],
                ['label' => '🔄 Politique de retour', 'query' => 'politique de retour'],
                ['label' => '📞 Support client', 'query' => 'contact support'],
            ],
            'direction' => $isRtl ? 'rtl' : 'ltr',
        ];
    }

    protected function handleReturnIntent(string $lang, bool $isRtl): array
    {
        $policy = config("shopping_assistant.return_policy.{$lang}", config('shopping_assistant.return_policy.fr'));

        $msg = match ($lang) {
            'ar' => "سياسة الإرجاع والاستبدال 🔄:\n" . $policy,
            'en' => "Returns & Exchanges Policy 🔄:\n" . $policy,
            default => "Politique de retours et échanges 🔄 :\n" . $policy,
        };

        return [
            'message' => $msg,
            'products' => [],
            'quick_actions' => [
                ['label' => '🚚 Frais de livraison', 'query' => 'livraison'],
                ['label' => '💬 Contacter le support', 'query' => 'service client'],
            ],
            'direction' => $isRtl ? 'rtl' : 'ltr',
        ];
    }

    protected function handleSupportIntent(string $lang, bool $isRtl): array
    {
        $phone = AssistantSetting::get('contact_phone', config('shopping_assistant.contact_phone', '+216 71 000 000'));
        $email = AssistantSetting::get('contact_email', config('shopping_assistant.contact_email', 'support@aquawasouk.tn'));

        $msg = match ($lang) {
            'ar' => "فريق خدمة العملاء جاهز لمساعدتك! يمكنك التواصل معنا عبر الهاتف {$phone} أو البريد الإلكتروني {$email}.",
            'en' => "Our support team is happy to assist you! Reach us at {$phone} or via email at {$email}.",
            default => "Notre service client est à votre écoute ! Vous pouvez nous joindre par téléphone au {$phone} ou par email à {$email}.",
        };

        return [
            'message' => $msg,
            'products' => [],
            'quick_actions' => [
                ['label' => '✉️ Page Contact', 'url' => route('contact')],
                ['label' => '📦 Suivre ma commande', 'query' => 'suivre ma commande'],
            ],
            'direction' => $isRtl ? 'rtl' : 'ltr',
        ];
    }

    protected function handleProductSearch(string $text, string $lang, bool $isRtl): array
    {
        // Strip common question words
        $clean = preg_replace('/(je cherche|trouve|donne moi|nheb|a7san|fama|show me|find me|looking for|أريد|أبحث عن|فما|شنوة)/iu', '', $text);
        $term = trim(preg_replace('/\s+/', ' ', $clean));

        $products = [];
        if (mb_strlen($term) >= 2) {
            $products = $this->searchService->search([
                'query' => $term,
                'in_stock_only' => true,
                'limit' => 6,
            ]);
        }

        if (empty($products)) {
            // Fallback to general popular products with clarification
            $fallback = $this->searchService->getBestSellers(4);
            $msg = match ($lang) {
                'ar' => "لم أجد نتائج دقيقة لـ \"{$term}\". إليك بعض المنتجات الأكثر طلباً، أو أخبرني بنوع المنتج أو ميزانيتك لأساعدك بدقة أكثر!",
                'en' => "I couldn't find exact matches for \"{$term}\". Here are some popular products, or tell me your target budget or category!",
                default => "Je n'ai pas trouvé de correspondance exacte pour \"{$term}\". Voici nos produits les plus populaires, ou précisez votre budget / catégorie pour vous guider !",
            };

            return [
                'message' => $msg,
                'products' => $fallback,
                'quick_actions' => $this->getSuggestedActions($lang),
                'direction' => $isRtl ? 'rtl' : 'ltr',
            ];
        }

        $count = count($products);
        $msg = match ($lang) {
            'ar' => "وجدت {$count} منتج(ات) مطابقة لبحثك لدى بائعينا المعتمدين:",
            'en' => "Found {$count} product(s) matching your search across our verified vendors:",
            default => "J'ai trouvé {$count} produit(s) correspondant à votre recherche parmi nos vendeurs partenaires :",
        };

        return [
            'message' => $msg,
            'products' => $products,
            'quick_actions' => [
                ['label' => '💰 Moins de 50 DT', 'query' => 'moins de 50 dt'],
                ['label' => '🛍️ Mon panier', 'action' => 'open_cart'],
                ['label' => '🚚 Infos livraison', 'query' => 'livraison'],
            ],
            'direction' => $isRtl ? 'rtl' : 'ltr',
        ];
    }

    public function getSuggestedActions(string $lang): array
    {
        return match ($lang) {
            'ar' => [
                ['label' => '🔥 الأكثر طلباً', 'query' => 'الأكثر مبيعا'],
                ['label' => '💰 أقل من 50 دينار', 'query' => 'أقل من 50 دينار'],
                ['label' => '🏷️ تصفح الأقسام', 'query' => 'تصفح الأقسام'],
                ['label' => '📦 تتبع طلبي', 'query' => 'تتبع طلبي'],
                ['label' => '🚚 مصاريف الشحن', 'query' => 'مصاريف الشحن'],
            ],
            'en' => [
                ['label' => '🔥 Best sellers', 'query' => 'best sellers'],
                ['label' => '💰 Under 50 DT', 'query' => 'under 50 DT'],
                ['label' => '🏷️ Categories', 'query' => 'browse categories'],
                ['label' => '📦 Track order', 'query' => 'track my order'],
                ['label' => '🚚 Shipping info', 'query' => 'shipping policy'],
            ],
            default => [
                ['label' => '🔥 Meilleures ventes', 'query' => 'meilleures ventes'],
                ['label' => '💰 Moins de 50 DT', 'query' => 'moins de 50 DT'],
                ['label' => '🏷️ Catégories', 'query' => 'catégories'],
                ['label' => '📦 Suivre ma commande', 'query' => 'suivre ma commande'],
                ['label' => '🚚 Délais & livraison', 'query' => 'infos livraison'],
            ],
        };
    }
}
