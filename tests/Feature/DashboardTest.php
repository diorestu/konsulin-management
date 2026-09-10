<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectThreat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_stats_and_charts(): void
    {
        $user = \App\Models\User::factory()->create();
        $category = ProjectCategory::create(['name' => 'Finance & Tax', 'is_active' => true]);
        $client = Client::create([
            'name' => 'PT Sinar Pajak',
            'email' => 'finance@sinar.test',
        ]);
        Project::create([
            'client_id' => $client->id,
            'project_category_id' => $category->id,
            'name' => 'Monthly Tax Compliance',
            'service_type' => 'Tax',
            'status' => 'in_progress',
            'priority' => 'high',
            'start_date' => now()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'description' => 'Prepare tax report.',
        ]);
        ProjectThreat::create([
            'project_id' => Project::first()->id,
            'user_id' => $user->id,
            'title' => 'Missing document',
            'severity' => 'high',
            'status' => 'open',
            'description' => 'Client has not sent document.',
        ]);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Total projects')
            ->assertSee('Active projects')
            ->assertSee('Clients')
            ->assertSee('Open threats')
            ->assertSee('Projects by Category')
            ->assertSee('Progress Updates (Last 14 Days)')
            ->assertSee('projectsByCategoryChart')
            ->assertSee('progressUpdatesChart');
    }
}
