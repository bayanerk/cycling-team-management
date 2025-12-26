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
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // المستخدم المرتبط
            $table->string('identifier'); // البريد الإلكتروني أو رقم الهاتف
            $table->enum('type', ['email', 'phone']); // نوع التحقق
            $table->string('otp_code', 6); // رمز OTP (6 أرقام)
            $table->boolean('is_verified')->default(false); // حالة التحقق
            $table->timestamp('expires_at'); // تاريخ انتهاء الصلاحية
            $table->timestamp('verified_at')->nullable(); // تاريخ التحقق
            $table->timestamps();
            
            $table->index(['identifier', 'type', 'is_verified']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
