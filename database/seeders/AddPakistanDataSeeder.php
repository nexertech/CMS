<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Spare;
use App\Models\Complaint;
use App\Models\SlaRule;
use App\Models\SpareApprovalPerforma;
use App\Models\SpareApprovalItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AddPakistanDataSeeder extends Seeder
{
    /**
     * Run the database seeds with Pakistan-specific realistic data
     */
    public function run(): void
    {
        $this->command->info('Adding Pakistan-specific realistic data...');

        // Add Users with Pakistan names and phone numbers
        $this->addUsers();
        
        // Add Clients with Pakistan company names
        $this->addClients();
        
        // Add Employees with Pakistan data  
        $this->addEmployees();
        
        // Add Spare Parts with Pakistan product names
        $this->addSpares();
        
        // Add Complaints with realistic scenarios
        $this->addComplaints();
        
        // Add Approvals linked to complaints
        $this->addApprovals();
        
        // Add SLA Rules
        $this->addSlaRules();
        
        $this->command->info('Pakistan data added successfully!');
    }
    
    private function addSlaRules()
    {
        $this->command->info('Adding SLA rules...');
        
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Skipping SLA rules...');
            return;
        }
        
        $slaRules = [
            [
                'complaint_type' => 'urgent',
                'max_response_time' => 1,
                'max_resolution_time' => 4,
                'escalation_level' => 3,
                'notify_to' => $users->random()->id,
                'status' => 'active',
            ],
            [
                'complaint_type' => 'high',
                'max_response_time' => 2,
                'max_resolution_time' => 8,
                'escalation_level' => 2,
                'notify_to' => $users->random()->id,
                'status' => 'active',
            ],
            [
                'complaint_type' => 'medium',
                'max_response_time' => 4,
                'max_resolution_time' => 24,
                'escalation_level' => 1,
                'notify_to' => $users->random()->id,
                'status' => 'active',
            ],
            [
                'complaint_type' => 'low',
                'max_response_time' => 8,
                'max_resolution_time' => 48,
                'escalation_level' => 1,
                'notify_to' => $users->random()->id,
                'status' => 'active',
            ],
            [
                'complaint_type' => 'electric',
                'max_response_time' => 2,
                'max_resolution_time' => 6,
                'escalation_level' => 3,
                'notify_to' => $users->random()->id,
                'status' => 'active',
            ],
            [
                'complaint_type' => 'technical',
                'max_response_time' => 3,
                'max_resolution_time' => 12,
                'escalation_level' => 2,
                'notify_to' => $users->random()->id,
                'status' => 'active',
            ],
        ];
        
        foreach ($slaRules as $rule) {
            SlaRule::updateOrCreate(
                [
                    'complaint_type' => $rule['complaint_type'],
                    'escalation_level' => $rule['escalation_level'],
                ],
                $rule
            );
        }
    }
    
    private function addApprovals()
    {
        $this->command->info('Adding approvals...');
        
        $complaints = Complaint::whereIn('status', ['assigned', 'in_progress'])->get();
        $employees = Employee::all();
        $spares = Spare::all();
        
        if ($complaints->isEmpty() || $employees->isEmpty() || $spares->isEmpty()) {
            $this->command->warn('Not enough data to create approvals. Skipping...');
            return;
        }
        
        // Create some approval performas
        for ($i = 0; $i < 5; $i++) {
            try {
                $complaint = $complaints->random();
                $employee = $employees->random();
                
                $approval = SpareApprovalPerforma::create([
                    'complaint_id' => $complaint->id,
                    'requested_by' => $employee->id,
                    'status' => ['pending', 'approved', 'rejected'][rand(0, 2)],
                    'remarks' => 'Spare parts required for complaint resolution.',
                    'created_at' => now()->subDays(rand(1, 20)),
                ]);
                
                // Add items to approval
                $itemsCount = rand(1, 3);
                for ($j = 0; $j < $itemsCount; $j++) {
                    $spare = $spares->random();
                    SpareApprovalItem::create([
                        'performa_id' => $approval->id,
                        'spare_id' => $spare->id,
                        'quantity_requested' => rand(1, 5),
                        'reason' => 'Required for maintenance',
                    ]);
                }
            } catch (\Exception $e) {
                $this->command->warn('Error creating approval: ' . $e->getMessage());
            }
        }
    }

    private function addUsers()
    {
        $this->command->info('Adding users...');
        
        $users = [
            [
                'username' => 'admin',
                'email' => 'admin@cms.com',
                'password' => Hash::make('password'),
                'phone' => '0300-1234567',
                'role_id' => Role::where('role_name', 'admin')->first()->id,
                'status' => 'active',
            ],
            [
                'username' => 'manager',
                'email' => 'manager@cms.com',
                'password' => Hash::make('password'),
                'phone' => '0311-7654321',
                'role_id' => Role::where('role_name', 'manager')->first()->id,
                'status' => 'active',
            ],
            [
                'username' => 'muhammad.asif',
                'email' => 'muhammad.asif@cms.com',
                'password' => Hash::make('password'),
                'phone' => '0333-9876543',
                'role_id' => Role::where('role_name', 'employee')->first()->id,
                'status' => 'active',
            ],
            [
                'username' => 'ali.hassan',
                'email' => 'ali.hassan@cms.com',
                'password' => Hash::make('password'),
                'phone' => '0321-4567890',
                'role_id' => Role::where('role_name', 'employee')->first()->id,
                'status' => 'active',
            ],
            [
                'username' => 'fatima.khan',
                'email' => 'fatima.khan@cms.com',
                'password' => Hash::make('password'),
                'phone' => '0312-1234567',
                'role_id' => Role::where('role_name', 'employee')->first()->id,
                'status' => 'active',
            ],
            [
                'username' => 'usman.ahmed',
                'email' => 'usman.ahmed@cms.com',
                'password' => Hash::make('password'),
                'phone' => '0333-5432109',
                'role_id' => Role::where('role_name', 'employee')->first()->id,
                'status' => 'active',
            ],
            [
                'username' => 'ayesha.malik',
                'email' => 'ayesha.malik@cms.com',
                'password' => Hash::make('password'),
                'phone' => '0300-8765432',
                'role_id' => Role::where('role_name', 'employee')->first()->id,
                'status' => 'active',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['username' => $userData['username']],
                $userData
            );
        }
    }

    private function addClients()
    {
        $this->command->info('Adding clients...');
        
        $clients = [
            [
                'client_name' => 'Ahmed Ali',
                'contact_person' => 'Ahmed Ali',
                'email' => 'ahmed.ali@email.pk',
                'phone' => '0300-1234567',
                'address' => 'Gulshan Block 15, Karachi',
                'city' => 'Karachi',
                'state' => 'Sindh',
                'pincode' => '75500',
                'status' => 'active',
            ],
            [
                'client_name' => 'Hassan Khan',
                'contact_person' => 'Hassan Khan',
                'email' => 'hassan.khan@email.pk',
                'phone' => '0311-7654321',
                'address' => 'Model Town, Lahore',
                'city' => 'Lahore',
                'state' => 'Punjab',
                'pincode' => '54000',
                'status' => 'active',
            ],
            [
                'client_name' => 'Fatima Malik',
                'contact_person' => 'Fatima Malik',
                'email' => 'fatima.malik@email.pk',
                'phone' => '0333-9876543',
                'address' => 'DHA Phase 1, Islamabad',
                'city' => 'Islamabad',
                'state' => 'ICT',
                'pincode' => '44000',
                'status' => 'active',
            ],
            [
                'client_name' => 'Usman Sheikh',
                'contact_person' => 'Usman Sheikh',
                'email' => 'usman.sheikh@email.pk',
                'phone' => '0321-4567890',
                'address' => 'Bahria Town Phase 7, Rawalpindi',
                'city' => 'Rawalpindi',
                'state' => 'Punjab',
                'pincode' => '46000',
                'status' => 'active',
            ],
            [
                'client_name' => 'Ayesha Raza',
                'contact_person' => 'Ayesha Raza',
                'email' => 'ayesha.raza@email.pk',
                'phone' => '0307-5432109',
                'address' => 'Clifton Block 5, Karachi',
                'city' => 'Karachi',
                'state' => 'Sindh',
                'pincode' => '75600',
                'status' => 'active',
            ],
            [
                'client_name' => 'Bilal Sheikh',
                'contact_person' => 'Bilal Sheikh',
                'email' => 'bilal.sheikh@email.pk',
                'phone' => '0312-8765432',
                'address' => 'Johar Town Sector A, Lahore',
                'city' => 'Lahore',
                'state' => 'Punjab',
                'pincode' => '54000',
                'status' => 'active',
            ],
            [
                'client_name' => 'Saima Khan',
                'contact_person' => 'Saima Khan',
                'email' => 'saima.khan@email.pk',
                'phone' => '0332-1112223',
                'address' => 'F-8 Sector, Islamabad',
                'city' => 'Islamabad',
                'state' => 'ICT',
                'pincode' => '44000',
                'status' => 'active',
            ],
            [
                'client_name' => 'Junaid Ali',
                'contact_person' => 'Junaid Ali',
                'email' => 'junaid.ali@email.pk',
                'phone' => '0300-9998887',
                'address' => 'PECHS Block 6, Karachi',
                'city' => 'Karachi',
                'state' => 'Sindh',
                'pincode' => '75400',
                'status' => 'active',
            ],
            [
                'client_name' => 'Hina Butt',
                'contact_person' => 'Hina Butt',
                'email' => 'hina.butt@email.pk',
                'phone' => '0334-5556667',
                'address' => 'Garden Town, Lahore',
                'city' => 'Lahore',
                'state' => 'Punjab',
                'pincode' => '54000',
                'status' => 'active',
            ],
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }
    }

    private function addEmployees()
    {
        $this->command->info('Adding employees...');
        
        $users = User::whereIn('username', ['muhammad.asif', 'ali.hassan', 'fatima.khan', 'usman.ahmed', 'ayesha.malik'])->get();
        
        $employees = [
            [
                'user_id' => $users[0]->id,
                'department' => 'Technical',
                'designation' => 'Senior Technician',
                'phone' => '0333-9876543',
                'biometric_id' => 'BIO001',
                'date_of_hire' => now()->subYears(2),
                'leave_quota' => 20,
                'address' => 'Gulshan Block 15, Karachi',
            ],
            [
                'user_id' => $users[1]->id ?? $users[0]->id,
                'department' => 'Maintenance',
                'designation' => 'Junior Technician',
                'phone' => '0321-4567890',
                'biometric_id' => 'BIO002',
                'date_of_hire' => now()->subYear(),
                'leave_quota' => 15,
                'address' => 'Model Town, Lahore',
            ],
            [
                'user_id' => $users[2]->id ?? $users[0]->id,
                'department' => 'Electrical',
                'designation' => 'Electrical Engineer',
                'phone' => '0312-1234567',
                'biometric_id' => 'BIO003',
                'date_of_hire' => now()->subMonths(18),
                'leave_quota' => 18,
                'address' => 'Clifton Block 9, Karachi',
            ],
            [
                'user_id' => $users[3]->id ?? $users[0]->id,
                'department' => 'Technical',
                'designation' => 'IT Support Specialist',
                'phone' => '0333-5432109',
                'biometric_id' => 'BIO004',
                'date_of_hire' => now()->subYear(),
                'leave_quota' => 16,
                'address' => 'Faisal Town, Karachi',
            ],
            [
                'user_id' => $users[4]->id ?? $users[0]->id,
                'department' => 'Administration',
                'designation' => 'Administrative Officer',
                'phone' => '0300-8765432',
                'biometric_id' => 'BIO005',
                'date_of_hire' => now()->subMonths(9),
                'leave_quota' => 20,
                'address' => 'Johar Town, Lahore',
            ],
        ];

        foreach ($employees as $employee) {
            Employee::updateOrCreate(
                ['user_id' => $employee['user_id']],
                $employee
            );
        }
    }

    private function addSpares()
    {
        $this->command->info('Adding spare parts...');
        
        $spares = [
            ['item_name' => 'MCB Circuit Breaker 32A', 'category' => 'electric', 'unit' => 'pcs', 'unit_price' => 2500, 'stock_quantity' => 15, 'threshold_level' => 10, 'supplier' => 'Pakistan Electric'],
            ['item_name' => 'Wire 2.5mm Twin & Earth', 'category' => 'electric', 'unit' => 'meter', 'unit_price' => 180, 'stock_quantity' => 250, 'threshold_level' => 100, 'supplier' => 'Karachi Cables'],
            ['item_name' => 'Bathroom Shower Set White', 'category' => 'sanitary', 'unit' => 'set', 'unit_price' => 3500, 'stock_quantity' => 8, 'threshold_level' => 5, 'supplier' => 'Dolphin Plumbing'],
            ['item_name' => 'Toilet Flush Valve', 'category' => 'sanitary', 'unit' => 'pcs', 'unit_price' => 1200, 'stock_quantity' => 12, 'threshold_level' => 8, 'supplier' => 'Elite Sanitary'],
            ['item_name' => 'Kitchen Mixer Tap', 'category' => 'kitchen', 'unit' => 'pcs', 'unit_price' => 4500, 'stock_quantity' => 6, 'threshold_level' => 4, 'supplier' => 'Prime Kitchen'],
            ['item_name' => 'PVC Pipe 1 Inch', 'category' => 'plumbing', 'unit' => 'meter', 'unit_price' => 300, 'stock_quantity' => 200, 'threshold_level' => 50, 'supplier' => 'Pak Pipes'],
            ['item_name' => 'LED Bulb 12W Cool White', 'category' => 'electric', 'unit' => 'pcs', 'unit_price' => 850, 'stock_quantity' => 30, 'threshold_level' => 20, 'supplier' => 'Osram Pakistan'],
            ['item_name' => 'Wire Nuts Connectors', 'category' => 'electric', 'unit' => 'pcs', 'unit_price' => 150, 'stock_quantity' => 100, 'threshold_level' => 50, 'supplier' => 'Diamond Electric'],
            ['item_name' => 'Electrical Switch 1-Way', 'category' => 'electric', 'unit' => 'pcs', 'unit_price' => 450, 'stock_quantity' => 25, 'threshold_level' => 15, 'supplier' => 'Pakistan Electric'],
            ['item_name' => 'Ceiling Fan 52 Inch', 'category' => 'electric', 'unit' => 'pcs', 'unit_price' => 8500, 'stock_quantity' => 5, 'threshold_level' => 3, 'supplier' => 'GFC Pakistan'],
            ['item_name' => 'Solar Panel 330W', 'category' => 'electric', 'unit' => 'pcs', 'unit_price' => 35000, 'stock_quantity' => 3, 'threshold_level' => 2, 'supplier' => 'Solar Solutions'],
            ['item_name' => 'Kitchen Sink Stainless Steel', 'category' => 'kitchen', 'unit' => 'pcs', 'unit_price' => 12000, 'stock_quantity' => 4, 'threshold_level' => 2, 'supplier' => 'Kitchen Works'],
            ['item_name' => 'Water Filter RO System', 'category' => 'sanitary', 'unit' => 'set', 'unit_price' => 25000, 'stock_quantity' => 2, 'threshold_level' => 1, 'supplier' => 'Aqua Water'],
            ['item_name' => 'PVC Pipe 2 Inch', 'category' => 'plumbing', 'unit' => 'meter', 'unit_price' => 550, 'stock_quantity' => 150, 'threshold_level' => 40, 'supplier' => 'Pak Pipes'],
            ['item_name' => 'Bathroom Tiles White 12x12', 'category' => 'sanitary', 'unit' => 'box', 'unit_price' => 4500, 'stock_quantity' => 20, 'threshold_level' => 10, 'supplier' => 'City Tiles'],
        ];

        foreach ($spares as $spare) {
            Spare::create($spare);
        }
    }

    private function addComplaints()
    {
        $this->command->info('Adding complaints...');
        
        $clients = Client::all();
        $employees = Employee::all();
        
        if ($clients->isEmpty() || $employees->isEmpty()) {
            $this->command->warn('No clients or employees found. Skipping complaints.');
            return;
        }

        $complaints = [
            ['title' => 'Power Outage in Building A Block 5', 'category' => 'electric', 'priority' => 'urgent'],
            ['title' => 'Water Leakage in Ground Floor Washroom', 'category' => 'sanitary', 'priority' => 'high'],
            ['title' => 'AC Not Working in Conference Room', 'category' => 'technical', 'priority' => 'high'],
            ['title' => 'Elevator Door Not Closing Properly', 'category' => 'technical', 'priority' => 'urgent'],
            ['title' => 'Internet Connectivity Issue in IT Department', 'category' => 'technical', 'priority' => 'medium'],
            ['title' => 'CCTV Camera Not Recording', 'category' => 'technical', 'priority' => 'medium'],
            ['title' => 'Fire Alarm Malfunction in Basement', 'category' => 'technical', 'priority' => 'urgent'],
            ['title' => 'Parking Gate Not Opening', 'category' => 'technical', 'priority' => 'high'],
            ['title' => 'Generator Running Out of Fuel', 'category' => 'technical', 'priority' => 'urgent'],
            ['title' => 'Water Cooler Not Working in Lobby', 'category' => 'sanitary', 'priority' => 'low'],
            ['title' => 'Main Gate Automation Not Responding', 'category' => 'technical', 'priority' => 'high'],
            ['title' => 'Water Pressure Low in 2nd Floor', 'category' => 'plumbing', 'priority' => 'medium'],
            ['title' => 'Office Furniture Damaged', 'category' => 'service', 'priority' => 'low'],
            ['title' => 'Phone Line Not Working', 'category' => 'technical', 'priority' => 'high'],
            ['title' => 'Backup Generator Maintenance Due', 'category' => 'technical', 'priority' => 'medium'],
            ['title' => 'Kitchen Drainage Blocked', 'category' => 'plumbing', 'priority' => 'urgent'],
            ['title' => 'UPS Battery Replacement Needed', 'category' => 'electric', 'priority' => 'high'],
            ['title' => 'Lights Flickering in Main Hall', 'category' => 'electric', 'priority' => 'medium'],
            ['title' => 'Fire Exit Door Jammed', 'category' => 'technical', 'priority' => 'urgent'],
            ['title' => 'Internet Router Reset Required', 'category' => 'technical', 'priority' => 'low'],
            ['title' => 'Air Conditioner Tripping Circuit', 'category' => 'electric', 'priority' => 'high'],
        ];

        foreach ($complaints as $complaintData) {
            Complaint::create([
                'title' => $complaintData['title'],
                'client_id' => $clients->random()->id,
                'category' => $complaintData['category'],
                'priority' => $complaintData['priority'],
                'description' => 'Customer reported issue requires immediate attention and resolution.',
                'assigned_employee_id' => $employees->random()->id,
                'status' => 'assigned',
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(0, 15)),
            ]);
        }
    }
}

