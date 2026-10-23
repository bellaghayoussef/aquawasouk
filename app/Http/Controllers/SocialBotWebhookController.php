<?php

namespace App\Http\Controllers;

use App\Services\SocialBot\MetaGraphApiService;
use App\Services\SocialBot\SocialBotEngine;
use App\Models\SocialBotConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SocialBotWebhookController extends Controller
{
    protected SocialBotEngine $engine;

    public function __construct(SocialBotEngine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Webhook Verification for Meta (Facebook & Instagram)
     * Meta sends: hub.mode, hub.verify_token, hub.challenge
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

        $expectedToken = MetaGraphApiService::getVerifyToken();

        if ($mode === 'subscribe' && $token === $expectedToken) {
            Log::info("Meta Webhook verified successfully!");
            return response($challenge, 200);
        }

        Log::warning("Meta Webhook verification failed. Token received: '{$token}'");
        return response('Forbidden', 403);
    }

    /**
     * Webhook Event Receiver for Meta (Messenger & Instagram DMs)
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        $object = $payload['object'] ?? '';

        // Verify it's from page (Facebook) or instagram
        if (!in_array($object, ['page', 'instagram'])) {
            return response('EVENT_RECEIVED', 200);
        }

        $channel = $object === 'instagram' ? 'instagram' : 'facebook';

        try {
            $entries = $payload['entry'] ?? [];
            foreach ($entries as $entry) {
                $messagingEvents = $entry['messaging'] ?? [];
                foreach ($messagingEvents as $event) {
                    $senderId = $event['sender']['id'] ?? null;
                    $message = $event['message'] ?? null;

                    // Only process incoming text messages from users (ignore echoes/delivery receipts)
                    if ($senderId && $message && !empty($message['text']) && empty($message['is_echo'])) {
                        $incomingText = $message['text'];

                        // 1. Process message through bot engine
                        $reply = $this->engine->handle($channel, (string) $senderId, $incomingText);

                        // 2. Send reply back to customer via Meta Graph API
                        MetaGraphApiService::sendTextMessage((string) $senderId, $reply, $channel);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error("Error handling Meta Webhook: " . $e->getMessage());
        }

        return response('EVENT_RECEIVED', 200);
    }

    /**
     * Simulation endpoint for testing the bot from Admin panel
     */
    public function simulate(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'channel' => 'nullable|in:facebook,instagram,simulator',
            'sender_id' => 'nullable|string',
            'sender_name' => 'nullable|string',
        ]);

        $channel = $request->get('channel', 'simulator');
        $senderId = $request->get('sender_id', 'sim_user_001');
        $senderName = $request->get('sender_name', 'Client Test');

        $reply = $this->engine->handle($channel, $senderId, $request->message, $senderName);

        $conversation = SocialBotConversation::where('channel', $channel)
            ->where('sender_id', $senderId)
            ->first();

        return response()->json([
            'success' => true,
            'reply' => $reply,
            'conversation' => $conversation,
        ]);
    }

    /**
     * Reset simulation state
     */
    public function resetSimulation(Request $request)
    {
        $senderId = $request->get('sender_id', 'sim_user_001');
        $channel = $request->get('channel', 'simulator');

        $conv = SocialBotConversation::where('channel', $channel)
            ->where('sender_id', $senderId)
            ->first();

        if ($conv) {
            $conv->resetOrder();
        }

        return response()->json([
            'success' => true,
            'message' => 'Simulation réinitialisée avec succès',
        ]);
    }
}
