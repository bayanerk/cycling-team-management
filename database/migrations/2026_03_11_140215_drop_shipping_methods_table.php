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
        // أولاً: حذف العمود shipping_method_id من جدول orders
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'shipping_method_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['shipping_method_id']);
                $table->dropColumn('shipping_method_id');
            });
        }
        
        // ثانياً: حذف جدول shipping_methods
        if (Schema::hasTable('shipping_methods')) {
            Schema::dropIfExists('shipping_methods');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // إعادة إنشاء الجدول في حالة rollback
        if (!Schema::hasTable('shipping_methods')) {
            Schema::create('shipping_methods', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('cost', 10, 2)->default(0);
                $table->integer('estimated_days')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index('is_active');
            });
        }
    }
};
