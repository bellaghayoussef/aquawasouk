<?php

namespace App\Services\ShoppingAssistant;

use App\Models\AssistantSetting;
use App\Services\ShoppingAssistant\CartIntegrationService;
use App\Services\ShoppingAssistant\OrderTrackingService;
use App\Services\ShoppingAssistant\ProductSearchService;
use App\Services\ShoppingAssistant\Providers\OpenAiCompatibleProvider;
use App\Services\ShoppingAssistant\Providers\RuleBasedAssistantProvider;
use Illuminate\Support\Facades\Log;

class ShoppingAssistantService
{
    protected ProductSearchService $searchService;
    protected CartIntegrationService $cartService;
    protected OrderTrackingService $orderService;
    protected RuleBasedAssistantProvider $ruleBasedProvider;
    protected OpenAiCompatibleProvider $aiProvider;

    public function __construct(
        ProductSearchService $searchService,
        CartIntegrationService $cartService,
        OrderTrackingService $orderService,
        RuleBasedAssistantProvider $ruleBasedProvider,
        OpenAiCompatibleProvider $aiProvider
    ) {
        $this->searchService = $searchService;
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->ruleBasedProvider = $ruleBasedProvider;
        $this->aiProvider = $aiProvider;
    }

    /**
     * Check if shopping assistant is enabled.
     */
    public function isEnabled(): bool
    {
        return (bool) AssistantSetting::get('enabled', config('shopping_assistant.enabled', true));
    }

    /**
     * Handle incoming user message.
     */
    public function handleMessage(string $message, array $context = []): array
    {
        if (!$this->isEnabled()) {
            return [
                'message' => "L'assistant shopping est momentanément indisponible.",
                'products' => [],
                'quick_actions' => [],
                'direction' => 'ltr',
            ];
        }

        $cleanMessage = trim(strip_tags($message));
        if (mb_strlen($cleanMessage) === 0) {
            return [
                'message' => "Comment puis-je vous aider aujourd'hui ?",
                'products' => $this->searchService->getBestSellers(4),
                'quick_actions' => $this->ruleBasedProvider->getSuggestedActions('fr'),
                'direction' => 'ltr',
            ];
        }

        // 1. Process deterministic intent first to get verified DB entities
        $baseResult = $this->ruleBasedProvider->process($cleanMessage, $context);

        // 2. If AI is configured and enabled, refine response text while preserving real DB products
        $aiEnabled = (bool) AssistantSetting::get('ai_enabled', config('shopping_assistant.ai_enabled', false));
        if ($aiEnabled && $this->aiProvider->isAvailable()) {
            try {
                $aiResponse = $this->aiProvider->generateResponse($cleanMessage, [
                    'locale' => $this->ruleBasedProvider->detectLanguage($cleanMessage),
                    'products' => $baseResult['products'] ?? [],
                    'cart' => $baseResult['cart'] ?? null,
                ]);

                if (!empty($aiResponse['message'])) {
                    $baseResult['message'] = $aiResponse['message'];
                    $baseResult['ai_generated'] = true;
                }
            } catch (\Throwable $e) {
                Log::warning('AI provider error, falling back to rule-based engine: ' . $e->getMessage());
            }
        }

        // Include current cart items count for UI state syncing
        $cartSummary = $this->cartService->getCartSummary();
        $baseResult['cart_count'] = $cartSummary['total_items'];
        $baseResult['cart_subtotal'] = $cartSummary['subtotal'];
        $baseResult['currency'] = $cartSummary['currency'];

        return $baseResult;
    }

    /**
     * Get initial assistant state and configuration for frontend initialization.
     */
    public function getInitialConfig(string $locale = 'fr'): array
    {
        $welcome = config("shopping_assistant.welcome_messages.{$locale}", config('shopping_assistant.welcome_messages.fr'));
        $suggestedActions = $this->ruleBasedProvider->getSuggestedActions($locale);
        $cartSummary = $this->cartService->getCartSummary();

        return [
            'enabled' => $this->isEnabled(),
            'welcome_message' => $welcome,
            'suggested_actions' => $suggestedActions,
            'cart' => $cartSummary,
            'currency' => AssistantSetting::get('currency', config('shopping_assistant.currency', 'DT')),
            'contact_phone' => AssistantSetting::get('contact_phone', config('shopping_assistant.contact_phone')),
            'contact_email' => AssistantSetting::get('contact_email', config('shopping_assistant.contact_email')),
            'ai_active' => (bool) AssistantSetting::get('ai_enabled', config('shopping_assistant.ai_enabled', false)),
        ];
    }
}
