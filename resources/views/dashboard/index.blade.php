<x-layouts.app :title="(!empty($isStaff) && $isStaff) ? 'Dashboard Staff : Konsulin Manager' : 'Dashboard : Konsulin Manager'">
@if(!empty($isStaff) && $isStaff)
    <!-- Topbar Header for Staff -->
    <div class="topbar !mb-4 !pb-3">
        <div>
            <div class="flex items-center gap-2">
                <h1>Dashboard Staff</h1>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                    Staff Workspace
                </span>
            </div>
            <p class="muted !text-xs mt-0.5">Selamat datang, {{ auth()->user()->name }}. Kelola penugasan tugas Anda, pantau tenggat waktu, dan catat jam kerja.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('kanban.index') }}" class="button small">
                <x-heroicon-o-view-columns class="w-3.5 h-3.5" />
                <span>Kanban Board</span>
            </a>
            <a href="{{ route('projects.index') }}" class="button secondary small">
                <x-heroicon-o-folder class="w-3.5 h-3.5" />
                <span>Proyek Saya</span>
            </a>
            <button
                type="button"
                onclick="window.KonsulinTimer && window.KonsulinTimer.openTaskPickerModal()"
                class="button secondary small border-emerald-600/30 text-emerald-800 hover:bg-emerald-50 cursor-pointer"
            >
                <x-heroicon-o-play class="w-3.5 h-3.5 text-emerald-600" />
                <span>Lacak Waktu Kerja</span>
            </button>
        </div>
    </div>

    <!-- Staff Stat Strip (6 Cards) -->
    <div class="overflow-x-auto pb-1 mb-5 -mx-1 px-1">
        <section class="stats-row-6 grid grid-cols-6 gap-2.5 min-w-[680px] lg:min-w-0">
            <!-- 1. Total My Tasks -->
            <div class="stat !p-3 !rounded-xl border border-slate-200/90 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-1 mb-1">
                    <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider truncate">Tugas Ditugaskan</span>
                    <div class="w-5 h-5 rounded bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                        <x-heroicon-o-clipboard-document-check class="w-3 h-3" />
                    </div>
                </div>
                <strong class="text-lg font-bold text-slate-900 block leading-tight">{{ $totalMyTasks }}</strong>
                <span class="text-[10.5px] text-slate-500 font-medium truncate block mt-0.5">
                    {{ $completedTasks }} selesai · {{ $todoTasks + $inProgressTasks }} aktif
                </span>
            </div>

            <!-- 2. Sedang Dikerjakan -->
            <div class="stat !p-3 !rounded-xl border border-slate-200/90 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-1 mb-1">
                    <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider truncate">Sedang Dikerjakan</span>
                    <div class="w-5 h-5 rounded bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                        <x-heroicon-o-play class="w-3 h-3" />
                    </div>
                </div>
                <strong class="text-lg font-bold text-blue-700 block leading-tight">{{ $inProgressTasks }}</strong>
                <span class="text-[10.5px] text-blue-600 font-medium truncate block mt-0.5">Fokus pengerjaan</span>
            </div>

            <!-- 3. Tenggat Kritis -->
            <div class="stat !p-3 !rounded-xl border border-slate-200/90 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-1 mb-1">
                    <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider truncate">Tenggat &lt; 7 Hari</span>
                    <div class="w-5 h-5 rounded {{ $nearDeadlineTasks > 0 ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center shrink-0">
                        <x-heroicon-o-clock class="w-3 h-3" />
                    </div>
                </div>
                <strong class="text-lg font-bold {{ $nearDeadlineTasks > 0 ? 'text-amber-700' : 'text-slate-800' }} block leading-tight">{{ $nearDeadlineTasks }}</strong>
                <span class="text-[10.5px] {{ $nearDeadlineTasks > 0 ? 'text-amber-700 font-semibold' : 'text-slate-500 font-medium' }} truncate block mt-0.5">
                    {{ $nearDeadlineTasks > 0 ? 'Mendekati deadline' : 'Jadwal aman' }}
                </span>
            </div>

            <!-- 4. Proyek Saya -->
            <div class="stat !p-3 !rounded-xl border border-slate-200/90 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-1 mb-1">
                    <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider truncate">Proyek Saya</span>
                    <div class="w-5 h-5 rounded bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                        <x-heroicon-o-folder class="w-3 h-3" />
                    </div>
                </div>
                <strong class="text-lg font-bold text-emerald-700 block leading-tight">{{ $activeProjectsCount }}</strong>
                <span class="text-[10.5px] text-emerald-600 font-medium truncate block mt-0.5">
                    {{ $completedProjectsCount }} selesai diarsip
                </span>
            </div>

            <!-- 5. Jam Kerja Hari Ini -->
            <div class="stat !p-3 !rounded-xl border border-slate-200/90 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-1 mb-1">
                    <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider truncate">Waktu Hari Ini</span>
                    <div class="w-5 h-5 rounded bg-indigo-50 text-indigo-700 flex items-center justify-center shrink-0">
                        <x-heroicon-o-bolt class="w-3 h-3" />
                    </div>
                </div>
                <strong class="text-lg font-bold text-indigo-700 block leading-tight">
                    {{ floor($todayMinutes / 60) }}j {{ $todayMinutes % 60 }}m
                </strong>
                <span class="text-[10.5px] text-indigo-600 font-medium truncate block mt-0.5">
                    Minggu: {{ floor($weekMinutes / 60) }}j {{ $weekMinutes % 60 }}m
                </span>
            </div>

            <!-- 6. Status Time Tracker -->
            <div class="stat !p-3 !rounded-xl border border-slate-200/90 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-1 mb-1">
                    <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider truncate">Status Tracker</span>
                    <div class="w-5 h-5 rounded {{ $activeTimeLog ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center shrink-0">
                        <x-heroicon-o-arrow-path class="w-3 h-3 {{ $activeTimeLog ? 'animate-spin text-emerald-600' : '' }}" />
                    </div>
                </div>
                <strong class="text-lg font-bold {{ $activeTimeLog ? 'text-emerald-700' : 'text-slate-800' }} block leading-tight">
                    {{ $activeTimeLog ? 'Sedang Aktif' : 'Standby' }}
                </strong>
                <span class="text-[10.5px] {{ $activeTimeLog ? 'text-emerald-700 font-semibold' : 'text-slate-500 font-medium' }} truncate block mt-0.5">
                    {{ $activeTimeLog ? ($activeTimeLog->task->title ?? 'Tugas aktif') : 'Siap mulai sesi' }}
                </span>
            </div>
        </section>
    </div>

    <!-- Staff Main Grid (7 : 5) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start mb-8">
        <!-- Left Column: Tasks & Projects (Col 7) -->
        <div class="lg:col-span-7 flex flex-col gap-5">
            @if(isset($revisionTasks) && $revisionTasks->isNotEmpty())
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 shadow-xs">
                    <div class="flex items-center gap-2 text-rose-800 font-bold text-xs mb-1">
                        <x-heroicon-s-exclamation-triangle class="w-4 h-4 text-rose-600 shrink-0" />
                        <span>Perhatian: Terdapat {{ $revisionTasks->count() }} tugas yang memerlukan revisi dari Reviewer</span>
                    </div>
                    <p class="text-[11px] text-rose-700 leading-relaxed mb-2.5">
                        Reviewer telah memberikan catatan koreksi. Silakan periksa catatan dan ajukan ulang setelah perbaikan selesai.
                    </p>
                    <div class="space-y-2">
                        @foreach($revisionTasks as $revTask)
                            <div class="p-2.5 rounded-lg bg-white border border-rose-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div>
                                    <div class="font-bold text-slate-900">{{ $revTask->title }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">{{ $revTask->project->name ?? 'Proyek' }} · {{ $revTask->project->client->name ?? 'Klien' }}</div>
                                    <div class="text-[11px] text-rose-800 bg-rose-50/70 p-1.5 rounded mt-1 border border-rose-100 italic">
                                        &ldquo;{{ $revTask->review_notes }}&rdquo;
                                    </div>
                                </div>
                                <a href="{{ route('projects.show', $revTask->project_id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-rose-600 hover:bg-rose-700 text-white transition shrink-0">
                                    <span>Buka Tugas</span>
                                    <x-heroicon-o-arrow-right class="w-3 h-3" />
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Panel 1: Daftar Tugas Penugasan Saya -->
            <section class="panel !p-4 !mb-0 border border-slate-200 rounded-xl bg-white shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="!text-sm !font-bold text-slate-900 !mb-0.5 flex items-center gap-2">
                            <x-heroicon-o-check-badge class="w-4 h-4 text-blue-600" />
                            <span>Tugas Penugasan Saya</span>
                        </h2>
                        <p class="text-[11px] text-slate-500">Tugas yang ditugaskan kepada Anda pada seluruh proyek aktif.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                            {{ $myTasks->count() }} Tugas
                        </span>
                    </div>
                </div>

                @if($myTasks->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 font-semibold uppercase text-[10.5px]">
                                    <th class="py-2 px-2.5">Judul Tugas & Proyek</th>
                                    <th class="py-2 px-2.5">Prioritas</th>
                                    <th class="py-2 px-2.5">Tenggat</th>
                                    <th class="py-2 px-2.5">Status</th>
                                    <th class="py-2 px-2 text-right">Lacak Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($myTasks as $task)
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="py-2.5 px-2.5">
                                            <div class="font-semibold text-slate-900">{{ $task->title }}</div>
                                            <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1.5 truncate">
                                                <span class="font-medium text-slate-700">{{ $task->project->name ?? 'Proyek' }}</span>
                                                <span>·</span>
                                                <span>{{ $task->project->client->name ?? 'Klien' }}</span>
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-2.5 whitespace-nowrap">
                                            @if($task->priority === 'high')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">High</span>
                                            @elseif($task->priority === 'medium')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Medium</span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">Low</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-2.5 whitespace-nowrap">
                                            @if($task->due_date)
                                                <span class="font-mono text-[11px] text-slate-700">
                                                    {{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}
                                                </span>
                                            @else
                                                <span class="text-slate-400 text-[11px]">-</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-2.5 whitespace-nowrap">
                                            @if($task->status === 'completed' || $task->status === 'done')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">Selesai</span>
                                            @elseif($task->status === 'in_review' || $task->status === 'review')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200">In Review</span>
                                            @elseif($task->isRevisionRequested())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200" title="{{ $task->review_notes }}">Perlu Revisi</span>
                                            @elseif($task->status === 'in_progress')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200">In Progress</span>
                                            @elseif($task->status === 'waiting_client')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">Waiting</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">To Do</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-2 text-right whitespace-nowrap">
                                            @if($activeTimeLog && $activeTimeLog->project_task_id === $task->id)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-ping"></span>
                                                    <span>Berjalan</span>
                                                </span>
                                            @else
                                                <button
                                                    type="button"
                                                    onclick="window.KonsulinTimer && window.KonsulinTimer.start({{ $task->id }}, '{{ addslashes($task->title) }}', '{{ addslashes($task->project->client->name ?? 'Klien') }}')"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 hover:bg-[#0b192c] text-slate-700 hover:text-white border border-slate-200 transition cursor-pointer"
                                                    title="Mulai Lacak Waktu untuk Tugas Ini"
                                                >
                                                    <x-heroicon-o-play class="w-3 h-3 text-emerald-600" />
                                                    <span>Mulai</span>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-6 text-center text-slate-500 bg-slate-50/50 rounded-lg border border-dashed border-slate-200">
                        <x-heroicon-o-clipboard-document-check class="w-8 h-8 mx-auto mb-2 text-slate-400" />
                        <p class="text-xs font-medium">Belum ada tugas yang ditugaskan kepada Anda saat ini.</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Tugas yang di-assign oleh Admin atau Reviewer akan muncul di sini.</p>
                    </div>
                @endif
            </section>

            <!-- Panel 2: Proyek yang Ditugaskan ke Saya -->
            <section class="panel !p-4 !mb-0 border border-slate-200 rounded-xl bg-white shadow-sm">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="!text-sm !font-bold text-slate-900 !mb-0.5 flex items-center gap-2">
                            <x-heroicon-o-folder class="w-4 h-4 text-blue-600" />
                            <span>Proyek yang Ditugaskan ke Saya</span>
                        </h2>
                        <p class="text-[11px] text-slate-500">Daftar proyek aktif di mana Anda terlibat sebagai pelaksana tugas.</p>
                    </div>
                    <a href="{{ route('projects.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        <span>Lihat Semua</span>
                        <x-heroicon-o-arrow-right class="w-3 h-3" />
                    </a>
                </div>

                @if($myProjects->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($myProjects as $proj)
                            @php
                                $totalTasks = $proj->tasks->count();
                                $doneTasks = $proj->tasks->where('status', 'done')->count();
                                $pct = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;
                            @endphp
                            <div class="p-3 rounded-lg border border-slate-200/90 hover:border-slate-300 bg-slate-50/30 transition">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                                    <div>
                                        <a href="{{ route('projects.show', $proj) }}" class="text-xs font-bold text-slate-900 hover:text-blue-600 hover:underline flex items-center gap-1.5">
                                            <span>{{ $proj->name }}</span>
                                            <span class="text-[10.5px] font-normal text-slate-500">({{ $proj->client->name ?? 'Klien' }})</span>
                                        </a>
                                        <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-2">
                                            <span>Kategori: <strong>{{ $proj->category->name ?? 'Umum' }}</strong></span>
                                            <span>·</span>
                                            <span>Tenggat: <strong>{{ $proj->due_date ? \Carbon\Carbon::parse($proj->due_date)->format('d M Y') : '-' }}</strong></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        @if($proj->status === 'completed')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">Selesai</span>
                                        @elseif($proj->status === 'review')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-800 border border-purple-200">Review</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-800 border border-blue-200">In Progress</span>
                                        @endif
                                        <a href="{{ route('projects.show', $proj) }}" class="button secondary small !py-1 !px-2.5 !text-[11px]">
                                            Detail Proyek
                                        </a>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="flex items-center justify-between text-[10.5px] text-slate-500 font-medium mb-1">
                                        <span>Progress Tugas: {{ $doneTasks }}/{{ $totalTasks }} Selesai</span>
                                        <span class="font-bold text-slate-700">{{ $pct }}%</span>
                                    </div>
                                    <div class="w-full h-1.5 rounded-full bg-slate-200 overflow-hidden">
                                        <div class="h-full bg-blue-600 rounded-full transition-all duration-300" style="width: {{ $pct }}%;"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center text-slate-500 bg-slate-50/50 rounded-lg border border-dashed border-slate-200">
                        <x-heroicon-o-folder class="w-8 h-8 mx-auto mb-2 text-slate-400" />
                        <p class="text-xs font-medium">Belum ada proyek yang ditugaskan kepada Anda.</p>
                    </div>
                @endif
            </section>
        </div>

        <!-- Right Column: Time Tracking & Threats (Col 5) -->
        <div class="lg:col-span-5 flex flex-col gap-5">
            <!-- Panel 1: Always-on-Top Work Time Tracker Control -->
            <section class="panel !p-4 !mb-0 border border-slate-200 rounded-xl bg-white shadow-sm">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="!text-sm !font-bold text-slate-900 !mb-0.5 flex items-center gap-2">
                            <x-heroicon-o-clock class="w-4 h-4 text-emerald-600" />
                            <span>Always-on-Top Time Tracker</span>
                        </h2>
                        <p class="text-[11px] text-slate-500">Lacak durasi kerja per tugas agar fokus dan akuntabel.</p>
                    </div>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        Staff Tool
                    </span>
                </div>

                @if($activeTimeLog)
                    <div class="p-3.5 rounded-xl bg-emerald-50/60 border border-emerald-200 mb-3">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-emerald-800">
                                <span class="w-2 h-2 rounded-full bg-emerald-600 animate-ping"></span>
                                Sesi Waktu Sedang Berjalan
                            </span>
                            <span class="text-[11px] font-mono font-bold text-emerald-800">
                                Dimulai {{ \Carbon\Carbon::parse($activeTimeLog->started_at)->format('H:i') }}
                            </span>
                        </div>
                        <div class="text-xs font-bold text-slate-900 truncate">{{ $activeTimeLog->task->title ?? 'Tugas Aktif' }}</div>
                        <div class="text-[11px] text-slate-600 mt-0.5 truncate">{{ $activeTimeLog->task->project->name ?? 'Proyek' }} · {{ $activeTimeLog->task->project->client->name ?? 'Klien' }}</div>

                        <div class="mt-3 flex items-center gap-2">
                            <button
                                type="button"
                                onclick="window.KonsulinTimer && window.KonsulinTimer.togglePiP()"
                                class="button secondary small !py-1 !px-2.5 !text-[11px] flex-1 flex items-center justify-center gap-1 cursor-pointer"
                            >
                                <x-heroicon-o-arrows-pointing-out class="w-3.5 h-3.5" />
                                <span>Always-on-Top</span>
                            </button>
                            <button
                                type="button"
                                onclick="window.KonsulinTimer && window.KonsulinTimer.openStopModal()"
                                class="button small !py-1 !px-2.5 !text-[11px] !bg-rose-700 hover:!bg-rose-800 flex items-center justify-center gap-1 text-white cursor-pointer"
                            >
                                <x-heroicon-s-stop class="w-3 h-3" />
                                <span>Selesai & Simpan</span>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 mb-3 text-center">
                        <p class="text-xs text-slate-600 font-medium">Tidak ada sesi waktu yang sedang berjalan.</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Pilih tugas yang ingin Anda kerjakan untuk memulai stopwatch.</p>
                        <button
                            type="button"
                            onclick="window.KonsulinTimer && window.KonsulinTimer.openTaskPickerModal()"
                            class="button small !py-1.5 !px-3 !text-xs !bg-[#0b192c] text-white mt-2.5 inline-flex items-center gap-1.5 cursor-pointer"
                        >
                            <x-heroicon-o-play class="w-3.5 h-3.5 text-emerald-400" />
                            <span>Mulai Sesi Waktu Kerja</span>
                        </button>
                    </div>
                @endif

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Aplikasi Desktop:</span>
                    <button
                        type="button"
                        onclick="window.KonsulinTimer && window.KonsulinTimer.openDesktopAppLink()"
                        class="text-blue-600 hover:underline font-semibold flex items-center gap-1 cursor-pointer"
                    >
                        <x-heroicon-o-computer-desktop class="w-3.5 h-3.5" />
                        <span>Buka Aplikasi Mac / Windows</span>
                    </button>
                </div>
            </section>

            <!-- Panel 2: Riwayat Pencatatan Waktu Terbaru -->
            <section class="panel !p-4 !mb-0 border border-slate-200 rounded-xl bg-white shadow-sm">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="!text-sm !font-bold text-slate-900 !mb-0.5 flex items-center gap-2">
                            <x-heroicon-o-list-bullet class="w-4 h-4 text-indigo-600" />
                            <span>Pencatatan Waktu Terbaru</span>
                        </h2>
                        <p class="text-[11px] text-slate-500">Log pengerjaan tugas terakhir yang telah Anda selesaikan.</p>
                    </div>
                </div>

                @if($recentTimeLogs->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($recentTimeLogs as $log)
                            <div class="p-2.5 rounded-lg border border-slate-200/70 bg-white flex items-center justify-between gap-2">
                                <div class="overflow-hidden">
                                    <div class="text-xs font-semibold text-slate-900 truncate">
                                        {{ $log->task->title ?? 'Tugas' }}
                                    </div>
                                    <div class="text-[10.5px] text-slate-500 truncate mt-0.5">
                                        {{ \Carbon\Carbon::parse($log->started_at)->format('d M, H:i') }}
                                        @if($log->stopped_at)
                                            - {{ \Carbon\Carbon::parse($log->stopped_at)->format('H:i') }}
                                        @endif
                                        · {{ $log->task->project->name ?? 'Proyek' }}
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[11px] font-bold font-mono bg-indigo-50 text-indigo-800 border border-indigo-200 shrink-0">
                                    {{ $log->duration_minutes ?? 0 }}m
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 text-center text-slate-500 bg-slate-50/50 rounded-lg border border-dashed border-slate-200">
                        <p class="text-xs">Belum ada riwayat pengerjaan waktu yang tercatat.</p>
                    </div>
                @endif
            </section>

            <!-- Panel 3: Radar Kendala & Threats Proyek Saya -->
            <section class="panel !p-4 !mb-0 border border-slate-200 rounded-xl bg-white shadow-sm">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="!text-sm !font-bold text-slate-900 !mb-0.5 flex items-center gap-2">
                            <x-heroicon-o-shield-exclamation class="w-4 h-4 text-rose-600" />
                            <span>Kendala & Threats Proyek</span>
                        </h2>
                        <p class="text-[11px] text-slate-500">Isu operasional yang perlu diwaspadai pada proyek Anda.</p>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $myThreats->count() > 0 ? 'bg-rose-100 text-rose-800' : 'bg-emerald-50 text-emerald-700' }}">
                        {{ $myThreats->count() }} Terbuka
                    </span>
                </div>

                @if($myThreats->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($myThreats as $threat)
                            <div class="p-2.5 rounded-lg border border-rose-200 bg-rose-50/30">
                                <div class="flex items-center justify-between gap-1 mb-1">
                                    <span class="text-xs font-bold text-slate-900 truncate">{{ $threat->title }}</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase {{ $threat->severity === 'critical' ? 'bg-rose-600 text-white' : ($threat->severity === 'high' ? 'bg-amber-600 text-white' : 'bg-slate-200 text-slate-800') }}">
                                        {{ $threat->severity }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-600 line-clamp-2">{{ $threat->description }}</div>
                                <div class="text-[10px] text-slate-400 mt-1.5 pt-1 border-t border-rose-100 flex items-center justify-between">
                                    <span>Proyek: <strong>{{ $threat->project->name ?? 'Proyek' }}</strong></span>
                                    <span>Oleh: {{ $threat->user->name ?? 'Tim' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 text-center text-slate-500 bg-emerald-50/30 rounded-lg border border-dashed border-emerald-200">
                        <x-heroicon-o-check-circle class="w-6 h-6 mx-auto mb-1 text-emerald-600" />
                        <p class="text-xs font-semibold text-emerald-800">Tidak ada kendala aktif pada proyek Anda.</p>
                        <p class="text-[10.5px] text-emerald-700 mt-0.5">Semua proses operasional berjalan lancar.</p>
                    </div>
                @endif
            </section>
        </div>
    </div>
@else
    <!-- Topbar Header -->
    <div class="topbar !mb-4 !pb-3">
        <div>
            <div class="flex items-center gap-2">
                <h1>Dashboard</h1>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    Operasional &amp; Kepatuhan
                </span>
            </div>
            <p class="muted !text-xs mt-0.5">Monitoring performa proyek, kepatuhan pajak bulanan, beban tim PIC, dan mitigasi risiko.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('kanban.index') }}" class="button small">
                <x-heroicon-o-view-columns class="w-3.5 h-3.5" />
                <span>Kanban Board</span>
            </a>
            <a href="{{ route('clients.index') }}" class="button secondary small">
                <x-heroicon-o-building-office-2 class="w-3.5 h-3.5" />
                <span>Klien & Pajak</span>
            </a>
            <a href="{{ route('projects.index') }}" class="button secondary small">
                <x-heroicon-o-folder class="w-3.5 h-3.5" />
                <span>Semua Proyek</span>
            </a>
        </div>
    </div>

    <!-- Compact Stat Strip (1 Single Row) -->
    <div class="overflow-x-auto pb-1 mb-5 -mx-1 px-1">
        <section class="stats-row-6 grid grid-cols-6 gap-2.5 min-w-[680px] lg:min-w-0" data-animate-children>
            <!-- Total projects -->
            <div class="stat !p-3 !rounded-xl border border-slate-200/90 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-1 mb-1">
                    <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider truncate">Total projects</span>
                    <div class="w-5 h-5 rounded bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                        <x-heroicon-o-folder class="w-3 h-3" />
                    </div>
                </div>
                <strong class="text-lg font-bold text-slate-900 block leading-tight">{{ $totalProjects }}</strong>
                <span class="text-[10.5px] text-slate-500 font-medium truncate block mt-0.5">{{ $completedProjects }} Selesai</span>
            </div>

            <!-- Active projects -->
            <div class="stat !p-3 !rounded-xl border border-slate-200/90 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-1 mb-1">
                    <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider truncate">Active projects</span>
                    <div class="w-5 h-5 rounded bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                        <x-heroicon-o-arrow-path class="w-3 h-3" />
                    </div>
                </div>
                <strong class="text-lg font-bold text-blue-700 block leading-tight">{{ $activeProjects }}</strong>
                <span class="text-[10.5px] text-blue-600 font-medium truncate block mt-0.5">Sedang berjalan</span>
            </div>

            <!-- Tenggat Kritis -->
            <div class="stat !p-3 !rounded-xl border border-slate-200/90 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-1 mb-1">
                    <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider truncate">Tenggat &lt; 7 Hari</span>
                    <div class="w-5 h-5 rounded {{ $nearDeadlineCount > 0 ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center shrink-0">
                        <x-heroicon-o-clock class="w-3 h-3" />
                    </div>
                </div>
                <strong class="text-lg font-bold {{ $nearDeadlineCount > 0 ? 'text-amber-700' : 'text-slate-800' }} block leading-tight">{{ $nearDeadlineCount }}</strong>
                <span class="text-[10.5px] {{ $nearDeadlineCount > 0 ? 'text-amber-700 font-semibold' : 'text-slate-500 font-medium' }} truncate block mt-0.5">
                    {{ $nearDeadlineCount > 0 ? 'Mendekati deadline' : 'Jadwal aman' }}
                </span>
            </div>

            <!-- Open threats -->
            <div class="stat !p-3 !rounded-xl border border-slate-200/90 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-1 mb-1">
                    <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider truncate">Open threats</span>
                    <div class="w-5 h-5 rounded {{ $openThreats > 0 ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center shrink-0">
                        <x-heroicon-o-shield-exclamation class="w-3 h-3" />
                    </div>
                </div>
                <strong class="text-lg font-bold {{ $openThreats > 0 ? 'text-rose-600' : 'text-slate-800' }} block leading-tight">{{ $openThreats }}</strong>
                <span class="text-[10.5px] {{ $openThreats > 0 ? 'text-rose-600 font-semibold' : 'text-slate-500 font-medium' }} truncate block mt-0.5">
                    {{ $openThreats > 0 ? 'Butuh mitigasi' : 'Kondisi aman' }}
                </span>
            </div>

            <!-- Review / Approval -->
            <div class="stat !p-3 !rounded-xl border border-slate-200/90 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-1 mb-1">
                    <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider truncate">Review Pending</span>
                    <div class="w-5 h-5 rounded bg-purple-50 text-purple-700 flex items-center justify-center shrink-0">
                        <x-heroicon-o-clipboard-document-check class="w-3 h-3" />
                    </div>
                </div>
                <strong class="text-lg font-bold text-purple-700 block leading-tight">{{ $pendingReviewCount }}</strong>
                <span class="text-[10.5px] text-purple-600 font-medium truncate block mt-0.5">Persetujuan partner</span>
            </div>

            <!-- Clients -->
            <div class="stat !p-3 !rounded-xl border border-slate-200/90 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-1 mb-1">
                    <span class="text-[10.5px] font-bold text-slate-500 uppercase tracking-wider truncate">Clients</span>
                    <div class="w-5 h-5 rounded bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                        <x-heroicon-o-building-office-2 class="w-3 h-3" />
                    </div>
                </div>
                <strong class="text-lg font-bold text-slate-900 block leading-tight">{{ $totalClients }}</strong>
                <span class="text-[10.5px] text-emerald-700 font-semibold truncate block mt-0.5">{{ $activeClientsCount ?? $totalClients }} Aktif • {{ $nonactiveClientsCount ?? 0 }} Nonaktif</span>
            </div>
        </section>
    </div>

    <!-- Client Portfolio Analytics Grid (4 Charts from User Screenshot) -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
            <div>
                <h2 class="!text-sm !font-bold text-slate-900 !mb-0.5 flex items-center gap-2">
                    <x-heroicon-o-chart-bar class="w-4 h-4 text-blue-600" />
                    <span>Distribusi Portofolio & Penugasan Klien</span>
                </h2>
                <p class="text-[11px] text-slate-500">Pemetaan durasi kontrak, alokasi PIC Tax, status data migrasi, dan PIC Accounting.</p>
            </div>
            <div class="flex items-center gap-1.5 flex-wrap">
                <span class="text-[11px] font-semibold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-md border border-slate-200">
                    Total {{ $totalClients }} Klien
                </span>
                <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                    {{ $activeClientsCount ?? $totalClients }} Aktif
                </span>
                <span class="text-[11px] font-semibold text-slate-600 bg-slate-100 px-2 py-1 rounded-md border border-slate-200">
                    {{ $pkpClientsCount ?? $totalClients }} PKP
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- 1. Jumlah Kontrak Klien -->
            <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-base sm:text-lg font-semibold text-slate-700 mb-3 tracking-tight">Jumlah Kontrak Klien</h3>
                <div class="chart-wrap" style="height: 220px; min-height: 220px; position: relative;">
                    <canvas id="contractDurationsChart"></canvas>
                </div>
            </div>

            <!-- 2. PIC Tax -->
            <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-base sm:text-lg font-semibold text-slate-700 mb-3 tracking-tight">PIC Tax</h3>
                <div class="chart-wrap" style="height: 220px; min-height: 220px; position: relative;">
                    <canvas id="taxPicChart"></canvas>
                </div>
            </div>

            <!-- 3. Data Migration -->
            <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-base sm:text-lg font-semibold text-slate-700 mb-3 tracking-tight">Data Migration</h3>
                <div class="chart-wrap" style="height: 220px; min-height: 220px; position: relative;">
                    <canvas id="dataMigrationChart"></canvas>
                </div>
            </div>

            <!-- 4. PIC Accounting -->
            <div class="bg-white p-4 sm:p-5 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-base sm:text-lg font-semibold text-slate-700 mb-3 tracking-tight">PIC Accounting</h3>
                <div class="chart-wrap" style="height: 220px; min-height: 220px; position: relative;">
                    <canvas id="accountingPicChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Dual-Column Layout (7 : 5) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
        
        <!-- Kolom Kiri: Operasional Proyek & Matriks Pajak (Col-span 7) -->
        <div class="lg:col-span-7 flex flex-col gap-5">
            
            @if(isset($pendingReviewTasks) && $pendingReviewTasks->isNotEmpty())
                <!-- Panel 0: Quality Gate & Tugas Menunggu Review -->
                <section class="panel !p-4 !mb-0 border border-indigo-200 rounded-xl bg-indigo-50/30 shadow-sm" data-animate-children>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3 pb-2 border-b border-indigo-100">
                        <div>
                            <h2 class="!text-sm !font-bold text-slate-900 !mb-0.5 flex items-center gap-2">
                                <x-heroicon-o-shield-check class="w-4 h-4 text-indigo-600" />
                                <span>Quality Gate: Tugas Menunggu Review</span>
                            </h2>
                            <p class="text-[11px] text-slate-500">Tugas staf yang diajukan untuk verifikasi kertas kerja dan persetujuan output.</p>
                        </div>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 border border-indigo-200">
                            {{ $pendingReviewTasks->count() }} Menunggu QC
                        </span>
                    </div>

                    <div class="space-y-2">
                        @foreach($pendingReviewTasks as $pTask)
                            <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-xs flex items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-1.5 text-[11px] text-slate-500 mb-0.5">
                                        <span class="font-mono font-bold text-indigo-700">TSK-{{ $pTask->id }}</span>
                                        <span>·</span>
                                        <span>{{ $pTask->project->name ?? 'Proyek' }}</span>
                                        <span>·</span>
                                        <span>{{ $pTask->project->client->name ?? 'Klien' }}</span>
                                    </div>
                                    <div class="font-semibold text-xs text-slate-900 line-clamp-1">{{ $pTask->title }}</div>
                                    <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-2">
                                        <span>Staf: <strong class="text-slate-700">{{ $pTask->assignee?->name ?? 'Unassigned' }}</strong></span>
                                        <span>·</span>
                                        <span class="font-mono font-semibold text-indigo-700">{{ $pTask->checklists->where('is_checked', true)->count() }}/{{ $pTask->checklists->count() }} QC Terverifikasi</span>
                                    </div>
                                </div>
                                <a href="{{ route('projects.show', $pTask->project_id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white transition shrink-0">
                                    <span>QC Output</span>
                                    <x-heroicon-o-arrow-right class="w-3 h-3" />
                                </a>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Panel 1: Proyek Prioritas & Tenggat Terdekat -->
            <section class="panel !p-4 !mb-0 border border-slate-200 rounded-xl bg-white shadow-sm" data-animate-children>
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="!text-sm !font-bold text-slate-900 !mb-0.5 flex items-center gap-2">
                            <x-heroicon-o-calendar class="w-4 h-4 text-blue-600" />
                            <span>Tenggat Proyek Terdekat</span>
                        </h2>
                        <p class="text-[11px] text-slate-500">Proyek aktif yang diurutkan berdasarkan tenggat waktu (due date).</p>
                    </div>
                    <a href="{{ route('projects.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        <span>Lihat Semua</span>
                        <x-heroicon-o-arrow-right class="w-3 h-3" />
                    </a>
                </div>

                @if($upcomingProjects->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 bg-slate-50/75 text-slate-500 font-semibold uppercase text-[10.5px]">
                                    <th class="py-2 px-2.5">Proyek & Klien</th>
                                    <th class="py-2 px-2.5">Tenggat</th>
                                    <th class="py-2 px-2.5">PIC Tim</th>
                                    <th class="py-2 px-2.5">Progress</th>
                                    <th class="py-2 px-2.5">Status</th>
                                    <th class="py-2 px-2 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($upcomingProjects as $project)
                                    @php
                                        $now = \Carbon\Carbon::now();
                                        $dueDate = $project->due_date;
                                        $isOverdue = $dueDate && $dueDate->lt($now->startOfDay());
                                        $isDueSoon = $dueDate && !$isOverdue && $dueDate->lte($now->copy()->addDays(7));
                                        $progress = $project->progressPercent();
                                    @endphp
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="py-2.5 px-2.5">
                                            <a href="{{ route('projects.show', $project) }}" class="font-semibold text-slate-900 hover:text-blue-600 block line-clamp-1">
                                                {{ $project->name }}
                                            </a>
                                            <span class="text-[11px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                                                <span>{{ $project->client?->name ?? 'Tanpa Klien' }}</span>
                                                @if($project->category)
                                                    <span class="text-slate-300">•</span>
                                                    <span class="text-[10px] px-1.5 py-0.2 bg-slate-100 text-slate-600 rounded">{{ $project->category->name }}</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-2.5 whitespace-nowrap">
                                            @if($dueDate)
                                                @if($isOverdue)
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10.5px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                        <x-heroicon-s-exclamation-circle class="w-3 h-3 text-rose-600" />
                                                        <span>Terlewat</span>
                                                    </span>
                                                @elseif($isDueSoon)
                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10.5px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                                        <x-heroicon-o-clock class="w-3 h-3 text-amber-600" />
                                                        <span>{{ $dueDate->diffForHumans(['parts' => 1]) }}</span>
                                                    </span>
                                                @else
                                                    <span class="text-slate-600 font-medium">{{ $dueDate->format('d M Y') }}</span>
                                                @endif
                                            @else
                                                <span class="text-slate-400">Tidak ada</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-2.5">
                                            <div class="flex flex-col gap-0.5 text-[11px]">
                                                @php
                                                    $accPic = $project->accountingStaff->first()?->name ?? $project->client?->accounting_pic;
                                                    $taxPic = $project->taxStaff->first()?->name ?? $project->client?->tax_pic;
                                                @endphp
                                                @if($accPic)
                                                    <div class="flex items-center gap-1 text-slate-700">
                                                        <span class="px-1 py-0.2 bg-blue-100 text-blue-900 rounded text-[9.5px] font-bold">ACC</span>
                                                        <span class="truncate max-w-[90px]" title="{{ $accPic }}">{{ $accPic }}</span>
                                                    </div>
                                                @endif
                                                @if($taxPic)
                                                    <div class="flex items-center gap-1 text-slate-700">
                                                        <span class="px-1 py-0.2 bg-emerald-100 text-emerald-900 rounded text-[9.5px] font-bold">TAX</span>
                                                        <span class="truncate max-w-[90px]" title="{{ $taxPic }}">{{ $taxPic }}</span>
                                                    </div>
                                                @endif
                                                @if(!$accPic && !$taxPic)
                                                    <span class="text-slate-400 text-[10.5px]">Belum ditugaskan</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-2.5">
                                            <div class="w-20">
                                                <div class="flex justify-between items-center text-[10px] font-semibold text-slate-600 mb-0.5">
                                                    <span>{{ $progress }}%</span>
                                                </div>
                                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $progress }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-2.5 px-2.5 whitespace-nowrap">
                                            @php
                                                $statusColors = [
                                                    'todo' => 'bg-slate-100 text-slate-800 border-slate-200',
                                                    'in_progress' => 'bg-blue-50 text-blue-800 border-blue-200',
                                                    'waiting_client' => 'bg-amber-50 text-amber-800 border-amber-200',
                                                    'review' => 'bg-purple-50 text-purple-800 border-purple-200',
                                                    'completed' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                                ];
                                                $statusLabels = [
                                                    'todo' => 'To Do',
                                                    'in_progress' => 'In Progress',
                                                    'waiting_client' => 'Waiting Client',
                                                    'review' => 'In Review',
                                                    'completed' => 'Done',
                                                ];
                                            @endphp
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10.5px] font-semibold border {{ $statusColors[$project->status] ?? 'bg-slate-100 text-slate-800 border-slate-200' }}">
                                                {{ $statusLabels[$project->status] ?? ucfirst(str_replace('_', ' ', $project->status)) }}
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-2 text-right">
                                            <a href="{{ route('projects.show', $project) }}" class="button secondary icon-only small !w-7 !h-7 !min-h-0" title="Buka Detail Proyek">
                                                <x-heroicon-o-chevron-right class="w-3.5 h-3.5" />
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-6 text-center text-slate-500 text-xs">
                        <x-heroicon-o-check-circle class="w-6 h-6 text-emerald-500 mx-auto mb-1.5" />
                        <p class="font-medium text-slate-700">Tidak ada proyek aktif dengan tenggat mendesak.</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Semua proyek telah diselesaikan atau dijadwalkan dengan aman.</p>
                    </div>
                @endif
            </section>

            <!-- Panel 2: Matriks Kepatuhan SPT & Pajak Bulanan -->
            <section class="panel !p-4 !mb-0 border border-slate-200 rounded-xl bg-white shadow-sm" data-animate-children>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="!text-sm !font-bold text-slate-900 !mb-0.5 flex items-center gap-2">
                            <x-heroicon-o-document-chart-bar class="w-4 h-4 text-emerald-600" />
                            <span>Matriks Kepatuhan Pajak (SPT Masa)</span>
                        </h2>
                        <p class="text-[11px] text-slate-500">Status pelaporan pajak per klien untuk periode berjalan (Mastersheet Sync).</p>
                    </div>

                    <!-- Period Selector Pill Group -->
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-[11px] font-semibold text-slate-500 mr-0.5">Periode:</span>
                        <div class="inline-flex bg-slate-100 p-0.5 rounded-lg border border-slate-200 overflow-x-auto max-w-[280px] sm:max-w-none">
                            @foreach($compliancePeriods as $p)
                                <a href="{{ route('dashboard', ['period' => $p]) }}"
                                   class="px-2 py-0.5 rounded-md text-[11px] font-semibold whitespace-nowrap transition-colors {{ $selectedPeriod === $p ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                                    {{ $p }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Period Summary Stats Strip & Quick Filter -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3 bg-slate-50/80 p-2.5 rounded-lg border border-slate-100">
                    <div class="flex items-center gap-2 flex-wrap text-xs">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-white border border-slate-200 text-slate-700 shadow-2xs">
                            <span class="text-slate-400">Total Klien:</span>
                            <span class="font-bold text-slate-900">{{ $periodCompliances->count() }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 border border-emerald-200 text-emerald-800 shadow-2xs">
                            <span>✓ Lapor / Done:</span>
                            <span class="font-bold">{{ $periodDoneCount }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 border border-blue-200 text-blue-800 shadow-2xs">
                            <span>Rilis LK:</span>
                            <span class="font-bold">{{ $periodLkCount }}</span>
                        </span>
                        @if($periodPendingNotes->count() > 0)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 border border-amber-200 text-amber-800 shadow-2xs">
                                <span>Pending Notes:</span>
                                <span class="font-bold">{{ $periodPendingNotes->count() }}</span>
                            </span>
                        @endif
                    </div>
                    <div class="relative min-w-[180px]">
                        <input type="text"
                               id="complianceSearchInput"
                               placeholder="Cari nama atau kode klien..."
                               class="w-full text-xs py-1 pl-2.5 pr-7 rounded-md border border-slate-200 bg-white focus:outline-none focus:ring-1 focus:ring-blue-600"
                               onkeyup="filterComplianceTable(this.value)">
                        <x-heroicon-o-magnifying-glass class="w-3.5 h-3.5 text-slate-400 absolute right-2 top-2 pointer-events-none" />
                    </div>
                </div>

                @if($periodCompliances->isNotEmpty())
                    <div class="overflow-x-auto max-h-[460px] overflow-y-auto border border-slate-200/80 rounded-lg">
                        <table id="complianceTable" class="w-full text-left border-collapse text-xs">
                            <thead class="sticky top-0 bg-slate-50/95 backdrop-blur-xs z-10">
                                <tr class="border-b border-slate-200 text-slate-500 font-semibold uppercase text-[10px]">
                                    <th class="py-2 px-2.5">Klien</th>
                                    <th class="py-2 px-2 text-center">PPh 21</th>
                                    <th class="py-2 px-2 text-center">PPh Unifikasi</th>
                                    <th class="py-2 px-2 text-center">PPN</th>
                                    <th class="py-2 px-2 text-center">PP 55</th>
                                    <th class="py-2 px-2 text-center">PPh 25</th>
                                    <th class="py-2 px-2 text-center">LK</th>
                                    <th class="py-2 px-2.5">Catatan / Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($periodCompliances as $comp)
                                    @php
                                        $badgeHelper = function($val) {
                                            $v = trim((string)$val);
                                            if (in_array(strtolower($v), ['done', 'final', 'selesai', 'lapor', 'reported'])) {
                                                return '<span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">✓ Done</span>';
                                            }
                                            if (in_array(strtolower($v), ['pending', 'draft', 'proses', 'in progress'])) {
                                                return '<span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">Pending</span>';
                                            }
                                            if (in_array(strtolower($v), ['n/a', '-', 'na', 'none'])) {
                                                return '<span class="inline-flex items-center px-1 py-0.2 rounded text-[10px] font-medium bg-slate-100 text-slate-500">N/A</span>';
                                            }
                                            if (!empty($v)) {
                                                return '<span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-blue-50 text-blue-800 border border-blue-200">' . e($v) . '</span>';
                                            }
                                            return '<span class="text-slate-300 text-[10px]">-</span>';
                                        };
                                        $searchKeywords = strtolower(($comp->client?->name ?? '') . ' ' . ($comp->client?->client_code ?? '') . ' ' . $comp->notes);
                                    @endphp
                                    <tr class="hover:bg-slate-50/60 transition-colors compliance-row" data-search="{{ $searchKeywords }}">
                                        <td class="py-2.5 px-2.5 whitespace-nowrap">
                                            <a href="{{ route('clients.show', $comp->client_id) }}" class="font-semibold text-slate-900 hover:text-blue-600 block line-clamp-1">
                                                {{ $comp->client?->name }}
                                            </a>
                                            <span class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5">
                                                <span>{{ $comp->client?->client_code ?? 'ID: ' . $comp->client_id }}</span>
                                                @if($comp->client?->tax_status)
                                                    <span class="text-slate-300">•</span>
                                                    <span class="font-medium text-slate-600">{{ $comp->client->tax_status }}</span>
                                                @endif
                                                @if($comp->client?->client_type)
                                                    <span class="text-slate-300">•</span>
                                                    <span>{{ $comp->client->client_type }}</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-2 text-center whitespace-nowrap">{!! $badgeHelper($comp->pph_21) !!}</td>
                                        <td class="py-2.5 px-2 text-center whitespace-nowrap">{!! $badgeHelper($comp->pph_unifikasi) !!}</td>
                                        <td class="py-2.5 px-2 text-center whitespace-nowrap">{!! $badgeHelper($comp->ppn) !!}</td>
                                        <td class="py-2.5 px-2 text-center whitespace-nowrap">{!! $badgeHelper($comp->pp_55) !!}</td>
                                        <td class="py-2.5 px-2 text-center whitespace-nowrap">{!! $badgeHelper($comp->pph_25) !!}</td>
                                        <td class="py-2.5 px-2 text-center whitespace-nowrap">{!! $badgeHelper($comp->lk) !!}</td>
                                        <td class="py-2.5 px-2.5 text-[11px] text-slate-600 max-w-[180px] truncate" title="{{ $comp->notes }}">
                                            @if($comp->notes && !in_array(strtolower(trim($comp->notes)), ['-', 'ok', 'done', 'tepat waktu']))
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-900 border border-amber-200">
                                                    {{ $comp->notes }}
                                                </span>
                                            @else
                                                <span class="text-slate-400">{{ $comp->notes ?: 'Tepat waktu' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-6 text-center text-slate-500 text-xs">
                        <x-heroicon-o-document-text class="w-6 h-6 text-slate-400 mx-auto mb-1.5" />
                        <p class="font-medium text-slate-700">Belum ada data kepatuhan pajak untuk periode {{ $selectedPeriod }}.</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Kelola kepatuhan klien melalui menu Klien & Pajak.</p>
                    </div>
                @endif
            </section>
        </div>

        <!-- Kolom Kanan: Radar Risiko, Beban PIC & Analitik Ringkas (Col-span 5) -->
        <div class="lg:col-span-5 flex flex-col gap-5">
            
            <!-- Panel 3: Radar Risiko & Threats Aktif -->
            <section class="panel !p-4 !mb-0 border border-slate-200 rounded-xl bg-white shadow-sm" data-animate-children>
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="!text-sm !font-bold text-slate-900 !mb-0.5 flex items-center gap-2">
                            <x-heroicon-o-shield-exclamation class="w-4 h-4 {{ $openThreats > 0 ? 'text-rose-600' : 'text-slate-500' }}" />
                            <span>Radar Risiko & Threats Aktif</span>
                        </h2>
                        <p class="text-[11px] text-slate-500">Hambatan operasional yang memerlukan tindakan mitigasi.</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-xs font-bold {{ $openThreats > 0 ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700' }}">
                        {{ $openThreats }} Terbuka
                    </span>
                </div>

                @if($activeThreatsList->isNotEmpty())
                    <div class="divide-y divide-slate-100">
                        @foreach($activeThreatsList as $threat)
                            @php
                                $sevColors = [
                                    'critical' => 'bg-rose-100 text-rose-800 border-rose-200',
                                    'high' => 'bg-red-50 text-red-700 border-red-200',
                                    'medium' => 'bg-amber-50 text-amber-800 border-amber-200',
                                    'low' => 'bg-slate-100 text-slate-700 border-slate-200',
                                ];
                            @endphp
                            <div class="py-2.5 first:pt-0 last:pb-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold border uppercase {{ $sevColors[$threat->severity] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                                {{ $threat->severity }}
                                            </span>
                                            <a href="{{ route('projects.show', $threat->project_id) }}" class="text-xs font-semibold text-slate-900 hover:text-blue-600 truncate">
                                                {{ $threat->title }}
                                            </a>
                                        </div>
                                        <p class="text-[11px] text-slate-500 line-clamp-1 mb-1">
                                            Proyek: <span class="font-medium text-slate-700">{{ $threat->project?->name }}</span>
                                            @if($threat->project?->client)
                                                ({{ $threat->project->client->name }})
                                            @endif
                                        </p>
                                        @if($threat->mitigation_plan)
                                            <div class="text-[10.5px] bg-slate-50 p-1.5 rounded text-slate-600 border border-slate-100 flex items-start gap-1">
                                                <span class="font-semibold text-slate-700 shrink-0">Mitigasi:</span>
                                                <span class="line-clamp-1">{{ $threat->mitigation_plan }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <a href="{{ route('projects.show', $threat->project_id) }}" class="button secondary icon-only small !w-7 !h-7 !min-h-0 shrink-0 mt-0.5" title="Buka Proyek">
                                        <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" />
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-6 text-center text-slate-500 text-xs">
                        <x-heroicon-o-shield-check class="w-7 h-7 text-emerald-500 mx-auto mb-1.5" />
                        <p class="font-semibold text-slate-800">Tidak ada risiko atau blocker aktif</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Semua proses operasional klien berjalan tanpa kendala kritis.</p>
                    </div>
                @endif
            </section>

            <!-- Panel 3b: Catatan Operasional & Follow-up Periode Berjalan -->
            @if(isset($periodPendingNotes) && $periodPendingNotes->isNotEmpty())
                <section class="panel !p-4 !mb-0 border border-amber-200/90 rounded-xl bg-amber-50/30 shadow-xs" data-animate-children>
                    <div class="flex items-center justify-between mb-2.5 pb-2 border-b border-amber-200/60">
                        <div>
                            <h2 class="!text-sm !font-bold text-amber-950 !mb-0.5 flex items-center gap-1.5">
                                <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-amber-600" />
                                <span>Catatan Operasional ({{ $selectedPeriod }})</span>
                            </h2>
                            <p class="text-[11px] text-amber-800/80">Kepatuhan dan follow-up yang memerlukan aksi tim.</p>
                        </div>
                        <span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-900 border border-amber-200">
                            {{ $periodPendingNotes->count() }} Isu
                        </span>
                    </div>

                    <div class="divide-y divide-amber-200/40 max-h-56 overflow-y-auto pr-1">
                        @foreach($periodPendingNotes as $pNote)
                            <div class="py-2 first:pt-0 last:pb-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('clients.show', $pNote->client_id) }}" class="text-xs font-bold text-slate-900 hover:text-blue-700 truncate">
                                                {{ $pNote->client?->name }}
                                            </a>
                                            @if($pNote->client?->tax_status)
                                                <span class="text-[9.5px] px-1 py-0.2 bg-slate-100 text-slate-600 rounded">{{ $pNote->client->tax_status }}</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-amber-950 font-medium mt-0.5 leading-snug">
                                            {{ $pNote->notes }}
                                        </p>
                                    </div>
                                    <a href="{{ route('clients.show', $pNote->client_id) }}" class="text-[10.5px] text-amber-900 hover:text-amber-950 font-semibold underline shrink-0 mt-0.5">
                                        Buka Klien
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Panel 4: Beban Kerja Tim PIC (Workload) -->
            <section class="panel !p-4 !mb-0 border border-slate-200 rounded-xl bg-white shadow-sm" data-animate-children>
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="!text-sm !font-bold text-slate-900 !mb-0.5 flex items-center gap-2">
                            <x-heroicon-o-user-group class="w-4 h-4 text-blue-600" />
                            <span>Beban Kerja Tim PIC</span>
                        </h2>
                        <p class="text-[11px] text-slate-500">Distribusi proyek aktif pada konsultan accounting & tax.</p>
                    </div>
                    <a href="{{ route('staff.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        <span>Kelola Tim</span>
                        <x-heroicon-o-arrow-right class="w-3 h-3" />
                    </a>
                </div>

                <div class="space-y-2.5">
                    @forelse($staffWorkloads as $staff)
                        @php
                            $maxLoad = max($staffWorkloads->max('projects_count'), 1);
                            $loadPercent = min(100, (int) round(($staff->projects_count / $maxLoad) * 100));
                            $typeBadge = [
                                'accounting' => 'bg-blue-100 text-blue-900',
                                'tax' => 'bg-emerald-100 text-emerald-900',
                                'legal' => 'bg-purple-100 text-purple-900',
                            ][$staff->type] ?? 'bg-slate-100 text-slate-800';
                        @endphp
                        <div class="p-2 rounded-lg bg-slate-50/70 border border-slate-100">
                            <div class="flex items-center justify-between mb-1">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-bold">
                                        {{ substr($staff->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                            <span>{{ $staff->name }}</span>
                                            <span class="px-1 py-0.2 rounded text-[9.5px] font-bold uppercase {{ $typeBadge }}">
                                                {{ $staff->type }}
                                            </span>
                                        </div>
                                        <span class="text-[10px] text-slate-400 block">{{ $staff->position ?? 'Staff' }}</span>
                                    </div>
                                </div>
                                <span class="text-xs font-bold {{ $staff->projects_count > 0 ? 'text-slate-900' : 'text-slate-400' }}">
                                    {{ $staff->projects_count }} <span class="text-[10px] font-normal text-slate-500">Proyek Aktif</span>
                                </span>
                            </div>
                            <div class="w-full bg-slate-200/80 rounded-full h-1.5 overflow-hidden">
                                <div class="{{ $staff->projects_count >= 3 ? 'bg-amber-500' : 'bg-blue-600' }} h-1.5 rounded-full transition-all" style="width: {{ $loadPercent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-3">Belum ada staff terdaftar.</p>
                    @endforelse
                </div>
            </section>

            <!-- Panel 4b: Supervisi Reviewer & Partner -->
            @if(isset($reviewersDistribution) && $reviewersDistribution->isNotEmpty())
                <section class="panel !p-4 !mb-0 border border-slate-200 rounded-xl bg-white shadow-sm" data-animate-children>
                    <div class="flex items-center justify-between mb-2.5 pb-2 border-b border-slate-100">
                        <div>
                            <h2 class="!text-sm !font-bold text-slate-900 !mb-0.5 flex items-center gap-1.5">
                                <x-heroicon-o-check-badge class="w-4 h-4 text-purple-600" />
                                <span>Supervisi Reviewer & Partner</span>
                            </h2>
                            <p class="text-[11px] text-slate-500">Distribusi penugasan review dan persetujuan berkas klien.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach($reviewersDistribution as $revName => $revCount)
                            <div class="p-2 rounded-lg bg-slate-50 border border-slate-100 flex flex-col justify-between">
                                <span class="text-[11px] font-semibold text-slate-800 line-clamp-1" title="{{ $revName }}">
                                    {{ $revName }}
                                </span>
                                <div class="flex items-baseline justify-between mt-1">
                                    <span class="text-[10px] text-slate-400 font-medium">Approval</span>
                                    <span class="text-xs font-bold text-purple-700">{{ $revCount }} Klien</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- Panel 5: Visualisasi Distribusi & Aktivitas (Compact Charts) -->
            <section class="panel !p-4 !mb-0 border border-slate-200 rounded-xl bg-white shadow-sm" data-animate-children>
                <div class="flex items-center justify-between mb-2 pb-2 border-b border-slate-100">
                    <div>
                        <h2 class="!text-sm !font-bold text-slate-900 !mb-0.5 flex items-center gap-2">
                            <x-heroicon-o-chart-pie class="w-4 h-4 text-slate-700" />
                            <span>Analitik & Distribusi</span>
                        </h2>
                        <p class="text-[11px] text-slate-500">Ringkasan kategori layanan dan update aktivitas tim.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    <!-- Projects by Category (Test target) -->
                    <div class="bg-slate-50/60 p-2.5 rounded-lg border border-slate-100">
                        <div class="flex items-center justify-between mb-1.5">
                            <h3 class="!text-xs !font-bold text-slate-800 !mb-0">Projects by Category</h3>
                            <span class="text-[10px] text-slate-400 font-medium">Layanan</span>
                        </div>
                        <div class="chart-wrap" style="height: 140px; position: relative;">
                            <canvas id="projectsByCategoryChart"></canvas>
                        </div>
                    </div>

                    <!-- Progress Updates (Last 14 Days) (Test target) -->
                    <div class="bg-slate-50/60 p-2.5 rounded-lg border border-slate-100">
                        <div class="flex items-center justify-between mb-1.5">
                            <h3 class="!text-xs !font-bold text-slate-800 !mb-0">Progress Updates (Last 14 Days)</h3>
                            <span class="text-[10px] text-slate-400 font-medium">Aktivitas</span>
                        </div>
                        <div class="chart-wrap" style="height: 140px; position: relative;">
                            <canvas id="progressUpdatesChart"></canvas>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>
@endif

@if(empty($isStaff) || !$isStaff)
    <!-- Chart.js Engine -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        const categoryLabels = @js($projectsByCategory->pluck('name'));
        const categoryData = @js($projectsByCategory->pluck('projects_count'));

        const progressLabels = @js($progressUpdates->pluck('date'));
        const progressData = @js($progressUpdates->pluck('avg_progress'));
        const progressCount = @js($progressUpdates->pluck('count'));

        new Chart(document.getElementById('projectsByCategoryChart'), {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [{
                    label: 'Projects',
                    data: categoryData,
                    backgroundColor: '#0b192c',
                    hoverBackgroundColor: '#1e3e62',
                    borderColor: '#070e18',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        padding: 8,
                        titleFont: { size: 11 },
                        bodyFont: { size: 11 }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { size: 10 } }
                    }
                }
            }
        });

        new Chart(document.getElementById('progressUpdatesChart'), {
            type: 'line',
            data: {
                labels: progressLabels,
                datasets: [{
                    label: 'Avg progress %',
                    data: progressData,
                    borderColor: '#0b192c',
                    backgroundColor: 'rgba(11, 25, 44, 0.08)',
                    borderWidth: 2,
                    pointBackgroundColor: '#0b192c',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 1.5,
                    pointRadius: 3,
                    fill: true,
                    tension: 0.3,
                }, {
                    label: 'Updates count',
                    data: progressCount,
                    borderColor: '#f59e0b',
                    backgroundColor: 'transparent',
                    borderWidth: 1.5,
                    borderDash: [3, 3],
                    pointBackgroundColor: '#f59e0b',
                    pointRadius: 2.5,
                    tension: 0.3,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } },
                    tooltip: {
                        padding: 8,
                        titleFont: { size: 11 },
                        bodyFont: { size: 11 }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 9 } }
                    },
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        ticks: { font: { size: 9 } }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { stepSize: 1, font: { size: 9 } }
                    }
                }
            }
        });

        // 4 Portfolio Analytics Horizontal Bar Charts
        const barLabelsPlugin = {
            id: 'barLabelsPlugin',
            afterDatasetsDraw(chart) {
                const { ctx } = chart;
                chart.data.datasets.forEach((dataset, i) => {
                    const meta = chart.getDatasetMeta(i);
                    meta.data.forEach((bar, index) => {
                        const val = dataset.data[index];
                        if (val !== undefined && val !== null && val > 0) {
                            ctx.save();
                            ctx.font = '600 11px sans-serif';
                            const barWidth = Math.abs(bar.x - bar.base);
                            if (barWidth > 28) {
                                ctx.fillStyle = '#ffffff';
                                ctx.textAlign = 'right';
                                ctx.textBaseline = 'middle';
                                ctx.fillText(val, bar.x - 6, bar.y);
                            } else {
                                ctx.fillStyle = '#0c356a';
                                ctx.textAlign = 'left';
                                ctx.textBaseline = 'middle';
                                ctx.fillText(val, bar.x + 6, bar.y);
                            }
                            ctx.restore();
                        }
                    });
                });
            }
        };

        const createHorizontalBarChart = (canvasId, labels, data, yTitle) => {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            const numData = data.map(v => Number(v) || 0);
            const maxVal = Math.max(...numData, 1);

            new Chart(canvas, {
                type: 'bar',
                plugins: [barLabelsPlugin],
                data: {
                    labels: labels,
                    datasets: [{
                        data: numData,
                        backgroundColor: '#0c356a',
                        hoverBackgroundColor: '#144686',
                        borderRadius: 3,
                        barPercentage: 0.75,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `Jumlah: ${ctx.parsed.x} Klien`
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            suggestedMax: Math.ceil(maxVal * 1.15),
                            title: {
                                display: true,
                                text: 'COUNTA of Client Name',
                                font: { size: 11, weight: '500' },
                                color: '#1e293b'
                            },
                            grid: {
                                color: '#e2e8f0',
                                drawTicks: true
                            },
                            ticks: {
                                stepSize: maxVal > 10 ? 5 : (maxVal > 5 ? 2 : 1),
                                font: { size: 11 }
                            }
                        },
                        y: {
                            grid: { display: false },
                            title: {
                                display: true,
                                text: yTitle,
                                font: { size: 11, weight: '600' },
                                color: '#0f172a'
                            },
                            ticks: {
                                font: { size: 11 },
                                color: '#0f172a'
                            }
                        }
                    }
                }
            });
        };

        // 1. Jumlah Kontrak Klien
        createHorizontalBarChart(
            'contractDurationsChart',
            @js($contractDurations->keys()),
            @js($contractDurations->values()),
            'Contract Durations'
        );

        // 2. PIC Tax
        createHorizontalBarChart(
            'taxPicChart',
            @js($taxPicDistribution->keys()),
            @js($taxPicDistribution->values()),
            'Tax PIC'
        );

        // 3. Data Migration
        createHorizontalBarChart(
            'dataMigrationChart',
            @js($dataMigrationDistribution->keys()),
            @js($dataMigrationDistribution->values()),
            'Transfer'
        );

        // 4. PIC Accounting
        createHorizontalBarChart(
            'accountingPicChart',
            @js($accountingPicDistribution->keys()),
            @js($accountingPicDistribution->values()),
            'Accounting PIC'
        );

        // Instant filter for compliance table
        function filterComplianceTable(query) {
            const term = (query || '').toLowerCase().trim();
            const rows = document.querySelectorAll('.compliance-row');
            rows.forEach(row => {
                const search = row.getAttribute('data-search') || '';
                if (!term || search.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
@endif
</x-layouts.app>
