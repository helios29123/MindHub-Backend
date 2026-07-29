<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class AdminAuditLog extends Model
{
    protected $fillable = ['admin_id', 'action', 'target_type', 'target_id', 'old_values', 'new_values', 'ip_address', 'user_agent'];
    protected $casts = ['old_values' => 'array', 'new_values' => 'array'];
}
