<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id',
        'project_category_id',
        'created_by',
        'reviewer_id',
        'name',
        'service_type',
        'status',
        'priority',
        'start_date',
        'due_date',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function category()
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id')->withDefault(function () {
            return $this->creator;
        });
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function staff()
    {
        return $this->belongsToMany(Staff::class)->withPivot('role')->withTimestamps();
    }

    public function accountingStaff()
    {
        return $this->belongsToMany(Staff::class)
            ->where(function ($query) {
                $query->where('project_staff.role', 'pic_accounting')
                    ->orWhere(function ($q) {
                        $q->whereNull('project_staff.role')->where('staff.type', 'accounting');
                    });
            })
            ->withPivot('role')
            ->withTimestamps();
    }

    public function taxStaff()
    {
        return $this->belongsToMany(Staff::class)
            ->where(function ($query) {
                $query->where('project_staff.role', 'pic_tax')
                    ->orWhere(function ($q) {
                        $q->whereNull('project_staff.role')->where('staff.type', 'tax');
                    });
            })
            ->withPivot('role')
            ->withTimestamps();
    }

    public function progressUpdates()
    {
        return $this->hasMany(ProjectProgressUpdate::class)->latest();
    }

    public function threats()
    {
        return $this->hasMany(ProjectThreat::class)->latest();
    }

    public function progressPercent(): int
    {
        if ($this->tasks->isEmpty()) {
            return 0;
        }

        return (int) round($this->tasks->avg('progress_percent'));
    }

    public function scopeVisibleTo($query, ?User $user = null)
    {
        if (! $user) {
            return $query;
        }

        if ($user->isAdmin() || $user->isReviewer()) {
            return $query;
        }

        // Staff only sees projects assigned to them
        return $query->where(function ($q) use ($user) {
            $q->whereHas('tasks', function ($taskQuery) use ($user) {
                $taskQuery->where('assigned_to', $user->id);
            })->orWhereHas('staff', function ($staffQuery) use ($user) {
                $staffQuery->where('staff.email', $user->email);
            });
        });
    }

    public function isAssignedTo(?User $user = null): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isAdmin() || $user->isReviewer()) {
            return true;
        }

        return $this->tasks()->where('assigned_to', $user->id)->exists()
            || $this->staff()->where('staff.email', $user->email)->exists();
    }

    public function totalLoggedSeconds(): int
    {
        return (int) TaskTimeLog::whereHas('task', function ($q) {
            $q->where('project_id', $this->id);
        })->where('status', 'completed')->sum('duration_seconds');
    }

    public function formattedTotalLoggedTime(): string
    {
        $totalSec = $this->totalLoggedSeconds();
        if ($totalSec === 0) {
            return '0 jam';
        }
        $hours = floor($totalSec / 3600);
        $mins = floor(($totalSec % 3600) / 60);
        if ($hours > 0 && $mins > 0) {
            return "{$hours}j {$mins}m";
        } elseif ($hours > 0) {
            return "{$hours} jam";
        }
        return "{$mins} menit";
    }
}
