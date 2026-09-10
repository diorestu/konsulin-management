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

    <section class="panel" data-animate-children>
        <div class="datatable-toolbar">
            <label>Live search
                <input id="categorySearch" type="search" placeholder="Search category...">
            </label>
            <label>Rows per page
                <select id="categoryRowsPerPage">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </label>
            <details class="column-filter">
                <summary>View columns</summary>
                <div class="column-filter-menu">
                    @foreach (['name' => 'Name', 'description' => 'Description', 'projects' => 'Projects', 'status' => 'Status', 'actions' => 'Actions'] as $column => $label)
                        <label><input type="checkbox" data-column-toggle="{{ $column }}" checked> {{ $label }}</label>
                    @endforeach
                </div>
            </details>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th data-column="name"><button class="sortable" type="button" data-sort="name">Name</button></th>
                        <th data-column="description"><button class="sortable" type="button" data-sort="description">Description</button></th>
                        <th data-column="projects"><button class="sortable" type="button" data-sort="projects">Projects</button></th>
                        <th data-column="status"><button class="sortable" type="button" data-sort="status">Status</button></th>
                        <th data-column="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr data-row data-name="{{ $category->name }}" data-description="{{ $category->description }}" data-projects="{{ $category->projects_count }}" data-status="{{ $category->is_active ? 'active' : 'inactive' }}">
                            <td data-column="name"><strong>{{ $category->name }}</strong></td>
                            <td data-column="description">{{ $category->description ?? '-' }}</td>
                            <td data-column="projects">{{ $category->projects_count }}</td>
                            <td data-column="status"><span class="label {{ $category->is_active ? '' : 'warning' }}">{{ $category->is_active ? 'active' : 'inactive' }}</span></td>
                            <td data-column="actions">
                                <div class="actions">
                                    <button class="button secondary small icon-only" type="button" aria-label="Edit category" title="Edit category" data-open-category-modal="edit" data-action="{{ route('project-categories.update', $category) }}" data-name="{{ $category->name }}" data-description="{{ $category->description }}" data-is-active="{{ $category->is_active ? '1' : '0' }}">
                                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                    </button>
                                    <form method="POST" action="{{ route('project-categories.destroy', $category) }}" onsubmit="return confirm('Delete category {{ $category->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button danger small icon-only" type="submit" aria-label="Delete category" title="Delete category">
                                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="muted">No categories yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-bar">
            <span class="muted" id="categoryPaginationInfo">Showing 0 categories</span>
            <div class="actions">
                <button class="button secondary small" type="button" id="categoryPrevPage">Previous</button>
                <button class="button secondary small" type="button" id="categoryNextPage">Next</button>
            </div>
        </div>
    </section>

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
