<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dealers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('dealer_code')->unique();
            $table->string('name');
            $table->string('mobile', 11)->unique();
            $table->string('email')->nullable();
            $table->text('address');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->string('approval_status', 20)->default('pending');
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('current_due', 12, 2)->default(0);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('approval_status');
            $table->index('zone_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dealers');
    }
};
