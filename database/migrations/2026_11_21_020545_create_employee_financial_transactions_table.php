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
        Schema::create('employee_financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_profile_id')->constrained('employee_profiles')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('transaction_type', ['debit', 'credit']); // debit = مدين (عليه للمؤسسة مثل عهدة أو سلفة)، credit = دائن (له عند المؤسسة مثل تسوية مصاريف أو راتب)
            $table->enum('category', ['custody', 'advance', 'expense_settlement', 'salary', 'repayment', 'other'])->default('custody');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('YER');
            $table->string('reference_number', 50)->nullable();
            $table->date('transaction_date');
            $table->string('payment_method', 50)->default('cash');
            $table->string('receipt_attachment')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_financial_transactions');
    }
};
