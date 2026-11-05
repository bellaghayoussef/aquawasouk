<?php

namespace App\Services;

use App\Models\AssistantSetting;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private string $baseUrl = 'https://api.textbee.dev/api/v1/gateway/send-sms';

    public function getDeviceId(): ?string
    {
        return AssistantSetting::get('textbee_device_id', config('textbee.device_id'));
    }

    public function getApiKey(): ?string
    {
        return AssistantSetting::get('textbee_api_key', config('textbee.api_key'));
    }

    /**
     * Format a phone number to standard international format (defaults to Tunisia +216 if 8 digits)
     */
    public function formatPhoneNumber(string $phone): string
    {
        $clean = preg_replace('/[^\d+]/', '', trim($phone));

        if (empty($clean)) {
            return '';
        }

        if (str_starts_with($clean, '+')) {
            return $clean;
        }

        if (str_starts_with($clean, '00')) {
            return '+' . substr($clean, 2);
        }

        // Tunisian 8-digit phone numbers
        if (strlen($clean) === 8) {
            return '+216' . $clean;
        }

        // If starts with 216 and is 11 digits
        if (str_starts_with($clean, '216') && strlen($clean) === 11) {
            return '+' . $clean;
        }

        return '+' . $clean;
    }

    /**
     * Send SMS via TextBee Gateway
     */
    public function send(string|array $recipients, string $message, ?int $adminId = null): array
    {
        $deviceId = $this->getDeviceId();
        $apiKey   = $this->getApiKey();

        if (empty($deviceId) || empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'TextBee Device ID ou Clé API non configurés.',
            ];
        }

        $rawRecipients = is_array($recipients) ? $recipients : explode(',', $recipients);
        $formattedRecipients = [];

        foreach ($rawRecipients as $r) {
            $formatted = $this->formatPhoneNumber((string) $r);
            if (!empty($formatted)) {
                $formattedRecipients[] = $formatted;
            }
        }

        $formattedRecipients = array_values(array_unique($formattedRecipients));

        if (empty($formattedRecipients)) {
            return [
                'success' => false,
                'message' => 'Aucun destinataire valide spécifié.',
            ];
        }

        try {
            $response = Http::withHeader('x-api-key', $apiKey)
                ->timeout(20)
                ->post($this->baseUrl, [
                    'deviceId'   => $deviceId,
                    'recipients' => $formattedRecipients,
                    'message'    => $message,
                ]);

            $responseData = $response->json();

            if ($response->successful()) {
                $this->logSms(
                    recipient: implode(', ', array_slice($formattedRecipients, 0, 5)) . (count($formattedRecipients) > 5 ? ' (+' . (count($formattedRecipients) - 5) . ' autres)' : ''),
                    message: $message,
                    status: 'sent',
                    count: count($formattedRecipients),
                    response: $responseData,
                    adminId: $adminId
                );

                return [
                    'success' => true,
                    'message' => 'SMS envoyé avec succès !',
                    'data' => $responseData,
                    'recipients_count' => count($formattedRecipients),
                ];
            }

            $errorMessage = $responseData['message'] ?? 'Erreur lors de l\'envoi via TextBee (' . $response->status() . ')';

            $this->logSms(
                recipient: implode(', ', array_slice($formattedRecipients, 0, 5)),
                message: $message,
                status: 'failed',
                count: count($formattedRecipients),
                response: $responseData,
                error: $errorMessage,
                adminId: $adminId
            );

            return [
                'success' => false,
                'message' => $errorMessage,
                'status_code' => $response->status(),
                'data' => $responseData,
            ];
        } catch (\Throwable $e) {
            Log::error('TextBee SMS Exception: ' . $e->getMessage());

            $this->logSms(
                recipient: implode(', ', array_slice($formattedRecipients, 0, 5)),
                message: $message,
                status: 'failed',
                count: count($formattedRecipients),
                error: $e->getMessage(),
                adminId: $adminId
            );

            return [
                'success' => false,
                'message' => 'Erreur de connexion : ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Internal helper to record SMS logs
     */
    protected function logSms(string $recipient, string $message, string $status, int $count = 1, ?array $response = null, ?string $error = null, ?int $adminId = null): void
    {
        try {
            SmsLog::create([
                'recipient' => $recipient,
                'message' => $message,
                'status' => $status,
                'recipients_count' => $count,
                'response_payload' => $response,
                'error_message' => $error,
                'sender_admin_id' => $adminId,
            ]);
        } catch (\Throwable $e) {
            // Silently fallback if table not yet migrated
            Log::warning('Could not log SMS: ' . $e->getMessage());
        }
    }
}
