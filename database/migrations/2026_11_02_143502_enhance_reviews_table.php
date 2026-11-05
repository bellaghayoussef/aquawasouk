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
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                if (!Schema::hasColumn('reviews', 'title')) {
                    $table->string('title')->nullable()->after('rating');
                }
                if (!Schema::hasColumn('reviews', 'verified_purchase')) {
                    $table->boolean('verified_purchase')->default(false)->after('is_approved');
                }
                if (!Schema::hasColumn('reviews', 'images')) {
                    $table->json('images')->nullable()->after('comment');
                }
                if (!Schema::hasColumn('reviews', 'vendor_reply')) {
                    $table->text('vendor_reply')->nullable()->after('images');
                }
                if (!Schema::hasColumn('reviews', 'vendor_replied_at')) {
                    $table->timestamp('vendor_replied_at')->nullable()->after('vendor_reply');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $columns = ['title', 'verified_purchase', 'images', 'vendor_reply', 'vendor_replied_at'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('reviews', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
