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
        // تحديث refund_method enum في order_returns
        \DB::statement("ALTER TABLE order_returns MODIFY COLUMN refund_method ENUM('cash', 'online') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // إرجاع القيم القديمة
        \DB::statement("ALTER TABLE order_returns MODIFY COLUMN refund_method ENUM('cash', 'card', 'wallet') NULL");
    }
};
