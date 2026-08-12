<?php

namespace App\Console\Commands;

use App\Services\Payment\RevenueShareService;
use Illuminate\Console\Command;

class ReleaseAvailableRevenuesCommand extends Command
{
    protected $signature = 'revenues:release-available';
    protected $description = 'Release mature pending revenues to available status after refund hold period';

    public function handle(RevenueShareService $revenueService): int
    {
        $this->info('Releasing mature pending revenues...');
        $count = $revenueService->releaseAvailableRevenues();
        $this->info("Successfully released {$count} mature revenues.");

        return Command::SUCCESS;
    }
}
