<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class PayoutServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Services\Payout\Contracts\PayoutGatewayInterface::class, function ($app) {
            $driver = config('payout.driver', 'manual');
            
            if ($driver === 'manual') {
                return new \App\Services\Payout\Gateways\ManualPayoutGateway();
            }

            if ($driver === 'fake') {
                return new \App\Services\Payout\Gateways\FakePayoutGateway();
            }

            // Fallback for future drivers
            throw new \Exception("Unsupported payout driver: {$driver}");
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
