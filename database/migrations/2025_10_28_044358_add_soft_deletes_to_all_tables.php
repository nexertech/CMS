<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add deleted_at column to all tables that need soft deletes
        $tables = [
            'roles',
            'users', 
            'role_permissions',
            'clients',
            'employees',
            'employee_leaves',
            'complaints',
            'complaint_attachments',
            'complaint_logs',
            'complaint_spares',
            'spares',
            'spare_approval_performa',
            'spare_approval_items',
            'spare_stock_logs',
            'reports_summary',
            'sla_rules'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                // Check if column already exists before adding
                if (!Schema::hasColumn($tableName, 'deleted_at')) {
                    try {
                        Schema::table($tableName, function (Blueprint $table) {
                            $table->softDeletes();
                        });
                    } catch (\Exception $e) {
                        // If Schema method fails, try raw SQL
                        try {
                            DB::statement("ALTER TABLE `{$tableName}` ADD COLUMN `deleted_at` TIMESTAMP NULL AFTER `updated_at`");
                        } catch (\Exception $e2) {
                            \Log::warning("Failed to add deleted_at to {$tableName}: " . $e2->getMessage());
                        }
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove deleted_at column from all tables
        $tables = [
            'roles',
            'users', 
            'role_permissions',
            'clients',
            'employees',
            'employee_leaves',
            'complaints',
            'complaint_attachments',
            'complaint_logs',
            'complaint_spares',
            'spares',
            'spare_approval_performa',
            'spare_approval_items',
            'spare_stock_logs',
            'reports_summary',
            'sla_rules'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
