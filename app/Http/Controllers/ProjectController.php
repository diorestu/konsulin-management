<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectThreat;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $projects = Project::query()
            ->with(['category', 'client', 'staff', 'tasks.assignee', 'threats'])
            ->latest()
            ->get();

        return view('projects.index', [
            'projects' => $projects,
            'clients' => Client::orderBy('name')->get(),
            'categories' => ProjectCategory::where('is_active', true)->orderBy('name')->get(),
            'staff' => Staff::where('is_active', true)->orderBy('name')->get(),
            'bosses' => User::where('role', 'boss')->orderBy('name')->get(),
            'totalProjectsCount' => Project::count(),
            'clientsCount' => Client::count(),
            'activeProjectsCount' => Project::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'openThreatsCount' => ProjectThreat::where('status', 'open')->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['nullable', 'exists:clients,id'],
            'project_category_id' => ['nullable', 'exists:project_categories,id'],
            'staff_ids' => ['nullable', 'array'],
            'staff_ids.*' => ['exists:staff,id'],
            'client_name' => ['required_without:client_id', 'nullable', 'string', 'max:255'],
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
            'created_by' => ['nullable', 'exists:users,id'],
        ]);

        $client = isset($validated['client_id'])
            ? Client::findOrFail($validated['client_id'])
            : Client::create([
                'name' => $validated['client_name'],
                'email' => $validated['client_email'] ?? null,
                'phone' => $validated['client_phone'] ?? null,
                'tax_id' => $validated['client_tax_id'] ?? null,
            ]);

        $project = Project::create([
            'client_id' => $client->id,
            'project_category_id' => $validated['project_category_id'] ?? null,
            'created_by' => $validated['created_by'] ?? null,
            'name' => $validated['name'],
            'service_type' => $validated['service_type'],
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'start_date' => $validated['start_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        $project->staff()->sync($validated['staff_ids'] ?? []);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Project created.');
    }

    public function show(Project $project): View
    {
        $project->load([
            'client',
            'category',
            'staff',
            'tasks.assignee',
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
        $validated = $request->validate([
            'client_name' => ['required', 'string', 'max:255'],
            'project_category_id' => ['nullable', 'exists:project_categories,id'],
            'staff_ids' => ['nullable', 'array'],
            'staff_ids.*' => ['exists:staff,id'],
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
            'created_by' => ['nullable', 'exists:users,id'],
        ]);

        $project->client->update([
            'name' => $validated['client_name'],
            'email' => $validated['client_email'] ?? null,
            'phone' => $validated['client_phone'] ?? null,
            'tax_id' => $validated['client_tax_id'] ?? null,
        ]);

        $project->update([
            'project_category_id' => $validated['project_category_id'] ?? null,
            'created_by' => $validated['created_by'] ?? null,
            'name' => $validated['name'],
            'service_type' => $validated['service_type'],
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'start_date' => $validated['start_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        $project->staff()->sync($validated['staff_ids'] ?? []);

        return redirect()
            ->route('projects.index')
            ->with('status', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('status', 'Project deleted.');
    }

    public function storeTask(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'status' => ['required', 'string', 'max:50'],
            'progress_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $project->tasks()->create($validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Task added.');
    }
}
