<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TaskReviewGateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $reviewer;
    private User $staff;
    private User $otherStaff;
    private Project $taxProject;
    private ProjectTask $taxTask;

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

        $this->staff = User::factory()->create(['name' => 'Rafi Staff', 'role' => 'staff']);
        $this->staff->assignRole($staffRole);

        $this->otherStaff = User::factory()->create(['name' => 'Budi Staff', 'role' => 'staff']);
        $this->otherStaff->assignRole($staffRole);

        $client = Client::create([
            'name' => 'PT Manufaktur Solusi',
            'email' => 'finance@manufaktur.test',
            'status' => 'active',
        ]);

        $this->taxProject = Project::create([
            'client_id' => $client->id,
            'name' => 'Kepatuhan SPT Masa PPN & PPh',
            'service_type' => 'Tax Planning',
            'status' => 'in_progress',
            'priority' => 'high',
            'reviewer_id' => $this->reviewer->id,
        ]);

        $this->taxTask = ProjectTask::create([
            'project_id' => $this->taxProject->id,
            'assigned_to' => $this->staff->id,
            'title' => 'Rekonsiliasi Faktur Pajak Masukan',
            'status' => 'in_progress',
            'progress_percent' => 60,
        ]);
    }

    public function test_default_checklists_are_generated_for_task(): void
    {
        $this->taxTask->populateDefaultChecklists();

        $this->assertDatabaseHas('task_checklists', [
            'project_task_id' => $this->taxTask->id,
            'title' => 'Rekonsiliasi faktur pajak masukan & keluaran dengan buku besar',
        ]);
        $this->assertCount(4, $this->taxTask->fresh()->checklists);
    }

    public function test_staff_can_submit_task_for_review(): void
    {
        $response = $this->actingAs($this->staff)->postJson(
            route('projects.tasks.submit-review', [$this->taxProject, $this->taxTask])
        );

        $response->assertOk()
            ->assertJsonPath('success', true);

        $freshTask = $this->taxTask->fresh();
        $this->assertEquals('in_review', $freshTask->status);
        $this->assertEquals('pending', $freshTask->review_status);
        $this->assertGreaterThanOrEqual(80, $freshTask->progress_percent);

        // Checklists should automatically populate
        $this->assertGreaterThan(0, $freshTask->checklists()->count());
    }

    public function test_staff_cannot_self_approve_task_in_review(): void
    {
        $this->taxTask->update([
            'status' => 'in_review',
            'review_status' => 'pending',
        ]);

        $response = $this->actingAs($this->staff)->patchJson(
            route('projects.tasks.update-status', [$this->taxProject, $this->taxTask]),
            ['status' => 'completed']
        );

        $response->assertForbidden();
        $this->assertEquals('in_review', $this->taxTask->fresh()->status);
    }

    public function test_reviewer_can_approve_task_and_complete_it(): void
    {
        $this->taxTask->update([
            'status' => 'in_review',
            'review_status' => 'pending',
        ]);

        $response = $this->actingAs($this->reviewer)->postJson(
            route('projects.tasks.review', [$this->taxProject, $this->taxTask]),
            [
                'action' => 'approved',
                'notes' => 'Kertas kerja telah diperiksa dan sesuai regulasi.',
            ]
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('action', 'approved');

        $freshTask = $this->taxTask->fresh();
        $this->assertEquals('completed', $freshTask->status);
        $this->assertEquals('approved', $freshTask->review_status);
        $this->assertEquals(100, $freshTask->progress_percent);
        $this->assertEquals($this->reviewer->id, $freshTask->reviewed_by);
        $this->assertNotNull($freshTask->reviewed_at);

        // Audit log created
        $this->assertDatabaseHas('task_reviews', [
            'project_task_id' => $this->taxTask->id,
            'reviewer_id' => $this->reviewer->id,
            'action' => 'approved',
        ]);
    }

    public function test_reviewer_can_request_revision_with_notes(): void
    {
        $this->taxTask->update([
            'status' => 'in_review',
            'review_status' => 'pending',
        ]);

        $response = $this->actingAs($this->reviewer)->postJson(
            route('projects.tasks.review', [$this->taxProject, $this->taxTask]),
            [
                'action' => 'revision_requested',
                'notes' => 'Terdapat selisih nominal Rp 2.500.000 pada faktur nomor 010.001-24.12345678.',
            ]
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('action', 'revision_requested');

        $freshTask = $this->taxTask->fresh();
        $this->assertEquals('in_progress', $freshTask->status);
        $this->assertEquals('revision_requested', $freshTask->review_status);
        $this->assertStringContainsString('selisih nominal', $freshTask->review_notes);

        // Audit log recorded
        $this->assertDatabaseHas('task_reviews', [
            'project_task_id' => $this->taxTask->id,
            'reviewer_id' => $this->reviewer->id,
            'action' => 'revision_requested',
        ]);
    }

    public function test_staff_cannot_perform_review_action(): void
    {
        $this->taxTask->update([
            'status' => 'in_review',
        ]);

        $response = $this->actingAs($this->staff)->postJson(
            route('projects.tasks.review', [$this->taxProject, $this->taxTask]),
            [
                'action' => 'approved',
                'notes' => 'Self approval attempt.',
            ]
        );

        $response->assertForbidden();
    }

    public function test_checklist_can_be_toggled(): void
    {
        $this->taxTask->populateDefaultChecklists();
        $checklist = $this->taxTask->checklists()->first();

        $response = $this->actingAs($this->reviewer)->patchJson(
            route('projects.tasks.checklists.toggle', [$this->taxProject, $this->taxTask, $checklist])
        );

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('is_checked', true);

        $this->assertTrue($checklist->fresh()->is_checked);
        $this->assertEquals($this->reviewer->id, $checklist->fresh()->checked_by);
    }
}
