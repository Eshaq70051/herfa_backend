<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('image')->nullable()->after('description');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('receipt_image')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('image');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('receipt_image');
        });
    }
};
