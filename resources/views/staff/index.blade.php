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

    <x-datatable
        id="staff"
        :columns="[
            'name' => ['label' => 'Name', 'sortable' => true],
            'type' => ['label' => 'Type', 'sortable' => true, 'info' => 'Role spesialisasi'],
            'email' => ['label' => 'Email', 'sortable' => true],
            'phone' => ['label' => 'Phone', 'sortable' => false],
            'position' => ['label' => 'Position', 'sortable' => false],
            'projects' => ['label' => 'Projects', 'sortable' => true],
            'status' => ['label' => 'Status', 'sortable' => true, 'sorted' => true, 'direction' => 'desc'],
            'actions' => ['label' => 'Actions', 'sortable' => false, 'align' => 'right'],
        ]"
        searchPlaceholder="Search staff..."
    >
        @forelse ($staff as $employee)
            <tr
                data-row
                data-name="{{ $employee->name }}"
                data-type="{{ $employee->type }}"
                data-email="{{ $employee->email }}"
                data-projects="{{ $employee->projects_count }}"
                data-status="{{ $employee->is_active ? 'active' : 'inactive' }}"
                class="hover:bg-slate-50/80 transition-colors"
            >
                <td data-column="name" class="py-3.5 px-4 font-semibold text-slate-900">
                    <strong>{{ $employee->name }}</strong>
                </td>
                <td data-column="type" class="py-3.5 px-4">
                    <span class="label text-[11px]">{{ $employee->type }}</span>
                </td>
                <td data-column="email" class="py-3.5 px-4 text-slate-600">{{ $employee->email ?? '-' }}</td>
                <td data-column="phone" class="py-3.5 px-4 text-slate-600">{{ $employee->phone ?? '-' }}</td>
                <td data-column="position" class="py-3.5 px-4 text-slate-600 font-medium">{{ $employee->position ?? '-' }}</td>
                <td data-column="projects" class="py-3.5 px-4 font-semibold text-slate-800">{{ $employee->projects_count }}</td>
                <td data-column="status" class="py-3.5 px-4">
                    <x-datatable.status :type="$employee->is_active ? 'active' : 'inactive'" :label="$employee->is_active ? 'active' : 'inactive'" />
                </td>
                <td data-column="actions" class="py-3.5 px-4 text-right">
                    <div class="actions justify-end">
                        <button class="button secondary small icon-only" type="button" aria-label="Edit staff" title="Edit staff" data-open-staff-modal="edit" data-action="{{ route('staff.update', $employee) }}" data-name="{{ $employee->name }}" data-email="{{ $employee->email }}" data-phone="{{ $employee->phone }}" data-type="{{ $employee->type }}" data-position="{{ $employee->position }}" data-is-active="{{ $employee->is_active ? '1' : '0' }}">
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                        </button>
                        @if (!auth()->check() || auth()->user()->isBoss() || auth()->user()->can('manage staff'))
                            <form method="POST" action="{{ route('staff.destroy', $employee) }}" onsubmit="return confirm('Delete staff {{ $employee->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button class="button danger small icon-only" type="submit" aria-label="Delete staff" title="Delete staff">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr data-empty-row>
                <td colspan="8" class="muted py-8 text-center text-xs">No staff yet.</td>
            </tr>
        @endforelse
    </x-datatable>

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
