<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class AdminNotification extends Model
{
    protected $fillable = ['type', 'title', 'message', 'severity', 'related_type', 'related_id', 'data', 'is_read', 'read_at'];
    protected $casts = ['data' => 'array', 'is_read' => 'boolean', 'read_at' => 'datetime'];
}
