<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AddPriorityToSlaRules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-priority-to-sla-rules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!Schema::hasColumn('sla_rules', 'priority')) {
            Schema::table('sla_rules', function (Blueprint $table) {
                $table->enum('priority', ['low', 'medium', 'high', 'urgent', 'emergency'])->default('medium')->after('complaint_type');
            });
            $this->info('Priority column added to sla_rules table successfully!');
        } else {
            $this->info('Priority column already exists in sla_rules table.');
        }
    }
}
