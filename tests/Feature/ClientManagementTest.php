<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientCompliance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view clients', 'create clients', 'edit clients', 'delete clients',
            'view projects', 'create projects', 'edit projects', 'delete projects',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $bossRole = Role::firstOrCreate(['name' => 'boss', 'guard_name' => 'web']);
        $bossRole->syncPermissions(Permission::all());

        $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $employeeRole->syncPermissions([
            'view clients', 'create clients', 'edit clients',
            'view projects', 'create projects', 'edit projects',
        ]);
    }

    public function test_user_can_view_client_list(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $user->assignRole('employee');
        $this->actingAs($user);

        $client = Client::create([
            'client_code' => 'CLI-TEST-001',
            'name' => 'PT Testindo Pratama',
            'client_pic' => 'Ibu Rina',
            'location' => 'Jakarta Barat',
            'tax_status' => 'PKP',
            'contract_status' => 'Active',
            'status' => 'active',
        ]);

        $response = $this->get(route('clients.index'));
        $response->assertOk()
            ->assertSee('Kelola Client')
            ->assertSee('PT Testindo Pratama')
            ->assertSee('CLI-TEST-001')
            ->assertSee('Ibu Rina')
            ->assertSee('Jakarta Barat')
            ->assertSee('PKP');
    }

    public function test_user_can_create_client_with_all_fields_and_monthly_matrix(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $user->assignRole('employee');
        $this->actingAs($user);

        $postData = [
            'client_code' => 'CLI-TEST-002',
            'name' => 'PT Solusi Finansial Global',
            'migration_date' => '2026-03-01',
            'client_pic' => 'Bpk. Budi Santoso',
            'location' => 'Jakarta Selatan',
            'tax_status' => 'PKP',
            'business_type' => 'Fintech & Konsultasi Keuangan',
            'contract_status' => 'Active',
            'start_date' => '2026-03-01',
            'contract_duration_months' => 12,
            'end_contract_due_date' => '2027-02-28',
            'client_type' => 'Badan (PT)',
            'finance_package' => 'Premium Finance',
            'tax_package' => 'Corporate Tax Full',
            'addon' => 'SPT Tahunan & Restitusi PPN',
            'package_detail' => 'Layanan bulanan pajak & pembukuan akuntansi terintegrasi.',
            'status' => 'active',
            'files' => 'https://drive.google.com/test-files',
            'review_approval' => 'Approved',
            'tax_pic' => 'Nadia Tax',
            'accounting_pic' => 'Ari Accounting',
            'compliances' => [
                'Mar 26' => [
                    'pph_21' => 'Done',
                    'pph_unifikasi' => 'Done',
                    'ppn' => 'Done',
                    'pp_55' => 'N/A',
                    'pph_25' => 'Done',
                    'lk' => 'Final',
                    'notes' => 'Lapor tepat waktu tgl 20.',
                ],
                'Apr 26' => [
                    'pph_21' => 'In Progress',
                    'pph_unifikasi' => 'Draft',
                    'ppn' => 'Pending Faktur',
                    'pp_55' => 'N/A',
                    'pph_25' => 'Done',
                    'lk' => 'Draft',
                    'notes' => 'Menunggu data mutasi rekening koran.',
                ],
            ],
        ];

        $response = $this->post(route('clients.store'), $postData);

        $client = Client::where('client_code', 'CLI-TEST-002')->first();
        $this->assertNotNull($client);
        $response->assertRedirect(route('clients.show', $client));

        $this->assertDatabaseHas('clients', [
            'client_code' => 'CLI-TEST-002',
            'name' => 'PT Solusi Finansial Global',
            'tax_status' => 'PKP',
            'tax_pic' => 'Nadia Tax',
            'accounting_pic' => 'Ari Accounting',
        ]);

        $this->assertDatabaseHas('client_compliances', [
            'client_id' => $client->id,
            'period' => 'Mar 26',
            'pph_21' => 'Done',
            'ppn' => 'Done',
            'lk' => 'Final',
            'notes' => 'Lapor tepat waktu tgl 20.',
        ]);

        $this->assertDatabaseHas('client_compliances', [
            'client_id' => $client->id,
            'period' => 'Apr 26',
            'pph_21' => 'In Progress',
            'notes' => 'Menunggu data mutasi rekening koran.',
        ]);
    }

    public function test_user_can_view_client_detail_and_monthly_matrix(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $user->assignRole('employee');
        $this->actingAs($user);

        $client = Client::create([
            'client_code' => 'CLI-SHOW-001',
            'name' => 'PT Menara Graha Pratama',
            'client_pic' => 'Bpk. Ridwan',
            'location' => 'Surabaya',
            'tax_status' => 'PKP',
            'contract_status' => 'Active',
            'status' => 'active',
            'tax_pic' => 'Nadia Tax Consultant',
            'accounting_pic' => 'Ari Senior Accountant',
        ]);

        ClientCompliance::create([
            'client_id' => $client->id,
            'period' => 'Mar 26',
            'pph_21' => 'Done',
            'pph_unifikasi' => 'Done',
            'ppn' => 'Done',
            'pp_55' => 'N/A',
            'pph_25' => 'Done',
            'lk' => 'Final',
            'notes' => 'Semua laporan beres.',
        ]);

        $response = $this->get(route('clients.show', $client));
        $response->assertOk()
            ->assertSee('PT Menara Graha Pratama')
            ->assertSee('CLI-SHOW-001')
            ->assertSee('Matriks Kepatuhan Pajak', false)
            ->assertSee('PPh 21')
            ->assertSee('PPh Unifikasi')
            ->assertSee('PPN')
            ->assertSee('PP 55')
            ->assertSee('PPh 25')
            ->assertSee('Laporan Keuangan (LK)')
            ->assertSee('Pending / Notes')
            ->assertSee('Mar 26')
            ->assertSee('Semua laporan beres.');
    }

    public function test_employee_cannot_delete_client_but_boss_can(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $employee->assignRole('employee');

        $boss = User::factory()->create(['role' => 'boss']);
        $boss->assignRole('boss');

        $client = Client::create([
            'name' => 'PT Doomed Client',
            'status' => 'active',
        ]);

        // Employee tries to delete -> 403 Forbidden
        $this->actingAs($employee);
        $this->delete(route('clients.destroy', $client))->assertForbidden();
        $this->assertDatabaseHas('clients', ['id' => $client->id]);

        // Boss deletes -> Redirects with success
        $this->actingAs($boss);
        $response = $this->delete(route('clients.destroy', $client));
        $response->assertRedirect(route('clients.index'));
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }
}
