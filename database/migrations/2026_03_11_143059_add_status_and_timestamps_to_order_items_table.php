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
        Schema::table('order_items', function (Blueprint $table) {
            // إضافة status و timestamps للإلغاء والاسترجاع
            if (!Schema::hasColumn('order_items', 'status')) {
                $table->enum('status', ['active', 'cancelled', 'returned'])->default('active')->after('subtotal');
            }
            if (!Schema::hasColumn('order_items', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('order_items', 'returned_at')) {
                $table->timestamp('returned_at')->nullable()->after('cancelled_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'returned_at')) {
                $table->dropColumn('returned_at');
            }
            if (Schema::hasColumn('order_items', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }
            if (Schema::hasColumn('order_items', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
