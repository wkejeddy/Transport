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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->nullable()->constrained('transport_agencies')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('trip_id')->nullable()->constrained('trips')->nullOnDelete();
            
            $table->unsignedTinyInteger('score'); // 1-5
            $table->unsignedTinyInteger('punctuality_score')->nullable(); // 1-5
            $table->unsignedTinyInteger('comfort_score')->nullable(); // 1-5
            $table->unsignedTinyInteger('customer_service_score')->nullable(); // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
