<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('collected_from_staff_id')
                ->nullable()
                ->after('collected_at')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Delivery staff who handed the cash to the manager');

            $table->index('collected_from_staff_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['collected_from_staff_id']);
            $table->dropIndex(['collected_from_staff_id']);
            $table->dropColumn('collected_from_staff_id');
        });
    }
};
