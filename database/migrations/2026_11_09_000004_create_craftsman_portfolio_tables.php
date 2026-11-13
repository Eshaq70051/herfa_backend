<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 13. جدول craftsman_portfolio — معرض أعمال الحرفيين
        Schema::create('craftsman_portfolio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('craftsman_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('image_path', 255);
            $table->date('work_date')->nullable();
            $table->enum('review_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });

        // 14. جدول craftsman_certificates — شهادات الحرفيين
        Schema::create('craftsman_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('craftsman_id')->constrained('users')->cascadeOnDelete();
            $table->string('certificate_name', 150);
            $table->string('issuing_authority', 150)->nullable();
            $table->string('certificate_image', 255);
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('craftsman_certificates');
        Schema::dropIfExists('craftsman_portfolio');
    }
};
