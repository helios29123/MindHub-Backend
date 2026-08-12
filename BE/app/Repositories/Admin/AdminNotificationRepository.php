<?php

namespace App\Repositories\Admin;

use App\Models\AdminAuditLog;
use App\Models\AdminNotification;

final class AdminNotificationRepository
{
    public function paginate(array $filters)
    {
        $q = AdminNotification::query()->latest();
        foreach (['type', 'severity'] as $f) {
            if (!empty($filters[$f])) $q->where($f, $filters[$f]);
        }
        if (isset($filters['is_read'])) $q->where('is_read', (bool)$filters['is_read']);
        return $q->paginate($filters['per_page'] ?? 15);
    }
    public function auditLogs(array $filters)
    {
        return AdminAuditLog::query()->with('admin')->latest()->paginate($filters['per_page'] ?? 15);
    }
}
