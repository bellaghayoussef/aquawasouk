<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Get all conversations for authenticated user (customer or vendor)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isVendor = $user->isVendor() && $user->vendor;

        $query = Conversation::with(['customer', 'vendor', 'product.primaryImage', 'latestMessage']);

        if ($isVendor) {
            $query->where('vendor_id', $user->vendor->id);
        } else {
            $query->where('customer_id', $user->id);
        }

        $conversations = $query->orderBy('last_message_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'is_vendor' => (bool) $isVendor,
            'data' => $conversations,
        ]);
    }

    /**
     * Start conversation with vendor (or get existing one)
     */
    public function start(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'product_id' => 'nullable|exists:products,id',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:3000',
        ]);

        $user = $request->user();

        if ($user->vendor && $user->vendor->id == $request->vendor_id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas envoyer de message à votre propre boutique.',
            ], 400);
        }

        $conversation = Conversation::firstOrCreate(
            [
                'customer_id' => $user->id,
                'vendor_id' => $request->vendor_id,
                'product_id' => $request->product_id,
            ],
            [
                'subject' => $request->subject ?? 'Demande d\'information',
                'last_message_at' => now(),
            ]
        );

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'sender_type' => 'customer',
            'body' => $request->message,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Message envoyé avec succès',
            'data' => [
                'conversation' => $conversation->load(['vendor', 'product']),
                'message' => $message,
            ],
        ], 201);
    }

    /**
     * Get conversation details and messages
     */
    public function show(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        $isVendorUser = $user->vendor && $conversation->vendor_id === $user->vendor->id;
        $isCustomerUser = $conversation->customer_id === $user->id;

        if (!$isVendorUser && !$isCustomerUser) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé',
            ], 403);
        }

        // Mark unread messages from other party as read
        $otherSenderType = $isVendorUser ? 'customer' : 'vendor';
        $conversation->messages()
            ->where('sender_type', $otherSenderType)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $conversation->load([
            'customer',
            'vendor',
            'product.primaryImage',
            'messages.sender',
        ]);

        return response()->json([
            'success' => true,
            'data' => $conversation,
        ]);
    }

    /**
     * Send message in existing conversation
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $request->validate([
            'message' => 'required|string|max:3000',
        ]);

        $user = $request->user();
        $isVendorUser = $user->vendor && $conversation->vendor_id === $user->vendor->id;
        $isCustomerUser = $conversation->customer_id === $user->id;

        if (!$isVendorUser && !$isCustomerUser) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé',
            ], 403);
        }

        $senderType = $isVendorUser ? 'vendor' : 'customer';

        $msg = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'sender_type' => $senderType,
            'body' => $request->message,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Message envoyé',
            'data' => $msg->load('sender'),
        ], 201);
    }
}
