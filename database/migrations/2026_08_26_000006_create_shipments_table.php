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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code')->unique(); // e.g. "SH-CM-928341"
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('agency_id')->nullable()->constrained('transport_agencies')->nullOnDelete();
            $table->foreignId('trip_id')->nullable()->constrained('trips')->nullOnDelete();
            
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->string('recipient_city');
            $table->string('destination_station');
            
            $table->string('item_category')->default('general'); // 'documents', 'electronics', 'clothing', 'perishables', 'general'
            $table->text('item_description')->nullable();
            $table->decimal('weight_kg', 8, 2);
            $table->decimal('declared_value', 10, 2)->default(0.00);
            $table->boolean('insured')->default(false);
            $table->decimal('insurance_fee', 10, 2)->default(0.00);
            $table->decimal('cargo_fee', 10, 2);
            $table->decimal('total_amount', 10, 2);
            
            $table->enum('status', ['registered', 'in_transit', 'arrived', 'collected', 'cancelled'])->default('registered');
            $table->string('proof_of_delivery_code', 10); // 6-digit OTP code
            $table->text('proof_of_delivery_notes')->nullable();
            $table->dateTime('collected_at')->nullable();
            $table->string('collected_by_name')->nullable();
            $table->string('collected_by_cni')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
