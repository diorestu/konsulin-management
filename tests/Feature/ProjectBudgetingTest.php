<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\TaskTimeLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectBudgetingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $reviewer;
    private User $assignedStaff;
    private User $unassignedStaff;
    private Client $client;
    private Project $project;
    private ProjectTask $task;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        $reviewerRole = Role::firstOrCreate(['name' => 'reviewer', 'guard_name' => 'web']);
        $staffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        $this->admin = User::factory()->create(['name' => 'Dewi Admin', 'role' => 'admin']);
        $this->admin->assignRole($adminRole);

        $this->reviewer = User::factory()->create(['name' => 'Nadia Reviewer', 'role' => 'reviewer']);
        $this->reviewer->assignRole($reviewerRole);

        $this->assignedStaff = User::factory()->create(['name' => 'Rafi Staff', 'role' => 'staff']);
        $this->assignedStaff->assignRole($staffRole);

        $this->unassignedStaff = User::factory()->create(['name' => 'Budi Lain', 'role' => 'staff']);
        $this->unassignedStaff->assignRole($staffRole);

        $this->client = Client::create([
            'name' => 'PT Manufaktur Presisi',
            'email' => 'finance@manufaktur.test',
            'status' => 'active',
        ]);

        $this->project = Project::create([
            'client_id' => $this->client->id,
            'name' => 'Restrukturisasi Pajak & Laporan Keuangan 2026',
            'service_type' => 'Tax Planning',
            'status' => 'in_progress',
            'priority' => 'high',
            'estimated_hours' => 20.0,
            'reviewer_id' => $this->reviewer->id,
        ]);

        $this->task = ProjectTask::create([
            'project_id' => $this->project->id,
            'assigned_to' => $this->assignedStaff->id,
            'title' => 'Rekonsiliasi Buku Besar & Ekualisasi PPh 21',
            'status' => 'in_progress',
            'progress_percent' => 30,
            'estimated_hours' => 10.0,
        ]);
    }

    public function test_can_create_project_with_estimated_hours(): void
    {
        $response = $this->actingAs($this->admin)->post(route('projects.store'), [
            'client_id' => $this->client->id,
            'name' => 'Audit Kepatuhan Pajak Badan 2026',
            'service_type' => 'Tax Audit',
            'status' => 'not_started',
            'priority' => 'medium',
            'estimated_hours' => 35.5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'name' => 'Audit Kepatuhan Pajak Badan 2026',
            'estimated_hours' => 35.5,
        ]);
    }

    public function test_can_update_project_estimated_hours(): void
    {
        $response = $this->actingAs($this->admin)->put(route('projects.update', $this->project), [
            'client_name' => $this->client->name,
            'name' => $this->project->name,
            'service_type' => $this->project->service_type,
            'status' => $this->project->status,
            'priority' => $this->project->priority,
            'estimated_hours' => 25.0,
        ]);

        $response->assertRedirect();
        $this->assertEquals(25.0, (float) $this->project->fresh()->estimated_hours);
    }

    public function test_can_create_task_with_estimated_hours(): void
    {
        $response = $this->actingAs($this->admin)->postJson(
            route('projects.tasks.store', $this->project),
            [
                'title' => 'Verifikasi Bukti Potong Unifikasi',
                'assigned_to' => $this->assignedStaff->id,
                'status' => 'not_started',
                'progress_percent' => 0,
                'estimated_hours' => 6.5,
            ]
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('task.estimated_hours', 6.5)
            ->assertJsonPath('task.budget_status', 'on_track')
            ->assertJsonPath('project_budget.effective_estimated_hours', 20);

        $this->assertDatabaseHas('project_tasks', [
            'title' => 'Verifikasi Bukti Potong Unifikasi',
            'estimated_hours' => 6.5,
        ]);
    }

    public function test_task_budget_and_burn_rate_calculations(): void
    {
        // 1. Initial state (0 hours logged)
        $this->assertEquals(0, $this->task->actualLoggedHours());
        $this->assertEquals(0, $this->task->burnRatePercent());
        $this->assertEquals('on_track', $this->task->budgetStatus());
        $this->assertEquals(10.0, $this->task->remainingHours());
        $this->assertEquals(0, $this->task->overBudgetHours());

        // 2. Log 5 hours (50% burn rate: On Track)
        TaskTimeLog::create([
            'project_task_id' => $this->task->id,
            'user_id' => $this->assignedStaff->id,
            'started_at' => Carbon::now()->subHours(5),
            'stopped_at' => Carbon::now(),
            'duration_seconds' => 5 * 3600,
            'status' => 'completed',
        ]);

        $task = $this->task->fresh();
        $this->assertEquals(5.0, $task->actualLoggedHours());
        $this->assertEquals(50, $task->burnRatePercent());
        $this->assertEquals('on_track', $task->budgetStatus());
        $this->assertEquals(5.0, $task->remainingHours());
        $this->assertEquals(0, $task->overBudgetHours());

        // 3. Log additional 3.5 hours (total 8.5 hours = 85%: Warning)
        TaskTimeLog::create([
            'project_task_id' => $this->task->id,
            'user_id' => $this->assignedStaff->id,
            'started_at' => Carbon::now()->subMinutes(210),
            'stopped_at' => Carbon::now(),
            'duration_seconds' => 210 * 60,
            'status' => 'completed',
        ]);

        $task = $this->task->fresh();
        $this->assertEquals(8.5, $task->actualLoggedHours());
        $this->assertEquals(85, $task->burnRatePercent());
        $this->assertEquals('warning', $task->budgetStatus());
        $this->assertEquals(1.5, $task->remainingHours());
        $this->assertEquals(0, $task->overBudgetHours());

        // 4. Log additional 3 hours (total 11.5 hours = 115%: Over Budget)
        TaskTimeLog::create([
            'project_task_id' => $this->task->id,
            'user_id' => $this->assignedStaff->id,
            'started_at' => Carbon::now()->subHours(3),
            'stopped_at' => Carbon::now(),
            'duration_seconds' => 3 * 3600,
            'status' => 'completed',
        ]);

        $task = $this->task->fresh();
        $this->assertEquals(11.5, $task->actualLoggedHours());
        $this->assertEquals(115, $task->burnRatePercent());
        $this->assertEquals('over_budget', $task->budgetStatus());
        $this->assertEquals(0, $task->remainingHours());
        $this->assertEquals(1.5, $task->overBudgetHours());
    }

    public function test_project_budget_burn_rate_and_thresholds(): void
    {
        // Project estimated_hours = 20.0
        // Log 16 hours to the task
        TaskTimeLog::create([
            'project_task_id' => $this->task->id,
            'user_id' => $this->assignedStaff->id,
            'started_at' => Carbon::now()->subHours(16),
            'stopped_at' => Carbon::now(),
            'duration_seconds' => 16 * 3600,
            'status' => 'completed',
        ]);

        $project = $this->project->fresh();
        $this->assertEquals(20.0, $project->effectiveEstimatedHours());
        $this->assertEquals(16.0, $project->totalLoggedHours());
        $this->assertEquals(80, $project->burnRatePercent());
        $this->assertEquals('warning', $project->budgetStatus());
        $this->assertEquals(4.0, $project->remainingHours());

        // Now log 6 more hours (total 22 hours / 20 hours = 110%)
        TaskTimeLog::create([
            'project_task_id' => $this->task->id,
            'user_id' => $this->assignedStaff->id,
            'started_at' => Carbon::now()->subHours(6),
            'stopped_at' => Carbon::now(),
            'duration_seconds' => 6 * 3600,
            'status' => 'completed',
        ]);

        $project = $this->project->fresh();
        $this->assertEquals(22.0, $project->totalLoggedHours());
        $this->assertEquals(110, $project->burnRatePercent());
        $this->assertEquals('over_budget', $project->budgetStatus());
        $this->assertEquals(0, $project->remainingHours());
        $this->assertEquals(2.0, $project->overBudgetHours());
    }

    public function test_project_effective_estimate_falls_back_to_sum_of_tasks_when_project_budget_is_zero(): void
    {
        $this->project->update(['estimated_hours' => 0]);

        // Task has 10 hours estimated
        ProjectTask::create([
            'project_id' => $this->project->id,
            'title' => 'Tugas Tambahan Kedua',
            'status' => 'not_started',
            'estimated_hours' => 5.0,
        ]);

        $project = $this->project->fresh();
        $this->assertEquals(0, $project->projectBudgetHours());
        $this->assertEquals(15.0, $project->tasksTotalEstimatedHours());
        $this->assertEquals(15.0, $project->effectiveEstimatedHours());
    }

    public function test_authorized_user_can_update_task_estimate(): void
    {
        $response = $this->actingAs($this->assignedStaff)->patchJson(
            route('projects.tasks.update-estimate', [$this->project, $this->task]),
            ['estimated_hours' => 12.5]
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('task.estimated_hours', 12.5);

        $this->assertEquals(12.5, (float) $this->task->fresh()->estimated_hours);
    }

    public function test_unassigned_staff_cannot_update_task_estimate(): void
    {
        $response = $this->actingAs($this->unassignedStaff)->patchJson(
            route('projects.tasks.update-estimate', [$this->project, $this->task]),
            ['estimated_hours' => 14.0]
        );

        $response->assertForbidden();
        $this->assertEquals(10.0, (float) $this->task->fresh()->estimated_hours);
    }

    public function test_admin_can_update_project_budget(): void
    {
        $response = $this->actingAs($this->admin)->patchJson(
            route('projects.update-budget', $this->project),
            ['estimated_hours' => 50.0]
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('project_budget.project_budget_hours', 50);

        $this->assertEquals(50.0, (float) $this->project->fresh()->estimated_hours);
    }

    public function test_staff_cannot_update_project_budget(): void
    {
        $response = $this->actingAs($this->assignedStaff)->patchJson(
            route('projects.update-budget', $this->project),
            ['estimated_hours' => 99.0]
        );

        $response->assertForbidden();
        $this->assertEquals(20.0, (float) $this->project->fresh()->estimated_hours);
    }
}
