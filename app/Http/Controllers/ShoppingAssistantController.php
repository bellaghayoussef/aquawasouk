<?php

namespace App\Http\Controllers;

use App\Models\AssistantSetting;
use App\Services\ShoppingAssistant\CartIntegrationService;
use App\Services\ShoppingAssistant\OrderTrackingService;
use App\Services\ShoppingAssistant\ProductSearchService;
use App\Services\ShoppingAssistant\ShoppingAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShoppingAssistantController extends Controller
{
    protected ShoppingAssistantService $assistantService;
    protected CartIntegrationService $cartService;
    protected OrderTrackingService $orderService;
    protected ProductSearchService $searchService;

    public function __construct(
        ShoppingAssistantService $assistantService,
        CartIntegrationService $cartService,
        OrderTrackingService $orderService,
        ProductSearchService $searchService
    ) {
        $this->assistantService = $assistantService;
        $this->cartService = $cartService;
        $this->orderService = $orderService;
        $this->searchService = $searchService;
    }

    /**
     * Get initial configuration and state for the chatbot widget.
     */
    public function config(Request $request): JsonResponse
    {
        $locale = $request->query('locale', app()->getLocale() ?: 'fr');
        return response()->json($this->assistantService->getInitialConfig($locale));
    }

    /**
     * Send message to the shopping assistant.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'locale' => 'nullable|string|in:fr,ar,en',
        ]);

        $message = $request->input('message');
        $locale = $request->input('locale', app()->getLocale() ?: 'fr');

        $response = $this->assistantService->handleMessage($message, [
            'locale' => $locale,
            'user' => $request->user(),
        ]);

        return response()->json($response);
    }

    /**
     * Add product to cart directly from assistant card.
     */
    public function addToCart(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'nullable|integer|min:1|max:50',
        ]);

        $productId = (int) $request->input('product_id');
        $quantity = (int) $request->input('quantity', 1);

        $result = $this->cartService->addToCart($productId, $quantity);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Get current cart snapshot.
     */
    public function getCart(): JsonResponse
    {
        return response()->json($this->cartService->getCartSummary());
    }

    /**
     * Update quantity of an item in the cart.
     */
    public function updateCartItem(Request $request, int $itemId): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:0|max:50',
        ]);

        $result = $this->cartService->updateItem($itemId, (int) $request->input('quantity'));

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Remove item from cart.
     */
    public function removeCartItem(int $itemId): JsonResponse
    {
        $result = $this->cartService->removeItem($itemId);
        return response()->json($result);
    }

    /**
     * Order tracking endpoint.
     */
    public function trackOrder(Request $request): JsonResponse
    {
        $request->validate([
            'order_number' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
        ]);

        $result = $this->orderService->trackOrder(
            $request->input('order_number'),
            $request->input('phone')
        );

        return response()->json($result);
    }

    /**
     * Search products directly for assistant.
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $filters = [
            'query' => $request->query('q'),
            'category' => $request->query('category'),
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price'),
            'sort' => $request->query('sort', 'popularity'),
            'in_stock_only' => true,
            'limit' => (int) $request->query('limit', 6),
        ];

        return response()->json([
            'products' => $this->searchService->search($filters),
        ]);
    }

    /**
     * Admin view to manage Shopping Assistant settings.
     */
    public function adminIndex(): Response
    {
        $settings = [
            'enabled' => (bool) AssistantSetting::get('enabled', config('shopping_assistant.enabled', true)),
            'ai_enabled' => (bool) AssistantSetting::get('ai_enabled', config('shopping_assistant.ai_enabled', false)),
            'ai_endpoint' => (string) AssistantSetting::get('ai_endpoint', config('shopping_assistant.ai_endpoint')),
            'ai_model' => (string) AssistantSetting::get('ai_model', config('shopping_assistant.ai_model', 'gpt-4o-mini')),
            'ai_api_key' => (string) AssistantSetting::get('ai_api_key', config('shopping_assistant.ai_api_key')),
            'ai_timeout' => (int) AssistantSetting::get('ai_timeout', config('shopping_assistant.ai_timeout', 6)),
            'max_recommendations' => (int) AssistantSetting::get('max_recommendations', config('shopping_assistant.max_recommendations', 6)),
            'currency' => (string) AssistantSetting::get('currency', config('shopping_assistant.currency', 'DT')),
            'contact_phone' => (string) AssistantSetting::get('contact_phone', config('shopping_assistant.contact_phone')),
            'contact_email' => (string) AssistantSetting::get('contact_email', config('shopping_assistant.contact_email')),
            'shipping_policy_fr' => (string) AssistantSetting::get('shipping_policy_fr', config('shopping_assistant.shipping_policy.fr')),
            'shipping_policy_ar' => (string) AssistantSetting::get('shipping_policy_ar', config('shopping_assistant.shipping_policy.ar')),
            'return_policy_fr' => (string) AssistantSetting::get('return_policy_fr', config('shopping_assistant.return_policy.fr')),
            'return_policy_ar' => (string) AssistantSetting::get('return_policy_ar', config('shopping_assistant.return_policy.ar')),
        ];

        return Inertia::render('Admin/ShoppingAssistant/Settings', [
            'settings' => $settings,
        ]);
    }

    /**
     * Save admin settings.
     */
    public function adminUpdate(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'ai_enabled' => 'required|boolean',
            'ai_endpoint' => 'nullable|url|max:255',
            'ai_model' => 'nullable|string|max:100',
            'ai_api_key' => 'nullable|string|max:255',
            'ai_timeout' => 'nullable|integer|min:2|max:30',
            'max_recommendations' => 'required|integer|min:1|max:12',
            'currency' => 'required|string|max:10',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:100',
            'shipping_policy_fr' => 'nullable|string|max:1000',
            'shipping_policy_ar' => 'nullable|string|max:1000',
            'return_policy_fr' => 'nullable|string|max:1000',
            'return_policy_ar' => 'nullable|string|max:1000',
        ]);

        foreach ($validated as $key => $value) {
            AssistantSetting::set($key, $value);
        }

        return back()->with('success', 'Paramètres de l\'assistant shopping mis à jour avec succès.');
    }
}
