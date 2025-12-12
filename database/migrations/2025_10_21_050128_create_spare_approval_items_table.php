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
        Schema::create('spare_approval_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('performa_id');
            $table->unsignedBigInteger('spare_id');
            $table->integer('quantity_requested');
            $table->integer('quantity_approved')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['performa_id', 'spare_id']);
            $table->index('spare_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spare_approval_items');
    }
};
