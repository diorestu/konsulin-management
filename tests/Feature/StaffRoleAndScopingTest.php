<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffRoleAndScopingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $reviewer;
    private User $staff1;
    private User $staff2;
    private Project $project1;
    private Project $project2;
    private ProjectTask $taskStaff1;
    private ProjectTask $taskStaff2;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Roles & Permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        $reviewerRole = Role::firstOrCreate(['name' => 'reviewer', 'guard_name' => 'web']);
        $staffRole = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->assignRole($adminRole);

        $this->reviewer = User::factory()->create(['role' => 'reviewer']);
        $this->reviewer->assignRole($reviewerRole);

        $this->staff1 = User::factory()->create(['name' => 'Staff Satu', 'email' => 'staff1@konsulin.test', 'role' => 'staff']);
        $this->staff1->assignRole($staffRole);

        $this->staff2 = User::factory()->create(['name' => 'Staff Dua', 'email' => 'staff2@konsulin.test', 'role' => 'staff']);
        $this->staff2->assignRole($staffRole);

        $client = Client::create([
            'name' => 'PT Mitra Sejahtera',
            'email' => 'mitra@test.com',
            'status' => 'active',
        ]);

        // Project 1 is assigned to staff 1 via task
        $this->project1 = Project::create([
            'client_id' => $client->id,
            'name' => 'Project Pajak Staff 1',
            'service_type' => 'tax',
            'status' => 'in_progress',
            'priority' => 'high',
        ]);

        $this->taskStaff1 = ProjectTask::create([
            'project_id' => $this->project1->id,
            'assigned_to' => $this->staff1->id,
            'title' => 'Tugas Khusus Staff 1',
            'status' => 'not_started',
            'progress_percent' => 0,
        ]);

        // Project 2 is assigned to staff 2
        $this->project2 = Project::create([
            'client_id' => $client->id,
            'name' => 'Project Akuntansi Staff 2',
            'service_type' => 'accounting',
            'status' => 'in_progress',
            'priority' => 'normal',
        ]);

        $this->taskStaff2 = ProjectTask::create([
            'project_id' => $this->project2->id,
            'assigned_to' => $this->staff2->id,
            'title' => 'Tugas Khusus Staff 2',
            'status' => 'not_started',
            'progress_percent' => 0,
        ]);
    }

    public function test_user_roles_are_correctly_identified(): void
    {
        $this->assertTrue($this->admin->isAdmin());
        $this->assertFalse($this->admin->isStaff());

        $this->assertTrue($this->reviewer->isReviewer());
        $this->assertFalse($this->reviewer->isStaff());

        $this->assertTrue($this->staff1->isStaff());
        $this->assertFalse($this->staff1->isAdmin());
        $this->assertFalse($this->staff1->isReviewer());
    }

    public function test_staff_can_only_see_assigned_projects_in_index(): void
    {
        $response = $this->actingAs($this->staff1)->get(route('projects.index'));
        $response->assertOk();
        $response->assertSee('Project Pajak Staff 1');
        $response->assertDontSee('Project Akuntansi Staff 2');

        // Admin can see both
        $adminResponse = $this->actingAs($this->admin)->get(route('projects.index'));
        $adminResponse->assertOk();
        $adminResponse->assertSee('Project Pajak Staff 1');
        $adminResponse->assertSee('Project Akuntansi Staff 2');
    }

    public function test_staff_cannot_view_unassigned_project_details(): void
    {
        // Staff 1 can view project 1
        $this->actingAs($this->staff1)
            ->get(route('projects.show', $this->project1))
            ->assertOk()
            ->assertSee('Project Pajak Staff 1');

        // Staff 1 cannot view project 2
        $this->actingAs($this->staff1)
            ->get(route('projects.show', $this->project2))
            ->assertForbidden();
    }

    public function test_staff_can_only_update_tasks_assigned_to_them(): void
    {
        // Staff 1 updating own task succeeds
        $response = $this->actingAs($this->staff1)
            ->patchJson(route('projects.tasks.update-status', [$this->project1, $this->taskStaff1]), [
                'status' => 'in_progress',
            ]);

        $response->assertOk();
        $this->assertEquals('in_progress', $this->taskStaff1->fresh()->status);

        // Staff 1 trying to update Staff 2's task is forbidden
        $forbiddenResponse = $this->actingAs($this->staff1)
            ->patchJson(route('projects.tasks.update-status', [$this->project2, $this->taskStaff2]), [
                'status' => 'in_progress',
            ]);

        $forbiddenResponse->assertForbidden();
        $this->assertEquals('not_started', $this->taskStaff2->fresh()->status);
    }

    public function test_time_tracker_is_only_allowed_for_staff(): void
    {
        // Staff 1 can start tracking own task
        $staffResponse = $this->actingAs($this->staff1)->postJson(route('time-logs.start'), [
            'task_id' => $this->taskStaff1->id,
        ]);
        $staffResponse->assertOk()->assertJsonPath('success', true);

        // Admin cannot track time
        $adminResponse = $this->actingAs($this->admin)->postJson(route('time-logs.start'), [
            'task_id' => $this->taskStaff1->id,
        ]);
        $adminResponse->assertForbidden()
            ->assertJsonPath('success', false);

        // Reviewer cannot track time
        $reviewerResponse = $this->actingAs($this->reviewer)->postJson(route('time-logs.start'), [
            'task_id' => $this->taskStaff1->id,
        ]);
        $reviewerResponse->assertForbidden()
            ->assertJsonPath('success', false);
    }

    public function test_staff_cannot_track_tasks_assigned_to_another_staff(): void
    {
        // Staff 1 trying to track Staff 2's task is forbidden
        $response = $this->actingAs($this->staff1)->postJson(route('time-logs.start'), [
            'task_id' => $this->taskStaff2->id,
        ]);

        $response->assertForbidden();
    }

    public function test_project_show_contains_improved_stats_widgets(): void
    {
        $response = $this->actingAs($this->admin)->get(route('projects.show', $this->project1));

        $response->assertOk()
            ->assertSee('Overall Progress')
            ->assertSee('Tasks Lifecycle')
            ->assertSee('Logged Work Time')
            ->assertSee('Operational Threats');
    }
}
