<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
    protected $fillable = [
        'project_id',
        'assigned_to',
        'title',
        'status',
        'progress_percent',
        'due_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'progress_percent' => 'integer',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function progressUpdates()
    {
        return $this->hasMany(ProjectProgressUpdate::class);
    }

    public function threats()
    {
        return $this->hasMany(ProjectThreat::class);
    }

    public function timeLogs()
    {
        return $this->hasMany(TaskTimeLog::class);
    }

    public function totalDurationSeconds(): int
    {
        return (int) $this->timeLogs()->where('status', 'completed')->sum('duration_seconds');
    }

    public function activeTimeLogFor(?int $userId = null)
    {
        $query = $this->timeLogs()->where('status', 'running')->whereNull('stopped_at');
        if ($userId) {
            $query->where('user_id', $userId);
        }
        return $query->latest('started_at')->first();
    }

    public function canBeUpdatedBy(?User $user = null): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isAdmin() || $user->isReviewer()) {
            return true;
        }

        // Staff cannot update tasks assigned to other users
        if (! is_null($this->assigned_to) && (int) $this->assigned_to !== (int) $user->id) {
            return false;
        }

        return true;
    }
}
