<x-layouts.app :title="'Kanban Board Project Management'">
    <div class="kanban-page page-transition">
        <!-- Topbar & Action Header -->
        <div class="topbar">
            <div>
                <div class="page-eyebrow">Project Management Workspace</div>
                <h1 class="page-title">Kanban Board Project</h1>
                <p class="page-desc">Visualisasi alur kerja project tax & accounting konsulin dari perencanaan hingga penyelesaian.</p>
            </div>
            <div class="actions">
                <a href="{{ route('projects.index') }}" class="button secondary">
                    <x-heroicon-o-table-cells class="w-4 h-4" />
                    <span>Compliance Matrix</span>
                </a>
                <button type="button" class="button" onclick="openProjectModal()">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    <span>Buat Project Baru</span>
                </button>
            </div>
        </div>

        <!-- Metrics Overview Ribbon -->
        <div class="metrics-ribbon">
            <div class="metric-item">
                <div class="metric-meta">
                    <span class="metric-label">Total Project</span>
                    <span class="metric-icon"><x-heroicon-o-folder class="w-4 h-4 text-slate-500" /></span>
                </div>
                <div class="metric-value">{{ $metrics['total'] }}</div>
                <div class="metric-sub">Seluruh project terdaftar</div>
            </div>
            <div class="metric-item">
                <div class="metric-meta">
                    <span class="metric-label">Sedang Berjalan</span>
                    <span class="metric-icon"><x-heroicon-o-play class="w-4 h-4 text-blue-600" /></span>
                </div>
                <div class="metric-value text-blue-600">{{ $metrics['in_progress'] }}</div>
                <div class="metric-sub">Pengerjaan aktif staff</div>
            </div>
            <div class="metric-item">
                <div class="metric-meta">
                    <span class="metric-label">Menunggu Klien</span>
                    <span class="metric-icon"><x-heroicon-o-clock class="w-4 h-4 text-amber-600" /></span>
                </div>
                <div class="metric-value text-amber-600">{{ $metrics['waiting_client'] }}</div>
                <div class="metric-sub">Pending review / berkas</div>
            </div>
            <div class="metric-item">
                <div class="metric-meta">
                    <span class="metric-label">Selesai</span>
                    <span class="metric-icon"><x-heroicon-o-check-circle class="w-4 h-4 text-emerald-600" /></span>
                </div>
                <div class="metric-value text-emerald-600">{{ $metrics['completed'] }}</div>
                <div class="metric-sub">Deliverable final terkirim</div>
            </div>
            @if ($metrics['open_threats'] > 0)
                <div class="metric-item alert-threat">
                    <div class="metric-meta">
                        <span class="metric-label">Active Threats</span>
                        <span class="metric-icon"><x-heroicon-o-exclamation-triangle class="w-4 h-4 text-red-600" /></span>
                    </div>
                    <div class="metric-value text-red-600">{{ $metrics['open_threats'] }}</div>
                    <div class="metric-sub">Kendala butuh resolusi</div>
                </div>
            @endif
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="kanban-toolbar">
            <div class="search-box">
                <x-heroicon-o-magnifying-glass class="search-icon" />
                <input
                    type="text"
                    id="kanbanSearchInput"
                    placeholder="Cari nama project, klien, atau kode..."
                    value="{{ $filters['search'] }}"
                    aria-label="Cari nama project atau klien"
                    autocomplete="off"
                    class="kanban-search-input"
                >
                <button type="button" id="clearSearchBtn" class="clear-search-btn" style="display: none;" title="Hapus pencarian">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            </div>

            <div class="filter-group">
                <div class="filter-item">
                    <label for="filterServiceType" class="sr-only">Layanan</label>
                    <select id="filterServiceType" aria-label="Filter Layanan">
                        <option value="">Semua Layanan</option>
                        @foreach ($serviceTypes as $service)
                            <option value="{{ $service }}" @selected($filters['service_type'] === $service)>{{ $service }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-item">
                    <label for="filterPriority" class="sr-only">Prioritas</label>
                    <select id="filterPriority" aria-label="Filter Prioritas">
                        <option value="">Semua Prioritas</option>
                        <option value="urgent" @selected($filters['priority'] === 'urgent')>Urgent</option>
                        <option value="high" @selected($filters['priority'] === 'high')>High</option>
                        <option value="medium" @selected($filters['priority'] === 'medium')>Medium</option>
                        <option value="low" @selected($filters['priority'] === 'low')>Low</option>
                    </select>
                </div>

                <div class="filter-item">
                    <label for="filterReviewer" class="sr-only">Reviewer</label>
                    <select id="filterReviewer" aria-label="Filter Reviewer">
                        <option value="">Semua Reviewer</option>
                        @foreach ($reviewers as $reviewer)
                            <option value="{{ $reviewer->id }}" @selected($filters['reviewer_id'] == $reviewer->id)>{{ $reviewer->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-item">
                    <label for="filterClient" class="sr-only">Klien</label>
                    <select id="filterClient" aria-label="Filter Klien">
                        <option value="">Semua Klien</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @selected($filters['client_id'] == $client->id)>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" id="resetFiltersBtn" class="button secondary small" title="Kembalikan semua filter ke awal">
                    <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                    <span>Reset Filter</span>
                </button>
            </div>
        </div>

        <!-- Notification Toast (Live status updates) -->
        <div id="kanbanToast" class="kanban-toast" role="status" aria-live="polite"></div>

        <!-- Global Empty Filter State -->
        <div id="globalEmptyState" class="global-empty-state" style="display: none;">
            <div class="empty-icon-wrap">
                <x-heroicon-o-folder-minus class="w-8 h-8 text-slate-400" />
            </div>
            <h3 class="empty-title">Tidak ada project yang cocok</h3>
            <p class="empty-desc">Tidak ditemukan project dengan kriteria pencarian atau filter yang dipilih.</p>
            <button type="button" class="button secondary small" onclick="resetAllFilters()">
                <x-heroicon-o-arrow-path class="w-4 h-4" />
                <span>Reset Semua Filter</span>
            </button>
        </div>

        <!-- Kanban Board Columns Container -->
        <div class="kanban-board" id="kanbanBoard">
            @foreach ($columns as $columnId => $col)
                <div
                    class="kanban-column"
                    data-column-id="{{ $columnId }}"
                    ondragover="handleDragOver(event)"
                    ondragenter="handleDragEnter(event)"
                    ondragleave="handleDragLeave(event)"
                    ondrop="handleDrop(event, '{{ $columnId }}')"
                >
                    <!-- Column Header -->
                    <div class="column-header">
                        <div class="col-title-wrap">
                            <span class="status-indicator" style="background-color: {{ $col['accent'] }};"></span>
                            <h2 class="column-title">{{ $col['title'] }}</h2>
                            <span class="column-counter" id="count-{{ $columnId }}">{{ $col['projects']->count() }}</span>
                        </div>
                        <div class="column-subtitle">{{ $col['subtitle'] }}</div>
                    </div>

                    <!-- Column Cards Container -->
                    <div class="column-cards" id="cards-{{ $columnId }}">
                        @forelse ($col['projects'] as $project)
                            @php
                                $totalTasks = $project->tasks->count();
                                $completedTasks = $project->tasks->where('status', 'done')->count();
                                $progressPercent = $project->progressPercent();
                                $openThreats = $project->threats->where('status', 'open')->count();
                                $isOverdue = $project->due_date && $project->due_date->isPast() && $project->status !== 'completed';
                                $reviewerUser = $project->reviewer ?? $project->creator;
                            @endphp

                            <article
                                class="kanban-card"
                                id="card-project-{{ $project->id }}"
                                data-project-id="{{ $project->id }}"
                                data-project-name="{{ strtolower($project->name) }}"
                                data-client-name="{{ strtolower($project->client?->name ?? '') }}"
                                data-client-id="{{ $project->client_id }}"
                                data-service-type="{{ $project->service_type }}"
                                data-priority="{{ $project->priority }}"
                                data-reviewer-id="{{ $project->reviewer_id ?? $project->created_by }}"
                                data-status="{{ $project->status }}"
                                draggable="true"
                                ondragstart="handleDragStart(event, {{ $project->id }})"
                                ondragend="handleDragEnd(event)"
                                tabindex="0"
                                aria-label="Project {{ $project->name }}"
                            >
                                <!-- Card Header -->
                                <div class="card-header">
                                    <div class="code-and-priority">
                                        <span class="project-code">PRJ-#{{ $project->id }}</span>
                                        <span class="priority-badge priority-{{ $project->priority }}">
                                            {{ ucfirst($project->priority) }}
                                        </span>
                                    </div>

                                    <!-- Quick Status Move Menu (Accessible for keyboard) -->
                                    <div class="card-quick-actions">
                                        <label for="quick-move-{{ $project->id }}" class="sr-only">Pindah status project</label>
                                        <select
                                            id="quick-move-{{ $project->id }}"
                                            class="quick-move-select"
                                            title="Pindahkan status project"
                                            onchange="moveProject({{ $project->id }}, this.value)"
                                        >
                                            <option value="" disabled selected>Pindah...</option>
                                            <option value="not_started" @disabled($project->status === 'not_started')>To Do</option>
                                            <option value="in_progress" @disabled($project->status === 'in_progress')>In Progress</option>
                                            <option value="waiting_client" @disabled($project->status === 'waiting_client')>Waiting Client</option>
                                            <option value="completed" @disabled($project->status === 'completed')>Done</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Card Title & Client -->
                                <div class="card-body">
                                    <h3 class="card-title">
                                        <a href="{{ route('projects.show', $project) }}" class="card-link" title="Buka detail project">
                                            {{ $project->name }}
                                        </a>
                                    </h3>

                                    <div class="card-client">
                                        <x-heroicon-o-building-office class="client-icon" />
                                        @if ($project->client)
                                            <a href="{{ route('clients.show', $project->client) }}" class="client-link">
                                                {{ $project->client->name }}
                                            </a>
                                            @if ($project->client->client_pic)
                                                <span class="client-pic-sub">({{ $project->client->client_pic }})</span>
                                            @endif
                                        @else
                                            <span class="client-link text-slate-400">Tidak terhubung klien</span>
                                        @endif
                                    </div>

                                    <div class="service-pill">
                                        <x-heroicon-o-tag class="service-icon" />
                                        <span>{{ $project->service_type }}</span>
                                    </div>
                                </div>

                                <!-- Team Assignment Section (Reviewer, PIC Accounting, PIC Tax) -->
                                <div class="card-team">
                                    <div class="team-role-item" title="Reviewer Project">
                                        <span class="role-tag role-reviewer">REV</span>
                                        <span class="member-name">{{ $reviewerUser?->name ?? 'Belum ditentukan' }}</span>
                                    </div>

                                    @if ($project->accountingStaff->isNotEmpty())
                                        <div class="team-role-item" title="PIC Accounting">
                                            <span class="role-tag role-accounting">ACC</span>
                                            <span class="member-name">
                                                {{ $project->accountingStaff->pluck('name')->join(', ') }}
                                            </span>
                                        </div>
                                    @endif

                                    @if ($project->taxStaff->isNotEmpty())
                                        <div class="team-role-item" title="PIC Tax">
                                            <span class="role-tag role-tax">TAX</span>
                                            <span class="member-name">
                                                {{ $project->taxStaff->pluck('name')->join(', ') }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Progress & Warning Indicators -->
                                <div class="card-meta">
                                    <div class="meta-row">
                                        <div class="task-count">
                                            <x-heroicon-o-check-badge class="meta-icon" />
                                            <span>{{ $completedTasks }}/{{ $totalTasks }} tugas ({{ $progressPercent }}%)</span>
                                        </div>

                                        @if ($openThreats > 0)
                                            <div class="threat-indicator" title="{{ $openThreats }} kendala aktif pada project ini">
                                                <x-heroicon-o-exclamation-triangle class="threat-icon" />
                                                <span>{{ $openThreats }} Threat</span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="card-progress-bar" role="progressbar" aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-fill" style="width: {{ $progressPercent }}%;"></div>
                                    </div>

                                    <!-- Due Date & Footer Controls -->
                                    <div class="card-footer-row">
                                        <div class="due-date-pill {{ $isOverdue ? 'is-overdue' : '' }}" title="{{ $isOverdue ? 'Melewati batas waktu' : 'Jatuh tempo' }}">
                                            <x-heroicon-o-calendar class="due-icon" />
                                            <span>{{ $project->due_date ? $project->due_date->format('d M Y') : 'Tanpa batas' }}</span>
                                        </div>

                                        <a href="{{ route('projects.show', $project) }}" class="card-detail-btn" title="Buka detail project">
                                            <span>Detail</span>
                                            <x-heroicon-o-arrow-right class="w-3.5 h-3.5" />
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="column-empty" id="empty-{{ $columnId }}">
                                <x-heroicon-o-inbox class="w-6 h-6 text-slate-300" />
                                <span>Belum ada project di tahapan ini</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Quick Project Creation Modal (Reuses High-Craft 3-Step Wizard) -->
    <dialog id="projectModal">
        <div class="modal-box">
            <div class="modal-head">
                <div>
                    <h2 class="modal-title">Buat Project Baru</h2>
                    <p class="modal-desc">Lengkapi informasi klien, detail layanan, dan susunan tim kerja.</p>
                </div>
                <button type="button" class="close-btn" onclick="document.getElementById('projectModal').close()" aria-label="Tutup popup">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <div class="wizard-stepper">
                <div class="step-badge active" id="stepBadge1">
                    <span class="step-num">1</span>
                    <span class="step-name">Klien</span>
                </div>
                <div class="step-line" id="stepLine1"></div>
                <div class="step-badge" id="stepBadge2">
                    <span class="step-num">2</span>
                    <span class="step-name">Layanan & Jadwal</span>
                </div>
                <div class="step-line" id="stepLine2"></div>
                <div class="step-badge" id="stepBadge3">
                    <span class="step-num">3</span>
                    <span class="step-name">Tim Konsulin</span>
                </div>
            </div>

            <form action="{{ route('projects.store') }}" method="POST" id="newProjectForm">
                @csrf
                <input type="hidden" name="client_mode" id="client_mode_input" value="{{ $clients->isEmpty() ? 'new' : 'existing' }}">

                <div class="modal-body">
                    <!-- STEP 1: Client Selection & Quick Add -->
                    <div class="wizard-step" id="wizardStep1">
                        @if ($clients->isEmpty())
                            <div class="client-alert-banner">
                                <div class="banner-icon">
                                    <x-heroicon-o-information-circle class="w-5 h-5" />
                                </div>
                                <div class="banner-text">
                                    <strong>Belum ada data klien terdaftar</strong>
                                    Silakan isi form di bawah ini untuk membuat data klien baru dan langsung menghubungkannya ke project ini.
                                </div>
                            </div>
                        @else
                            <div class="client-mode-selector">
                                <span style="font-size: 12.5px; font-weight: 600; color: #334155;">Pilih Sumber Klien:</span>
                                <div class="client-tabs">
                                    <button type="button" class="client-tab active" id="tabExistingClient" onclick="setClientMode('existing')">
                                        <x-heroicon-o-building-office class="w-4 h-4" />
                                        <span>Gunakan Klien Terdaftar</span>
                                    </button>
                                    <button type="button" class="client-tab" id="tabNewClient" onclick="setClientMode('new')">
                                        <x-heroicon-o-user-plus class="w-4 h-4" />
                                        <span>+ Buat Klien Baru</span>
                                    </button>
                                </div>
                            </div>
                        @endif

                        <div id="existingClientSection" style="{{ $clients->isEmpty() ? 'display: none;' : 'display: block;' }}">
                            <label>Pilih Klien Terdaftar
                                <select name="client_id" id="modal_client_id" onchange="previewSelectedClient(this)">
                                    <option value="">Pilih Klien</option>
                                    @foreach ($clients as $client)
                                        <option
                                            value="{{ $client->id }}"
                                            data-pic="{{ $client->client_pic ?? '-' }}"
                                            data-type="{{ $client->client_type ?? 'Badan' }}"
                                            data-email="{{ $client->email ?? '-' }}"
                                            data-phone="{{ $client->phone ?? '-' }}"
                                        >
                                            {{ $client->name }} ({{ $client->client_pic ?? 'PIC -' }})
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <div id="clientPreviewCard" class="client-preview-card" style="display: none;">
                                <div class="preview-header">
                                    <strong id="previewClientName">Nama Klien</strong>
                                    <span class="badge" id="previewClientType">Badan</span>
                                </div>
                                <div class="preview-grid">
                                    <div><span class="text-slate-500">PIC:</span> <span id="previewClientPic" class="font-semibold">-</span></div>
                                    <div><span class="text-slate-500">Email:</span> <span id="previewClientEmail">-</span></div>
                                </div>
                            </div>
                        </div>

                        <div id="newClientSection" style="{{ $clients->isEmpty() ? 'display: block;' : 'display: none;' }}">
                            <div class="form-grid">
                                <label>Nama Perusahaan / Klien Baru <span style="color: #dc2626;">*</span>
                                    <input type="text" name="client_name" id="modal_client_name" placeholder="PT Contoh Solusi Indonesia">
                                </label>
                                <label>Nama PIC Klien
                                    <input type="text" name="client_pic" id="modal_client_pic" placeholder="Bpk. Handoko">
                                </label>
                            </div>
                            <div class="form-grid" style="margin-top: 10px;">
                                <label>Tipe Klien
                                    <select name="client_type" id="modal_client_type">
                                        <option value="Badan">Badan (PT / CV / Yayasan)</option>
                                        <option value="Perorangan">Perorangan / OP</option>
                                    </select>
                                </label>
                                <label>Status Pajak
                                    <input type="text" name="tax_status" id="modal_tax_status" placeholder="PKP / Non PKP">
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: Project Details -->
                    <div class="wizard-step" id="wizardStep2" style="display: none;">
                        <label>Nama Project <span style="color: #dc2626;">*</span>
                            <input type="text" name="name" id="modal_project_name" required placeholder="Contoh: Penyusunan SPT Tahunan Badan & Tax Review 2026">
                        </label>
                        <div class="form-grid">
                            <label>Kategori Project
                                <select name="project_category_id" id="modal_category_id">
                                    <option value="">Tanpa Kategori</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>Jenis Layanan Konsulin <span style="color: #dc2626;">*</span>
                                <select name="service_type" id="modal_service_type" required>
                                    @foreach (['Tax Planning', 'Monthly Tax Compliance', 'Annual Tax Compliance (SPT Badan)', 'Accounting & Bookkeeping Service', 'Financial Statement Review', 'Tax Audit Assistance', 'Transfer Pricing Documentation (TP Doc)', 'Legal & Corporate Advisory'] as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                        <div class="form-grid">
                            <label>Tahapan Awal (Status)
                                <select name="status" id="modal_status" required>
                                    <option value="not_started">To Do (Belum Dimulai)</option>
                                    <option value="in_progress">In Progress (Sedang Berjalan)</option>
                                    <option value="waiting_client">Waiting Client (Menunggu Klien)</option>
                                    <option value="completed">Done (Selesai)</option>
                                </select>
                            </label>
                            <label>Prioritas Project
                                <select name="priority" id="modal_priority" required>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="low">Low</option>
                                </select>
                            </label>
                        </div>
                        <div class="form-grid">
                            <label>Tanggal Mulai
                                <input type="date" name="start_date" id="modal_start_date">
                            </label>
                            <label>Batas Waktu (Due Date)
                                <input type="date" name="due_date" id="modal_due_date">
                            </label>
                        </div>
                    </div>

                    <!-- STEP 3: Team Assignment (1 Reviewer, 1+ PIC Accounting, 1+ PIC Tax) -->
                    <div class="wizard-step" id="wizardStep3" style="display: none;">
                        <label>Reviewer Project <span style="color: #dc2626;">*</span>
                            <select name="reviewer_id" id="modal_reviewer_id" required>
                                <option value="">Pilih Reviewer (Supervisor / Manager / Boss)</option>
                                @foreach ($reviewers as $reviewer)
                                    <option value="{{ $reviewer->id }}" @selected($reviewer->id === auth()->id())>
                                        {{ $reviewer->name }} ({{ ucfirst($reviewer->role ?? 'User') }})
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <div class="form-grid" style="margin-top: 10px;">
                            <label>PIC Accounting (Pilih 1 atau Lebih)
                                <select name="accounting_staff_ids[]" id="modal_accounting_staff" multiple size="4">
                                    @foreach ($accountingStaffList as $staffItem)
                                        <option value="{{ $staffItem->id }}">
                                            {{ $staffItem->name }} ({{ $staffItem->position ?? 'Accounting' }})
                                        </option>
                                    @endforeach
                                </select>
                                <span style="font-size: 11px; color: #64748b;">Tahan tombol Ctrl (Windows) atau Command (Mac) untuk memilih beberapa staff.</span>
                            </label>

                            <label>PIC Tax (Pilih 1 atau Lebih)
                                <select name="tax_staff_ids[]" id="modal_tax_staff" multiple size="4">
                                    @foreach ($taxStaffList as $staffItem)
                                        <option value="{{ $staffItem->id }}">
                                            {{ $staffItem->name }} ({{ $staffItem->position ?? 'Tax Staff' }})
                                        </option>
                                    @endforeach
                                </select>
                                <span style="font-size: 11px; color: #64748b;">Tahan tombol Ctrl (Windows) atau Command (Mac) untuk memilih beberapa staff.</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-foot">
                    <button type="button" class="button secondary" id="btnPrevStep" onclick="navigateStep(-1)" style="display: none;">
                        <x-heroicon-o-chevron-left class="w-4 h-4" />
                        <span>Kembali</span>
                    </button>
                    <div style="margin-left: auto; display: flex; gap: 8px;">
                        <button type="button" class="button secondary" onclick="document.getElementById('projectModal').close()">
                            Batal
                        </button>
                        <button type="button" class="button" id="btnNextStep" onclick="navigateStep(1)">
                            <span>Lanjut</span>
                            <x-heroicon-o-chevron-right class="w-4 h-4" />
                        </button>
                        <button type="submit" class="button" id="btnSubmitProject" style="display: none;">
                            <x-heroicon-o-check class="w-4 h-4" />
                            <span>Simpan Project</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </dialog>

    <style>
        /* Kanban Layout Styles - Pure White & Navy Aesthetic (Antislop Compliant) */
        .kanban-page {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Wizard Stepper in Kanban Modal */
        .wizard-stepper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 24px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            gap: 8px;
        }
        .step-badge {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 6px 12px;
            border-radius: 8px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #000000;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.15s ease;
        }
        .step-badge .step-num {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #cbd5e1;
            color: #000000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
        }
        .step-badge.active {
            background: #0f172a;
            border-color: #0f172a;
            color: #ffffff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.2);
        }
        .step-badge.active .step-num {
            background: #ffffff;
            color: #0f172a;
        }
        .step-badge.completed {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #065f46;
        }
        .step-badge.completed .step-num {
            background: #059669;
            color: #ffffff;
        }
        .step-line {
            flex: 1;
            height: 2px;
            background: #e2e8f0;
            border-radius: 999px;
            transition: background 0.15s ease;
        }
        .step-line.active {
            background: #0f172a;
        }
        .page-eyebrow {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 2px;
        }
        .page-title {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #0f172a;
            margin: 0 0 4px;
        }
        .page-desc {
            font-size: 13px;
            color: #64748b;
            margin: 0;
        }

        /* Metrics Ribbon */
        .metrics-ribbon {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 12px;
        }
        .metric-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 14px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .metric-item.alert-threat {
            background: #fef2f2;
            border-color: #fecaca;
        }
        .metric-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .metric-label {
            font-size: 11.5px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .metric-value {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.1;
        }
        .metric-sub {
            font-size: 11px;
            color: #94a3b8;
        }

        /* Toolbar & Filters */
        .kanban-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
        }
        .search-box {
            position: relative;
            flex: 1 1 280px;
            min-width: 240px;
            display: flex;
            align-items: center;
        }
        .search-box .search-icon {
            position: absolute;
            left: 12px;
            width: 16px;
            height: 16px;
            color: #94a3b8;
            pointer-events: none;
            z-index: 2;
        }
        .search-box input,
        .search-box input.kanban-search-input {
            width: 100% !important;
            height: 36px !important;
            min-height: 36px !important;
            padding: 6px 32px 6px 38px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            font-size: 12.5px !important;
            background: #f8fafc !important;
            color: #0f172a !important;
            box-sizing: border-box !important;
        }
        .search-box input:focus {
            background: #ffffff;
            border-color: #0b192c;
            box-shadow: 0 0 0 2px rgba(11, 25, 44, 0.1);
        }
        .clear-search-btn {
            position: absolute;
            right: 8px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 2px;
            display: flex;
            align-items: center;
        }
        .filter-group {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }
        .filter-item select {
            height: 36px;
            min-height: 36px;
            padding: 6px 28px 6px 10px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: #f8fafc;
            color: #334155;
            cursor: pointer;
        }
        .filter-item select:focus {
            background: #ffffff;
            border-color: #0b192c;
        }

        /* Toast feedback */
        .kanban-toast {
            display: none;
            padding: 10px 16px;
            background: #0f172a;
            color: #ffffff;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: opacity 0.2s ease;
        }
        .kanban-toast.show {
            display: block;
            animation: fade-in-up 0.2s ease-out;
        }

        /* Kanban Board Grid */
        .kanban-board {
            display: grid;
            grid-template-columns: repeat(4, minmax(280px, 1fr));
            gap: 14px;
            align-items: start;
            overflow-x: auto;
            padding-bottom: 24px;
        }

        /* Columns */
        .kanban-column {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            min-height: 520px;
            padding: 12px;
            transition: background 0.15s ease, border-color 0.15s ease;
        }
        .kanban-column.drag-over {
            background: #eff6ff;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }
        .column-header {
            padding: 4px 6px 12px;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 10px;
        }
        .col-title-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .column-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.01em;
        }
        .column-counter {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: 999px;
            margin-left: auto;
        }
        .column-subtitle {
            font-size: 11px;
            color: #64748b;
            margin-top: 3px;
        }

        /* Cards list */
        .column-cards {
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex: 1 1 auto;
            min-height: 120px;
        }
        .column-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 36px 16px;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            color: #64748b;
            font-size: 12px;
            text-align: center;
            background: rgba(255, 255, 255, 0.6);
        }

        /* Global Empty State */
        .global-empty-state {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 48px 24px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .empty-icon-wrap {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 4px;
        }
        .empty-title {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }
        .empty-desc {
            font-size: 13px;
            color: #64748b;
            max-width: 400px;
            margin: 0 0 8px;
        }

        /* Project Card */
        .kanban-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
            display: flex;
            flex-direction: column;
            gap: 10px;
            cursor: grab;
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
            position: relative;
        }
        .kanban-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 4px 10px -2px rgba(15, 23, 42, 0.08);
            transform: translateY(-1px);
        }
        .kanban-card:focus-visible {
            outline: 2px solid #0284c7;
            outline-offset: 2px;
        }
        .kanban-card.dragging {
            opacity: 0.45;
            cursor: grabbing;
            transform: scale(0.98);
        }

        /* Card Header */
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
        }
        .code-and-priority {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .project-code {
            font-size: 10.5px;
            font-weight: 700;
            color: #475569;
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 5px;
            border: 1px solid #e2e8f0;
            letter-spacing: 0.02em;
        }
        .priority-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 5px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .priority-urgent {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .priority-high {
            background: #ffedd5;
            color: #9a3412;
            border: 1px solid #fed7aa;
        }
        .priority-medium {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .priority-low {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .quick-move-select {
            height: 24px;
            min-height: 24px;
            font-size: 10.5px;
            font-weight: 600;
            padding: 0 4px;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            background: #f8fafc;
            color: #475569;
            cursor: pointer;
        }
        .quick-move-select:focus {
            border-color: #0b192c;
            outline: none;
        }

        /* Card Body */
        .card-body {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .card-title {
            font-size: 13px;
            font-weight: 600;
            line-height: 1.35;
            margin: 0;
            color: #0f172a;
        }
        .card-link {
            color: #0f172a;
            text-decoration: none;
            transition: color 0.15s ease;
        }
        .card-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        .card-client {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 11.5px;
            color: #475569;
        }
        .client-icon {
            width: 13px;
            height: 13px;
            flex-shrink: 0;
            color: #64748b;
        }
        .client-link {
            color: #334155;
            font-weight: 600;
            text-decoration: none;
        }
        .client-link:hover {
            color: #0b192c;
            text-decoration: underline;
        }
        .client-pic-sub {
            color: #64748b;
            font-size: 11px;
        }
        .service-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 7px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 500;
            color: #475569;
            width: fit-content;
            margin-top: 2px;
        }
        .service-icon {
            width: 11px;
            height: 11px;
            color: #94a3b8;
        }

        /* Card Team Section */
        .card-team {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 6px 8px;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 6px;
            font-size: 11px;
        }
        .team-role-item {
            display: flex;
            align-items: center;
            gap: 6px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .role-tag {
            font-size: 9px;
            font-weight: 700;
            padding: 1px 4px;
            border-radius: 4px;
            letter-spacing: 0.03em;
            flex-shrink: 0;
        }
        .role-reviewer {
            background: #ede9fe;
            color: #6d28d9;
            border: 1px solid #ddd6fe;
        }
        .role-accounting {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }
        .role-tax {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .member-name {
            color: #334155;
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Card Progress & Footer */
        .card-meta {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding-top: 4px;
            border-top: 1px dashed #e2e8f0;
        }
        .meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
        }
        .task-count {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            color: #64748b;
        }
        .meta-icon {
            width: 13px;
            height: 13px;
            color: #059669;
        }
        .threat-indicator {
            display: flex;
            align-items: center;
            gap: 3px;
            padding: 1px 5px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
        }
        .threat-icon {
            width: 12px;
            height: 12px;
        }
        .card-progress-bar {
            width: 100%;
            height: 5px;
            background: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: #0b192c;
            border-radius: 999px;
            transition: width 0.3s ease;
        }
        .card-footer-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-top: 2px;
        }
        .due-date-pill {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 10.5px;
            color: #64748b;
        }
        .due-date-pill.is-overdue {
            color: #dc2626;
            font-weight: 600;
        }
        .due-icon {
            width: 12px;
            height: 12px;
        }
        .card-detail-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            color: #1e3e62;
            text-decoration: none;
            padding: 3px 6px;
            border-radius: 5px;
            transition: all 0.15s ease;
        }
        .card-detail-btn:hover {
            background: #f1f5f9;
            color: #0b192c;
        }

        /* Responsive */
        @media (max-width: 1280px) {
            .kanban-board {
                grid-template-columns: repeat(4, 280px);
            }
        }
    </style>

    <script>
        // Antislop Interactive Kanban Controller
        let draggedProjectId = null;

        function handleDragStart(event, projectId) {
            draggedProjectId = projectId;
            event.dataTransfer.setData('text/plain', projectId);
            event.dataTransfer.effectAllowed = 'move';
            const card = document.getElementById(`card-project-${projectId}`);
            if (card) {
                card.classList.add('dragging');
            }
        }

        function handleDragEnd(event) {
            document.querySelectorAll('.kanban-card.dragging').forEach(card => card.classList.remove('dragging'));
            document.querySelectorAll('.kanban-column.drag-over').forEach(col => col.classList.remove('drag-over'));
            draggedProjectId = null;
        }

        function handleDragOver(event) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
        }

        function handleDragEnter(event) {
            event.preventDefault();
            const col = event.currentTarget;
            if (col && col.classList.contains('kanban-column')) {
                col.classList.add('drag-over');
            }
        }

        function handleDragLeave(event) {
            const col = event.currentTarget;
            if (col && event.relatedTarget && !col.contains(event.relatedTarget)) {
                col.classList.remove('drag-over');
            }
        }

        function handleDrop(event, targetStatus) {
            event.preventDefault();
            const col = event.currentTarget;
            if (col) {
                col.classList.remove('drag-over');
            }

            const projectId = draggedProjectId || event.dataTransfer.getData('text/plain');
            if (!projectId) return;

            moveProject(projectId, targetStatus);
        }

        function moveProject(projectId, targetStatus) {
            const card = document.getElementById(`card-project-${projectId}`);
            if (!card) return;

            const currentStatus = card.dataset.status;
            if (currentStatus === targetStatus) return;

            const targetCardsContainer = document.getElementById(`cards-${targetStatus}`);
            const originalCardsContainer = document.getElementById(`cards-${currentStatus}`);

            // Optimistic DOM Update
            card.dataset.status = targetStatus;
            targetCardsContainer.appendChild(card);

            // Update Quick Move select inside card
            const quickSelect = card.querySelector('.quick-move-select');
            if (quickSelect) {
                quickSelect.value = '';
                Array.from(quickSelect.options).forEach(opt => {
                    opt.disabled = (opt.value === targetStatus);
                });
            }

            // Update counters and empty states
            updateColumnCounters();
            checkColumnEmptyStates();

            // Send AJAX PATCH request
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(`/kanban/projects/${projectId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ status: targetStatus })
            })
            .then(res => {
                if (!res.ok) throw new Error('Gagal memperbarui status di server');
                return res.json();
            })
            .then(data => {
                showToast(data.message || 'Status project berhasil diperbarui.');
            })
            .catch(err => {
                console.error(err);
                // Rollback optimistic update
                card.dataset.status = currentStatus;
                originalCardsContainer.appendChild(card);
                if (quickSelect) {
                    Array.from(quickSelect.options).forEach(opt => {
                        opt.disabled = (opt.value === currentStatus);
                    });
                }
                updateColumnCounters();
                checkColumnEmptyStates();
                showToast('Gagal memindahkan project. Perubahan dibatalkan.', true);
            });
        }

        function updateColumnCounters() {
            ['not_started', 'in_progress', 'waiting_client', 'completed'].forEach(status => {
                const countBadge = document.getElementById(`count-${status}`);
                const container = document.getElementById(`cards-${status}`);
                if (countBadge && container) {
                    const visibleCards = container.querySelectorAll(`.kanban-card:not([style*="display: none"])`);
                    countBadge.textContent = visibleCards.length;
                }
            });
        }

        function checkColumnEmptyStates() {
            ['not_started', 'in_progress', 'waiting_client', 'completed'].forEach(status => {
                const container = document.getElementById(`cards-${status}`);
                if (!container) return;

                let emptyNotice = container.querySelector('.column-empty');
                const visibleCards = container.querySelectorAll(`.kanban-card:not([style*="display: none"])`);

                if (visibleCards.length === 0) {
                    if (!emptyNotice) {
                        emptyNotice = document.createElement('div');
                        emptyNotice.className = 'column-empty';
                        emptyNotice.id = `empty-${status}`;
                        emptyNotice.innerHTML = `
                            <svg class="w-6 h-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <span>Belum ada project di tahapan ini</span>
                        `;
                        container.appendChild(emptyNotice);
                    } else {
                        emptyNotice.style.display = 'flex';
                    }
                } else if (emptyNotice) {
                    emptyNotice.style.display = 'none';
                }
            });
        }

        function showToast(message, isError = false) {
            if (window.toast) {
                if (isError) {
                    window.toast.error(message);
                } else {
                    window.toast.success(message);
                }
                return;
            }
            const toast = document.getElementById('kanbanToast');
            if (!toast) return;
            toast.textContent = message;
            toast.style.background = isError ? '#991b1b' : '#0f172a';
            toast.classList.add('show');
            clearTimeout(window.toastTimer);
            window.toastTimer = setTimeout(() => {
                toast.classList.remove('show');
            }, 3500);
        }

        // Real-time Instant Filter & Search Engine
        const searchInput = document.getElementById('kanbanSearchInput');
        const clearSearchBtn = document.getElementById('clearSearchBtn');
        const filterServiceType = document.getElementById('filterServiceType');
        const filterPriority = document.getElementById('filterPriority');
        const filterReviewer = document.getElementById('filterReviewer');
        const filterClient = document.getElementById('filterClient');
        const resetFiltersBtn = document.getElementById('resetFiltersBtn');
        const globalEmptyState = document.getElementById('globalEmptyState');
        const kanbanBoard = document.getElementById('kanbanBoard');

        function applyFilters() {
            const searchTerm = (searchInput?.value || '').trim().toLowerCase();
            const serviceVal = filterServiceType?.value || '';
            const priorityVal = filterPriority?.value || '';
            const reviewerVal = filterReviewer?.value || '';
            const clientVal = filterClient?.value || '';

            if (clearSearchBtn) {
                clearSearchBtn.style.display = searchTerm ? 'flex' : 'none';
            }

            const allCards = document.querySelectorAll('.kanban-card');
            let totalVisible = 0;

            allCards.forEach(card => {
                const name = card.dataset.projectName || '';
                const clientName = card.dataset.clientName || '';
                const service = card.dataset.serviceType || '';
                const priority = card.dataset.priority || '';
                const reviewer = card.dataset.reviewerId || '';
                const client = card.dataset.clientId || '';

                const matchesSearch = !searchTerm || name.includes(searchTerm) || clientName.includes(searchTerm) || card.id.includes(searchTerm);
                const matchesService = !serviceVal || service === serviceVal;
                const matchesPriority = !priorityVal || priority === priorityVal;
                const matchesReviewer = !reviewerVal || reviewer === reviewerVal;
                const matchesClient = !clientVal || client === clientVal;

                if (matchesSearch && matchesService && matchesPriority && matchesReviewer && matchesClient) {
                    card.style.display = 'flex';
                    totalVisible++;
                } else {
                    card.style.display = 'none';
                }
            });

            updateColumnCounters();
            checkColumnEmptyStates();

            if (totalVisible === 0 && allCards.length > 0) {
                globalEmptyState.style.display = 'flex';
                kanbanBoard.style.display = 'none';
            } else {
                globalEmptyState.style.display = 'none';
                kanbanBoard.style.display = 'grid';
            }
        }

        function resetAllFilters() {
            if (searchInput) searchInput.value = '';
            if (filterServiceType) filterServiceType.value = '';
            if (filterPriority) filterPriority.value = '';
            if (filterReviewer) filterReviewer.value = '';
            if (filterClient) filterClient.value = '';
            applyFilters();
        }

        if (searchInput) searchInput.addEventListener('input', applyFilters);
        if (clearSearchBtn) clearSearchBtn.addEventListener('click', () => { searchInput.value = ''; applyFilters(); });
        if (filterServiceType) filterServiceType.addEventListener('change', applyFilters);
        if (filterPriority) filterPriority.addEventListener('change', applyFilters);
        if (filterReviewer) filterReviewer.addEventListener('change', applyFilters);
        if (filterClient) filterClient.addEventListener('change', applyFilters);
        if (resetFiltersBtn) resetFiltersBtn.addEventListener('click', resetAllFilters);

        // Project Modal Wizard Functions
        let currentStep = 1;

        function openProjectModal() {
            currentStep = 1;
            updateWizardView();
            const modal = document.getElementById('projectModal');
            if (modal) modal.showModal();
        }

        function setClientMode(mode) {
            const input = document.getElementById('client_mode_input');
            const tabExisting = document.getElementById('tabExistingClient');
            const tabNew = document.getElementById('tabNewClient');
            const secExisting = document.getElementById('existingClientSection');
            const secNew = document.getElementById('newClientSection');

            if (input) input.value = mode;

            if (mode === 'existing') {
                tabExisting?.classList.add('active');
                tabNew?.classList.remove('active');
                if (secExisting) secExisting.style.display = 'block';
                if (secNew) secNew.style.display = 'none';
            } else {
                tabExisting?.classList.remove('active');
                tabNew?.classList.add('active');
                if (secExisting) secExisting.style.display = 'none';
                if (secNew) secNew.style.display = 'block';
            }
        }

        function previewSelectedClient(selectElem) {
            const opt = selectElem.options[selectElem.selectedIndex];
            const card = document.getElementById('clientPreviewCard');
            if (!opt || !opt.value) {
                if (card) card.style.display = 'none';
                return;
            }
            if (card) {
                card.style.display = 'block';
                document.getElementById('previewClientName').textContent = opt.text;
                document.getElementById('previewClientType').textContent = opt.dataset.type || 'Badan';
                document.getElementById('previewClientPic').textContent = opt.dataset.pic || '-';
                document.getElementById('previewClientEmail').textContent = opt.dataset.email || '-';
            }
        }

        function navigateStep(direction) {
            if (direction === 1) {
                // Validation for Step 1
                if (currentStep === 1) {
                    const mode = document.getElementById('client_mode_input')?.value || 'existing';
                    if (mode === 'existing') {
                        const clientId = document.getElementById('modal_client_id')?.value;
                        if (!clientId) {
                            alert('Silakan pilih salah satu klien terdaftar terlebih dahulu.');
                            return;
                        }
                    } else {
                        const clientName = document.getElementById('modal_client_name')?.value?.trim();
                        if (!clientName) {
                            alert('Silakan masukkan nama klien baru.');
                            return;
                        }
                    }
                }
                // Validation for Step 2
                if (currentStep === 2) {
                    const projectName = document.getElementById('modal_project_name')?.value?.trim();
                    if (!projectName) {
                        alert('Silakan masukkan nama project.');
                        return;
                    }
                }
            }

            currentStep += direction;
            if (currentStep < 1) currentStep = 1;
            if (currentStep > 3) currentStep = 3;
            updateWizardView();
        }

        function updateWizardView() {
            [1, 2, 3].forEach(step => {
                const stepEl = document.getElementById(`wizardStep${step}`);
                const badgeEl = document.getElementById(`stepBadge${step}`);
                const lineEl = document.getElementById(`stepLine${step}`);

                if (stepEl) stepEl.style.display = (step === currentStep) ? 'block' : 'none';
                if (badgeEl) {
                    badgeEl.classList.toggle('active', step === currentStep);
                    badgeEl.classList.toggle('completed', step < currentStep);
                }
                if (lineEl) {
                    lineEl.classList.toggle('active', step < currentStep);
                }
            });

            const btnPrev = document.getElementById('btnPrevStep');
            const btnNext = document.getElementById('btnNextStep');
            const btnSubmit = document.getElementById('btnSubmitProject');

            if (btnPrev) btnPrev.style.display = (currentStep > 1) ? 'inline-flex' : 'none';
            if (btnNext) btnNext.style.display = (currentStep < 3) ? 'inline-flex' : 'none';
            if (btnSubmit) btnSubmit.style.display = (currentStep === 3) ? 'inline-flex' : 'none';
        }

        // Initialize state on page load
        document.addEventListener('DOMContentLoaded', () => {
            updateColumnCounters();
            checkColumnEmptyStates();
        });
    </script>
</x-layouts.app>
