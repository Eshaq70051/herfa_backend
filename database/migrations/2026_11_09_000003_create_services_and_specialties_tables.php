<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 8. جدول categories — تصنيفات الخدمات
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('image', 255)->nullable();
            $table->string('icon', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 9. جدول services — الخدمات
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('image', 255)->nullable();
            $table->decimal('base_price', 10, 2)->default(50.00);
            $table->unsignedInteger('warranty_days')->default(30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 10. جدول craftsman_specialties — تخصصات الحرفيين
        Schema::create('craftsman_specialties', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 11. جدول craftsman_services — خدمات الحرفيين
        Schema::create('craftsman_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('craftsman_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['craftsman_id', 'service_id']);
        });

        // 12. جدول service_images — صور الخدمات
        Schema::create('service_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('image_path', 255);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_images');
        Schema::dropIfExists('craftsman_services');
        Schema::dropIfExists('craftsman_specialties');
        Schema::dropIfExists('services');
        Schema::dropIfExists('categories');
    }
};
