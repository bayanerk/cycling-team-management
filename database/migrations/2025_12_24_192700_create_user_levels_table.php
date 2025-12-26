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
        Schema::create('user_levels', function (Blueprint $table) {
            $table->id();
            
            // Foreign key - one level per user
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            
            // Level information
            $table->string('level_name'); // Beginner / Intermediate / Advanced
            $table->integer('level_number')->default(1); // 1, 2, 3 within each level
            
            // Performance metrics
            $table->decimal('total_distance', 10, 2)->default(0); // إجمالي المسافة
            $table->integer('total_rides')->default(0); // إجمالي عدد الجولات
            $table->integer('total_points')->default(0); // إجمالي النقاط
            
            // Last update timestamp
            $table->timestamp('last_updated')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_levels');
    }
};
