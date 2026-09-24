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
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->string('dispute_code')->unique(); // e.g. "DSP-2026-092"
            $table->foreignId('raised_by_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('agency_id')->nullable()->constrained('transport_agencies')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('shipment_id')->nullable()->constrained('shipments')->nullOnDelete();
            
            $table->string('title');
            $table->string('category')->default('other'); // delay, lost_package, damaged_item, denied_boarding, refund_request, other
            $table->text('description');
            
            $table->enum('status', ['open', 'in_review', 'resolved', 'rejected'])->default('open');
            $table->boolean('escalated')->default(false);
            $table->dateTime('escalated_at')->nullable();
            
            $table->text('manager_response')->nullable();
            $table->dateTime('manager_responded_at')->nullable();
            
            $table->text('admin_notes')->nullable();
            $table->foreignId('admin_resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('admin_resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
