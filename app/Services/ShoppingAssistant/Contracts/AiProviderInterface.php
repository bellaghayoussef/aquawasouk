<?php

namespace App\Services\ShoppingAssistant\Contracts;

interface AiProviderInterface
{
    /**
     * Generate an AI-assisted chat completion response.
     *
     * @param string $userMessage
     * @param array $context (locale, candidateProducts, currentCart, user)
     * @return array|null Returns array with ['message' => string, 'reasoning' => ?string] or null on failure.
     */
    public function generateResponse(string $userMessage, array $context = []): ?array;
}
