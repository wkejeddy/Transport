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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference')->unique(); // e.g. "BK-2026-X8F9"
            $table->foreignId('passenger_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');
            $table->foreignId('trip_class_id')->nullable()->constrained('trip_classes')->nullOnDelete();
            
            $table->string('transport_class')->default('classic'); // 'vip', 'classic', '1st_class', '2nd_class', 'couchette'
            $table->unsignedInteger('seats_count')->default(1);
            $table->json('seat_numbers')->nullable(); // ["S-12", "S-13"] or ["B-04A"]
            $table->json('passengers_data')->nullable(); // List of passenger names/IDs for group bookings
            
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'reserved', 'confirmed', 'checked_in', 'cancelled', 'payment_failed'])->default('pending');
            $table->string('qr_code_token')->unique();
            $table->dateTime('expires_at'); // 15-min seat lock
            
            $table->dateTime('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
