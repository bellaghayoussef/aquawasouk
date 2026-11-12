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
        // Add Ecomness fields to products
        Schema::table('products', function (Blueprint $table) {
            $table->string('ecomness_code')->nullable()->index()->after('sku');
            $table->decimal('ecomness_min_price', 10, 2)->nullable()->after('ecomness_code');
            $table->string('ecomness_category')->nullable()->after('ecomness_min_price');
            $table->json('ecomness_raw_data')->nullable()->after('ecomness_category');
        });

        // Add Ecomness fields to product variations
        Schema::table('product_variations', function (Blueprint $table) {
            $table->string('ecomness_code')->nullable()->index()->after('sku');
            $table->decimal('ecomness_min_price', 10, 2)->nullable()->after('ecomness_code');
        });

        // Add Ecomness fields to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->string('ecomness_order_code')->nullable()->index()->after('order_number');
            $table->string('ecomness_status')->nullable()->after('ecomness_order_code');
            $table->string('ecomness_tracking_number')->nullable()->after('ecomness_status');
            $table->json('ecomness_payload')->nullable()->after('ecomness_tracking_number');
            $table->text('ecomness_error')->nullable()->after('ecomness_payload');
            $table->timestamp('ecomness_synced_at')->nullable()->after('ecomness_error');
        });

        // Add Ecomness fields to order_items
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('ecomness_code')->nullable()->after('size');
            $table->string('ecomness_variant_code')->nullable()->after('ecomness_code');
            $table->string('ecomness_parcel_code')->nullable()->after('ecomness_variant_code');
        });

        // Add Ecomness category mapping to categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->string('ecomness_category')->nullable()->after('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['ecomness_category']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['ecomness_code', 'ecomness_variant_code', 'ecomness_parcel_code']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'ecomness_order_code',
                'ecomness_status',
                'ecomness_tracking_number',
                'ecomness_payload',
                'ecomness_error',
                'ecomness_synced_at',
            ]);
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropColumn(['ecomness_code', 'ecomness_min_price']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'ecomness_code',
                'ecomness_min_price',
                'ecomness_category',
                'ecomness_raw_data',
            ]);
        });
    }
};

