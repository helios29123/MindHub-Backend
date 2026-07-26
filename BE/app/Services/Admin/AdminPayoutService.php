<?php

namespace App\Services\Admin;

use App\Models\PayoutBatch;
use App\Models\PayoutItem;
use App\Models\Revenue;
use App\Models\User;
use App\Repositories\Admin\AdminPayoutRepository;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class AdminPayoutService
{
    public function __construct(private readonly AdminPayoutRepository $repo, private readonly AdminNotificationService $notifications) {}
    public function paginate(array $filters)
    {
        return $this->repo->paginate($filters);
    }
    public function createBatch(array $data, User $admin): PayoutBatch
    {
        return DB::transaction(function () use ($data, $admin) {
            $month = (int)$data['period_month'];
            $year = (int)$data['period_year'];
            $periodStart = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
            $periodEnd = date('Y-m-t', strtotime($periodStart));
            $code = 'PAY-' . $year . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . strtoupper(bin2hex(random_bytes(4)));

            $batch = PayoutBatch::query()->firstOrCreate(
                ['period_month' => $month, 'period_year' => $year],
                [
                    'code' => $code,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'status' => 'draft',
                    'created_by' => $admin->id,
                    'note' => $data['note'] ?? null
                ]
            );
            if ($batch->items()->exists()) return $batch->load('items.instructor', 'items.payoutAccount');
            $groups = $this->repo->availableRevenueByInstructor((int)$data['period_month'], (int)$data['period_year']);
            $total = 0;
            foreach ($groups as $g) {
                $item = $batch->items()->create(['instructor_id' => $g->instructor_id, 'gross_amount' => $g->gross_amount, 'instructor_amount' => $g->instructor_amount, 'platform_fee_amount' => $g->platform_fee_amount, 'paid_amount' => $g->instructor_amount, 'status' => 'pending']);
                $revenues = Revenue::query()->where('status', 'available')->where('instructor_id', $g->instructor_id)->whereMonth('earned_at', $data['period_month'])->whereYear('earned_at', $data['period_year'])->get();
                foreach ($revenues as $revenue) {
                    $item->revenues()->attach($revenue->id, ['amount' => $revenue->instructor_amount]);
                    $revenue->update(['status' => 'included_in_payout']);
                }
                $total += (float)$g->instructor_amount;
            }
            $batch->update(['total_amount' => $total, 'total_instructors' => $groups->count()]);
            $this->notifications->audit($admin, 'payout_batch.create', $batch);
            return $batch->fresh(['items.instructor', 'items.payoutAccount']);
        });
    }
    public function showBatch(PayoutBatch $batch): PayoutBatch
    {
        return $batch->load(['items.instructor', 'items.payoutAccount', 'items.revenues.course', 'items.revenues.order']);
    }
    public function lockBatch(PayoutBatch $batch): PayoutBatch
    {
        $batch->update(['status' => 'locked']);
        return $batch->fresh('items');
    }
    public function markItemPaid(PayoutItem $item, array $data, User $admin): PayoutItem
    {
        return DB::transaction(function () use ($item, $data, $admin) {
            $item->update(['status' => 'paid', 'transaction_code' => $data['transaction_code'] ?? $item->transaction_code, 'paid_at' => $data['paid_at'] ?? now(), 'note' => $data['note'] ?? $item->note]);
            foreach ($item->revenues as $revenue) {
                $revenue->update(['status' => 'paid']);
            }
            $this->notifications->audit($admin, 'payout_item.mark_paid', $item);
            return $item->fresh(['instructor', 'payoutAccount', 'revenues']);
        });
    }
    public function holdItem(PayoutItem $item, array $data, User $admin): PayoutItem
    {
        $item->update(['status' => 'on_hold', 'note' => $data['reason'] ?? $item->note]);
        $this->notifications->audit($admin, 'payout_item.hold', $item);
        return $item->fresh(['instructor', 'payoutAccount']);
    }
    public function exportBankList(PayoutBatch $batch): StreamedResponse
    {
        return response()->streamDownload(function () use ($batch) {
            echo "Giang vien,So tien,Trang thai\n";
            foreach ($batch->items()->with('instructor')->get() as $item) {
                echo ($item->instructor->name ?? $item->instructor_id) . ',' . $item->paid_amount . ',' . $item->status . "\n";
            }
        }, 'payout_batch_' . $batch->id . '.csv');
    }
}
