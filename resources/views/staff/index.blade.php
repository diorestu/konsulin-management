<x-layouts.app title="Staff / Employees - Konsulin Manager">
    <div class="topbar">
        <div>
            <h1>Staff / Employees</h1>
            <p class="muted">Kelola employee berdasarkan tipe accounting, tax, legal, marketing, dan it.</p>
        </div>
        <button class="button" type="button" data-open-staff-modal="create">New Staff</button>
    </div>

    <section class="stats" data-animate-children>
        <div class="stat"><strong>{{ $totalStaffCount }}</strong><span class="muted">Total staff</span></div>
        <div class="stat"><strong>{{ $activeStaffCount }}</strong><span class="muted">Active staff</span></div>
        <div class="stat"><strong>{{ $taxAccountingStaffCount }}</strong><span class="muted">Tax/accounting</span></div>
        <div class="stat"><strong>{{ $assignedStaffCount }}</strong><span class="muted">Assigned staff</span></div>
    </section>

    <section class="panel" data-animate-children>
        <div class="datatable-toolbar">
            <label>Live search
                <input id="staffSearch" type="search" placeholder="Search staff...">
            </label>
            <label>Rows per page
                <select id="staffRowsPerPage">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </label>
            <details class="column-filter">
                <summary>View columns</summary>
                <div class="column-filter-menu">
                    @foreach (['name' => 'Name', 'type' => 'Type', 'email' => 'Email', 'phone' => 'Phone', 'position' => 'Position', 'projects' => 'Projects', 'status' => 'Status', 'actions' => 'Actions'] as $column => $label)
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
                        <th data-column="type"><button class="sortable" type="button" data-sort="type">Type</button></th>
                        <th data-column="email"><button class="sortable" type="button" data-sort="email">Email</button></th>
                        <th data-column="phone">Phone</th>
                        <th data-column="position">Position</th>
                        <th data-column="projects"><button class="sortable" type="button" data-sort="projects">Projects</button></th>
                        <th data-column="status"><button class="sortable" type="button" data-sort="status">Status</button></th>
                        <th data-column="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staff as $employee)
                        <tr data-row data-name="{{ $employee->name }}" data-type="{{ $employee->type }}" data-email="{{ $employee->email }}" data-projects="{{ $employee->projects_count }}" data-status="{{ $employee->is_active ? 'active' : 'inactive' }}">
                            <td data-column="name"><strong>{{ $employee->name }}</strong></td>
                            <td data-column="type"><span class="label">{{ $employee->type }}</span></td>
                            <td data-column="email">{{ $employee->email ?? '-' }}</td>
                            <td data-column="phone">{{ $employee->phone ?? '-' }}</td>
                            <td data-column="position">{{ $employee->position ?? '-' }}</td>
                            <td data-column="projects">{{ $employee->projects_count }}</td>
                            <td data-column="status"><span class="label {{ $employee->is_active ? '' : 'warning' }}">{{ $employee->is_active ? 'active' : 'inactive' }}</span></td>
                            <td data-column="actions">
                                <div class="actions">
                                    <button class="button secondary small icon-only" type="button" aria-label="Edit staff" title="Edit staff" data-open-staff-modal="edit" data-action="{{ route('staff.update', $employee) }}" data-name="{{ $employee->name }}" data-email="{{ $employee->email }}" data-phone="{{ $employee->phone }}" data-type="{{ $employee->type }}" data-position="{{ $employee->position }}" data-is-active="{{ $employee->is_active ? '1' : '0' }}">
                                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                    </button>
                                    <form method="POST" action="{{ route('staff.destroy', $employee) }}" onsubmit="return confirm('Delete staff {{ $employee->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button danger small icon-only" type="submit" aria-label="Delete staff" title="Delete staff">
                                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="muted">No staff yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-bar">
            <span class="muted" id="staffPaginationInfo">Showing 0 staff</span>
            <div class="actions">
                <button class="button secondary small" type="button" id="staffPrevPage">Previous</button>
                <button class="button secondary small" type="button" id="staffNextPage">Next</button>
            </div>
        </div>
    </section>

    <dialog id="staffModal">
        <div class="modal-head">
            <div>
                <h2 id="staffModalTitle">New Staff</h2>
                <p class="muted">Create dan update staff dilakukan lewat modal.</p>
            </div>
            <button class="icon-button" type="button" data-close-staff-modal aria-label="Close modal">&times;</button>
        </div>
        <form class="modal-body" id="staffForm" method="POST" action="{{ route('staff.store') }}">
            @csrf
            <input type="hidden" name="_method" id="staffFormMethod" value="POST">
            <div class="form-grid">
                <label>Name <input name="name" id="staff_name" required></label>
                <label>Type
                    <select name="type" id="staff_type" required>
                        @foreach ($types as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Email <input type="email" name="email" id="staff_email"></label>
                <label>Phone <input name="phone" id="staff_phone"></label>
                <label>Position <input name="position" id="staff_position"></label>
                <label>Status
                    <select name="is_active" id="staff_is_active">
                        <option value="1">active</option>
                        <option value="0">inactive</option>
                    </select>
                </label>
            </div>
            <div class="modal-actions">
                <button class="button secondary" type="button" data-close-staff-modal>Cancel</button>
                <button class="button" type="submit">Save Staff</button>
            </div>
        </form>
    </dialog>

    <script>
        initSimpleTable('staff');

        const staffModal = document.getElementById('staffModal');
        const staffForm = document.getElementById('staffForm');
        const staffMethod = document.getElementById('staffFormMethod');
        const staffTitle = document.getElementById('staffModalTitle');

        document.querySelectorAll('[data-open-staff-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                staffForm.reset();
                if (button.dataset.openStaffModal === 'edit') {
                    staffForm.action = button.dataset.action;
                    staffMethod.value = 'PUT';
                    staffTitle.textContent = 'Edit Staff';
                    document.getElementById('staff_name').value = button.dataset.name || '';
                    document.getElementById('staff_email').value = button.dataset.email || '';
                    document.getElementById('staff_phone').value = button.dataset.phone || '';
                    document.getElementById('staff_type').value = button.dataset.type || 'accounting';
                    document.getElementById('staff_position').value = button.dataset.position || '';
                    document.getElementById('staff_is_active').value = button.dataset.isActive || '1';
                } else {
                    staffForm.action = @js(route('staff.store'));
                    staffMethod.value = 'POST';
                    staffTitle.textContent = 'New Staff';
                }
                staffModal.showModal();
            });
        });

        document.querySelectorAll('[data-close-staff-modal]').forEach((button) => {
            button.addEventListener('click', () => staffModal.close());
        });
    </script>
</x-layouts.app>
