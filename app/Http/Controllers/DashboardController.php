<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientCompliance;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectProgressUpdate;
use App\Models\ProjectThreat;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'in_progress')->count();
        $completedProjects = Project::where('status', 'completed')->count();
        $totalClients = Client::count();
        $totalStaff = Staff::count();
        $openThreats = ProjectThreat::where('status', 'open')->count();

        // Critical / upcoming deadlines (< 7 days or overdue, not completed)
        $nearDeadlineCount = Project::where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->where('due_date', '<=', Carbon::now()->addDays(7))
            ->count();

        // Pending review count (projects in review status + clients with pending review approval)
        $pendingReviewCount = Project::where('status', 'review')->count()
            + Client::where('review_approval', 'like', '%Pending%')->count();

        // Upcoming active projects sorted by due date
        $upcomingProjects = Project::with(['client', 'category', 'accountingStaff', 'taxStaff', 'reviewer', 'tasks'])
            ->where('status', '!=', 'completed')
            ->orderBy('due_date', 'asc')
            ->take(6)
            ->get();

        // Open threats list sorted by severity (critical, high, medium, low)
        $activeThreatsList = ProjectThreat::with(['project.client', 'user'])
            ->where('status', 'open')
            ->orderByRaw("CASE WHEN severity = 'critical' THEN 1 WHEN severity = 'high' THEN 2 WHEN severity = 'medium' THEN 3 ELSE 4 END")
            ->take(5)
            ->get();

        // Staff workload (Tax & Accounting PICs)
        $staffWorkloads = Staff::where('is_active', true)
            ->withCount(['projects' => function ($q) {
                $q->where('status', '!=', 'completed');
            }])
            ->orderBy('projects_count', 'desc')
            ->take(6)
            ->get();

        // Tax Compliance periods and selected period
        $compliancePeriods = Client::COMPLIANCE_PERIODS;
        $selectedPeriod = $request->query('period', 'Mar 26');
        if (!in_array($selectedPeriod, $compliancePeriods, true)) {
            $selectedPeriod = 'Mar 26';
        }

        $periodCompliances = ClientCompliance::with('client:id,name,client_code,tax_status')
            ->where('period', $selectedPeriod)
            ->get();

        $projectsByCategory = ProjectCategory::withCount('projects')
            ->orderBy('projects_count', 'desc')
            ->get();

        $progressUpdates = ProjectProgressUpdate::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, AVG(progress_percent) as avg_progress')
            ->where('created_at', '>=', Carbon::now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 4 Portfolio Analytics Charts (Jumlah Kontrak Klien, PIC Tax, Data Migration, PIC Accounting)
        $allClients = Client::all();

        $activeClientsCount = $allClients->where('contract_status', 'Active')->count();
        $nonactiveClientsCount = $allClients->where('contract_status', '!=', 'Active')->count();
        $pkpClientsCount = $allClients->where('tax_status', 'PKP')->count();
        $nonPkpClientsCount = $allClients->where('tax_status', '!=', 'PKP')->count();

        // Reviewer / Approver partner distribution
        $reviewersDistribution = $allClients->whereNotNull('review_approval')
            ->where('review_approval', '!=', '')
            ->groupBy('review_approval')
            ->map->count()
            ->sortDesc();

        $contractDurations = collect([
            'Monthly' => $allClients->filter(fn($c) => $c->contract_duration_months && $c->contract_duration_months < 12)->count(),
            'Annual' => $allClients->filter(fn($c) => $c->contract_duration_months && $c->contract_duration_months >= 12)->count(),
        ]);

        $taxPicDistribution = $allClients->whereNotNull('tax_pic')
            ->where('tax_pic', '!=', '')
            ->groupBy('tax_pic')
            ->map->count()
            ->sortDesc();

        $dataMigrationDistribution = $allClients->groupBy(function ($c) {
            return $c->migration_date ? $c->migration_date->format('d/m/Y') : 'Belum';
        })->map->count()->sortDesc();

        $accountingPicDistribution = $allClients->whereNotNull('accounting_pic')
            ->where('accounting_pic', '!=', '')
            ->groupBy('accounting_pic')
            ->map->count()
            ->sortDesc();

        // Selected period compliance metrics
        $periodDoneCount = 0;
        $periodLkCount = 0;
        foreach ($periodCompliances as $comp) {
            foreach (['pph_21', 'pph_unifikasi', 'ppn', 'pp_55', 'pph_25'] as $field) {
                $val = strtolower((string)$comp->{$field});
                if (in_array($val, ['lapor', 'done', 'final', 'selesai', 'reported'])) {
                    $periodDoneCount++;
                }
            }
            if (in_array(strtolower((string)$comp->lk), ['rilis lk', 'final', 'done', 'selesai'])) {
                $periodLkCount++;
            }
        }

        // Actionable pending notes in the selected period
        $periodPendingNotes = $periodCompliances->filter(function ($comp) {
            $notes = trim((string)$comp->notes);
            return !empty($notes) && !in_array(strtolower($notes), ['-', 'ok', 'done', 'tepat waktu']);
        })->values();

        return view('dashboard.index', compact(
            'totalProjects',
            'activeProjects',
            'completedProjects',
            'totalClients',
            'activeClientsCount',
            'nonactiveClientsCount',
            'pkpClientsCount',
            'nonPkpClientsCount',
            'reviewersDistribution',
            'totalStaff',
            'openThreats',
            'nearDeadlineCount',
            'pendingReviewCount',
            'upcomingProjects',
            'activeThreatsList',
            'staffWorkloads',
            'compliancePeriods',
            'selectedPeriod',
            'periodCompliances',
            'periodDoneCount',
            'periodLkCount',
            'periodPendingNotes',
            'projectsByCategory',
            'progressUpdates',
            'contractDurations',
            'taxPicDistribution',
            'dataMigrationDistribution',
            'accountingPicDistribution'
        ));
    }
}

