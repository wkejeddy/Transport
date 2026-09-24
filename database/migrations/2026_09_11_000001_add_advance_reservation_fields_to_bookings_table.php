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
            $table->string('booking_type')->default('immediate')->after('transport_class'); // 'immediate', 'advance_reservation'
            $table->decimal('reservation_fee', 10, 2)->default(0.00)->after('total_amount');
            $table->boolean('reservation_fee_paid')->default(false)->after('reservation_fee');
            $table->timestamp('reminder_sent_at')->nullable()->after('expires_at');
        });

        // Add payment_type to payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_type')->default('ticket')->after('payable_id'); // 'ticket', 'reservation_fee', 'shipment'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['booking_type', 'reservation_fee', 'reservation_fee_paid', 'reminder_sent_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_type']);
        });
    }
};
