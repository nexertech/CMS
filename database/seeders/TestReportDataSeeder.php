<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\Client;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TestReportDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Adding test report data...');

        // Get or create clients
        $clients = Client::firstOrCreate(
            ['client_name' => 'Test Client 1'],
            [
                'contact_person' => 'Test Person 1',
                'email' => 'test1@example.com',
                'phone' => '0300-1111111',
                'address' => 'Test Address 1',
                'city' => 'Karachi',
                'state' => 'Sindh',
                'status' => 'active',
            ]
        );

        $client2 = Client::firstOrCreate(
            ['client_name' => 'Test Client 2'],
            [
                'contact_person' => 'Test Person 2',
                'email' => 'test2@example.com',
                'phone' => '0300-2222222',
                'address' => 'Test Address 2',
                'city' => 'Lahore',
                'state' => 'Punjab',
                'status' => 'active',
            ]
        );

        $client3 = Client::firstOrCreate(
            ['client_name' => 'GE (N) Const Isld'],
            [
                'contact_person' => 'GE Contact',
                'email' => 'ge@example.com',
                'phone' => '0300-3333333',
                'address' => 'GE Construction Address',
                'city' => 'Islamabad',
                'state' => 'ICT',
                'status' => 'active',
            ]
        );

        // Get or create employees
        $employees = Employee::firstOrCreate(
            ['email' => 'emp1@test.com'],
            [
                'name' => 'Test Employee 1',
                'department' => 'B&R-I',
                'designation' => 'Technician',
                'phone' => '0300-4444444',
                'status' => 'active',
            ]
        );

        $emp2 = Employee::firstOrCreate(
            ['email' => 'emp2@test.com'],
            [
                'name' => 'Test Employee 2',
                'department' => 'B&R-II',
                'designation' => 'Technician',
                'phone' => '0300-5555555',
                'status' => 'active',
            ]
        );

        $emp3 = Employee::firstOrCreate(
            ['email' => 'emp3@test.com'],
            [
                'name' => 'Test Employee 3',
                'department' => 'E&M NRC',
                'designation' => 'Technician',
                'phone' => '0300-6666666',
                'status' => 'active',
            ]
        );

        $emp4 = Employee::firstOrCreate(
            ['email' => 'emp4@test.com'],
            [
                'name' => 'Test Employee 4',
                'department' => 'F&S',
                'designation' => 'Technician',
                'phone' => '0300-7777777',
                'status' => 'active',
            ]
        );

        // Departments for test data
        $departments = [
            'B&R-I',
            'B&R-II',
            'E&M NRC',
            'F&S',
        ];

        $statuses = [
            'new',
            'assigned',
            'in_progress',
            'resolved',
            'closed',
        ];

        $categories = [
            'Electric',
            'Plumbing',
            'General',
            'Kitchen',
            'Barrak Damages',
        ];

        $priorities = ['low', 'medium', 'high', 'urgent', 'emergency'];

        // Create complaints for last 30 days
        $startDate = Carbon::now()->subDays(30);
        
        $complaints = [];

        // B&R-I Department - 20 complaints
        for ($i = 0; $i < 20; $i++) {
            $createdAt = $startDate->copy()->addDays(rand(0, 29))->addHours(rand(0, 23));
            
            $complaints[] = [
                'title' => 'B&R-I Complaint ' . ($i + 1),
                'client_id' => $clients->id,
                'category' => $categories[array_rand($categories)],
                'department' => 'B&R-I',
                'description' => 'Test description for B&R-I complaint ' . ($i + 1),
                'status' => $statuses[array_rand($statuses)],
                'assigned_employee_id' => $employees->id,
                'priority' => $priorities[array_rand($priorities)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays(rand(0, 5)),
            ];
        }

        // B&R-II Department - 15 complaints
        for ($i = 0; $i < 15; $i++) {
            $createdAt = $startDate->copy()->addDays(rand(0, 29))->addHours(rand(0, 23));
            
            $complaints[] = [
                'title' => 'B&R-II Complaint ' . ($i + 1),
                'client_id' => $client2->id,
                'category' => $categories[array_rand($categories)],
                'department' => 'B&R-II',
                'description' => 'Test description for B&R-II complaint ' . ($i + 1),
                'status' => $statuses[array_rand($statuses)],
                'assigned_employee_id' => $emp2->id,
                'priority' => $priorities[array_rand($priorities)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays(rand(0, 5)),
            ];
        }

        // E&M NRC Department - 25 complaints (Elect category)
        for ($i = 0; $i < 25; $i++) {
            $createdAt = $startDate->copy()->addDays(rand(0, 29))->addHours(rand(0, 23));
            
            $complaints[] = [
                'title' => 'E&M NRC Elect Complaint ' . ($i + 1),
                'client_id' => $clients->id,
                'category' => 'Electric',
                'department' => 'E&M NRC',
                'description' => 'Test description for E&M NRC Elect complaint ' . ($i + 1),
                'status' => $statuses[array_rand($statuses)],
                'assigned_employee_id' => $emp3->id,
                'priority' => $priorities[array_rand($priorities)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays(rand(0, 5)),
            ];
        }

        // E&M NRC Department - 10 complaints (Gas category)
        for ($i = 0; $i < 10; $i++) {
            $createdAt = $startDate->copy()->addDays(rand(0, 29))->addHours(rand(0, 23));
            
            $complaints[] = [
                'title' => 'E&M NRC Gas Complaint ' . ($i + 1),
                'client_id' => $client2->id,
                'category' => 'Gas',
                'department' => 'E&M NRC',
                'description' => 'Test description for E&M NRC Gas complaint ' . ($i + 1),
                'status' => $statuses[array_rand($statuses)],
                'assigned_employee_id' => $emp3->id,
                'priority' => $priorities[array_rand($priorities)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays(rand(0, 5)),
            ];
        }

        // E&M NRC Department - 15 complaints (Water Supply/Plumbing)
        for ($i = 0; $i < 15; $i++) {
            $createdAt = $startDate->copy()->addDays(rand(0, 29))->addHours(rand(0, 23));
            
            $complaints[] = [
                'title' => 'E&M NRC Water Complaint ' . ($i + 1),
                'client_id' => $clients->id,
                'category' => 'Plumbing',
                'department' => 'E&M NRC',
                'description' => 'Test description for E&M NRC Water Supply complaint ' . ($i + 1),
                'status' => $statuses[array_rand($statuses)],
                'assigned_employee_id' => $emp3->id,
                'priority' => $priorities[array_rand($priorities)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays(rand(0, 5)),
            ];
        }

        // F&S Department - 12 complaints
        for ($i = 0; $i < 12; $i++) {
            $createdAt = $startDate->copy()->addDays(rand(0, 29))->addHours(rand(0, 23));
            
            $complaints[] = [
                'title' => 'F&S Complaint ' . ($i + 1),
                'client_id' => $client2->id,
                'category' => $categories[array_rand($categories)],
                'department' => 'F&S',
                'description' => 'Test description for F&S complaint ' . ($i + 1),
                'status' => $statuses[array_rand($statuses)],
                'assigned_employee_id' => $emp4->id,
                'priority' => $priorities[array_rand($priorities)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays(rand(0, 5)),
            ];
        }

        // Add some special cases
        // Door Locked complaints
        for ($i = 0; $i < 5; $i++) {
            $createdAt = $startDate->copy()->addDays(rand(0, 29))->addHours(rand(0, 23));
            
            $complaints[] = [
                'title' => 'Door Locked Complaint ' . ($i + 1),
                'client_id' => $clients->id,
                'category' => 'General',
                'department' => $departments[array_rand($departments)],
                'description' => 'Door locked issue - unable to access the area',
                'status' => $statuses[array_rand(['new', 'in_progress'])],
                'assigned_employee_id' => null, // Unauthorized
                'priority' => $priorities[array_rand($priorities)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }

        // Barrak Damages complaints
        for ($i = 0; $i < 8; $i++) {
            $createdAt = $startDate->copy()->addDays(rand(0, 29))->addHours(rand(0, 23));
            
            $complaints[] = [
                'title' => 'Barrak Damages Complaint ' . ($i + 1),
                'client_id' => $clients->id,
                'category' => 'Barrak Damages',
                'department' => $departments[array_rand($departments)],
                'description' => 'Barrak damages reported - requires immediate attention',
                'status' => $statuses[array_rand($statuses)],
                'assigned_employee_id' => $employees->id,
                'priority' => 'high',
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays(rand(0, 5)),
            ];
        }

        // GE (N) Const Isld complaints
        for ($i = 0; $i < 6; $i++) {
            $createdAt = $startDate->copy()->addDays(rand(0, 29))->addHours(rand(0, 23));
            
            $complaints[] = [
                'title' => 'GE Const Isld Complaint ' . ($i + 1),
                'client_id' => $client3->id,
                'category' => $categories[array_rand($categories)],
                'department' => $departments[array_rand($departments)],
                'description' => 'Pertains to GE (N) Const Isld - maintenance required',
                'status' => $statuses[array_rand($statuses)],
                'assigned_employee_id' => $employees->id,
                'priority' => $priorities[array_rand($priorities)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays(rand(0, 5)),
            ];
        }

        // Insert all complaints
        foreach ($complaints as $complaint) {
            Complaint::create($complaint);
        }

        $this->command->info('Created ' . count($complaints) . ' test complaints successfully!');
        $this->command->info('Departments: B&R-I, B&R-II, E&M NRC, F&S');
        $this->command->info('Statuses: new, assigned, in_progress, resolved, closed');
    }
}
