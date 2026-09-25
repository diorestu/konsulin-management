<x-layouts.app :title="$project->name . ' : Jira Board : Konsulin Manager'">
    <header class="project-detail-header">
        <div class="min-w-0">
            <a href="{{ route('projects.index') }}" class="project-back-link">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                <span>Projects</span>
            </a>
            <div class="flex items-center gap-2 mt-3 min-w-0">
                <h1 class="truncate">{{ $project->name }}</h1>
                <span class="label navy text-xs shrink-0">{{ $project->service_type }}</span>
            </div>
            <p class="project-detail-meta">
                <a href="{{ route('clients.show', $project->client) }}" class="font-semibold text-slate-700 hover:text-[#0b192c]">{{ $project->client->name }}</a>
                <span aria-hidden="true">·</span>
                <span>PRJ-{{ str_pad($project->id, 3, '0', STR_PAD_LEFT) }}</span>
                <span aria-hidden="true">·</span>
                <span>{{ $project->due_date ? 'Tenggat ' . $project->due_date->format('d M Y') : 'Tanpa tenggat' }}</span>
            </p>
        </div>
        @if(!auth()->check() || !auth()->user()->isStaff() || auth()->user()->can('manage tasks'))
            <button class="button project-primary-action" type="button" onclick="document.getElementById('taskFormModal').showModal()">
                <x-heroicon-o-plus class="w-4 h-4" />
                <span>Tambah tugas</span>
            </button>
        @endif
    </header>

    <!-- Improved Jira Project Stats Bar -->
    <section class="stats" data-animate-children>
        <!-- Stat 1: Overall Progress & Deliverable Health -->
        <div class="stat">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Overall Progress</span>
                @if ($project->progressPercent() >= 100)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Completed
                    </span>
                @elseif ($project->threats->where('status', 'open')->count() > 0)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                        Mitigasi
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                        On Track
                    </span>
                @endif
            </div>
            <div class="flex items-baseline justify-between mb-1.5">
                <strong id="statProjectProgress" class="text-2xl font-bold tracking-tight text-slate-900 font-mono">
                    {{ $project->progressPercent() }}%
                </strong>
                <span class="text-[11px] text-slate-500 font-medium">
                    {{ $project->tasks->where('status', 'completed')->count() }} / {{ $project->tasks->count() }} task
                </span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden mb-2">
                <div id="statProjectProgressBar" class="bg-[#0b192c] h-2 rounded-full transition-all duration-300" style="width: {{ $project->progressPercent() }}%"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-500">
                <span class="truncate">Target: {{ $project->due_date?->format('d M Y') ?? 'TBA' }}</span>
                @if ($project->due_date && $project->due_date->isPast() && $project->status !== 'completed')
                    <span class="text-rose-600 font-bold shrink-0">Overdue</span>
                @elseif ($project->due_date)
                    <span class="text-slate-400 shrink-0">{{ $project->due_date->diffForHumans() }}</span>
                @endif
            </div>
        </div>

        <!-- Stat 2: Tasks Lifecycle & Velocity -->
        <div class="stat">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tasks Lifecycle</span>
                <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center border border-blue-100">
                    <x-heroicon-o-check-circle class="w-4 h-4" />
                </div>
            </div>
            <div class="flex items-baseline gap-2 mb-2">
                <strong id="statTasksCount" class="text-2xl font-bold tracking-tight text-slate-900 font-mono">
                    {{ $project->tasks->count() }}
                </strong>
                <span class="text-xs text-slate-500 font-medium">Total Isu</span>
            </div>
            <!-- Micro Status Distribution Chips -->
            <div class="grid grid-cols-5 gap-1 mb-2">
                <div class="text-center py-1 rounded bg-emerald-50 border border-emerald-100" title="Done / Selesai">
                    <div class="text-[11px] font-bold text-emerald-800">{{ $project->tasks->where('status', 'completed')->count() }}</div>
                    <div class="text-[9px] uppercase font-bold text-emerald-600">Done</div>
                </div>
                <div class="text-center py-1 rounded bg-indigo-50 border border-indigo-100" title="In Review / Kendali Mutu">
                    <div class="text-[11px] font-bold text-indigo-800">{{ $project->tasks->where('status', 'in_review')->count() }}</div>
                    <div class="text-[9px] uppercase font-bold text-indigo-600">Review</div>
                </div>
                <div class="text-center py-1 rounded bg-blue-50 border border-blue-100" title="In Progress / Berjalan">
                    <div class="text-[11px] font-bold text-blue-800">{{ $project->tasks->where('status', 'in_progress')->count() }}</div>
                    <div class="text-[9px] uppercase font-bold text-blue-600">Active</div>
                </div>
                <div class="text-center py-1 rounded bg-amber-50 border border-amber-100" title="Waiting Client / Pending">
                    <div class="text-[11px] font-bold text-amber-800">{{ $project->tasks->where('status', 'waiting_client')->count() }}</div>
                    <div class="text-[9px] uppercase font-bold text-amber-600">Wait</div>
                </div>
                <div class="text-center py-1 rounded bg-slate-100 border border-slate-200" title="To Do / Belum Dimulai">
                    <div class="text-[11px] font-bold text-slate-700">{{ $project->tasks->where('status', 'not_started')->count() }}</div>
                    <div class="text-[9px] uppercase font-bold text-slate-500">To Do</div>
                </div>
            </div>
            <div class="text-[11px] text-slate-500 truncate" id="statTasksSubtext">
                {{ $project->tasks->where('status', 'completed')->count() }} done · {{ $project->tasks->where('status', 'in_review')->count() }} review · {{ $project->tasks->where('status', 'in_progress')->count() }} in progress
            </div>
        </div>

        <!-- Stat 3: Staff Logged Work Time & Budget Burn Rate -->
        @php
            $budgetStatus = $project->budgetStatus();
            $effectiveBudget = $project->effectiveEstimatedHours();
            $burnRate = $project->burnRatePercent();
            $isOverBudget = $budgetStatus === 'over_budget';
            $isWarning = $budgetStatus === 'warning';
        @endphp
        <div class="stat {{ $isOverBudget ? 'border-rose-300 bg-rose-50/20' : ($isWarning ? 'border-amber-300 bg-amber-50/20' : '') }}" id="statProjectBudgetCard">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Work Time & Budget</span>
                <div class="w-7 h-7 rounded-lg {{ $isOverBudget ? 'bg-rose-100 text-rose-700 border-rose-200' : ($isWarning ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-indigo-50 text-indigo-700 border-indigo-100') }} flex items-center justify-center border" id="statBudgetIconContainer">
                    <x-heroicon-o-clock class="w-4 h-4" />
                </div>
            </div>
            <div class="flex items-baseline gap-2 mb-1.5 flex-wrap">
                <strong class="text-2xl font-bold tracking-tight text-slate-900 font-mono" id="statLoggedTimeValue">
                    {{ $project->formattedTotalLoggedTime() }}
                </strong>
                @if ($effectiveBudget > 0)
                    <span class="text-xs text-slate-500 font-medium font-mono" id="statBudgetTargetValue">
                        / {{ (float) $effectiveBudget }}j
                    </span>
                @else
                    <span class="text-xs text-slate-400 font-medium" id="statBudgetTargetValue">
                        (Tanpa Kuota)
                    </span>
                @endif
            </div>

            @if ($effectiveBudget > 0)
                <!-- Burn Rate Progress Bar -->
                <div class="w-full bg-slate-200/80 rounded-full h-1.5 mb-2 overflow-hidden">
                    <div
                        id="statBurnProgressBar"
                        class="h-1.5 rounded-full transition-all duration-500 {{ $isOverBudget ? 'bg-rose-600' : ($isWarning ? 'bg-amber-500' : 'bg-emerald-600') }}"
                        style="width: {{ min(100, $burnRate) }}%"
                    ></div>
                </div>
                <div class="p-1.5 rounded-lg {{ $isOverBudget ? 'bg-rose-50 border-rose-200 text-rose-800' : ($isWarning ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-slate-50 border-slate-200 text-slate-700') }} border mb-2 flex items-center justify-between text-[11px]" id="statBurnInfoBox">
                    <span class="font-medium flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ $isOverBudget ? 'bg-rose-600 animate-pulse' : ($isWarning ? 'bg-amber-500' : 'bg-emerald-500') }}" id="statBurnDot"></span>
                        <span id="statBurnRateBadgeText">Burn Rate {{ $burnRate }}%</span>
                    </span>
                    <span class="font-bold font-mono" id="statRemainingHoursText">
                        @if ($isOverBudget)
                            Over +{{ $project->overBudgetHours() }}j
                        @else
                            Sisa {{ $project->remainingHours() }}j
                        @endif
                    </span>
                </div>
            @else
                <div class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 mb-2 flex items-center justify-between text-[11px]" id="statBurnInfoBox">
                    <span class="text-slate-600 font-medium flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-slate-400" id="statBurnDot"></span>
                        <span id="statBurnRateBadgeText">Estimasi Kuota</span>
                    </span>
                    <span class="font-medium text-slate-500" id="statRemainingHoursText">Belum Diset</span>
                </div>
            @endif

            <div class="text-[11px] text-slate-500 truncate" id="statBudgetSubtext">
                <button type="button" onclick="switchProjectView('budget')" class="text-indigo-600 hover:text-indigo-800 font-semibold cursor-pointer">
                    Rincian realisasi & budget &rarr;
                </button>
            </div>
        </div>

        <!-- Stat 4: Operational Threats & Risks -->
        <div class="stat {{ $project->threats->where('status', 'open')->count() > 0 ? 'border-rose-300 bg-rose-50/20' : '' }}">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Operational Threats</span>
                <div class="w-7 h-7 rounded-lg {{ $project->threats->where('status', 'open')->count() > 0 ? 'bg-rose-100 text-rose-700' : 'bg-emerald-50 text-emerald-700' }} flex items-center justify-center border {{ $project->threats->where('status', 'open')->count() > 0 ? 'border-rose-200' : 'border-emerald-100' }}">
                    <x-heroicon-o-shield-exclamation class="w-4 h-4" />
                </div>
            </div>
            <div class="flex items-baseline gap-2 mb-1.5">
                <strong id="statOpenThreatsCount" class="text-2xl font-bold tracking-tight font-mono {{ $project->threats->where('status', 'open')->count() > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                    {{ $project->threats->where('status', 'open')->count() }}
                </strong>
                @if ($project->threats->where('status', 'open')->count() > 0)
                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                        Perlu Tindakan
                    </span>
                @else
                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Zero Blocker
                    </span>
                @endif
            </div>
            <div class="p-1.5 rounded-lg {{ $project->threats->where('status', 'open')->count() > 0 ? 'bg-rose-50 border border-rose-200' : 'bg-emerald-50/60 border border-emerald-200/60' }} mb-2 text-[11px]">
                <span id="statOpenThreatsSubtext" class="{{ $project->threats->where('status', 'open')->count() > 0 ? 'text-rose-800 font-semibold' : 'text-emerald-800 font-medium' }}">
                    {{ $project->threats->where('status', 'open')->count() > 0 ? 'Terdapat resiko operasional aktif' : 'Status project aman tanpa resiko aktif' }}
                </span>
            </div>
            <div class="text-[11px] text-slate-500 truncate">
                Monitoring kepatuhan & risiko klien
            </div>
        </div>
    </section>

    <!-- Main Content & Jira Board -->
    <div class="project-detail-layout grid" style="grid-template-columns: minmax(0, 1.8fr) minmax(320px, 0.8fr); gap: 20px;">
        <div>
            <section class="project-team-strip" aria-label="Tim proyek">
                <span class="project-team-label">Tim proyek</span>
                <div class="flex items-center -space-x-2">
                    @php $reviewer = $project->reviewer ?? $project->creator; @endphp
                    @if ($reviewer)
                        <span class="project-detail-avatar bg-slate-900 text-white" title="Reviewer: {{ $reviewer->name }}" aria-label="Reviewer: {{ $reviewer->name }}">{{ \Illuminate\Support\Str::of($reviewer->name)->explode(' ')->filter()->take(2)->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))->join('') }}</span>
                    @endif
                    @foreach ($project->accountingStaff as $member)
                        <span class="project-detail-avatar bg-blue-100 text-blue-800" title="PIC Accounting: {{ $member->name }}" aria-label="PIC Accounting: {{ $member->name }}">{{ \Illuminate\Support\Str::of($member->name)->explode(' ')->filter()->take(2)->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))->join('') }}</span>
                    @endforeach
                    @foreach ($project->taxStaff as $member)
                        <span class="project-detail-avatar bg-amber-100 text-amber-800" title="PIC Tax: {{ $member->name }}" aria-label="PIC Tax: {{ $member->name }}">{{ \Illuminate\Support\Str::of($member->name)->explode(' ')->filter()->take(2)->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))->join('') }}</span>
                    @endforeach
                </div>
                <span class="text-xs text-slate-500">{{ $project->tasks->where('status', 'in_progress')->count() }} tugas berjalan</span>
            </section>

            <!-- View Switcher Tabs: Board vs List vs Dokumen Klien -->
            <div class="flex items-center justify-between mb-4 border-b border-slate-200 pb-2">
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        id="tabBoardBtn"
                        onclick="switchProjectView('board')"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#0b192c] text-white flex items-center gap-1.5 transition cursor-pointer"
                    >
                        <x-heroicon-o-squares-2x2 class="w-4 h-4" />
                        <span>Board</span>
                    </button>
                    <button
                        type="button"
                        id="tabListBtn"
                        onclick="switchProjectView('list')"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition cursor-pointer"
                    >
                        <x-heroicon-o-queue-list class="w-4 h-4" />
                        <span>Daftar</span>
                    </button>
                    <button
                        type="button"
                        id="tabDocsBtn"
                        onclick="switchProjectView('documents')"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition cursor-pointer"
                    >
                        <x-heroicon-o-document-check class="w-4 h-4" />
                        <span>Dokumen</span>
                        @php
                            $totalDocs = $project->documents->count();
                            $verifiedDocs = $project->documents->where('status', 'verified')->count();
                            $criticalPending = $project->documents->where('is_critical', true)->whereIn('status', ['pending', 'partial'])->count();
                        @endphp
                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-full {{ $criticalPending > 0 ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-slate-100 text-slate-700' }}" id="tabDocsBadge">
                            {{ $verifiedDocs }}/{{ $totalDocs }}
                        </span>
                    </button>
                    <button
                        type="button"
                        id="tabBudgetBtn"
                        onclick="switchProjectView('budget')"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition cursor-pointer"
                    >
                        <x-heroicon-o-chart-bar class="w-4 h-4" />
                        <span>Budget</span>
                        @if ($effectiveBudget > 0)
                            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-full {{ $isOverBudget ? 'bg-rose-100 text-rose-800 border border-rose-200' : ($isWarning ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200') }}" id="tabBudgetBadge">
                                {{ $burnRate }}%
                            </span>
                        @endif
                    </button>
                </div>
                <span class="text-xs text-slate-400">{{ $project->tasks->count() }} tugas</span>
            </div>

            <!-- 1. JIRA KANBAN BOARD VIEW -->
            <div id="jiraBoardView" class="kanban-workspace" tabindex="0" aria-label="Kanban proyek">
                <div class="kanban-lanes">
                    @php
                        $columns = [
                            ['status' => 'not_started', 'label' => 'To Do', 'color' => 'slate', 'badge' => 'bg-slate-100 text-slate-700 border-slate-200'],
                            ['status' => 'in_progress', 'label' => 'In Progress', 'color' => 'blue', 'badge' => 'bg-blue-50 text-blue-800 border-blue-200'],
                            ['status' => 'waiting_client', 'label' => 'Waiting Client', 'color' => 'amber', 'badge' => 'bg-amber-50 text-amber-800 border-amber-200'],
                            ['status' => 'in_review', 'label' => 'In Review (QA)', 'color' => 'indigo', 'badge' => 'bg-indigo-50 text-indigo-800 border-indigo-200'],
                            ['status' => 'completed', 'label' => 'Done', 'color' => 'emerald', 'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
                        ];
                    @endphp

                    @foreach ($columns as $col)
                        @php
                            $colTasks = $project->tasks->where('status', $col['status']);
                        @endphp
                        <section class="kanban-lane bg-slate-100/70 border border-slate-200/80 rounded-xl p-3">
                            <!-- Column Header -->
                            <div class="flex items-center justify-between mb-2.5 px-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[11px] font-bold text-slate-800 uppercase tracking-wider">{{ $col['label'] }}</span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-full border {{ $col['badge'] }}" id="col-count-{{ $col['status'] }}">
                                        {{ $colTasks->count() }}
                                    </span>
                                </div>
                            </div>

                            <!-- Task Cards in this column -->
                            <div class="kanban-lane-tasks space-y-2.5" id="kanban-col-{{ $col['status'] }}">
                                @foreach ($colTasks as $task)
                                    <article class="bg-white border border-slate-200 rounded-lg p-3 shadow-xs hover:shadow-sm hover:border-slate-300 transition-all group task-card-item" id="task-card-{{ $task->id }}" data-task-id="{{ $task->id }}" data-status="{{ $task->status }}">
                                        <!-- Issue Key, Status Badges & Priority -->
                                        <div class="flex items-center justify-between gap-1 mb-1.5">
                                            <span class="text-[10.5px] font-mono font-semibold text-slate-500">
                                                TSK-{{ $task->id }}
                                            </span>
                                            <div class="flex items-center gap-1">
                                                @if ($task->threats->where('status', 'open')->count() > 0)
                                                    <span title="Open risks on this task" class="text-rose-600">
                                                        <x-heroicon-s-exclamation-triangle class="w-3.5 h-3.5" />
                                                    </span>
                                                @endif
                                                @if ($task->status === 'in_review')
                                                    <span class="text-[9px] font-bold px-1 py-0.2 rounded bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                        QA Gate
                                                    </span>
                                                @elseif ($task->isRevisionRequested())
                                                    <span class="text-[9px] font-bold px-1 py-0.2 rounded bg-rose-50 text-rose-700 border border-rose-200" title="{{ $task->review_notes }}">
                                                        Revisi
                                                    </span>
                                                @elseif ($task->isApproved())
                                                    <span class="text-[9px] font-bold px-1 py-0.2 rounded bg-emerald-50 text-emerald-700 border border-emerald-200" title="Diverifikasi oleh {{ $task->reviewer?->name ?? 'Reviewer' }}">
                                                        Verified
                                                    </span>
                                                @endif
                                                <span class="text-[9.5px] font-bold px-1.5 py-0.2 rounded bg-slate-100 text-slate-700 uppercase task-progress-badge">
                                                    {{ $task->progress_percent }}%
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Title -->
                                        <div class="text-xs font-semibold text-slate-900 leading-snug mb-1.5 task-title-text">
                                            {{ $task->title }}
                                        </div>

                                        @if ($task->isRevisionRequested() && $task->review_notes)
                                            <div class="p-1.5 rounded bg-rose-50 border border-rose-200/80 text-[10.5px] text-rose-900 mb-2 leading-relaxed">
                                                <div class="font-bold text-rose-800 text-[9.5px] flex items-center gap-1 mb-0.5">
                                                    <x-heroicon-s-exclamation-triangle class="w-3 h-3 text-rose-600" />
                                                    <span>Catatan Revisi:</span>
                                                </div>
                                                <div class="line-clamp-2">{{ $task->review_notes }}</div>
                                            </div>
                                        @elseif ($task->notes)
                                            <p class="text-[11px] text-slate-500 line-clamp-2 mb-2 task-notes-text">
                                                {{ $task->notes }}
                                            </p>
                                        @endif

                                        <!-- Quality Checklist & Time Budget Buttons -->
                                        <div class="mb-1.5 flex items-center justify-between gap-1 flex-wrap">
                                            <button
                                                type="button"
                                                onclick="openQualityGateModal({{ $task->id }})"
                                                class="inline-flex items-center gap-1 text-[10px] font-medium text-slate-700 hover:text-indigo-700 bg-slate-50 hover:bg-indigo-50/60 px-1.5 py-0.5 rounded border border-slate-200 transition cursor-pointer"
                                                title="Buka Quality Gate & Checklist Kertas Kerja"
                                            >
                                                <x-heroicon-o-clipboard-document-check class="w-3.5 h-3.5 text-indigo-600" />
                                                <span>{{ $task->checklists->where('is_checked', true)->count() }}/{{ $task->checklists->count() }} QC</span>
                                            </button>

                                            <!-- Task Time Budget Pill Button -->
                                            @php
                                                $tBudgetStatus = $task->budgetStatus();
                                                $tEstimate = (float) ($task->estimated_hours ?? 0);
                                                $tBurn = $task->burnRatePercent();
                                            @endphp
                                            <button
                                                type="button"
                                                id="task-budget-pill-{{ $task->id }}"
                                                onclick="openTaskBudgetModal({{ $task->id }}, '{{ addslashes($task->title) }}', {{ $tEstimate }}, {{ $task->actualLoggedHours() }}, {{ $tBurn }}, '{{ $tBudgetStatus }}')"
                                                class="inline-flex items-center gap-1 text-[10px] font-medium px-1.5 py-0.5 rounded border transition cursor-pointer {{ $tBudgetStatus === 'over_budget' ? 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' : ($tBudgetStatus === 'warning' ? 'bg-amber-50 text-amber-800 border-amber-200 hover:bg-amber-100' : ($tEstimate > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100')) }}"
                                                title="Realisasi {{ $task->formattedActualLoggedTime() }} / {{ $tEstimate }} jam (Burn: {{ $tBurn }}%) · Klik untuk ubah alokasi"
                                            >
                                                <x-heroicon-o-clock class="w-3.5 h-3.5 text-slate-500" />
                                                <span class="font-mono" id="task-budget-pill-text-{{ $task->id }}">{{ $task->formattedActualLoggedTime() }} / {{ $tEstimate }}j</span>
                                                @if ($tBudgetStatus === 'over_budget')
                                                    <span class="text-[8px] font-bold px-1 py-0.2 rounded bg-rose-200/80 text-rose-800" id="task-budget-pill-badge-{{ $task->id }}">Over</span>
                                                @elseif ($tBudgetStatus === 'warning')
                                                    <span class="text-[8px] font-bold px-1 py-0.2 rounded bg-amber-200/80 text-amber-800" id="task-budget-pill-badge-{{ $task->id }}">{{ $tBurn }}%</span>
                                                @endif
                                            </button>
                                        </div>

                                        <!-- Progress Bar -->
                                        <div class="w-full bg-slate-100 rounded-full h-1.5 mb-2 overflow-hidden">
                                            <div class="bg-[#0b192c] h-1.5 rounded-full task-progress-bar-fill" style="width: {{ $task->progress_percent }}%"></div>
                                        </div>

                                        <!-- Footer: Assignee & Due Date -->
                                        <div class="flex items-center justify-between pt-1 border-t border-slate-100 text-[11px] text-slate-500">
                                            <div class="flex items-center gap-1">
                                                <div class="w-5 h-5 rounded-full bg-[#1e3e62] text-white text-[9px] font-bold flex items-center justify-center shrink-0">
                                                    {{ substr($task->assignee?->name ?? 'U', 0, 1) }}
                                                </div>
                                                <span class="truncate max-w-[70px]" title="{{ $task->assignee?->name ?? 'Unassigned' }}">
                                                    {{ $task->assignee?->name ?? 'Unassigned' }}
                                                </span>
                                            </div>
                                            @if ($task->due_date)
                                                <span class="{{ $task->due_date->isPast() && $task->status !== 'completed' ? 'text-rose-600 font-bold' : '' }}">
                                                    {{ $task->due_date->format('d M') }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Quick Status Move Menu & Timer Action -->
                                        <div class="mt-2 pt-1.5 border-t border-dashed border-slate-100 flex items-center justify-between gap-1">
                                            @php
                                                $canEditThisTask = !auth()->check() || !auth()->user()->isStaff() || (int)$task->assigned_to === (int)auth()->id();
                                                $isReviewerOrAdmin = auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isReviewer());
                                            @endphp

                                            <div class="flex items-center gap-1">
                                                @if(auth()->check() && auth()->user()->isStaff() && (int)$task->assigned_to === (int)auth()->id())
                                                    <button
                                                        type="button"
                                                        data-task-timer-btn="{{ $task->id }}"
                                                        onclick="window.KonsulinTimer.start({{ $task->id }})"
                                                        class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition cursor-pointer shrink-0"
                                                        title="Mulai Waktu Kerja"
                                                    >
                                                        <x-heroicon-o-play class="w-3 h-3 text-slate-500" />
                                                        <span>Mulai</span>
                                                    </button>

                                                    @if($task->status === 'in_progress')
                                                        <button
                                                            type="button"
                                                            onclick="submitTaskForReview({{ $task->id }})"
                                                            class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 transition cursor-pointer shrink-0"
                                                            title="Ajukan tugas ini ke Reviewer"
                                                        >
                                                            <x-heroicon-o-arrow-up-tray class="w-3 h-3" />
                                                            <span>Review</span>
                                                        </button>
                                                    @endif
                                                @endif

                                                @if($isReviewerOrAdmin)
                                                    <button
                                                        type="button"
                                                        onclick="openQualityGateModal({{ $task->id }})"
                                                        class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-600 hover:bg-indigo-700 text-white transition cursor-pointer shrink-0"
                                                        title="Buka Lembar Verifikasi Quality Gate"
                                                    >
                                                        <x-heroicon-o-shield-check class="w-3 h-3" />
                                                        <span>QC</span>
                                                    </button>
                                                @endif
                                            </div>

                                            <form method="POST" action="{{ route('projects.tasks.update-status', [$project, $task]) }}" class="inline-flex gap-1 m-0">
                                                @csrf
                                                @method('PATCH')
                                                <select
                                                    name="status"
                                                    @if(!$canEditThisTask)
                                                        disabled
                                                        title="Hanya staff yang ditugaskan yang dapat memperbarui tugas ini"
                                                        class="text-[10px] py-0.5 px-1 h-6 bg-slate-100 border border-slate-200 rounded text-slate-400 cursor-not-allowed font-medium task-status-select"
                                                    @else
                                                        onchange="changeTaskStatus(this, '{{ route('projects.tasks.update-status', [$project, $task]) }}', {{ $task->id }})"
                                                        class="text-[10px] py-0.5 px-1 h-6 bg-slate-50 border border-slate-200 rounded text-slate-700 cursor-pointer font-medium task-status-select"
                                                    @endif
                                                >
                                                    <option value="not_started" {{ $task->status === 'not_started' ? 'selected' : '' }}>To Do</option>
                                                    <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                    <option value="waiting_client" {{ $task->status === 'waiting_client' ? 'selected' : '' }}>Waiting</option>
                                                    <option value="in_review" {{ $task->status === 'in_review' ? 'selected' : '' }}>In Review</option>
                                                    <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Done</option>
                                                </select>
                                            </form>
                                        </div>
                                    </article>
                                @endforeach

                                <div class="kanban-empty-state text-center text-[11px] text-slate-400 border border-dashed border-slate-200 rounded-lg col-empty-placeholder" id="empty-col-{{ $col['status'] }}" style="{{ $colTasks->count() > 0 ? 'display: none;' : '' }}">
                                    No tasks in {{ $col['label'] }}
                                </div>
                            </div>
                        </section>
                    @endforeach
                </div>
            </div>

            <!-- 2. JIRA LIST / TABLE VIEW (Retained for monitoring & tests) -->
            <div id="jiraListView" style="display: none;">
                <section class="panel">
                    <h2>Task Monitoring</h2>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Employee</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Due</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="tasksTableBody">
                                @forelse ($project->tasks as $task)
                                    <tr id="table-task-row-{{ $task->id }}">
                                        <td>
                                            <div class="font-semibold text-slate-900">{{ $task->title }}</div>
                                            @if ($task->notes)
                                                <div class="text-xs text-slate-500">{{ $task->notes }}</div>
                                            @endif
                                            <div class="flex items-center gap-1.5 mt-1">
                                                @if ($task->status === 'in_review')
                                                    <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-indigo-50 text-indigo-700 border border-indigo-200">QA Gate</span>
                                                @elseif ($task->isRevisionRequested())
                                                    <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-rose-50 text-rose-700 border border-rose-200" title="{{ $task->review_notes }}">Revisi</span>
                                                @elseif ($task->isApproved())
                                                    <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">Verified</span>
                                                @endif
                                                <button type="button" onclick="openQualityGateModal({{ $task->id }})" class="text-[10px] font-medium text-slate-600 hover:text-indigo-700 bg-slate-100 hover:bg-indigo-50 px-1.5 py-0.2 rounded border border-slate-200 cursor-pointer">
                                                    {{ $task->checklists->where('is_checked', true)->count() }}/{{ $task->checklists->count() }} QC
                                                </button>
                                            </div>
                                        </td>
                                        <td>{{ $task->assignee?->name ?? 'Unassigned' }}</td>
                                        <td><span class="label task-table-status-label">{{ str_replace('_', ' ', $task->status) }}</span></td>
                                        <td>
                                            <div class="font-bold task-table-progress">{{ $task->progress_percent }}%</div>
                                        </td>
                                        <td>{{ $task->due_date?->format('d M Y') ?? '-' }}</td>
                                        <td>
                                            <div class="flex items-center gap-1">
                                                <button
                                                    type="button"
                                                    onclick="openQualityGateModal({{ $task->id }})"
                                                    class="inline-flex items-center gap-1 px-2 py-1 rounded text-[11px] font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition cursor-pointer"
                                                    title="Buka Quality Gate"
                                                >
                                                    <x-heroicon-o-shield-check class="w-3 h-3 text-indigo-600" />
                                                    <span>QC</span>
                                                </button>
                                                @if(auth()->check() && auth()->user()->isStaff() && (int)$task->assigned_to === (int)auth()->id())
                                                    <button
                                                        type="button"
                                                        data-task-timer-btn="{{ $task->id }}"
                                                        onclick="window.KonsulinTimer.start({{ $task->id }})"
                                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-[11px] font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition cursor-pointer"
                                                        title="Mulai Waktu Kerja"
                                                    >
                                                        <x-heroicon-o-play class="w-3 h-3 text-slate-500" />
                                                        <span>Mulai</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="emptyTasksTableRow"><td colspan="6" class="muted">No tasks yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- 3. CLIENT INPUT DOCUMENTS VAULT VIEW -->
            <div id="jiraDocsView" style="display: none;">
                <section class="panel">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-200">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h2 style="margin: 0; font-size: 15px;">Checklist Dokumen Masukan Klien</h2>
                                <span class="label navy text-xs" id="docVaultEngagementBadge">{{ $project->service_type }}</span>
                            </div>
                            <p class="muted text-xs">
                                Kawal kelengkapan dan verifikasi berkas masukan dari klien sebelum penyusunan kertas kerja, rekonsiliasi, dan pelaporan.
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button
                                type="button"
                                onclick="populateDefaultDocs('{{ strtolower($project->service_type ?? 'advisory') }}')"
                                class="button secondary small inline-flex items-center gap-1.5 text-xs font-semibold"
                                title="Muat ulang atau lengkapi berkas checklist standar"
                            >
                                <x-heroicon-o-arrow-path class="w-3.5 h-3.5 text-slate-500" />
                                <span>Checklist Standar</span>
                            </button>
                            <button
                                type="button"
                                onclick="openAddDocumentModal()"
                                class="button small inline-flex items-center gap-1.5 text-xs font-semibold"
                            >
                                <x-heroicon-o-plus class="w-3.5 h-3.5" />
                                <span>Tambah Dokumen</span>
                            </button>
                        </div>
                    </div>

                    <!-- Document Vault Progress Summary Bar -->
                    @php
                        $docTotal = $project->documents->count();
                        $docVerified = $project->documents->where('status', 'verified')->count();
                        $docReceived = $project->documents->whereIn('status', ['received', 'verified'])->count();
                        $docPending = $project->documents->where('status', 'pending')->count();
                        $docPartial = $project->documents->where('status', 'partial')->count();
                        $docCriticalPending = $project->documents->where('is_critical', true)->whereIn('status', ['pending', 'partial'])->count();
                        $docPercent = $docTotal > 0 ? (int) round(($docReceived / $docTotal) * 100) : 0;
                    @endphp
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-700">Tingkat Kelengkapan Berkas:</span>
                                <strong class="text-xs font-mono font-bold text-slate-900" id="docVaultProgressText">{{ $docPercent }}%</strong>
                                <span class="text-[11px] text-slate-500 font-medium" id="docVaultCountText">({{ $docReceived }} dari {{ $docTotal }} berkas diterima)</span>
                            </div>
                            <div>
                                @if($docCriticalPending > 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-bold bg-rose-100 text-rose-800 border border-rose-200" id="docVaultCriticalAlert">
                                        <x-heroicon-s-exclamation-triangle class="w-3.5 h-3.5" />
                                        <span>{{ $docCriticalPending }} Berkas Kritis Belum Diterima</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200" id="docVaultCriticalAlert">
                                        <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                                        <span>Semua Berkas Kritis Lengkap</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-[#0b192c] h-2 rounded-full transition-all duration-300" id="docVaultProgressBar" style="width: {{ $docPercent }}%"></div>
                        </div>
                    </div>

                    <!-- Filter Chips -->
                    <div class="flex items-center gap-1.5 mb-3 overflow-x-auto pb-1 text-xs">
                        <button type="button" onclick="filterDocuments('all')" class="doc-filter-btn px-2.5 py-1 rounded-lg font-semibold bg-[#0b192c] text-white" data-filter="all">Semua ({{ $docTotal }})</button>
                        <button type="button" onclick="filterDocuments('critical')" class="doc-filter-btn px-2.5 py-1 rounded-lg font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50" data-filter="critical">Kritis ({{ $project->documents->where('is_critical', true)->count() }})</button>
                        <button type="button" onclick="filterDocuments('pending')" class="doc-filter-btn px-2.5 py-1 rounded-lg font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50" data-filter="pending">Belum Diterima ({{ $docPending }})</button>
                        <button type="button" onclick="filterDocuments('partial')" class="doc-filter-btn px-2.5 py-1 rounded-lg font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50" data-filter="partial">Sebagian ({{ $docPartial }})</button>
                        <button type="button" onclick="filterDocuments('received')" class="doc-filter-btn px-2.5 py-1 rounded-lg font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50" data-filter="received">Diterima ({{ $project->documents->where('status', 'received')->count() }})</button>
                        <button type="button" onclick="filterDocuments('verified')" class="doc-filter-btn px-2.5 py-1 rounded-lg font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50" data-filter="verified">Diverifikasi ({{ $docVerified }})</button>
                    </div>

                    <!-- Documents Table -->
                    <div class="table-wrap">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 text-slate-600 text-xs uppercase tracking-wider bg-slate-50/50">
                                    <th class="py-2.5 px-3">Dokumen & Kategori</th>
                                    <th class="py-2.5 px-3">Tenggat</th>
                                    <th class="py-2.5 px-3">Status Berkas</th>
                                    <th class="py-2.5 px-3">Tautan Berkas</th>
                                    <th class="py-2.5 px-3 text-right">Aksi & Eskalasi</th>
                                </tr>
                            </thead>
                            <tbody id="clientDocsTableBody" class="divide-y divide-slate-100 text-xs">
                                @forelse ($project->documents as $doc)
                                    <tr class="hover:bg-slate-50/60 transition-colors client-doc-row" id="client-doc-row-{{ $doc->id }}" data-status="{{ $doc->status }}" data-critical="{{ $doc->is_critical ? 'true' : 'false' }}">
                                        <td class="py-3 px-3">
                                            <div class="flex items-start gap-2">
                                                @if ($doc->is_critical)
                                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200 shrink-0" title="Dokumen Wajib / Kritis">
                                                        Kritis
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 shrink-0">
                                                        Standar
                                                    </span>
                                                @endif
                                                <div>
                                                    <div class="font-semibold text-slate-900 flex items-center gap-1.5">
                                                        <span>{{ $doc->title }}</span>
                                                        <span class="text-[10px] font-medium px-1.5 py-0.2 rounded bg-blue-50 text-blue-700 border border-blue-100">{{ $doc->category }}</span>
                                                    </div>
                                                    @if ($doc->notes)
                                                        <div class="text-[11px] text-slate-500 mt-0.5">{{ $doc->notes }}</div>
                                                    @endif
                                                    @if ($doc->status === 'verified' && $doc->verifier)
                                                        <div class="text-[10px] text-emerald-700 font-medium mt-1 flex items-center gap-1">
                                                            <x-heroicon-s-check-circle class="w-3 h-3 text-emerald-600" />
                                                            <span>Diverifikasi oleh {{ $doc->verifier->name }} ({{ $doc->verified_at?->format('d M H:i') }})</span>
                                                        </div>
                                                    @elseif ($doc->status === 'received' && $doc->received_at)
                                                        <div class="text-[10px] text-blue-700 font-medium mt-1 flex items-center gap-1">
                                                            <x-heroicon-o-clock class="w-3 h-3 text-blue-600" />
                                                            <span>Diterima pada {{ $doc->received_at->format('d M H:i') }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-3 shrink-0">
                                            @if ($doc->due_date)
                                                <div class="font-medium {{ $doc->isOverdue() ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                                    {{ $doc->due_date->format('d M Y') }}
                                                </div>
                                                @if ($doc->isOverdue())
                                                    <span class="text-[10px] font-bold text-rose-600 block">Terlambat</span>
                                                @endif
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3">
                                            <select
                                                onchange="updateDocStatus({{ $doc->id }}, this.value)"
                                                class="text-[11px] font-semibold py-1 px-2 rounded-lg border cursor-pointer doc-status-select-{{ $doc->id }} {{ match($doc->status) {
                                                    'verified' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                                    'received' => 'bg-blue-50 text-blue-800 border-blue-200',
                                                    'partial' => 'bg-amber-50 text-amber-800 border-amber-200',
                                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                                } }}"
                                            >
                                                <option value="pending" @selected($doc->status === 'pending')>Belum Diterima</option>
                                                <option value="partial" @selected($doc->status === 'partial')>Sebagian / Kurang</option>
                                                <option value="received" @selected($doc->status === 'received')>Diterima</option>
                                                <option value="verified" @selected($doc->status === 'verified')>Diverifikasi</option>
                                            </select>
                                        </td>
                                        <td class="py-3 px-3">
                                            @if ($doc->file_url)
                                                <div class="flex items-center gap-1.5">
                                                    <a href="{{ $doc->file_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-semibold text-xs truncate max-w-[140px]" title="{{ $doc->file_url }}">
                                                        <x-heroicon-o-link class="w-3.5 h-3.5 shrink-0" />
                                                        <span>Buka Berkas</span>
                                                    </a>
                                                    <button type="button" onclick="openDocLinkModal({{ $doc->id }}, '{{ addslashes($doc->file_url) }}', '{{ addslashes($doc->notes ?? '') }}')" class="text-slate-400 hover:text-slate-600 p-0.5 cursor-pointer" title="Edit Tautan">
                                                        <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                                                    </button>
                                                </div>
                                            @else
                                                <button
                                                    type="button"
                                                    onclick="openDocLinkModal({{ $doc->id }}, '', '{{ addslashes($doc->notes ?? '') }}')"
                                                    class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500 hover:text-blue-600 bg-slate-100 hover:bg-blue-50 px-2 py-0.5 rounded border border-slate-200 transition cursor-pointer"
                                                >
                                                    <x-heroicon-o-plus class="w-3 h-3" />
                                                    <span>Tautkan File/Drive</span>
                                                </button>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if ($doc->threat && $doc->threat->status === 'open')
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200" title="Dokumen telah dilaporkan sebagai ancaman operasional">
                                                        <x-heroicon-s-shield-exclamation class="w-3.5 h-3.5 text-rose-600" />
                                                        <span>Threat Aktif</span>
                                                    </span>
                                                @elseif (in_array($doc->status, ['pending', 'partial']))
                                                    <button
                                                        type="button"
                                                        onclick="escalateDocThreat({{ $doc->id }}, '{{ addslashes($doc->title) }}')"
                                                        class="inline-flex items-center gap-1 px-2 py-1 rounded text-[11px] font-bold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition cursor-pointer"
                                                        title="Eskalasi keterlambatan berkas ini ke resiko/threat operasional"
                                                    >
                                                        <x-heroicon-o-shield-exclamation class="w-3 h-3 text-rose-600" />
                                                        <span>Eskalasi Threat</span>
                                                    </button>
                                                @endif

                                                @if (!auth()->check() || !auth()->user()->isStaff() || auth()->user()->isAdmin())
                                                    <button
                                                        type="button"
                                                        onclick="deleteClientDoc({{ $doc->id }})"
                                                        class="text-slate-400 hover:text-rose-600 p-1 transition cursor-pointer"
                                                        title="Hapus Dokumen"
                                                    >
                                                        <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="emptyClientDocsRow">
                                        <td colspan="5" class="py-8 text-center text-slate-400">
                                            <x-heroicon-o-folder-open class="w-8 h-8 mx-auto mb-1 text-slate-300" />
                                            <p class="text-xs font-medium">Belum ada dokumen masukan yang dicatat.</p>
                                            <button type="button" onclick="populateDefaultDocs('{{ strtolower($project->service_type ?? 'advisory') }}')" class="mt-2 text-xs font-bold text-blue-600 hover:underline cursor-pointer">
                                                Muat Checklist Standar Otomatis &rarr;
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- 4. PROJECT BUDGETING & WORK TIME REALIZATION VIEW -->
            <div id="jiraBudgetView" style="display: none;">
                <section class="panel">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-200">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h2 style="margin: 0; font-size: 15px;">Realisasi Jam Kerja vs Estimasi (Project Budgeting)</h2>
                                <span class="label {{ $isOverBudget ? 'danger' : ($isWarning ? 'warning' : 'navy') }} text-xs" id="budgetTabMainStatusBadge">
                                    {{ $isOverBudget ? 'Over-Budget' : ($isWarning ? 'Mendekati Batas (>=80%)' : ($effectiveBudget > 0 ? 'On-Track' : 'Tanpa Kuota')) }}
                                </span>
                            </div>
                            <p class="muted text-xs">
                                Evaluasi efisiensi waktu kerja staf, rasio burn rate terhadap target kuota proyek, serta mitigasi resiko pengerjaan berlebih.
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if (!auth()->check() || auth()->user()->isAdmin() || auth()->user()->isReviewer())
                                <button
                                    type="button"
                                    onclick="openEditProjectBudgetModal()"
                                    class="button secondary small inline-flex items-center gap-1.5 text-xs font-semibold"
                                    title="Ubah target alokasi kuota waktu untuk proyek ini"
                                >
                                    <x-heroicon-o-adjustments-horizontal class="w-3.5 h-3.5 text-slate-500" />
                                    <span>Set Kuota Proyek</span>
                                </button>
                            @endif
                            <button
                                type="button"
                                onclick="document.getElementById('taskFormModal').showModal()"
                                class="button small inline-flex items-center gap-1.5 text-xs font-semibold"
                            >
                                <x-heroicon-o-plus class="w-3.5 h-3.5" />
                                <span>Tambah Tugas</span>
                            </button>
                        </div>
                    </div>

                    <!-- 4 Summary Stat Cards Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
                        <!-- Card 1: Target Kuota Proyek -->
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Target Kuota Proyek</span>
                                <x-heroicon-o-flag class="w-4 h-4 text-slate-400" />
                            </div>
                            <div class="flex items-baseline gap-1.5 mb-1">
                                <strong class="text-xl font-bold font-mono text-slate-900" id="budgetViewProjectBudget">
                                    {{ (float) $project->projectBudgetHours() }}j
                                </strong>
                                <span class="text-xs text-slate-500">disepakati</span>
                            </div>
                            <div class="text-[11px] text-slate-500 truncate" id="budgetViewTasksTotalEstimate">
                                Akumulasi tugas: {{ (float) $project->tasksTotalEstimatedHours() }} jam
                            </div>
                        </div>

                        <!-- Card 2: Realisasi Waktu Tercatat -->
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Realisasi Jam Kerja</span>
                                <x-heroicon-o-clock class="w-4 h-4 text-indigo-500" />
                            </div>
                            <div class="flex items-baseline gap-1.5 mb-1">
                                <strong class="text-xl font-bold font-mono text-slate-900" id="budgetViewTotalLogged">
                                    {{ $project->formattedTotalLoggedTime() }}
                                </strong>
                                <span class="text-xs text-slate-500" id="budgetViewDecimalHours">({{ $project->totalLoggedHours() }}j)</span>
                            </div>
                            <div class="text-[11px] text-slate-500 truncate">
                                Dari seluruh log timer staf aktif & selesai
                            </div>
                        </div>

                        <!-- Card 3: Burn Rate Efektif -->
                        <div class="p-3.5 rounded-xl {{ $isOverBudget ? 'bg-rose-50 border-rose-200' : ($isWarning ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-200') }}" id="budgetViewBurnBox">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10.5px] font-bold {{ $isOverBudget ? 'text-rose-700' : ($isWarning ? 'text-amber-800' : 'text-slate-500') }} uppercase tracking-wider">Burn Rate Efektif</span>
                                <x-heroicon-o-fire class="w-4 h-4 {{ $isOverBudget ? 'text-rose-600' : ($isWarning ? 'text-amber-600' : 'text-slate-400') }}" />
                            </div>
                            <div class="flex items-baseline gap-1.5 mb-1">
                                <strong class="text-xl font-bold font-mono {{ $isOverBudget ? 'text-rose-700' : ($isWarning ? 'text-amber-900' : 'text-slate-900') }}" id="budgetViewBurnRate">
                                    {{ $burnRate }}%
                                </strong>
                                <span class="text-[10.5px] font-bold px-1.5 py-0.2 rounded {{ $isOverBudget ? 'bg-rose-200/80 text-rose-900' : ($isWarning ? 'bg-amber-200/80 text-amber-900' : 'bg-emerald-100 text-emerald-800') }}" id="budgetViewBurnStatusPill">
                                    {{ $isOverBudget ? 'Over-Budget' : ($isWarning ? 'Waspada' : 'Aman') }}
                                </span>
                            </div>
                            <div class="text-[11px] {{ $isOverBudget ? 'text-rose-600 font-medium' : ($isWarning ? 'text-amber-700 font-medium' : 'text-slate-500') }} truncate" id="budgetViewBurnRateSubtext">
                                Target efisiensi: &le; 100%
                            </div>
                        </div>

                        <!-- Card 4: Sisa / Deviasi Waktu -->
                        <div class="p-3.5 rounded-xl {{ $isOverBudget ? 'bg-rose-50 border-rose-200' : 'bg-slate-50 border-slate-200' }}" id="budgetViewRemainingBox">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10.5px] font-bold {{ $isOverBudget ? 'text-rose-700' : 'text-slate-500' }} uppercase tracking-wider" id="budgetViewRemainingTitle">
                                    {{ $isOverBudget ? 'Kelebihan Jam (Over)' : 'Sisa Kuota Waktu' }}
                                </span>
                                <x-heroicon-o-scale class="w-4 h-4 {{ $isOverBudget ? 'text-rose-600' : 'text-slate-400' }}" />
                            </div>
                            <div class="flex items-baseline gap-1.5 mb-1">
                                <strong class="text-xl font-bold font-mono {{ $isOverBudget ? 'text-rose-700' : 'text-emerald-700' }}" id="budgetViewRemainingHours">
                                    {{ $isOverBudget ? '+' . $project->overBudgetHours() . 'j' : $project->remainingHours() . 'j' }}
                                </strong>
                                <span class="text-xs text-slate-500">tersisa</span>
                            </div>
                            <div class="text-[11px] {{ $isOverBudget ? 'text-rose-600 font-semibold' : 'text-slate-500' }} truncate" id="budgetViewRemainingSubtext">
                                {{ $isOverBudget ? 'Perlu penyesuaian scope / add-on' : 'Kapasitas pengerjaan masih tersedia' }}
                            </div>
                        </div>
                    </div>

                    <!-- Visual Burn Rate Bar Container -->
                    @if ($effectiveBudget > 0)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 mb-5" id="budgetViewMeterContainer">
                            <div class="flex items-center justify-between text-xs mb-1.5">
                                <span class="font-bold text-slate-700 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full {{ $isOverBudget ? 'bg-rose-600' : ($isWarning ? 'bg-amber-500' : 'bg-emerald-500') }}" id="budgetViewMeterDot"></span>
                                    <span>Meter Penggunaan Kuota Waktu</span>
                                </span>
                                <span class="font-mono text-slate-600 font-medium" id="budgetViewMeterSubtext">
                                    {{ $project->totalLoggedHours() }} jam dari batas {{ (float) $effectiveBudget }} jam
                                </span>
                            </div>
                            <div class="relative w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                                <div
                                    id="budgetViewProgressBar"
                                    class="h-3 rounded-full transition-all duration-500 {{ $isOverBudget ? 'bg-rose-600' : ($isWarning ? 'bg-amber-500' : 'bg-emerald-600') }}"
                                    style="width: {{ min(100, $burnRate) }}%"
                                ></div>
                            </div>
                            <div class="flex items-center justify-between text-[10.5px] text-slate-400 mt-1 font-mono">
                                <span>0%</span>
                                <span>80% (Batas Waspada)</span>
                                <span>100% (Target Disepakati)</span>
                            </div>
                        </div>
                    @endif

                    <!-- Detailed Tasks Budgeting Table -->
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase text-[10.5px] tracking-wider">
                                    <th class="py-2.5 px-3">Tugas & PIC</th>
                                    <th class="py-2.5 px-3">Status</th>
                                    <th class="py-2.5 px-3">Target Estimasi</th>
                                    <th class="py-2.5 px-3">Realisasi Jam</th>
                                    <th class="py-2.5 px-3">Burn Rate</th>
                                    <th class="py-2.5 px-3">Status Anggaran</th>
                                    <th class="py-2.5 px-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100" id="taskBudgetTableBody">
                                @forelse ($project->tasks as $t)
                                    @php
                                        $tEstimate = (float) ($t->estimated_hours ?? 0);
                                        $tActual = $t->actualLoggedHours();
                                        $tBurn = $t->burnRatePercent();
                                        $tStatus = $t->budgetStatus();
                                        $tOver = $tStatus === 'over_budget';
                                        $tWarn = $tStatus === 'warning';
                                    @endphp
                                    <tr class="hover:bg-slate-50/60 transition-colors" id="budget-table-row-{{ $t->id }}">
                                        <td class="py-3 px-3">
                                            <div class="font-bold text-slate-900 leading-snug">{{ $t->title }}</div>
                                            <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1.5">
                                                <span class="font-mono text-[10.5px] text-slate-400">TSK-{{ $t->id }}</span>
                                                <span>·</span>
                                                <span>{{ $t->assignee?->name ?? 'Unassigned' }}</span>
                                                @if ($t->due_date)
                                                    <span>·</span>
                                                    <span class="{{ $t->due_date->isPast() && $t->status !== 'completed' ? 'text-rose-600 font-semibold' : '' }}">
                                                        Due {{ $t->due_date->format('d M Y') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3 px-3 whitespace-nowrap" id="budget-row-task-status-{{ $t->id }}">
                                            @if ($t->status === 'completed')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">Selesai</span>
                                            @elseif ($t->status === 'in_review')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200">In Review</span>
                                            @elseif ($t->status === 'in_progress')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200">In Progress</span>
                                            @elseif ($t->status === 'waiting_client')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">Waiting</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">To Do</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5 font-mono">
                                                <strong class="text-slate-900 font-bold" id="budget-row-estimate-{{ $t->id }}">
                                                    {{ $tEstimate > 0 ? $tEstimate . 'j' : '-' }}
                                                </strong>
                                                <button
                                                    type="button"
                                                    onclick="openTaskBudgetModal({{ $t->id }}, '{{ addslashes($t->title) }}', {{ $tEstimate }}, {{ $tActual }}, {{ $tBurn }}, '{{ $tStatus }}')"
                                                    class="text-slate-400 hover:text-indigo-600 p-0.5 rounded hover:bg-indigo-50 transition cursor-pointer"
                                                    title="Ubah target estimasi jam kerja"
                                                >
                                                    <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                                                </button>
                                            </div>
                                        </td>
                                        <td class="py-3 px-3 whitespace-nowrap font-mono">
                                            <strong class="text-slate-900 font-bold" id="budget-row-actual-{{ $t->id }}">
                                                {{ $t->formattedActualLoggedTime() }}
                                            </strong>
                                            <span class="text-[11px] text-slate-400">({{ $tActual }}j)</span>
                                        </td>
                                        <td class="py-3 px-3 whitespace-nowrap">
                                            @if ($tEstimate > 0)
                                                <div class="w-24" id="budget-row-burn-container-{{ $t->id }}">
                                                    <div class="flex items-center justify-between text-[10.5px] font-mono mb-1">
                                                        <span class="font-bold {{ $tOver ? 'text-rose-700' : ($tWarn ? 'text-amber-800' : 'text-slate-700') }}" id="budget-row-burn-text-{{ $t->id }}">{{ $tBurn }}%</span>
                                                        <span class="text-[9.5px] text-slate-400" id="budget-row-delta-text-{{ $t->id }}">{{ $tOver ? '+' . $t->overBudgetHours() . 'j' : '-' . $t->remainingHours() . 'j' }}</span>
                                                    </div>
                                                    <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                                        <div
                                                            id="budget-row-burn-bar-{{ $t->id }}"
                                                            class="h-1.5 rounded-full {{ $tOver ? 'bg-rose-600' : ($tWarn ? 'bg-amber-500' : 'bg-emerald-600') }}"
                                                            style="width: {{ min(100, $tBurn) }}%"
                                                        ></div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-[11px] text-slate-400" id="budget-row-burn-container-{{ $t->id }}">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 whitespace-nowrap" id="budget-row-status-cell-{{ $t->id }}">
                                            @if ($tStatus === 'over_budget')
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    <x-heroicon-s-exclamation-triangle class="w-3 h-3 text-rose-600" />
                                                    <span>Over-Budget</span>
                                                </span>
                                            @elseif ($tStatus === 'warning')
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                    <x-heroicon-o-exclamation-circle class="w-3 h-3 text-amber-600" />
                                                    <span>Mendekati Kuota (>=80%)</span>
                                                </span>
                                            @elseif ($tEstimate > 0)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <x-heroicon-o-check-circle class="w-3 h-3 text-emerald-600" />
                                                    <span>Aman / On-Track</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                                    Tanpa Estimasi
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-right whitespace-nowrap">
                                            <div class="inline-flex items-center gap-1.5">
                                                <button
                                                    type="button"
                                                    onclick="openTaskBudgetModal({{ $t->id }}, '{{ addslashes($t->title) }}', {{ $tEstimate }}, {{ $tActual }}, {{ $tBurn }}, '{{ $tStatus }}')"
                                                    class="button secondary small text-xs py-1 px-2"
                                                >
                                                    Atur Estimasi
                                                </button>
                                                @if(auth()->check() && auth()->user()->isStaff() && (int)$t->assigned_to === (int)auth()->id())
                                                    <button
                                                        type="button"
                                                        onclick="window.KonsulinTimer && window.KonsulinTimer.start({{ $t->id }}, '{{ addslashes($t->title) }}', '{{ addslashes($project->client->name ?? 'Klien') }}')"
                                                        class="button small text-xs py-1 px-2"
                                                        title="Mulai Lacak Jam Kerja"
                                                    >
                                                        <x-heroicon-o-play class="w-3 h-3 text-emerald-400" />
                                                        <span>Timer</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="emptyBudgetTasksRow">
                                        <td colspan="7" class="py-8 text-center text-slate-400">
                                            <x-heroicon-o-clipboard-document-list class="w-8 h-8 mx-auto mb-1 text-slate-300" />
                                            <p class="text-xs font-medium">Belum ada tugas pada proyek ini.</p>
                                            <button type="button" onclick="document.getElementById('taskFormModal').showModal()" class="mt-2 text-xs font-bold text-blue-600 hover:underline cursor-pointer">
                                                + Tambah Tugas Baru &rarr;
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- Progress Stream / Activity Timeline -->
            <section class="panel mt-4">
                <div class="flex items-center justify-between mb-3">
                    <h2>Progress Updates</h2>
                    <button class="button secondary small" type="button" onclick="document.getElementById('progressModal').showModal()">
                        <x-heroicon-o-arrow-up-tray class="w-3.5 h-3.5" />
                        <span>Post Progress</span>
                    </button>
                </div>
                <div id="progressUpdatesList" class="space-y-3">
                    @forelse ($project->progressUpdates as $update)
                        <article class="card" style="margin-bottom: 12px;">
                            <div class="card-head">
                                <div>
                                    <h3 class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900">{{ $update->user->name }}</span>
                                        <span class="text-xs font-normal text-slate-500">uploaded</span>
                                        <span class="label success text-xs">{{ $update->progress_percent }}%</span>
                                    </h3>
                                    <p class="muted text-xs">{{ $update->task?->title ?? 'Project update' }} · {{ $update->created_at->format('d M Y H:i') }}</p>
                                </div>
                                <span class="label text-xs font-mono">#{{ $update->id }}</span>
                            </div>
                            <p class="text-xs text-slate-700 leading-relaxed">{{ $update->summary }}</p>
                            @if ($update->attachment_path)
                                <div class="mt-2 text-xs text-blue-600 font-medium flex items-center gap-1">
                                    <x-heroicon-o-paper-clip class="w-3.5 h-3.5" />
                                    <span>Attachment: {{ $update->attachment_path }}</span>
                                </div>
                            @endif
                        </article>
                    @empty
                        <p class="muted text-xs" id="emptyProgressNotice">No progress uploaded yet.</p>
                    @endforelse
                </div>
            </section>

            <!-- Operational Threats / Risk Management -->
            <section class="panel">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <h2>Threats</h2>
                        <span class="label danger text-xs" id="threatsOpenBadge">{{ $project->threats->where('status', 'open')->count() }} open</span>
                    </div>
                    <button class="button secondary small" type="button" onclick="document.getElementById('threatModal').showModal()">
                        <x-heroicon-o-shield-exclamation class="w-3.5 h-3.5 text-rose-600" />
                        <span>Log Threat</span>
                    </button>
                </div>
                <div id="threatsList" class="space-y-3">
                    @forelse ($project->threats as $threat)
                        <article class="card" style="margin-bottom: 12px; border-left: 3px solid {{ in_array($threat->severity, ['high', 'critical']) ? '#ef4444' : '#f59e0b' }};">
                            <div class="card-head">
                                <div>
                                    <h3 class="font-bold text-slate-900">{{ $threat->title }}</h3>
                                    <p class="muted text-xs">{{ $threat->user->name }} · {{ $threat->task?->title ?? 'Project' }} · logged {{ $threat->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="label text-xs {{ $threat->severity === 'high' || $threat->severity === 'critical' ? 'danger' : 'warning' }}">{{ $threat->severity }}</span>
                                    <span class="label text-xs">{{ $threat->status }}</span>
                                </div>
                            </div>
                            <p class="text-xs text-slate-700 mb-2">{{ $threat->description }}</p>
                            @if ($threat->mitigation_plan)
                                <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 text-xs">
                                    <strong class="text-slate-900 block mb-0.5">Mitigation Plan:</strong>
                                    <span class="text-slate-600">{{ $threat->mitigation_plan }}</span>
                                </div>
                            @endif
                        </article>
                    @empty
                        <p class="muted text-xs" id="emptyThreatsNotice">No threats logged.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <!-- Right Side Management Panel (Action Buttons & Overview) -->
        <aside class="flex flex-col gap-4" data-animate-children>
            <section class="panel !p-4 !mb-0">
                <h2 class="!text-sm !mb-3">Aksi proyek</h2>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="document.getElementById('taskFormModal').showModal()" class="project-icon-action" aria-label="Tambah tugas" title="Tambah tugas">
                        <x-heroicon-o-plus class="w-5 h-5" />
                    </button>
                    <button type="button" onclick="document.getElementById('progressModal').showModal()" class="project-icon-action" aria-label="Catat progres" title="Catat progres">
                        <x-heroicon-o-arrow-up-tray class="w-5 h-5" />
                    </button>
                    <button type="button" onclick="document.getElementById('threatModal').showModal()" class="project-icon-action text-rose-700" aria-label="Catat risiko" title="Catat risiko">
                        <x-heroicon-o-shield-exclamation class="w-5 h-5" />
                    </button>
                </div>
            </section>

            <!-- Client Documents Vault Summary Card -->
            <section class="panel !p-4 !mb-0 border border-slate-200 rounded-xl bg-white shadow-sm">
                <div class="mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="!text-sm !font-bold text-slate-900 !mb-0 flex items-center gap-1.5">
                        <x-heroicon-o-document-duplicate class="w-4 h-4 text-indigo-600" />
                        <span>Berkas Masukan Klien</span>
                    </h2>
                    <button type="button" onclick="switchProjectView('documents')" class="text-[11px] font-semibold text-blue-600 hover:text-blue-800 cursor-pointer">
                        Kelola &rarr;
                    </button>
                </div>

                <div class="space-y-2 mb-3">
                    <div class="flex items-baseline justify-between text-xs">
                        <span class="text-slate-500 font-medium">Kelengkapan</span>
                        <span class="font-bold font-mono text-slate-900" id="asideDocPercent">{{ $docPercent }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-[#0b192c] h-2 rounded-full transition-all duration-300" id="asideDocProgressBar" style="width: {{ $docPercent }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500">
                        <span id="asideDocReceivedText">{{ $docReceived }} dari {{ $docTotal }} diterima</span>
                        <span id="asideDocVerifiedText" class="font-medium text-emerald-700">{{ $docVerified }} terverifikasi</span>
                    </div>
                </div>

                @if ($docCriticalPending > 0)
                    <div class="p-2.5 rounded-lg bg-rose-50 border border-rose-200 text-xs mb-3 text-rose-800 flex items-center gap-2" id="asideDocAlertBox">
                        <x-heroicon-s-exclamation-triangle class="w-4 h-4 text-rose-600 shrink-0" />
                        <span class="text-[11px] font-semibold leading-tight" id="asideDocAlertText">{{ $docCriticalPending }} berkas kritis belum diserahkan klien.</span>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-2">
                    <button
                        type="button"
                        onclick="switchProjectView('documents')"
                        class="button secondary small w-full justify-center !text-xs !py-1.5 cursor-pointer"
                    >
                        <x-heroicon-o-list-bullet class="w-3.5 h-3.5" />
                        <span>Lihat Vault</span>
                    </button>
                    <button
                        type="button"
                        onclick="openAddDocumentModal()"
                        class="button small w-full justify-center !text-xs !py-1.5 cursor-pointer"
                    >
                        <x-heroicon-o-plus class="w-3.5 h-3.5" />
                        <span>Tambah</span>
                    </button>
                </div>
            </section>

            <!-- Project Meta & Client Overview Card -->
            <section class="panel !p-4 !mb-0 border border-slate-200 rounded-xl bg-white shadow-sm">
                <div class="mb-3 pb-2 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="!text-sm !font-bold text-slate-900 !mb-0 flex items-center gap-1.5">
                        <x-heroicon-o-building-office-2 class="w-4 h-4 text-slate-600" />
                        <span>Informasi Klien & Kontrak</span>
                    </h2>
                    <a href="{{ route('clients.show', $project->client_id) }}" class="text-[11px] font-semibold text-blue-600 hover:text-blue-800">
                        Detail &rarr;
                    </a>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Klien</span>
                        <a href="{{ route('clients.show', $project->client_id) }}" class="font-semibold text-slate-900 hover:text-blue-600 truncate max-w-[170px]">
                            {{ $project->client->name }}
                        </a>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">PIC Klien</span>
                        <span class="font-medium text-slate-800">{{ $project->client->client_pic ?: 'Tidak ada' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Status Pajak</span>
                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10.5px] font-bold bg-slate-100 text-slate-800">
                            {{ $project->client->tax_status ?: 'Non-PKP' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Skema PPh</span>
                        <span class="font-semibold text-slate-900">{{ $project->client->pph_scheme ?? 'Belum ditentukan' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Mulai</span>
                        <span class="font-medium text-slate-800">{{ $project->start_date?->format('d M Y') ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500">Tenggat Waktu</span>
                        <span class="font-semibold text-slate-900">{{ $project->due_date?->format('d M Y') ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <span class="text-slate-500">Reviewer / Lead</span>
                        <span class="font-semibold text-slate-900">{{ $project->reviewer?->name ?? 'Belum ditentukan' }}</span>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    <style>
        .project-detail-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 20px; padding-bottom: 18px; border-bottom: 1px solid #e2e8f0; }
        .project-back-link { display: inline-flex; align-items: center; gap: 6px; min-height: 32px; color: #64748b; font-size: 12px; font-weight: 600; }
        .project-back-link:hover { color: #0b192c; }
        .project-back-link:focus-visible, .project-icon-action:focus-visible { outline: 2px solid #1e3e62; outline-offset: 2px; }
        .project-detail-meta { display: flex; flex-wrap: wrap; gap: 6px; margin: 5px 0 0; color: #64748b; font-size: 12px; }
        .project-primary-action { min-height: 40px; flex-shrink: 0; }
        .project-detail-header + .stats { grid-template-columns: repeat(3, minmax(0, 1fr)); margin-bottom: 16px; }
        .project-detail-header + .stats > :nth-child(2) { display: none; }
        .project-detail-header + .stats .stat { padding: 14px; }
        .project-team-strip { display: flex; align-items: center; gap: 12px; min-height: 52px; margin-bottom: 16px; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; }
        .project-team-label { color: #475569; font-size: 12px; font-weight: 700; }
        .project-detail-avatar { display: inline-flex; width: 30px; height: 30px; align-items: center; justify-content: center; border: 2px solid #fff; border-radius: 9999px; font-size: 10px; font-weight: 700; }
        .project-icon-action { display: inline-flex; min-height: 44px; align-items: center; justify-content: center; border: 1px solid #cbd5e1; border-radius: 7px; background: #fff; color: #334155; cursor: pointer; transition: background-color .15s ease, border-color .15s ease; }
        .project-icon-action:hover { border-color: #94a3b8; background: #f8fafc; }
        .project-detail-layout { grid-template-columns: minmax(0, 1fr) !important; }
        .project-detail-layout > aside { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .kanban-workspace { overflow-x: auto; padding-bottom: 8px; scroll-snap-type: x proximity; }
        .kanban-workspace:focus-visible { outline: 2px solid #1e3e62; outline-offset: 3px; }
        .kanban-lanes { display: grid; grid-template-columns: repeat(5, minmax(220px, 1fr)); gap: 12px; min-width: 1160px; align-items: start; }
        .kanban-lane { min-height: 184px; scroll-snap-align: start; }
        .kanban-lane-tasks { min-height: 112px; }
        .kanban-empty-state { display: grid; min-height: 112px; place-items: center; padding: 16px; line-height: 1.4; }
        .task-card-item { min-width: 0; }
        .task-card-item .task-title-text { font-size: 13px; line-height: 1.4; }
        .task-card-item .task-status-select { min-width: 96px; min-height: 32px; }
        @media (max-width: 767px) {
            .project-detail-header { align-items: stretch; flex-direction: column; gap: 12px; }
            .project-primary-action { width: 100%; }
            .project-detail-header + .stats { grid-template-columns: 1fr; }
            .project-team-strip { flex-wrap: wrap; }
            .project-detail-layout > aside { grid-template-columns: 1fr; }
            .kanban-lanes { grid-template-columns: repeat(5, minmax(248px, 1fr)); min-width: 1300px; }
            .kanban-lane { min-height: 168px; }
        }
    </style>

    <!-- Modals for Full Dialogs -->
    <!-- Modals for Full Dialogs -->
    <dialog id="taskFormModal">
        <div class="modal-head">
            <h2>Create New Issue / Task</h2>
            <button class="icon-button" type="button" onclick="document.getElementById('taskFormModal').close()">&times;</button>
        </div>
        <form method="POST" action="{{ route('projects.tasks.store', $project) }}" class="modal-body" id="ajaxTaskForm">
            @csrf
            <label>Task title <input name="title" required placeholder="Contoh: Rekonsiliasi Faktur Pajak"></label>
            <div class="form-grid">
                <label>Employee
                    <select name="assigned_to">
                        <option value="">Unassigned</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Status
                    <select name="status">
                        @foreach (['not_started', 'in_progress', 'waiting_client', 'in_review', 'completed'] as $status)
                            <option value="{{ $status }}">{{ str_replace('_', ' ', $status) }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="form-grid">
                <label>Progress % <input type="number" name="progress_percent" min="0" max="100" value="0" required></label>
                <label>Estimasi Waktu Kerja (Jam) <input type="number" step="0.25" min="0" max="999" name="estimated_hours" value="0" placeholder="Contoh: 4.5"></label>
            </div>
            <div class="form-grid">
                <label>Due date <input type="date" name="due_date"></label>
            </div>
            <label>Notes <textarea name="notes" placeholder="Catatan detail..."></textarea></label>
            <div class="modal-actions">
                <button type="button" class="button secondary" onclick="document.getElementById('taskFormModal').close()">Cancel</button>
                <button class="button" type="submit">Save Task</button>
            </div>
        </form>
    </dialog>

    <!-- Modal: Task Budget & Estimasi Jam Kerja -->
    <dialog id="taskBudgetModal" class="rounded-2xl p-0 border border-slate-200 shadow-2xl backdrop:bg-slate-900/50 w-full max-w-md overflow-hidden m-auto">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center border border-indigo-100">
                    <x-heroicon-o-clock class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 leading-tight">Alokasi & Estimasi Jam Tugas</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5 truncate max-w-[240px]" id="budgetModalTaskTitle">Judul Tugas</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('taskBudgetModal').close()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition cursor-pointer">
                <x-heroicon-s-x-mark class="w-4 h-4" />
            </button>
        </div>
        <form id="ajaxTaskBudgetForm" class="p-5 space-y-4">
            @csrf
            <input type="hidden" id="budgetModalTaskId" name="task_id">

            <!-- Realization & Burn Rate Stat -->
            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-500 block mb-0.5 tracking-wider">Realisasi Tercatat</span>
                    <strong class="text-lg font-bold font-mono text-slate-900" id="budgetModalActualHours">0.0 jam</strong>
                </div>
                <div class="text-right">
                    <span class="text-[10px] uppercase font-bold text-slate-500 block mb-0.5 tracking-wider">Burn Rate Saat Ini</span>
                    <span class="text-xs font-bold font-mono px-2 py-0.5 rounded border" id="budgetModalBurnBadge">0%</span>
                </div>
            </div>

            <div>
                <label for="budgetModalEstimatedHours" class="block text-xs font-semibold text-slate-700 mb-1">
                    Target Estimasi Jam Kerja (Budget Hours) <span class="text-rose-600">*</span>
                </label>
                <div class="relative">
                    <input
                        type="number"
                        step="0.25"
                        min="0"
                        max="999"
                        id="budgetModalEstimatedHours"
                        name="estimated_hours"
                        required
                        placeholder="Contoh: 8.5"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#0b192c]/20 focus:border-[#0b192c] transition font-mono pr-12"
                    >
                    <span class="absolute right-3 top-2.5 text-xs text-slate-400 font-medium">Jam</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">
                    Target alokasi jam kerja staf untuk penyelesaian tugas ini.
                </p>
            </div>

            <div class="p-2.5 rounded-lg border text-xs" id="budgetModalStatusAlert">
                <!-- Status explanation populated by JS -->
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('taskBudgetModal').close()" class="button secondary text-xs py-1.5 px-3">Batal</button>
                <button type="submit" id="budgetModalSubmitBtn" class="button text-xs py-1.5 px-4">Simpan Estimasi</button>
            </div>
        </form>
    </dialog>

    <!-- Modal: Edit Project Budget Hours -->
    <dialog id="editProjectBudgetModal" class="rounded-2xl p-0 border border-slate-200 shadow-2xl backdrop:bg-slate-900/50 w-full max-w-md overflow-hidden m-auto">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center border border-indigo-100">
                    <x-heroicon-o-adjustments-horizontal class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 leading-tight">Target Kuota Waktu Proyek</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5 truncate max-w-[240px]">{{ $project->name }}</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('editProjectBudgetModal').close()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition cursor-pointer">
                <x-heroicon-s-x-mark class="w-4 h-4" />
            </button>
        </div>
        <form id="ajaxProjectBudgetForm" method="POST" action="{{ route('projects.update-budget', $project) }}" class="p-5 space-y-4">
            @csrf
            @method('PATCH')

            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-500 block mb-0.5 tracking-wider">Total Log Saat Ini</span>
                    <strong class="text-base font-bold font-mono text-slate-900" id="projectBudgetModalLoggedTime">{{ $project->formattedTotalLoggedTime() }}</strong>
                </div>
                <div class="text-right">
                    <span class="text-[10px] uppercase font-bold text-slate-500 block mb-0.5 tracking-wider">Total Estimasi Tugas</span>
                    <strong class="text-base font-bold font-mono text-slate-900" id="projectBudgetModalTasksEstimate">{{ (float) $project->tasksTotalEstimatedHours() }} jam</strong>
                </div>
            </div>

            <div>
                <label for="projectBudgetEstimatedHoursInput" class="block text-xs font-semibold text-slate-700 mb-1">
                    Kuota Jam Proyek (Budget Hours Cap)
                </label>
                <div class="relative">
                    <input
                        type="number"
                        step="0.5"
                        min="0"
                        max="9999"
                        id="projectBudgetEstimatedHoursInput"
                        name="estimated_hours"
                        value="{{ (float) $project->projectBudgetHours() }}"
                        placeholder="Contoh: 40"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#0b192c]/20 focus:border-[#0b192c] transition font-mono pr-12"
                    >
                    <span class="absolute right-3 top-2.5 text-xs text-slate-400 font-medium">Jam</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-1.5">
                    Masukkan 0 jika ingin kuota proyek dihitung otomatis dari akumulasi estimasi masing-masing tugas.
                </p>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('editProjectBudgetModal').close()" class="button secondary text-xs py-1.5 px-3">Batal</button>
                <button type="submit" id="projectBudgetSubmitBtn" class="button text-xs py-1.5 px-4">Simpan Kuota</button>
            </div>
        </form>
    </dialog>

    <dialog id="progressModal">
        <div class="modal-head">
            <h2>Post Progress Update</h2>
            <button class="icon-button" type="button" onclick="document.getElementById('progressModal').close()">&times;</button>
        </div>
        <form method="POST" action="{{ route('projects.progress.store', $project) }}" class="modal-body" id="ajaxProgressForm">
            @csrf
            <div class="form-grid">
                <label>Task
                    <select name="project_task_id" class="task-options-select">
                        <option value="">Project update</option>
                        @foreach ($project->tasks as $task)
                            <option value="{{ $task->id }}">{{ $task->title }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Progress %
                    <input type="number" name="progress_percent" min="0" max="100" value="{{ $project->progressPercent() }}" required>
                </label>
            </div>
            <label>Summary / Notes <textarea name="summary" required placeholder="Jelaskan progres hari ini..."></textarea></label>
            <div class="modal-actions">
                <button type="button" class="button secondary" onclick="document.getElementById('progressModal').close()">Cancel</button>
                <button class="button" type="submit">Post Update</button>
            </div>
        </form>
    </dialog>

    <dialog id="threatModal">
        <div class="modal-head">
            <h2>Add Threat / Blocker</h2>
            <button class="icon-button" type="button" onclick="document.getElementById('threatModal').close()">&times;</button>
        </div>
        <form method="POST" action="{{ route('projects.threats.store', $project) }}" class="modal-body" id="ajaxThreatForm">
            @csrf
            <label>Title <input name="title" required placeholder="Contoh: Dokumen rekening koran belum dikirim"></label>
            <div class="form-grid">
                <label>Related Task
                    <select name="project_task_id" class="task-options-select">
                        <option value="">None (Project Level)</option>
                        @foreach ($project->tasks as $task)
                            <option value="{{ $task->id }}">{{ $task->title }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Risk Category
                    <select name="category">
                        <option value="Client Dependency">Client Dependency</option>
                        <option value="Tax Compliance">Tax Compliance</option>
                        <option value="Resource / PIC">Resource / PIC</option>
                        <option value="Technical / System">Technical / System</option>
                        <option value="Other">Other</option>
                    </select>
                </label>
            </div>
            <div class="form-grid">
                <label>Severity
                    <select name="severity">
                        @foreach (['low', 'medium', 'high', 'critical'] as $severity)
                            <option value="{{ $severity }}">{{ ucfirst($severity) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Status
                    <select name="status">
                        @foreach (['open', 'monitoring', 'resolved'] as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <label>Description <textarea name="description" required placeholder="Deskripsi kendala atau risiko operasional..."></textarea></label>
            <label>Mitigation plan <textarea name="mitigation_plan" placeholder="Rencana tindakan mitigasi..."></textarea></label>
            <div class="modal-actions">
                <button type="button" class="button secondary" onclick="document.getElementById('threatModal').close()">Cancel</button>
                <button class="button danger" type="submit">Save Threat</button>
            </div>
        </form>
    </dialog>

    <!-- Quality Gate & Workpaper Checklist Modal -->
    <dialog id="taskReviewModal" class="rounded-2xl p-0 border border-slate-200 shadow-2xl backdrop:bg-slate-900/50 w-full max-w-2xl overflow-hidden m-auto">
        <div class="bg-[#0B192C] px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center border border-white/20">
                    <x-heroicon-o-shield-check class="w-5 h-5 text-indigo-300" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white tracking-wide flex items-center gap-2">
                        <span>Quality Gate & Verifikasi Output</span>
                        <span id="qgModalTaskKey" class="text-[11px] font-mono px-2 py-0.2 rounded bg-white/15 text-slate-200">TSK</span>
                    </h2>
                    <p class="text-[11px] text-slate-300" id="qgModalSubtitle">Pemeriksaan kertas kerja & kepatuhan sebelum output disetujui.</p>
                </div>
            </div>
            <button class="text-slate-300 hover:text-white text-xl font-bold p-1 cursor-pointer" type="button" onclick="document.getElementById('taskReviewModal').close()">&times;</button>
        </div>

        <div class="p-6 max-h-[80vh] overflow-y-auto space-y-4" id="qgModalContent">
            <!-- Dynamic Content loaded via JS -->
            <div class="py-12 text-center text-slate-400">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-indigo-500 border-t-transparent"></div>
                <p class="mt-2 text-xs font-medium">Memuat data kendali mutu...</p>
            </div>
        </div>
    </dialog>

    <!-- Add Client Document Modal -->
    <dialog id="addDocumentModal" class="rounded-2xl p-0 border border-slate-200 shadow-2xl backdrop:bg-slate-900/50 w-full max-w-xl overflow-hidden m-auto">
        <div class="bg-[#0B192C] px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center border border-white/20">
                    <x-heroicon-o-document-plus class="w-5 h-5 text-blue-300" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white tracking-wide">Tambah Dokumen Masukan Klien</h2>
                    <p class="text-[11px] text-slate-300">Catat berkas wajib yang dibutuhkan dari {{ $project->client->name }}.</p>
                </div>
            </div>
            <button class="text-slate-300 hover:text-white text-xl font-bold p-1 cursor-pointer" type="button" onclick="document.getElementById('addDocumentModal').close()">&times;</button>
        </div>

        <form id="ajaxAddDocForm" method="POST" action="{{ route('projects.documents.store', $project) }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Dokumen <span class="text-rose-600">*</span></label>
                <input type="text" name="title" required placeholder="Contoh: Rekening Koran Mandiri Jan-Mar 2026" class="w-full text-xs py-2 px-3 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-slate-900 font-medium">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kategori Dokumen <span class="text-rose-600">*</span></label>
                    <input type="text" name="category" required list="docCategoryList" placeholder="Pilih atau ketik kategori..." class="w-full text-xs py-2 px-3 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-slate-900 font-medium">
                    <datalist id="docCategoryList">
                        <option value="Bank">
                        <option value="Penjualan">
                        <option value="Pembelian">
                        <option value="Kas & Bank">
                        <option value="Bukti Potong">
                        <option value="Payroll">
                        <option value="Laporan Keuangan">
                        <option value="Legalitas">
                        <option value="Kontrak">
                        <option value="Aset">
                        <option value="Buku Besar">
                    </datalist>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tenggat Waktu (Due Date)</label>
                    <input type="date" name="due_date" value="{{ $project->due_date?->format('Y-m-d') }}" class="w-full text-xs py-2 px-3 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-slate-900 font-medium">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tautan Berkas / Folder Google Drive</label>
                <input type="url" name="file_url" placeholder="https://drive.google.com/..." class="w-full text-xs py-2 px-3 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-slate-900 font-medium">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan Tambahan / Spesifikasi Format</label>
                <textarea name="notes" rows="2" placeholder="Contoh: Format PDF rekening koran lengkap dengan cap / e-statement asli." class="w-full text-xs py-2 px-3 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-slate-900 font-medium"></textarea>
            </div>

            <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_critical" value="1" checked class="rounded border-slate-300 text-slate-900 focus:ring-0">
                    <span class="text-xs font-semibold text-slate-800">Tandai sebagai Berkas Kritis (Bloker Utama)</span>
                </label>
                <div class="flex items-center gap-2">
                    <button type="button" class="button secondary small" onclick="document.getElementById('addDocumentModal').close()">Batal</button>
                    <button type="submit" class="button small">Simpan Dokumen</button>
                </div>
            </div>
        </form>
    </dialog>

    <!-- Edit File URL & Notes Modal -->
    <dialog id="documentLinkModal" class="rounded-2xl p-0 border border-slate-200 shadow-2xl backdrop:bg-slate-900/50 w-full max-w-md overflow-hidden m-auto">
        <div class="bg-[#0B192C] px-6 py-4 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <x-heroicon-o-link class="w-5 h-5 text-blue-300" />
                <h2 class="text-sm font-bold text-white tracking-wide">Tautkan Berkas Dokumen</h2>
            </div>
            <button class="text-slate-300 hover:text-white text-xl font-bold p-1 cursor-pointer" type="button" onclick="document.getElementById('documentLinkModal').close()">&times;</button>
        </div>

        <form id="ajaxDocLinkForm" class="p-6 space-y-4">
            <input type="hidden" id="linkModalDocId" value="">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tautan Berkas / Google Drive</label>
                <input type="url" id="linkModalFileUrl" placeholder="https://drive.google.com/..." class="w-full text-xs py-2 px-3 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-slate-900 font-medium">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan Keterangan</label>
                <textarea id="linkModalNotes" rows="3" placeholder="Catatan berkas, nama pengirim, atau rincian file..." class="w-full text-xs py-2 px-3 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-slate-900 font-medium"></textarea>
            </div>

            <div class="modal-actions !pt-2">
                <button type="button" class="button secondary small" onclick="document.getElementById('documentLinkModal').close()">Batal</button>
                <button type="submit" class="button small">Simpan Tautan</button>
            </div>
        </form>
    </dialog>

    <!-- Escalate Document Blocker to Threat Modal -->
    <dialog id="escalateDocThreatModal" class="rounded-2xl p-0 border border-slate-200 shadow-2xl backdrop:bg-slate-900/50 w-full max-w-md overflow-hidden m-auto">
        <div class="bg-rose-950 px-6 py-4 text-white flex items-center justify-between border-b border-rose-900">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-rose-500/20 text-rose-300 flex items-center justify-center border border-rose-500/30">
                    <x-heroicon-o-shield-exclamation class="w-5 h-5" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white tracking-wide">Eskalasi Kendala Dokumen ke Threat</h2>
                    <p class="text-[10.5px] text-rose-200">Keterlambatan berkas akan dicatat sebagai ancaman operasional proyek.</p>
                </div>
            </div>
            <button class="text-rose-300 hover:text-white text-xl font-bold p-1 cursor-pointer" type="button" onclick="document.getElementById('escalateDocThreatModal').close()">&times;</button>
        </div>

        <form id="ajaxEscalateDocForm" class="p-6 space-y-4">
            <input type="hidden" id="escalateDocId" value="">
            <div class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-xs text-rose-800">
                Dokumen: <strong id="escalateDocTitle" class="font-bold block mt-0.5 text-rose-950">-</strong>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan Tambahan Follow-up</label>
                <textarea id="escalateDocNotes" rows="3" placeholder="Contoh: Sudah dihubungi via WhatsApp 2x belum ada respons. PIC Klien sedang dinas luar." class="w-full text-xs py-2 px-3 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-slate-900 font-medium"></textarea>
            </div>

            <div class="modal-actions !pt-2">
                <button type="button" class="button secondary small" onclick="document.getElementById('escalateDocThreatModal').close()">Batal</button>
                <button type="submit" class="button danger small">Eskalasi ke Project Threat</button>
            </div>
        </form>
    </dialog>

    <script>
        function switchProjectView(view) {
            const board = document.getElementById('jiraBoardView');
            const list = document.getElementById('jiraListView');
            const docs = document.getElementById('jiraDocsView');
            const budget = document.getElementById('jiraBudgetView');
            const boardBtn = document.getElementById('tabBoardBtn');
            const listBtn = document.getElementById('tabListBtn');
            const docsBtn = document.getElementById('tabDocsBtn');
            const budgetBtn = document.getElementById('tabBudgetBtn');

            const activeClass = 'px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#0b192c] text-white flex items-center gap-1.5 transition cursor-pointer';
            const inactiveClass = 'px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition cursor-pointer';

            if (board) board.style.display = (view === 'board') ? 'block' : 'none';
            if (list) list.style.display = (view === 'list') ? 'block' : 'none';
            if (docs) docs.style.display = (view === 'documents') ? 'block' : 'none';
            if (budget) budget.style.display = (view === 'budget') ? 'block' : 'none';

            if (boardBtn) boardBtn.className = (view === 'board') ? activeClass : inactiveClass;
            if (listBtn) listBtn.className = (view === 'list') ? activeClass : inactiveClass;
            if (docsBtn) docsBtn.className = (view === 'documents') ? activeClass : inactiveClass;
            if (budgetBtn) budgetBtn.className = (view === 'budget') ? activeClass : inactiveClass;

            if (view === 'documents' && docs) {
                docs.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            if (view === 'budget' && budget) {
                budget.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function openEditProjectBudgetModal() {
            const modal = document.getElementById('editProjectBudgetModal');
            if (modal) modal.showModal();
        }

        function openTaskBudgetModal(taskId, title, estimate, actual, burnRate, budgetStatus) {
            document.getElementById('budgetModalTaskId').value = taskId;
            document.getElementById('budgetModalTaskTitle').textContent = title;
            document.getElementById('budgetModalActualHours').textContent = `${actual} jam`;
            document.getElementById('budgetModalEstimatedHours').value = (estimate && estimate > 0) ? estimate : '';

            const badge = document.getElementById('budgetModalBurnBadge');
            if (badge) {
                badge.textContent = `${burnRate}%`;
                if (budgetStatus === 'over_budget') {
                    badge.className = 'text-xs font-bold font-mono px-2 py-0.5 rounded border bg-rose-100 text-rose-800 border-rose-200';
                } else if (budgetStatus === 'warning') {
                    badge.className = 'text-xs font-bold font-mono px-2 py-0.5 rounded border bg-amber-100 text-amber-800 border-amber-200';
                } else if (estimate > 0) {
                    badge.className = 'text-xs font-bold font-mono px-2 py-0.5 rounded border bg-emerald-100 text-emerald-800 border-emerald-200';
                } else {
                    badge.className = 'text-xs font-bold font-mono px-2 py-0.5 rounded border bg-slate-100 text-slate-700 border-slate-200';
                }
            }

            const alertBox = document.getElementById('budgetModalStatusAlert');
            if (alertBox) {
                if (budgetStatus === 'over_budget') {
                    alertBox.className = 'p-2.5 rounded-lg border text-xs bg-rose-50 border-rose-200 text-rose-800';
                    alertBox.innerHTML = `<strong>Peringatan Over-Budget:</strong> Realisasi pengerjaan (${actual}j) telah melampaui estimasi (${estimate}j). Perlu evaluasi beban kerja atau penyesuaian alokasi.`;
                } else if (budgetStatus === 'warning') {
                    alertBox.className = 'p-2.5 rounded-lg border text-xs bg-amber-50 border-amber-200 text-amber-800';
                    alertBox.innerHTML = `<strong>Mendekati Batas:</strong> Waktu tercatat (${actual}j) telah mencapai ${burnRate}% dari alokasi (${estimate}j). Pantau agar tidak terjadi pembengkakan.`;
                } else if (estimate > 0) {
                    alertBox.className = 'p-2.5 rounded-lg border text-xs bg-emerald-50 border-emerald-200 text-emerald-800';
                    alertBox.innerHTML = `<strong>Terkendali:</strong> ${actual} jam digunakan dari target ${estimate} jam (${burnRate}%). Sisa alokasi masih mencukupi.`;
                } else {
                    alertBox.className = 'p-2.5 rounded-lg border text-xs bg-slate-50 border-slate-200 text-slate-600';
                    alertBox.innerHTML = `Tugas ini belum memiliki estimasi kuota jam kerja. Masukkan angka target di atas untuk memantau efisiensi staf.`;
                }
            }

            const modal = document.getElementById('taskBudgetModal');
            if (modal) modal.showModal();
        }

        function updateProjectBudgetUI(budget) {
            if (!budget) return;
            const isOver = budget.budget_status === 'over_budget';
            const isWarn = budget.budget_status === 'warning';
            const hasCap = budget.effective_estimated_hours > 0;

            const statLogged = document.getElementById('statLoggedTimeValue');
            if (statLogged) statLogged.textContent = budget.formatted_total_logged_time;

            const statTarget = document.getElementById('statBudgetTargetValue');
            if (statTarget) {
                statTarget.textContent = hasCap ? `/ ${budget.effective_estimated_hours}j` : '(Tanpa Kuota)';
                statTarget.className = hasCap ? 'text-xs text-slate-500 font-medium font-mono' : 'text-xs text-slate-400 font-medium';
            }

            const statProg = document.getElementById('statBurnProgressBar');
            if (statProg) {
                statProg.style.width = `${Math.min(100, budget.burn_rate)}%`;
                statProg.className = `h-1.5 rounded-full transition-all duration-500 ${isOver ? 'bg-rose-600' : (isWarn ? 'bg-amber-500' : 'bg-emerald-600')}`;
            }

            const statDot = document.getElementById('statBurnDot');
            if (statDot) {
                statDot.className = `w-2 h-2 rounded-full ${isOver ? 'bg-rose-600 animate-pulse' : (isWarn ? 'bg-amber-500' : (hasCap ? 'bg-emerald-500' : 'bg-slate-400'))}`;
            }

            const statBurnText = document.getElementById('statBurnRateBadgeText');
            if (statBurnText) {
                statBurnText.textContent = hasCap ? `Burn Rate ${budget.burn_rate}%` : 'Estimasi Kuota';
            }

            const statRemText = document.getElementById('statRemainingHoursText');
            if (statRemText) {
                if (hasCap) {
                    statRemText.textContent = isOver ? `Over +${budget.over_budget_hours}j` : `Sisa ${budget.remaining_hours}j`;
                } else {
                    statRemText.textContent = 'Belum Diset';
                }
            }

            const statBox = document.getElementById('statBurnInfoBox');
            if (statBox) {
                statBox.className = `p-1.5 rounded-lg border mb-2 flex items-center justify-between text-[11px] ${isOver ? 'bg-rose-50 border-rose-200 text-rose-800' : (isWarn ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-slate-50 border-slate-200 text-slate-700')}`;
            }

            const statCard = document.getElementById('statProjectBudgetCard');
            if (statCard) {
                statCard.className = `stat ${isOver ? 'border-rose-300 bg-rose-50/20' : (isWarn ? 'border-amber-300 bg-amber-50/20' : '')}`;
            }

            const statIcon = document.getElementById('statBudgetIconContainer');
            if (statIcon) {
                statIcon.className = `w-7 h-7 rounded-lg flex items-center justify-center border ${isOver ? 'bg-rose-100 text-rose-700 border-rose-200' : (isWarn ? 'bg-amber-100 text-amber-700 border-amber-200' : 'bg-indigo-50 text-indigo-700 border-indigo-100')}`;
            }

            const tabBadge = document.getElementById('tabBudgetBadge');
            if (tabBadge) {
                if (hasCap) {
                    tabBadge.style.display = '';
                    tabBadge.textContent = `${budget.burn_rate}%`;
                    tabBadge.className = `text-[10px] font-bold px-1.5 py-0.2 rounded-full ${isOver ? 'bg-rose-100 text-rose-800 border border-rose-200' : (isWarn ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200')}`;
                } else {
                    tabBadge.style.display = 'none';
                }
            }

            const tabMainBadge = document.getElementById('budgetTabMainStatusBadge');
            if (tabMainBadge) {
                tabMainBadge.className = `label ${isOver ? 'danger' : (isWarn ? 'warning' : 'navy')} text-xs`;
                tabMainBadge.textContent = isOver ? 'Over-Budget' : (isWarn ? 'Mendekati Batas (>=80%)' : (hasCap ? 'On-Track' : 'Tanpa Kuota'));
            }

            const cardProjBudget = document.getElementById('budgetViewProjectBudget');
            if (cardProjBudget) cardProjBudget.textContent = `${budget.project_budget_hours}j`;

            const cardTasksEst = document.getElementById('budgetViewTasksTotalEstimate');
            if (cardTasksEst) cardTasksEst.textContent = `Akumulasi tugas: ${budget.tasks_total_estimated_hours} jam`;

            const cardTotalLog = document.getElementById('budgetViewTotalLogged');
            if (cardTotalLog) cardTotalLog.textContent = budget.formatted_total_logged_time;

            const cardDecHours = document.getElementById('budgetViewDecimalHours');
            if (cardDecHours) cardDecHours.textContent = `(${budget.total_logged_hours}j)`;

            const cardBurn = document.getElementById('budgetViewBurnRate');
            if (cardBurn) {
                cardBurn.textContent = `${budget.burn_rate}%`;
                cardBurn.className = `text-xl font-bold font-mono ${isOver ? 'text-rose-700' : (isWarn ? 'text-amber-900' : 'text-slate-900')}`;
            }

            const cardBurnPill = document.getElementById('budgetViewBurnStatusPill');
            if (cardBurnPill) {
                cardBurnPill.textContent = isOver ? 'Over-Budget' : (isWarn ? 'Waspada' : 'Aman');
                cardBurnPill.className = `text-[10.5px] font-bold px-1.5 py-0.2 rounded ${isOver ? 'bg-rose-200/80 text-rose-900' : (isWarn ? 'bg-amber-200/80 text-amber-900' : 'bg-emerald-100 text-emerald-800')}`;
            }

            const cardRemTitle = document.getElementById('budgetViewRemainingTitle');
            if (cardRemTitle) cardRemTitle.textContent = isOver ? 'Kelebihan Jam (Over)' : 'Sisa Kuota Waktu';

            const cardRemHours = document.getElementById('budgetViewRemainingHours');
            if (cardRemHours) {
                cardRemHours.textContent = isOver ? `+${budget.over_budget_hours}j` : `${budget.remaining_hours}j`;
                cardRemHours.className = `text-xl font-bold font-mono ${isOver ? 'text-rose-700' : 'text-emerald-700'}`;
            }

            const cardRemSub = document.getElementById('budgetViewRemainingSubtext');
            if (cardRemSub) {
                cardRemSub.textContent = isOver ? 'Perlu penyesuaian scope / add-on' : 'Kapasitas pengerjaan masih tersedia';
            }

            const meterBar = document.getElementById('budgetViewProgressBar');
            if (meterBar) {
                meterBar.style.width = `${Math.min(100, budget.burn_rate)}%`;
                meterBar.className = `h-3 rounded-full transition-all duration-500 ${isOver ? 'bg-rose-600' : (isWarn ? 'bg-amber-500' : 'bg-emerald-600')}`;
            }

            const meterDot = document.getElementById('budgetViewMeterDot');
            if (meterDot) {
                meterDot.className = `w-2 h-2 rounded-full ${isOver ? 'bg-rose-600' : (isWarn ? 'bg-amber-500' : 'bg-emerald-500')}`;
            }

            const meterSub = document.getElementById('budgetViewMeterSubtext');
            if (meterSub) {
                meterSub.textContent = `${budget.total_logged_hours} jam dari batas ${budget.effective_estimated_hours} jam`;
            }

            const modalLogged = document.getElementById('projectBudgetModalLoggedTime');
            if (modalLogged) modalLogged.textContent = budget.formatted_total_logged_time;

            const modalTasksEst = document.getElementById('projectBudgetModalTasksEstimate');
            if (modalTasksEst) modalTasksEst.textContent = `${budget.tasks_total_estimated_hours} jam`;
        }

        function updateTaskBudgetUI(task) {
            if (!task) return;
            const isOver = task.budget_status === 'over_budget';
            const isWarn = task.budget_status === 'warning';
            const hasEst = task.estimated_hours > 0;

            const pill = document.getElementById(`task-budget-pill-${task.id}`);
            if (pill) {
                pill.title = `Realisasi ${task.formatted_actual_time} / ${task.estimated_hours} jam (Burn: ${task.burn_rate}%) · Klik untuk ubah alokasi`;
                pill.setAttribute('onclick', `openTaskBudgetModal(${task.id}, '${escapeHtml(task.title)}', ${task.estimated_hours}, ${task.actual_hours}, ${task.burn_rate}, '${task.budget_status}')`);
                pill.className = `inline-flex items-center gap-1 text-[10px] font-medium px-1.5 py-0.5 rounded border transition cursor-pointer ${isOver ? 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' : (isWarn ? 'bg-amber-50 text-amber-800 border-amber-200 hover:bg-amber-100' : (hasEst ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'))}`;

                const pillText = document.getElementById(`task-budget-pill-text-${task.id}`);
                if (pillText) {
                    pillText.textContent = `${task.formatted_actual_time} / ${task.estimated_hours}j`;
                }

                let badgeEl = document.getElementById(`task-budget-pill-badge-${task.id}`);
                if (isOver) {
                    if (!badgeEl) {
                        badgeEl = document.createElement('span');
                        badgeEl.id = `task-budget-pill-badge-${task.id}`;
                        pill.appendChild(badgeEl);
                    }
                    badgeEl.className = 'text-[8px] font-bold px-1 py-0.2 rounded bg-rose-200/80 text-rose-800';
                    badgeEl.textContent = 'Over';
                } else if (isWarn) {
                    if (!badgeEl) {
                        badgeEl = document.createElement('span');
                        badgeEl.id = `task-budget-pill-badge-${task.id}`;
                        pill.appendChild(badgeEl);
                    }
                    badgeEl.className = 'text-[8px] font-bold px-1 py-0.2 rounded bg-amber-200/80 text-amber-800';
                    badgeEl.textContent = `${task.burn_rate}%`;
                } else if (badgeEl) {
                    badgeEl.remove();
                }
            }

            const rowEstimate = document.getElementById(`budget-row-estimate-${task.id}`);
            if (rowEstimate) {
                rowEstimate.textContent = hasEst ? `${task.estimated_hours}j` : '-';
            }

            const rowBurnContainer = document.getElementById(`budget-row-burn-container-${task.id}`);
            if (rowBurnContainer) {
                if (hasEst) {
                    rowBurnContainer.innerHTML = `
                        <div class="flex items-center justify-between text-[10.5px] font-mono mb-1">
                            <span class="font-bold ${isOver ? 'text-rose-700' : (isWarn ? 'text-amber-800' : 'text-slate-700')}" id="budget-row-burn-text-${task.id}">${task.burn_rate}%</span>
                            <span class="text-[9.5px] text-slate-400" id="budget-row-delta-text-${task.id}">${isOver ? '+' + task.over_budget_hours + 'j' : '-' + task.remaining_hours + 'j'}</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                            <div
                                id="budget-row-burn-bar-${task.id}"
                                class="h-1.5 rounded-full ${isOver ? 'bg-rose-600' : (isWarn ? 'bg-amber-500' : 'bg-emerald-600')}"
                                style="width: ${Math.min(100, task.burn_rate)}%"
                            ></div>
                        </div>
                    `;
                } else {
                    rowBurnContainer.innerHTML = '<span class="text-[11px] text-slate-400">-</span>';
                }
            }

            const rowStatusCell = document.getElementById(`budget-row-status-cell-${task.id}`);
            if (rowStatusCell) {
                if (isOver) {
                    rowStatusCell.innerHTML = `
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            <svg class="w-3 h-3 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            <span>Over-Budget</span>
                        </span>
                    `;
                } else if (isWarn) {
                    rowStatusCell.innerHTML = `
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <span>Mendekati Kuota (>=80%)</span>
                        </span>
                    `;
                } else if (hasEst) {
                    rowStatusCell.innerHTML = `
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>Aman / On-Track</span>
                        </span>
                    `;
                } else {
                    rowStatusCell.innerHTML = `
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                            Tanpa Estimasi
                        </span>
                    `;
                }
            }
        }


        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function openAddDocumentModal() {
            const modal = document.getElementById('addDocumentModal');
            if (modal) modal.showModal();
        }

        function openDocLinkModal(docId, currentUrl, currentNotes) {
            document.getElementById('linkModalDocId').value = docId;
            document.getElementById('linkModalFileUrl').value = currentUrl || '';
            document.getElementById('linkModalNotes').value = currentNotes || '';
            document.getElementById('documentLinkModal').showModal();
        }

        function escalateDocThreat(docId, docTitle) {
            document.getElementById('escalateDocId').value = docId;
            document.getElementById('escalateDocTitle').textContent = docTitle;
            document.getElementById('escalateDocNotes').value = '';
            document.getElementById('escalateDocThreatModal').showModal();
        }

        function filterDocuments(filter) {
            const rows = document.querySelectorAll('.client-doc-row');
            const buttons = document.querySelectorAll('.doc-filter-btn');

            buttons.forEach(btn => {
                if (btn.dataset.filter === filter) {
                    btn.className = 'doc-filter-btn px-2.5 py-1 rounded-lg font-semibold bg-[#0b192c] text-white';
                } else {
                    btn.className = 'doc-filter-btn px-2.5 py-1 rounded-lg font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50';
                }
            });

            rows.forEach(row => {
                if (filter === 'all') {
                    row.style.display = '';
                } else if (filter === 'critical') {
                    row.style.display = (row.dataset.critical === 'true') ? '' : 'none';
                } else {
                    row.style.display = (row.dataset.status === filter) ? '' : 'none';
                }
            });
        }

        function updateDocSummaryUI(summary) {
            if (!summary) return;
            const progressText = document.getElementById('docVaultProgressText');
            const countText = document.getElementById('docVaultCountText');
            const progressBar = document.getElementById('docVaultProgressBar');
            const criticalAlert = document.getElementById('docVaultCriticalAlert');
            const tabBadge = document.getElementById('tabDocsBadge');

            const asidePercent = document.getElementById('asideDocPercent');
            const asideProgressBar = document.getElementById('asideDocProgressBar');
            const asideReceivedText = document.getElementById('asideDocReceivedText');
            const asideVerifiedText = document.getElementById('asideDocVerifiedText');
            const asideAlertText = document.getElementById('asideDocAlertText');
            const asideAlertBox = document.getElementById('asideDocAlertBox');

            if (progressText) progressText.textContent = `${summary.percentage}%`;
            if (countText) countText.textContent = `(${summary.received} dari ${summary.total} berkas diterima)`;
            if (progressBar) progressBar.style.width = `${summary.percentage}%`;

            if (tabBadge) {
                tabBadge.textContent = `${summary.verified}/${summary.total}`;
                if (summary.critical_pending > 0) {
                    tabBadge.className = 'text-[10px] font-bold px-1.5 py-0.2 rounded-full bg-rose-100 text-rose-800 border border-rose-200';
                } else {
                    tabBadge.className = 'text-[10px] font-bold px-1.5 py-0.2 rounded-full bg-slate-100 text-slate-700';
                }
            }

            if (criticalAlert) {
                if (summary.critical_pending > 0) {
                    criticalAlert.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-bold bg-rose-100 text-rose-800 border border-rose-200';
                    criticalAlert.innerHTML = `
                        <svg class="w-3.5 h-3.5 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
                        <span>${summary.critical_pending} Berkas Kritis Belum Diterima</span>
                    `;
                } else {
                    criticalAlert.className = 'inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10.5px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200';
                    criticalAlert.innerHTML = `
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Semua Berkas Kritis Lengkap</span>
                    `;
                }
            }

            if (asidePercent) asidePercent.textContent = `${summary.percentage}%`;
            if (asideProgressBar) asideProgressBar.style.width = `${summary.percentage}%`;
            if (asideReceivedText) asideReceivedText.textContent = `${summary.received} dari ${summary.total} diterima`;
            if (asideVerifiedText) asideVerifiedText.textContent = `${summary.verified} terverifikasi`;
            if (asideAlertBox) {
                if (summary.critical_pending > 0) {
                    asideAlertBox.style.display = 'flex';
                    if (asideAlertText) asideAlertText.textContent = `${summary.critical_pending} berkas kritis belum diserahkan klien.`;
                } else {
                    asideAlertBox.style.display = 'none';
                }
            }
        }

        function updateDocStatus(docId, newStatus) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const selectEl = document.querySelector(`.doc-status-select-${docId}`);
            if (selectEl) selectEl.disabled = true;

            fetch(`/projects/{{ $project->id }}/documents/${docId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(async res => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    throw new Error(data.message || 'Gagal memperbarui status dokumen.');
                }
                return data;
            })
            .then(data => {
                window.toast?.success(data.message);
                if (selectEl) {
                    selectEl.className = `text-[11px] font-semibold py-1 px-2 rounded-lg border cursor-pointer doc-status-select-${docId} ` +
                        (newStatus === 'verified' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' :
                        (newStatus === 'received' ? 'bg-blue-50 text-blue-800 border-blue-200' :
                        (newStatus === 'partial' ? 'bg-amber-50 text-amber-800 border-amber-200' :
                        'bg-slate-100 text-slate-700 border-slate-200')));
                }

                const row = document.getElementById(`client-doc-row-${docId}`);
                if (row) {
                    row.dataset.status = newStatus;
                }

                if (data.summary) {
                    updateDocSummaryUI(data.summary);
                }

                // If marked received or verified, threat may have been resolved
                if (data.document && (newStatus === 'received' || newStatus === 'verified')) {
                    setTimeout(() => window.location.reload(), 800);
                }
            })
            .catch(err => {
                window.toast?.error(err.message);
            })
            .finally(() => {
                if (selectEl) selectEl.disabled = false;
            });
        }

        function populateDefaultDocs(type) {
            if (!confirm('Muat checklist standar dokumen untuk penugasan ini?')) return;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(`/projects/{{ $project->id }}/documents/populate-defaults`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ type: type })
            })
            .then(async res => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    throw new Error(data.message || 'Gagal memuat dokumen standar.');
                }
                return data;
            })
            .then(data => {
                window.toast?.success(data.message);
                setTimeout(() => window.location.reload(), 600);
            })
            .catch(err => {
                window.toast?.error(err.message);
            });
        }

        function deleteClientDoc(docId) {
            if (!confirm('Apakah Anda yakin ingin menghapus dokumen ini dari checklist?')) return;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(`/projects/{{ $project->id }}/documents/${docId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(async res => {
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    throw new Error(data.message || 'Gagal menghapus dokumen.');
                }
                return data;
            })
            .then(data => {
                window.toast?.success(data.message);
                const row = document.getElementById(`client-doc-row-${docId}`);
                if (row) row.remove();
                if (data.summary) {
                    updateDocSummaryUI(data.summary);
                }
            })
            .catch(err => {
                window.toast?.error(err.message);
            });
        }

        function updateKanbanColumnStates() {
            ['not_started', 'in_progress', 'waiting_client', 'in_review', 'completed'].forEach(status => {
                const colContainer = document.getElementById(`kanban-col-${status}`);
                const countBadge = document.getElementById(`col-count-${status}`);
                const emptyPlaceholder = document.getElementById(`empty-col-${status}`);
                if (colContainer) {
                    const cards = colContainer.querySelectorAll('.task-card-item');
                    if (countBadge) countBadge.textContent = cards.length;
                    if (emptyPlaceholder) {
                        emptyPlaceholder.style.display = cards.length === 0 ? 'block' : 'none';
                    }
                }
            });
        }

        window.openQualityGateModal = function(taskId) {
            const modal = document.getElementById('taskReviewModal');
            const content = document.getElementById('qgModalContent');
            const keyEl = document.getElementById('qgModalTaskKey');
            if (keyEl) keyEl.textContent = `TSK-${taskId}`;

            content.innerHTML = `
                <div class="py-12 text-center text-slate-400">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-indigo-500 border-t-transparent"></div>
                    <p class="mt-2 text-xs font-medium">Memuat data kendali mutu...</p>
                </div>
            `;
            modal.showModal();

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(`/projects/{{ $project->id }}/tasks/${taskId}/review-data`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async res => {
                if (!res.ok) throw new Error('Gagal memuat data review task.');
                return res.json();
            })
            .then(data => {
                renderQualityGateModal(data, taskId);
            })
            .catch(err => {
                content.innerHTML = `
                    <div class="p-6 text-center text-rose-600 bg-rose-50 rounded-xl border border-rose-200">
                        <p class="text-xs font-semibold">${escapeHtml(err.message)}</p>
                    </div>
                `;
            });
        };

        function renderQualityGateModal(data, taskId) {
            const content = document.getElementById('qgModalContent');
            const t = data.task;
            const u = data.user;

            let checklistsHtml = '';
            if (t.checklists && t.checklists.length > 0) {
                checklistsHtml = t.checklists.map(c => `
                    <label class="flex items-start gap-2.5 p-2.5 rounded-lg border ${c.is_checked ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-white hover:bg-slate-50'} transition cursor-pointer">
                        <input
                            type="checkbox"
                            ${c.is_checked ? 'checked' : ''}
                            onchange="toggleQcItem(${taskId}, ${c.id}, this)"
                            class="mt-0.5 rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4"
                        >
                        <div class="flex-1 text-xs">
                            <div class="${c.is_checked ? 'text-slate-600 line-through' : 'text-slate-900 font-medium'} leading-snug">
                                ${escapeHtml(c.title)}
                            </div>
                            ${c.checked_by_name ? `
                                <div class="text-[10.5px] text-slate-400 mt-0.5">
                                    Diverifikasi oleh ${escapeHtml(c.checked_by_name)} · ${escapeHtml(c.checked_at || '')}
                                </div>
                            ` : ''}
                        </div>
                    </label>
                `).join('');
            } else {
                checklistsHtml = '<div class="text-xs text-slate-400 p-3 bg-slate-50 rounded-lg text-center">Belum ada item checklist.</div>';
            }

            let historyHtml = '';
            if (t.reviews_history && t.reviews_history.length > 0) {
                historyHtml = t.reviews_history.map(r => `
                    <div class="p-2.5 rounded-lg border border-slate-200 bg-white text-xs">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-semibold text-slate-900">${escapeHtml(r.reviewer_name)}</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold ${r.action === 'approved' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'}">
                                ${escapeHtml(r.action_label)}
                            </span>
                        </div>
                        ${r.notes ? `<div class="text-slate-600 text-[11px] mt-1 bg-slate-50 p-2 rounded">${escapeHtml(r.notes)}</div>` : ''}
                        <div class="text-[10px] text-slate-400 mt-1 text-right">${escapeHtml(r.created_at)}</div>
                    </div>
                `).join('');
            }

            content.innerHTML = `
                <!-- Task Overview -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs text-slate-500 font-mono">TSK-${t.id} · ${escapeHtml(t.assignee_name)}</div>
                        <div class="text-sm font-bold text-slate-900 mt-0.5">${escapeHtml(t.title)}</div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold ${
                            t.status === 'completed' ? 'bg-emerald-100 text-emerald-800' :
                            t.status === 'in_review' ? 'bg-indigo-100 text-indigo-800' :
                            t.review_status === 'revision_requested' ? 'bg-rose-100 text-rose-800' :
                            'bg-blue-100 text-blue-800'
                        }">
                            ${escapeHtml(t.status.replace('_', ' ').toUpperCase())}
                        </span>
                        <div class="text-[11px] text-slate-500 mt-0.5">Tenggat: ${escapeHtml(t.due_date)}</div>
                    </div>
                </div>

                ${t.review_notes && t.review_status === 'revision_requested' ? `
                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-900 leading-relaxed">
                        <div class="font-bold text-rose-800 mb-1 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                            <span>Catatan Perbaikan Terakhir dari Reviewer:</span>
                        </div>
                        <div class="pl-3.5 font-medium">${escapeHtml(t.review_notes)}</div>
                    </div>
                ` : ''}

                <!-- Quality Checklist Section -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-slate-900 uppercase tracking-wider">Kertas Kerja & Checklist Mutu</span>
                            <span id="qgProgressCounter" class="px-2 py-0.5 rounded text-[10.5px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200 font-mono">
                                ${t.checklists_summary.completed} / ${t.checklists_summary.total} Selesai
                            </span>
                        </div>
                    </div>

                    <div class="space-y-1.5" id="qgChecklistsList">
                        ${checklistsHtml}
                    </div>

                    <!-- Add Checklist Item -->
                    <div class="mt-2.5 flex items-center gap-2">
                        <input
                            type="text"
                            id="qgNewItemInput"
                            placeholder="Tambah item validasi kertas kerja..."
                            class="flex-1 text-xs px-3 py-1.5 rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        >
                        <button
                            type="button"
                            onclick="addNewQcItem(${taskId})"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 transition cursor-pointer shrink-0"
                        >
                            + Tambah
                        </button>
                    </div>
                </div>

                <!-- Review History -->
                ${historyHtml ? `
                    <div class="pt-2 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-2">Riwayat Review & Keputusan</span>
                        <div class="space-y-2">
                            ${historyHtml}
                        </div>
                    </div>
                ` : ''}

                <!-- Action Gate Form -->
                <div class="pt-3 border-t border-slate-200">
                    ${u.can_review ? `
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-800">Catatan Reviewer / Rekomendasi:</label>
                            <textarea
                                id="qgReviewNotes"
                                rows="2"
                                placeholder="Masukkan catatan hasil pemeriksaan (wajib diisi bila meminta revisi)..."
                                class="w-full text-xs p-2.5 rounded-lg border border-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            ></textarea>
                            <div class="flex items-center justify-between gap-2 pt-1">
                                <button
                                    type="button"
                                    onclick="executeReviewAction(${taskId}, 'revision_requested')"
                                    class="px-3.5 py-2 rounded-lg text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-200 transition cursor-pointer flex items-center gap-1.5"
                                >
                                    <span>Minta Revisi Staf</span>
                                </button>
                                <button
                                    type="button"
                                    onclick="executeReviewAction(${taskId}, 'approved')"
                                    class="px-4 py-2 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition cursor-pointer flex items-center gap-1.5"
                                >
                                    <span>Setujui (Approve & Selesai)</span>
                                </button>
                            </div>
                        </div>
                    ` : (t.status !== 'in_review' && t.status !== 'completed' ? `
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs text-slate-500">Tugas siap diverifikasi oleh Reviewer?</span>
                            <button
                                type="button"
                                onclick="submitTaskForReview(${taskId})"
                                class="px-4 py-2 rounded-lg text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm transition cursor-pointer"
                            >
                                Ajukan ke Reviewer
                            </button>
                        </div>
                    ` : `
                        <div class="p-2.5 rounded-lg bg-indigo-50 text-indigo-800 border border-indigo-200 text-xs font-medium text-center">
                            Tugas saat ini sedang dalam antrean verifikasi oleh akun Reviewer / Admin.
                        </div>
                    `)}
                </div>
            `;
        }

        window.toggleQcItem = function(taskId, checklistId, checkboxEl) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            checkboxEl.disabled = true;

            fetch(`/projects/{{ $project->id }}/tasks/${taskId}/checklists/${checklistId}`, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async res => {
                if (!res.ok) throw new Error('Gagal memperbarui status checklist.');
                return res.json();
            })
            .then(data => {
                const counter = document.getElementById('qgProgressCounter');
                if (counter) counter.textContent = `${data.completed_count} / ${data.total_count} Selesai`;

                // Update on Kanban card
                const card = document.getElementById(`task-card-${taskId}`);
                if (card) {
                    const pill = card.querySelector('button[title*="Quality Gate"] span');
                    if (pill) pill.textContent = `${data.completed_count}/${data.total_count} QC`;
                }
            })
            .catch(err => {
                checkboxEl.checked = !checkboxEl.checked;
                window.toast?.error(err.message);
            })
            .finally(() => {
                checkboxEl.disabled = false;
            });
        };

        window.addNewQcItem = function(taskId) {
            const input = document.getElementById('qgNewItemInput');
            const title = input?.value?.trim();
            if (!title) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            input.disabled = true;

            fetch(`/projects/{{ $project->id }}/tasks/${taskId}/checklists`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ title: title })
            })
            .then(async res => {
                if (!res.ok) throw new Error('Gagal menambahkan item checklist.');
                return res.json();
            })
            .then(data => {
                input.value = '';
                openQualityGateModal(taskId); // refresh modal
                window.toast?.success(data.message);
            })
            .catch(err => {
                window.toast?.error(err.message);
            })
            .finally(() => {
                if (input) input.disabled = false;
            });
        };

        window.executeReviewAction = function(taskId, action) {
            const notesEl = document.getElementById('qgReviewNotes');
            const notes = notesEl?.value?.trim();

            if (action === 'revision_requested' && !notes) {
                window.toast?.error('Catatan revisi wajib diisi agar staf mengetahui aspek yang perlu diperbaiki.');
                notesEl?.focus();
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(`/projects/{{ $project->id }}/tasks/${taskId}/review`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ action: action, notes: notes })
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal memproses review.');
                return data;
            })
            .then(data => {
                document.getElementById('taskReviewModal')?.close();
                window.toast?.success(data.message);

                // Reload or move card in Kanban
                setTimeout(() => window.location.reload(), 400);
            })
            .catch(err => {
                window.toast?.error(err.message);
            });
        };

        window.submitTaskForReview = function(taskId) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch(`/projects/{{ $project->id }}/tasks/${taskId}/submit-review`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal mengajukan review.');
                return data;
            })
            .then(data => {
                document.getElementById('taskReviewModal')?.close();
                window.toast?.success(data.message);
                setTimeout(() => window.location.reload(), 400);
            })
            .catch(err => {
                window.toast?.error(err.message);
            });
        };

        function changeTaskStatus(selectEl, url, taskId) {
            const newStatus = selectEl.value;
            const card = document.getElementById(`task-card-${taskId}`);
            if (!card) return;

            const oldStatus = card.dataset.status;
            if (oldStatus === newStatus) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            selectEl.disabled = true;

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(async res => {
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    throw new Error(err.message || 'Gagal mengubah status tugas');
                }
                return res.json();
            })
            .then(data => {
                card.dataset.status = newStatus;
                const targetContainer = document.getElementById(`kanban-col-${newStatus}`);
                if (targetContainer) {
                    targetContainer.appendChild(card);
                }

                const progressBadge = card.querySelector('.task-progress-badge');
                const progressBar = card.querySelector('.task-progress-bar-fill');
                if (progressBadge) progressBadge.textContent = `${data.progress_percent}%`;
                if (progressBar) progressBar.style.width = `${data.progress_percent}%`;

                const tableRow = document.getElementById(`table-task-row-${taskId}`);
                if (tableRow) {
                    const statusCell = tableRow.querySelector('.task-table-status-label');
                    const progCell = tableRow.querySelector('.task-table-progress');
                    if (statusCell) statusCell.textContent = newStatus.replace('_', ' ');
                    if (progCell) progCell.textContent = `${data.progress_percent}%`;
                }

                if (data.project_progress !== undefined) {
                    const statProg = document.getElementById('statProjectProgress');
                    const statProgBar = document.getElementById('statProjectProgressBar');
                    if (statProg) statProg.textContent = `${data.project_progress}%`;
                    if (statProgBar) statProgBar.style.width = `${data.project_progress}%`;
                }
                if (data.completed_tasks !== undefined && data.in_progress_tasks !== undefined) {
                    const statSub = document.getElementById('statTasksSubtext');
                    if (statSub) statSub.textContent = `${data.completed_tasks} done · ${data.in_progress_tasks} in progress`;
                }

                if (data.project_budget) {
                    updateProjectBudgetUI(data.project_budget);
                }
                const budgetRowStatus = document.getElementById(`budget-row-task-status-${taskId}`);
                if (budgetRowStatus) {
                    const statusMap = {
                        'completed': '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">Selesai</span>',
                        'in_review': '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200">In Review</span>',
                        'in_progress': '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200">In Progress</span>',
                        'waiting_client': '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">Waiting</span>',
                        'not_started': '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">To Do</span>'
                    };
                    budgetRowStatus.innerHTML = statusMap[newStatus] || `<span class="label text-[10px]">${newStatus}</span>`;
                }

                updateKanbanColumnStates();
                window.toast?.success(data.message || 'Status task berhasil diperbarui.');

                if (newStatus === 'in_progress' && window.KonsulinTimer) {
                    const taskTitle = card.querySelector('.task-title-text')?.textContent?.trim() || 'Task';
                    window.KonsulinTimer.promptStartOnInProgress(taskId, taskTitle, '{{ addslashes($project->name) }}');
                }
            })
            .catch(err => {
                console.error(err);
                selectEl.value = oldStatus;
                window.toast?.error(err.message || 'Terjadi kesalahan saat memperbarui status.');
            })
            .finally(() => {
                selectEl.disabled = false;
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            // 1. AJAX Task Form
            const taskForm = document.getElementById('ajaxTaskForm');
            if (taskForm) {
                taskForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const submitBtn = taskForm.querySelector('button[type="submit"]');
                    const originalBtnText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span>Menyimpan...</span>';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const formData = new FormData(taskForm);

                    fetch(taskForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: formData
                    })
                    .then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            const firstError = data.errors ? Object.values(data.errors)[0][0] : data.message;
                            throw new Error(firstError || 'Gagal membuat task baru.');
                        }
                        return data;
                    })
                    .then(data => {
                        document.getElementById('taskFormModal').close();
                        taskForm.reset();
                        window.toast?.success(data.message || 'Task baru berhasil ditambahkan.');

                        const task = data.task;
                        if (task) {
                            const isStaffUser = {{ auth()->check() && auth()->user()->isStaff() ? 'true' : 'false' }};
                            const currentAuthId = {{ auth()->id() ?? 'null' }};
                            const canEditDynamic = !isStaffUser || (task.assigned_to && Number(task.assigned_to) === Number(currentAuthId));

                            const colContainer = document.getElementById(`kanban-col-${task.status}`);
                            if (colContainer) {
                                const newCard = document.createElement('div');
                                newCard.className = 'bg-white border border-slate-200 rounded-lg p-3 shadow-xs hover:shadow-sm hover:border-slate-300 transition-all group task-card-item';
                                newCard.id = `task-card-${task.id}`;
                                newCard.dataset.taskId = task.id;
                                newCard.dataset.status = task.status;
                                newCard.innerHTML = `
                                    <div class="flex items-center justify-between gap-1 mb-1.5">
                                        <span class="text-[11px] font-mono font-semibold text-slate-500">TSK-${task.id}</span>
                                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-slate-100 text-slate-700 uppercase task-progress-badge">${task.progress_percent}%</span>
                                    </div>
                                    <div class="text-xs font-semibold text-slate-900 leading-snug mb-2 task-title-text">${escapeHtml(task.title)}</div>
                                    ${task.notes ? `<p class="text-[11px] text-slate-500 line-clamp-2 mb-2 task-notes-text">${escapeHtml(task.notes)}</p>` : ''}
                                    <div class="mb-1.5 flex items-center justify-between gap-1 flex-wrap">
                                        <button
                                            type="button"
                                            onclick="openQualityGateModal(${task.id})"
                                            class="inline-flex items-center gap-1 text-[10px] font-medium text-slate-700 hover:text-indigo-700 bg-slate-50 hover:bg-indigo-50/60 px-1.5 py-0.5 rounded border border-slate-200 transition cursor-pointer"
                                            title="Buka Quality Gate & Checklist Kertas Kerja"
                                        >
                                            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            <span>0/0 QC</span>
                                        </button>
                                        <button
                                            type="button"
                                            id="task-budget-pill-${task.id}"
                                            onclick="openTaskBudgetModal(${task.id}, '${escapeHtml(task.title)}', ${task.estimated_hours || 0}, 0, 0, 'on_track')"
                                            class="inline-flex items-center gap-1 text-[10px] font-medium px-1.5 py-0.5 rounded border transition cursor-pointer ${task.estimated_hours > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'}"
                                            title="Realisasi 0m / ${task.estimated_hours || 0} jam · Klik untuk ubah alokasi"
                                        >
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            <span class="font-mono" id="task-budget-pill-text-${task.id}">0m / ${task.estimated_hours || 0}j</span>
                                        </button>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5 mb-2 overflow-hidden">
                                        <div class="bg-[#0b192c] h-1.5 rounded-full task-progress-bar-fill" style="width: ${task.progress_percent}%"></div>
                                    </div>
                                    <div class="flex items-center justify-between pt-1 border-t border-slate-100 text-[11px] text-slate-500">
                                        <div class="flex items-center gap-1">
                                            <div class="w-5 h-5 rounded-full bg-[#1e3e62] text-white text-[9px] font-bold flex items-center justify-center shrink-0">
                                                ${escapeHtml(task.assignee_initial)}
                                            </div>
                                            <span class="truncate max-w-[80px]" title="${escapeHtml(task.assignee_name)}">${escapeHtml(task.assignee_name)}</span>
                                        </div>
                                        ${task.due_date ? `<span>${escapeHtml(task.due_date)}</span>` : ''}
                                    </div>
                                    <div class="mt-2 pt-1.5 border-t border-dashed border-slate-100 flex items-center justify-between gap-1">
                                        ${(isStaffUser && canEditDynamic) ? `
                                            <button
                                                type="button"
                                                data-task-timer-btn="${task.id}"
                                                onclick="window.KonsulinTimer.start(${task.id})"
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition cursor-pointer shrink-0"
                                                title="Mulai Waktu Kerja"
                                            >
                                                <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path></svg>
                                                <span>Mulai</span>
                                            </button>
                                        ` : ''}
                                        <form method="POST" action="/projects/{{ $project->id }}/tasks/${task.id}/status" class="inline-flex gap-1 m-0">
                                            <select
                                                name="status"
                                                ${!canEditDynamic ? 'disabled title="Hanya staff yang ditugaskan yang dapat memperbarui tugas ini" class="text-[10px] py-0.5 px-1.5 h-6 bg-slate-100 border border-slate-200 rounded text-slate-400 cursor-not-allowed font-medium task-status-select"' : `onchange="changeTaskStatus(this, '/projects/{{ $project->id }}/tasks/${task.id}/status', ${task.id})" class="text-[10px] py-0.5 px-1.5 h-6 bg-slate-50 border border-slate-200 rounded text-slate-700 cursor-pointer font-medium task-status-select"`}
                                            >
                                                <option value="not_started" ${task.status === 'not_started' ? 'selected' : ''}>To Do</option>
                                                <option value="in_progress" ${task.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                                                <option value="waiting_client" ${task.status === 'waiting_client' ? 'selected' : ''}>Waiting</option>
                                                <option value="completed" ${task.status === 'completed' ? 'selected' : ''}>Done</option>
                                            </select>
                                        </form>
                                    </div>
                                `;
                                colContainer.appendChild(newCard);
                            }

                            const tableBody = document.getElementById('tasksTableBody');
                            const emptyRow = document.getElementById('emptyTasksTableRow');
                            if (emptyRow) emptyRow.style.display = 'none';
                            if (tableBody) {
                                const tr = document.createElement('tr');
                                tr.id = `table-task-row-${task.id}`;
                                tr.innerHTML = `
                                    <td>
                                        <div class="font-semibold text-slate-900">${escapeHtml(task.title)}</div>
                                        ${task.notes ? `<div class="text-xs text-slate-500">${escapeHtml(task.notes)}</div>` : ''}
                                    </td>
                                    <td>${escapeHtml(task.assignee_name)}</td>
                                    <td><span class="label task-table-status-label">${escapeHtml(task.status.replace('_', ' '))}</span></td>
                                    <td><div class="font-bold task-table-progress">${task.progress_percent}%</div></td>
                                    <td>${escapeHtml(task.due_date_full)}</td>
                                    <td>
                                        ${(isStaffUser && canEditDynamic) ? `
                                            <button
                                                type="button"
                                                data-task-timer-btn="${task.id}"
                                                onclick="window.KonsulinTimer.start(${task.id})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-[11px] font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition cursor-pointer"
                                                title="Mulai Waktu Kerja"
                                            >
                                                <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path></svg>
                                                <span>Mulai</span>
                                            </button>
                                        ` : '<span class="text-xs text-slate-400">-</span>'}
                                    </td>
                                `;
                                tableBody.appendChild(tr);
                            }

                            const budgetTableBody = document.getElementById('taskBudgetTableBody');
                            if (budgetTableBody) {
                                const bRow = document.createElement('tr');
                                bRow.className = 'hover:bg-slate-50/60 transition-colors';
                                bRow.id = `budget-table-row-${task.id}`;
                                const estHours = (task.estimated_hours && task.estimated_hours > 0) ? task.estimated_hours : 0;
                                bRow.innerHTML = `
                                    <td class="py-3 px-3">
                                        <div class="font-bold text-slate-900 leading-snug">${escapeHtml(task.title)}</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1.5">
                                            <span class="font-mono text-[10.5px] text-slate-400">TSK-${task.id}</span>
                                            <span>·</span>
                                            <span>${escapeHtml(task.assignee_name || 'Unassigned')}</span>
                                            ${task.due_date ? `<span>·</span><span>Due ${escapeHtml(task.due_date)}</span>` : ''}
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 whitespace-nowrap" id="budget-row-task-status-${task.id}">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">To Do</span>
                                    </td>
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5 font-mono">
                                            <strong class="text-slate-900 font-bold" id="budget-row-estimate-${task.id}">
                                                ${estHours > 0 ? estHours + 'j' : '-'}
                                            </strong>
                                            <button
                                                type="button"
                                                onclick="openTaskBudgetModal(${task.id}, '${escapeHtml(task.title)}', ${estHours}, 0, 0, 'on_track')"
                                                class="text-slate-400 hover:text-indigo-600 p-0.5 rounded hover:bg-indigo-50 transition cursor-pointer"
                                                title="Ubah target estimasi jam kerja"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 whitespace-nowrap font-mono">
                                        <strong class="text-slate-900 font-bold" id="budget-row-actual-${task.id}">0m</strong>
                                        <span class="text-[11px] text-slate-400">(0j)</span>
                                    </td>
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        <span class="text-[11px] text-slate-400" id="budget-row-burn-container-${task.id}">${estHours > 0 ? '0%' : '-'}</span>
                                    </td>
                                    <td class="py-3 px-3 whitespace-nowrap" id="budget-row-status-cell-${task.id}">
                                        ${estHours > 0 ? `
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span>Aman / On-Track</span>
                                            </span>
                                        ` : `
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                                Tanpa Estimasi
                                            </span>
                                        `}
                                    </td>
                                    <td class="py-3 px-3 text-right whitespace-nowrap">
                                        <button
                                            type="button"
                                            onclick="openTaskBudgetModal(${task.id}, '${escapeHtml(task.title)}', ${estHours}, 0, 0, 'on_track')"
                                            class="button secondary small text-xs py-1 px-2"
                                        >
                                            Atur Estimasi
                                        </button>
                                    </td>
                                `;
                                budgetTableBody.appendChild(bRow);
                            }

                            if (data.project_budget) {
                                updateProjectBudgetUI(data.project_budget);
                            }

                            document.querySelectorAll('.task-options-select').forEach(sel => {
                                const opt = document.createElement('option');
                                opt.value = task.id;
                                opt.textContent = task.title;
                                sel.appendChild(opt);
                            });

                            const statTasksCount = document.getElementById('statTasksCount');
                            if (statTasksCount) statTasksCount.textContent = data.total_tasks;
                            const statSub = document.getElementById('statTasksSubtext');
                            if (statSub) statSub.textContent = `${data.completed_tasks} done · ${data.in_progress_tasks} in progress`;
                            if (data.project_progress !== undefined) {
                                const statProg = document.getElementById('statProjectProgress');
                                const statProgBar = document.getElementById('statProjectProgressBar');
                                if (statProg) statProg.textContent = `${data.project_progress}%`;
                                if (statProgBar) statProgBar.style.width = `${data.project_progress}%`;
                            }
                            updateKanbanColumnStates();
                        }
                    })
                    .catch(err => {
                        window.toast?.error(err.message);
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    });
                });
            }

            // 2. AJAX Progress Form
            const progressForm = document.getElementById('ajaxProgressForm');
            if (progressForm) {
                progressForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const submitBtn = progressForm.querySelector('button[type="submit"]');
                    const originalBtnText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span>Mengunggah...</span>';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const formData = new FormData(progressForm);

                    fetch(progressForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: formData
                    })
                    .then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            const firstError = data.errors ? Object.values(data.errors)[0][0] : data.message;
                            throw new Error(firstError || 'Gagal mengunggah progress.');
                        }
                        return data;
                    })
                    .then(data => {
                        document.getElementById('progressModal').close();
                        progressForm.reset();
                        window.toast?.success(data.message || 'Progress berhasil diunggah.');

                        const prog = data.progress;
                        if (prog) {
                            const listContainer = document.getElementById('progressUpdatesList');
                            const emptyNotice = document.getElementById('emptyProgressNotice');
                            if (emptyNotice) emptyNotice.style.display = 'none';

                            if (listContainer) {
                                const article = document.createElement('article');
                                article.className = 'card';
                                article.style.marginBottom = '12px';
                                article.innerHTML = `
                                    <div class="card-head">
                                        <div>
                                            <h3 class="flex items-center gap-2">
                                                <span class="font-bold text-slate-900">${escapeHtml(prog.user_name)}</span>
                                                <span class="text-xs font-normal text-slate-500">uploaded</span>
                                                <span class="label success text-xs">${prog.progress_percent}%</span>
                                            </h3>
                                            <p class="muted text-xs">${escapeHtml(prog.task_title)} · ${escapeHtml(prog.created_at)}</p>
                                        </div>
                                        <span class="label text-xs font-mono">#${prog.id}</span>
                                    </div>
                                    <p class="text-xs text-slate-700 leading-relaxed">${escapeHtml(prog.summary)}</p>
                                    ${prog.attachment_path ? `
                                        <div class="mt-2 text-xs text-blue-600 font-medium flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                            <span>Attachment: ${escapeHtml(prog.attachment_path)}</span>
                                        </div>
                                    ` : ''}
                                `;
                                listContainer.prepend(article);
                            }

                            if (prog.task_id) {
                                const taskCard = document.getElementById(`task-card-${prog.task_id}`);
                                if (taskCard) {
                                    const badge = taskCard.querySelector('.task-progress-badge');
                                    const bar = taskCard.querySelector('.task-progress-bar-fill');
                                    if (badge) badge.textContent = `${prog.progress_percent}%`;
                                    if (bar) bar.style.width = `${prog.progress_percent}%`;
                                }
                            }

                            if (data.project_progress !== undefined) {
                                const statProg = document.getElementById('statProjectProgress');
                                const statProgBar = document.getElementById('statProjectProgressBar');
                                if (statProg) statProg.textContent = `${data.project_progress}%`;
                                if (statProgBar) statProgBar.style.width = `${data.project_progress}%`;
                            }
                        }
                    })
                    .catch(err => {
                        window.toast?.error(err.message);
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    });
                });
            }

            // 3. AJAX Threat Form
            const threatForm = document.getElementById('ajaxThreatForm');
            if (threatForm) {
                threatForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const submitBtn = threatForm.querySelector('button[type="submit"]');
                    const originalBtnText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span>Menyimpan...</span>';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const formData = new FormData(threatForm);

                    fetch(threatForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: formData
                    })
                    .then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            const firstError = data.errors ? Object.values(data.errors)[0][0] : data.message;
                            throw new Error(firstError || 'Gagal mencatat threat.');
                        }
                        return data;
                    })
                    .then(data => {
                        document.getElementById('threatModal').close();
                        threatForm.reset();
                        window.toast?.success(data.message || 'Threat berhasil dicatat.');

                        const thr = data.threat;
                        if (thr) {
                            const listContainer = document.getElementById('threatsList');
                            const emptyNotice = document.getElementById('emptyThreatsNotice');
                            if (emptyNotice) emptyNotice.style.display = 'none';

                            if (listContainer) {
                                const isSevere = ['high', 'critical'].includes(thr.severity);
                                const article = document.createElement('article');
                                article.className = 'card';
                                article.style.marginBottom = '12px';
                                article.style.borderLeft = `3px solid ${isSevere ? '#ef4444' : '#f59e0b'}`;
                                article.innerHTML = `
                                    <div class="card-head">
                                        <div>
                                            <h3 class="font-bold text-slate-900">${escapeHtml(thr.title)}</h3>
                                            <p class="muted text-xs">${escapeHtml(thr.user_name)} · ${escapeHtml(thr.task_title)} · logged ${escapeHtml(thr.created_human)}</p>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="label text-xs ${isSevere ? 'danger' : 'warning'}">${escapeHtml(thr.severity)}</span>
                                            <span class="label text-xs">${escapeHtml(thr.status)}</span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-700 mb-2">${escapeHtml(thr.description)}</p>
                                    ${thr.mitigation_plan ? `
                                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 text-xs">
                                            <strong class="text-slate-900 block mb-0.5">Mitigation Plan:</strong>
                                            <span class="text-slate-600">${escapeHtml(thr.mitigation_plan)}</span>
                                        </div>
                                    ` : ''}
                                `;
                                listContainer.prepend(article);
                            }

                            const badge = document.getElementById('threatsOpenBadge');
                            if (badge) badge.textContent = `${data.open_threats} open`;
                            const statCount = document.getElementById('statOpenThreatsCount');
                            const statSub = document.getElementById('statOpenThreatsSubtext');
                            if (statCount) {
                                statCount.textContent = data.open_threats;
                                statCount.className = data.open_threats > 0 ? 'text-rose-600' : '';
                            }
                            if (statSub) {
                                statSub.textContent = data.open_threats > 0 ? 'Active operational risks' : 'No open threats';
                                statSub.className = `text-xs ${data.open_threats > 0 ? 'text-rose-600 font-medium' : 'text-slate-500'}`;
                            }
                        }
                    })
                    .catch(err => {
                        window.toast?.error(err.message);
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    });
                });
            }

            // 4. AJAX Add Document Form
            const addDocForm = document.getElementById('ajaxAddDocForm');
            if (addDocForm) {
                addDocForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const submitBtn = addDocForm.querySelector('button[type="submit"]');
                    const origText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span>Menyimpan...</span>';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const formData = new FormData(addDocForm);

                    fetch(addDocForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: formData
                    })
                    .then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            const firstErr = data.errors ? Object.values(data.errors)[0][0] : data.message;
                            throw new Error(firstErr || 'Gagal menambahkan dokumen.');
                        }
                        return data;
                    })
                    .then(data => {
                        document.getElementById('addDocumentModal').close();
                        addDocForm.reset();
                        window.toast?.success(data.message);
                        setTimeout(() => window.location.reload(), 600);
                    })
                    .catch(err => {
                        window.toast?.error(err.message);
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = origText;
                    });
                });
            }

            // 5. AJAX Edit Document File URL & Notes Form
            const docLinkForm = document.getElementById('ajaxDocLinkForm');
            if (docLinkForm) {
                docLinkForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const docId = document.getElementById('linkModalDocId').value;
                    const fileUrl = document.getElementById('linkModalFileUrl').value;
                    const notes = document.getElementById('linkModalNotes').value;
                    const submitBtn = docLinkForm.querySelector('button[type="submit"]');
                    const origText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span>Menyimpan...</span>';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    fetch(`/projects/{{ $project->id }}/documents/${docId}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            status: document.querySelector(`.doc-status-select-${docId}`)?.value || 'pending',
                            file_url: fileUrl,
                            notes: notes
                        })
                    })
                    .then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            throw new Error(data.message || 'Gagal memperbarui tautan berkas.');
                        }
                        return data;
                    })
                    .then(data => {
                        document.getElementById('documentLinkModal').close();
                        window.toast?.success('Tautan berkas berhasil disimpan.');
                        setTimeout(() => window.location.reload(), 600);
                    })
                    .catch(err => {
                        window.toast?.error(err.message);
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = origText;
                    });
                });
            }

            // 6. AJAX Escalate Document to Threat Form
            const escalateForm = document.getElementById('ajaxEscalateDocForm');
            if (escalateForm) {
                escalateForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const docId = document.getElementById('escalateDocId').value;
                    const notes = document.getElementById('escalateDocNotes').value;
                    const submitBtn = escalateForm.querySelector('button[type="submit"]');
                    const origText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span>Mengeskalasi...</span>';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    fetch(`/projects/{{ $project->id }}/documents/${docId}/escalate`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ notes: notes })
                    })
                    .then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            throw new Error(data.message || 'Gagal mengeskalasi dokumen ke threat.');
                        }
                        return data;
                    })
                    .then(data => {
                        document.getElementById('escalateDocThreatModal').close();
                        window.toast?.success(data.message);
                        setTimeout(() => window.location.reload(), 600);
                    })
                    .catch(err => {
                        window.toast?.error(err.message);
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = origText;
                    });
                });
            }

            // 7. AJAX Task Budget Form
            const taskBudgetForm = document.getElementById('ajaxTaskBudgetForm');
            if (taskBudgetForm) {
                taskBudgetForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const taskId = document.getElementById('budgetModalTaskId').value;
                    const estimatedHours = document.getElementById('budgetModalEstimatedHours').value;
                    const submitBtn = document.getElementById('budgetModalSubmitBtn');
                    const origText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span>Menyimpan...</span>';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    fetch(`/projects/{{ $project->id }}/tasks/${taskId}/estimate`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ estimated_hours: estimatedHours })
                    })
                    .then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            const firstErr = data.errors ? Object.values(data.errors)[0][0] : data.message;
                            throw new Error(firstErr || 'Gagal menyimpan estimasi jam tugas.');
                        }
                        return data;
                    })
                    .then(data => {
                        document.getElementById('taskBudgetModal').close();
                        window.toast?.success(data.message || 'Estimasi jam tugas berhasil diperbarui.');

                        if (data.task) {
                            updateTaskBudgetUI(data.task);
                        }
                        if (data.project_budget) {
                            updateProjectBudgetUI(data.project_budget);
                        }
                    })
                    .catch(err => {
                        window.toast?.error(err.message);
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = origText;
                    });
                });
            }

            // 8. AJAX Project Budget Cap Form
            const projectBudgetForm = document.getElementById('ajaxProjectBudgetForm');
            if (projectBudgetForm) {
                projectBudgetForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const hoursInput = document.getElementById('projectBudgetEstimatedHoursInput').value;
                    const submitBtn = document.getElementById('projectBudgetSubmitBtn');
                    const origText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span>Menyimpan...</span>';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    fetch(`/projects/{{ $project->id }}/budget`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ estimated_hours: hoursInput })
                    })
                    .then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) {
                            const firstErr = data.errors ? Object.values(data.errors)[0][0] : data.message;
                            throw new Error(firstErr || 'Gagal menyimpan kuota proyek.');
                        }
                        return data;
                    })
                    .then(data => {
                        document.getElementById('editProjectBudgetModal').close();
                        window.toast?.success(data.message || 'Target kuota proyek berhasil diperbarui.');

                        if (data.project_budget) {
                            updateProjectBudgetUI(data.project_budget);
                        }
                    })
                    .catch(err => {
                        window.toast?.error(err.message);
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = origText;
                    });
                });
            }
        });
    </script>
</x-layouts.app>
