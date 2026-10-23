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
        Schema::table('products', function (Blueprint $table) {
            // Index for active products ordered by date (most common catalog query)
            $table->index(['status', 'created_at'], 'idx_products_status_created_at');

            // Index for featured products query
            $table->index(['status', 'is_featured'], 'idx_products_status_featured');

            // Index for category catalog query
            $table->index(['category_id', 'status'], 'idx_products_category_status');

            // Index for price sorting and filtering
            $table->index(['status', 'price'], 'idx_products_status_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_status_created_at');
            $table->dropIndex('idx_products_status_featured');
            $table->dropIndex('idx_products_category_status');
            $table->dropIndex('idx_products_status_price');
        });
    }
};
