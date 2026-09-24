<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('wallet_balance', 12, 2)->default(0.00)->after('status');
            $table->foreignId('branch_terminal_id')->nullable()->constrained('terminals')->nullOnDelete()->after('wallet_balance');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_terminal_id']);
            $table->dropColumn(['wallet_balance', 'branch_terminal_id']);
        });
    }
};
