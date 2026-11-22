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
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('transactions', 'receipt_image')) {
                    $table->string('receipt_image')->nullable()->after('payment_status');
                }
                if (! Schema::hasColumn('transactions', 'notes')) {
                    $table->text('notes')->nullable()->after('provider_transaction_id');
                }
            });
        }

        if (Schema::hasTable('service_requests')) {
            Schema::table('service_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('service_requests', 'receipt_image')) {
                    $table->string('receipt_image')->nullable()->after('payment_status');
                }
                if (! Schema::hasColumn('service_requests', 'transfer_reference')) {
                    $table->string('transfer_reference', 150)->nullable()->after('receipt_image');
                }
            });
        }

        if (Schema::hasTable('request_assignments')) {
            Schema::table('request_assignments', function (Blueprint $table) {
                if (! Schema::hasColumn('request_assignments', 'opened_at')) {
                    $table->timestamp('opened_at')->nullable()->after('assigned_at');
                }
                if (! Schema::hasColumn('request_assignments', 'accepted_at')) {
                    $table->timestamp('accepted_at')->nullable()->after('opened_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (Schema::hasColumn('transactions', 'receipt_image')) {
                    $table->dropColumn('receipt_image');
                }
                if (Schema::hasColumn('transactions', 'notes')) {
                    $table->dropColumn('notes');
                }
            });
        }

        if (Schema::hasTable('service_requests')) {
            Schema::table('service_requests', function (Blueprint $table) {
                if (Schema::hasColumn('service_requests', 'receipt_image')) {
                    $table->dropColumn('receipt_image');
                }
                if (Schema::hasColumn('service_requests', 'transfer_reference')) {
                    $table->dropColumn('transfer_reference');
                }
            });
        }

        if (Schema::hasTable('request_assignments')) {
            Schema::table('request_assignments', function (Blueprint $table) {
                if (Schema::hasColumn('request_assignments', 'opened_at')) {
                    $table->dropColumn('opened_at');
                }
                if (Schema::hasColumn('request_assignments', 'accepted_at')) {
                    $table->dropColumn('accepted_at');
                }
            });
        }
    }
};
