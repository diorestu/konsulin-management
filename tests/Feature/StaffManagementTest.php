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
}
