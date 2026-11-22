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
        Schema::table('service_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('service_requests', 'craftsman_id')) {
                $table->unsignedBigInteger('craftsman_id')->nullable()->after('customer_id');
            }
            if (! Schema::hasColumn('service_requests', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('craftsman_id');
            }
            if (! Schema::hasColumn('service_requests', 'pricing_type')) {
                $table->string('pricing_type', 50)->default('fixed')->after('service_id');
            }
            if (! Schema::hasColumn('service_requests', 'inspection_fee')) {
                $table->decimal('inspection_fee', 12, 2)->default(0.00)->after('pricing_type');
            }
            if (! Schema::hasColumn('service_requests', 'labor_fee')) {
                $table->decimal('labor_fee', 12, 2)->default(0.00)->after('inspection_fee');
            }
            if (! Schema::hasColumn('service_requests', 'materials_fee')) {
                $table->decimal('materials_fee', 12, 2)->default(0.00)->after('labor_fee');
            }
            if (! Schema::hasColumn('service_requests', 'discount')) {
                $table->decimal('discount', 12, 2)->default(0.00)->after('materials_fee');
            }
            if (! Schema::hasColumn('service_requests', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->default(0.00)->after('discount');
            }
            if (! Schema::hasColumn('service_requests', 'platform_commission')) {
                $table->decimal('platform_commission', 12, 2)->default(0.00)->after('total_amount');
            }
            if (! Schema::hasColumn('service_requests', 'craftsman_earnings')) {
                $table->decimal('craftsman_earnings', 12, 2)->default(0.00)->after('platform_commission');
            }
            if (! Schema::hasColumn('service_requests', 'payment_method')) {
                $table->string('payment_method', 50)->default('cash')->after('craftsman_earnings');
            }
            if (! Schema::hasColumn('service_requests', 'payment_status')) {
                $table->string('payment_status', 50)->default('unpaid')->after('payment_method');
            }
            if (! Schema::hasColumn('service_requests', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('preferred_time');
            }
            if (! Schema::hasColumn('service_requests', 'is_urgent')) {
                $table->boolean('is_urgent')->default(false)->after('scheduled_at');
            }
            if (! Schema::hasColumn('service_requests', 'warranty_until')) {
                $table->timestamp('warranty_until')->nullable()->after('is_urgent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $cols = [
                'craftsman_id', 'category_id', 'pricing_type', 'inspection_fee',
                'labor_fee', 'materials_fee', 'discount', 'total_amount',
                'platform_commission', 'craftsman_earnings', 'payment_method',
                'payment_status', 'scheduled_at', 'is_urgent', 'warranty_until',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('service_requests', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
