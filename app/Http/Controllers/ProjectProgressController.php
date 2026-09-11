<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectProgressController extends Controller
{
    public function store(Request $request, Project $project): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'project_task_id' => ['nullable', 'exists:project_tasks,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'progress_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'summary' => ['required', 'string'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
        ]);

        // Security: auto-assign authenticated user unless boss overrides
        $validated['user_id'] = (auth()->check() && (!auth()->user()->isBoss() || empty($validated['user_id'])))
            ? auth()->id()
            : ($validated['user_id'] ?? auth()->id());

        $update = $project->progressUpdates()->create($validated);

        if ($update->task) {
            $update->task->update([
                'progress_percent' => $validated['progress_percent'],
                'status' => $validated['progress_percent'] >= 100 ? 'completed' : 'in_progress',
            ]);
        }

        $update->load(['user', 'task']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Progress berhasil diunggah (' . $update->progress_percent . '%).',
                'progress' => [
                    'id' => $update->id,
                    'user_name' => $update->user->name,
                    'progress_percent' => $update->progress_percent,
                    'task_title' => $update->task?->title ?? 'Project update',
                    'task_id' => $update->project_task_id,
                    'created_at' => $update->created_at->format('d M Y H:i'),
                    'summary' => $update->summary,
                    'attachment_path' => $update->attachment_path,
                ],
                'project_progress' => $project->fresh()->progressPercent(),
                'completed_tasks' => $project->tasks()->where('status', 'completed')->count(),
                'in_progress_tasks' => $project->tasks()->where('status', 'in_progress')->count(),
            ]);
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Progress uploaded.');
    }
}
