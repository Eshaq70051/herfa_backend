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
        // 1. users — جدول المستخدمين
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 150);
            $table->string('phone', 20)->unique();
            $table->string('email', 150)->nullable()->unique();
            $table->string('password', 255);
            $table->string('profile_image', 255)->nullable();
            $table->enum('role', ['customer', 'craftsman', 'admin'])->default('customer');
            $table->enum('account_status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            
            // حقول إضافية لدعم التطبيق الهجين الموحد والمحفظة
            $table->enum('current_mode', ['customer', 'craftsman'])->default('customer');
            $table->decimal('wallet_balance', 12, 2)->default(0.00);
            $table->boolean('is_online')->default(false);
            $table->string('fcm_token', 500)->nullable();
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->unsignedInteger('total_reviews')->default(0);

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
