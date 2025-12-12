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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 150)->nullable()->unique();
            $table->string('designation', 100)->nullable();
            $table->string('phone', 20)->nullable();
            // emp_id removed
            $table->date('date_of_hire')->nullable();
            $table->integer('leave_quota')->default(30);
            $table->text('address')->nullable();
            // Add city_id and sector_id columns (nullable for existing installations)
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('sector_id')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
