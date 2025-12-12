<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\Client;
use App\Models\Employee;
use App\Models\City;
use App\Models\Sector;
use App\Models\ComplaintCategory;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AddRecentMonthsComplaintsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Adding 500 complaints for the entire year...');

        // Get all required data
        $clients = Client::all();
        $employees = Employee::all();
        $cities = City::where('status', 'active')->get();
        $categories = ComplaintCategory::all();
        
        if ($clients->isEmpty() || $employees->isEmpty() || $cities->isEmpty()) {
            $this->command->warn('No clients, employees, or cities found. Skipping complaints.');
            return;
        }

        // Get sectors for each city
        $sectorsByCity = [];
        foreach ($cities as $city) {
            $sectorsByCity[$city->id] = Sector::where('city_id', $city->id)->where('status', 'active')->get();
        }

        // Statuses array
        $statuses = [
            'assigned',
            'in_progress',
            'resolved',
            'closed',
            'work_performa',
            'maint_performa',
            'product_na',
            'un_authorized',
            'pertains_to_ge_const_isld',
        ];

        // Categories array
        $categoryNames = ['technical', 'service', 'billing', 'sanitary', 'electric', 'kitchen', 'plumbing', 'other'];
        
        // Complaints data
        $complaintTitles = [
            'Power Outage in Building A',
            'Water Leakage in Ground Floor',
            'AC Not Working in Conference Room',
            'Elevator Door Not Closing',
            'Internet Connectivity Issue',
            'CCTV Camera Not Recording',
            'Fire Alarm Malfunction',
            'Parking Gate Not Opening',
            'Generator Running Out of Fuel',
            'Water Cooler Not Working',
            'Main Gate Automation Issue',
            'Water Pressure Low',
            'Office Furniture Damaged',
            'Phone Line Not Working',
            'Backup Generator Maintenance',
            'Kitchen Drainage Blocked',
            'UPS Battery Replacement',
            'Lights Flickering',
            'Fire Exit Door Jammed',
            'Internet Router Reset',
            'Air Conditioner Tripping',
            'HVAC System Maintenance',
            'Security System Update',
            'Plumbing Pipe Leak',
            'Electrical Panel Inspection',
        ];

        $totalComplaints = 0;
        $targetComplaints = 500;
        $now = Carbon::now();
        $currentYear = $now->year;

        // Create complaints for entire year (12 months)
        $complaintsPerMonth = intval($targetComplaints / 12); // ~41-42 per month
        $remainingComplaints = $targetComplaints % 12; // Distribute remainder across months

        for ($monthOffset = 0; $monthOffset < 12; $monthOffset++) {
            $monthDate = $now->copy()->startOfYear()->addMonths($monthOffset);
            $daysInMonth = $monthDate->daysInMonth;
            
            // Add extra complaints to first few months to distribute remainder
            $monthlyCount = $complaintsPerMonth + ($monthOffset < $remainingComplaints ? 1 : 0);
            
            for ($i = 0; $i < $monthlyCount; $i++) {
                // Random day in the month
                $day = rand(1, $daysInMonth);
                $createdAt = $monthDate->copy()->day($day)->hour(rand(8, 18))->minute(rand(0, 59));
                
                // Random city and sector
                $city = $cities->random();
                $sectors = $sectorsByCity[$city->id] ?? collect();
                $sector = $sectors->isNotEmpty() ? $sectors->random() : null;
                
                // Random status
                $status = $statuses[array_rand($statuses)];
                
                // Random category
                $category = $categoryNames[array_rand($categoryNames)];
                
                // If resolved or closed, set closed_at
                $closedAt = null;
                if (in_array($status, ['resolved', 'closed'])) {
                    $daysToResolve = rand(1, 15);
                    $closedAt = $createdAt->copy()->addDays($daysToResolve);
                    // Make sure closed_at is not in the future
                    if ($closedAt->isFuture()) {
                        $closedAt = $createdAt->copy()->addDays(rand(1, min(15, $now->diffInDays($createdAt))));
                    }
                }
                
                Complaint::create([
                    'title' => $complaintTitles[array_rand($complaintTitles)] . ' - ' . $city->name,
                    'client_id' => $clients->random()->id,
                    'city_id' => $city->id,
                    'sector_id' => $sector ? $sector->id : null,
                    'category' => $category,
                    'priority' => ['low', 'medium', 'high', 'urgent'][array_rand(['low', 'medium', 'high', 'urgent'])],
                    'description' => 'Test complaint for dashboard graph visualization. This complaint was created to populate the monthly complaints chart.',
                    'assigned_employee_id' => $employees->random()->id,
                    'status' => $status,
                    'closed_at' => $closedAt,
                    'created_at' => $createdAt,
                    'updated_at' => $closedAt ?? $createdAt->copy()->addDays(rand(1, 5)),
                ]);
                
                $totalComplaints++;
            }
        }

        $this->command->info("Successfully created {$totalComplaints} complaints for the entire year ({$currentYear})!");
    }
}

