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
        // 1. service_requests: إضافة حقول الصور (صورة المشكلة وصورة إنجاز العمل)
        Schema::table('service_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('service_requests', 'image')) {
                $table->string('image', 255)->nullable()->after('description');
            }
            if (! Schema::hasColumn('service_requests', 'completion_image')) {
                $table->string('completion_image', 255)->nullable()->after('image');
            }
        });

        // 2. services: إضافة حقل الأيقونة icon
        Schema::table('services', function (Blueprint $table) {
            if (! Schema::hasColumn('services', 'icon')) {
                $table->string('icon', 100)->nullable()->after('image');
            }
        });

        // 3. reviews: إضافة حقل صورة التقييم
        Schema::table('reviews', function (Blueprint $table) {
            if (! Schema::hasColumn('reviews', 'image')) {
                $table->string('image', 255)->nullable()->after('comment');
            }
        });

        // 4. support_tickets: إضافة حقل المرفق / الصورة
        Schema::table('support_tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('support_tickets', 'image')) {
                $table->string('image', 255)->nullable()->after('description');
            }
        });

        // 5. craftsman_profiles: إضافة صور الهوية الخلفية والترخيص المهني
        Schema::table('craftsman_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('craftsman_profiles', 'national_id_back')) {
                $table->string('national_id_back', 255)->nullable()->after('identity_image');
            }
            if (! Schema::hasColumn('craftsman_profiles', 'license_image')) {
                $table->string('license_image', 255)->nullable()->after('national_id_back');
            }
        });

        // 6. cancellation_reasons: إضافة أيقونة سبب الإلغاء
        Schema::table('cancellation_reasons', function (Blueprint $table) {
            if (! Schema::hasColumn('cancellation_reasons', 'icon')) {
                $table->string('icon', 100)->nullable()->after('reason');
            }
        });

        // 7. notification_types: إضافة أيقونة وصورة لنوع الإشعار
        Schema::table('notification_types', function (Blueprint $table) {
            if (! Schema::hasColumn('notification_types', 'icon')) {
                $table->string('icon', 100)->nullable()->after('name');
            }
            if (! Schema::hasColumn('notification_types', 'image')) {
                $table->string('image', 255)->nullable()->after('icon');
            }
        });

        // 8. governorates: إضافة صورة للمحافظة
        Schema::table('governorates', function (Blueprint $table) {
            if (! Schema::hasColumn('governorates', 'image')) {
                $table->string('image', 255)->nullable()->after('name');
            }
        });

        // 9. districts: إضافة صورة للمديرية
        Schema::table('districts', function (Blueprint $table) {
            if (! Schema::hasColumn('districts', 'image')) {
                $table->string('image', 255)->nullable()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('service_requests', 'image')) {
                $table->dropColumn('image');
            }
            if (Schema::hasColumn('service_requests', 'completion_image')) {
                $table->dropColumn('completion_image');
            }
        });

        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'icon')) {
                $table->dropColumn('icon');
            }
        });

        Schema::table('reviews', function (Blueprint $table) {
            if (Schema::hasColumn('reviews', 'image')) {
                $table->dropColumn('image');
            }
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('support_tickets', 'image')) {
                $table->dropColumn('image');
            }
        });

        Schema::table('craftsman_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('craftsman_profiles', 'national_id_back')) {
                $table->dropColumn('national_id_back');
            }
            if (Schema::hasColumn('craftsman_profiles', 'license_image')) {
                $table->dropColumn('license_image');
            }
        });

        Schema::table('cancellation_reasons', function (Blueprint $table) {
            if (Schema::hasColumn('cancellation_reasons', 'icon')) {
                $table->dropColumn('icon');
            }
        });

        Schema::table('notification_types', function (Blueprint $table) {
            if (Schema::hasColumn('notification_types', 'icon')) {
                $table->dropColumn('icon');
            }
            if (Schema::hasColumn('notification_types', 'image')) {
                $table->dropColumn('image');
            }
        });

        Schema::table('governorates', function (Blueprint $table) {
            if (Schema::hasColumn('governorates', 'image')) {
                $table->dropColumn('image');
            }
        });

        Schema::table('districts', function (Blueprint $table) {
            if (Schema::hasColumn('districts', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};
