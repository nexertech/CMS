<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SpareApprovalPerforma;
use App\Models\SpareApprovalItem;
use App\Models\StockApprovalData;
use App\Models\ComplaintSpare;
use App\Models\Spare;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateApprovalStockData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:approval-stock-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing stock issues from spare_approval_performa to stock_approval_data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of approval stock data...');

        try {
            DB::beginTransaction();

            // Get all approvals that have items
            $approvals = SpareApprovalPerforma::with(['items.spare', 'complaint'])->get();
            
            $this->info("Found {$approvals->count()} approvals to process");

            $migratedCount = 0;
            $skippedCount = 0;

            foreach ($approvals as $approval) {
                $this->info("Processing approval ID {$approval->id}");
                
                if (!$approval->complaint) {
                    $this->warn("Skipping approval ID {$approval->id} - no complaint found");
                    $skippedCount++;
                    continue;
                }

                $this->info("  Complaint ID: {$approval->complaint_id}, Items count: " . $approval->items->count());

                // Get complaint spares that were used for this complaint (if any)
                $complaintSpares = ComplaintSpare::where('complaint_id', $approval->complaint_id)
                    ->with('spare')
                    ->get();

                $this->info("  Complaint spares found: " . $complaintSpares->count());

                // Process each approval item
                foreach ($approval->items as $approvalItem) {
                    $this->info("  Processing item ID {$approvalItem->id}, spare_id: {$approvalItem->spare_id}");
                    
                    if (!$approvalItem->spare) {
                        $this->warn("Skipping approval item {$approvalItem->id} - no spare found");
                        $skippedCount++;
                        continue;
                    }

                    // Check if record already exists
                    $existing = StockApprovalData::where('approval_id', $approval->id)
                        ->where('spare_id', $approvalItem->spare_id)
                        ->first();

                    if ($existing) {
                        $this->warn("Record already exists for approval {$approval->id}, spare {$approvalItem->spare_id}");
                        continue;
                    }

                    // Find matching complaint spare (if exists)
                    $complaintSpare = $complaintSpares->where('spare_id', $approvalItem->spare_id)->first();

                    $spare = $approvalItem->spare;
                    
                    // Calculate available stock before issue
                    // If complaint spare exists, use its quantity; otherwise use approval item quantity
                    $issuedQty = $complaintSpare ? $complaintSpare->quantity : ($approvalItem->quantity_approved ?? $approvalItem->quantity_requested);
                    $availableStock = $spare->stock_quantity + $issuedQty; // Stock before issue

                    $this->info("Creating record for approval {$approval->id}, spare {$approvalItem->spare_id}, complaint {$approval->complaint_id}");

                    StockApprovalData::create([
                        'spare_id' => $approvalItem->spare_id,
                        'complaint_id' => $approval->complaint_id,
                        'approval_id' => $approval->id,
                        'issue_date' => $complaintSpare ? ($complaintSpare->used_at ?? $approval->created_at) : $approval->created_at,
                        'category' => $spare->category,
                        'product_name' => $spare->item_name,
                        'available_stock' => $availableStock,
                        'requested_stock' => $approvalItem->quantity_requested,
                        'approval_stock' => $approvalItem->quantity_approved ?? $approvalItem->quantity_requested,
                        'issued_quantity' => $issuedQty,
                        'status' => 'pending',
                        'remarks' => 'Migrated from existing approval',
                        'issued_by' => $complaintSpare ? $complaintSpare->used_by : null,
                    ]);

                    $migratedCount++;
                }
            }

            DB::commit();

            $this->info("Migration completed successfully!");
            $this->info("Migrated: {$migratedCount} records");
            $this->info("Skipped: {$skippedCount} records");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Migration failed: " . $e->getMessage());
            Log::error('Migration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
}

