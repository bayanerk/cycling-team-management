<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Road obstacles are standalone (no FK to users/rides/tracks).
     */
    public function up(): void
    {
        if (! Schema::hasTable('road_obstacles')) {
            return;
        }

        $fkColumns = ['user_id', 'ride_id', 'ride_participant_id', 'ride_track_id'];

        Schema::table('road_obstacles', function (Blueprint $table) use ($fkColumns) {
            foreach ($fkColumns as $column) {
                if (Schema::hasColumn('road_obstacles', $column)) {
                    $table->dropForeign([$column]);
                }
            }
        });

        Schema::table('road_obstacles', function (Blueprint $table) use ($fkColumns) {
            $existing = array_values(array_filter(
                $fkColumns,
                fn (string $c): bool => Schema::hasColumn('road_obstacles', $c)
            ));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('road_obstacles')) {
            return;
        }

        Schema::table('road_obstacles', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ride_id')->nullable()->constrained('rides')->nullOnDelete();
            $table->foreignId('ride_participant_id')->nullable()->constrained('ride_participants')->nullOnDelete();
            $table->foreignId('ride_track_id')->nullable()->constrained('ride_tracks')->nullOnDelete();
        });
    }
};
