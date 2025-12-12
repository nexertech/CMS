<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SpareStockLog;
use App\Models\Spare;

class BackfillStockLogBrandNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:backfill-brand-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill brand_name in old stock logs with their spare\'s current brand_name';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to backfill brand names in stock logs...');

        // Get all stock logs that don't have brand_name
        $logsWithoutBrand = SpareStockLog::whereNull('brand_name')
            ->orWhere('brand_name', '')
            ->get();

        $this->info("Found {$logsWithoutBrand->count()} logs without brand_name");

        $updated = 0;
        $skipped = 0;

        foreach ($logsWithoutBrand as $log) {
            $spare = Spare::find($log->spare_id);
            
            if (!$spare) {
                $this->warn("Spare ID {$log->spare_id} not found for log ID {$log->id}");
                $skipped++;
                continue;
            }

            if ($spare->brand_name) {
                $log->brand_name = $spare->brand_name;
                $log->save();
                $updated++;
            } else {
                $this->warn("Spare ID {$log->spare_id} doesn't have brand_name, skipping log ID {$log->id}");
                $skipped++;
            }
        }

        $this->info("Backfill completed!");
        $this->info("Updated: {$updated} logs");
        $this->info("Skipped: {$skipped} logs");

        return Command::SUCCESS;
    }
}
