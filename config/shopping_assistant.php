<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shopping Assistant Master Switch
    |--------------------------------------------------------------------------
    | When disabled, the shopping assistant will not respond or appear in storefront.
    */
    'enabled' => env('SHOPPING_ASSISTANT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | External AI Provider Configuration (Optional)
    |--------------------------------------------------------------------------
    | If ai_enabled is false, the assistant operates 100% locally via deterministic
    | rule-based natural language processing with zero external API costs.
    */
    'ai_enabled' => env('SHOPPING_ASSISTANT_AI_ENABLED', false),
    'ai_endpoint' => env('SHOPPING_ASSISTANT_AI_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
    'ai_model' => env('SHOPPING_ASSISTANT_AI_MODEL', 'gpt-4o-mini'),
    'ai_api_key' => env('SHOPPING_ASSISTANT_AI_API_KEY', ''),
    'ai_timeout' => env('SHOPPING_ASSISTANT_AI_TIMEOUT', 6),

    /*
    |--------------------------------------------------------------------------
    | General Assistant Configuration
    |--------------------------------------------------------------------------
    */
    'max_recommendations' => env('SHOPPING_ASSISTANT_MAX_RECOMMENDATIONS', 6),
    'currency' => env('SHOPPING_ASSISTANT_CURRENCY', 'DT'),
    'contact_phone' => env('SHOPPING_ASSISTANT_CONTACT_PHONE', '+216 71 000 000'),
    'contact_email' => env('SHOPPING_ASSISTANT_CONTACT_EMAIL', 'support@aquawasouk.tn'),

    /*
    |--------------------------------------------------------------------------
    | Verified Store Policies
    |--------------------------------------------------------------------------
    */
    'shipping_policy' => [
        'fr' => "Expédition 24h à 48h partout en Tunisie. Paiement en espèces à la livraison (COD) ou virement bancaire. Les frais de livraison sont définis par chaque vendeur.",
        'ar' => "التوصيل متوفر لكامل تراب الجمهورية التونسية خلال 24 إلى 48 ساعة. الدفع عند الاستلام نقداً أو عبر تحويل بنكي. معاليم التوصيل يحددها كل بائع.",
        'en' => "Express delivery all over Tunisia within 24 to 48 hours. Cash on delivery (COD) or bank transfer. Shipping rates depend on the vendor.",
    ],

    'return_policy' => [
        'fr' => "Retour sous 7 jours après réception si l'article est intact dans son emballage d'origine non ouvert. Contactez le vendeur ou le support pour la procédure.",
        'ar' => "إمكانية إرجاع المنتج خلال 7 أيام من الاستلام شريطة أن يكون المنتج بحالته الأصلية وفي عبوته غير المفتوحة. تواصل مع البائع أو الدعم لبدء الإرجاع.",
        'en' => "Returns accepted within 7 days of receipt provided the product is intact in its original unopened packaging. Contact the vendor or support.",
    ],

    'welcome_messages' => [
        'fr' => "Bonjour ! 👋 Je suis l'assistant shopping AquawaSouk. Comment puis-je vous aider aujourd'hui ? (Recherche de produit, budget, suivi de commande...)",
        'ar' => "أهلاً بك! 👋 أنا المساعد الذكي لـ AquawaSouk. كيف يمكنني مساعدتك اليوم؟ (البحث عن منتج، أسعار، تتبع طلب...)",
        'en' => "Hello! 👋 I'm your AquawaSouk shopping assistant. How can I help you today? (Find products, budget filters, track order...)",
    ],
];
