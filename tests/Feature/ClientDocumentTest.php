<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientDocument;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectThreat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClientDocumentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $reviewer;
    private User $assignedStaff;
    private User $unassignedStaff;
    private Client $client;
    private Project $project;

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

        $this->unassignedStaff = User::factory()->create(['name' => 'Budi Luar', 'role' => 'staff']);
        $this->unassignedStaff->assignRole($staffRole);

        $this->client = Client::create([
            'name' => 'PT Surya Digital Mandiri',
            'email' => 'finance@suryadigital.test',
            'client_pic' => 'Ibu Ratna (Finance Manager)',
            'status' => 'active',
        ]);

        $this->project = Project::create([
            'client_id' => $this->client->id,
            'name' => 'Penyusunan SPT Tahunan Badan & Audit Support',
            'service_type' => 'Tax',
            'status' => 'in_progress',
            'priority' => 'high',
            'reviewer_id' => $this->reviewer->id,
            'due_date' => Carbon::now()->addDays(20),
        ]);

        ProjectTask::create([
            'project_id' => $this->project->id,
            'assigned_to' => $this->assignedStaff->id,
            'title' => 'Rekonsiliasi Omzet & PPh Badan',
            'status' => 'in_progress',
            'progress_percent' => 30,
        ]);
    }

    public function test_default_client_documents_can_be_populated_by_service_type(): void
    {
        $count = $this->project->populateDefaultDocuments();
        $this->assertGreaterThan(0, $count);
        $this->assertDatabaseHas('client_documents', [
            'project_id' => $this->project->id,
            'client_id' => $this->client->id,
            'title' => 'Rekap Penjualan & Faktur Pajak Keluaran',
            'is_critical' => true,
            'status' => 'pending',
        ]);

        // Second run should not create duplicate items
        $secondCount = $this->project->populateDefaultDocuments();
        $this->assertEquals(0, $secondCount);
    }

    public function test_project_show_page_renders_document_vault_and_stats(): void
    {
        $this->project->populateDefaultDocuments();

        $response = $this->actingAs($this->assignedStaff)->get(route('projects.show', $this->project));

        $response->assertOk()
            ->assertSee('Checklist Dokumen Masukan Klien')
            ->assertSee('>Dokumen<', false)
            ->assertSee('Berkas Masukan Klien')
            ->assertSee('Rekap Penjualan &amp; Faktur Pajak Keluaran', false);
    }

    public function test_user_can_add_custom_client_document(): void
    {
        $response = $this->actingAs($this->assignedStaff)->postJson(route('projects.documents.store', $this->project), [
            'title' => 'Dokumen Rekening Koran BCA Operasional',
            'category' => 'Bank',
            'due_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
            'is_critical' => 1,
            'file_url' => 'https://drive.google.com/sample-folder',
            'notes' => 'E-statement resmi cap basah',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('client_documents', [
            'project_id' => $this->project->id,
            'client_id' => $this->client->id,
            'title' => 'Dokumen Rekening Koran BCA Operasional',
            'category' => 'Bank',
            'is_critical' => true,
            'status' => 'pending',
            'file_url' => 'https://drive.google.com/sample-folder',
        ]);
    }

    public function test_updating_document_status_to_received_and_verified(): void
    {
        $doc = ClientDocument::create([
            'project_id' => $this->project->id,
            'client_id' => $this->client->id,
            'title' => 'Faktur Pajak Masukan Q1',
            'category' => 'Pembelian',
            'status' => 'pending',
            'is_critical' => true,
        ]);

        // 1. Mark as received
        $response1 = $this->actingAs($this->assignedStaff)->patchJson(route('projects.documents.update-status', [$this->project, $doc]), [
            'status' => 'received',
            'file_url' => 'https://drive.google.com/file-1',
        ]);

        $response1->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('document.status', 'received');

        $doc->refresh();
        $this->assertEquals('received', $doc->status);
        $this->assertNotNull($doc->received_at);
        $this->assertNull($doc->verified_at);

        // 2. Mark as verified by reviewer
        $response2 = $this->actingAs($this->reviewer)->patchJson(route('projects.documents.update-status', [$this->project, $doc]), [
            'status' => 'verified',
        ]);

        $response2->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('document.status', 'verified');

        $doc->refresh();
        $this->assertEquals('verified', $doc->status);
        $this->assertNotNull($doc->verified_at);
        $this->assertEquals($this->reviewer->id, $doc->verified_by);
    }

    public function test_delayed_document_can_be_escalated_to_threat_and_auto_resolves(): void
    {
        $doc = ClientDocument::create([
            'project_id' => $this->project->id,
            'client_id' => $this->client->id,
            'title' => 'Rekening Koran Seluruh Bank',
            'category' => 'Bank',
            'status' => 'pending',
            'is_critical' => true,
            'due_date' => Carbon::now()->subDays(2), // Overdue!
        ]);

        $this->assertTrue($doc->isOverdue());

        // Escalate to operational threat
        $response = $this->actingAs($this->assignedStaff)->postJson(route('projects.documents.escalate', [$this->project, $doc]), [
            'notes' => 'Klien tidak bisa dihubungi via telepon.',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('threat_severity', 'critical');

        $doc->refresh();
        $this->assertNotNull($doc->threat_id);

        $threat = ProjectThreat::find($doc->threat_id);
        $this->assertNotNull($threat);
        $this->assertEquals('open', $threat->status);
        $this->assertEquals('critical', $threat->severity);
        $this->assertStringContainsString('Dokumen Tertahan: Rekening Koran Seluruh Bank', $threat->title);

        // Later, client delivers document -> mark as verified -> threat automatically resolves!
        $this->actingAs($this->reviewer)->patchJson(route('projects.documents.update-status', [$this->project, $doc]), [
            'status' => 'verified',
        ]);

        $threat->refresh();
        $this->assertEquals('resolved', $threat->status);
        $this->assertStringContainsString('Dokumen telah diterima dan diverifikasi', $threat->mitigation_plan);
    }

    public function test_unassigned_staff_cannot_access_or_modify_project_documents(): void
    {
        $doc = ClientDocument::create([
            'project_id' => $this->project->id,
            'client_id' => $this->client->id,
            'title' => 'Laporan Audit Tahun Lalu',
            'category' => 'Legalitas',
            'status' => 'pending',
            'is_critical' => false,
        ]);

        $response = $this->actingAs($this->unassignedStaff)->postJson(route('projects.documents.store', $this->project), [
            'title' => 'Illegal Document',
            'category' => 'Other',
        ]);

        $response->assertForbidden();

        $patchResponse = $this->actingAs($this->unassignedStaff)->patchJson(route('projects.documents.update-status', [$this->project, $doc]), [
            'status' => 'received',
        ]);

        $patchResponse->assertForbidden();
    }

    public function test_admin_can_delete_document_from_checklist(): void
    {
        $doc = ClientDocument::create([
            'project_id' => $this->project->id,
            'client_id' => $this->client->id,
            'title' => 'Dokumen Salah Input',
            'category' => 'Lainnya',
            'status' => 'pending',
            'is_critical' => false,
        ]);

        $response = $this->actingAs($this->admin)->deleteJson(route('projects.documents.destroy', [$this->project, $doc]));

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('client_documents', [
            'id' => $doc->id,
        ]);
    }
}
