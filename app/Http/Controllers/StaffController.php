<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $staff = Staff::with([
            'projects' => function ($query) {
                $query->select('projects.id', 'projects.name', 'projects.client_id', 'projects.status', 'projects.service_type')
                    ->with('client:id,name,business_type,status');
            }
        ])
        ->latest()
        ->get();

        $totalClientsCount = Client::count();
        $totalClientsHandled = Client::whereHas('projects.staff')->count();
        $unassignedClientsCount = max(0, $totalClientsCount - $totalClientsHandled);

        $totalActiveAssignments = 0;
        $availableCount = 0;
        $optimalCount = 0;
        $heavyCount = 0;
        $overloadCount = 0;

        foreach ($staff as $employee) {
            $activeProjects = $employee->projects->whereNotIn('status', ['completed', 'cancelled'])->values();
            $completedProjects = $employee->projects->where('status', 'completed')->values();

            $employee->active_projects_count = $activeProjects->count();
            $employee->completed_projects_count = $completedProjects->count();
            $employee->projects_count = $employee->projects->count();

            // Unique clients map with their project details
            $clientsMap = [];
            foreach ($employee->projects as $project) {
                if ($project->client) {
                    $clientId = $project->client->id;
                    if (! isset($clientsMap[$clientId])) {
                        $clientsMap[$clientId] = [
                            'client' => $project->client,
                            'active_projects' => [],
                            'completed_projects' => [],
                        ];
                    }
                    if (in_array($project->status, ['completed', 'cancelled'])) {
                        $clientsMap[$clientId]['completed_projects'][] = $project;
                    } else {
                        $clientsMap[$clientId]['active_projects'][] = $project;
                    }
                }
            }

            $employee->client_portfolios = array_values($clientsMap);
            $employee->handled_clients = collect(array_column($clientsMap, 'client'));
            $employee->clients_count = count($clientsMap);

            if (! $employee->is_active) {
                $employee->workload_status = 'inactive';
                $employee->workload_label = 'Non-aktif';
                $employee->workload_badge_class = 'bg-slate-100 text-slate-600 border-slate-200';
            } elseif ($employee->active_projects_count === 0) {
                $employee->workload_status = 'available';
                $employee->workload_label = 'Tersedia';
                $employee->workload_badge_class = 'bg-slate-100 text-slate-700 border-slate-200';
                $availableCount++;
            } elseif ($employee->active_projects_count <= 3) {
                $employee->workload_status = 'optimal';
                $employee->workload_label = 'Optimal';
                $employee->workload_badge_class = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                $optimalCount++;
            } elseif ($employee->active_projects_count <= 5) {
                $employee->workload_status = 'heavy';
                $employee->workload_label = 'Beban Tinggi';
                $employee->workload_badge_class = 'bg-amber-50 text-amber-700 border-amber-200';
                $heavyCount++;
            } else {
                $employee->workload_status = 'overload';
                $employee->workload_label = 'Overload';
                $employee->workload_badge_class = 'bg-rose-50 text-rose-700 border-rose-200';
                $overloadCount++;
            }

            if ($employee->is_active) {
                $totalActiveAssignments += $employee->active_projects_count;
            }

            $employee->portfolio_data = [
                'id' => $employee->id,
                'name' => $employee->name,
                'position' => $employee->position ?? 'Konsultan',
                'type' => $employee->type,
                'email' => $employee->email ?? '-',
                'phone' => $employee->phone ?? '-',
                'is_active' => $employee->is_active,
                'workload_status' => $employee->workload_status,
                'workload_label' => $employee->workload_label,
                'workload_badge_class' => $employee->workload_badge_class,
                'active_projects_count' => $employee->active_projects_count,
                'completed_projects_count' => $employee->completed_projects_count,
                'clients_count' => $employee->clients_count,
                'portfolios' => $employee->client_portfolios,
            ];
        }

        $activeStaffCount = $staff->where('is_active', true)->count();
        $assignedStaffCount = $staff->filter(fn ($s) => $s->active_projects_count > 0)->count();
        $avgActiveProjects = $activeStaffCount > 0 ? round($totalActiveAssignments / $activeStaffCount, 1) : 0;

        return view('staff.index', [
            'staff' => $staff,
            'types' => Staff::TYPES,
            'totalStaffCount' => $staff->count(),
            'activeStaffCount' => $activeStaffCount,
            'taxAccountingStaffCount' => $staff->whereIn('type', ['tax', 'accounting'])->count(),
            'assignedStaffCount' => $assignedStaffCount,
            'totalClientsCount' => $totalClientsCount,
            'totalClientsHandled' => $totalClientsHandled,
            'unassignedClientsCount' => $unassignedClientsCount,
            'totalActiveAssignments' => $totalActiveAssignments,
            'availableCount' => $availableCount,
            'optimalCount' => $optimalCount,
            'heavyCount' => $heavyCount,
            'overloadCount' => $overloadCount,
            'avgActiveProjects' => $avgActiveProjects,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (auth()->check() && !auth()->user()->can('manage staff') && !auth()->user()->isBoss()) {
            abort(403, 'Hanya pimpinan / admin yang diizinkan menambah data staff.');
        }

        Staff::create($this->validated($request));

        return redirect()
            ->route('staff.index')
            ->with('status', 'Staff created.');
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        if (auth()->check() && !auth()->user()->can('manage staff') && !auth()->user()->isBoss()) {
            abort(403, 'Hanya pimpinan / admin yang diizinkan mengubah data staff.');
        }

        $staff->update($this->validated($request, $staff));

        return redirect()
            ->route('staff.index')
            ->with('status', 'Staff updated.');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        if (auth()->check() && !auth()->user()->can('manage staff') && !auth()->user()->isBoss()) {
            abort(403, 'Hanya pimpinan / admin yang diizinkan menghapus data staff.');
        }

        $staff->delete();

        return redirect()
            ->route('staff.index')
            ->with('status', 'Staff deleted.');
    }

    private function validated(Request $request, ?Staff $staff = null): array
    {
        $id = $staff?->id ?? 'NULL';

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:staff,email,'.$id],
            'phone' => ['nullable', 'string', 'max:50'],
            'type' => ['required', Rule::in(Staff::TYPES)],
            'position' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        $validated['is_active'] = (bool) $validated['is_active'];

        return $validated;
    }
}
