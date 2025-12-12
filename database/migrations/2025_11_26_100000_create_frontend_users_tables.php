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
        // Create frontend_users table with JSON columns
        Schema::create('frontend_users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 100)->unique();
            $table->string('name', 100)->nullable();
            $table->string('password', 255);
            $table->rememberToken();
            $table->string('email', 150)->nullable()->unique();
            $table->string('phone', 20)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->json('cme_ids')->nullable();
            $table->json('group_ids')->nullable();
            $table->json('node_ids')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frontend_users');
    }
};
