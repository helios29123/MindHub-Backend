<?php

namespace App\Services\Admin;

use App\Models\AdminAuditLog;
use App\Models\AdminNotification;
use App\Models\User;
use App\Repositories\Admin\AdminNotificationRepository;

final class AdminNotificationService
{
    public function __construct(private readonly AdminNotificationRepository $repo) {}
    public function paginate(array $filters)
    {
        return $this->repo->paginate($filters);
    }
    public function show(AdminNotification $notification): AdminNotification
    {
        return $notification;
    }
    public function markRead(AdminNotification $notification): AdminNotification
    {
        $notification->update(['is_read' => true, 'read_at' => now()]);
        return $notification->fresh();
    }
    public function markAllRead(): array
    {
        return ['updated' => AdminNotification::query()->where('is_read', false)->update(['is_read' => true, 'read_at' => now()])];
    }
    public function auditLogs(array $filters)
    {
        return $this->repo->auditLogs($filters);
    }
    public function audit(User $admin, string $action, object $target, array $old = [], array $new = []): void
    {
        AdminAuditLog::query()->create(['admin_id' => $admin->id, 'action' => $action, 'target_type' => get_class($target), 'target_id' => $target->id ?? null, 'old_values' => $old, 'new_values' => $new, 'ip_address' => request()?->ip(), 'user_agent' => request()?->userAgent()]);
    }
}
