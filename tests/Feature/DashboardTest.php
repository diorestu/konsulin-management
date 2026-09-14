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

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_dashboard_displays_stats_and_charts(): void
    {
        $user = $this->authenticateAsBoss();
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
            ->assertSee('Tenggat &lt; 7 Hari', false)
            ->assertSee('Review Pending')
            ->assertSee('Tenggat Proyek Terdekat')
            ->assertSee('Matriks Kepatuhan Pajak')
            ->assertSee('Radar Risiko & Threats Aktif', false)
            ->assertSee('Beban Kerja Tim PIC')
            ->assertSee('Projects by Category')
            ->assertSee('Progress Updates (Last 14 Days)')
            ->assertSee('projectsByCategoryChart')
            ->assertSee('progressUpdatesChart')
            ->assertSee('Jumlah Kontrak Klien')
            ->assertSee('PIC Tax')
            ->assertSee('Data Migration')
            ->assertSee('PIC Accounting')
            ->assertSee('contractDurationsChart')
            ->assertSee('taxPicChart')
            ->assertSee('dataMigrationChart')
            ->assertSee('accountingPicChart');
    }

    public function test_dashboard_filters_tax_compliance_by_period(): void
    {
        $this->authenticateAsBoss();
        $client = Client::create([
            'name' => 'PT Mitra Konsulin',
            'email' => 'mitra@konsulin.test',
        ]);

        \App\Models\ClientCompliance::create([
            'client_id' => $client->id,
            'period' => 'Apr 26',
            'pph_21' => 'Done',
            'pph_unifikasi' => 'Done',
            'ppn' => 'Done',
            'lk' => 'Final',
            'notes' => 'Laporan Apr 26 siap.',
        ]);

        $this->get('/dashboard?period=Apr 26')
            ->assertOk()
            ->assertSee('PT Mitra Konsulin')
            ->assertSee('Apr 26')
            ->assertSee('Laporan Apr 26 siap.');
    }

    public function test_staff_dashboard_displays_staff_workbench_and_scopes_menu(): void
    {
        $staff = $this->authenticateAsEmployee();

        $client = Client::create([
            'name' => 'PT Klien Staff',
            'email' => 'klienstaff@test.com',
        ]);

        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Audit Keuangan Internal',
            'service_type' => 'Accounting',
            'status' => 'in_progress',
            'priority' => 'high',
            'start_date' => now()->toDateString(),
            'due_date' => now()->addDays(5)->toDateString(),
        ]);

        $task = \App\Models\ProjectTask::create([
            'project_id' => $project->id,
            'assigned_to' => $staff->id,
            'title' => 'Rekonsiliasi Bank Bulan Maret',
            'priority' => 'high',
            'status' => 'in_progress',
            'due_date' => now()->addDays(2)->toDateString(),
        ]);

        $response = $this->get('/dashboard');

        $response->assertOk();
        // Staff Dashboard specific elements
        $response->assertSee('Dashboard Staff');
        $response->assertSee('Staff Workspace');
        $response->assertSee('Tugas Ditugaskan');
        $response->assertSee('Rekonsiliasi Bank Bulan Maret');
        $response->assertSee('Proyek yang Ditugaskan ke Saya');
        $response->assertSee('Audit Keuangan Internal');
        $response->assertSee('Always-on-Top Time Tracker');
        $response->assertSee('Waktu Hari Ini');

        // Must NOT see executive charts & sensitive tables
        $response->assertDontSee('Distribusi Portofolio &amp; Penugasan Klien', false);
        $response->assertDontSee('Jumlah Kontrak Klien');
        $response->assertDontSee('Matriks Kepatuhan Pajak');
        $response->assertDontSee('Beban Kerja Tim PIC');

        // Menu scoping for staff
        $response->assertSee('Proyek Saya');
        $response->assertSee('Kanban Board');
        $response->assertDontSee('Kelola Client');
        $response->assertDontSee('Project Categories');
        $response->assertDontSee('Staff / Employees');
        $response->assertDontSee('Website Content');
    }

    public function test_admin_sees_all_menus_and_executive_dashboard(): void
    {
        $this->authenticateAsBoss();

        $response = $this->get('/dashboard');

        $response->assertOk();
        // Admin sees all menus
        $response->assertSee('Dashboard');
        $response->assertSee('Kanban Board');
        $response->assertSee('Projects & Compliance');
        $response->assertSee('Kelola Client');
        $response->assertSee('Project Categories');
        $response->assertSee('Staff / Employees');
        $response->assertSee('Website Content');
        $response->assertSee('Live Landing Page');

        // Admin sees executive dashboard
        $response->assertSee('Operasional & Kepatuhan');
        $response->assertSee('Total projects');
        $response->assertSee('Clients');
    }
}

