<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            if (!Schema::hasColumn('advertisements', 'image')) {
                $table->string('image')->nullable()->after('target_url');
            }
        });

        Schema::table('offers', function (Blueprint $table) {
            if (!Schema::hasColumn('offers', 'image')) {
                $table->string('image')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('advertisements', function (Blueprint $table) {
            if (Schema::hasColumn('advertisements', 'image')) {
                $table->dropColumn('image');
            }
        });

        Schema::table('offers', function (Blueprint $table) {
            if (Schema::hasColumn('offers', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};
