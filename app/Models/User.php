<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->role === 'admin' || $this->hasRole('boss') || $this->role === 'boss';
    }

    public function isReviewer(): bool
    {
        return $this->hasRole('reviewer') || $this->role === 'reviewer';
    }

    public function isStaff(): bool
    {
        if ($this->isAdmin() || $this->isReviewer()) {
            return false;
        }

        return true;
    }

    public function isBoss(): bool
    {
        return $this->isAdmin();
    }

    public function getRoleDisplayNameAttribute(): string
    {
        if ($this->isAdmin()) {
            return 'Admin';
        }
        if ($this->isReviewer()) {
            return 'Reviewer';
        }
        return 'Staff';
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));
        $initials = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $initials .= strtoupper(mb_substr($w, 0, 1));
        }
        return $initials ?: 'U';
    }

    public function assignedTasks()
    {
        return $this->hasMany(ProjectTask::class, 'assigned_to');
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

    public function activeTimeLog()
    {
        return $this->hasOne(TaskTimeLog::class)
            ->where('status', 'running')
            ->whereNull('stopped_at')
            ->latest('started_at');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
