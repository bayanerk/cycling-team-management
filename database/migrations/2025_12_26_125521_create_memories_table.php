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
        Schema::create('memories', function (Blueprint $table) {
            $table->id();
            
            // Memory information
            $table->string('image_path'); // مسار الصورة
            $table->text('description')->nullable(); // وصف الصورة (اختياري)
            $table->boolean('is_active')->default(true); // تفعيل/تعطيل الذكرى
            $table->date('photo_date')->nullable(); // تاريخ الصورة
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memories');
    }
};
