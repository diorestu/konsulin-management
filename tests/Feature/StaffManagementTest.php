<?php

namespace Tests\Feature;

use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_index_has_stats_datatable_and_modal_controls(): void
    {
        $this->authenticateAsBoss();
        Staff::create([
            'name' => 'Nadia Tax',
            'email' => 'nadia@konsulin.test',
            'phone' => '081234',
            'type' => 'tax',
            'position' => 'Tax Consultant',
            'is_active' => true,
        ]);

        $this->get(route('staff.index'))
            ->assertOk()
            ->assertSee('Staff / Employees')
            ->assertSee('Live search')
            ->assertSee('Rows per page')
            ->assertSee('View columns')
            ->assertSee('New Staff')
            ->assertSee('Nadia Tax')
            ->assertSee('tax');
    }

    public function test_staff_can_be_created_updated_and_deleted(): void
    {
        $this->authenticateAsBoss();
        $this->post(route('staff.store'), [
            'name' => 'Ari Accounting',
            'email' => 'ari@konsulin.test',
            'phone' => '08222',
            'type' => 'accounting',
            'position' => 'Senior Accountant',
            'is_active' => '1',
        ])->assertRedirect(route('staff.index'));

        $staff = Staff::first();

        $this->assertDatabaseHas('staff', [
            'name' => 'Ari Accounting',
            'type' => 'accounting',
        ]);

        $this->put(route('staff.update', $staff), [
            'name' => 'Ari Tax',
            'email' => 'ari.tax@konsulin.test',
            'phone' => '08333',
            'type' => 'tax',
            'position' => 'Tax Specialist',
            'is_active' => '0',
        ])->assertRedirect(route('staff.index'));

        $this->assertDatabaseHas('staff', [
            'id' => $staff->id,
            'name' => 'Ari Tax',
            'type' => 'tax',
            'is_active' => false,
        ]);

        $this->delete(route('staff.destroy', $staff))
            ->assertRedirect(route('staff.index'));

        $this->assertDatabaseMissing('staff', [
            'id' => $staff->id,
        ]);
    }

    public function test_staff_index_shows_workload_and_handled_clients_summary(): void
    {
        $this->authenticateAsBoss();

        $clientA = \App\Models\Client::create([
            'name' => 'PT Maju Bersama',
            'status' => 'active',
        ]);
        $clientB = \App\Models\Client::create([
            'name' => 'CV Sukses Mandiri',
            'status' => 'active',
        ]);

        $staff = Staff::create([
            'name' => 'Budi Konsultan',
            'email' => 'budi@konsulin.test',
            'type' => 'tax',
            'position' => 'Senior Tax Consultant',
            'is_active' => true,
        ]);

        $project1 = \App\Models\Project::create([
            'client_id' => $clientA->id,
            'name' => 'Audit SPT Tahunan Badan',
            'service_type' => 'Tax Filing',
            'status' => 'in_progress',
            'priority' => 'high',
        ]);
        $project2 = \App\Models\Project::create([
            'client_id' => $clientB->id,
            'name' => 'Restrukturisasi Pajak',
            'service_type' => 'Tax Advisory',
            'status' => 'in_progress',
            'priority' => 'medium',
        ]);
        $project3 = \App\Models\Project::create([
            'client_id' => $clientB->id,
            'name' => 'Penyusunan Bukpot PPh 21',
            'service_type' => 'Tax Filing',
            'status' => 'completed',
            'priority' => 'low',
        ]);

        $staff->projects()->attach([$project1->id, $project2->id, $project3->id]);

        $response = $this->get(route('staff.index'));

        $response->assertOk()
            ->assertSee('Budi Konsultan')
            ->assertSee('Total Tim Staff')
            ->assertSee('Klien Ditangani')
            ->assertSee('Beban Proyek Aktif')
            ->assertSee('Kapasitas Tim')
            ->assertSee('Optimal')
            ->assertSee('PT Maju Bersama')
            ->assertSee('CV Sukses Mandiri')
            ->assertSee('2')
            ->assertSee('Klien');
    }
}
