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
        if (Schema::hasTable('sectors')) {
             if (!Schema::hasColumn('sectors', 'cme_id')) {
                Schema::table('sectors', function (Blueprint $table) {
                    $table->unsignedBigInteger('cme_id')->nullable()->after('id');
                });
            }
            if (!Schema::hasColumn('sectors', 'city_id')) {
                 Schema::table('sectors', function (Blueprint $table) {
                    $table->unsignedBigInteger('city_id')->nullable()->after('cme_id');
                });
            }
             // If table exists, we assume other columns might exist or we just want to patch it.
             // But the original code was creating the table.
             // To be safe and consistent with the "cities" approach which seemed to return if table exists:
             // We should probably just return here if we are only patching.
             // However, the user's error suggests the table exists but column is missing.
             // Let's wrap the create statement in a check.
             return;
        }

        Schema::create('sectors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cme_id')->nullable();
            // Add city_id column (nullable for existing installations)
            $table->unsignedBigInteger('city_id')->nullable();
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
        Schema::dropIfExists('sectors');
    }
};
