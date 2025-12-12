<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('spare_approval_performa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('complaint_id');
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('performa_type', 50)->nullable();
            $table->string('authority_number')->nullable();
            $table->boolean('waiting_for_authority')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['status', 'created_at']);
            $table->index(['requested_by', 'status']);
            $table->index(['approved_by', 'status']);
        });

        // Update status to 'approved' for all existing approvals that have performa_type set
        // This ensures existing data is consistent with new logic (status = approved when performa_type is set)
        // Safe to run even if table was just created (no records will match)
        try {
            DB::table('spare_approval_performa')
                ->whereNotNull('performa_type')
                ->where('status', 'pending')
                ->update(['status' => 'approved']);
        } catch (\Exception $e) {
            // Ignore if table doesn't exist yet or no records match
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spare_approval_performa');
    }
};
