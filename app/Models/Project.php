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

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function staff()
    {
        return $this->belongsToMany(Staff::class)->withTimestamps();
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
}
