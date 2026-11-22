<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create coupons table if not exists
        if (! Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 50)->unique();
                $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
                $table->decimal('discount_value', 10, 2);
                $table->decimal('min_order_value', 10, 2)->default(0);
                $table->decimal('max_discount', 10, 2)->nullable();
                $table->integer('usage_limit')->nullable();
                $table->integer('used_count')->default(0);
                $table->dateTime('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Create coupon_redemptions table if not exists
        if (! Schema::hasTable('coupon_redemptions')) {
            Schema::create('coupon_redemptions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('service_request_id')->constrained('service_requests')->cascadeOnDelete();
                $table->decimal('discount_applied', 12, 2);
                $table->timestamp('redeemed_at')->useCurrent();
                $table->timestamps();
                $table->unique(['coupon_id', 'service_request_id']);
                $table->index(['user_id', 'coupon_id']);
            });
        }

        // 3. Create wallet_transactions table if not exists
        if (! Schema::hasTable('wallet_transactions')) {
            Schema::create('wallet_transactions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('service_requests')->nullOnDelete();
                $table->enum('type', ['deposit', 'withdrawal', 'payment', 'refund', 'cashback', 'hold', 'release']);
                $table->decimal('amount', 14, 2);
                $table->decimal('balance_after', 14, 2);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->index(['user_id', 'created_at']);
            });
        }

        // 4. Create payout_requests table if not exists
        if (! Schema::hasTable('payout_requests')) {
            Schema::create('payout_requests', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('craftsman_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('amount', 14, 2);
                $table->string('bank_name', 150);
                $table->string('iban', 100);
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('admin_notes')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }

        // 5. Create order_offers table if not exists
        if (! Schema::hasTable('order_offers')) {
            Schema::create('order_offers', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->constrained('service_requests')->cascadeOnDelete();
                $table->foreignId('craftsman_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('proposed_price', 14, 2);
                $table->string('estimated_duration', 100)->nullable();
                $table->text('notes')->nullable();
                $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
                $table->timestamps();
                $table->unique(['order_id', 'craftsman_id']);
            });
        }

        // 6. Create service_request_disputes table if not exists
        if (! Schema::hasTable('service_request_disputes')) {
            Schema::create('service_request_disputes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('service_request_id')->constrained('service_requests')->cascadeOnDelete();
                $table->foreignId('raised_by_user_id')->constrained('users')->restrictOnDelete();
                $table->string('dispute_reason', 255);
                $table->text('details');
                $table->enum('dispute_status', ['OPENED', 'UNDER_REVIEW', 'RESOLVED_REFUND', 'RESOLVED_REWORK', 'REJECTED'])->default('OPENED');
                $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('resolution_notes')->nullable();
                $table->decimal('refund_amount', 12, 2)->default(0);
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
                $table->index('service_request_id');
            });
        }

        // 7. Create user_otps table if not exists
        if (! Schema::hasTable('user_otps')) {
            Schema::create('user_otps', function (Blueprint $table): void {
                $table->id();
                $table->string('phone', 50);
                $table->char('phone_hash', 64)->nullable();
                $table->string('otp_code', 10);
                $table->enum('purpose', ['LOGIN', 'REGISTER', 'PHONE_VERIFICATION', 'PASSWORD_RESET', 'ORDER_COMPLETION']);
                $table->unsignedTinyInteger('attempts_count')->default(0);
                $table->boolean('is_used')->default(false);
                $table->timestamp('expires_at');
                $table->timestamps();
                $table->index(['phone', 'purpose', 'is_used', 'expires_at']);
            });
        }

        // 8. Create customer_ratings_by_craftsman table if not exists
        if (! Schema::hasTable('customer_ratings_by_craftsman')) {
            Schema::create('customer_ratings_by_craftsman', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('service_request_id')->constrained('service_requests')->cascadeOnDelete();
                $table->foreignId('craftsman_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
                $table->unsignedTinyInteger('rating');
                $table->text('comment')->nullable();
                $table->unsignedTinyInteger('cooperation_rating')->nullable();
                $table->unsignedTinyInteger('payment_punctuality_rating')->nullable();
                $table->timestamps();
                $table->unique('service_request_id');
            });
        }

        // 9. Add missing columns to service_requests
        Schema::table('service_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('service_requests', 'completion_otp')) {
                $table->char('completion_otp', 6)->nullable()->after('status');
            }
            if (! Schema::hasColumn('service_requests', 'is_emergency')) {
                $table->boolean('is_emergency')->default(false)->after('completion_otp');
            }
            if (! Schema::hasColumn('service_requests', 'estimated_cost')) {
                $table->decimal('estimated_cost', 14, 2)->nullable()->after('is_emergency');
            }
            if (! Schema::hasColumn('service_requests', 'final_total_amount')) {
                $table->decimal('final_total_amount', 14, 2)->nullable()->after('estimated_cost');
            }
            if (! Schema::hasColumn('service_requests', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('final_total_amount');
            }
            if (! Schema::hasColumn('service_requests', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('started_at');
            }
        });

        // 10. Add missing columns to users
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'failed_login_attempts')) {
                $table->unsignedTinyInteger('failed_login_attempts')->default(0)->after('last_login_at');
            }
            if (! Schema::hasColumn('users', 'lockout_until')) {
                $table->timestamp('lockout_until')->nullable()->after('failed_login_attempts');
            }
            if (! Schema::hasColumn('users', 'preferred_language')) {
                $table->string('preferred_language', 10)->default('ar')->after('lockout_until');
            }
        });

        // 11. Add missing columns to profiles_craftsman
        Schema::table('profiles_craftsman', function (Blueprint $table): void {
            if (! Schema::hasColumn('profiles_craftsman', 'rating_avg')) {
                $table->decimal('rating_avg', 3, 2)->default(0)->after('commission_rate');
            }
            if (! Schema::hasColumn('profiles_craftsman', 'total_reviews_count')) {
                $table->unsignedInteger('total_reviews_count')->default(0)->after('rating_avg');
            }
            if (! Schema::hasColumn('profiles_craftsman', 'completed_requests_count')) {
                $table->unsignedInteger('completed_requests_count')->default(0)->after('total_reviews_count');
            }
            if (! Schema::hasColumn('profiles_craftsman', 'max_travel_distance_km')) {
                $table->unsignedSmallInteger('max_travel_distance_km')->default(25)->after('completed_requests_count');
            }
            if (! Schema::hasColumn('profiles_craftsman', 'bank_name')) {
                $table->string('bank_name', 100)->nullable()->after('max_travel_distance_km');
            }
            if (! Schema::hasColumn('profiles_craftsman', 'iban')) {
                $table->string('iban', 50)->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('profiles_craftsman', 'account_holder_name')) {
                $table->string('account_holder_name', 150)->nullable()->after('iban');
            }
        });

        // 12. Add missing columns to services
        Schema::table('services', function (Blueprint $table): void {
            if (! Schema::hasColumn('services', 'estimated_duration_minutes')) {
                $table->unsignedInteger('estimated_duration_minutes')->nullable()->after('pricing_type');
            }
            if (! Schema::hasColumn('services', 'requires_materials')) {
                $table->boolean('requires_materials')->default(false)->after('estimated_duration_minutes');
            }
        });

        // 13. Add missing columns to purchase_invoices
        if (Schema::hasTable('purchase_invoices')) {
            Schema::table('purchase_invoices', function (Blueprint $table): void {
                if (! Schema::hasColumn('purchase_invoices', 'payment_status')) {
                    $table->enum('payment_status', ['UNPAID', 'PARTIALLY_PAID', 'PAID'])->default('UNPAID')->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_ratings_by_craftsman');
        Schema::dropIfExists('user_otps');
        Schema::dropIfExists('service_request_disputes');
        Schema::dropIfExists('order_offers');
        Schema::dropIfExists('payout_requests');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('coupon_redemptions');
        Schema::dropIfExists('coupons');

        Schema::table('service_requests', function (Blueprint $table): void {
            $table->dropColumn([
                'completion_otp',
                'is_emergency',
                'estimated_cost',
                'final_total_amount',
                'started_at',
                'completed_at',
            ]);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'failed_login_attempts',
                'lockout_until',
                'preferred_language',
            ]);
        });

        Schema::table('profiles_craftsman', function (Blueprint $table): void {
            $table->dropColumn([
                'rating_avg',
                'total_reviews_count',
                'completed_requests_count',
                'max_travel_distance_km',
                'bank_name',
                'iban',
                'account_holder_name',
            ]);
        });

        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn([
                'estimated_duration_minutes',
                'requires_materials',
            ]);
        });

        if (Schema::hasTable('purchase_invoices')) {
            Schema::table('purchase_invoices', function (Blueprint $table): void {
                if (Schema::hasColumn('purchase_invoices', 'payment_status')) {
                    $table->dropColumn('payment_status');
                }
            });
        }
    }
};
