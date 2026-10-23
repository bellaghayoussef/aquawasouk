<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('social_bot_conversations', function (Blueprint $table) {
            $table->id();
            $table->enum('channel', ['facebook', 'instagram', 'simulator'])->default('facebook');
            $table->string('sender_id'); // Meta PSID / IGSID
            $table->string('sender_name')->nullable();
            $table->string('step')->default('browsing'); // browsing, collecting_product, collecting_variant, collecting_name, collecting_phone, collecting_city, collecting_address, awaiting_confirmation, completed
            $table->json('pending_order_data')->nullable(); // holds draft order payload
            $table->foreignId('last_product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->unique(['channel', 'sender_id'], 'uniq_social_bot_channel_sender');
            $table->index(['channel', 'sender_id']);
            $table->index('last_message_at');
        });

        Schema::create('social_bot_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->default('facebook');
            $table->string('sender_id');
            $table->enum('direction', ['inbound', 'outbound']);
            $table->text('message');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['channel', 'sender_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_bot_logs');
        Schema::dropIfExists('social_bot_conversations');
    }
};
