<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditRecorder
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function record(string $action, ?Model $subject = null, array $properties = [], ?int $departmentId = null): void
    {
        $departmentId ??= $subject->department_id ?? auth()->user()?->department_id;

        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'department_id' => $departmentId,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);
    }
}
