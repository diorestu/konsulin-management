<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskChecklist extends Model
{
    protected $fillable = [
        'project_task_id',
        'title',
        'is_checked',
        'checked_by',
        'checked_at',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_checked' => 'boolean',
            'checked_at' => 'datetime',
            'order' => 'integer',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function toggle(?User $user = null): bool
    {
        $newStatus = ! $this->is_checked;

        $this->update([
            'is_checked' => $newStatus,
            'checked_by' => $newStatus ? ($user?->id ?? auth()->id()) : null,
            'checked_at' => $newStatus ? Carbon::now() : null,
        ]);

        return $newStatus;
    }
}
