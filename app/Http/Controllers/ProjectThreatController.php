<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectThreatController extends Controller
{
    public function store(Request $request, Project $project): JsonResponse|RedirectResponse
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

        $threat = $project->threats()->create($validated);
        $threat->load(['user', 'task']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Threat "' . $threat->title . '" berhasil dicatat.',
                'threat' => [
                    'id' => $threat->id,
                    'user_name' => $threat->user->name,
                    'task_title' => $threat->task?->title ?? 'Project threat',
                    'title' => $threat->title,
                    'severity' => $threat->severity,
                    'status' => $threat->status,
                    'description' => $threat->description,
                    'mitigation_plan' => $threat->mitigation_plan,
                    'created_human' => $threat->created_at->diffForHumans(),
                ],
                'open_threats' => $project->fresh()->threats()->where('status', 'open')->count(),
            ]);
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Threat logged.');
    }
}
