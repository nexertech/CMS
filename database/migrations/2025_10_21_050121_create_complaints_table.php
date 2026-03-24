<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('complaint_title_id')->nullable();
            $table->unsignedBigInteger('house_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('sector_id')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 50)->default('new');
            $table->unsignedBigInteger('assigned_employee_id')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->string('availability_time')->nullable();
            $table->timestamp('closed_at')->nullable();

            // Spare part columns
            $table->unsignedBigInteger('spare_id')->nullable();
            $table->integer('spare_quantity')->nullable();
            $table->unsignedBigInteger('spare_used_by')->nullable();
            $table->timestamp('spare_used_at')->nullable();

            $table->timestamps();

            // Performance indexes
            $table->index('city_id');
            $table->index('sector_id');
            $table->index('status');
            $table->index('category_id');
            $table->index('complaint_title_id');
            $table->index('created_at');
            $table->index('assigned_employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_spares');
        Schema::dropIfExists('complaints');
    }
};
