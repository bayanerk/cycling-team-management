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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            
            // Foreign key - one setting per user
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            
            // Settings
            $table->enum('language', ['ar', 'en'])->default('ar');
            $table->boolean('notification_enabled')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
