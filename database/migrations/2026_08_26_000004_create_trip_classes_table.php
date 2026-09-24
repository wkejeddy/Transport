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
        Schema::create('trip_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');
            $table->string('class_code'); // '1st_class', '2nd_class', 'couchette', 'vip', 'classic'
            $table->string('class_name'); // e.g. "1ère Classe Prestige", "Couchette Wagon-Lit (Berth)"
            $table->unsignedInteger('seat_count');
            $table->unsignedInteger('seats_available');
            $table->decimal('price', 10, 2);
            $table->json('amenities')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_classes');
    }
};
