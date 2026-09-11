<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectKanbanTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateUser(): User
    {
        $user = User::factory()->create(['name' => 'Manager User', 'role' => 'boss']);
        $this->actingAs($user);
        return $user;
    }

    public function test_guest_cannot_access_kanban_board(): void
    {
        $response = $this->get(route('kanban.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_kanban_board_with_columns_and_team(): void
    {
        $user = $this->authenticateUser();

        $client = Client::create([
            'name' => 'PT Maju Makmur Pajak',
            'client_pic' => 'Bpk. Handoko',
            'client_type' => 'Badan',
            'email' => 'finance@majumakmur.test',
        ]);

        $category = ProjectCategory::create(['name' => 'Tax Advisory', 'is_active' => true]);

        $reviewer = User::factory()->create(['name' => 'Budi Reviewer']);
        $accStaff = Staff::create(['name' => 'Dewi Accounting', 'type' => 'accounting', 'is_active' => true]);
        $taxStaff = Staff::create(['name' => 'Eko Tax Officer', 'type' => 'tax', 'is_active' => true]);

        $projectTodo = Project::create([
            'client_id' => $client->id,
            'project_category_id' => $category->id,
            'created_by' => $user->id,
            'reviewer_id' => $reviewer->id,
            'name' => 'SPT Tahunan Badan PT Maju',
            'service_type' => 'Tax Planning',
            'status' => 'not_started',
            'priority' => 'high',
            'due_date' => now()->addDays(14)->toDateString(),
        ]);
        $projectTodo->accountingStaff()->attach($accStaff->id, ['role' => 'pic_accounting']);
        $projectTodo->taxStaff()->attach($taxStaff->id, ['role' => 'pic_tax']);

        $projectProgress = Project::create([
            'client_id' => $client->id,
            'project_category_id' => $category->id,
            'created_by' => $user->id,
            'reviewer_id' => $reviewer->id,
            'name' => 'Financial Review Semester 1',
            'service_type' => 'Accounting & Bookkeeping Service',
            'status' => 'in_progress',
            'priority' => 'urgent',
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $response = $this->get(route('kanban.index'));

        $response->assertOk();
        $response->assertSee('Kanban Board Project');
        $response->assertSee('To Do');
        $response->assertSee('In Progress');
        $response->assertSee('Waiting Client');
        $response->assertSee('Done');

        // Check project cards content
        $response->assertSee('SPT Tahunan Badan PT Maju');
        $response->assertSee('Financial Review Semester 1');
        $response->assertSee('PT Maju Makmur Pajak');
        $response->assertSee('Bpk. Handoko');
        $response->assertSee('Budi Reviewer');
        $response->assertSee('Dewi Accounting');
        $response->assertSee('Eko Tax Officer');
    }

    public function test_user_can_filter_kanban_board_by_search_query(): void
    {
        $user = $this->authenticateUser();

        $clientA = Client::create(['name' => 'PT Alpha Solusi', 'client_type' => 'Badan']);
        $clientB = Client::create(['name' => 'CV Beta Jaya', 'client_type' => 'Badan']);

        Project::create([
            'client_id' => $clientA->id,
            'created_by' => $user->id,
            'name' => 'Alpha Tax Planning',
            'service_type' => 'Tax Planning',
            'status' => 'not_started',
            'priority' => 'medium',
        ]);

        Project::create([
            'client_id' => $clientB->id,
            'created_by' => $user->id,
            'name' => 'Beta Bookkeeping',
            'service_type' => 'Accounting',
            'status' => 'in_progress',
            'priority' => 'low',
        ]);

        $response = $this->get(route('kanban.index', ['search' => 'Alpha']));

        $response->assertOk();
        $response->assertSee('Alpha Tax Planning');
        $response->assertDontSee('Beta Bookkeeping');
    }

    public function test_user_can_update_project_status_via_ajax(): void
    {
        $user = $this->authenticateUser();

        $client = Client::create(['name' => 'PT Delta Persada', 'client_type' => 'Badan']);

        $project = Project::create([
            'client_id' => $client->id,
            'created_by' => $user->id,
            'name' => 'Auditing Assistance Delta',
            'service_type' => 'Tax Audit Assistance',
            'status' => 'not_started',
            'priority' => 'high',
        ]);

        $this->assertEquals('not_started', $project->status);

        // Move to in_progress via AJAX
        $response = $this->patchJson(route('kanban.update-status', $project), [
            'status' => 'in_progress',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'project_id' => $project->id,
            'old_status' => 'not_started',
            'new_status' => 'in_progress',
        ]);

        $project->refresh();
        $this->assertEquals('in_progress', $project->status);

        // Move to completed via standard form submit
        $redirectResponse = $this->patch(route('kanban.update-status', $project), [
            'status' => 'completed',
        ]);

        $redirectResponse->assertRedirect();
        $project->refresh();
        $this->assertEquals('completed', $project->status);
    }

    public function test_kanban_status_update_validates_allowed_statuses(): void
    {
        $user = $this->authenticateUser();

        $client = Client::create(['name' => 'PT Epsilon', 'client_type' => 'Badan']);
        $project = Project::create([
            'client_id' => $client->id,
            'created_by' => $user->id,
            'name' => 'Epsilon Project',
            'service_type' => 'Tax',
            'status' => 'not_started',
            'priority' => 'medium',
        ]);

        $response = $this->patchJson(route('kanban.update-status', $project), [
            'status' => 'invalid_status_string',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }
}
