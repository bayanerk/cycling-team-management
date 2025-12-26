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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            
            // Foreign key - user can have multiple addresses
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Address fields
            $table->string('city'); // المدينة
            $table->string('district'); // الحي
            $table->string('street'); // الشارع
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
