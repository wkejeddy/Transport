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
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('return_trip_id')->nullable()->after('trip_id')->constrained('trips')->nullOnDelete();
            $table->boolean('is_round_trip')->default(false)->after('booking_type');
            $table->decimal('round_trip_discount', 10, 2)->default(0.00)->after('total_amount');
            
            // Performance composite indexes
            $table->index(['trip_id', 'status'], 'idx_bookings_trip_status');
            $table->index(['trip_id', 'status', 'expires_at'], 'idx_bookings_trip_status_expires');
            $table->index(['passenger_id', 'status'], 'idx_bookings_passenger_status');
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->index(['departure_city', 'arrival_city', 'departure_time', 'status'], 'idx_trips_search_composite');
            $table->index(['branch_id', 'status', 'departure_time'], 'idx_trips_branch_status_departure');
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->index(['sender_id', 'status'], 'idx_shipments_sender_status');
            $table->index(['branch_id', 'status'], 'idx_shipments_branch_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropIndex('idx_shipments_sender_status');
            $table->dropIndex('idx_shipments_branch_status');
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->dropIndex('idx_trips_search_composite');
            $table->dropIndex('idx_trips_branch_status_departure');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_trip_status');
            $table->dropIndex('idx_bookings_trip_status_expires');
            $table->dropIndex('idx_bookings_passenger_status');
            $table->dropForeign(['return_trip_id']);
            $table->dropColumn(['return_trip_id', 'is_round_trip', 'round_trip_discount']);
        });
    }
};