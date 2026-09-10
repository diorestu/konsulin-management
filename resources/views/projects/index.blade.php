<x-layouts.app title="Projects - Konsulin Manager">
    <div class="topbar">
        <div>
            <h1>Projects</h1>
            <p class="muted">Monitor client tax and accounting work, task progress, and open threats.</p>
        </div>
        <button class="button" type="button" data-open-project-modal="create">New Project</button>
    </div>

    <section class="stats" data-animate-children>
        <div class="stat">
            <strong>{{ $totalProjectsCount }}</strong>
            <span class="muted">Total projects</span>
        </div>
        <div class="stat">
            <strong>{{ $clientsCount }}</strong>
            <span class="muted">Clients</span>
        </div>
        <div class="stat">
            <strong>{{ $activeProjectsCount }}</strong>
            <span class="muted">Active projects</span>
        </div>
        <div class="stat">
            <strong>{{ $openThreatsCount }}</strong>
            <span class="muted">Open threats</span>
        </div>
    </section>

    <x-datatable
        id="project"
        :columns="[
            'client' => ['label' => 'Client', 'sortable' => true],
            'project' => ['label' => 'Project', 'sortable' => true],
            'category' => ['label' => 'Category', 'sortable' => true, 'info' => 'Kategori layanan'],
            'staff' => ['label' => 'Staff', 'sortable' => true],
            'service' => ['label' => 'Service', 'sortable' => true],
            'status' => ['label' => 'Status', 'sortable' => true, 'sorted' => true, 'direction' => 'desc'],
            'priority' => ['label' => 'Priority', 'sortable' => true],
            'trend' => ['label' => 'Trend', 'sortable' => false],
            'progress' => ['label' => 'Progress', 'sortable' => true],
            'due' => ['label' => 'Due', 'sortable' => true],
            'actions' => ['label' => 'Actions', 'sortable' => false, 'align' => 'right'],
        ]"
        searchPlaceholder="Search client, project, service, status..."
    >
        @forelse ($projects as $project)
            <tr
                data-row
                data-client="{{ $project->client->name }}"
                data-project="{{ $project->name }}"
                data-category="{{ $project->category?->name }}"
                data-staff="{{ $project->staff->pluck('name')->join(' ') }}"
                data-service="{{ $project->service_type }}"
                data-status="{{ $project->status }}"
                data-priority="{{ $project->priority }}"
                data-progress="{{ $project->progressPercent() }}"
                data-due="{{ $project->due_date?->format('Y-m-d') ?? '' }}"
                class="hover:bg-slate-50/80 transition-colors"
            >
                <td data-column="client" class="py-3.5 px-4 font-semibold text-slate-900">
                    {{ $project->client->name }}
                </td>
                <td data-column="project" class="py-3.5 px-4">
                    <a href="{{ route('projects.show', $project) }}" class="font-semibold text-slate-900 hover:text-[#1e3e62]">
                        <strong>{{ $project->name }}</strong>
                    </a>
                    <div class="muted text-xs mt-0.5">{{ $project->tasks->count() }} tasks · {{ $project->threats->where('status', 'open')->count() }} open threats</div>
                    @if ($project->tasks->isNotEmpty())
                        <div class="muted text-xs">Latest task: {{ $project->tasks->first()->title }}</div>
                    @endif
                </td>
                <td data-column="category" class="py-3.5 px-4 text-slate-600">{{ $project->category?->name ?? '-' }}</td>
                <td data-column="staff" class="py-3.5 px-4">
                    @forelse ($project->staff as $assignedStaff)
                        <span class="label text-[11px]">{{ $assignedStaff->name }} · {{ $assignedStaff->type }}</span>
                    @empty
                        <span class="muted text-xs">Unassigned</span>
                    @endforelse
                </td>
                <td data-column="service" class="py-3.5 px-4 text-slate-700 font-medium">{{ $project->service_type }}</td>
                <td data-column="status" class="py-3.5 px-4">
                    <x-datatable.status :type="$project->status" />
                </td>
                <td data-column="priority" class="py-3.5 px-4">
                    <span class="label {{ in_array($project->priority, ['high', 'urgent'], true) ? 'warning' : '' }} text-xs">
                        {{ $project->priority }}
                    </span>
                </td>
                <td data-column="trend" class="py-3.5 px-4">
                    <x-datatable.sparkline :trend="$project->progressPercent() >= 50 ? 'peak' : ($project->progressPercent() > 0 ? 'up' : 'flat')" />
                </td>
                <td data-column="progress" class="py-3.5 px-4">
                    <div class="progress" aria-label="Project progress" style="margin: 0 0 4px; width: 80px;">
                        <span style="width: {{ $project->progressPercent() }}%"></span>
                    </div>
                    <strong class="text-xs text-slate-800">{{ $project->progressPercent() }}%</strong>
                </td>
                <td data-column="due" class="py-3.5 px-4 text-xs text-slate-600 whitespace-nowrap">{{ $project->due_date?->format('d M Y') ?? '-' }}</td>
                <td data-column="actions" class="py-3.5 px-4 text-right">
                    <div class="actions justify-end">
                        <a class="button secondary small icon-only" href="{{ route('projects.show', $project) }}" aria-label="View project" title="View Jira Board">
                            <x-heroicon-o-squares-2x2 class="w-4 h-4" />
                        </a>
                        <button
                            class="button secondary small icon-only"
                            type="button"
                            aria-label="Edit project"
                            title="Edit project"
                            data-open-project-modal="edit"
                            data-action="{{ route('projects.update', $project) }}"
                            data-client-name="{{ $project->client->name }}"
                            data-client-email="{{ $project->client->email }}"
                            data-client-phone="{{ $project->client->phone }}"
                            data-client-tax-id="{{ $project->client->tax_id }}"
                            data-project-category-id="{{ $project->project_category_id }}"
                            data-staff-ids="{{ $project->staff->pluck('id')->join(',') }}"
                            data-name="{{ $project->name }}"
                            data-service-type="{{ $project->service_type }}"
                            data-status="{{ $project->status }}"
                            data-priority="{{ $project->priority }}"
                            data-start-date="{{ $project->start_date?->format('Y-m-d') }}"
                            data-due-date="{{ $project->due_date?->format('Y-m-d') }}"
                            data-created-by="{{ $project->created_by }}"
                            data-description="{{ $project->description }}"
                        >
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                        </button>
                        @if (!auth()->check() || auth()->user()->isBoss() || auth()->user()->can('delete projects'))
                            <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Delete project {{ $project->name }}? This will also remove its tasks, progress updates, and threats.');">
                                @csrf
                                @method('DELETE')
                                <button class="button danger small icon-only" type="submit" aria-label="Delete project" title="Delete project">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr data-empty-row>
                <td colspan="11" class="muted py-8 text-center text-xs">
                    No projects yet. Create the first client project to start tracking tasks, progress, and threats.
                </td>
            </tr>
        @endforelse
    </x-datatable>

    <dialog id="projectModal">
        <div class="modal-head">
            <div>
                <h2 id="projectModalTitle">New Project</h2>
                <p class="muted">Use the wizard so each step stays compact.</p>
            </div>
            <button class="icon-button" type="button" data-close-project-modal aria-label="Close modal">&times;</button>
        </div>
        <form class="modal-body" id="projectForm" method="POST" action="{{ route('projects.store') }}">
            @csrf
            <input type="hidden" name="_method" id="projectFormMethod" value="POST">

            <div class="wizard-steps" aria-label="Project form steps">
                <button class="wizard-pill active" type="button" data-wizard-go="0">1. Client</button>
                <button class="wizard-pill" type="button" data-wizard-go="1">2. Project</button>
                <button class="wizard-pill" type="button" data-wizard-go="2">3. Assignment</button>
            </div>

            <section class="wizard-step" data-wizard-step="0">
                <div class="form-grid">
                    <label id="existingClientField">Existing client
                        <select name="client_id" id="client_id">
                            <option value="">Create new client</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Client name
                        <input name="client_name" id="client_name">
                    </label>
                    <label>Email
                        <input type="email" name="client_email" id="client_email">
                    </label>
                    <label>Phone
                        <input name="client_phone" id="client_phone">
                    </label>
                    <label>Tax ID / NPWP
                        <input name="client_tax_id" id="client_tax_id">
                    </label>
                </div>
            </section>

            <section class="wizard-step" data-wizard-step="1" hidden>
                <div class="form-grid">
                    <label>Project name
                        <input name="name" id="name" required>
                    </label>
                    <label>Project category
                        <select name="project_category_id" id="project_category_id">
                            <option value="">Uncategorized</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Service type
                        <select name="service_type" id="service_type" required>
                            @foreach (['Tax', 'Accounting', 'Audit Support', 'Payroll'] as $type)
                                <option>{{ $type }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Status
                        <select name="status" id="status" required>
                            @foreach (['not_started', 'in_progress', 'waiting_client', 'completed'] as $status)
                                <option value="{{ $status }}">{{ str_replace('_', ' ', $status) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Priority
                        <select name="priority" id="priority" required>
                            @foreach (['low', 'medium', 'high', 'urgent'] as $priority)
                                <option value="{{ $priority }}">{{ $priority }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Start date
                        <input type="date" name="start_date" id="start_date">
                    </label>
                    <label>Due date
                        <input type="date" name="due_date" id="due_date">
                    </label>
                </div>
            </section>

            <section class="wizard-step" data-wizard-step="2" hidden>
                <div class="form-grid">
                    <label>Boss
                        <select name="created_by" id="created_by">
                            <option value="">Unassigned</option>
                            @foreach ($bosses as $boss)
                                <option value="{{ $boss->id }}">{{ $boss->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Assigned staff
                        <select name="staff_ids[]" id="staff_ids" multiple size="6">
                            @foreach ($staff as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }} · {{ $employee->type }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Description
                        <textarea name="description" id="description"></textarea>
                    </label>
                </div>
            </section>

            <div class="modal-actions">
                <button class="button secondary" type="button" data-wizard-prev>Previous</button>
                <div class="actions">
                    <button class="button secondary" type="button" data-wizard-next>Next</button>
                    <button class="button" type="submit" data-wizard-submit hidden>Save Project</button>
                </div>
            </div>
        </form>
    </dialog>

    <script>
        const tableRows = Array.from(document.querySelectorAll('[data-row]'));
        const searchInput = document.getElementById('projectSearch');
        const rowsPerPageSelect = document.getElementById('projectRowsPerPage');
        const paginationInfo = document.getElementById('projectPaginationInfo');
        const prevPageButton = document.getElementById('projectPrevPage');
        const nextPageButton = document.getElementById('projectNextPage');
        let currentPage = 1;
        let sortKey = 'project';
        let sortDirection = 'asc';

        function normalized(value) {
            return String(value ?? '').toLowerCase();
        }

        function visibleRows() {
            const term = normalized(searchInput.value);
            return tableRows
                .filter((row) => normalized(row.textContent).includes(term))
                .sort((a, b) => {
                    const left = a.dataset[sortKey] ?? '';
                    const right = b.dataset[sortKey] ?? '';
                    const result = sortKey === 'progress'
                        ? Number(left) - Number(right)
                        : left.localeCompare(right);

                    return sortDirection === 'asc' ? result : -result;
                });
        }

        function setRowVisibility(row, visible) {
            if (visible) {
                row.hidden = false;
                row.classList.remove('is-exiting');
                row.classList.add('is-entering');
                row.addEventListener('animationend', () => row.classList.remove('is-entering'), { once: true });
                return;
            }

            row.classList.add('is-exiting');
            setTimeout(() => {
                row.hidden = true;
                row.classList.remove('is-exiting');
            }, 80);
        }

        function renderTable() {
            const rows = visibleRows();
            const perPage = Number(rowsPerPageSelect.value);
            const totalPages = Math.max(1, Math.ceil(rows.length / perPage));
            currentPage = Math.min(currentPage, totalPages);
            const start = (currentPage - 1) * perPage;
            const end = start + perPage;

            tableRows.forEach((row) => setRowVisibility(row, false));
            rows.slice(start, end).forEach((row) => setRowVisibility(row, true));

            paginationInfo.textContent = rows.length
                ? `Showing ${start + 1}-${Math.min(end, rows.length)} of ${rows.length} projects`
                : 'Showing 0 projects';
            prevPageButton.disabled = currentPage <= 1;
            nextPageButton.disabled = currentPage >= totalPages;
        }

        document.querySelectorAll('[data-sort]').forEach((button) => {
            button.addEventListener('click', () => {
                if (sortKey === button.dataset.sort) {
                    sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    sortKey = button.dataset.sort;
                    sortDirection = 'asc';
                }
                renderTable();
            });
        });

        document.querySelectorAll('[data-column-toggle]').forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                document.querySelectorAll(`[data-column="${checkbox.dataset.columnToggle}"]`)
                    .forEach((cell) => cell.classList.toggle('hidden-column', !checkbox.checked));
            });
        });

        searchInput.addEventListener('input', () => {
            currentPage = 1;
            renderTable();
        });
        rowsPerPageSelect.addEventListener('change', () => {
            currentPage = 1;
            renderTable();
        });
        prevPageButton.addEventListener('click', () => {
            currentPage -= 1;
            renderTable();
        });
        nextPageButton.addEventListener('click', () => {
            currentPage += 1;
            renderTable();
        });

        const modal = document.getElementById('projectModal');
        const form = document.getElementById('projectForm');
        const methodInput = document.getElementById('projectFormMethod');
        const title = document.getElementById('projectModalTitle');
        const existingClientField = document.getElementById('existingClientField');
        let wizardStep = 0;

        function setWizardStep(step) {
            wizardStep = step;
            document.querySelectorAll('[data-wizard-step]').forEach((section, index) => {
                section.hidden = index !== wizardStep;
            });
            document.querySelectorAll('[data-wizard-go]').forEach((button, index) => {
                button.classList.toggle('active', index === wizardStep);
            });
            document.querySelector('[data-wizard-prev]').disabled = wizardStep === 0;
            document.querySelector('[data-wizard-next]').hidden = wizardStep === 2;
            document.querySelector('[data-wizard-submit]').hidden = wizardStep !== 2;
        }

        function setValue(id, value) {
            const field = document.getElementById(id);
            if (field) field.value = value ?? '';
        }

        function openCreateModal() {
            form.reset();
            form.action = @js(route('projects.store'));
            methodInput.value = 'POST';
            title.textContent = 'New Project';
            existingClientField.hidden = false;
            setWizardStep(0);
            modal.showModal();
        }

        function openEditModal(button) {
            form.reset();
            form.action = button.dataset.action;
            methodInput.value = 'PUT';
            title.textContent = 'Edit Project';
            existingClientField.hidden = true;
            setValue('client_id', '');
            setValue('client_name', button.dataset.clientName);
            setValue('client_email', button.dataset.clientEmail);
            setValue('client_phone', button.dataset.clientPhone);
            setValue('client_tax_id', button.dataset.clientTaxId);
            setValue('project_category_id', button.dataset.projectCategoryId);
            const selectedStaffIds = (button.dataset.staffIds || '').split(',').filter(Boolean);
            Array.from(document.getElementById('staff_ids').options).forEach((option) => {
                option.selected = selectedStaffIds.includes(option.value);
            });
            setValue('name', button.dataset.name);
            setValue('service_type', button.dataset.serviceType);
            setValue('status', button.dataset.status);
            setValue('priority', button.dataset.priority);
            setValue('start_date', button.dataset.startDate);
            setValue('due_date', button.dataset.dueDate);
            setValue('created_by', button.dataset.createdBy);
            setValue('description', button.dataset.description);
            setWizardStep(0);
            modal.showModal();
        }

        document.querySelectorAll('[data-open-project-modal]').forEach((button) => {
            button.addEventListener('click', () => {
                button.dataset.openProjectModal === 'edit' ? openEditModal(button) : openCreateModal();
            });
        });
        document.querySelector('[data-close-project-modal]').addEventListener('click', () => modal.close());
        document.querySelector('[data-wizard-prev]').addEventListener('click', () => setWizardStep(Math.max(0, wizardStep - 1)));
        document.querySelector('[data-wizard-next]').addEventListener('click', () => setWizardStep(Math.min(2, wizardStep + 1)));
        document.querySelectorAll('[data-wizard-go]').forEach((button) => {
            button.addEventListener('click', () => setWizardStep(Number(button.dataset.wizardGo)));
        });

        renderTable();
    </script>
</x-layouts.app>
