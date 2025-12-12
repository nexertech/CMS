<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Complaint;
use App\Models\Client;
use App\Models\Sector;
use App\Models\Employee;
use Carbon\Carbon;

class EmployeePerformanceSeeder extends Seeder
{
    public function run()
    {
        $clients = Client::all();
        $sectors = Sector::with('city')->get();
        $employees = Employee::all();
        
        if ($clients->isEmpty() || $sectors->isEmpty() || $employees->isEmpty()) {
            $this->command->error('Please ensure Clients, Sectors, and Employees exist before running this seeder.');
            return;
        }

        $categories = array_keys(Complaint::getCategories());
        
        // Create complaints for each employee (evenly distributed)
        // Each employee will get 30-50 complaints in the last 30 days
        foreach ($employees as $employee) {
            $complaintCount = rand(30, 50);
            
            for ($i = 0; $i < $complaintCount; $i++) {
                $sector = $sectors->random();
                $city = $sector->city;
                $client = $clients->random();
                $category = $categories[array_rand($categories)];
                
                if (!$city) continue;
                
                // Random date within last 30 days
                $date = Carbon::now()->subDays(rand(1, 30));
                
                Complaint::create([
                    'title' => 'Employee Perf Test - ' . $employee->name . ' - ' . $i,
                    'client_id' => $client->id,
                    'city_id' => $city->id,
                    'sector_id' => $sector->id,
                    'category' => $category,
                    'priority' => 'medium',
                    'description' => 'Auto-generated for Employee Performance chart visualization.',
                    'assigned_employee_id' => $employee->id,
                    'status' => 'in_progress', // All in_progress
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }
        
        $this->command->info('Created complaints for ' . $employees->count() . ' employees.');
    }
}
