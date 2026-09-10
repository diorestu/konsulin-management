<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectThreatController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'project_task_id' => ['nullable', 'exists:project_tasks,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'severity' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string'],
            'mitigation_plan' => ['nullable', 'string'],
        ]);

        // Security: auto-assign authenticated user unless boss overrides
        $validated['user_id'] = (auth()->check() && (!auth()->user()->isBoss() || empty($validated['user_id'])))
            ? auth()->id()
            : ($validated['user_id'] ?? auth()->id());

        $project->threats()->create($validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Threat logged.');
    }
}
