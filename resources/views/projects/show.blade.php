<x-layouts.app :title="$project->name . ' : Jira Board : Konsulin Manager'">
    <!-- Topbar & Project Header -->
    <div class="topbar">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <a href="{{ route('projects.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 flex items-center gap-1">
                    <x-heroicon-o-arrow-left class="w-3.5 h-3.5" />
                    <span>Projects</span>
                </a>
                <span class="text-xs text-slate-300">/</span>
                <span class="text-xs font-mono font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200">
                    PRJ-{{ str_pad($project->id, 3, '0', STR_PAD_LEFT) }}
                </span>
                <span class="label navy text-xs">{{ $project->service_type }}</span>
                <span class="label {{ in_array($project->priority, ['high', 'urgent']) ? 'warning' : 'navy' }}">
                    {{ $project->priority }}
                </span>
            </div>
            <h1>{{ $project->name }}</h1>
            <p class="muted">
                {{ $project->client->name }} · {{ $project->category?->name ?? 'Uncategorized' }} · Due: {{ $project->due_date?->format('d M Y') ?? '-' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a class="button secondary small" href="{{ route('projects.index') }}">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                <span>Back</span>
            </a>
            @if(!auth()->check() || !auth()->user()->isStaff() || auth()->user()->can('manage tasks'))
                <button class="button small" type="button" onclick="document.getElementById('taskFormModal').showModal()">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    <span>New Issue / Task</span>
                </button>
            @endif
        </div>
    </div>

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

        <!-- Stat 3: Staff Logged Work Time (Connected to Time Tracker!) -->
        <div class="stat">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Logged Work Time</span>
                <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center border border-indigo-100">
                    <x-heroicon-o-clock class="w-4 h-4" />
                </div>
            </div>
            <div class="flex items-baseline gap-2 mb-1.5">
                <strong class="text-2xl font-bold tracking-tight text-slate-900 font-mono">
                    {{ $project->formattedTotalLoggedTime() }}
                </strong>
                <span class="text-xs text-slate-500 font-medium">Tercatat</span>
            </div>
            <div class="p-1.5 rounded-lg bg-slate-50 border border-slate-200 mb-2 flex items-center justify-between text-[11px]">
                <span class="text-slate-600 font-medium flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Staff Timer</span>
                </span>
                <span class="font-bold text-slate-900 font-mono">{{ $project->staff->count() }} PIC Ditugaskan</span>
            </div>
            <div class="text-[11px] text-slate-500 truncate">
                Lacak jam kerja real-time tim konsultan
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
    <div class="grid" style="grid-template-columns: minmax(0, 1.8fr) minmax(320px, 0.8fr); gap: 20px;">
        <div>
            <!-- Team Composition (Reviewer, PIC Accounting, PIC Tax) -->
            <div class="panel p-4 mb-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <h2 style="margin: 0; font-size: 14px;">Team Composition & Roles</h2>
                        <span class="text-xs text-slate-400 font-medium">(1 Client · 1 Reviewer · {{ $project->accountingStaff->count() }} PIC Accounting · {{ $project->taxStaff->count() }} PIC Tax)</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Reviewer Card -->
                    <div class="p-3 rounded-lg bg-slate-900 text-white shadow-sm">
                        <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span>Reviewer (1 Orang)</span>
                        </div>
                        <div class="font-bold text-xs text-white">{{ $project->reviewer?->name ?? $project->creator?->name ?? '-' }}</div>
                        <div class="text-[11px] text-slate-300 truncate">{{ $project->reviewer?->email ?? '-' }}</div>
                    </div>

                    <!-- PIC Accounting Card -->
                    <div class="p-3 rounded-lg bg-blue-50/70 border border-blue-200">
                        <div class="text-[10px] uppercase font-bold text-blue-700 tracking-wider mb-1 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                            <span>PIC Accounting ({{ $project->accountingStaff->count() }})</span>
                        </div>
                        <div class="space-y-1">
                            @forelse($project->accountingStaff as $acc)
                                <div class="text-xs font-semibold text-slate-900 flex items-center justify-between">
                                    <span>{{ $acc->name }}</span>
                                    <span class="text-[10px] text-blue-700 font-normal">{{ $acc->position ?? 'Accounting' }}</span>
                                </div>
                            @empty
                                <div class="text-xs text-slate-400 italic">Belum ada PIC Accounting</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- PIC Tax Card -->
                    <div class="p-3 rounded-lg bg-amber-50/70 border border-amber-200">
                        <div class="text-[10px] uppercase font-bold text-amber-800 tracking-wider mb-1 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                            <span>PIC Tax ({{ $project->taxStaff->count() }})</span>
                        </div>
                        <div class="space-y-1">
                            @forelse($project->taxStaff as $tax)
                                <div class="text-xs font-semibold text-slate-900 flex items-center justify-between">
                                    <span>{{ $tax->name }}</span>
                                    <span class="text-[10px] text-amber-800 font-normal">{{ $tax->position ?? 'Tax' }}</span>
                                </div>
                            @empty
                                <div class="text-xs text-slate-400 italic">Belum ada PIC Tax</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

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
                        <span>Kanban Board</span>
                    </button>
                    <button
                        type="button"
                        id="tabListBtn"
                        onclick="switchProjectView('list')"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition cursor-pointer"
                    >
                        <x-heroicon-o-queue-list class="w-4 h-4" />
                        <span>List / Table View</span>
                    </button>
                    <button
                        type="button"
                        id="tabDocsBtn"
                        onclick="switchProjectView('documents')"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition cursor-pointer"
                    >
                        <x-heroicon-o-document-check class="w-4 h-4" />
                        <span>Dokumen Masukan (Vault)</span>
                        @php
                            $totalDocs = $project->documents->count();
                            $verifiedDocs = $project->documents->where('status', 'verified')->count();
                            $criticalPending = $project->documents->where('is_critical', true)->whereIn('status', ['pending', 'partial'])->count();
                        @endphp
                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-full {{ $criticalPending > 0 ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-slate-100 text-slate-700' }}" id="tabDocsBadge">
                            {{ $verifiedDocs }}/{{ $totalDocs }}
                        </span>
                    </button>
                </div>
                <span class="text-xs text-slate-400">Jira Workflow Engine</span>
            </div>

            <!-- 1. JIRA KANBAN BOARD VIEW -->
            <div id="jiraBoardView">
                <div style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; align-items: start;">
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
                        <div class="bg-slate-100/70 border border-slate-200/80 rounded-xl p-2.5">
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
                            <div class="space-y-2.5 min-h-[140px]" id="kanban-col-{{ $col['status'] }}">
                                @foreach ($colTasks as $task)
                                    <div class="bg-white border border-slate-200 rounded-lg p-2.5 shadow-xs hover:shadow-sm hover:border-slate-300 transition-all group task-card-item" id="task-card-{{ $task->id }}" data-task-id="{{ $task->id }}" data-status="{{ $task->status }}">
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

                                        <!-- Quality Checklist Button Indicator -->
                                        <div class="mb-1.5 flex items-center justify-between gap-1">
                                            <button
                                                type="button"
                                                onclick="openQualityGateModal({{ $task->id }})"
                                                class="inline-flex items-center gap-1 text-[10px] font-medium text-slate-700 hover:text-indigo-700 bg-slate-50 hover:bg-indigo-50/60 px-1.5 py-0.5 rounded border border-slate-200 transition cursor-pointer"
                                                title="Buka Quality Gate & Checklist Kertas Kerja"
                                            >
                                                <x-heroicon-o-clipboard-document-check class="w-3.5 h-3.5 text-indigo-600" />
                                                <span>{{ $task->checklists->where('is_checked', true)->count() }}/{{ $task->checklists->count() }} QC</span>
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
                                    </div>
                                @endforeach

                                <div class="text-center py-6 text-[11px] text-slate-400 border border-dashed border-slate-200 rounded-lg col-empty-placeholder" id="empty-col-{{ $col['status'] }}" style="{{ $colTasks->count() > 0 ? 'display: none;' : '' }}">
                                    No tasks in {{ $col['label'] }}
                                </div>
                            </div>
                        </div>
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
            <!-- Quick Actions Panel -->
            <section class="panel !p-4 !mb-0 border border-slate-200 rounded-xl bg-white shadow-sm">
                <div class="mb-3 pb-2 border-b border-slate-100">
                    <h2 class="!text-sm !font-bold text-slate-900 !mb-0.5 flex items-center gap-1.5">
                        <x-heroicon-o-bolt class="w-4 h-4 text-blue-600" />
                        <span>Quick Actions</span>
                    </h2>
                    <p class="text-[11px] text-slate-500">Aksi cepat untuk update pekerjaan proyek.</p>
                </div>

                <div class="flex flex-col gap-2.5">
                    <!-- Button 1: Add Task -->
                    <button
                        type="button"
                        onclick="document.getElementById('taskFormModal').showModal()"
                        class="w-full text-left p-3 rounded-lg border border-slate-200 hover:border-blue-400 bg-slate-50/60 hover:bg-blue-50/40 transition-all flex items-center justify-between group cursor-pointer"
                    >
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <x-heroicon-o-plus-circle class="w-5 h-5" />
                            </div>
                            <div>
                                <strong class="text-xs font-bold text-slate-900 group-hover:text-blue-700 block leading-tight">Add Task</strong>
                                <span class="text-[11px] text-slate-500 block mt-0.5">Tambah tugas atau issue kerja baru</span>
                            </div>
                        </div>
                        <x-heroicon-o-chevron-right class="w-4 h-4 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-0.5 transition-all" />
                    </button>

                    <!-- Button 2: Upload Progress -->
                    <button
                        type="button"
                        onclick="document.getElementById('progressModal').showModal()"
                        class="w-full text-left p-3 rounded-lg border border-slate-200 hover:border-emerald-400 bg-slate-50/60 hover:bg-emerald-50/40 transition-all flex items-center justify-between group cursor-pointer"
                    >
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <x-heroicon-o-arrow-up-tray class="w-5 h-5" />
                            </div>
                            <div>
                                <strong class="text-xs font-bold text-slate-900 group-hover:text-emerald-700 block leading-tight">Upload Progress</strong>
                                <span class="text-[11px] text-slate-500 block mt-0.5">Catat milestone & kemajuan proyek</span>
                            </div>
                        </div>
                        <x-heroicon-o-chevron-right class="w-4 h-4 text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition-all" />
                    </button>

                    <!-- Button 3: Log Threat -->
                    <button
                        type="button"
                        onclick="document.getElementById('threatModal').showModal()"
                        class="w-full text-left p-3 rounded-lg border border-slate-200 hover:border-rose-400 bg-slate-50/60 hover:bg-rose-50/40 transition-all flex items-center justify-between group cursor-pointer"
                    >
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                <x-heroicon-o-shield-exclamation class="w-5 h-5" />
                            </div>
                            <div>
                                <strong class="text-xs font-bold text-slate-900 group-hover:text-rose-700 block leading-tight">Log Threat</strong>
                                <span class="text-[11px] text-slate-500 block mt-0.5">Laporkan risiko atau blocker operasional</span>
                            </div>
                        </div>
                        <x-heroicon-o-chevron-right class="w-4 h-4 text-slate-400 group-hover:text-rose-600 group-hover:translate-x-0.5 transition-all" />
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
                <label>Due date <input type="date" name="due_date"></label>
            </div>
            <label>Notes <textarea name="notes" placeholder="Catatan detail..."></textarea></label>
            <div class="modal-actions">
                <button type="button" class="button secondary" onclick="document.getElementById('taskFormModal').close()">Cancel</button>
                <button class="button" type="submit">Save Task</button>
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
            const boardBtn = document.getElementById('tabBoardBtn');
            const listBtn = document.getElementById('tabListBtn');
            const docsBtn = document.getElementById('tabDocsBtn');

            const activeClass = 'px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#0b192c] text-white flex items-center gap-1.5 transition cursor-pointer';
            const inactiveClass = 'px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition cursor-pointer';

            if (board) board.style.display = (view === 'board') ? 'block' : 'none';
            if (list) list.style.display = (view === 'list') ? 'block' : 'none';
            if (docs) docs.style.display = (view === 'documents') ? 'block' : 'none';

            if (boardBtn) boardBtn.className = (view === 'board') ? activeClass : inactiveClass;
            if (listBtn) listBtn.className = (view === 'list') ? activeClass : inactiveClass;
            if (docsBtn) docsBtn.className = (view === 'documents') ? activeClass : inactiveClass;

            if (view === 'documents' && docs) {
                docs.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
        });
    </script>
</x-layouts.app>
