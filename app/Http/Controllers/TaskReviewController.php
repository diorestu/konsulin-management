<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectProgressUpdate;
use App\Models\ProjectTask;
use App\Models\TaskChecklist;
use App\Models\TaskReview;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskReviewController extends Controller
{
    /**
     * Submit a task for formal QA review (Staff or Reviewer/Admin).
     */
    public function submitForReview(Request $request, Project $project, ProjectTask $task): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if (! $task->canBeUpdatedBy($user)) {
            abort(403, 'Akses ditolak. Anda hanya dapat mengajukan tugas yang ditugaskan kepada Anda.');
        }

        // Ensure default checklists exist for the task
        $task->populateDefaultChecklists();

        $task->update([
            'status' => 'in_review',
            'review_status' => 'pending',
            'progress_percent' => max(80, (int) $task->progress_percent),
        ]);

        // Automatically log a progress update
        ProjectProgressUpdate::create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'project_task_id' => $task->id,
            'progress_percent' => $task->progress_percent,
            'summary' => 'Tugas "' . $task->title . '" diajukan ke Reviewer untuk verifikasi kertas kerja & kepatuhan.',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tugas berhasil diajukan untuk review kendali mutu.',
                'task' => $this->formatTaskData($task->fresh(['checklists', 'reviews.reviewer', 'reviewer', 'assignee'])),
                'project_progress' => $project->fresh()->progressPercent(),
            ]);
        }

        return back()->with('status', 'Tugas berhasil diajukan untuk review.');
    }

    /**
     * Reviewer/Admin approves or requests revision on a task.
     */
    public function review(Request $request, Project $project, ProjectTask $task): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if (! $task->canBeReviewedBy($user)) {
            abort(403, 'Hanya akun Reviewer dan Admin yang berwenang melakukan verifikasi kendali mutu.');
        }

        $validated = $request->validate([
            'action' => ['required', 'string', 'in:approved,revision_requested'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $action = $validated['action'];
        $notes = $validated['notes'] ?? null;

        if ($action === 'revision_requested' && empty(trim((string) $notes))) {
            return response()->json([
                'success' => false,
                'message' => 'Catatan revisi wajib diisi agar staf mengetahui aspek yang perlu diperbaiki.',
            ], 422);
        }

        $now = Carbon::now();

        // Create review audit log
        TaskReview::create([
            'project_task_id' => $task->id,
            'reviewer_id' => $user->id,
            'action' => $action,
            'notes' => $notes,
        ]);

        if ($action === 'approved') {
            $task->update([
                'status' => 'completed',
                'review_status' => 'approved',
                'reviewed_by' => $user->id,
                'reviewed_at' => $now,
                'progress_percent' => 100,
                'review_notes' => $notes,
            ]);

            ProjectProgressUpdate::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'project_task_id' => $task->id,
                'progress_percent' => 100,
                'summary' => 'Reviewer ' . $user->name . ' telah menyetujui output tugas: ' . ($notes ?: 'Seluruh kertas kerja & checklist terverifikasi valid.'),
            ]);

            $message = 'Tugas berhasil disetujui dan berstatus Selesai (Completed).';
        } else {
            // Revision requested -> send back to in_progress
            $task->update([
                'status' => 'in_progress',
                'review_status' => 'revision_requested',
                'reviewed_by' => $user->id,
                'reviewed_at' => $now,
                'review_notes' => $notes,
            ]);

            ProjectProgressUpdate::create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'project_task_id' => $task->id,
                'progress_percent' => $task->progress_percent,
                'summary' => 'Reviewer ' . $user->name . ' meminta revisi: ' . $notes,
            ]);

            $message = 'Permintaan revisi berhasil dikirim ke staf terkait.';
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'action' => $action,
                'task' => $this->formatTaskData($task->fresh(['checklists', 'reviews.reviewer', 'reviewer', 'assignee'])),
                'project_progress' => $project->fresh()->progressPercent(),
            ]);
        }

        return back()->with('status', $message);
    }

    /**
     * Toggle checklist item state.
     */
    public function toggleChecklist(Request $request, Project $project, ProjectTask $task, TaskChecklist $checklist): JsonResponse
    {
        $user = $request->user();

        if (! $task->canBeUpdatedBy($user)) {
            abort(403, 'Akses ditolak.');
        }

        $newChecked = $checklist->toggle($user);

        return response()->json([
            'success' => true,
            'is_checked' => $newChecked,
            'checked_by_name' => $newChecked ? ($user->name ?? 'User') : null,
            'checked_at_human' => $newChecked ? Carbon::now()->format('d M H:i') : null,
            'completed_count' => $task->checklists()->where('is_checked', true)->count(),
            'total_count' => $task->checklists()->count(),
        ]);
    }

    /**
     * Add a custom checklist item to a task.
     */
    public function addChecklist(Request $request, Project $project, ProjectTask $task): JsonResponse
    {
        $user = $request->user();

        if (! $task->canBeUpdatedBy($user)) {
            abort(403, 'Akses ditolak.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $maxOrder = $task->checklists()->max('order') ?? 0;
        $checklist = $task->checklists()->create([
            'title' => $validated['title'],
            'is_checked' => false,
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item checklist berhasil ditambahkan.',
            'checklist' => [
                'id' => $checklist->id,
                'title' => $checklist->title,
                'is_checked' => false,
                'order' => $checklist->order,
            ],
            'completed_count' => $task->checklists()->where('is_checked', true)->count(),
            'total_count' => $task->checklists()->count(),
        ]);
    }

    /**
     * Get review details and checklist data for modal preview.
     */
    public function getReviewData(Request $request, Project $project, ProjectTask $task): JsonResponse
    {
        // Populate defaults if none exist yet
        $task->populateDefaultChecklists();

        $task->load([
            'project.client',
            'assignee',
            'reviewer',
            'checklists.checker',
            'reviews.reviewer',
        ]);

        return response()->json([
            'success' => true,
            'task' => $this->formatTaskData($task),
            'user' => [
                'id' => auth()->id(),
                'role' => auth()->user()?->role_display_name,
                'can_review' => $task->canBeReviewedBy(auth()->user()),
                'can_update' => $task->canBeUpdatedBy(auth()->user()),
            ],
        ]);
    }

    /**
     * Helper to format task payload for frontend JSON response.
     */
    protected function formatTaskData(ProjectTask $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'status' => $task->status,
            'review_status' => $task->review_status,
            'review_notes' => $task->review_notes,
            'progress_percent' => $task->progress_percent,
            'due_date' => $task->due_date ? $task->due_date->format('d M Y') : '-',
            'reviewed_at' => $task->reviewed_at ? $task->reviewed_at->format('d M Y, H:i') : null,
            'reviewer_name' => $task->reviewer?->name ?? null,
            'assignee_name' => $task->assignee?->name ?? 'Unassigned',
            'assignee_initial' => substr($task->assignee?->name ?? 'U', 0, 1),
            'notes' => $task->notes,
            'checklists' => $task->checklists->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'is_checked' => (bool) $item->is_checked,
                    'checked_by_name' => $item->checker?->name,
                    'checked_at' => $item->checked_at ? $item->checked_at->format('d M H:i') : null,
                ];
            }),
            'checklists_summary' => [
                'completed' => $task->checklists->where('is_checked', true)->count(),
                'total' => $task->checklists->count(),
            ],
            'reviews_history' => $task->reviews->map(function ($rev) {
                return [
                    'id' => $rev->id,
                    'action' => $rev->action,
                    'action_label' => $rev->action === 'approved' ? 'Disetujui (Approved)' : 'Minta Revisi',
                    'notes' => $rev->notes,
                    'reviewer_name' => $rev->reviewer?->name ?? 'Reviewer',
                    'created_at' => $rev->created_at->format('d M Y, H:i'),
                ];
            }),
        ];
    }
}
