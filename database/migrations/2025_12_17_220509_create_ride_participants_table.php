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
        Schema::create('ride_participants', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('ride_id')->constrained('rides')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Status
            $table->enum('status', ['joined', 'cancelled', 'excused', 'completed', 'no_show'])->default('joined');
            
            // Timestamps
            $table->dateTime('joined_at');
            $table->dateTime('cancelled_at')->nullable();
            $table->dateTime('excused_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('checked_at')->nullable();
            
            // Ride statistics
            $table->decimal('distance_km', 5, 2)->nullable();
            $table->decimal('avg_speed_kmh', 5, 2)->nullable();
            $table->integer('calories_burned')->nullable();
            $table->integer('points_earned')->nullable();
            
            $table->timestamps();
            
            // Unique constraint: user can only join a ride once
            $table->unique(['ride_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_participants');
    }
};
