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
            <div class="grid grid-cols-4 gap-1 mb-2">
                <div class="text-center py-1 rounded bg-emerald-50 border border-emerald-100" title="Done / Selesai">
                    <div class="text-[11px] font-bold text-emerald-800">{{ $project->tasks->where('status', 'completed')->count() }}</div>
                    <div class="text-[9px] uppercase font-bold text-emerald-600">Done</div>
                </div>
                <div class="text-center py-1 rounded bg-blue-50 border border-blue-100" title="In Progress / Berjalan">
                    <div class="text-[11px] font-bold text-blue-800">{{ $project->tasks->where('status', 'in_progress')->count() }}</div>
                    <div class="text-[9px] uppercase font-bold text-blue-600">Active</div>
                </div>
                <div class="text-center py-1 rounded bg-amber-50 border border-amber-100" title="Waiting Client / Review">
                    <div class="text-[11px] font-bold text-amber-800">{{ $project->tasks->where('status', 'waiting_client')->count() }}</div>
                    <div class="text-[9px] uppercase font-bold text-amber-600">Wait</div>
                </div>
                <div class="text-center py-1 rounded bg-slate-100 border border-slate-200" title="To Do / Belum Dimulai">
                    <div class="text-[11px] font-bold text-slate-700">{{ $project->tasks->where('status', 'not_started')->count() }}</div>
                    <div class="text-[9px] uppercase font-bold text-slate-500">To Do</div>
                </div>
            </div>
            <div class="text-[11px] text-slate-500 truncate" id="statTasksSubtext">
                {{ $project->tasks->where('status', 'completed')->count() }} done · {{ $project->tasks->where('status', 'in_progress')->count() }} in progress
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

            <!-- View Switcher Tabs: Board vs List -->
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
                </div>
                <span class="text-xs text-slate-400">Jira Workflow Engine</span>
            </div>

            <!-- 1. JIRA KANBAN BOARD VIEW -->
            <div id="jiraBoardView">
                <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; align-items: start;">
                    @php
                        $columns = [
                            ['status' => 'not_started', 'label' => 'To Do', 'color' => 'slate', 'badge' => 'bg-slate-100 text-slate-700 border-slate-200'],
                            ['status' => 'in_progress', 'label' => 'In Progress', 'color' => 'blue', 'badge' => 'bg-blue-50 text-blue-800 border-blue-200'],
                            ['status' => 'waiting_client', 'label' => 'Waiting Client', 'color' => 'amber', 'badge' => 'bg-amber-50 text-amber-800 border-amber-200'],
                            ['status' => 'completed', 'label' => 'Done', 'color' => 'emerald', 'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
                        ];
                    @endphp

                    @foreach ($columns as $col)
                        @php
                            $colTasks = $project->tasks->where('status', $col['status']);
                        @endphp
                        <div class="bg-slate-100/70 border border-slate-200/80 rounded-xl p-3">
                            <!-- Column Header -->
                            <div class="flex items-center justify-between mb-3 px-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">{{ $col['label'] }}</span>
                                    <span class="text-[11px] font-bold px-1.5 py-0.2 rounded-full border {{ $col['badge'] }}" id="col-count-{{ $col['status'] }}">
                                        {{ $colTasks->count() }}
                                    </span>
                                </div>
                            </div>

                            <!-- Task Cards in this column -->
                            <div class="space-y-2.5 min-h-[140px]" id="kanban-col-{{ $col['status'] }}">
                                @foreach ($colTasks as $task)
                                    <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-xs hover:shadow-sm hover:border-slate-300 transition-all group task-card-item" id="task-card-{{ $task->id }}" data-task-id="{{ $task->id }}" data-status="{{ $task->status }}">
                                        <!-- Issue Key & Priority -->
                                        <div class="flex items-center justify-between gap-1 mb-1.5">
                                            <span class="text-[11px] font-mono font-semibold text-slate-500">
                                                TSK-{{ $task->id }}
                                            </span>
                                            <div class="flex items-center gap-1">
                                                @if ($task->threats->where('status', 'open')->count() > 0)
                                                    <span title="Open risks on this task" class="text-rose-600">
                                                        <x-heroicon-s-exclamation-triangle class="w-3.5 h-3.5" />
                                                    </span>
                                                @endif
                                                <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-slate-100 text-slate-700 uppercase task-progress-badge">
                                                    {{ $task->progress_percent }}%
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Title -->
                                        <div class="text-xs font-semibold text-slate-900 leading-snug mb-2 task-title-text">
                                            {{ $task->title }}
                                        </div>

                                        @if ($task->notes)
                                            <p class="text-[11px] text-slate-500 line-clamp-2 mb-2 task-notes-text">
                                                {{ $task->notes }}
                                            </p>
                                        @endif

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
                                                <span class="truncate max-w-[80px]" title="{{ $task->assignee?->name ?? 'Unassigned' }}">
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
                                            @endphp
                                            @if(auth()->check() && auth()->user()->isStaff() && (int)$task->assigned_to === (int)auth()->id())
                                                <button
                                                    type="button"
                                                    data-task-timer-btn="{{ $task->id }}"
                                                    onclick="window.KonsulinTimer.start({{ $task->id }})"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition cursor-pointer shrink-0"
                                                    title="Mulai Waktu Kerja"
                                                >
                                                    <x-heroicon-o-play class="w-3 h-3 text-slate-500" />
                                                    <span>Mulai</span>
                                                </button>
                                            @endif
                                            <form method="POST" action="{{ route('projects.tasks.update-status', [$project, $task]) }}" class="inline-flex gap-1 m-0">
                                                @csrf
                                                @method('PATCH')
                                                <select
                                                    name="status"
                                                    @if(!$canEditThisTask)
                                                        disabled
                                                        title="Hanya staff yang ditugaskan yang dapat memperbarui tugas ini"
                                                        class="text-[10px] py-0.5 px-1.5 h-6 bg-slate-100 border border-slate-200 rounded text-slate-400 cursor-not-allowed font-medium task-status-select"
                                                    @else
                                                        onchange="changeTaskStatus(this, '{{ route('projects.tasks.update-status', [$project, $task]) }}', {{ $task->id }})"
                                                        class="text-[10px] py-0.5 px-1.5 h-6 bg-slate-50 border border-slate-200 rounded text-slate-700 cursor-pointer font-medium task-status-select"
                                                    @endif
                                                >
                                                    <option value="not_started" {{ $task->status === 'not_started' ? 'selected' : '' }}>To Do</option>
                                                    <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                    <option value="waiting_client" {{ $task->status === 'waiting_client' ? 'selected' : '' }}>Waiting</option>
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
                                        </td>
                                        <td>{{ $task->assignee?->name ?? 'Unassigned' }}</td>
                                        <td><span class="label task-table-status-label">{{ str_replace('_', ' ', $task->status) }}</span></td>
                                        <td>
                                            <div class="font-bold task-table-progress">{{ $task->progress_percent }}%</div>
                                        </td>
                                        <td>{{ $task->due_date?->format('d M Y') ?? '-' }}</td>
                                        <td>
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
                                            @else
                                                <span class="text-xs text-slate-400">-</span>
                                            @endif
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
                        @foreach (['not_started', 'in_progress', 'waiting_client', 'completed'] as $status)
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
                <label>Employee
                    <select name="user_id" required>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" {{ auth()->id() === $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="form-grid">
                <label>Progress % <input type="number" name="progress_percent" min="0" max="100" required placeholder="Contoh: 75"></label>
                <label>Attachment path <input name="attachment_path" placeholder="progress/document.pdf"></label>
            </div>
            <label>Summary <textarea name="summary" required placeholder="Uraikan hasil kerja dan kemajuan yang dicapai..."></textarea></label>
            <div class="modal-actions">
                <button type="button" class="button secondary" onclick="document.getElementById('progressModal').close()">Cancel</button>
                <button class="button" type="submit">Submit Progress</button>
            </div>
        </form>
    </dialog>

    <dialog id="threatModal">
        <div class="modal-head">
            <h2>Log Operational Threat / Risk</h2>
            <button class="icon-button" type="button" onclick="document.getElementById('threatModal').close()">&times;</button>
        </div>
        <form method="POST" action="{{ route('projects.threats.store', $project) }}" class="modal-body" id="ajaxThreatForm">
            @csrf
            <label>Title <input name="title" required placeholder="Contoh: Klien belum menyerahkan dokumen"></label>
            <div class="form-grid">
                <label>Task
                    <select name="project_task_id" class="task-options-select">
                        <option value="">Project threat</option>
                        @foreach ($project->tasks as $task)
                            <option value="{{ $task->id }}">{{ $task->title }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Employee
                    <select name="user_id" required>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" {{ auth()->id() === $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                        @endforeach
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

    <script>
        function switchProjectView(view) {
            const board = document.getElementById('jiraBoardView');
            const list = document.getElementById('jiraListView');
            const boardBtn = document.getElementById('tabBoardBtn');
            const listBtn = document.getElementById('tabListBtn');

            if (view === 'board') {
                board.style.display = 'block';
                list.style.display = 'none';
                boardBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#0b192c] text-white flex items-center gap-1.5 transition cursor-pointer';
                listBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition cursor-pointer';
            } else {
                board.style.display = 'none';
                list.style.display = 'block';
                listBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#0b192c] text-white flex items-center gap-1.5 transition cursor-pointer';
                boardBtn.className = 'px-3 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center gap-1.5 transition cursor-pointer';
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

        function updateKanbanColumnStates() {
            ['not_started', 'in_progress', 'waiting_client', 'completed'].forEach(status => {
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
        });
    </script>
</x-layouts.app>
