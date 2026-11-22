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
        Schema::table('service_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('service_requests', 'completion_image')) {
                $table->string('completion_image', 500)->nullable()->after('completion_otp');
            }
            if (! Schema::hasColumn('service_requests', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('completion_image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('service_requests', 'completion_image')) {
                $table->dropColumn('completion_image');
            }
            if (Schema::hasColumn('service_requests', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
        });
    }
};
