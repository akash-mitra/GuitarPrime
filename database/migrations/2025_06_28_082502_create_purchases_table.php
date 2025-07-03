<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('purchasable_type');
            $table->ulid('purchasable_id');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('payment_provider', ['stripe', 'razorpay']);
            $table->string('payment_id')->nullable();
            $table->string('checkout_session_id')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['purchasable_type', 'purchasable_id']);
            $table->index(['user_id', 'status']);
            $table->unique(['payment_id', 'payment_provider'], 'unique_payment_per_provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
