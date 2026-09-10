<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectProgressUpdate extends Model
{
    protected $fillable = [
        'project_id',
        'project_task_id',
        'user_id',
        'progress_percent',
        'summary',
        'attachment_path',
    ];

    protected function casts(): array
    {
        return [
            'progress_percent' => 'integer',
        ];
    }

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
