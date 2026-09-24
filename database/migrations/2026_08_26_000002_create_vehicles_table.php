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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->constrained('transport_agencies')->nullOnDelete();
            $table->string('code')->unique(); // e.g. "BUS-CE-01", "TRAIN-CR-10"
            $table->string('name')->nullable(); // e.g. "Le Mbam Express", "Alize VIP"
            $table->enum('transport_mode', ['road', 'rail'])->default('road');
            $table->string('type')->default('classic_bus'); // vip_bus, classic_bus, coaster, express_train, omnibus_train, sleeper_train
            $table->unsignedInteger('capacity_seats')->default(70);
            $table->unsignedInteger('capacity_cargo')->default(2000); // in kg
            $table->json('seat_layout')->nullable(); // JSON configuration of rows, columns, classes
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
