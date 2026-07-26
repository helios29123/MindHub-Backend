<?php

namespace App\Console\Commands;

use App\Services\Payout\InstructorPayoutService;
use Illuminate\Console\Command;

class ProcessReadyPayoutsCommand extends Command
{
    protected $signature = 'payouts:process-ready';
    protected $description = 'Process ready_to_pay payout statements to paid status';

    public function handle(InstructorPayoutService $payoutService): int
    {
        $this->info('Processing ready_to_pay payouts...');
        $count = $payoutService->processReadyPayouts();
        $this->info("Successfully processed {$count} payouts to paid status.");

        return Command::SUCCESS;
    }
}
