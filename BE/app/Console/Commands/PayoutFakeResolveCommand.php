<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PayoutFakeResolveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payout:fake-resolve {id} {status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resolve a PROCESSING payout to SUCCESS or FAILED for local testing';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\Payout\PayoutService $payoutService)
    {
        $id = $this->argument('id');
        $status = strtolower($this->argument('status'));

        if (!in_array($status, ['success', 'failed'])) {
            $this->error("Status must be 'success' or 'failed'.");
            return;
        }

        $withdrawal = \App\Models\WithdrawRequest::find($id);

        if (!$withdrawal) {
            $this->error("Withdrawal #{$id} not found.");
            return;
        }

        if ($withdrawal->status !== \App\Models\WithdrawRequest::STATUS_PROCESSING) {
            $this->error("Withdrawal #{$id} is not in PROCESSING state (current: {$withdrawal->status}).");
            return;
        }

        $payoutService->resolveWebhook($withdrawal, $status, 'Resolved by CLI command');
        
        $this->info("Successfully resolved Withdrawal #{$id} to {$status}.");
    }
}
