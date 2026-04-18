<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->unique();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('transaction_type', 20);   // stock_in, stock_out, adjustment, damaged, returned
            $table->string('adjustment_direction', 10)->nullable(); // increase, decrease
            $table->integer('quantity');
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->date('transaction_date');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('product_id');
            $table->index('transaction_type');
            $table->index('adjustment_direction');
            $table->index('transaction_date');
            $table->index('created_by');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
