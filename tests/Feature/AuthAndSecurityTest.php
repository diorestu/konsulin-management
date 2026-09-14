<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectTask;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Seed permissions & roles
        $permissions = [
            'view projects', 'create projects', 'edit projects', 'delete projects',
            'manage tasks', 'update task progress', 'manage threats',
            'manage staff', 'manage categories', 'manage website-content',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $bossRole = Role::firstOrCreate(['name' => 'boss', 'guard_name' => 'web']);
        $bossRole->syncPermissions(Permission::all());

        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $employeeRole->syncPermissions([
            'view projects', 'create projects', 'edit projects',
            'manage tasks', 'update task progress', 'manage threats',
        ]);
    }

    public function test_user_can_view_login_page(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Konsulin Manager')
            ->assertSee('Email Kantor')
            ->assertSee('Kata Sandi')
            ->assertSee('admin@konsulin.test')
            ->assertSee('reviewer@konsulin.test')
            ->assertSee('rafi@konsulin.test')
            ->assertSee('Admin')
            ->assertSee('Reviewer')
            ->assertSee('Staff');
    }

    public function test_user_can_authenticate_and_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'boss@konsulin.test',
            'password' => Hash::make('password'),
            'role' => 'boss',
        ]);
        $user->assignRole('boss');

        $response = $this->post('/login', [
            'email' => 'boss@konsulin.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_authenticate_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'boss@konsulin.test',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'boss@konsulin.test',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('logout'));
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_user_can_register_as_employee(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Fahri Tax Specialist',
            'email' => 'fahri@konsulin.test',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'fahri@konsulin.test',
            'role' => 'employee',
        ]);

        $user = User::where('email', 'fahri@konsulin.test')->first();
        $this->assertTrue($user->hasRole('employee'));
    }

    public function test_employee_cannot_delete_project_data_security(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $employee->assignRole('employee');
        $this->actingAs($employee);

        $project = Project::factory()->create();

        $response = $this->delete(route('projects.destroy', $project));
        $response->assertForbidden();
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    public function test_boss_can_delete_project(): void
    {
        $boss = User::factory()->create(['role' => 'boss']);
        $boss->assignRole('boss');
        $this->actingAs($boss);

        $project = Project::factory()->create();

        $response = $this->delete(route('projects.destroy', $project));
        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_employee_cannot_manage_staff_or_categories(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $employee->assignRole('employee');
        $this->actingAs($employee);

        $staff = Staff::create([
            'name' => 'Existing Staff',
            'type' => 'tax',
            'is_active' => true,
        ]);

        // Trying to delete staff
        $this->delete(route('staff.destroy', $staff))->assertForbidden();

        // Trying to create category
        $this->post(route('project-categories.store'), [
            'name' => 'Illegal Category',
            'is_active' => '1',
        ])->assertForbidden();
    }

    public function test_task_status_can_be_updated_in_jira_workflow(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $user->assignRole('employee');
        $this->actingAs($user);

        $project = Project::factory()->create();
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'title' => 'Jira Task Workflow',
            'status' => 'not_started',
            'progress_percent' => 0,
        ]);

        // Move to in_progress
        $this->patch(route('projects.tasks.update-status', [$project, $task]), [
            'status' => 'in_progress',
        ])->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_tasks', [
            'id' => $task->id,
            'status' => 'in_progress',
        ]);

        // Move to completed (should auto-set 100% progress)
        $this->patch(route('projects.tasks.update-status', [$project, $task]), [
            'status' => 'completed',
        ])->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_tasks', [
            'id' => $task->id,
            'status' => 'completed',
            'progress_percent' => 100,
        ]);
    }

    public function test_progress_update_secures_user_id_from_session(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $employee->assignRole('employee');
        $this->actingAs($employee);

        $otherUser = User::factory()->create();
        $project = Project::factory()->create();
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'title' => 'Security Test Task',
            'status' => 'in_progress',
            'progress_percent' => 20,
        ]);

        // Attempting to spoof another user ID
        $this->post(route('projects.progress.store', $project), [
            'project_task_id' => $task->id,
            'user_id' => $otherUser->id,
            'progress_percent' => 60,
            'summary' => 'Progress update with secure author',
        ])->assertRedirect(route('projects.show', $project));

        // Must be recorded as the authenticated employee, NOT the spoofed other user!
        $this->assertDatabaseHas('project_progress_updates', [
            'project_id' => $project->id,
            'project_task_id' => $task->id,
            'user_id' => $employee->id,
            'progress_percent' => 60,
        ]);
    }
}

