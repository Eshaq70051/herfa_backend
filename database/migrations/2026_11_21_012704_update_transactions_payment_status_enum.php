<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add pending_verification to transactions payment_status enum
        DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_status ENUM('pending','paid','failed','refunded','pending_verification') NOT NULL DEFAULT 'pending'");

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('receipt_image', 500)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending'");
    }
};
