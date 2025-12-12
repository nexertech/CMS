<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Complaint;
use App\Models\SpareApprovalPerforma;
use App\Models\Employee;

class CreateMissingApprovalPerformas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'complaints:create-missing-approvals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create missing approval performas for existing complaints';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for complaints without approval performas...');
        $this->newLine();
        
        // Get all complaints that don't have approval performas
        $complaints = Complaint::whereDoesntHave('spareApprovals')->get();
        
        if ($complaints->isEmpty()) {
            $this->info('✓ All complaints already have approval performas.');
            return 0;
        }
        
        $this->info("Found {$complaints->count()} complaint(s) without approval performas:");
        foreach ($complaints as $complaint) {
            $this->line("  - Complaint ID: {$complaint->id} (Title: {$complaint->title})");
        }
        $this->newLine();
        
        // Get default employee
        $defaultEmployee = Employee::first();
        
        if (!$defaultEmployee) {
            $this->error('✗ No employee found in database. Please create an employee first.');
            return 1;
        }
        
        $this->info("Using default employee: {$defaultEmployee->name} (ID: {$defaultEmployee->id})");
        $this->newLine();
        
        $created = 0;
        $failed = 0;
        
        $bar = $this->output->createProgressBar($complaints->count());
        $bar->start();
        
        foreach ($complaints as $complaint) {
            try {
                // Use assigned employee if available, otherwise use default employee
                $requestedByEmployee = $complaint->assigned_employee_id 
                    ? Employee::find($complaint->assigned_employee_id)
                    : $defaultEmployee;
                
                if (!$requestedByEmployee) {
                    $requestedByEmployee = $defaultEmployee;
                }
                
                SpareApprovalPerforma::create([
                    'complaint_id' => $complaint->id,
                    'requested_by' => $requestedByEmployee->id,
                    'status' => 'pending',
                    'remarks' => 'Auto-created for existing complaint',
                ]);
                
                $created++;
                $bar->advance();
                
            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->error("✗ Failed to create approval performa for complaint ID: {$complaint->id} - {$e->getMessage()}");
                $bar->advance();
            }
        }
        
        $bar->finish();
        $this->newLine(2);
        
        if ($created > 0) {
            $this->info("✓ Successfully created {$created} approval performa(s).");
        }
        
        if ($failed > 0) {
            $this->warn("✗ Failed to create {$failed} approval performa(s).");
        }
        
        $this->newLine();
        $this->info('Done! All complaints should now appear in the approval modal.');
        
        return 0;
    }
}

