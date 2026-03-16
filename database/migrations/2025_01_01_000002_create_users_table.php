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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100)->unique();
            $table->string('name', 100)->nullable();
            $table->string('password', 255);
            $table->rememberToken();
            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->json('city_ids')->nullable();
            $table->json('sector_ids')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('theme', 10)->default('auto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
