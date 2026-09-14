<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\TaskTimeLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTimeLogTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private ProjectTask $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $client = Client::create([
            'name' => 'PT Test Client',
            'email' => 'client@test.com',
            'phone' => '0812345678',
            'address' => 'Jakarta',
            'status' => 'active',
        ]);

        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Audit Pajak Tahunan',
            'description' => 'Uji coba tracking waktu',
            'status' => 'in_progress',
            'service_type' => 'tax',
            'priority' => 'high',
        ]);

        $this->task = ProjectTask::create([
            'project_id' => $project->id,
            'assigned_to' => $this->user->id,
            'title' => 'Rekonsiliasi Faktur Pajak Masukan',
            'status' => 'not_started',
            'progress_percent' => 0,
        ]);
    }

    public function test_can_start_and_get_active_time_log(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('time-logs.start'), [
            'task_id' => $this->task->id,
            'notes' => 'Mulai pengerjaan awal',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('task.id', $this->task->id);

        $this->assertDatabaseHas('task_time_logs', [
            'project_task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'status' => 'running',
        ]);

        // Verify task status was auto upgraded to in_progress
        $this->assertEquals('in_progress', $this->task->fresh()->status);

        // Verify active endpoint
        $activeResponse = $this->actingAs($this->user)->getJson(route('time-logs.active'));
        $activeResponse->assertOk()
            ->assertJsonPath('active', true)
            ->assertJsonPath('task.id', $this->task->id);
    }

    public function test_starting_new_timer_stops_previous_running_timer(): void
    {
        $otherTask = ProjectTask::create([
            'project_id' => $this->task->project_id,
            'assigned_to' => $this->user->id,
            'title' => 'Penyusunan Lampiran Khusus',
            'status' => 'not_started',
            'progress_percent' => 0,
        ]);

        $this->actingAs($this->user)->postJson(route('time-logs.start'), [
            'task_id' => $this->task->id,
        ]);

        // Start another
        $this->actingAs($this->user)->postJson(route('time-logs.start'), [
            'task_id' => $otherTask->id,
        ]);

        $firstLog = TaskTimeLog::where('project_task_id', $this->task->id)->first();
        $this->assertEquals('completed', $firstLog->status);
        $this->assertNotNull($firstLog->stopped_at);

        $secondLog = TaskTimeLog::where('project_task_id', $otherTask->id)->first();
        $this->assertEquals('running', $secondLog->status);
    }

    public function test_can_stop_active_time_log_and_optionally_complete_task(): void
    {
        $this->actingAs($this->user)->postJson(route('time-logs.start'), [
            'task_id' => $this->task->id,
        ]);

        $response = $this->actingAs($this->user)->postJson(route('time-logs.stop'), [
            'notes' => 'Selesai mereview seluruh faktur',
            'mark_completed' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $log = TaskTimeLog::where('project_task_id', $this->task->id)->first();
        $this->assertEquals('completed', $log->status);
        $this->assertNotNull($log->stopped_at);
        $this->assertNotNull($log->duration_seconds);

        $this->assertEquals('completed', $this->task->fresh()->status);
        $this->assertEquals(100, $this->task->fresh()->progress_percent);
    }

    public function test_can_fetch_my_tasks_for_quick_launcher(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('time-logs.my-tasks'));

        $response->assertOk()
            ->assertJsonStructure(['tasks']);

        $tasks = $response->json('tasks');
        $this->assertCount(1, $tasks);
        $this->assertEquals($this->task->id, $tasks[0]['id']);
    }
}
