<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Unicode-safe Bangla name and address (stored as utf8mb4)
            $table->string('name_bn')->nullable()->after('name');
            $table->text('address_bn')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['name_bn', 'address_bn']);
        });
    }
};
