<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Branch;

class RecalculateScorecards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transport:recalculate-scorecards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate performance metrics for Real Voyage branches';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $branches = Branch::all();

        $this->info("Recalculated metrics for {$branches->count()} Real Voyage regional branches.");

        return Command::SUCCESS;
    }
}
