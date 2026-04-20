<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            // Drop old columns
            $table->dropUnique(['slug']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['city']);
            $table->dropColumn(['slug', 'city', 'district', 'is_active']);

            // Add new columns
            $table->string('code', 50)->unique()->after('name');
            $table->text('description')->nullable()->after('code');
            $table->foreignId('delivery_manager_id')
                ->nullable()
                ->after('description')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('status', 20)->default('active')->after('delivery_manager_id');

            $table->index('status');
            $table->index('delivery_manager_id');
        });
    }

    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->dropForeign(['delivery_manager_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['delivery_manager_id']);
            $table->dropColumn(['code', 'description', 'delivery_manager_id', 'status']);

            $table->string('slug')->unique()->after('name');
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->boolean('is_active')->default(true);
            $table->index('is_active');
            $table->index('city');
        });
    }
};
