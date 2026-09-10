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
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_boss_can_monitor_project_progress_from_dashboard(): void
    {
        $boss = $this->authenticateAsBoss();
        $employee = User::factory()->create(['name' => 'Rafi Staff', 'role' => 'employee']);
        $category = ProjectCategory::create(['name' => 'Finance & Tax', 'is_active' => true]);
        $accountingStaff = Staff::create(['name' => 'Ari Accounting', 'type' => 'accounting', 'is_active' => true]);
        $taxStaff = Staff::create(['name' => 'Nadia Tax', 'type' => 'tax', 'is_active' => true]);
        $client = Client::create([
            'name' => 'PT Sinar Pajak',
            'email' => 'finance@sinar.test',
            'phone' => '08123456789',
            'tax_id' => '12.345.678.9-012.000',
        ]);
        $project = Project::create([
            'client_id' => $client->id,
            'project_category_id' => $category->id,
            'name' => 'Monthly Tax Compliance',
            'service_type' => 'Tax',
            'status' => 'in_progress',
            'priority' => 'high',
            'start_date' => now()->toDateString(),
            'due_date' => now()->addDays(10)->toDateString(),
            'description' => 'Prepare monthly tax report.',
            'created_by' => $boss->id,
        ]);
        $project->staff()->sync([$accountingStaff->id, $taxStaff->id]);
        ProjectTask::create([
            'project_id' => $project->id,
            'assigned_to' => $employee->id,
            'title' => 'Collect VAT invoices',
            'status' => 'in_progress',
            'progress_percent' => 40,
            'due_date' => now()->addDays(3)->toDateString(),
        ]);

        $this->get('/projects')
            ->assertOk()
            ->assertSee('Live search')
            ->assertSee('Rows per page')
            ->assertSee('View columns')
            ->assertSee('Monthly Tax Compliance')
            ->assertSee('PT Sinar Pajak')
            ->assertSee('Finance &amp; Tax', false)
            ->assertSee('Ari Accounting')
            ->assertSee('Nadia Tax')
            ->assertSee('40%')
            ->assertSee('Collect VAT invoices');
    }

    public function test_project_can_be_created_for_a_client(): void
    {
        $boss = $this->authenticateAsBoss();
        $category = ProjectCategory::create(['name' => 'Financial Report', 'is_active' => true]);
        $accountingStaff = Staff::create(['name' => 'Ari Accounting', 'type' => 'accounting', 'is_active' => true]);
        $taxStaff = Staff::create(['name' => 'Nadia Tax', 'type' => 'tax', 'is_active' => true]);

        $response = $this->post('/projects', [
            'project_category_id' => $category->id,
            'staff_ids' => [$accountingStaff->id, $taxStaff->id],
            'client_name' => 'CV Akuntansi Maju',
            'client_email' => 'owner@maju.test',
            'client_phone' => '0822222222',
            'client_tax_id' => '98.765.432.1-000.000',
            'name' => 'Annual Financial Statement',
            'service_type' => 'Accounting',
            'status' => 'not_started',
            'priority' => 'medium',
            'start_date' => '2026-07-01',
            'due_date' => '2026-07-31',
            'description' => 'Prepare audited management report.',
            'created_by' => $boss->id,
        ]);

        $project = Project::first();

        $response->assertRedirect(route('projects.show', $project));
        $this->assertDatabaseHas('clients', ['name' => 'CV Akuntansi Maju']);
        $this->assertDatabaseHas('projects', [
            'client_id' => Client::first()->id,
            'project_category_id' => $category->id,
            'name' => 'Annual Financial Statement',
            'service_type' => 'Accounting',
        ]);
        $this->assertDatabaseHas('project_staff', [
            'project_id' => $project->id,
            'staff_id' => $accountingStaff->id,
        ]);
        $this->assertDatabaseHas('project_staff', [
            'project_id' => $project->id,
            'staff_id' => $taxStaff->id,
        ]);
    }

    public function test_project_can_be_added_to_an_existing_client(): void
    {
        $this->authenticateAsBoss();
        $client = Client::create([
            'name' => 'PT Multi Project',
            'email' => 'finance@multi.test',
        ]);

        $this->post('/projects', [
            'client_id' => $client->id,
            'name' => 'Corporate Tax Review',
            'service_type' => 'Tax',
            'status' => 'not_started',
            'priority' => 'high',
            'start_date' => '2026-08-01',
            'due_date' => '2026-08-20',
        ])->assertRedirect();

        $this->assertDatabaseCount('clients', 1);
        $this->assertDatabaseHas('projects', [
            'client_id' => $client->id,
            'name' => 'Corporate Tax Review',
        ]);
    }

    public function test_project_can_be_updated_with_client_details(): void
    {
        $this->authenticateAsBoss();
        $project = Project::factory()->create([
            'name' => 'Monthly Bookkeeping',
            'service_type' => 'Accounting',
            'status' => 'not_started',
            'priority' => 'medium',
        ]);
        $category = ProjectCategory::create(['name' => 'Tax and Accounting', 'is_active' => true]);
        $staff = Staff::create(['name' => 'Bagus Tax', 'type' => 'tax', 'is_active' => true]);

        $this->put(route('projects.update', $project), [
            'project_category_id' => $category->id,
            'staff_ids' => [$staff->id],
            'client_name' => 'PT Updated Client',
            'client_email' => 'updated@example.test',
            'client_phone' => '0811111111',
            'client_tax_id' => '11.222.333.4-555.000',
            'name' => 'Monthly Bookkeeping Updated',
            'service_type' => 'Accounting',
            'status' => 'in_progress',
            'priority' => 'high',
            'start_date' => '2026-09-01',
            'due_date' => '2026-09-30',
            'description' => 'Updated project scope.',
        ])->assertRedirect(route('projects.index'));

        $this->assertDatabaseHas('clients', [
            'id' => $project->client_id,
            'name' => 'PT Updated Client',
        ]);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'project_category_id' => $category->id,
            'name' => 'Monthly Bookkeeping Updated',
            'status' => 'in_progress',
            'priority' => 'high',
        ]);
        $this->assertDatabaseHas('project_staff', [
            'project_id' => $project->id,
            'staff_id' => $staff->id,
        ]);
    }

    public function test_project_can_be_deleted_after_confirmation_flow_submits(): void
    {
        $this->authenticateAsBoss();
        $project = Project::factory()->create(['name' => 'Delete Candidate']);

        $this->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'));

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
        ]);
    }

    public function test_employee_can_upload_progress_and_threat_on_a_project_task(): void
    {
        $employee = $this->authenticateAsEmployee();
        $project = Project::factory()->create();
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'assigned_to' => $employee->id,
            'title' => 'Reconcile bank statements',
            'status' => 'in_progress',
            'progress_percent' => 25,
        ]);

        $this->post(route('projects.progress.store', $project), [
            'project_task_id' => $task->id,
            'user_id' => $employee->id,
            'progress_percent' => 75,
            'summary' => 'Bank statements reconciled through week three.',
            'attachment_path' => 'progress/bank-recap.xlsx',
        ])->assertRedirect(route('projects.show', $project));

        $this->post(route('projects.threats.store', $project), [
            'project_task_id' => $task->id,
            'user_id' => $employee->id,
            'title' => 'Client has not sent final bank file',
            'severity' => 'high',
            'status' => 'open',
            'description' => 'Missing file may delay final reconciliation.',
            'mitigation_plan' => 'Follow up daily and escalate to manager on Friday.',
        ])->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_progress_updates', [
            'project_id' => $project->id,
            'project_task_id' => $task->id,
            'user_id' => $employee->id,
            'progress_percent' => 75,
            'summary' => 'Bank statements reconciled through week three.',
        ]);
        $this->assertDatabaseHas('project_threats', [
            'project_id' => $project->id,
            'title' => 'Client has not sent final bank file',
            'severity' => 'high',
        ]);
        $this->assertDatabaseHas('project_tasks', [
            'id' => $task->id,
            'progress_percent' => 75,
        ]);
    }
}
