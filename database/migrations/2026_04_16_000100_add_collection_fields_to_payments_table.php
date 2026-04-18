<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('invoice_id')->constrained('orders')->nullOnDelete();
            $table->foreignId('delivery_id')->nullable()->after('order_id')->constrained('deliveries')->nullOnDelete();
            $table->string('collection_source', 30)->default('admin')->after('received_by');
            $table->string('collection_status', 20)->default('accepted')->after('collection_source');
            $table->dateTime('collected_at')->nullable()->after('collection_status');

            $table->index('order_id');
            $table->index('delivery_id');
            $table->index('collection_source');
            $table->index('collection_status');
            $table->index('collected_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['delivery_id']);
            $table->dropIndex(['order_id']);
            $table->dropIndex(['delivery_id']);
            $table->dropIndex(['collection_source']);
            $table->dropIndex(['collection_status']);
            $table->dropIndex(['collected_at']);
            $table->dropColumn(['order_id', 'delivery_id', 'collection_source', 'collection_status', 'collected_at']);
        });
    }
};
