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
        Schema::create('user_fitness_profiles', function (Blueprint $table) {
            $table->id();
            
            // Foreign key - one profile per user
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            
            // Physical attributes
            $table->integer('height_cm')->nullable(); // الطول بالسنتيمتر
            $table->decimal('weight_kg', 5, 2)->nullable(); // الوزن بالكيلوغرام
            
            // Medical and sports info
            $table->text('medical_notes')->nullable(); // ملاحظات طبية
            $table->text('other_sports')->nullable(); // رياضات أخرى
            
            // Cycling history
            $table->date('last_ride_date')->nullable(); // آخر تاريخ لقيادة الدراجة
            $table->decimal('max_distance_km', 8, 2)->nullable(); // أطول مسافة قطعها المستخدم
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_fitness_profiles');
    }
};
