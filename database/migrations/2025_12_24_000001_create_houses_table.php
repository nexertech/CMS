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
        Schema::create('houses', function (Blueprint $table) {
            $table->id();
            $table->string('username', 150)->unique()->nullable();
            $table->string('house_no', 150)->nullable();
            $table->string('password')->nullable();
            $table->timestamp('password_updated_at')->nullable();
            $table->string('fcm_token', 255)->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedBigInteger('city_id');
            $table->unsignedBigInteger('sector_id');
            $table->text('address')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('type', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // High-performance B-Tree indexes for 100k+ house queries
            $table->index('city_id');
            $table->index('sector_id');
            $table->index('status');
            $table->index('house_no');
            $table->index('phone');
            $table->index(['city_id', 'sector_id', 'status']);
            $table->index(['sector_id', 'status']);
            $table->index(['city_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('houses');
    }
};
