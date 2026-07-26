<?php

namespace App\Console\Commands;

use App\Services\Payout\InstructorPayoutService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlyPayoutsCommand extends Command
{
    protected $signature = 'payouts:generate-monthly {--period=} {--instructor=} {--dry-run}';
    protected $description = 'Generate periodic monthly payout statements for instructors';

    public function handle(InstructorPayoutService $payoutService): int
    {
        $periodInput = $this->option('period');
        $periodEnd = $periodInput ? Carbon::parse($periodInput)->endOfMonth() : now()->endOfMonth();
        $instructorId = $this->option('instructor');

        $this->info("Generating monthly payouts for period ending {$periodEnd->toDateString()}...");

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN MODE ENABLED. No database changes will be committed.');
        }

        if ($instructorId) {
            $payout = $payoutService->generateMonthlyPayout((int) $instructorId, $periodEnd);
            if ($payout) {
                $this->info("Payout generated for Instructor #{$instructorId}: Amount {$payout->amount}, Status {$payout->status}");
            } else {
                $this->info("No eligible revenue for Instructor #{$instructorId}.");
            }
        } else {
            $payouts = $payoutService->generateAllMonthlyPayouts($periodEnd);
            $count = count($payouts);
            $this->info("Successfully generated {$count} monthly payout statements.");
        }

        return Command::SUCCESS;
    }
}
