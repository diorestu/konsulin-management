<x-layouts.app :title="$project->name . ' - Konsulin Manager'">
    <div class="topbar">
        <div>
            <h1>{{ $project->name }}</h1>
            <p class="muted">{{ $project->client->name }} · {{ $project->category?->name ?? 'Uncategorized' }} · {{ $project->service_type }} · due {{ $project->due_date?->format('d M Y') ?? '-' }}</p>
        </div>
        <a class="button secondary" href="{{ route('projects.index') }}">Back</a>
    </div>

    <section class="stats" data-animate-children>
        <div class="stat">
            <strong>{{ $project->progressPercent() }}%</strong>
            <span class="muted">Overall progress</span>
        </div>
        <div class="stat">
            <strong>{{ $project->tasks->count() }}</strong>
            <span class="muted">Tasks</span>
        </div>
        <div class="stat">
            <strong>{{ $project->threats->where('status', 'open')->count() }}</strong>
            <span class="muted">Open threats</span>
        </div>
        <div class="stat">
            <strong>{{ $project->staff->count() }}</strong>
            <span class="muted">Assigned staff</span>
        </div>
    </section>

    <div class="grid">
        <div>
            <section class="panel">
                <h2>Assigned Staff</h2>
                <div class="actions">
                    @forelse ($project->staff as $assignedStaff)
                        <span class="label">{{ $assignedStaff->name }} · {{ $assignedStaff->type }}</span>
                    @empty
                        <span class="muted">No staff assigned.</span>
                    @endforelse
                </div>
            </section>

            <section class="panel">
                <h2>Task Monitoring</h2>
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
                                <td>{{ $task->title }}</td>
                                <td>{{ $task->assignee?->name ?? 'Unassigned' }}</td>
                                <td>{{ str_replace('_', ' ', $task->status) }}</td>
                                <td>{{ $task->progress_percent }}%</td>
                                <td>{{ $task->due_date?->format('d M Y') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="muted">No tasks yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>

            <section class="panel">
                <h2>Progress Updates</h2>
                @forelse ($project->progressUpdates as $update)
                    <article class="card" style="margin-bottom: 10px;">
                        <div class="card-head">
                            <div>
                                <h3>{{ $update->user->name }} uploaded {{ $update->progress_percent }}%</h3>
                                <p class="muted">{{ $update->task?->title ?? 'Project update' }} · {{ $update->created_at->format('d M Y H:i') }}</p>
                            </div>
                            <span class="label">{{ $update->progress_percent }}%</span>
                        </div>
                        <p>{{ $update->summary }}</p>
                        @if ($update->attachment_path)
                            <p class="muted">Attachment: {{ $update->attachment_path }}</p>
                        @endif
                    </article>
                @empty
                    <p class="muted">No progress uploaded yet.</p>
                @endforelse
            </section>

            <section class="panel">
                <h2>Threats</h2>
                @forelse ($project->threats as $threat)
                    <article class="card" style="margin-bottom: 10px;">
                        <div class="card-head">
                            <div>
                                <h3>{{ $threat->title }}</h3>
                                <p class="muted">{{ $threat->user->name }} · {{ $threat->task?->title ?? 'Project' }}</p>
                            </div>
                            <span class="label {{ $threat->severity === 'high' || $threat->severity === 'critical' ? 'danger' : 'warning' }}">{{ $threat->severity }}</span>
                        </div>
                        <p>{{ $threat->description }}</p>
                        @if ($threat->mitigation_plan)
                            <p><strong>Mitigation:</strong> {{ $threat->mitigation_plan }}</p>
                        @endif
                    </article>
                @empty
                    <p class="muted">No threats logged.</p>
                @endforelse
            </section>
        </div>

        <aside data-animate-children>
            <section class="panel">
                <h2>Add Task</h2>
                <form method="POST" action="{{ route('projects.tasks.store', $project) }}">
                    @csrf
                    <label>Task title <input name="title" required></label>
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
                    <label>Notes <textarea name="notes"></textarea></label>
                    <button class="button" type="submit">Add Task</button>
                </form>
            </section>

            <section class="panel">
                <h2>Upload Progress</h2>
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
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Progress % <input type="number" name="progress_percent" min="0" max="100" required></label>
                    <label>Summary <textarea name="summary" required></textarea></label>
                    <label>Attachment path <input name="attachment_path" placeholder="progress/report.xlsx"></label>
                    <button class="button" type="submit">Upload Progress</button>
                </form>
            </section>

            <section class="panel">
                <h2>Log Threat</h2>
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
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Title <input name="title" required></label>
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
                    <label>Description <textarea name="description" required></textarea></label>
                    <label>Mitigation plan <textarea name="mitigation_plan"></textarea></label>
                    <button class="button" type="submit">Log Threat</button>
                </form>
            </section>
        </aside>
    </div>
</x-layouts.app>
