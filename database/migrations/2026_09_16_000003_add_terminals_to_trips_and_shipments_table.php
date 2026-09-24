<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->foreignId('departure_terminal_id')->nullable()->constrained('terminals')->nullOnDelete()->after('departure_station');
            $table->foreignId('arrival_terminal_id')->nullable()->constrained('terminals')->nullOnDelete()->after('arrival_station');
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->foreignId('origin_terminal_id')->nullable()->constrained('terminals')->nullOnDelete()->after('destination_station');
            $table->foreignId('destination_terminal_id')->nullable()->constrained('terminals')->nullOnDelete()->after('origin_terminal_id');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign(['departure_terminal_id']);
            $table->dropForeign(['arrival_terminal_id']);
            $table->dropColumn(['departure_terminal_id', 'arrival_terminal_id']);
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['origin_terminal_id']);
            $table->dropForeign(['destination_terminal_id']);
            $table->dropColumn(['origin_terminal_id', 'destination_terminal_id']);
        });
    }
};
