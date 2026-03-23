<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Road hazards detected by the on-device / backend model (potholes, speed bumps).
     * Standalone rows (no FK to users/rides/tracks).
     */
    public function up(): void
    {
        Schema::create('road_obstacles', function (Blueprint $table) {
            $table->id();

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            /** Rounded coordinates for deduplication (same physical spot ≈ same key). */
            $table->string('location_key', 36)->index();

            $table->enum('type', ['pothole', 'bump'])->comment('pothole=حفرة, bump=مطب');
            /** Model confidence 0..1; nullable if only stored as a flag. */
            $table->decimal('confidence', 5, 4)->nullable();
            /** When the model detected the hazard (may differ from created_at). */
            $table->dateTime('detected_at')->nullable();
            /** Extra model output / sensor snapshot (JSON). */
            $table->json('metadata')->nullable();

            $table->unsignedInteger('reports_count')->default(1);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['location_key', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('road_obstacles');
    }
};
