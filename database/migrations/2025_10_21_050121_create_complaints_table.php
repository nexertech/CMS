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
            $table->string('title');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('sector_id')->nullable();
            $table->string('category', 100);
            $table->text('description')->nullable();
            $table->enum('status', ['new', 'assigned', 'in_progress', 'resolved', 'closed', 'work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa', 'product_na', 'un_authorized', 'pertains_to_ge_const_isld', 'barak_damages'])->default('new');
            $table->unsignedBigInteger('assigned_employee_id')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent', 'emergency'])->default('medium');
            $table->string('availability_time')->nullable();
            $table->timestamp('closed_at')->nullable();

            // Spare part columns
            $table->unsignedBigInteger('spare_id')->nullable();
            $table->integer('spare_quantity')->nullable();
            $table->unsignedBigInteger('spare_used_by')->nullable();
            $table->timestamp('spare_used_at')->nullable();

            $table->timestamps();
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
