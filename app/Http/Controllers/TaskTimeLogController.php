<?php

namespace App\Http\Controllers;

use App\Models\ProjectTask;
use App\Models\TaskTimeLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskTimeLogController extends Controller
{
    /**
     * Get currently running active time log for authenticated user.
     */
    public function active(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $user->isStaff()) {
            return response()->json([
                'active' => false,
                'log' => null,
            ]);
        }

        $activeLog = TaskTimeLog::with(['task.project.client'])
            ->where('user_id', $user->id)
            ->where('status', 'running')
            ->whereNull('stopped_at')
            ->latest('started_at')
            ->first();

        if (! $activeLog || ! $activeLog->task) {
            return response()->json([
                'active' => false,
                'log' => null,
            ]);
        }

        $task = $activeLog->task;
        $project = $task->project;
        $client = $project?->client;
        $elapsedSeconds = max(0, Carbon::now()->diffInSeconds($activeLog->started_at));

        return response()->json([
            'active' => true,
            'log' => [
                'id' => $activeLog->id,
                'started_at' => $activeLog->started_at->toISOString(),
                'started_at_human' => $activeLog->started_at->format('H:i:s'),
                'elapsed_seconds' => $elapsedSeconds,
                'notes' => $activeLog->notes,
            ],
            'task' => [
                'id' => $task->id,
                'key' => 'TSK-' . $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'progress_percent' => $task->progress_percent,
            ],
            'project' => $project ? [
                'id' => $project->id,
                'key' => 'PRJ-' . str_pad($project->id, 3, '0', STR_PAD_LEFT),
                'name' => $project->name,
            ] : null,
            'client' => $client ? [
                'id' => $client->id,
                'name' => $client->name,
            ] : null,
        ]);
    }

    /**
     * Start tracking work time on a task.
     */
    public function start(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Time tracker hanya berlaku untuk akun staff.',
            ], 403);
        }

        $validated = $request->validate([
            'task_id' => ['required', 'integer', 'exists:project_tasks,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $task = ProjectTask::with(['project.client'])->findOrFail($validated['task_id']);

        if (! $task->canBeUpdatedBy($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya dapat melacak waktu untuk tugas yang ditugaskan kepada Anda.',
            ], 403);
        }

        // Stop any currently active time logs for this user first
        $existingActive = TaskTimeLog::where('user_id', $user->id)
            ->where('status', 'running')
            ->whereNull('stopped_at')
            ->get();

        foreach ($existingActive as $log) {
            $log->stop('Beralih ke task lain');
        }

        // Create new running log
        $now = Carbon::now();
        $timeLog = TaskTimeLog::create([
            'project_task_id' => $task->id,
            'user_id' => $user->id,
            'started_at' => $now,
            'status' => 'running',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Auto advance task status to in_progress if currently not_started
        if ($task->status === 'not_started') {
            $task->update(['status' => 'in_progress']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Waktu kerja berhasil dimulai untuk task: ' . $task->title,
            'log' => [
                'id' => $timeLog->id,
                'started_at' => $timeLog->started_at->toISOString(),
                'started_at_human' => $timeLog->started_at->format('H:i:s'),
                'elapsed_seconds' => 0,
            ],
            'task' => [
                'id' => $task->id,
                'key' => 'TSK-' . $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'progress_percent' => $task->progress_percent,
            ],
            'project' => $task->project ? [
                'id' => $task->project->id,
                'key' => 'PRJ-' . str_pad($task->project->id, 3, '0', STR_PAD_LEFT),
                'name' => $task->project->name,
            ] : null,
            'client' => $task->project?->client ? [
                'id' => $task->project->client->id,
                'name' => $task->project->client->name,
            ] : null,
        ]);
    }

    /**
     * Stop tracking work time for currently active task.
     */
    public function stop(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $user->isStaff()) {
            return response()->json([
                'success' => false,
                'message' => 'Time tracker hanya berlaku untuk akun staff.',
            ], 403);
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'mark_completed' => ['nullable', 'boolean'],
        ]);

        $activeLog = TaskTimeLog::with(['task.project'])
            ->where('user_id', $user->id)
            ->where('status', 'running')
            ->whereNull('stopped_at')
            ->latest('started_at')
            ->first();

        if (! $activeLog) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada sesi waktu kerja aktif.',
            ], 404);
        }

        $activeLog->stop($validated['notes'] ?? null);
        $task = $activeLog->task;

        if ($task) {
            $taskUpdates = [];
            if ($request->boolean('mark_completed')) {
                $taskUpdates['status'] = 'completed';
                $taskUpdates['progress_percent'] = 100;
            } elseif (isset($validated['progress_percent'])) {
                $taskUpdates['progress_percent'] = $validated['progress_percent'];
            }

            if (! empty($taskUpdates)) {
                $task->update($taskUpdates);
            }
        }

        $durationMins = round($activeLog->duration_seconds / 60, 1);

        return response()->json([
            'success' => true,
            'message' => 'Waktu kerja berhasil dicatat (' . $durationMins . ' menit).',
            'duration_seconds' => $activeLog->duration_seconds,
            'duration_human' => gmdate('H:i:s', $activeLog->duration_seconds),
            'task' => $task ? [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'progress_percent' => $task->progress_percent,
            ] : null,
        ]);
    }

    /**
     * Get list of user's active/pending tasks for quick selection in desktop or widget.
     */
    public function myTasks(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $user->isStaff()) {
            return response()->json([
                'tasks' => [],
            ]);
        }

        $tasks = ProjectTask::with(['project.client'])
            ->where('assigned_to', $user->id)
            ->where('status', '!=', 'completed')
            ->orderByRaw("CASE WHEN status = 'in_progress' THEN 1 WHEN status = 'not_started' THEN 2 ELSE 3 END")
            ->orderBy('id', 'desc')
            ->take(30)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'key' => 'TSK-' . $t->id,
                    'title' => $t->title,
                    'status' => $t->status,
                    'project_name' => $t->project?->name ?? 'Unassigned Project',
                    'client_name' => $t->project?->client?->name ?? 'Internal',
                    'progress_percent' => $t->progress_percent,
                    'total_duration_seconds' => $t->totalDurationSeconds(),
                ];
            });

        return response()->json([
            'tasks' => $tasks,
        ]);
    }
}
