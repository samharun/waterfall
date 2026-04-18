<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id')->unique(); // WF-CUS-000001
            $table->foreignId('zone_id')->constrained('zones')->restrictOnDelete();
            $table->string('name');
            $table->string('mobile', 11)->unique();
            $table->string('email')->nullable()->unique();
            $table->text('address')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'inactive'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('zone_id');
            $table->index('mobile');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
