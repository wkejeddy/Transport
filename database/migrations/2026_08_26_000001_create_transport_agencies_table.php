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
        Schema::create('transport_agencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->string('company_name');
            $table->enum('transport_mode', ['road', 'rail'])->default('road');
            $table->string('registration_number')->unique();
            $table->string('tax_number')->nullable();
            $table->string('logo')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone');
            $table->string('headquarters_city')->default('Douala');
            $table->text('description')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('verification_notes')->nullable();
            
            // Scorecard metrics
            $table->decimal('rating_avg', 3, 2)->default(5.00);
            $table->unsignedInteger('total_reviews')->default(0);
            $table->decimal('on_time_rate', 5, 2)->default(95.00); // percentage 0-100
            $table->unsignedInteger('dispute_count')->default(0);
            $table->decimal('scorecard_score', 5, 2)->default(90.00); // 0-100
            $table->string('scorecard_grade', 5)->default('A'); // A, B, C, D
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_agencies');
    }
};
