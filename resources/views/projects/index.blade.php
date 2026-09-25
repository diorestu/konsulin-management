<x-layouts.app title="Projects - Konsulin Manager">
    <div class="topbar">
        <div>
            <h1>Projects</h1>
            <p class="muted">Monitor client tax and accounting work, task progress, and open threats.</p>
        </div>
        <button class="button" type="button" data-open-project-modal="create">New Project</button>
    </div>

    <div class="overflow-x-auto pb-1 mb-5 -mx-1 px-1">
        <section class="stats stats-row-4 grid grid-cols-4 gap-3 min-w-[560px] md:min-w-0" data-animate-children>
            <div class="stat !p-3 !rounded-xl border border-slate-200 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-1 mb-1">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider truncate">Total projects</span>
                    <div class="w-6 h-6 rounded bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                        <x-heroicon-o-folder class="w-3.5 h-3.5" />
                    </div>
                </div>
                <strong class="text-xl font-bold text-slate-900 block leading-tight">{{ $totalProjectsCount }}</strong>
                <span class="text-[11px] text-slate-500 font-medium truncate block mt-0.5">Semua portofolio</span>
            </div>
            <div class="stat !p-3 !rounded-xl border border-slate-200 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-1 mb-1">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider truncate">Clients</span>
                    <div class="w-6 h-6 rounded bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                        <x-heroicon-o-building-office-2 class="w-3.5 h-3.5" />
                    </div>
                </div>
                <strong class="text-xl font-bold text-slate-900 block leading-tight">{{ $clientsCount }}</strong>
                <span class="text-[11px] text-slate-500 font-medium truncate block mt-0.5">Perusahaan aktif</span>
            </div>
            <div class="stat !p-3 !rounded-xl border border-slate-200 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-1 mb-1">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider truncate">Active projects</span>
                    <div class="w-6 h-6 rounded bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                        <x-heroicon-o-arrow-path class="w-3.5 h-3.5" />
                    </div>
                </div>
                <strong class="text-xl font-bold text-blue-700 block leading-tight">{{ $activeProjectsCount }}</strong>
                <span class="text-[11px] text-blue-600 font-medium truncate block mt-0.5">Sedang berjalan</span>
            </div>
            <div class="stat !p-3 !rounded-xl border border-slate-200 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-1 mb-1">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider truncate">Open threats</span>
                    <div class="w-6 h-6 rounded {{ $openThreatsCount > 0 ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center shrink-0">
                        <x-heroicon-o-shield-exclamation class="w-3.5 h-3.5" />
                    </div>
                </div>
                <strong class="text-xl font-bold {{ $openThreatsCount > 0 ? 'text-rose-600' : 'text-slate-800' }} block leading-tight">{{ $openThreatsCount }}</strong>
                <span class="text-[11px] {{ $openThreatsCount > 0 ? 'text-rose-600 font-semibold' : 'text-slate-500 font-medium' }} truncate block mt-0.5">
                    {{ $openThreatsCount > 0 ? 'Butuh mitigasi' : 'Kondisi aman' }}
                </span>
            </div>
        </section>
    </div>

    <x-datatable
        id="project"
        :columns="[
            'client' => ['label' => 'Client', 'sortable' => true],
            'project' => ['label' => 'Project', 'sortable' => true],
            'category' => ['label' => 'Category', 'sortable' => true, 'info' => 'Kategori layanan'],
            'client_profile' => ['label' => 'Client tax & contract', 'sortable' => true],
            'staff' => ['label' => 'Team & PIC', 'sortable' => true],
            'service' => ['label' => 'Service', 'sortable' => true],
            'status' => ['label' => 'Status', 'sortable' => true, 'sorted' => true, 'direction' => 'desc'],
            'active_period' => ['label' => 'Active period', 'sortable' => true],
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
                data-staff="{{ $project->reviewer?->name }} {{ $project->accountingStaff->pluck('name')->join(' ') }} {{ $project->taxStaff->pluck('name')->join(' ') }} {{ $project->staff->pluck('name')->join(' ') }}"
                data-service="{{ $project->service_type }}"
                data-status="{{ $project->status }}"
                data-active_period="{{ $project->start_date?->format('Y-m-d') ?? '' }}"
                data-priority="{{ $project->priority }}"
                data-progress="{{ $project->progressPercent() }}"
                data-due="{{ $project->due_date?->format('Y-m-d') ?? '' }}"
                class="hover:bg-slate-50/80 transition-colors"
            >
                <td data-column="client" class="project-sticky-client py-3.5 px-4 font-semibold text-slate-900">
                    {{ $project->client->name }}
                </td>
                <td data-column="project" class="py-3.5 px-4">
                    <a href="{{ route('projects.show', $project) }}" class="font-semibold text-slate-900 hover:text-[#1e3e62]">
                        <strong>{{ $project->name }}</strong>
                    </a>
                    <div class="muted text-xs mt-0.5 flex items-center gap-1.5 flex-wrap">
                        <span>{{ $project->tasks->count() }} tasks</span>
                        <span>·</span>
                        <span>{{ $project->threats->where('status', 'open')->count() }} open threats</span>
                        @if ($project->effectiveEstimatedHours() > 0)
                            <span>·</span>
                            <span class="font-mono {{ $project->budgetStatus() === 'over_budget' ? 'text-rose-600 font-bold' : ($project->budgetStatus() === 'warning' ? 'text-amber-700 font-bold' : 'text-slate-600') }}" title="Realisasi {{ $project->formattedTotalLoggedTime() }} dari anggaran {{ (float)$project->effectiveEstimatedHours() }} jam (Burn: {{ $project->burnRatePercent() }}%)">
                                {{ $project->formattedTotalLoggedTime() }} / {{ (float) $project->effectiveEstimatedHours() }}j ({{ $project->burnRatePercent() }}%)
                            </span>
                        @endif
                    </div>
                    @if ($project->tasks->isNotEmpty())
                        <div class="muted text-xs">Latest task: {{ $project->tasks->first()->title }}</div>
                    @endif
                </td>
                <td data-column="category" class="py-3.5 px-4 text-slate-600">{{ $project->category?->name ?? '-' }}</td>
                <td data-column="client_profile" class="py-3.5 px-4 text-xs text-slate-700">
                    <div class="font-semibold">{{ $project->client->tax_status ?? '-' }}</div>
                    <div class="text-slate-500">{{ $project->client->start_date?->format('d M Y') ?? '-' }} – {{ $project->client->end_contract_due_date?->format('d M Y') ?? '-' }}</div>
                    <div class="text-slate-500">{{ $project->client->pph_scheme ?? 'Skema PPh belum diatur' }}</div>
                </td>
                <td data-column="staff" class="py-3.5 px-4">
                    @php
                        $reviewer = $project->reviewer ?? $project->creator;
                        $accountingPic = $project->accountingStaff->isNotEmpty() ? $project->accountingStaff : $project->staff->where('type', 'accounting');
                        $taxPic = $project->taxStaff->isNotEmpty() ? $project->taxStaff : $project->staff->where('type', 'tax');
                    @endphp
                    <div class="flex items-center -space-x-1.5" aria-label="Team assignments">
                        @if ($reviewer)
                            <span class="project-team-avatar bg-slate-900 text-white" aria-label="Reviewer: {{ $reviewer->name }}" title="{{ $reviewer->name }}">{{ \Illuminate\Support\Str::of($reviewer->name)->explode(' ')->filter()->take(2)->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))->join('') }}</span>
                        @endif
                        @foreach ($accountingPic as $member)
                            <span class="project-team-avatar bg-blue-100 text-blue-800" aria-label="PIC Accounting: {{ $member->name }}" title="{{ $member->name }}">{{ \Illuminate\Support\Str::of($member->name)->explode(' ')->filter()->take(2)->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))->join('') }}</span>
                        @endforeach
                        @foreach ($taxPic as $member)
                            <span class="project-team-avatar bg-amber-100 text-amber-800" aria-label="PIC Tax: {{ $member->name }}" title="{{ $member->name }}">{{ \Illuminate\Support\Str::of($member->name)->explode(' ')->filter()->take(2)->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))->join('') }}</span>
                        @endforeach
                    </div>
                </td>
                <td data-column="service" class="py-3.5 px-4 text-slate-700 font-medium">{{ $project->service_type }}</td>
                <td data-column="status" class="py-3.5 px-4">
                    <x-datatable.status :type="$project->status" />
                </td>
                <td data-column="active_period" class="py-3.5 px-4 text-xs text-slate-700 whitespace-nowrap">
                    @if ($project->start_date || $project->due_date)
                        {{ $project->start_date?->format('d M Y') ?? 'Belum dimulai' }} – {{ $project->due_date?->format('d M Y') ?? 'Tanpa batas' }}
                    @else
                        <span class="text-slate-400">Belum diatur</span>
                    @endif
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
                <td data-column="actions" class="project-sticky-actions py-3.5 px-4 text-right">
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
                            data-client-pic="{{ $project->client->client_pic }}"
                            data-client-type="{{ $project->client->client_type }}"
                            data-client-tax-status="{{ $project->client->tax_status }}"
                            data-client-email="{{ $project->client->email }}"
                            data-client-phone="{{ $project->client->phone }}"
                            data-client-tax-id="{{ $project->client->tax_id }}"
                            data-project-category-id="{{ $project->project_category_id }}"
                            data-reviewer-id="{{ $project->reviewer_id ?? $project->created_by }}"
                            data-accounting-staff-ids="{{ $project->accountingStaff->pluck('id')->join(',') ?: $project->staff->where('type', 'accounting')->pluck('id')->join(',') }}"
                            data-tax-staff-ids="{{ $project->taxStaff->pluck('id')->join(',') ?: $project->staff->where('type', 'tax')->pluck('id')->join(',') }}"
                            data-staff-ids="{{ $project->staff->pluck('id')->join(',') }}"
                            data-name="{{ $project->name }}"
                            data-service-type="{{ $project->service_type }}"
                            data-status="{{ $project->status }}"
                            data-priority="{{ $project->priority }}"
                            data-estimated-hours="{{ (float) ($project->estimated_hours ?? 0) }}"
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
                <td colspan="13" class="muted py-8 text-center text-xs">
                    No projects yet. Create the first client project to start tracking tasks, progress, and threats.
                </td>
            </tr>
        @endforelse
