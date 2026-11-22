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
        Schema::table('profiles_craftsman', function (Blueprint $table) {
            if (! Schema::hasColumn('profiles_craftsman', 'age')) {
                $table->unsignedInteger('age')->nullable()->after('experience_years');
            }
            if (! Schema::hasColumn('profiles_craftsman', 'alternative_phone')) {
                $table->string('alternative_phone', 50)->nullable()->after('age');
            }
            if (! Schema::hasColumn('profiles_craftsman', 'address')) {
                $table->string('address', 255)->nullable()->after('alternative_phone');
            }
            if (! Schema::hasColumn('profiles_craftsman', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('address');
            }
            if (! Schema::hasColumn('profiles_craftsman', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (! Schema::hasColumn('profiles_craftsman', 'profession')) {
                $table->string('profession', 150)->nullable()->after('longitude');
            }
            if (! Schema::hasColumn('profiles_craftsman', 'custom_specialty')) {
                $table->string('custom_specialty', 255)->nullable()->after('profession');
            }
            if (! Schema::hasColumn('profiles_craftsman', 'identity_image')) {
                $table->string('identity_image', 255)->nullable()->after('custom_specialty');
            }
            if (! Schema::hasColumn('profiles_craftsman', 'national_id_back')) {
                $table->string('national_id_back', 255)->nullable()->after('identity_image');
            }
            if (! Schema::hasColumn('profiles_craftsman', 'cv_document')) {
                $table->string('cv_document', 255)->nullable()->after('national_id_back');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles_craftsman', function (Blueprint $table) {
            $table->dropColumn([
                'age',
                'alternative_phone',
                'address',
                'latitude',
                'longitude',
                'profession',
                'custom_specialty',
                'identity_image',
                'national_id_back',
                'cv_document',
            ]);
        });
    }
};
