<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectThreat extends Model
{
    protected $fillable = [
        'project_id',
        'project_task_id',
        'user_id',
        'title',
        'severity',
        'status',
        'description',
        'mitigation_plan',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
