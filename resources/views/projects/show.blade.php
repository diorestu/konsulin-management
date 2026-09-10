<x-layouts.app :title="$project->name . ' — Jira Board — Konsulin Manager'">
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
            <button class="button small" type="button" onclick="document.getElementById('taskFormModal').showModal()">
                <x-heroicon-o-plus class="w-4 h-4" />
                <span>New Issue / Task</span>
            </button>
        </div>
    </div>

    <!-- Jira Stats Bar -->
    <section class="stats" data-animate-children>
        <div class="stat">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Overall progress</span>
                <div class="w-8 h-8 rounded-lg bg-[#0b192c]/5 text-[#0b192c] flex items-center justify-center">
                    <x-heroicon-o-chart-pie class="w-4 h-4" />
                </div>
            </div>
            <strong>{{ $project->progressPercent() }}%</strong>
            <div class="progress" style="margin: 6px 0 0;"><span style="width: {{ $project->progressPercent() }}%"></span></div>
        </div>
        <div class="stat">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tasks</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
                    <x-heroicon-o-check-circle class="w-4 h-4" />
                </div>
            </div>
            <strong>{{ $project->tasks->count() }}</strong>
            <span class="text-xs text-slate-500">{{ $project->tasks->where('status', 'completed')->count() }} done · {{ $project->tasks->where('status', 'in_progress')->count() }} in progress</span>
        </div>
        <div class="stat">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Open threats</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-700 flex items-center justify-center">
                    <x-heroicon-o-shield-exclamation class="w-4 h-4" />
                </div>
            </div>
            <strong class="{{ $project->threats->where('status', 'open')->count() > 0 ? 'text-rose-600' : '' }}">
                {{ $project->threats->where('status', 'open')->count() }}
            </strong>
            <span class="text-xs {{ $project->threats->where('status', 'open')->count() > 0 ? 'text-rose-600 font-medium' : 'text-slate-500' }}">
                {{ $project->threats->where('status', 'open')->count() > 0 ? 'Active operational risks' : 'No open threats' }}
            </span>
        </div>
        <div class="stat">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Assigned staff</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <x-heroicon-o-user-group class="w-4 h-4" />
                </div>
            </div>
            <strong>{{ $project->staff->count() }}</strong>
            <span class="text-xs text-slate-500">Cross-functional team</span>
        </div>
    </section>

    <!-- Main Content & Jira Board -->
    <div class="grid" style="grid-template-columns: minmax(0, 1.8fr) minmax(320px, 0.8fr); gap: 20px;">
        <div>
            <!-- Assigned Staff Chips -->
            <div class="panel p-4 mb-4 flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-2">
                    <h2 style="margin: 0; font-size: 14px;">Assigned Staff</h2>
                    <span class="text-xs text-slate-400 font-medium">({{ $project->staff->count() }} members)</span>
                </div>
                <div class="actions">
                    @forelse ($project->staff as $assignedStaff)
                        <span class="label navy flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#0b192c]"></span>
                            <span>{{ $assignedStaff->name }} · {{ $assignedStaff->type }}</span>
                        </span>
                    @empty
                        <span class="muted text-xs">No staff assigned.</span>
                    @endforelse
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
                                    <span class="text-[11px] font-bold px-1.5 py-0.2 rounded-full border {{ $col['badge'] }}">
                                        {{ $colTasks->count() }}
                                    </span>
                                </div>
                            </div>

                            <!-- Task Cards in this column -->
                            <div class="space-y-2.5 min-h-[140px]">
                                @forelse ($colTasks as $task)
                                    <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-xs hover:shadow-sm hover:border-slate-300 transition-all group">
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
                                                <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-slate-100 text-slate-700 uppercase">
                                                    {{ $task->progress_percent }}%
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Title -->
                                        <div class="text-xs font-semibold text-slate-900 leading-snug mb-2">
                                            {{ $task->title }}
                                        </div>

                                        @if ($task->notes)
                                            <p class="text-[11px] text-slate-500 line-clamp-2 mb-2">
                                                {{ $task->notes }}
                                            </p>
                                        @endif

                                        <!-- Progress Bar -->
                                        <div class="w-full bg-slate-100 rounded-full h-1.5 mb-2 overflow-hidden">
                                            <div class="bg-[#0b192c] h-1.5 rounded-full" style="width: {{ $task->progress_percent }}%"></div>
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

                                        <!-- Quick Status Move Menu (Jira Style Transition) -->
                                        <div class="mt-2 pt-1.5 border-t border-dashed border-slate-100 flex items-center justify-between">
                                            <span class="text-[10px] text-slate-400">Move to:</span>
                                            <form method="POST" action="{{ route('projects.tasks.update-status', [$project, $task]) }}" class="inline-flex gap-1 m-0">
                                                @csrf
                                                @method('PATCH')
                                                <select
                                                    name="status"
                                                    onchange="this.form.submit()"
                                                    class="text-[10px] py-0.5 px-1.5 h-6 bg-slate-50 border border-slate-200 rounded text-slate-700 cursor-pointer font-medium"
                                                >
                                                    <option value="not_started" {{ $task->status === 'not_started' ? 'selected' : '' }}>To Do</option>
                                                    <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                    <option value="waiting_client" {{ $task->status === 'waiting_client' ? 'selected' : '' }}>Waiting</option>
                                                    <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Done</option>
                                                </select>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-6 text-[11px] text-slate-400 border border-dashed border-slate-200 rounded-lg">
                                        No tasks in {{ $col['label'] }}
                                    </div>
                                @endforelse
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
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($project->tasks as $task)
                                    <tr>
                                        <td>
                                            <div class="font-semibold text-slate-900">{{ $task->title }}</div>
                                            @if ($task->notes)
                                                <div class="text-xs text-slate-500">{{ $task->notes }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $task->assignee?->name ?? 'Unassigned' }}</td>
                                        <td><span class="label">{{ str_replace('_', ' ', $task->status) }}</span></td>
                                        <td>
                                            <div class="font-bold">{{ $task->progress_percent }}%</div>
                                        </td>
                                        <td>{{ $task->due_date?->format('d M Y') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="muted">No tasks yet.</td></tr>
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
                    <p class="muted text-xs">No progress uploaded yet.</p>
                @endforelse
            </section>

            <!-- Operational Threats / Risk Management -->
            <section class="panel">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <h2>Threats</h2>
                        <span class="label danger text-xs">{{ $project->threats->where('status', 'open')->count() }} open</span>
                    </div>
                    <button class="button secondary small" type="button" onclick="document.getElementById('threatModal').showModal()">
                        <x-heroicon-o-shield-exclamation class="w-3.5 h-3.5 text-rose-600" />
                        <span>Log Threat</span>
                    </button>
                </div>
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
                    <p class="muted text-xs">No threats logged.</p>
                @endforelse
            </section>
        </div>

        <!-- Right Side Management Panel (Quick forms & actions) -->
        <aside data-animate-children>
            <section class="panel">
                <div class="flex items-center justify-between mb-2">
                    <h2>Add Task</h2>
                    <span class="text-[11px] text-slate-400">Quick Entry</span>
                </div>
                <form method="POST" action="{{ route('projects.tasks.store', $project) }}">
                    @csrf
                    <label>Task title <input name="title" required placeholder="Contoh: Rekonsiliasi Faktur Pajak"></label>
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
                    <label>Progress % <input type="number" name="progress_percent" min="0" max="100" value="0" required></label>
                    <label>Due date <input type="date" name="due_date"></label>
                    <label>Notes <textarea name="notes" placeholder="Detail catatan tugas..."></textarea></label>
                    <button class="button" type="submit">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        <span>Add Task</span>
                    </button>
                </form>
            </section>

            <section class="panel">
                <div class="flex items-center justify-between mb-2">
                    <h2>Upload Progress</h2>
                    <span class="text-[11px] text-slate-400">Log Milestone</span>
                </div>
                <form method="POST" action="{{ route('projects.progress.store', $project) }}">
                    @csrf
                    <label>Task
                        <select name="project_task_id">
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
                    <label>Progress % <input type="number" name="progress_percent" min="0" max="100" required placeholder="Contoh: 75"></label>
                    <label>Summary <textarea name="summary" required placeholder="Uraikan hasil kerja dan kemajuan yang dicapai..."></textarea></label>
                    <label>Attachment path <input name="attachment_path" placeholder="progress/bank-recap.xlsx"></label>
                    <button class="button" type="submit">
                        <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                        <span>Upload Progress</span>
                    </button>
                </form>
            </section>

            <section class="panel">
                <div class="flex items-center justify-between mb-2">
                    <h2>Log Threat</h2>
                    <span class="text-[11px] text-rose-600 font-semibold">Risk Alert</span>
                </div>
                <form method="POST" action="{{ route('projects.threats.store', $project) }}">
                    @csrf
                    <label>Task
                        <select name="project_task_id">
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
                    <label>Title <input name="title" required placeholder="Contoh: Klien belum menyerahkan dokumen"></label>
                    <label>Severity
                        <select name="severity">
                            @foreach (['low', 'medium', 'high', 'critical'] as $severity)
                                <option value="{{ $severity }}">{{ $severity }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Status
                        <select name="status">
                            @foreach (['open', 'monitoring', 'resolved'] as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Description <textarea name="description" required placeholder="Deskripsi risiko/kendala..."></textarea></label>
                    <label>Mitigation plan <textarea name="mitigation_plan" placeholder="Rencana tindakan mitigasi..."></textarea></label>
                    <button class="button danger" type="submit">
                        <x-heroicon-o-shield-exclamation class="w-4 h-4" />
                        <span>Log Threat</span>
                    </button>
                </form>
            </section>
        </aside>
    </div>

    <!-- Modals for Full Dialogs -->
    <dialog id="taskFormModal">
        <div class="modal-head">
            <h2>Create New Issue / Task</h2>
            <button class="icon-button" type="button" onclick="document.getElementById('taskFormModal').close()">&times;</button>
        </div>
        <form method="POST" action="{{ route('projects.tasks.store', $project) }}" class="modal-body">
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
        <form method="POST" action="{{ route('projects.progress.store', $project) }}" class="modal-body">
            @csrf
            <div class="form-grid">
                <label>Task
                    <select name="project_task_id">
                        <option value="">Project update</option>
                        @foreach ($project->tasks as $task)
                            <option value="{{ $task->id }}">{{ $task->title }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Progress % <input type="number" name="progress_percent" min="0" max="100" required></label>
            </div>
            <label>Summary <textarea name="summary" required placeholder="Uraikan progress yang dicapai..."></textarea></label>
            <label>Attachment path <input name="attachment_path" placeholder="progress/document.pdf"></label>
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
        <form method="POST" action="{{ route('projects.threats.store', $project) }}" class="modal-body">
            @csrf
            <label>Title <input name="title" required placeholder="Judul risiko"></label>
            <div class="form-grid">
                <label>Severity
                    <select name="severity">
                        @foreach (['low', 'medium', 'high', 'critical'] as $severity)
                            <option value="{{ $severity }}">{{ $severity }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Status
                    <select name="status">
                        @foreach (['open', 'monitoring', 'resolved'] as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <label>Description <textarea name="description" required placeholder="Deskripsi kendala..."></textarea></label>
            <label>Mitigation plan <textarea name="mitigation_plan" placeholder="Rencana penanganan..."></textarea></label>
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
    </script>
</x-layouts.app>
