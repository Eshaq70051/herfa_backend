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
        Schema::table('order_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('order_messages', 'file_path')) {
                $table->string('file_path', 500)->nullable()->after('message');
            }
            if (Schema::hasColumn('order_messages', 'message')) {
                $table->text('message')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_messages', function (Blueprint $table) {
            if (Schema::hasColumn('order_messages', 'file_path')) {
                $table->dropColumn('file_path');
            }
        });
    }
};
