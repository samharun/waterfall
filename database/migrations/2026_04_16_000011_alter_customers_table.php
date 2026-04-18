<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Drop old columns
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'notes']);

            // Rename address to allow NOT NULL later (currently nullable)
            // We'll just modify it
            $table->text('address')->nullable(false)->change();

            // Add new columns after existing ones
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('customer_type', 20)->default('residential')->after('email');
            $table->string('approval_status', 20)->default('pending')->after('customer_type');
            $table->string('default_delivery_slot', 20)->nullable()->after('approval_status');
            $table->decimal('opening_balance', 12, 2)->default(0)->after('default_delivery_slot');
            $table->decimal('current_due', 12, 2)->default(0)->after('opening_balance');
            $table->integer('jar_deposit_qty')->default(0)->after('current_due');
            $table->string('qr_code')->nullable()->after('jar_deposit_qty');
            $table->foreignId('approved_by')
                ->nullable()
                ->after('qr_code')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');

            $table->index('approval_status');
            $table->index('customer_type');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['approval_status']);
            $table->dropIndex(['customer_type']);
            $table->dropColumn([
                'user_id', 'customer_type', 'approval_status', 'default_delivery_slot',
                'opening_balance', 'current_due', 'jar_deposit_qty', 'qr_code',
                'approved_by', 'approved_at',
            ]);
            $table->text('address')->nullable()->change();
            $table->enum('status', ['pending', 'approved', 'rejected', 'inactive'])->default('pending');
            $table->text('notes')->nullable();
            $table->index('status');
        });
    }
};
