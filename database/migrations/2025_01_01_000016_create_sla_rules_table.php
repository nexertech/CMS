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
        Schema::create('sla_rules', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_type', 100);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent', 'emergency'])->default('medium');
            $table->integer('max_response_time'); // in hours
            $table->integer('max_resolution_time')->nullable(); // in hours
            $table->unsignedBigInteger('notify_to');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sla_rules');
    }
};
