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
        Schema::create('registered_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique();
            $table->string('device_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('assigned_to_house_no')->nullable(); // Simple string as requested, no FK
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->foreignId('sector_id')->nullable()->constrained('sectors')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registered_devices');
    }
};
