<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * طلبات حجز رايد جماعي (تنسيق فقط؛ منفصلة عن جدول rides الرسمي).
     * عدد المشاركين: لا يُقبل الطلب إلا إذا كان بين 10 و 20 شخصاً.
     */
    public function up(): void
    {
        Schema::create('group_ride_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->string('title');
            $table->string('group_name');
            $table->enum('group_type', [
                'friends',
                'scouts',
                'institute',
                'association',
                'organization',
                'other',
            ])->default('friends');

            $table->text('location');
            $table->dateTime('scheduled_at');

            // من 10 (أقل عدد لاعتبار الطلب) إلى 20 — انظر GroupRideRequest::MIN_PEOPLE / MAX_PEOPLE
            $table->unsignedTinyInteger('people_count')
                ->comment('10–20: أقل عدد لقبول طلب رايد جماعي هو 10 أشخاص');

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_ride_requests');
    }
};
