<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('subscription_no')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->integer('quantity')->default(1);
            $table->string('frequency', 20)->default('daily');
            $table->json('delivery_days')->nullable();
            $table->string('preferred_delivery_slot', 20)->default('morning');
            $table->time('preferred_delivery_time')->nullable();
            $table->date('start_date');
            $table->date('next_delivery_date')->nullable();
            $table->date('paused_from')->nullable();
            $table->date('paused_to')->nullable();
            $table->text('pause_reason')->nullable();
            $table->string('status', 20)->default('active');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('product_id');
            $table->index('frequency');
            $table->index('status');
            $table->index('start_date');
            $table->index('next_delivery_date');
            $table->index('created_by');
            $table->index('updated_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_subscriptions');
    }
};
