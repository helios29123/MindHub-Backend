<?php

namespace App\Services\Payment;

use App\Repositories\Payment\OrderRepository;
use Illuminate\Support\Facades\DB;

class OrderExpirationService
{
    public function __construct(
        private readonly OrderRepository $orderRepository
    ) {
    }

    public function expirePendingOrders(int $hours, bool $dryRun = false): array
    {
        $expiredBefore = now();

        if ($dryRun) {
            return [
                'expired_count' => $this->orderRepository->countPendingExpiredOrders($expiredBefore),
                'hours' => $hours,
                'expired_before' => $expiredBefore->toDateTimeString(),
                'dry_run' => true,
            ];
        }

        $expiredCount = DB::transaction(function () use ($expiredBefore): int {
            return $this->orderRepository->expirePendingOrders($expiredBefore);
        });

        return [
            'expired_count' => $expiredCount,
            'hours' => $hours,
            'expired_before' => $expiredBefore->toDateTimeString(),
            'dry_run' => false,
        ];
    }
}