<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jar_deposits', function (Blueprint $table) {
            $table->id();
            $table->string('deposit_no')->unique();
            $table->string('party_type', 20)->default('customer'); // customer, dealer
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('dealer_id')->nullable()->constrained('dealers')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('transaction_type', 20); // deposit_received, jar_issued, jar_returned, adjustment
            $table->string('adjustment_direction', 10)->nullable(); // increase, decrease
            $table->integer('quantity');
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->date('transaction_date');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('party_type');
            $table->index('customer_id');
            $table->index('dealer_id');
            $table->index('product_id');
            $table->index('transaction_type');
            $table->index('transaction_date');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jar_deposits');
    }
};
