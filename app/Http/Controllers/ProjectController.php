<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectTask;
use App\Models\ProjectThreat;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $projectsQuery = Project::query()->visibleTo($user);
        $projects = (clone $projectsQuery)
            ->with(['category', 'client', 'staff', 'reviewer', 'tasks.assignee', 'threats'])
            ->latest()
            ->get();

        $allActiveStaff = Staff::where('is_active', true)->orderBy('name')->get();
        $accountingStaffList = $allActiveStaff->where('type', 'accounting');
        if ($accountingStaffList->isEmpty()) {
            $accountingStaffList = $allActiveStaff;
        }
        $taxStaffList = $allActiveStaff->where('type', 'tax');
        if ($taxStaffList->isEmpty()) {
            $taxStaffList = $allActiveStaff;
        }

        return view('projects.index', [
            'projects' => $projects,
            'clients' => Client::orderBy('name')->get(),
            'categories' => ProjectCategory::where('is_active', true)->orderBy('name')->get(),
            'staff' => $allActiveStaff,
            'accountingStaffList' => $accountingStaffList,
            'taxStaffList' => $taxStaffList,
            'reviewers' => User::orderBy('name')->get(),
            'bosses' => User::where('role', 'boss')->orderBy('name')->get(),
            'totalProjectsCount' => (clone $projectsQuery)->count(),
            'clientsCount' => Client::count(),
            'activeProjectsCount' => (clone $projectsQuery)->whereNotIn('status', ['completed', 'cancelled'])->count(),
            'openThreatsCount' => ProjectThreat::where('status', 'open')->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (auth()->check() && auth()->user()->isStaff() && !auth()->user()->can('create projects')) {
            abort(403, 'Hanya admin dan reviewer yang dapat membuat project baru.');
        }
        $validated = $request->validate([
            'client_mode' => ['nullable', 'string', 'in:existing,new'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'project_category_id' => ['nullable', 'exists:project_categories,id'],
            'client_name' => ['required_without:client_id', 'nullable', 'string', 'max:255'],
            'client_pic' => ['nullable', 'string', 'max:255'],
            'client_type' => ['nullable', 'string', 'max:100'],
            'tax_status' => ['nullable', 'string', 'max:100'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:50'],
            'client_tax_id' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'service_type' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', 'max:50'],
            'priority' => ['required', 'string', 'max:50'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
            'reviewer_id' => ['nullable', 'exists:users,id'],
            'created_by' => ['nullable', 'exists:users,id'],
            'accounting_staff_ids' => ['nullable', 'array'],
            'accounting_staff_ids.*' => ['exists:staff,id'],
            'tax_staff_ids' => ['nullable', 'array'],
            'tax_staff_ids.*' => ['exists:staff,id'],
            'staff_ids' => ['nullable', 'array'],
            'staff_ids.*' => ['exists:staff,id'],
        ]);

        $client = !empty($validated['client_id'])
            ? Client::findOrFail($validated['client_id'])
            : Client::create([
                'name' => $validated['client_name'],
                'client_pic' => $validated['client_pic'] ?? null,
                'client_type' => $validated['client_type'] ?? 'Badan',
                'tax_status' => $validated['tax_status'] ?? null,
                'email' => $validated['client_email'] ?? null,
                'phone' => $validated['client_phone'] ?? null,
                'tax_id' => $validated['client_tax_id'] ?? null,
            ]);

        $reviewerId = $validated['reviewer_id'] ?? $validated['created_by'] ?? auth()->id();

        $project = Project::create([
            'client_id' => $client->id,
            'project_category_id' => $validated['project_category_id'] ?? null,
            'created_by' => $validated['created_by'] ?? auth()->id(),
            'reviewer_id' => $reviewerId,
            'name' => $validated['name'],
            'service_type' => $validated['service_type'],
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'start_date' => $validated['start_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        $accountingIds = collect($validated['accounting_staff_ids'] ?? [])->map(fn($id) => (int)$id);
        $taxIds = collect($validated['tax_staff_ids'] ?? [])->map(fn($id) => (int)$id);

        if ($accountingIds->isEmpty() && $taxIds->isEmpty() && !empty($validated['staff_ids'])) {
            $staffMembers = Staff::whereIn('id', $validated['staff_ids'])->get();
            $accountingIds = $staffMembers->where('type', 'accounting')->pluck('id');
            $taxIds = $staffMembers->where('type', 'tax')->pluck('id');
            if ($accountingIds->isEmpty() && $taxIds->isEmpty()) {
                $accountingIds = collect($validated['staff_ids']);
            }
        }

        $syncData = [];
        foreach ($accountingIds as $id) {
            $syncData[$id] = ['role' => 'pic_accounting'];
        }
        foreach ($taxIds as $id) {
            $syncData[$id] = ['role' => 'pic_tax'];
        }
        foreach ($validated['staff_ids'] ?? [] as $id) {
            if (!isset($syncData[$id])) {
                $syncData[$id] = ['role' => 'staff'];
            }
        }
        $project->staff()->sync($syncData);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Project created.');
    }

    public function show(Project $project): View
    {
        if (auth()->check() && ! $project->isAssignedTo(auth()->user())) {
            abort(403, 'Akses ditolak. Anda hanya dapat melihat project yang ditugaskan kepada Anda.');
        }

        $project->load([
            'client',
            'category',
            'reviewer',
            'staff',
            'tasks.assignee',
            'tasks.threats',
            'progressUpdates.user',
            'progressUpdates.task',
            'threats.user',
            'threats.task',
        ]);

        return view('projects.show', [
            'project' => $project,
            'employees' => User::where('role', 'employee')->orderBy('name')->get(),
            'staff' => Staff::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        if (auth()->check() && auth()->user()->isStaff() && !auth()->user()->can('edit projects')) {
            abort(403, 'Hanya admin dan reviewer yang dapat mengubah data project.');
        }
        $validated = $request->validate([
            'client_name' => ['required', 'string', 'max:255'],
            'client_pic' => ['nullable', 'string', 'max:255'],
            'client_type' => ['nullable', 'string', 'max:100'],
            'tax_status' => ['nullable', 'string', 'max:100'],
            'project_category_id' => ['nullable', 'exists:project_categories,id'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:50'],
            'client_tax_id' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'service_type' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', 'max:50'],
            'priority' => ['required', 'string', 'max:50'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
            'reviewer_id' => ['nullable', 'exists:users,id'],
            'created_by' => ['nullable', 'exists:users,id'],
            'accounting_staff_ids' => ['nullable', 'array'],
            'accounting_staff_ids.*' => ['exists:staff,id'],
            'tax_staff_ids' => ['nullable', 'array'],
            'tax_staff_ids.*' => ['exists:staff,id'],
            'staff_ids' => ['nullable', 'array'],
            'staff_ids.*' => ['exists:staff,id'],
        ]);

        $project->client->update([
            'name' => $validated['client_name'],
            'client_pic' => $validated['client_pic'] ?? $project->client->client_pic,
            'client_type' => $validated['client_type'] ?? $project->client->client_type,
            'tax_status' => $validated['tax_status'] ?? $project->client->tax_status,
            'email' => $validated['client_email'] ?? null,
            'phone' => $validated['client_phone'] ?? null,
            'tax_id' => $validated['client_tax_id'] ?? null,
        ]);

        $reviewerId = $validated['reviewer_id'] ?? $validated['created_by'] ?? $project->reviewer_id ?? $project->created_by;

        $project->update([
            'project_category_id' => $validated['project_category_id'] ?? null,
            'created_by' => $validated['created_by'] ?? $project->created_by,
            'reviewer_id' => $reviewerId,
            'name' => $validated['name'],
            'service_type' => $validated['service_type'],
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'start_date' => $validated['start_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        $accountingIds = collect($validated['accounting_staff_ids'] ?? [])->map(fn($id) => (int)$id);
        $taxIds = collect($validated['tax_staff_ids'] ?? [])->map(fn($id) => (int)$id);

        if ($accountingIds->isEmpty() && $taxIds->isEmpty() && isset($validated['staff_ids'])) {
            $staffMembers = Staff::whereIn('id', $validated['staff_ids'])->get();
            $accountingIds = $staffMembers->where('type', 'accounting')->pluck('id');
            $taxIds = $staffMembers->where('type', 'tax')->pluck('id');
            if ($accountingIds->isEmpty() && $taxIds->isEmpty()) {
                $accountingIds = collect($validated['staff_ids']);
            }
        }

        if ($accountingIds->isNotEmpty() || $taxIds->isNotEmpty() || isset($validated['staff_ids'])) {
            $syncData = [];
            foreach ($accountingIds as $id) {
                $syncData[$id] = ['role' => 'pic_accounting'];
            }
            foreach ($taxIds as $id) {
                $syncData[$id] = ['role' => 'pic_tax'];
            }
            foreach ($validated['staff_ids'] ?? [] as $id) {
                if (!isset($syncData[$id])) {
                    $syncData[$id] = ['role' => 'staff'];
                }
            }
            $project->staff()->sync($syncData);
        }

        return redirect()
            ->route('projects.index')
            ->with('status', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        if (auth()->check() && !auth()->user()->can('delete projects') && !auth()->user()->isBoss()) {
            abort(403, 'Hanya pimpinan / admin yang berwenang menghapus project.');
        }

        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('status', 'Project deleted.');
    }

    public function storeTask(Request $request, Project $project): JsonResponse|RedirectResponse
    {
        if (auth()->check() && auth()->user()->isStaff() && !auth()->user()->can('manage tasks')) {
            abort(403, 'Hanya admin dan reviewer yang dapat menambahkan tugas baru.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'string', 'max:50'],
            'progress_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $task = $project->tasks()->create($validated);
        $task->load('assignee');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Task "' . $task->title . '" berhasil ditambahkan.',
                'task' => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'status' => $task->status,
                    'progress_percent' => $task->progress_percent,
                    'due_date' => $task->due_date ? $task->due_date->format('d M') : null,
                    'due_date_full' => $task->due_date ? $task->due_date->format('d M Y') : '-',
                    'notes' => $task->notes,
                    'assignee_name' => $task->assignee?->name ?? 'Unassigned',
                    'assignee_initial' => substr($task->assignee?->name ?? 'U', 0, 1),
                ],
                'project_progress' => $project->fresh()->progressPercent(),
                'total_tasks' => $project->tasks()->count(),
                'completed_tasks' => $project->tasks()->where('status', 'completed')->count(),
                'in_progress_tasks' => $project->tasks()->where('status', 'in_progress')->count(),
            ]);
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Task added.');
    }

    public function updateTaskStatus(Request $request, Project $project, ProjectTask $task): JsonResponse|RedirectResponse
    {
        if (auth()->check() && ! $task->canBeUpdatedBy(auth()->user())) {
            abort(403, 'Akses ditolak. Anda hanya dapat memperbarui tugas yang ditugaskan kepada Anda.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:not_started,in_progress,waiting_client,completed'],
        ]);

        $status = $validated['status'];
        $progress = $task->progress_percent;

        if ($status === 'completed') {
            $progress = 100;
        } elseif ($progress >= 100 && $status !== 'completed') {
            $progress = 50;
        }

        $task->update([
            'status' => $status,
            'progress_percent' => $progress,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status task "' . $task->title . '" berhasil diperbarui ke ' . str_replace('_', ' ', $status) . '.',
                'task_id' => $task->id,
                'new_status' => $status,
                'progress_percent' => $progress,
                'project_progress' => $project->fresh()->progressPercent(),
                'completed_tasks' => $project->tasks()->where('status', 'completed')->count(),
                'in_progress_tasks' => $project->tasks()->where('status', 'in_progress')->count(),
            ]);
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Status task berhasil diperbarui.');
    }
}
