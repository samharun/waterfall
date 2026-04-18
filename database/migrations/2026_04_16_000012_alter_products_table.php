<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop old indexes and columns
            $table->dropIndex(['type']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['type', 'description', 'price', 'deposit', 'is_active']);

            // Add new columns
            $table->string('product_type', 20)->default('jar')->after('sku');
            $table->decimal('default_price', 12, 2)->default(0)->after('product_type');
            $table->decimal('deposit_amount', 12, 2)->default(0)->after('default_price');
            $table->integer('stock_alert_qty')->default(0)->after('deposit_amount');
            $table->string('status', 20)->default('active')->after('stock_alert_qty');

            $table->index('product_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['product_type']);
            $table->dropIndex(['status']);
            $table->dropColumn(['product_type', 'default_price', 'deposit_amount', 'stock_alert_qty', 'status']);

            $table->enum('type', ['jar', 'bottle', 'accessory']);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('deposit', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->index('type');
            $table->index('is_active');
        });
    }
};
