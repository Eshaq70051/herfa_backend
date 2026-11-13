<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 2. جدول craftsman_profiles — الملفات الشخصية للحرفيين
        Schema::create('craftsman_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('profession', 100)->nullable();
            $table->integer('experience_years')->default(1);
            $table->text('bio')->nullable();
            $table->string('city', 100)->nullable()->default('الرياض');
            $table->string('address', 255)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('identity_image', 255)->nullable();
            $table->enum('identity_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->enum('skill_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('availability_status')->default(true); // 1 = متاح، 0 = غير متاح
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(15.00); // نسبة عمولة المنصة
            $table->timestamps();
        });

        // 3. جدول user_addresses — عناوين المستخدمين
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->string('neighborhood', 150)->nullable();
            $table->string('address_details', 255);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('label', 50)->default('المنزل');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // 4. جدول user_devices — أجهزة المستخدمين
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_token', 500);
            $table->enum('device_type', ['android', 'ios'])->default('android');
            $table->string('device_name', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
        Schema::dropIfExists('user_addresses');
        Schema::dropIfExists('craftsman_profiles');
    }
};
