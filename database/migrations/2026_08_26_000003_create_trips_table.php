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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->constrained('transport_agencies')->nullOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->string('trip_number')->unique();
            $table->enum('transport_mode', ['road', 'rail'])->default('road');
            
            $table->string('departure_city');
            $table->string('departure_station');
            $table->string('arrival_city');
            $table->string('arrival_station');
            
            $table->dateTime('departure_time');
            $table->dateTime('arrival_time_estimated');
            $table->dateTime('delayed_departure_time')->nullable();
            
            $table->decimal('base_price', 10, 2); // In XAF / FCFA
            $table->unsignedInteger('seats_available');
            $table->unsignedInteger('cargo_available_kg');
            $table->decimal('cargo_price_per_kg', 8, 2)->default(250.00);
            
            $table->enum('status', ['scheduled', 'boarding', 'in_transit', 'completed', 'delayed', 'cancelled'])->default('scheduled');
            $table->text('delay_reason')->nullable();
            $table->json('pricing_rules')->nullable(); // Dynamic surge, group discount, luggage allowance
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
