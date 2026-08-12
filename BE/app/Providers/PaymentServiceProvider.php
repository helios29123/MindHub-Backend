<?php

namespace App\Providers;

use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\Gateways\SePayGateway;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, SePayGateway::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
