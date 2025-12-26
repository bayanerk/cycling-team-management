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
        Schema::table('ride_participants', function (Blueprint $table) {
            $table->enum('role', ['rider', 'coach'])->default('rider')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ride_participants', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
