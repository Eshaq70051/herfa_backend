<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 18. جدول request_statuses — حالات الطلبات
        Schema::create('request_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('description', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 21. جدول cancellation_reasons — أسباب إلغاء الطلبات
        Schema::create('cancellation_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('reason', 255);
            $table->enum('cancelled_by_type', ['customer', 'craftsman', 'admin'])->default('customer');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 15. جدول service_requests — طلبات الخدمات
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 30)->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('address_id')->nullable()->constrained('user_addresses')->nullOnDelete();
            $table->text('description');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('address_details', 255)->nullable();
            $table->date('preferred_date')->nullable();
            $table->time('preferred_time')->nullable();
            $table->foreignId('status_id')->constrained('request_statuses')->cascadeOnDelete();
            $table->foreignId('cancellation_reason_id')->nullable()->constrained('cancellation_reasons')->nullOnDelete();
            $table->text('cancellation_notes')->nullable();
            $table->timestamps();
        });

        // 16. جدول request_details — تفاصيل الطلبات
        Schema::create('request_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->string('detail_name', 150);
            $table->text('detail_value');
            $table->timestamp('created_at')->useCurrent();
        });

        // 17. جدول request_assignments — إسناد الطلبات
        Schema::create('request_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->foreignId('craftsman_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('assignment_status', ['pending', 'accepted', 'rejected', 'cancelled', 'completed'])->default('pending');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });

        // 19. جدول request_status_history — سجل حالات الطلبات
        Schema::create('request_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->foreignId('old_status_id')->nullable()->constrained('request_statuses')->nullOnDelete();
            $table->foreignId('new_status_id')->constrained('request_statuses')->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 20. جدول request_attachments — مرفقات الطلبات (الصور والمرفقات)
        Schema::create('request_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->string('file_path', 255);
            $table->enum('file_type', ['image', 'document'])->default('image');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_attachments');
        Schema::dropIfExists('request_status_history');
        Schema::dropIfExists('request_assignments');
        Schema::dropIfExists('request_details');
        Schema::dropIfExists('service_requests');
        Schema::dropIfExists('cancellation_reasons');
        Schema::dropIfExists('request_statuses');
    }
};
