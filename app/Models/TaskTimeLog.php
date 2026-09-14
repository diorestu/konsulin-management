<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskTimeLog extends Model
{
    protected $fillable = [
        'project_task_id',
        'user_id',
        'started_at',
        'stopped_at',
        'duration_seconds',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'stopped_at' => 'datetime',
            'duration_seconds' => 'integer',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId)
            ->where('status', 'running')
            ->whereNull('stopped_at');
    }

    public function getElapsedSecondsAttribute(): int
    {
        if ($this->stopped_at) {
            return $this->duration_seconds ?? 0;
        }

        return max(0, Carbon::now()->diffInSeconds($this->started_at));
    }

    public function stop(?string $notes = null): self
    {
        $now = Carbon::now();
        $duration = max(1, $now->diffInSeconds($this->started_at));

        $this->update([
            'stopped_at' => $now,
            'duration_seconds' => $duration,
            'status' => 'completed',
            'notes' => $notes ?? $this->notes,
        ]);

        return $this;
    }
}
