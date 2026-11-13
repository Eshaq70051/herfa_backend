<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * يضيف حقل status نصي لجدول service_requests ليتوافق مع ما يرسله الكنترولر
 * مباشرة مثل: pending_craftsman, accepted, on_the_way, arrived, in_progress, completed, cancelled.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('service_requests', 'status')) {
                $table->string('status', 50)->default('pending_craftsman')->after('status_id');
            }
            if (!Schema::hasColumn('service_requests', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('status');
            }
            if (!Schema::hasColumn('service_requests', 'cancelled_by')) {
                $table->string('cancelled_by', 20)->nullable()->after('cancellation_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn(['status', 'cancellation_reason', 'cancelled_by']);
        });
    }
};
