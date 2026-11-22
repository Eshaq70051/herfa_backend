<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('request_attachments') && Schema::hasColumn('request_attachments', 'file_type')) {
            DB::statement("ALTER TABLE request_attachments MODIFY COLUMN file_type VARCHAR(50) NOT NULL DEFAULT 'image'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('request_attachments') && Schema::hasColumn('request_attachments', 'file_type')) {
            DB::statement("ALTER TABLE request_attachments MODIFY COLUMN file_type ENUM('image', 'document') NOT NULL DEFAULT 'image'");
        }
    }
};
