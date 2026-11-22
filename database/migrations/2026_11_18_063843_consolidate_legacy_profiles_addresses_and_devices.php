<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('profiles_craftsman')->insertUsing(
            [
                'id',
                'user_id',
                'profession',
                'experience_years',
                'bio',
                'city',
                'address',
                'latitude',
                'longitude',
                'identity_image',
                'national_id_back',
                'license_image',
                'identity_status',
                'skill_status',
                'availability_status',
                'approval_status',
                'rejection_reason',
                'commission_rate',
                'created_at',
                'updated_at',
            ],
            DB::table('craftsman_profiles')->select([
                'id',
                'user_id',
                'profession',
                'experience_years',
                'bio',
                'city',
                'address',
                'latitude',
                'longitude',
                'identity_image',
                'national_id_back',
                'license_image',
                'identity_status',
                'skill_status',
                'availability_status',
                'approval_status',
                'rejection_reason',
                'commission_rate',
                'created_at',
                'updated_at',
            ]),
        );

        DB::table('addresses_user')->insertUsing(
            [
                'id',
                'user_id',
                'district_id',
                'neighborhood_id',
                'address_details',
                'latitude',
                'longitude',
                'label',
                'is_default',
                'governorate_id',
                'neighborhood',
                'created_at',
                'updated_at',
            ],
            DB::table('user_addresses')->select([
                'id',
                'user_id',
                'district_id',
                DB::raw('NULL'),
                'address_details',
                'latitude',
                'longitude',
                'label',
                'is_default',
                'governorate_id',
                'neighborhood',
                'created_at',
                'updated_at',
            ]),
        );

        DB::table('devices_user')->insertUsing(
            [
                'id',
                'user_id',
                'device_token',
                'device_type',
                'device_name',
                'is_active',
                'last_used_at',
                'created_at',
                'updated_at',
            ],
            DB::table('user_devices')->select([
                'id',
                'user_id',
                'device_token',
                'device_type',
                'device_name',
                'is_active',
                'last_used_at',
                'created_at',
                'updated_at',
            ]),
        );

        foreach ([
            'craftsman_profiles' => 'profiles_craftsman',
            'user_addresses' => 'addresses_user',
            'user_devices' => 'devices_user',
        ] as $sourceTable => $targetTable) {
            if (DB::table($sourceTable)->count() !== DB::table($targetTable)->count()) {
                throw new RuntimeException("Data copy verification failed for {$sourceTable}.");
            }
        }

        Schema::table('service_requests', function (Blueprint $table): void {
            $table->dropForeign('service_requests_address_id_foreign');
        });

        Schema::table('service_requests', function (Blueprint $table): void {
            $table->foreign('address_id', 'service_requests_address_id_foreign')
                ->references('id')
                ->on('addresses_user')
                ->nullOnDelete();
        });

        Schema::drop('craftsman_profiles');
        Schema::drop('user_addresses');
        Schema::drop('user_devices');
    }

    public function down(): void
    {
        Schema::create('craftsman_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('profession', 100)->nullable();
            $table->unsignedInteger('experience_years')->default(1);
            $table->text('bio')->nullable();
            $table->string('city', 100)->nullable()->default('الرياض');
            $table->string('address', 255)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('identity_image', 255)->nullable();
            $table->string('national_id_back', 255)->nullable();
            $table->string('license_image', 255)->nullable();
            $table->enum('identity_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('identity_rejection_reason')->nullable();
            $table->enum('skill_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('availability_status')->default(true);
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('approval_rejection_reason')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(15);
            $table->timestamps();
        });

        Schema::create('user_addresses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('governorate_id')->nullable()->constrained('governorates')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->string('neighborhood', 150)->nullable();
            $table->foreignId('neighborhood_id')->nullable()->constrained('neighborhoods')->nullOnDelete();
            $table->string('address_details', 255)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('label', 50)->default('المنزل');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('user_devices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('device_token', 500);
            $table->enum('device_type', ['android', 'ios'])->default('android');
            $table->string('device_name', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        DB::table('craftsman_profiles')->insertUsing(
            [
                'id',
                'user_id',
                'profession',
                'experience_years',
                'bio',
                'city',
                'address',
                'latitude',
                'longitude',
                'identity_image',
                'national_id_back',
                'license_image',
                'identity_status',
                'skill_status',
                'availability_status',
                'approval_status',
                'rejection_reason',
                'commission_rate',
                'created_at',
                'updated_at',
                'identity_rejection_reason',
                'approval_rejection_reason',
            ],
            DB::table('profiles_craftsman')->select([
                'id',
                'user_id',
                'profession',
                'experience_years',
                'bio',
                'city',
                'address',
                'latitude',
                'longitude',
                'identity_image',
                'national_id_back',
                'license_image',
                'identity_status',
                'skill_status',
                'availability_status',
                'approval_status',
                'rejection_reason',
                'commission_rate',
                'created_at',
                'updated_at',
                'identity_rejection_reason',
                'approval_rejection_reason',
            ]),
        );

        DB::table('user_addresses')->insertUsing(
            [
                'id',
                'user_id',
                'governorate_id',
                'district_id',
                'neighborhood',
                'neighborhood_id',
                'address_details',
                'latitude',
                'longitude',
                'label',
                'is_default',
                'created_at',
                'updated_at',
            ],
            DB::table('addresses_user')->select([
                'id',
                'user_id',
                'governorate_id',
                'district_id',
                'neighborhood',
                'neighborhood_id',
                'address_details',
                'latitude',
                'longitude',
                'label',
                'is_default',
                'created_at',
                'updated_at',
            ]),
        );

        DB::table('user_devices')->insertUsing(
            [
                'id',
                'user_id',
                'device_token',
                'device_type',
                'device_name',
                'is_active',
                'last_used_at',
                'created_at',
                'updated_at',
            ],
            DB::table('devices_user')->select([
                'id',
                'user_id',
                'device_token',
                'device_type',
                'device_name',
                'is_active',
                'last_used_at',
                'created_at',
                'updated_at',
            ]),
        );

        Schema::table('service_requests', function (Blueprint $table): void {
            $table->dropForeign('service_requests_address_id_foreign');
        });

        Schema::table('service_requests', function (Blueprint $table): void {
            $table->foreign('address_id', 'service_requests_address_id_foreign')
                ->references('id')
                ->on('user_addresses')
                ->nullOnDelete();
        });
    }
};
