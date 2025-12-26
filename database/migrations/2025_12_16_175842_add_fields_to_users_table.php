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
        Schema::table('users', function (Blueprint $table) {
            // Add phone column if it doesn't exist
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->unique()->nullable()->after('email');
            }
            
            // Add phone_verified_at column
            if (!Schema::hasColumn('users', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            }
            
            // Add gender column
            if (!Schema::hasColumn('users', 'gender')) {
                $table->enum('gender', ['male', 'female'])->nullable()->after('password');
            }
            
            // Add age column
            if (!Schema::hasColumn('users', 'age')) {
                $table->integer('age')->nullable()->after('gender');
            }
            
            // Add profession column
            if (!Schema::hasColumn('users', 'profession')) {
                $table->string('profession')->nullable()->after('age');
            }
            
            // Add profile_image column
            if (!Schema::hasColumn('users', 'profile_image')) {
                $table->string('profile_image')->nullable()->after('profession');
            }
            
            // Add role column
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'coach', 'rider'])->default('rider')->after('profile_image');
            }
            
            // Add language column
            if (!Schema::hasColumn('users', 'language')) {
                $table->enum('language', ['ar', 'en'])->default('ar')->after('role');
            }
            
            // Add is_active column
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('language');
            }
            
            // Add is_coach_approved column
            if (!Schema::hasColumn('users', 'is_coach_approved')) {
                $table->boolean('is_coach_approved')->default(false)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'phone_verified_at',
                'gender',
                'age',
                'profession',
                'profile_image',
                'role',
                'language',
                'is_active',
                'is_coach_approved',
            ]);
        });
    }
};
