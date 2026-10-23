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
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('price', 10, 2)->nullable(); // Optional price override
            $table->integer('stock')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            // Indexes for fast querying
            $table->index(['product_id', 'status']);
            $table->index(['product_id', 'color', 'size']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('variation_id')->nullable()->constrained('product_variations')->onDelete('set null');
            $table->string('color')->nullable()->after('price');
            $table->string('size')->nullable()->after('color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['variation_id']);
            $table->dropColumn(['variation_id', 'color', 'size']);
        });

        Schema::dropIfExists('product_variations');
    }
};
