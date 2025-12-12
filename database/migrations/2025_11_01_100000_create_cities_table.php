<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if cities table already exists and is accessible
        try {
            $result = DB::select("SHOW TABLES LIKE 'cities'");
            if (count($result) > 0) {
                // Try to query it to verify it works
                try {
                    DB::select('SELECT 1 FROM cities LIMIT 1');

                    // Table exists, check if cme_id column exists
                    if (!Schema::hasColumn('cities', 'cme_id')) {
                        Schema::table('cities', function (Blueprint $table) {
                            $table->unsignedBigInteger('cme_id')->nullable()->after('id');
                        });
                    }

                    // Table exists and is accessible, skip creation
                    return;
                } catch (\Exception $e) {
                    // Table exists but might be corrupted, try to drop
                }
            }
        } catch (\Exception $e) {
            // Table doesn't exist, continue with creation
        }

        // Force drop table and flush cache
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Drop cities table if exists
        try {
            DB::statement('DROP TABLE IF EXISTS cities');
        } catch (\Exception $e) {
            // Ignore
        }

        // Drop cities_new and cities_temp if exist
        try {
            DB::statement('DROP TABLE IF EXISTS cities_new');
            DB::statement('DROP TABLE IF EXISTS cities_temp');
        } catch (\Exception $e) {
            // Ignore
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Flush tables to clear MySQL cache
        try {
            DB::statement('FLUSH TABLES');
        } catch (\Exception $e) {
            // Ignore
        }

        // Create table
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cme_id')->nullable();
            $table->string('name', 100)->unique();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
