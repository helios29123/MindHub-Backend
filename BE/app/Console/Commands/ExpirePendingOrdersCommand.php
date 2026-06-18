<?php

namespace App\Console\Commands;

use App\Services\Payment\OrderExpirationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExpirePendingOrdersCommand extends Command
{
    protected $signature = 'orders:expire-pending
        {--hours= : So gio qua han cua don pending, mac dinh lay tu config mindhub.pending_order_expire_hours}
        {--dry-run : Chi dem so don hang se het han, khong update DB}';

    protected $description = 'Expire pending orders older than configured hours';

    public function __construct(
        private readonly OrderExpirationService $orderExpirationService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $hours = $this->resolveHours();

        if ($hours === null) {
            return self::INVALID;
        }

        try {
            $result = $this->orderExpirationService->expirePendingOrders(
                $hours,
                (bool) $this->option('dry-run')
            );

            $message = $this->buildSuccessMessage($result);

            $this->line(json_encode([
                'success' => true,
                'message' => $message,
                'data' => [
                    'expired_count' => $result['expired_count'],
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('Expire pending orders failed.', [
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            $this->line(json_encode([
                'success' => false,
                'message' => 'Không thể cập nhật đơn hàng quá hạn.',
                'errors' => [],
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            return self::FAILURE;
        }
    }

    private function resolveHours(): ?int
    {
        $hoursOption = $this->option('hours');

        if ($hoursOption === null || $hoursOption === '') {
            $hoursOption = config('mindhub.pending_order_expire_hours', 24);
        }

        if (! is_numeric($hoursOption) || (string) (int) $hoursOption !== (string) $hoursOption) {
            $this->error('Option --hours phải là số nguyên từ 1 đến 168.');

            return null;
        }

        $hours = (int) $hoursOption;

        if ($hours < 1 || $hours > 168) {
            $this->error('Option --hours phải nằm trong khoảng 1 đến 168.');

            return null;
        }

        return $hours;
    }

    private function buildSuccessMessage(array $result): string
    {
        if ($result['dry_run'] === true) {
            return 'Dry run expire pending orders completed.';
        }

        if ($result['expired_count'] === 0) {
            return 'Không có đơn hàng cần hết hạn.';
        }

        return 'Expire pending orders completed.';
    }
}