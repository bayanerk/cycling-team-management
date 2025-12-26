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
        Schema::table('memories', function (Blueprint $table) {
            // Add new fields
            $table->boolean('is_active')->default(true)->after('description');
            $table->date('photo_date')->nullable()->after('is_active'); // تاريخ الصورة
        });
        
        // Copy data from image_url to image_path and rename column
        if (Schema::hasColumn('memories', 'image_url')) {
            \DB::statement('ALTER TABLE memories CHANGE image_url image_path VARCHAR(255)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memories', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'photo_date']);
        });
        
        // Rename back
        if (Schema::hasColumn('memories', 'image_path')) {
            \DB::statement('ALTER TABLE memories CHANGE image_path image_url VARCHAR(255)');
        }
    }
};
