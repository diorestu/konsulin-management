<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectProgressUpdate;
use App\Models\ProjectThreat;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'in_progress')->count();
        $completedProjects = Project::where('status', 'completed')->count();
        $totalClients = Client::count();
        $totalStaff = Staff::count();
        $openThreats = ProjectThreat::where('status', 'open')->count();

        $projectsByCategory = ProjectCategory::withCount('projects')
            ->orderBy('projects_count', 'desc')
            ->get();

        $progressUpdates = ProjectProgressUpdate::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, AVG(progress_percent) as avg_progress')
            ->where('created_at', '>=', Carbon::now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('dashboard.index', compact(
            'totalProjects',
            'activeProjects',
            'completedProjects',
            'totalClients',
            'totalStaff',
            'openThreats',
            'projectsByCategory',
            'progressUpdates'
        ));
    }
}
