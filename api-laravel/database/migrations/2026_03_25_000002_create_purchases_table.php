<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('image_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('buyer_email');
            $table->unsignedInteger('amount_cents');
            $table->string('currency', 3)->default('usd');
            $table->string('gateway')->default('stripe');
            $table->string('gateway_session_id')->unique();
            $table->string('gateway_payment_id')->nullable();
            $table->string('status')->default('pending');
            $table->string('download_token', 64)->unique();
            $table->timestamp('download_expires_at')->nullable();
            $table->timestamps();

            $table->index(['download_token', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
