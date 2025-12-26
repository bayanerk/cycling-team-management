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
        Schema::create('ride_tracks', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('ride_id')->constrained('rides')->onDelete('cascade');
            $table->foreignId('ride_participant_id')->constrained('ride_participants')->onDelete('cascade');
            
            // GPS coordinates
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            
            // Speed in km/h
            $table->float('speed');
            
            // Timestamp when GPS point was recorded
            $table->dateTime('recorded_at');
            
            $table->timestamps();
            
            // Index for faster queries
            $table->index('ride_participant_id');
            $table->index('recorded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ride_tracks');
    }
};
