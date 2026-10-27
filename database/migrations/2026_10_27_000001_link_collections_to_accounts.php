<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Existing rows stay unposted until reviewed for manual duplicates.
            $table->boolean('accounting_enabled')->default(false);
            $table->foreignId('payment_account_id')->nullable()->constrained('payment_accounts')->restrictOnDelete();
        });
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->foreignId('payment_id')->nullable()->unique()->constrained('payments')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_id');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_account_id');
            $table->dropColumn('accounting_enabled');
        });
    }
};
