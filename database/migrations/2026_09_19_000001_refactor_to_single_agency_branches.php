<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Create Branches table for regional management
        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique(); // DLA, YDE, WST
                $table->string('region'); // Douala, Yaounde, Ouest
                $table->string('city');
                $table->string('address')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('manager_name')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed the 3 primary regional branches of Real Voyage
            DB::table('branches')->insert([
                [
                    'id' => 1,
                    'name' => 'Direction Régionale Littoral - Douala',
                    'code' => 'DLA',
                    'region' => 'Douala',
                    'city' => 'Douala',
                    'address' => 'Gare Routière Principale Bessengue, Douala',
                    'phone' => '+237 233 42 77 10',
                    'email' => 'douala@realvoyage.cm',
                    'manager_name' => 'Chef de Gare Douala',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 2,
                    'name' => 'Direction Régionale Centre - Yaoundé',
                    'code' => 'YDE',
                    'region' => 'Yaounde',
                    'city' => 'Yaoundé',
                    'address' => 'Terminal Interurbain de Mvan, Yaoundé',
                    'phone' => '+237 222 20 55 40',
                    'email' => 'yaounde@realvoyage.cm',
                    'manager_name' => 'Chef de Gare Yaoundé',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 3,
                    'name' => 'Direction Régionale Ouest - Bafoussam',
                    'code' => 'WST',
                    'region' => 'Ouest',
                    'city' => 'Bafoussam',
                    'address' => 'Gare Centrale de Bafoussam',
                    'phone' => '+237 233 44 88 25',
                    'email' => 'ouest@realvoyage.cm',
                    'manager_name' => 'Chef de Gare Bafoussam',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // 2. Drop train-related tables if they exist
        Schema::dropIfExists('train_schedules');
        Schema::dropIfExists('train_routes');
        Schema::dropIfExists('trains');

        // 3. Update users: add branch_id
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->after('role')->constrained('branches')->nullOnDelete();
                }
            });
        }

        $dropAgencyId = function (string $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'agency_id')) {
                return;
            }

            try {
                if (DB::getDriverName() !== 'sqlite') {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->dropForeign(['agency_id']);
                    });
                }
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('agency_id');
                });
            } catch (\Throwable $e) {
                try {
                    Schema::table($tableName, function (Blueprint $table) {
                        $table->unsignedBigInteger('agency_id')->nullable()->change();
                    });
                } catch (\Throwable $e2) {
                    // Ignore
                }
            }
        };

        // 4. Update vehicles (buses): add branch_id, drop agency_id
        if (Schema::hasTable('vehicles')) {
            Schema::table('vehicles', function (Blueprint $table) {
                if (!Schema::hasColumn('vehicles', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
                }
            });

            // Set default branch for existing vehicles
            DB::table('vehicles')->whereNull('branch_id')->update(['branch_id' => 1]);

            $dropAgencyId('vehicles');
        }

        // 5. Update trips (routes): add branch_id, drop agency_id
        if (Schema::hasTable('trips')) {
            Schema::table('trips', function (Blueprint $table) {
                if (!Schema::hasColumn('trips', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
                }
            });

            DB::table('trips')->whereNull('branch_id')->update(['branch_id' => 1]);

            $dropAgencyId('trips');
        }

        // 6. Update shipments: add branch_id, drop agency_id
        if (Schema::hasTable('shipments')) {
            Schema::table('shipments', function (Blueprint $table) {
                if (!Schema::hasColumn('shipments', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->after('sender_id')->constrained('branches')->nullOnDelete();
                }
                if (!Schema::hasColumn('shipments', 'origin_branch_id')) {
                    $table->foreignId('origin_branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
                }
                if (!Schema::hasColumn('shipments', 'destination_branch_id')) {
                    $table->foreignId('destination_branch_id')->nullable()->after('origin_branch_id')->constrained('branches')->nullOnDelete();
                }
            });

            DB::table('shipments')->whereNull('branch_id')->update(['branch_id' => 1]);

            $dropAgencyId('shipments');
        }

        // 7. Update ratings: add branch_id, drop agency_id
        if (Schema::hasTable('ratings')) {
            Schema::table('ratings', function (Blueprint $table) {
                if (!Schema::hasColumn('ratings', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
                }
            });

            $dropAgencyId('ratings');
        }

        // 8. Update disputes: add branch_id, drop agency_id
        if (Schema::hasTable('disputes')) {
            Schema::table('disputes', function (Blueprint $table) {
                if (!Schema::hasColumn('disputes', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
                }
            });

            $dropAgencyId('disputes');
        }

        // 9. Update terminals: add branch_id, drop agency_id
        if (Schema::hasTable('terminals')) {
            Schema::table('terminals', function (Blueprint $table) {
                if (!Schema::hasColumn('terminals', 'branch_id')) {
                    $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
                }
            });

            $dropAgencyId('terminals');
        }

        // 10. Drop transport_agencies table
        if (DB::getDriverName() !== 'sqlite') {
            Schema::dropIfExists('transport_agencies');
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('branches');
        Schema::enableForeignKeyConstraints();
    }
};
