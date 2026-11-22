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
        if (Schema::hasTable('craftsman_documents')) {
            DB::statement('ALTER TABLE `craftsman_documents` MODIFY COLUMN `document_type` VARCHAR(100) NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('craftsman_documents')) {
            DB::statement("ALTER TABLE `craftsman_documents` MODIFY COLUMN `document_type` ENUM('NATIONAL_ID_FRONT', 'NATIONAL_ID_BACK', 'PROFESSIONAL_CERTIFICATE', 'EXPERIENCE_CERTIFICATE', 'OTHER') NOT NULL");
        }
    }
};
