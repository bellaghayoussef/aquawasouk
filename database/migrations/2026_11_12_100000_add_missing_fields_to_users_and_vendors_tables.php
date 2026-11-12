<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add missing fields to `users` table
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'phone')) {
                    $table->string('phone', 30)->nullable()->after('email');
                }
            });
        }

        // 2. Add missing fields to `vendors` table
        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (!Schema::hasColumn('vendors', 'name')) {
                    $table->string('name')->nullable()->after('shop_name');
                }
                if (!Schema::hasColumn('vendors', 'rating')) {
                    $table->decimal('rating', 3, 2)->default(5.00)->after('commission_rate');
                }
            });

            // Populate vendor name with shop_name if empty
            if (Schema::hasColumn('vendors', 'name') && Schema::hasColumn('vendors', 'shop_name')) {
                try {
                    DB::table('vendors')
                        ->whereNull('name')
                        ->orWhere('name', '')
                        ->update(['name' => DB::raw('shop_name')]);
                } catch (\Throwable $e) {
                    // Safe fallback
                }
            }
        }

        // 3. Add missing fields to `cart_items` table
        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                if (!Schema::hasColumn('cart_items', 'variation_id')) {
                    $table->unsignedBigInteger('variation_id')->nullable()->after('product_id');
                }
            });
        }

        // 4. Ensure `banners` table has `page` column
        if (Schema::hasTable('banners')) {
            Schema::table('banners', function (Blueprint $table) {
                if (!Schema::hasColumn('banners', 'page')) {
                    $table->string('page', 50)->default('home')->after('title');
                }
            });
        }

        // 5. Ensure `categories` table has `image` and `parent_id`
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (!Schema::hasColumn('categories', 'image')) {
                    $table->string('image')->nullable()->after('description');
                }
                if (!Schema::hasColumn('categories', 'parent_id')) {
                    $table->unsignedBigInteger('parent_id')->nullable()->after('id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'phone')) {
                    $table->dropColumn('phone');
                }
            });
        }

        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                $dropCols = [];
                if (Schema::hasColumn('vendors', 'name')) {
                    $dropCols[] = 'name';
                }
                if (Schema::hasColumn('vendors', 'rating')) {
                    $dropCols[] = 'rating';
                }
                if (!empty($dropCols)) {
                    $table->dropColumn($dropCols);
                }
            });
        }

        if (Schema::hasTable('cart_items')) {
            Schema::table('cart_items', function (Blueprint $table) {
                if (Schema::hasColumn('cart_items', 'variation_id')) {
                    $table->dropColumn('variation_id');
                }
            });
        }
    }
};

