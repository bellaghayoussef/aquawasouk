<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Vendor;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatController extends Controller
{
    /**
     * Display list of conversations for current user (customer or vendor)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isVendor = $user->isVendor() && $user->vendor;

        if ($isVendor) {
            $conversations = Conversation::with(['customer', 'product', 'latestMessage'])
                ->where('vendor_id', $user->vendor->id)
                ->orderBy('last_message_at', 'desc')
                ->get();
        } else {
            $conversations = Conversation::with(['vendor', 'product', 'latestMessage'])
                ->where('customer_id', $user->id)
                ->orderBy('last_message_at', 'desc')
                ->get();
        }

        return Inertia::render('Chat/Index', [
            'conversations' => $conversations,
            'isVendor' => (bool) $isVendor,
        ]);
    }

    /**
     * Start or retrieve an existing conversation with a vendor
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

        // Customer cannot message their own vendor profile
        if ($user->vendor && $user->vendor->id == $request->vendor_id) {
            return back()->with('error', 'Vous ne pouvez pas envoyer de message à votre propre boutique.');
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

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'sender_type' => 'customer',
            'body' => $request->message,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return redirect()->route('chat.show', $conversation->id)->with('success', 'Message envoyé au vendeur.');
    }

    /**
     * View conversation and its messages
     */
    public function show(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        $isVendorUser = $user->vendor && $conversation->vendor_id === $user->vendor->id;
        $isCustomerUser = $conversation->customer_id === $user->id;

        if (!$isVendorUser && !$isCustomerUser) {
            abort(403, 'Accès non autorisé à cette conversation.');
        }

        // Mark unread messages from the other party as read
        $otherSenderType = $isVendorUser ? 'customer' : 'vendor';
        $conversation->messages()
            ->where('sender_type', $otherSenderType)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $conversation->load([
            'customer',
            'vendor.user',
            'product.primaryImage',
            'messages.sender',
        ]);

        return Inertia::render('Chat/Show', [
            'conversation' => $conversation,
            'isVendor' => $isVendorUser,
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
            abort(403, 'Accès non autorisé à cette conversation.');
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

        return back()->with('success', 'Message envoyé.');
    }
}
