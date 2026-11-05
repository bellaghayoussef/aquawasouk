<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sms_logs')) {
            Schema::create('sms_logs', function (Blueprint $table) {
                $table->id();
                $table->string('recipient');
                $table->text('message');
                $table->string('status')->default('sent');
                $table->unsignedInteger('recipients_count')->default(1);
                $table->text('error_message')->nullable();
                $table->json('response_payload')->nullable();
                $table->foreignId('sender_admin_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
