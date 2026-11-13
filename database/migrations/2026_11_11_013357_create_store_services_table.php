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
        Schema::create('store_services', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الخدمة أو نوع الخدمة مثل: عاملات منزلية بالساعة
            $table->string('company_name'); // اسم الشركة أو المتجر مثل: تراك كلين Track Clean
            $table->text('description')->nullable();
            $table->string('logo')->nullable(); // مسار أو رابط الشعار
            $table->string('cover_image')->nullable();
            $table->string('phone')->nullable(); // رقم التواصل والواتساب
            $table->decimal('rating', 3, 1)->default(4.8);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->json('tags')->nullable(); // وسوم الخدمات
            $table->string('badge')->nullable()->default('معتمد'); // شارة التوثيق
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_services');
    }
};
