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
}

