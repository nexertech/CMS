<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Complaint;
use App\Models\Client;
use App\Models\City;
use App\Models\Sector;
use App\Models\Employee;
use Carbon\Carbon;

class ChartVisualizationSeeder extends Seeder
{
    public function run()
    {
        // Ensure we have dependencies
        $clients = Client::all();
        $sectors = Sector::with('city')->get(); // Get sectors with their cities for consistency
        $employees = Employee::all();
        
        if ($clients->isEmpty() || $sectors->isEmpty()) {
            $this->command->error('Please ensure Clients and Sectors (with Cities) exist before running this seeder.');
            return;
        }

        $year = 2025;
        $months = range(1, 12);
        $categories = array_keys(Complaint::getCategories()); // Get all available categories
        
        // Create an index for round-robin employee assignment
        $employeeIndex = 0;
        $employeeCount = $employees->count();

        foreach ($months as $month) {
            $date = Carbon::create($year, $month, 15, 12, 0, 0);

            // 1. Addressed (Resolved/Closed) - High Volume (e.g., 80-100)
            $addressedCount = rand(80, 100);
            for ($i = 0; $i < $addressedCount; $i++) {
                $sector = $sectors->random();
                $city = $sector->city;
                $client = $clients->random();
                // Use round-robin for employee assignment
                $employee = null;
                if ($employeeCount > 0) {
                    $employee = $employees[$employeeIndex % $employeeCount];
                    $employeeIndex++;
                }
                $category = $categories[array_rand($categories)];

                if (!$city) continue; // Skip if sector has no city

                Complaint::create([
                    'title' => 'Dummy Resolved Complaint ' . $i,
                    'client_id' => $client->id,
                    'city_id' => $city->id,
                    'sector_id' => $sector->id,
                    'category' => $category,
                    'priority' => 'medium',
                    'description' => 'Auto-generated dummy complaint for chart visualization.',
                    'assigned_employee_id' => $employee ? $employee->id : null,
                    'status' => 'resolved',
                    'created_at' => $date->copy()->subDays(rand(1, 5)),
                    'updated_at' => $date,
                    'closed_at' => $date,
                ]);
            }

            // 2. Unauthorized - Low Volume (e.g., 2-5)
            $unauthorizedCount = rand(2, 5);
            for ($i = 0; $i < $unauthorizedCount; $i++) {
                $sector = $sectors->random();
                $city = $sector->city;
                $client = $clients->random();
                // Use round-robin for employee assignment
                $employee = null;
                if ($employeeCount > 0) {
                    $employee = $employees[$employeeIndex % $employeeCount];
                    $employeeIndex++;
                }
                $category = $categories[array_rand($categories)];

                if (!$city) continue;

                Complaint::create([
                    'title' => 'Dummy Unauthorized Complaint ' . $i,
                    'client_id' => $client->id,
                    'city_id' => $city->id,
                    'sector_id' => $sector->id,
                    'category' => $category,
                    'priority' => 'low',
                    'description' => 'Auto-generated dummy complaint for chart visualization.',
                    'assigned_employee_id' => $employee ? $employee->id : null,
                    'status' => 'un_authorized',
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }

            // 3. Performa - Low Volume (e.g., 2-5)
            $performaCount = rand(2, 5);
            for ($i = 0; $i < $performaCount; $i++) {
                $sector = $sectors->random();
                $city = $sector->city;
                $client = $clients->random();
                // Use round-robin for employee assignment
                $employee = null;
                if ($employeeCount > 0) {
                    $employee = $employees[$employeeIndex % $employeeCount];
                    $employeeIndex++;
                }
                $category = $categories[array_rand($categories)];

                if (!$city) continue;

                // Randomly pick a performa status
                $statuses = ['work_performa', 'maint_performa', 'work_priced_performa', 'maint_priced_performa'];
                $status = $statuses[array_rand($statuses)];

                Complaint::create([
                    'title' => 'Dummy Performa Complaint ' . $i,
                    'client_id' => $client->id,
                    'city_id' => $city->id,
                    'sector_id' => $sector->id,
                    'category' => $category,
                    'priority' => 'medium',
                    'description' => 'Auto-generated dummy complaint for chart visualization.',
                    'assigned_employee_id' => $employee ? $employee->id : null,
                    'status' => $status,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }
    }
}
