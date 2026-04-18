<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_otps', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 11);
            $table->string('otp_hash');
            $table->string('purpose', 30)->default('registration');
            $table->json('payload')->nullable();
            $table->dateTime('expires_at');
            $table->dateTime('verified_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedTinyInteger('resend_count')->default(0);
            $table->dateTime('last_sent_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index('mobile');
            $table->index('purpose');
            $table->index('expires_at');
            $table->index('verified_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_otps');
    }
};
