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
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('location')->nullable();
            // Beginner / Intermediate / Advanced
            $table->enum('level', ['Beginner', 'Intermediate', 'Advanced'])->default('Beginner');
            $table->decimal('distance', 8, 2)->nullable(); // كم

            $table->dateTime('start_time');        // وقت الانطلاق
            $table->dateTime('gathering_time');    // وقت التجمع
            $table->dateTime('end_time')->nullable(); // وقت العودة المتوقع

            $table->string('image_url')->nullable();
            $table->string('break_location')->nullable();
            $table->decimal('cost', 10, 2)->nullable();

            // إحداثيات البداية والنهاية
            $table->decimal('start_lat', 10, 7)->nullable();
            $table->decimal('start_lng', 10, 7)->nullable();
            $table->decimal('end_lat', 10, 7)->nullable();
            $table->decimal('end_lng', 10, 7)->nullable();

            // أنشئ بواسطة (أدمن)
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
