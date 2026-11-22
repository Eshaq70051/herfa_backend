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
        $tables = [
            'request_assignments' => 'request_assignments_craftsman_id_foreign',
            'craftsman_services' => 'craftsman_services_craftsman_id_foreign',
            'craftsman_portfolio' => 'craftsman_portfolio_craftsman_id_foreign',
            'craftsman_certificates' => 'craftsman_certificates_craftsman_id_foreign',
            'reviews' => 'reviews_craftsman_id_foreign',
            'review_replies' => 'review_replies_craftsman_id_foreign',
            'craftsman_specialty_changes' => 'craftsman_specialty_changes_craftsman_id_foreign',
        ];

        foreach ($tables as $tableName => $fkName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'craftsman_id')) {
                $exists = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
                    WHERE CONSTRAINT_SCHEMA = DATABASE() 
                      AND TABLE_NAME = ? 
                      AND CONSTRAINT_NAME = ? 
                      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                ", [$tableName, $fkName]);

                if (! empty($exists)) {
                    Schema::table($tableName, function (Blueprint $table) use ($fkName) {
                        $table->dropForeign($fkName);
                    });
                }

                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreign('craftsman_id')
                        ->references('id')
                        ->on('users')
                        ->cascadeOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'request_assignments' => 'request_assignments_craftsman_id_foreign',
            'craftsman_services' => 'craftsman_services_craftsman_id_foreign',
            'craftsman_portfolio' => 'craftsman_portfolio_craftsman_id_foreign',
            'craftsman_certificates' => 'craftsman_certificates_craftsman_id_foreign',
            'reviews' => 'reviews_craftsman_id_foreign',
            'review_replies' => 'review_replies_craftsman_id_foreign',
            'craftsman_specialty_changes' => 'craftsman_specialty_changes_craftsman_id_foreign',
        ];

        foreach ($tables as $tableName => $fkName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'craftsman_id')) {
                $exists = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
                    WHERE CONSTRAINT_SCHEMA = DATABASE() 
                      AND TABLE_NAME = ? 
                      AND CONSTRAINT_NAME = ? 
                      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                ", [$tableName, $fkName]);

                if (! empty($exists)) {
                    Schema::table($tableName, function (Blueprint $table) use ($fkName) {
                        $table->dropForeign($fkName);
                    });
                }

                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreign('craftsman_id')
                        ->references('id')
                        ->on('profiles_craftsman')
                        ->cascadeOnDelete();
                });
            }
        }
    }
};
