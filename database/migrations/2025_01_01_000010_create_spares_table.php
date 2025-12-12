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
        Schema::create('spares', function (Blueprint $table) {
            $table->id();
            // Product metadata
            $table->string('product_code', 50)->nullable();
            $table->string('brand_name', 100)->nullable();
            $table->string('item_name', 150);
            // Category from complaint_categories table
            $table->string('category', 100);
            // Location columns for city/sector-based filtering
            $table->unsignedBigInteger('city_id')->nullable();
            $table->unsignedBigInteger('sector_id')->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            // Stock metrics
            $table->integer('total_received_quantity')->default(0);
            $table->integer('issued_quantity')->default(0);
            $table->integer('stock_quantity')->default(0); // balance quantity for quick reads
            $table->integer('threshold_level')->default(10);
            $table->string('supplier', 255)->nullable();
            $table->text('description')->nullable();
            $table->timestamp('last_stock_in_at')->nullable();
            $table->timestamp('last_updated')->useCurrent();
            $table->timestamps();
        });

        Schema::create('spare_stock_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('spare_id');
            $table->string('brand_name', 100)->nullable();
            $table->enum('change_type', ['in', 'out']);
            $table->integer('quantity');
            $table->integer('reference_id')->nullable(); // complaint_id or purchase_id
            $table->text('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spare_stock_logs');
        Schema::dropIfExists('spares');
    }
};
