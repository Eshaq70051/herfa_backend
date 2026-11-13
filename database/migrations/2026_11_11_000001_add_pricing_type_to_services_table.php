<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('services', 'pricing_type')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('pricing_type', 30)->default('inspection')->after('base_price');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('services', 'pricing_type')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropColumn('pricing_type');
            });
        }
    }
};
