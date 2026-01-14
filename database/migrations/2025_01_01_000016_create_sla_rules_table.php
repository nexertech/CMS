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
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('complaint_categories')->onDelete('cascade');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent', 'emergency'])->default('medium');
            $table->integer('max_response_time')->comment('In hours');
            $table->integer('max_resolution_time')->comment('In hours');
            $table->unsignedBigInteger('notify_to')->nullable(); // Made nullable based on recent change
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
