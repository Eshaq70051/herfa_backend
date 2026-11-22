<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table): void {
            $table->id();
            $table->char('code', 3)->unique();
            $table->string('name', 100);
            $table->string('symbol', 10);
            $table->unsignedTinyInteger('decimal_places')->default(2);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('service_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained()->restrictOnDelete();
            $table->decimal('price', 14, 2);
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['service_id', 'is_active']);
        });

        Schema::create('accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('account_code', 30)->unique();
            $table->string('account_name', 150);
            $table->foreignId('parent_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->enum('account_type', ['ASSET', 'LIABILITY', 'EQUITY', 'REVENUE', 'EXPENSE']);
            $table->enum('normal_balance', ['DEBIT', 'CREDIT']);
            $table->boolean('is_control_account')->default(false);
            $table->boolean('is_postable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('symbol', 20);
            $table->foreignId('base_unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->decimal('conversion_factor', 12, 3)->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('profiles_craftsman', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('profession', 100)->nullable();
            $table->unsignedInteger('experience_years')->default(1);
            $table->text('bio')->nullable();
            $table->string('city', 100)->nullable()->default('الرياض');
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('identity_image')->nullable();
            $table->string('national_id_back')->nullable();
            $table->string('license_image')->nullable();
            $table->enum('identity_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('identity_rejection_reason')->nullable();
            $table->enum('skill_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('availability_status')->default(false);
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('approval_rejection_reason')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(15);
            $table->timestamps();
        });

        Schema::create('addresses_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('neighborhood_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('governorate_id')->nullable()->constrained()->nullOnDelete();
            $table->string('neighborhood', 150)->nullable();
            $table->string('address_details')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('label', 50);
            $table->boolean('is_default')->default(false);
            $table->unsignedBigInteger('default_user_id')->nullable()->storedAs('CASE WHEN is_default = 1 THEN user_id ELSE NULL END');
            $table->unique('default_user_id', 'addresses_user_single_default_unique');
            $table->timestamps();
        });

        Schema::create('devices_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_token', 500)->unique();
            $table->enum('device_type', ['android', 'ios']);
            $table->string('device_name', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('company_working_hours', function (Blueprint $table): void {
            $table->id();
            $table->string('timezone', 50)->default('Asia/Aden');
            $table->json('working_days');
            $table->time('opening_time');
            $table->time('closing_time');
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('accounting_periods', function (Blueprint $table): void {
            $table->id();
            $table->string('period_code', 30)->unique();
            $table->string('name', 100);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['OPEN', 'CLOSED', 'LOCKED'])->default('OPEN');
            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('locked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['start_date', 'end_date']);
        });

        Schema::create('departments', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 30)->unique();
            $table->text('description')->nullable();
            $table->foreignId('parent_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->unsignedBigInteger('manager_employee_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employee_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('employee_number', 30)->unique();
            $table->string('job_title', 150)->nullable();
            $table->foreignId('reports_to_employee_id')->nullable()->constrained('employee_profiles')->nullOnDelete();
            $table->enum('employment_type', ['FULL_TIME', 'PART_TIME', 'CONTRACT']);
            $table->enum('employment_status', ['ACTIVE', 'ON_LEAVE', 'SUSPENDED', 'TERMINATED'])->default('ACTIVE');
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('departments', function (Blueprint $table): void {
            $table->foreign('manager_employee_id')->references('id')->on('employee_profiles')->nullOnDelete();
        });

        Schema::create('employee_departments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->date('assigned_at');
            $table->date('ended_at')->nullable();
            $table->timestamps();
            $table->unique(['employee_profile_id', 'department_id', 'assigned_at'], 'employee_dept_assignment_unique');
        });

        Schema::create('exchange_rates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('from_currency_id')->constrained('currencies')->restrictOnDelete();
            $table->foreignId('to_currency_id')->constrained('currencies')->restrictOnDelete();
            $table->decimal('exchange_rate', 20, 8);
            $table->string('source', 100)->nullable();
            $table->timestamp('effective_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['from_currency_id', 'to_currency_id', 'effective_at'], 'unique_currency_rate_effective');
        });

        Schema::create('journal_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('entry_number', 50)->unique();
            $table->date('entry_date');
            $table->text('description');
            $table->string('source_type', 50)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->enum('status', ['DRAFT', 'POSTED', 'REVERSED'])->default('DRAFT');
            $table->foreignId('reversal_of_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->text('reversal_reason')->nullable();
            $table->foreignId('accounting_period_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('posted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            $table->index(['accounting_period_id', 'entry_date']);
            $table->index(['status', 'entry_date']);
        });

        Schema::create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->string('supplier_code', 30)->unique();
            $table->string('name', 150);
            $table->string('contact_person', 150)->nullable();
            $table->string('phone', 20);
            $table->string('email', 150)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->foreignId('account_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('default_currency_id')->constrained('currencies')->restrictOnDelete();
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->date('opening_balance_date')->nullable();
            $table->string('tax_number', 50)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('service_request_materials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('material_name', 150);
            $table->text('description')->nullable();
            $table->decimal('quantity', 12, 3);
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->enum('status', ['PENDING_APPROVAL', 'APPROVED', 'REJECTED', 'PARTIALLY_PURCHASED', 'PURCHASED', 'CANCELLED'])->default('PENDING_APPROVAL');
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['service_request_id']);
            $table->index(['status']);
        });

        Schema::create('purchase_invoices', function (Blueprint $table): void {
            $table->id();
            $table->string('invoice_number', 50);
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 14, 2);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2);
            $table->enum('status', ['DRAFT', 'POSTED', 'CANCELLED'])->default('DRAFT');
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('posted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            $table->unique(['supplier_id', 'invoice_number']);
            $table->index(['supplier_id', 'invoice_date']);
            $table->index(['status', 'invoice_date']);
        });

        Schema::create('purchase_invoice_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_request_material_id')->nullable()->constrained()->nullOnDelete();
            $table->string('material_name', 150);
            $table->text('description')->nullable();
            $table->decimal('quantity', 12, 3);
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->decimal('unit_price', 14, 2);
            $table->decimal('line_total', 14, 2);
            $table->timestamps();
        });

        Schema::create('journal_entry_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('line_number');
            $table->text('description')->nullable();
            $table->decimal('debit_amount', 14, 2)->default(0);
            $table->decimal('credit_amount', 14, 2)->default(0);
            $table->foreignId('currency_id')->nullable()->constrained()->restrictOnDelete();
            $table->decimal('foreign_amount', 14, 2)->nullable();
            $table->decimal('exchange_rate', 18, 8)->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('craftsman_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('service_request_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->unique(['journal_entry_id', 'line_number']);
        });

        Schema::create('accounting_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event_number', 50)->unique();
            $table->string('event_type', 50);
            $table->date('event_date');
            $table->text('description')->nullable();
            $table->string('idempotency_key', 150)->unique();
            $table->enum('status', ['PENDING', 'POSTED', 'FAILED', 'CANCELLED'])->default('PENDING');
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
            $table->index(['event_type', 'event_date']);
            $table->index(['status', 'event_date']);
        });

        Schema::create('accounting_event_journal_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('accounting_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['accounting_event_id', 'journal_entry_id'], 'event_journal_unique');
        });

        Schema::create('craftsman_earnings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_request_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('craftsman_user_id')->constrained('users')->restrictOnDelete();
            $table->decimal('total_service_amount', 14, 2);
            $table->decimal('materials_cost', 14, 2)->default(0);
            $table->decimal('extra_fees', 14, 2)->default(0);
            $table->decimal('total_customer_amount', 14, 2);
            $table->decimal('craftsman_share_amount', 14, 2);
            $table->decimal('company_share_amount', 14, 2);
            $table->foreignId('currency_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['PENDING_APPROVAL', 'APPROVED', 'CANCELLED'])->default('PENDING_APPROVAL');
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('craftsman_collections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_request_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('craftsman_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('customer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained()->restrictOnDelete();
            $table->decimal('collected_amount', 14, 2);
            $table->timestamp('collected_at');
            $table->string('receipt_number', 50)->unique();
            $table->enum('status', ['PENDING_VERIFICATION', 'CONFIRMED', 'REJECTED', 'CANCELLED'])->default('PENDING_VERIFICATION');
            $table->text('exception_reason')->nullable();
            $table->foreignId('exception_approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('exception_approved_at')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['craftsman_user_id', 'status']);
        });

        Schema::create('craftsman_settlements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('earning_id')->unique()->constrained('craftsman_earnings')->restrictOnDelete();
            $table->foreignId('collection_id')->unique()->constrained('craftsman_collections')->restrictOnDelete();
            $table->decimal('collected_amount', 14, 2);
            $table->decimal('craftsman_earning_amount', 14, 2);
            $table->decimal('company_due_amount', 14, 2);
            $table->timestamp('due_at');
            $table->enum('status', ['PENDING', 'PARTIALLY_REMITTED', 'REMITTED', 'CANCELLED'])->default('PENDING');
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['status', 'due_at']);
        });

        Schema::create('craftsman_payouts', function (Blueprint $table): void {
            $table->id();
            $table->string('payout_number', 50)->unique();
            $table->foreignId('earning_id')->constrained('craftsman_earnings')->restrictOnDelete();
            $table->foreignId('craftsman_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_method_id')->constrained()->restrictOnDelete();
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->enum('status', ['PENDING', 'CONFIRMED', 'FAILED', 'CANCELLED'])->default('PENDING');
            $table->string('reference_number', 100)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('craftsman_remittances', function (Blueprint $table): void {
            $table->id();
            $table->string('remittance_number', 50)->unique();
            $table->foreignId('craftsman_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_method_id')->constrained()->restrictOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->timestamp('remitted_at');
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['PENDING_VERIFICATION', 'CONFIRMED', 'REJECTED', 'CANCELLED'])->default('PENDING_VERIFICATION');
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('craftsman_remittance_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('remittance_id')->constrained('craftsman_remittances')->cascadeOnDelete();
            $table->foreignId('settlement_id')->constrained('craftsman_settlements')->cascadeOnDelete();
            $table->decimal('allocated_amount', 14, 2);
            $table->timestamps();
            $table->unique(['remittance_id', 'settlement_id'], 'craft_remit_alloc_unique');
        });

        Schema::create('service_request_material_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_request_material_id');
            $table->foreign('service_request_material_id', 'material_usage_material_fk')->references('id')->on('service_request_materials')->cascadeOnDelete();
            $table->foreignId('purchase_invoice_item_id');
            $table->foreign('purchase_invoice_item_id', 'material_usage_invoice_item_fk')->references('id')->on('purchase_invoice_items')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->foreignId('currency_id')->constrained()->restrictOnDelete();
            $table->foreignId('used_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('quantity_used', 12, 3);
            $table->decimal('unit_cost', 14, 2);
            $table->decimal('total_cost', 14, 2);
            $table->timestamp('used_at');
            $table->enum('status', ['DRAFT', 'CONFIRMED', 'CANCELLED'])->default('DRAFT');
            $table->timestamp('confirmed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('service_request_quotes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('previous_quote_id')->nullable()->constrained('service_request_quotes')->nullOnDelete();
            $table->unsignedSmallInteger('version');
            $table->decimal('craftsman_proposed_amount', 12, 2);
            $table->decimal('offered_amount', 12, 2);
            $table->foreignId('currency_id')->constrained()->restrictOnDelete();
            $table->text('description')->nullable();
            $table->text('adjustment_reason')->nullable();
            $table->foreignId('adjusted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['PENDING_CUSTOMER', 'ACCEPTED', 'REJECTED', 'SUPERSEDED', 'CANCELLED', 'EXPIRED'])->default('PENDING_CUSTOMER');
            $table->timestamp('issued_at');
            $table->timestamp('expires_at');
            $table->timestamps();
            $table->unique(['service_request_id', 'version']);
        });

        Schema::create('quote_negotiations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quote_id')->constrained('service_request_quotes')->cascadeOnDelete();
            $table->decimal('customer_proposed_amount', 12, 2);
            $table->text('customer_message')->nullable();
            $table->enum('status', ['PENDING', 'ACCEPTED', 'COUNTER_OFFERED', 'REJECTED'])->default('PENDING');
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('company_response')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('advertisement_position_pivot', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('advertisement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained('advertisement_positions')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['advertisement_id', 'position_id'], 'ad_position_unique');
        });

        Schema::create('craftsman_service_areas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('craftsman_profile_id')->constrained('profiles_craftsman')->cascadeOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('neighborhood_id')->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['craftsman_profile_id', 'district_id', 'neighborhood_id'], 'craftsman_area_unique');
        });

        Schema::create('craftsman_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('craftsman_profile_id')->constrained('profiles_craftsman')->cascadeOnDelete();
            $table->enum('document_type', ['NATIONAL_ID_FRONT', 'NATIONAL_ID_BACK', 'PROFESSIONAL_CERTIFICATE', 'EXPERIENCE_CERTIFICATE', 'OTHER']);
            $table->string('title', 150)->nullable();
            $table->string('file_path');
            $table->enum('review_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('craftsman_portfolio_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('portfolio_id')->constrained('craftsman_portfolio')->cascadeOnDelete();
            $table->string('image_path');
            $table->string('alt_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['portfolio_id', 'image_path'], 'portfolio_image_unique');
        });

        Schema::create('craftsman_specialty_changes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('craftsman_id')->constrained('profiles_craftsman')->cascadeOnDelete();
            $table->foreignId('specialty_id')->constrained('craftsman_specialties')->cascadeOnDelete();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'CANCELLED'])->default('PENDING');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('supplier_payments', function (Blueprint $table): void {
            $table->id();
            $table->string('payment_number', 50)->unique();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('currency_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_method_id')->constrained()->restrictOnDelete();
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('payment_date');
            $table->enum('status', ['DRAFT', 'POSTED', 'CANCELLED'])->default('DRAFT');
            $table->string('reference_number', 100)->nullable();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['supplier_id', 'payment_date']);
            $table->index(['status', 'payment_date']);
        });

        Schema::create('supplier_payment_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_invoice_id')->constrained()->restrictOnDelete();
            $table->decimal('allocated_amount', 14, 2);
            $table->timestamps();
            $table->unique(['supplier_payment_id', 'purchase_invoice_id'], 'supplier_payment_invoice_unique');
        });

        Schema::create('supplier_returns', function (Blueprint $table): void {
            $table->id();
            $table->string('return_number', 50)->unique();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('purchase_invoice_id')->constrained()->restrictOnDelete();
            $table->foreignId('currency_id')->constrained()->restrictOnDelete();
            $table->date('return_date');
            $table->decimal('total_amount', 14, 2);
            $table->enum('status', ['DRAFT', 'POSTED', 'CANCELLED'])->default('DRAFT');
            $table->text('reason')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('posted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('supplier_return_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_invoice_item_id')->constrained()->restrictOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('line_total', 14, 2);
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        foreach ([
            'craftsman_collection_accounting_events' => ['craftsman_collection_id', 'craftsman_collections'],
            'craftsman_earning_accounting_events' => ['craftsman_earning_id', 'craftsman_earnings'],
            'craftsman_payout_accounting_events' => ['craftsman_payout_id', 'craftsman_payouts'],
            'craftsman_remittance_accounting_events' => ['craftsman_remittance_id', 'craftsman_remittances'],
            'purchase_invoice_accounting_events' => ['purchase_invoice_id', 'purchase_invoices'],
            'service_request_material_usage_accounting_events' => ['service_request_material_usage_id', 'service_request_material_usages'],
            'supplier_payment_accounting_events' => ['supplier_payment_id', 'supplier_payments'],
            'supplier_return_accounting_events' => ['supplier_return_id', 'supplier_returns'],
            'transaction_accounting_events' => ['transaction_id', 'transactions'],
        ] as $tableName => [$sourceColumn, $sourceTable]) {
            Schema::create($tableName, function (Blueprint $table) use ($tableName, $sourceColumn, $sourceTable): void {
                $table->id();
                $table->foreignId($sourceColumn);
                $table->foreign($sourceColumn, $tableName.'_source_fk')->references('id')->on($sourceTable)->cascadeOnDelete();
                $table->foreignId('accounting_event_id');
                $table->foreign('accounting_event_id', $tableName.'_event_fk')->references('id')->on('accounting_events')->cascadeOnDelete();
                $table->timestamps();
                $table->unique([$sourceColumn, 'accounting_event_id'], 'source_event_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_accounting_events');
        Schema::dropIfExists('supplier_return_accounting_events');
        Schema::dropIfExists('supplier_payment_accounting_events');
        Schema::dropIfExists('service_request_material_usage_accounting_events');
        Schema::dropIfExists('purchase_invoice_accounting_events');
        Schema::dropIfExists('craftsman_remittance_accounting_events');
        Schema::dropIfExists('craftsman_payout_accounting_events');
        Schema::dropIfExists('craftsman_earning_accounting_events');
        Schema::dropIfExists('craftsman_collection_accounting_events');
        Schema::dropIfExists('supplier_return_items');
        Schema::dropIfExists('supplier_returns');
        Schema::dropIfExists('supplier_payment_allocations');
        Schema::dropIfExists('supplier_payments');
        Schema::dropIfExists('craftsman_specialty_changes');
        Schema::dropIfExists('craftsman_portfolio_images');
        Schema::dropIfExists('craftsman_documents');
        Schema::dropIfExists('craftsman_service_areas');
        Schema::dropIfExists('advertisement_position_pivot');
        Schema::dropIfExists('quote_negotiations');
        Schema::dropIfExists('service_request_quotes');
        Schema::dropIfExists('service_request_material_usages');
        Schema::dropIfExists('craftsman_remittance_allocations');
        Schema::dropIfExists('craftsman_remittances');
        Schema::dropIfExists('craftsman_payouts');
        Schema::dropIfExists('craftsman_settlements');
        Schema::dropIfExists('craftsman_collections');
        Schema::dropIfExists('craftsman_earnings');
        Schema::dropIfExists('accounting_event_journal_entries');
        Schema::dropIfExists('accounting_events');
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('purchase_invoice_items');
        Schema::dropIfExists('purchase_invoices');
        Schema::dropIfExists('service_request_materials');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('employee_departments');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('employee_profiles');
        Schema::dropIfExists('accounting_periods');
        Schema::dropIfExists('company_working_hours');
        Schema::dropIfExists('devices_user');
        Schema::dropIfExists('addresses_user');
        Schema::dropIfExists('profiles_craftsman');
        Schema::dropIfExists('service_prices');
        Schema::dropIfExists('units');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('currencies');
    }
};
