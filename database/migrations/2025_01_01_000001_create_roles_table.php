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
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('role_name', 50)->unique();
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });

            // Insert access level roles
            DB::table('roles')->insert([
                [
                    'role_name' => 'director',
                    'description' => 'Director - Head Office (Islamabad) - Can view all GEs and their complaints',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'role_name' => 'garrison_engineer',
                    'description' => 'Garrison Engineer (GE) - per city - Can view/manage complaint centers under his city',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'role_name' => 'complaint_center',
                    'description' => 'Complaint Center (Helpdesk staff) - Can register and track complaints for their area only',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'role_name' => 'department_staff',
                    'description' => 'Trade/Department Staff - Receive and register complaint, assign to concerned department',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
