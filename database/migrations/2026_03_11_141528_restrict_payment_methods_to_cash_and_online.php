<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Restrict orders.payment_method to: cash, online
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'payment_method')) {
            DB::statement("ALTER TABLE `orders` MODIFY `payment_method` ENUM('cash','online') NOT NULL DEFAULT 'cash'");
        }

        // Restrict payments.method to: cash, online
        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'method')) {
            DB::statement("ALTER TABLE `payments` MODIFY `method` ENUM('cash','online') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old set if needed
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'payment_method')) {
            DB::statement("ALTER TABLE `orders` MODIFY `payment_method` ENUM('cash','card','online','wallet') NOT NULL DEFAULT 'cash'");
        }

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'method')) {
            DB::statement("ALTER TABLE `payments` MODIFY `method` ENUM('cash','card','online','wallet') NOT NULL");
        }
    }
};
