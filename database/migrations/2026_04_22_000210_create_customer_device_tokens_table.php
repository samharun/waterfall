<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_device_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('device_token')->unique();
            $table->string('platform')->nullable();
            $table->string('device_name')->nullable();
            $table->string('app_version')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['customer_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_device_tokens');
    }
};
