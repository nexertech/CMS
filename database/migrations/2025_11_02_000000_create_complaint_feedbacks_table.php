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
        Schema::create('complaint_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('complaint_id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('entered_by')->nullable();
            $table->string('submitted_by')->nullable()->comment('Name of the person submitting feedback');

            // Overall rating
            $table->enum('overall_rating', ['excellent', 'good', 'average', 'poor'])->nullable();
            $table->integer('rating_score')->nullable()->comment('1-5 rating scale');

            // Specific criteria ratings
            $table->enum('service_quality', ['excellent', 'good', 'average', 'poor'])->nullable();
            $table->enum('response_time', ['excellent', 'good', 'average', 'poor'])->nullable();
            $table->enum('resolution_quality', ['excellent', 'good', 'average', 'poor'])->nullable();
            $table->enum('staff_behavior', ['excellent', 'good', 'average', 'poor'])->nullable();

            // Comments and notes
            $table->text('comments')->nullable()->comment('Client feedback comments');
            $table->text('remarks')->nullable()->comment('Staff internal notes');

            // Metadata
            $table->timestamp('feedback_date')->nullable()->comment('When client provided feedback');
            $table->timestamp('entered_at')->useCurrent()->comment('When staff entered in system');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_feedbacks');
    }
};

