<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dispute;
use Illuminate\Support\Facades\Log;

class EscalateDisputes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transport:escalate-disputes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically escalate unresolved disputes to platform administrators after 48 hours';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cutoff = now()->subHours(48);

        $disputesToEscalate = Dispute::where('status', 'open')
            ->where('escalated', false)
            ->where('created_at', '<=', $cutoff)
            ->get();

        $count = $disputesToEscalate->count();

        foreach ($disputesToEscalate as $dispute) {
            $dispute->update([
                'escalated' => true,
                'escalated_at' => now(),
            ]);
        }

        $this->info("Escalated {$count} disputes older than 48 hours to Administrator.");
        Log::info("transport:escalate-disputes auto-escalated {$count} disputes.");

        return Command::SUCCESS;
    }
}
