<?php

namespace App\Console\Commands;

use App\Services\Payout\InstructorPayoutService;
use Illuminate\Console\Command;

class ReconcilePayoutsCommand extends Command
{
    protected $signature = 'payouts:reconcile {--instructor=}';
    protected $description = 'Audit and reconcile payout statements against revenue line items';

    public function handle(InstructorPayoutService $payoutService): int
    {
        $instructorId = $this->option('instructor') ? (int) $this->option('instructor') : null;
        $result = $payoutService->reconcilePayouts($instructorId);

        $this->info("Reconciliation complete. Checked {$result['total_checked']} payouts.");
        if ($result['discrepancies_found'] > 0) {
            $this->error("Found {$result['discrepancies_found']} discrepancies!");
            foreach ($result['details'] as $detail) {
                $this->line("Payout #{$detail['payout_id']} (Instructor #{$detail['instructor_id']}): Statement Amount {$detail['payout_amount']} vs Revenue Sum {$detail['revenue_sum']}");
            }
            return Command::FAILURE;
        }

        $this->info('All payout statements reconciled cleanly with revenue items.');
        return Command::SUCCESS;
    }
}