</x-datatable>

<style>
    #projectTable { min-width: 1380px; table-layout: auto; }
    #projectTable th[data-column="client"], #projectTable td[data-column="client"] { min-width: 190px; max-width: 280px; }
    #projectTable th[data-column="project"], #projectTable td[data-column="project"] { min-width: 270px; }
    #projectTable th[data-column="staff"], #projectTable td[data-column="staff"] { min-width: 112px; }
    #projectTable th[data-column="service"], #projectTable td[data-column="service"] { min-width: 120px; }
    #projectTable th[data-column="active_period"], #projectTable td[data-column="active_period"] { min-width: 190px; }
    #projectTable th[data-column="actions"], #projectTable td[data-column="actions"] { min-width: 112px; }
    #projectTable .project-sticky-client { position: sticky; left: 0; z-index: 10; background: #fff; box-shadow: 2px 0 0 #e2e8f0; }
    #projectTable tbody tr:hover .project-sticky-client { background: #f8fafc; }
    #projectTable .project-sticky-actions { position: sticky; right: 0; z-index: 10; background: #fff; box-shadow: -2px 0 0 #e2e8f0; }
    #projectTable tbody tr:hover .project-sticky-actions { background: #f8fafc; }
    #projectTable thead .project-sticky-client, #projectTable thead .project-sticky-actions { z-index: 20; background: #fff; }
    .project-team-avatar { display: inline-flex; width: 30px; height: 30px; align-items: center; justify-content: center; border: 2px solid #fff; border-radius: 9999px; font-size: 10px; font-weight: 700; line-height: 1; }
    @media (max-width: 767px) { #projectTable { min-width: 1240px; } .project-team-avatar { width: 32px; height: 32px; } }
</style>

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
                @if ($clients->isEmpty())
                    <div class="client-alert-banner">
                        <div class="banner-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        </div>
                        <div class="banner-text">
                            <strong>Belum Ada Client Terdaftar</strong>
                            Silakan masukkan data client baru untuk project ini pada formulir di bawah, atau buka menu Kelola Client untuk pendaftaran lengkap 22 kolom.
                        </div>
                        <a href="{{ route('clients.create') }}" target="_blank" class="button small secondary">
                            + Menu Kelola Client ↗
                        </a>
                    </div>
                @else
                    <div class="client-mode-selector" id="clientModeSelector">
                        <div class="client-tabs">
                            <button type="button" class="client-tab active" id="tabSelectExisting" data-tab-mode="existing">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                Client Terdaftar ({{ $clients->count() }})
                            </button>
                            <button type="button" class="client-tab" id="tabCreateNew" data-tab-mode="new">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                + Tambah Client Baru
                            </button>
                        </div>
                        <a href="{{ route('clients.create') }}" target="_blank" class="button small secondary" title="Buka menu kelola client untuk isi detail 22 kolom">
                            Kelola Client ↗
                        </a>
                    </div>
                @endif

                <input type="hidden" name="client_mode" id="clientModeInput" value="{{ $clients->isEmpty() ? 'new' : 'existing' }}">

                {{-- Panel 1: Existing Client Select --}}
                @if ($clients->isNotEmpty())
                    <div id="panelExistingClient">
                        <label>Pilih Client Terdaftar <span style="color:#ef4444">*</span>
                            <select name="client_id" id="client_id">
                                <option value="">-- Cari atau pilih client --</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}"
                                        data-name="{{ $client->name }}"
                                        data-pic="{{ $client->client_pic ?? '-' }}"
                                        data-type="{{ $client->client_type ?? 'Badan' }}"
                                        data-tax-status="{{ $client->tax_status ?? 'PKP' }}"
                                        data-email="{{ $client->email ?? '-' }}"
                                        data-phone="{{ $client->phone ?? '-' }}"
                                        data-tax-id="{{ $client->tax_id ?? '-' }}">
                                        {{ $client->name }} {{ $client->client_pic ? '· PIC: ' . $client->client_pic : '' }} ({{ $client->client_type ?? 'Client' }})
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <div id="clientPreviewCard" class="client-preview-card" style="display: none;">
                            <div class="preview-header">
                                <strong id="previewClientName">-</strong>
                                <span class="badge" id="previewClientType">-</span>
                            </div>
                            <div class="preview-grid">
                                <div><span style="color:#64748b;">PIC:</span> <strong id="previewClientPic">-</strong></div>
                                <div><span style="color:#64748b;">Status Pajak:</span> <span id="previewClientTaxStatus">-</span></div>
                                <div><span style="color:#64748b;">Email:</span> <span id="previewClientEmail">-</span></div>
                                <div><span style="color:#64748b;">Telepon / WA:</span> <span id="previewClientPhone">-</span></div>
                                <div><span style="color:#64748b;">NPWP / Tax ID:</span> <span id="previewClientTaxId">-</span></div>
                            </div>
                        </div>

                        <p class="muted" style="margin-top: 14px; font-size: 12px;">
                            Client belum ada di daftar?
                            <button type="button" class="btn-text-link" id="quickSwitchToNewClient">+ Tambah client baru di popup ini</button>
                            atau
                            <a href="{{ route('clients.create') }}" target="_blank" style="color: #0284c7; font-weight: 600; text-decoration: underline;">buka menu Kelola Client</a>.
                        </p>
                    </div>
                @endif

                {{-- Panel 2: New Client Form (Or Edit Form) --}}
                <div id="panelNewClient" style="{{ $clients->isEmpty() ? '' : 'display: none;' }}">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <div>
                            <strong style="font-size: 13px; color: #0f172a;" id="newClientHeading">Registrasi Client Baru</strong>
                            <p class="muted" style="margin: 0; font-size: 11.5px;">Client baru akan otomatis dibuat dan ditautkan ke project ini.</p>
                        </div>
                        <a href="{{ route('clients.create') }}" target="_blank" class="button small secondary">
                            Form 22 Kolom ↗
                        </a>
                    </div>
                    <div class="form-grid">
                        <label class="full-width">Nama Client / Perusahaan <span style="color:#ef4444">*</span>
                            <input name="client_name" id="client_name" placeholder="Misal: PT Nusantara Solusi Mandiri">
                        </label>
                        <label>Nama PIC Client
                            <input name="client_pic" id="client_pic" placeholder="Misal: Bpk. Bambang / Ibu Dian">
                        </label>
                        <label>Tipe Client
                            <select name="client_type" id="client_type">
                                <option value="Badan">Badan (PT / CV)</option>
                                <option value="Perorangan">Perorangan (Orang Pribadi)</option>
                                <option value="Yayasan">Yayasan / Organisasi</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </label>
                        <label>Status Pajak
                            <select name="tax_status" id="tax_status">
                                <option value="PKP">PKP (Pengusaha Kena Pajak)</option>
                                <option value="Non PKP">Non PKP</option>
                            </select>
                        </label>
                        <label>Tax ID / NPWP
                            <input name="client_tax_id" id="client_tax_id" placeholder="00.000.000.0-000.000">
                        </label>
                        <label>Email
                            <input type="email" name="client_email" id="client_email" placeholder="client@domain.com">
                        </label>
                        <label>Phone / WhatsApp
                            <input name="client_phone" id="client_phone" placeholder="08xxxxxxxxxx">
                        </label>
                    </div>
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
                    <label class="full-width">Target Anggaran Jam Kerja (Hours)
                        <input type="number" step="0.25" min="0" max="9999" name="estimated_hours" id="estimated_hours" placeholder="Contoh: 25.0 (Target anggaran jam kerja proyek yang disepakati)">
                    </label>
                </div>
            </section>

            <section class="wizard-step" data-wizard-step="2" hidden>
                <div class="team-composition-card" style="margin-bottom: 16px; padding: 12px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                        <div>
                            <strong style="font-size: 13px; color: #0f172a; display: block;">Komposisi Tim Project</strong>
                            <span class="muted" style="font-size: 11.5px;">Setiap project wajib memiliki 1 Reviewer, minimal 1 PIC Accounting, dan minimal 1 PIC Tax.</span>
                        </div>
                        <div style="display: flex; gap: 6px;">
                            <span class="badge" style="background:#0f172a; color:#ffffff; font-size: 10.5px;">1 Reviewer</span>
                            <span class="badge" style="background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; font-size: 10.5px;">1+ PIC Accounting</span>
                            <span class="badge" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a; font-size: 10.5px;">1+ PIC Tax</span>
                        </div>
                    </div>
                </div>

                <div class="form-grid">
                    <!-- Reviewer -->
                    <label class="full-width">Reviewer Project (Tepat 1 Orang) <span style="color:#ef4444">*</span>
                        <select name="reviewer_id" id="reviewer_id" required>
                            <option value="">-- Pilih 1 Orang Reviewer --</option>
                            @foreach ($reviewers as $rev)
                                <option value="{{ $rev->id }}">
                                    {{ $rev->name }} · {{ $rev->role === 'boss' ? 'Partner / Lead Reviewer' : 'Consultant / Reviewer' }}
                                </option>
                            @endforeach
                        </select>
                        <span class="muted" style="font-size: 11px; font-weight: normal;">Reviewer bertanggung jawab atas QA, review kertas kerja, dan persetujuan laporan.</span>
                    </label>

                    <!-- PIC Accounting -->
                    <div>
                        <label>PIC Accounting (1 atau Lebih) <span style="color:#ef4444">*</span>
                            <select name="accounting_staff_ids[]" id="accounting_staff_ids" multiple size="5" class="w-full" required>
                                @foreach ($accountingStaffList as $accStaff)
                                    <option value="{{ $accStaff->id }}">{{ $accStaff->name }} · {{ $accStaff->position ?? 'Accounting' }}</option>
                                @endforeach
                            </select>
                            <span class="muted" style="font-size: 11px; font-weight: normal;">Tahan tombol Ctrl / Cmd untuk memilih lebih dari 1 PIC Accounting.</span>
                        </label>
                    </div>

                    <!-- PIC Tax -->
                    <div>
                        <label>PIC Tax (1 atau Lebih) <span style="color:#ef4444">*</span>
                            <select name="tax_staff_ids[]" id="tax_staff_ids" multiple size="5" class="w-full" required>
                                @foreach ($taxStaffList as $txStaff)
                                    <option value="{{ $txStaff->id }}">{{ $txStaff->name }} · {{ $txStaff->position ?? 'Tax' }}</option>
                                @endforeach
                            </select>
                            <span class="muted" style="font-size: 11px; font-weight: normal;">Tahan tombol Ctrl / Cmd untuk memilih lebih dari 1 PIC Tax.</span>
                        </label>
                    </div>

                    <!-- Description -->
                    <label class="full-width">Catatan & Deskripsi Project
                        <textarea name="description" id="description" placeholder="Catatan perikatan, instruksi khusus, atau ruang lingkup pekerjaan..."></textarea>
                    </label>
                </div>
            </section>

            <div class="modal-actions">
                <button class="button secondary" type="button" data-wizard-prev>Previous</button>
                <div class="actions">
                    <button class="button" type="button" data-wizard-next>Next</button>
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
            const q = normalized(searchInput ? searchInput.value : '');
            return tableRows.filter((row) => {
                if (!q) return true;
                const client = normalized(row.dataset.client);
                const project = normalized(row.dataset.project);
                const category = normalized(row.dataset.category);
                const staff = normalized(row.dataset.staff);
                const service = normalized(row.dataset.service);
                const status = normalized(row.dataset.status);
                return (
                    client.includes(q) ||
                    project.includes(q) ||
                    category.includes(q) ||
                    staff.includes(q) ||
                    service.includes(q) ||
                    status.includes(q)
                );
            }).sort((a, b) => {
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
            const perPage = Number(rowsPerPageSelect ? rowsPerPageSelect.value : 10);
            const totalPages = Math.max(1, Math.ceil(rows.length / perPage));
            currentPage = Math.min(currentPage, totalPages);

            tableRows.forEach((row) => (row.hidden = true));
            const start = (currentPage - 1) * perPage;
            const end = start + perPage;
            rows.slice(start, end).forEach((row) => (row.hidden = false));

            if (paginationInfo) {
                const visibleCount = rows.length;
                const from = visibleCount === 0 ? 0 : start + 1;
                const to = Math.min(end, visibleCount);
                paginationInfo.textContent = `Showing ${from}-${to} of ${visibleCount}`;
            }

            if (prevPageButton) prevPageButton.disabled = currentPage <= 1;
            if (nextPageButton) nextPageButton.disabled = currentPage >= totalPages;
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

        if (searchInput) searchInput.addEventListener('input', () => { currentPage = 1; renderTable(); });
        if (rowsPerPageSelect) rowsPerPageSelect.addEventListener('change', () => { currentPage = 1; renderTable(); });
        if (prevPageButton) prevPageButton.addEventListener('click', () => { currentPage -= 1; renderTable(); });
        if (nextPageButton) nextPageButton.addEventListener('click', () => { currentPage += 1; renderTable(); });

        const modal = document.getElementById('projectModal');
        const form = document.getElementById('projectForm');
        const methodInput = document.getElementById('projectFormMethod');
        const title = document.getElementById('projectModalTitle');
        const clientModeSelector = document.getElementById('clientModeSelector');
        const tabSelectExisting = document.getElementById('tabSelectExisting');
        const tabCreateNew = document.getElementById('tabCreateNew');
        const panelExistingClient = document.getElementById('panelExistingClient');
        const panelNewClient = document.getElementById('panelNewClient');
        const clientModeInput = document.getElementById('clientModeInput');
        const clientIdSelect = document.getElementById('client_id');
        const clientPreviewCard = document.getElementById('clientPreviewCard');
        const quickSwitchToNewClient = document.getElementById('quickSwitchToNewClient');
        const newClientHeading = document.getElementById('newClientHeading');
        const clientNameInput = document.getElementById('client_name');
        let wizardStep = 0;

        function setClientMode(mode) {
            if (clientModeInput) clientModeInput.value = mode;
            if (mode === 'existing') {
                if (tabSelectExisting) tabSelectExisting.classList.add('active');
                if (tabCreateNew) tabCreateNew.classList.remove('active');
                if (panelExistingClient) panelExistingClient.style.display = 'block';
                if (panelNewClient) panelNewClient.style.display = 'none';
                if (clientNameInput) clientNameInput.removeAttribute('required');
                if (clientIdSelect) clientIdSelect.setAttribute('required', 'required');
            } else {
                if (tabSelectExisting) tabSelectExisting.classList.remove('active');
                if (tabCreateNew) tabCreateNew.classList.add('active');
                if (panelExistingClient) panelExistingClient.style.display = 'none';
                if (panelNewClient) panelNewClient.style.display = 'block';
                if (clientIdSelect) {
                    clientIdSelect.value = '';
                    clientIdSelect.removeAttribute('required');
                }
                if (clientPreviewCard) clientPreviewCard.style.display = 'none';
                if (clientNameInput) clientNameInput.setAttribute('required', 'required');
            }
        }

        if (tabSelectExisting) {
            tabSelectExisting.addEventListener('click', () => setClientMode('existing'));
        }
        if (tabCreateNew) {
            tabCreateNew.addEventListener('click', () => setClientMode('new'));
        }
        if (quickSwitchToNewClient) {
            quickSwitchToNewClient.addEventListener('click', () => setClientMode('new'));
        }

        if (clientIdSelect) {
            clientIdSelect.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                if (this.value && opt && opt.dataset.name) {
                    document.getElementById('previewClientName').textContent = opt.dataset.name;
                    document.getElementById('previewClientType').textContent = opt.dataset.type || 'Badan';
                    document.getElementById('previewClientPic').textContent = opt.dataset.pic || '-';
                    document.getElementById('previewClientTaxStatus').textContent = opt.dataset.taxStatus || '-';
                    document.getElementById('previewClientEmail').textContent = opt.dataset.email || '-';
                    document.getElementById('previewClientPhone').textContent = opt.dataset.phone || '-';
                    document.getElementById('previewClientTaxId').textContent = opt.dataset.taxId || '-';
                    if (clientPreviewCard) clientPreviewCard.style.display = 'block';
                } else {
                    if (clientPreviewCard) clientPreviewCard.style.display = 'none';
                }
            });
        }

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
            if (clientModeSelector) clientModeSelector.style.display = 'flex';
            if (newClientHeading) newClientHeading.textContent = 'Registrasi Client Baru';
            const defaultMode = panelExistingClient ? 'existing' : 'new';
            setClientMode(defaultMode);
            if (clientPreviewCard) clientPreviewCard.style.display = 'none';

            setValue('reviewer_id', '');
            if (document.getElementById('accounting_staff_ids')) {
                Array.from(document.getElementById('accounting_staff_ids').options).forEach(opt => opt.selected = false);
            }
            if (document.getElementById('tax_staff_ids')) {
                Array.from(document.getElementById('tax_staff_ids').options).forEach(opt => opt.selected = false);
            }

            setWizardStep(0);
            modal.showModal();
        }

        function openEditModal(button) {
            form.reset();
            form.action = button.dataset.action;
            methodInput.value = 'PUT';
            title.textContent = 'Edit Project';
            if (clientModeSelector) clientModeSelector.style.display = 'none';
            if (panelExistingClient) panelExistingClient.style.display = 'none';
            if (panelNewClient) panelNewClient.style.display = 'block';
            if (newClientHeading) newClientHeading.textContent = 'Detail Client (Edit)';
            if (clientNameInput) clientNameInput.setAttribute('required', 'required');
            setValue('client_id', '');
            setValue('client_name', button.dataset.clientName);
            setValue('client_pic', button.dataset.clientPic);
            setValue('client_type', button.dataset.clientType || 'Badan');
            setValue('tax_status', button.dataset.clientTaxStatus || 'PKP');
            setValue('client_email', button.dataset.clientEmail);
            setValue('client_phone', button.dataset.clientPhone);
            setValue('client_tax_id', button.dataset.clientTaxId);
            setValue('project_category_id', button.dataset.projectCategoryId);
            setValue('reviewer_id', button.dataset.reviewerId);

            const accStaffIds = (button.dataset.accountingStaffIds || '').split(',').filter(Boolean);
            if (document.getElementById('accounting_staff_ids')) {
                Array.from(document.getElementById('accounting_staff_ids').options).forEach((option) => {
                    option.selected = accStaffIds.includes(option.value);
                });
            }

            const taxStaffIds = (button.dataset.taxStaffIds || '').split(',').filter(Boolean);
            if (document.getElementById('tax_staff_ids')) {
                Array.from(document.getElementById('tax_staff_ids').options).forEach((option) => {
                    option.selected = taxStaffIds.includes(option.value);
                });
            }

            setValue('name', button.dataset.name);
            setValue('service_type', button.dataset.serviceType);
            setValue('status', button.dataset.status);
            setValue('priority', button.dataset.priority);
            setValue('estimated_hours', button.dataset.estimatedHours || '0');
            setValue('start_date', button.dataset.startDate);
            setValue('due_date', button.dataset.dueDate);
            setValue('created_by', button.dataset.createdBy);
            setValue('description', button.dataset.description);
            setWizardStep(0);
            modal.showModal();
        }

        form.addEventListener('submit', function(e) {
            const reviewerSelect = document.getElementById('reviewer_id');
            const accSelect = document.getElementById('accounting_staff_ids');
            const taxSelect = document.getElementById('tax_staff_ids');

            if (reviewerSelect && !reviewerSelect.value) {
                e.preventDefault();
                setWizardStep(2);
                alert('Tiap project wajib memiliki 1 Reviewer.');
                reviewerSelect.focus();
                return false;
            }

            if (accSelect && Array.from(accSelect.selectedOptions).length < 1) {
                e.preventDefault();
                setWizardStep(2);
                alert('Tiap project wajib memiliki minimal 1 PIC Accounting.');
                accSelect.focus();
                return false;
            }

            if (taxSelect && Array.from(taxSelect.selectedOptions).length < 1) {
                e.preventDefault();
                setWizardStep(2);
                alert('Tiap project wajib memiliki minimal 1 PIC Tax.');
                taxSelect.focus();
                return false;
            }
        });

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
