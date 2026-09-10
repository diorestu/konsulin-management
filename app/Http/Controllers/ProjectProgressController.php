<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectProgressController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'project_task_id' => ['nullable', 'exists:project_tasks,id'],
            'user_id' => ['required', 'exists:users,id'],
            'progress_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'summary' => ['required', 'string'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
        ]);

        $update = $project->progressUpdates()->create($validated);

        if ($update->task) {
            $update->task->update([
                'progress_percent' => $validated['progress_percent'],
                'status' => $validated['progress_percent'] >= 100 ? 'completed' : 'in_progress',
            ]);
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Progress uploaded.');
    }
}
