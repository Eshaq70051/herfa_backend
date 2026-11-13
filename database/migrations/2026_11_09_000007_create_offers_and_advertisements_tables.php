<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 25. جدول offers — العروض
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('craftsman_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->decimal('discount_percentage', 5, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('offer_status', ['pending', 'active', 'expired', 'rejected'])->default('pending');
            $table->timestamps();
        });

        // 26. جدول offer_images — صور العروض
        Schema::create('offer_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained('offers')->cascadeOnDelete();
            $table->string('image_path', 255);
            $table->timestamp('created_at')->useCurrent();
        });

        // 27. جدول advertisements — الإعلانات
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('advertiser_name', 150)->nullable();
            $table->string('target_url', 255)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['pending', 'active', 'inactive', 'expired'])->default('pending');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // 28. جدول advertisement_images — صور الإعلانات
        Schema::create('advertisement_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advertisement_id')->constrained('advertisements')->cascadeOnDelete();
            $table->string('image_path', 255);
            $table->timestamp('created_at')->useCurrent();
        });

        // 29. جدول advertisement_positions — أماكن ظهور الإعلانات
        Schema::create('advertisement_positions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_positions');
        Schema::dropIfExists('advertisement_images');
        Schema::dropIfExists('advertisements');
        Schema::dropIfExists('offer_images');
        Schema::dropIfExists('offers');
    }
};
