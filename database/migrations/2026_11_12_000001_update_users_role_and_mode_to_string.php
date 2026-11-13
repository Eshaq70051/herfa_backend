<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * تشغيل التعديل لتوسيع أعمدة الدور والنمط الحالي لمنع أي خطأ اقتطاع في MySQL.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(30) DEFAULT 'customer'");
            DB::statement("ALTER TABLE users MODIFY COLUMN current_mode VARCHAR(30) DEFAULT 'customer'");
        });
    }

    /**
     * التراجع عن التعديل.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('customer','craftsman','admin') DEFAULT 'customer'");
            DB::statement("ALTER TABLE users MODIFY COLUMN current_mode ENUM('customer','craftsman') DEFAULT 'customer'");
        });
    }
};
