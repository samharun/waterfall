<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['name', 'type']);
            $table->index(['type', 'status']);
        });

        Schema::create('payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('account_no')->nullable();
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->boolean('status')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('name');
            $table->index(['type', 'status']);
        });

        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->unique();
            $table->date('transaction_date');
            $table->string('transaction_type');
            $table->foreignId('account_category_id')->nullable()->constrained('account_categories')->nullOnDelete();
            $table->foreignId('payment_account_id')->constrained('payment_accounts')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('paid_to')->nullable();
            $table->string('received_from')->nullable();
            $table->string('reference_no')->nullable();
            $table->text('description')->nullable();
            $table->string('attachment')->nullable();
            $table->string('status')->default('approved');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['transaction_date', 'transaction_type', 'status'], 'acct_txn_date_type_status_idx');
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile')->nullable();
            $table->text('address')->nullable();
            $table->decimal('opening_due', 12, 2)->default(0);
            $table->decimal('total_purchase', 12, 2)->default(0);
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->decimal('current_due', 12, 2)->default(0);
            $table->text('note')->nullable();
            $table->boolean('status')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('staff_salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('staff_name')->nullable();
            $table->string('salary_month');
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('advance_deduction', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('deduction', 12, 2)->default(0);
            $table->decimal('net_payable', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
            $table->date('payment_date');
            $table->foreignId('payment_account_id')->constrained('payment_accounts')->restrictOnDelete();
            $table->foreignId('account_transaction_id')->nullable()->constrained('account_transactions')->nullOnDelete();
            $table->string('status')->default('unpaid');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('jar_purchases', function (Blueprint $table) {
            $table->id();
            $table->date('purchase_date');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('supplier_name')->nullable();
            $table->string('jar_type')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('due_amount', 12, 2)->default(0);
            $table->foreignId('payment_account_id')->nullable()->constrained('payment_accounts')->restrictOnDelete();
            $table->foreignId('account_transaction_id')->nullable()->constrained('account_transactions')->nullOnDelete();
            $table->string('payment_status')->default('unpaid');
            $table->text('note')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('business_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_name');
            $table->string('category')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 12, 2)->default(0);
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('supplier_name')->nullable();
            $table->date('warranty_date')->nullable();
            $table->string('current_status')->default('active');
            $table->string('location')->nullable();
            $table->text('note')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('maintenance_costs', function (Blueprint $table) {
            $table->id();
            $table->date('maintenance_date');
            $table->foreignId('business_asset_id')->nullable()->constrained('business_assets')->nullOnDelete();
            $table->string('maintenance_type')->nullable();
            $table->text('description')->nullable();
            $table->decimal('cost', 12, 2)->default(0);
            $table->string('paid_to')->nullable();
            $table->foreignId('payment_account_id')->constrained('payment_accounts')->restrictOnDelete();
            $table->foreignId('account_transaction_id')->nullable()->constrained('account_transactions')->nullOnDelete();
            $table->date('next_service_date')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('investors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile')->nullable();
            $table->text('address')->nullable();
            $table->string('investment_type')->default('capital');
            $table->decimal('total_invested', 12, 2)->default(0);
            $table->decimal('total_returned', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->text('note')->nullable();
            $table->boolean('status')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('investor_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_id')->constrained('investors')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->string('transaction_type');
            $table->decimal('amount', 12, 2);
            $table->foreignId('payment_account_id')->constrained('payment_accounts')->restrictOnDelete();
            $table->foreignId('account_transaction_id')->nullable()->constrained('account_transactions')->nullOnDelete();
            $table->string('reference_no')->nullable();
            $table->text('note')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_transactions');
        Schema::dropIfExists('investors');
        Schema::dropIfExists('maintenance_costs');
        Schema::dropIfExists('business_assets');
        Schema::dropIfExists('jar_purchases');
        Schema::dropIfExists('staff_salary_payments');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('account_transactions');
        Schema::dropIfExists('payment_accounts');
        Schema::dropIfExists('account_categories');
    }
};
