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
        Schema::create('coaches', function (Blueprint $table) {
            $table->id();
            
            // Foreign key - connected to users table
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            
            // Coach information
            $table->text('bio')->nullable(); // نبذة تعريفية
            $table->integer('experience_years')->nullable(); // سنوات الخبرة
            $table->string('image_url')->nullable(); // صورة الكوتش
            $table->string('specialty')->nullable(); // التخصص
            $table->string('certificate')->nullable(); // الشهادات
            $table->decimal('rating', 3, 2)->nullable()->default(0); // التقييم (0.00 - 5.00)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coaches');
    }
};
