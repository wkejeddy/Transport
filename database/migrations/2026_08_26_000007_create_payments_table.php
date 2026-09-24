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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_reference')->unique(); // "PAY-2026-K9D8"
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->morphs('payable'); // payable_type (Booking/Shipment), payable_id
            
            $table->decimal('amount', 10, 2);
            $table->string('currency', 5)->default('XAF');
            $table->enum('method', ['orange_money', 'mtn_momo', 'wallet']);
            $table->string('payer_phone');
            $table->string('transaction_ref')->nullable(); // Telco transaction ID
            
            $table->enum('status', ['pending', 'successful', 'failed', 'refunded'])->default('pending');
            $table->json('gateway_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
