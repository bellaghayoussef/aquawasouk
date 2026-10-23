<?php

namespace App\Services\ShoppingAssistant\Providers;

use App\Models\AssistantSetting;
use App\Services\ShoppingAssistant\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiCompatibleProvider implements AiProviderInterface
{
    protected string $endpoint;
    protected string $model;
    protected string $apiKey;
    protected int $timeout;
    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = (bool) (AssistantSetting::get('ai_enabled', config('shopping_assistant.ai_enabled', false)));
        $this->endpoint = (string) (AssistantSetting::get('ai_endpoint', config('shopping_assistant.ai_endpoint', 'https://api.openai.com/v1/chat/completions')));
        $this->model = (string) (AssistantSetting::get('ai_model', config('shopping_assistant.ai_model', 'gpt-4o-mini')));
        $this->apiKey = (string) (AssistantSetting::get('ai_api_key', config('shopping_assistant.ai_api_key', '')));
        $this->timeout = (int) (AssistantSetting::get('ai_timeout', config('shopping_assistant.ai_timeout', 6)));
    }

    public function isAvailable(): bool
    {
        return $this->enabled && !empty($this->apiKey) && !empty($this->endpoint);
    }

    public function generateResponse(string $userMessage, array $context = []): ?array
    {
        if (!$this->isAvailable()) {
            return null;
        }

        $locale = $context['locale'] ?? 'fr';
        $products = $context['products'] ?? [];
        $shippingPolicy = $context['shipping_policy'] ?? config('shopping_assistant.shipping_policy.' . $locale, '');
        $returnPolicy = $context['return_policy'] ?? config('shopping_assistant.return_policy.' . $locale, '');

        $productSummaries = [];
        foreach ($products as $p) {
            $productSummaries[] = sprintf(
                "- %s: Prix %s DT (Vendeur: %s, Stock: %d, Catégorie: %s). Description: %s",
                $p['name'] ?? 'Produit',
                $p['current_price'] ?? $p['price'] ?? 0,
                $p['vendor_name'] ?? 'Boutique',
                $p['stock'] ?? 0,
                $p['category_name'] ?? 'Général',
                \Illuminate\Support\Str::limit(strip_tags($p['description'] ?? ''), 120)
            );
        }
        $productsText = empty($productSummaries) 
            ? "Aucun produit trouvé dans cette recherche." 
            : implode("\n", $productSummaries);

        $systemPrompt = <<<PROMPT
Tu es l'assistant shopping intelligent de la marketplace e-commerce multi-vendeurs "AquawaSouk" (Tunisie, monnaie: DT - Dinar Tunisien).
Ta mission est d'aider les clients à découvrir des produits réels, comparer les offres de différents vendeurs, et répondre à leurs questions sur la livraison et les commandes.

RÈGLES STRICTES ET NON NÉGOCIABLES:
1. Tu ne dois JAMAIS inventer un produit, un vendeur, un prix, un stock ou une promotion. Utilise UNIQUEMENT la liste des produits fournie ci-dessous.
2. Si aucun produit ne correspond, dis-le poliment et propose d'élargir la recherche ou d'ajuster le budget.
3. Langues supportées: Français, Arabe Tunisien (Derja / Arabizi), Arabe standard, ou Anglais. Réponds dans la langue du client.
4. Reste concis, chaleureux, commercial et direct (2 à 4 phrases maximum + conseils pertinents).
5. Politiques officielles:
   - Livraison: {$shippingPolicy}
   - Retours: {$returnPolicy}
6. Ne demande JAMAIS d'informations bancaires confidentielles (mots de passe, numéros de carte de crédit).

PRODUITS DISPONIBLES EN BASE ACTUELLEMENT:
{$productsText}
PROMPT;

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->endpoint, [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'max_tokens' => 350,
                    'temperature' => 0.4,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? null;
                if (!empty($content)) {
                    return [
                        'message' => trim($content),
                        'source' => 'ai',
                    ];
                }
            } else {
                Log::warning('Shopping Assistant AI API error: ' . $response->status() . ' - ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::warning('Shopping Assistant AI request failed: ' . $e->getMessage());
        }

        return null;
    }
}
