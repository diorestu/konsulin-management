<?php

namespace App\Http\Controllers;

use App\Models\ProjectCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectCategoryController extends Controller
{
    public function index(): View
    {
        $categories = ProjectCategory::withCount('projects')->latest()->get();

        return view('project-categories.index', [
            'categories' => $categories,
            'totalCategoriesCount' => ProjectCategory::count(),
            'activeCategoriesCount' => ProjectCategory::where('is_active', true)->count(),
            'inactiveCategoriesCount' => ProjectCategory::where('is_active', false)->count(),
            'usedCategoriesCount' => ProjectCategory::has('projects')->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ProjectCategory::create($this->validated($request));

        return redirect()
            ->route('project-categories.index')
            ->with('status', 'Project category created.');
    }

    public function update(Request $request, ProjectCategory $projectCategory): RedirectResponse
    {
        $projectCategory->update($this->validated($request, $projectCategory));

        return redirect()
            ->route('project-categories.index')
            ->with('status', 'Project category updated.');
    }

    public function destroy(ProjectCategory $projectCategory): RedirectResponse
    {
        $projectCategory->delete();

        return redirect()
            ->route('project-categories.index')
            ->with('status', 'Project category deleted.');
    }

    private function validated(Request $request, ?ProjectCategory $projectCategory = null): array
    {
        $id = $projectCategory?->id ?? 'NULL';

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:project_categories,name,'.$id],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $validated['is_active'] = (bool) $validated['is_active'];

        return $validated;
    }
}
