<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $isLocked = (bool) $this->locked || $this->status === 'locked';
        $effectiveStatus = $isLocked 
            ? 'locked' 
            : (($this->status === 'inactive' || empty($this->last_login_at)) ? 'inactive' : 'active');

        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'status' => $this->status,
            'effective_status' => $effectiveStatus,
            'email_verified_at' => $this->email_verified_at,
            'last_login_at' => $this->last_login_at,
            'locked' => $isLocked,
            'locked_reason' => $this->locked_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'instructor_profile' => $this->instructorProfile ? [
                'bio' => $this->instructorProfile->bio,
                'expertise' => $this->instructorProfile->expertise,
                'experience_years' => $this->instructorProfile->experience_years,
                'instructor_rank' => $this->instructorProfile->instructor_rank,
            ] : null,
            'has_pending_upgrade' => (($this->role === 'instructor' && $this->status === 'inactive') || ($this->role === 'learner' && $this->instructorProfile !== null)),
        ];
    }
}
