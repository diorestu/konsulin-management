<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectThreat;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectKanbanController extends Controller
{
    /**
     * Display the Kanban Board for project management.
     */
    public function index(Request $request): View
    {
        $query = Project::query()
            ->with([
                'category',
                'client',
                'reviewer',
                'accountingStaff',
                'taxStaff',
                'tasks',
                'threats',
            ]);

        // Search query filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('service_type', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('client_pic', 'like', "%{$search}%");
                    });
            });
        }

        // Service Type filter
        if ($serviceType = $request->input('service_type')) {
            $query->where('service_type', $serviceType);
        }

        // Priority filter
        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        // Reviewer filter
        if ($reviewerId = $request->input('reviewer_id')) {
            $query->where('reviewer_id', $reviewerId);
        }

        // Client filter
        if ($clientId = $request->input('client_id')) {
            $query->where('client_id', $clientId);
        }

        $allProjects = $query->latest('updated_at')->get();

        // Group into Kanban workflow stages
        $columns = [
            'not_started' => [
                'id' => 'not_started',
                'title' => 'To Do',
                'subtitle' => 'Belum Dimulai',
                'accent' => '#64748b',
                'bg' => '#f1f5f9',
                'badge' => '#475569',
                'projects' => $allProjects->where('status', 'not_started')->values(),
            ],
            'in_progress' => [
                'id' => 'in_progress',
                'title' => 'In Progress',
                'subtitle' => 'Sedang Berjalan',
                'accent' => '#1d4ed8',
                'bg' => '#eff6ff',
                'badge' => '#1e40af',
                'projects' => $allProjects->where('status', 'in_progress')->values(),
            ],
            'waiting_client' => [
                'id' => 'waiting_client',
                'title' => 'Waiting Client',
                'subtitle' => 'Review / Pending Klien',
                'accent' => '#d97706',
                'bg' => '#fffbeb',
                'badge' => '#b45309',
                'projects' => $allProjects->where('status', 'waiting_client')->values(),
            ],
            'completed' => [
                'id' => 'completed',
                'title' => 'Done',
                'subtitle' => 'Selesai & Final',
                'accent' => '#059669',
                'bg' => '#ecfdf5',
                'badge' => '#047857',
                'projects' => $allProjects->where('status', 'completed')->values(),
            ],
        ];

        // Service types for dropdown filter
        $serviceTypes = Project::distinct()->pluck('service_type')->filter()->values();
        if ($serviceTypes->isEmpty()) {
            $serviceTypes = collect(['Tax Planning', 'Monthly Tax Compliance', 'Accounting & Financial Report', 'Tax Audit Assistance']);
        }

        // Reviewers and clients for filter selection
        $reviewers = User::orderBy('name')->get();
        $clients = Client::orderBy('name')->get();
        $categories = ProjectCategory::where('is_active', true)->orderBy('name')->get();

        // Staff lists for new project modal
        $allActiveStaff = Staff::where('is_active', true)->orderBy('name')->get();
        $accountingStaffList = $allActiveStaff->where('type', 'accounting');
        if ($accountingStaffList->isEmpty()) {
            $accountingStaffList = $allActiveStaff;
        }
        $taxStaffList = $allActiveStaff->where('type', 'tax');
        if ($taxStaffList->isEmpty()) {
            $taxStaffList = $allActiveStaff;
        }

        // Summary metrics
        $metrics = [
            'total' => $allProjects->count(),
            'not_started' => $columns['not_started']['projects']->count(),
            'in_progress' => $columns['in_progress']['projects']->count(),
            'waiting_client' => $columns['waiting_client']['projects']->count(),
            'completed' => $columns['completed']['projects']->count(),
            'open_threats' => ProjectThreat::where('status', 'open')->count(),
            'urgent_count' => $allProjects->whereIn('priority', ['urgent', 'high'])->count(),
        ];

        return view('kanban.index', [
            'columns' => $columns,
            'metrics' => $metrics,
            'serviceTypes' => $serviceTypes,
            'reviewers' => $reviewers,
            'clients' => $clients,
            'categories' => $categories,
            'accountingStaffList' => $accountingStaffList,
            'taxStaffList' => $taxStaffList,
            'filters' => [
                'search' => $request->input('search', ''),
                'service_type' => $request->input('service_type', ''),
                'priority' => $request->input('priority', ''),
                'reviewer_id' => $request->input('reviewer_id', ''),
                'client_id' => $request->input('client_id', ''),
            ],
        ]);
    }

    /**
     * Update project status from Kanban drag and drop or quick transition.
     */
    public function updateStatus(Request $request, Project $project): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:not_started,in_progress,waiting_client,completed'],
        ]);

        $oldStatus = $project->status;
        $project->update([
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status project ' . $project->name . ' berhasil diperbarui ke ' . str_replace('_', ' ', $validated['status']) . '.',
                'project_id' => $project->id,
                'old_status' => $oldStatus,
                'new_status' => $validated['status'],
            ]);
        }

        return back()->with('success', 'Status project berhasil diperbarui.');
    }
}
