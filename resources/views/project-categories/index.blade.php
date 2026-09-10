<x-layouts.app title="Project Categories - Konsulin Manager">
    <div class="topbar">
        <div>
            <h1>Project Categories</h1>
            <p class="muted">Kelola kategori untuk membedakan jenis project konsultan.</p>
        </div>
        <button class="button" type="button" data-open-category-modal="create">New Category</button>
    </div>

    <section class="stats" data-animate-children>
        <div class="stat"><strong>{{ $totalCategoriesCount }}</strong><span class="muted">Total categories</span></div>
        <div class="stat"><strong>{{ $activeCategoriesCount }}</strong><span class="muted">Active</span></div>
        <div class="stat"><strong>{{ $inactiveCategoriesCount }}</strong><span class="muted">Inactive</span></div>
        <div class="stat"><strong>{{ $usedCategoriesCount }}</strong><span class="muted">Used in projects</span></div>
    </section>

    <x-datatable
        id="category"
        :columns="[
            'name' => ['label' => 'Name', 'sortable' => true],
            'description' => ['label' => 'Description', 'sortable' => true],
            'projects' => ['label' => 'Projects', 'sortable' => true],
            'status' => ['label' => 'Status', 'sortable' => true, 'sorted' => true, 'direction' => 'desc'],
            'actions' => ['label' => 'Actions', 'sortable' => false, 'align' => 'right'],
        ]"
        searchPlaceholder="Search category..."
    >
        @forelse ($categories as $category)
            <tr
                data-row
                data-name="{{ $category->name }}"
                data-description="{{ $category->description }}"
                data-projects="{{ $category->projects_count }}"
                data-status="{{ $category->is_active ? 'active' : 'inactive' }}"
                class="hover:bg-slate-50/80 transition-colors"
            >
                <td data-column="name" class="py-3.5 px-4 font-semibold text-slate-900">
                    <strong>{{ $category->name }}</strong>
                </td>
                <td data-column="description" class="py-3.5 px-4 text-slate-600">
                    {{ $category->description ?? '-' }}
                </td>
                <td data-column="projects" class="py-3.5 px-4 font-semibold text-slate-800">
                    {{ $category->projects_count }}
                </td>
                <td data-column="status" class="py-3.5 px-4">
                    <x-datatable.status :type="$category->is_active ? 'active' : 'inactive'" :label="$category->is_active ? 'active' : 'inactive'" />
                </td>
                <td data-column="actions" class="py-3.5 px-4 text-right">
                    <div class="actions justify-end">
                        <button class="button secondary small icon-only" type="button" aria-label="Edit category" title="Edit category" data-open-category-modal="edit" data-action="{{ route('project-categories.update', $category) }}" data-name="{{ $category->name }}" data-description="{{ $category->description }}" data-is-active="{{ $category->is_active ? '1' : '0' }}">
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                        </button>
                        @if (!auth()->check() || auth()->user()->isBoss() || auth()->user()->can('manage categories'))
                            <form method="POST" action="{{ route('project-categories.destroy', $category) }}" onsubmit="return confirm('Delete category {{ $category->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button class="button danger small icon-only" type="submit" aria-label="Delete category" title="Delete category">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr data-empty-row>
                <td colspan="5" class="muted py-8 text-center text-xs">No categories yet.</td>
            </tr>
        @endforelse
    </x-datatable>

    <dialog id="categoryModal">
        <div class="modal-head">
            <div>
                <h2 id="categoryModalTitle">New Category</h2>
                <p class="muted">Create dan update kategori dilakukan lewat modal.</p>
            </div>
            <button class="icon-button" type="button" data-close-category-modal aria-label="Close modal">&times;</button>
        </div>
        <form class="modal-body" id="categoryForm" method="POST" action="{{ route('project-categories.store') }}">
            @csrf
            <input type="hidden" name="_method" id="categoryFormMethod" value="POST">
            <div class="form-grid">
                <label>Name <input name="name" id="category_name" required></label>
                <label>Status
                    <select name="is_active" id="category_is_active">
                        <option value="1">active</option>
                        <option value="0">inactive</option>
                    </select>
                </label>
                <label>Description <textarea name="description" id="category_description"></textarea></label>
            </div>
            <div class="modal-actions">
                <button class="button secondary" type="button" data-close-category-modal>Cancel</button>
                <button class="button" type="submit">Save Category</button>
            </div>
        </form>
    </dialog>

    <script>
        initSimpleTable('category');

        const categoryModal = document.getElementById('categoryModal');
        const categoryForm = document.getElementById('categoryForm');
        const categoryMethod = document.getElementById('categoryFormMethod');
        const categoryTitle = document.getElementById('categoryModalTitle');

        document.querySelectorAll('[data-open-category-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                categoryForm.reset();
                if (button.dataset.openCategoryModal === 'edit') {
                    categoryForm.action = button.dataset.action;
                    categoryMethod.value = 'PUT';
                    categoryTitle.textContent = 'Edit Category';
                    document.getElementById('category_name').value = button.dataset.name || '';
                    document.getElementById('category_description').value = button.dataset.description || '';
                    document.getElementById('category_is_active').value = button.dataset.isActive || '1';
                } else {
                    categoryForm.action = @js(route('project-categories.store'));
                    categoryMethod.value = 'POST';
                    categoryTitle.textContent = 'New Category';
                }
                categoryModal.showModal();
            });
        });

        document.querySelectorAll('[data-close-category-modal]').forEach((button) => {
            button.addEventListener('click', () => categoryModal.close());
        });
    </script>
</x-layouts.app>
