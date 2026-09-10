<?php

namespace Tests\Feature;

use App\Models\ProjectCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_index_has_stats_datatable_and_modal_controls(): void
    {
        $this->authenticateAsBoss();
        ProjectCategory::create([
            'name' => 'Financial Statement',
            'description' => 'Accounting report project category.',
            'is_active' => true,
        ]);

        $this->get(route('project-categories.index'))
            ->assertOk()
            ->assertSee('Project Categories')
            ->assertSee('Live search')
            ->assertSee('Rows per page')
            ->assertSee('View columns')
            ->assertSee('New Category')
            ->assertSee('Financial Statement');
    }

    public function test_category_can_be_created_updated_and_deleted(): void
    {
        $this->authenticateAsBoss();
        $this->post(route('project-categories.store'), [
            'name' => 'Tax Compliance',
            'description' => 'Monthly and annual tax work.',
            'is_active' => '1',
        ])->assertRedirect(route('project-categories.index'));

        $category = ProjectCategory::first();

        $this->assertDatabaseHas('project_categories', [
            'name' => 'Tax Compliance',
            'is_active' => true,
        ]);

        $this->put(route('project-categories.update', $category), [
            'name' => 'Corporate Tax Compliance',
            'description' => 'Updated category.',
            'is_active' => '0',
        ])->assertRedirect(route('project-categories.index'));

        $this->assertDatabaseHas('project_categories', [
            'id' => $category->id,
            'name' => 'Corporate Tax Compliance',
            'is_active' => false,
        ]);

        $this->delete(route('project-categories.destroy', $category))
            ->assertRedirect(route('project-categories.index'));

        $this->assertDatabaseMissing('project_categories', [
            'id' => $category->id,
        ]);
    }
}
