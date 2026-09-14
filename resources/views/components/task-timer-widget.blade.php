@if(auth()->check() && auth()->user()->isStaff())
<!-- Floating / Docked Always-on-Top Task Time Tracker (Staff Only) -->
<div id="konsulinTimerContainer" class="konsulin-timer-wrapper">
    <!-- 1. Top Mini Quick Bar / Persistent Dock Indicator -->
    <div id="konsulinTimerDock" class="konsulin-timer-dock" style="display: none;">
        <div class="timer-dock-inner">
            <div class="timer-status-dot-pulse"></div>
            <div class="timer-dock-details">
                <div class="timer-dock-task">
                    <span id="dockTaskKey" class="timer-badge-key">TSK-0</span>
                    <span id="dockTaskTitle" class="timer-title-text truncate">Memuat task...</span>
                </div>
                <div class="timer-dock-sub">
                    <span id="dockClientName" class="timer-sub-client">Klien</span>
                    <span class="timer-dock-sep">·</span>
                    <span id="dockTimeElapsed" class="timer-elapsed-mono">00:00:00</span>
                </div>
            </div>
            <div class="timer-dock-actions">
                <button
                    type="button"
                    class="timer-btn-icon"
                    onclick="window.KonsulinTimer.togglePiP()"
                    title="Buka Floating Window Always-on-Top (Layar Selalu Terlihat)"
                >
                    <x-heroicon-o-arrows-pointing-out class="w-3.5 h-3.5" />
                    <span class="text-[11px] hidden sm:inline">Always-on-Top</span>
                </button>
                <button
                    type="button"
                    class="timer-btn-icon text-indigo-200 hover:text-white"
                    onclick="window.KonsulinTimer.openDesktopAppLink()"
                    title="Buka di Aplikasi Desktop Konsulin"
                >
                    <x-heroicon-o-computer-desktop class="w-3.5 h-3.5" />
                </button>
                <button
                    type="button"
                    class="timer-btn-stop"
                    onclick="window.KonsulinTimer.openStopModal()"
                    title="Hentikan dan Catat Waktu Kerja"
                >
                    <x-heroicon-s-stop class="w-3 h-3 mr-1" />
                    <span>Selesai</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 2. Idle Quick Launch Button (Visible in Topbar or Floating when no active timer) -->
    <div id="konsulinTimerIdleBtn" class="konsulin-timer-idle" style="display: none;">
        <button
            type="button"
            class="timer-btn-launch"
            onclick="window.KonsulinTimer.openTaskPickerModal()"
            title="Mulai Sesi Waktu Kerja Baru"
        >
            <x-heroicon-o-play class="w-3.5 h-3.5 text-emerald-400" />
            <span>Lacak Waktu Kerja</span>
        </button>
    </div>
</div>

<!-- Modal: Quick Task Picker to Start Tracking -->
<dialog id="timerPickerModal" class="timer-modal-dialog">
    <div class="timer-modal-card">
        <div class="timer-modal-header">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center border border-emerald-500/20">
                    <x-heroicon-o-clock class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 m-0">Mulai Lacak Waktu Kerja</h3>
                    <p class="text-xs text-slate-500 m-0">Pilih task aktif untuk mulai penghitungan jam kerja konsultan.</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="document.getElementById('timerPickerModal').close()">
                <x-heroicon-o-x-mark class="w-4 h-4" />
            </button>
        </div>

        <div class="timer-modal-body">
            <div class="mb-3">
                <input
                    type="text"
                    id="timerTaskSearchInput"
                    placeholder="Cari task atau nama project..."
                    class="w-full text-xs px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-slate-800 bg-slate-50 focus:bg-white"
                    oninput="window.KonsulinTimer.filterTasks(this.value)"
                />
            </div>

            <div id="timerTaskListContainer" class="timer-task-list max-h-[260px] overflow-y-auto space-y-1.5 pr-1">
                <div class="py-8 text-center text-xs text-slate-400">Memuat daftar task...</div>
            </div>
        </div>

        <div class="timer-modal-footer">
            <div class="text-[11px] text-slate-500 flex items-center gap-1.5">
                <x-heroicon-o-sparkles class="w-3.5 h-3.5 text-blue-600" />
                <span>Timer akan tetap mengambang di layar via Always-on-Top PiP.</span>
            </div>
            <button type="button" class="button secondary small" onclick="document.getElementById('timerPickerModal').close()">
                Batal
            </button>
        </div>
    </div>
</dialog>

<!-- Modal: Prompt Start Tracking upon task In Progress -->
<dialog id="timerPromptModal" class="timer-modal-dialog">
    <div class="timer-modal-card">
        <div class="timer-modal-header">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-600 flex items-center justify-center border border-blue-500/20">
                    <x-heroicon-o-play class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 m-0">Mulai Waktu Kerja Sekarang?</h3>
                    <p class="text-xs text-slate-500 m-0">Status task telah diubah menjadi In Progress.</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="document.getElementById('timerPromptModal').close()">
                <x-heroicon-o-x-mark class="w-4 h-4" />
            </button>
        </div>

        <div class="timer-modal-body">
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg mb-3">
                <div class="text-xs font-bold text-slate-900" id="promptTaskTitle">-</div>
                <div class="text-[11px] text-slate-500 mt-0.5" id="promptProjectMeta">-</div>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed m-0">
                Apakah Anda ingin memulai penghitungan jam kerja dan mengaktifkan floating timer di layar Anda?
            </p>
        </div>

        <div class="timer-modal-footer">
            <button type="button" class="button secondary small" onclick="document.getElementById('timerPromptModal').close()">
                Nanti Saja
            </button>
            <button
                type="button"
                id="btnPromptStart"
                class="button small bg-[#0b192c] text-white flex items-center gap-1.5"
                onclick="window.KonsulinTimer.confirmPromptStart()"
            >
                <x-heroicon-o-play class="w-3.5 h-3.5 text-emerald-400" />
                <span>Mulai Waktu Kerja</span>
            </button>
        </div>
    </div>
</dialog>

<!-- Modal: Stop Timer & Log Work Session -->
<dialog id="timerStopModal" class="timer-modal-dialog">
    <div class="timer-modal-card">
        <div class="timer-modal-header">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-600 flex items-center justify-center border border-rose-500/20">
                    <x-heroicon-o-stop class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 m-0">Selesaikan Sesi Kerja</h3>
                    <p class="text-xs text-slate-500 m-0">Catat durasi kerja dan rekap aktivitas pengerjaan.</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="document.getElementById('timerStopModal').close()">
                <x-heroicon-o-x-mark class="w-4 h-4" />
            </button>
        </div>

        <form id="timerStopForm" onsubmit="window.KonsulinTimer.submitStop(event)">
            <div class="timer-modal-body">
                <!-- Session Duration Summary Box -->
                <div class="p-3.5 bg-slate-900 text-white rounded-lg mb-4 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Total Durasi Sesi Ini</div>
                        <div id="stopModalDuration" class="text-xl font-bold font-mono text-emerald-400 mt-0.5">00:00:00</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Target Task</div>
                        <div id="stopModalTaskKey" class="text-xs font-semibold text-slate-200 mt-0.5">TSK-0</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Pengerjaan (Opsional)</label>
                    <textarea
                        name="notes"
                        id="stopModalNotes"
                        rows="2"
                        placeholder="Contoh: Menyelesaikan rekonsiliasi faktur pajak keluaran masa Juli..."
                        class="w-full text-xs p-2.5 rounded-lg border border-slate-200 focus:outline-none focus:border-slate-800 bg-white"
                    ></textarea>
                </div>

                <div class="mb-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Update Progress Task (%)</label>
                    <div class="flex items-center gap-3">
                        <input
                            type="range"
                            id="stopModalProgressRange"
                            min="0"
                            max="100"
                            step="5"
                            class="w-full accent-slate-900 cursor-pointer"
                            oninput="document.getElementById('stopModalProgressVal').value = this.value"
                        />
                        <div class="flex items-center gap-1 shrink-0">
                            <input
                                type="number"
                                name="progress_percent"
                                id="stopModalProgressVal"
                                min="0"
                                max="100"
                                class="w-14 text-xs font-bold text-center py-1 rounded border border-slate-200"
                                oninput="document.getElementById('stopModalProgressRange').value = this.value"
                            />
                            <span class="text-xs font-bold text-slate-500">%</span>
                        </div>
                    </div>
                </div>

                <div class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center gap-2">
                    <input
                        type="checkbox"
                        name="mark_completed"
                        id="stopModalMarkCompleted"
                        value="1"
                        class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                    />
                    <label for="stopModalMarkCompleted" class="text-xs font-semibold text-emerald-900 cursor-pointer">
                        Tandai task telah selesai 100% (Completed)
                    </label>
                </div>
            </div>

            <div class="timer-modal-footer">
                <button type="button" class="button secondary small" onclick="document.getElementById('timerStopModal').close()">
                    Lanjutkan Kerja
                </button>
                <button type="submit" id="btnConfirmStop" class="button small bg-rose-600 hover:bg-rose-700 text-white font-semibold">
                    Simpan & Hentikan Timer
                </button>
            </div>
        </form>
    </div>
</dialog>

<style>
/* Always-on-top Timer Styling */
.konsulin-timer-wrapper {
    position: fixed;
    bottom: 20px;
    right: 24px;
    z-index: 99999;
    font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
    pointer-events: none;
}
.konsulin-timer-wrapper * {
    pointer-events: auto;
}

/* Active Floating Pill Dock */
.konsulin-timer-dock {
    background: #0b192c;
    color: #ffffff;
    border: 1px solid #1e3e62;
    border-radius: 12px;
    padding: 8px 12px;
    box-shadow: 0 10px 25px -5px rgba(11, 25, 44, 0.4), 0 8px 10px -6px rgba(11, 25, 44, 0.3);
    min-width: 320px;
    max-width: 440px;
    animation: timerSlideUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes timerSlideUp {
    from { opacity: 0; transform: translateY(12px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.timer-dock-inner {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Pulsing Green Dot */
.timer-status-dot-pulse {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background-color: #10b981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: timerPulse 1.8s infinite;
    flex-shrink: 0;
}
@keyframes timerPulse {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { box-shadow: 0 0 0 7px rgba(16, 185, 129, 0); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.timer-dock-details {
    flex: 1;
    min-width: 0;
}
.timer-dock-task {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
}
.timer-badge-key {
    background: #1e3e62;
    color: #93c5fd;
    font-family: ui-monospace, monospace;
    font-size: 10px;
    padding: 1px 5px;
    border-radius: 4px;
    flex-shrink: 0;
}
.timer-title-text {
    color: #f8fafc;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.timer-dock-sub {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: #94a3b8;
    margin-top: 1px;
}
.timer-dock-sep {
    color: #475569;
}
.timer-elapsed-mono {
    font-family: ui-monospace, SFMono-Regular, monospace;
    font-weight: 700;
    color: #34d399;
    letter-spacing: 0.04em;
}

.timer-dock-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}
.timer-btn-icon {
    background: #1e3e62;
    border: 1px solid #2e537d;
    color: #cbd5e1;
    border-radius: 6px;
    padding: 5px 8px;
    cursor: pointer;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease;
}
.timer-btn-icon:hover {
    background: #2e537d;
    color: #ffffff;
}
.timer-btn-stop {
    background: #dc2626;
    border: 1px solid #ef4444;
    color: #ffffff;
    border-radius: 6px;
    padding: 5px 10px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    transition: all 0.15s ease;
    box-shadow: 0 1px 2px rgba(220, 38, 38, 0.3);
}
.timer-btn-stop:hover {
    background: #b91c1c;
}

/* Idle Launcher Button */
.konsulin-timer-idle {
    position: fixed;
    bottom: 20px;
    right: 24px;
    z-index: 99999;
}
.timer-btn-launch {
    background: #0b192c;
    color: #ffffff;
    border: 1px solid #1e3e62;
    border-radius: 20px;
    padding: 8px 14px;
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 7px;
    box-shadow: 0 4px 12px rgba(11, 25, 44, 0.25);
    transition: all 0.18s ease;
}
.timer-btn-launch:hover {
    background: #1e3e62;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(11, 25, 44, 0.35);
}

/* Dialog & Modals */
.timer-modal-dialog {
    border: none;
    background: transparent;
    padding: 0;
    max-width: 480px;
    width: 92vw;
    border-radius: 12px;
}
.timer-modal-dialog::backdrop {
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(3px);
}
.timer-modal-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}
.timer-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}
.timer-modal-body {
    padding: 16px 18px;
}
.timer-modal-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 18px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}
.modal-close-btn {
    border: none;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    padding: 4px;
    border-radius: 6px;
    display: flex;
    align-items: center;
}
.modal-close-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.timer-task-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 9px 12px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.12s ease;
}
.timer-task-item:hover {
    border-color: #0b192c;
    background: #f1f5f9;
}
</style>

<script>
window.KonsulinTimer = (function() {
    let activeTimer = null;
    let timerInterval = null;
    let cachedTasks = [];
    let pipWindow = null;
    let promptTargetTaskId = null;

    function formatSeconds(totalSec) {
        const hours = Math.floor(totalSec / 3600);
        const mins = Math.floor((totalSec % 3600) / 60);
        const secs = totalSec % 60;
        return [
            String(hours).padStart(2, '0'),
            String(mins).padStart(2, '0'),
            String(secs).padStart(2, '0')
        ].join(':');
    }

    async function fetchActive() {
        try {
            const res = await fetch('{{ route('time-logs.active') }}', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data.active) {
                setRunningState(data);
            } else {
                setIdleState();
            }
        } catch (err) {
            console.error('Failed to fetch active time log:', err);
        }
    }

    function setRunningState(data) {
        activeTimer = data;
        const dock = document.getElementById('konsulinTimerDock');
        const idle = document.getElementById('konsulinTimerIdleBtn');
        if (dock) dock.style.display = 'block';
        if (idle) idle.style.display = 'none';

        const taskKey = data.task ? data.task.key : 'TSK-0';
        const taskTitle = data.task ? data.task.title : 'Task';
        const clientName = data.client ? data.client.name : (data.project ? data.project.name : 'Internal');

        const elKey = document.getElementById('dockTaskKey');
        const elTitle = document.getElementById('dockTaskTitle');
        const elClient = document.getElementById('dockClientName');
        if (elKey) elKey.textContent = taskKey;
        if (elTitle) elTitle.textContent = taskTitle;
        if (elClient) elClient.textContent = clientName;

        let elapsed = data.log.elapsed_seconds || 0;
        const elTime = document.getElementById('dockTimeElapsed');
        if (elTime) elTime.textContent = formatSeconds(elapsed);

        if (timerInterval) clearInterval(timerInterval);
        timerInterval = setInterval(() => {
            elapsed++;
            if (activeTimer && activeTimer.log) {
                activeTimer.log.elapsed_seconds = elapsed;
            }
            if (elTime) elTime.textContent = formatSeconds(elapsed);
            updatePiPView();
        }, 1000);

        // Update in-page task buttons if on projects/show
        updateInPageTaskButtons(data.task.id, true);
    }

    function setIdleState() {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        activeTimer = null;
        const dock = document.getElementById('konsulinTimerDock');
        const idle = document.getElementById('konsulinTimerIdleBtn');
        if (dock) dock.style.display = 'none';
        if (idle) idle.style.display = 'block';

        if (pipWindow && !pipWindow.closed) {
            pipWindow.close();
        }

        updateInPageTaskButtons(null, false);
    }

    function updateInPageTaskButtons(activeTaskId, isRunning) {
        document.querySelectorAll('[data-task-timer-btn]').forEach(btn => {
            const taskId = parseInt(btn.getAttribute('data-task-timer-btn'), 10);
            if (isRunning && taskId === activeTaskId) {
                btn.classList.add('bg-emerald-600', 'text-white');
                btn.classList.remove('bg-slate-100', 'text-slate-700');
                btn.innerHTML = '<span class="w-2 h-2 rounded-full bg-white animate-pulse"></span><span>Aktif</span>';
            } else {
                btn.classList.remove('bg-emerald-600', 'text-white');
                btn.classList.add('bg-slate-100', 'text-slate-700');
                btn.innerHTML = '<svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path></svg><span>Mulai</span>';
            }
        });
    }

    async function start(taskId, notes = null) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('{{ route('time-logs.start') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ task_id: taskId, notes: notes })
            });
            const data = await res.json();
            if (data.success) {
                if (window.toast) window.toast.success(data.message);
                setRunningState({
                    active: true,
                    log: data.log,
                    task: data.task,
                    project: data.project,
                    client: data.client
                });
                const picker = document.getElementById('timerPickerModal');
                if (picker && picker.open) picker.close();
                const prompt = document.getElementById('timerPromptModal');
                if (prompt && prompt.open) prompt.close();
            } else {
                if (window.toast) window.toast.error(data.message || 'Gagal memulai timer');
            }
        } catch (err) {
            console.error('Error starting timer:', err);
            if (window.toast) window.toast.error('Gagal menghubungi server untuk memulai timer.');
        }
    }

    function openStopModal() {
        if (!activeTimer) return;
        const modal = document.getElementById('timerStopModal');
        if (!modal) return;

        const durEl = document.getElementById('stopModalDuration');
        const keyEl = document.getElementById('stopModalTaskKey');
        const rangeEl = document.getElementById('stopModalProgressRange');
        const valEl = document.getElementById('stopModalProgressVal');
        const notesEl = document.getElementById('stopModalNotes');
        const checkEl = document.getElementById('stopModalMarkCompleted');

        const elapsed = activeTimer.log.elapsed_seconds || 0;
        if (durEl) durEl.textContent = formatSeconds(elapsed);
        if (keyEl) keyEl.textContent = activeTimer.task ? activeTimer.task.key + ' : ' + activeTimer.task.title : 'TSK';
        
        const prog = activeTimer.task ? (activeTimer.task.progress_percent || 0) : 0;
        if (rangeEl) rangeEl.value = prog;
        if (valEl) valEl.value = prog;
        if (notesEl) notesEl.value = '';
        if (checkEl) checkEl.checked = false;

        modal.showModal();
    }

    async function submitStop(event) {
        if (event) event.preventDefault();
        const modal = document.getElementById('timerStopModal');
        const notesEl = document.getElementById('stopModalNotes');
        const valEl = document.getElementById('stopModalProgressVal');
        const checkEl = document.getElementById('stopModalMarkCompleted');

        const notes = notesEl ? notesEl.value : null;
        const progress = valEl ? parseInt(valEl.value, 10) : null;
        const markCompleted = checkEl ? checkEl.checked : false;

        const btn = document.getElementById('btnConfirmStop');
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Menyimpan...';
        }

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('{{ route('time-logs.stop') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    notes: notes,
                    progress_percent: progress,
                    mark_completed: markCompleted
                })
            });
            const data = await res.json();
            if (data.success) {
                if (window.toast) window.toast.success(data.message);
                if (modal && modal.open) modal.close();
                setIdleState();

                // If user marked task completed, refresh or update UI
                if (markCompleted && window.location.pathname.includes('/projects/')) {
                    setTimeout(() => window.location.reload(), 800);
                }
            } else {
                if (window.toast) window.toast.error(data.message || 'Gagal menghentikan timer');
            }
        } catch (err) {
            console.error('Error stopping timer:', err);
            if (window.toast) window.toast.error('Gagal menghubungi server.');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Simpan & Hentikan Timer';
            }
        }
    }

    async function openTaskPickerModal() {
        const modal = document.getElementById('timerPickerModal');
        if (!modal) return;
        modal.showModal();

        const container = document.getElementById('timerTaskListContainer');
        if (!container) return;
        container.innerHTML = '<div class="py-8 text-center text-xs text-slate-400">Mengambil daftar task Anda...</div>';

        try {
            const res = await fetch('{{ route('time-logs.my-tasks') }}', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            cachedTasks = data.tasks || [];
            renderTaskList(cachedTasks);
        } catch (err) {
            container.innerHTML = '<div class="py-6 text-center text-xs text-rose-500">Gagal memuat daftar task.</div>';
        }
    }

    function renderTaskList(tasks) {
        const container = document.getElementById('timerTaskListContainer');
        if (!container) return;

        if (!tasks || tasks.length === 0) {
            container.innerHTML = '<div class="py-8 text-center text-xs text-slate-400">Tidak ada task yang aktif atau perlu dikerjakan saat ini.</div>';
            return;
        }

        container.innerHTML = tasks.map(t => `
            <div class="timer-task-item" onclick="window.KonsulinTimer.start(${t.id})">
                <div class="min-w-0 pr-2">
                    <div class="flex items-center gap-1.5 mb-0.5">
                        <span class="text-[10px] font-mono font-bold px-1 rounded bg-slate-100 text-slate-600 border border-slate-200">${t.key}</span>
                        <span class="text-[10px] font-semibold text-blue-600">${t.client_name}</span>
                    </div>
                    <div class="text-xs font-bold text-slate-800 truncate">${t.title}</div>
                    <div class="text-[11px] text-slate-400 truncate">${t.project_name}</div>
                </div>
                <button type="button" class="button small bg-[#0b192c] text-white shrink-0 text-[11px] py-1 px-2.5 h-7">
                    Mulai
                </button>
            </div>
        `).join('');
    }

    function filterTasks(query) {
        if (!cachedTasks) return;
        const q = query.toLowerCase().trim();
        if (!q) {
            renderTaskList(cachedTasks);
            return;
        }
        const filtered = cachedTasks.filter(t => 
            t.title.toLowerCase().includes(q) ||
            t.key.toLowerCase().includes(q) ||
            t.project_name.toLowerCase().includes(q) ||
            t.client_name.toLowerCase().includes(q)
        );
        renderTaskList(filtered);
    }

    function promptStartOnInProgress(taskId, taskTitle, projectName) {
        promptTargetTaskId = taskId;
        const modal = document.getElementById('timerPromptModal');
        const titleEl = document.getElementById('promptTaskTitle');
        const metaEl = document.getElementById('promptProjectMeta');
        if (titleEl) titleEl.textContent = taskTitle;
        if (metaEl) metaEl.textContent = projectName || 'Konsulin Project';
        if (modal) modal.showModal();
    }

    function confirmPromptStart() {
        if (promptTargetTaskId) {
            start(promptTargetTaskId);
        }
    }

    // Document Picture-in-Picture (Native OS Always-on-top window)
    async function togglePiP() {
        if (!activeTimer) {
            if (window.toast) window.toast.error('Tidak ada timer kerja yang sedang berjalan.');
            return;
        }

        if (pipWindow && !pipWindow.closed) {
            pipWindow.close();
            pipWindow = null;
            return;
        }

        if ('documentPictureInPicture' in window) {
            try {
                pipWindow = await window.documentPictureInPicture.requestWindow({
                    width: 360,
                    height: 140,
                });

                // Style pip window with Deep Navy theme
                pipWindow.document.body.style.margin = '0';
                pipWindow.document.body.style.fontFamily = "'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";
                pipWindow.document.body.style.background = '#0b192c';
                pipWindow.document.body.style.color = '#ffffff';
                pipWindow.document.body.style.display = 'flex';
                pipWindow.document.body.style.flexDirection = 'column';
                pipWindow.document.body.style.justifyContent = 'center';
                pipWindow.document.body.style.height = '100vh';
                pipWindow.document.body.style.padding = '12px 16px';
                pipWindow.document.body.style.boxSizing = 'border-box';
                pipWindow.document.body.style.userSelect = 'none';

                pipWindow.document.title = 'Konsulin : Always on Top Timer';

                updatePiPView();

                pipWindow.addEventListener('pagehide', () => {
                    pipWindow = null;
                });
            } catch (err) {
                console.error('Failed to open Picture-in-Picture window:', err);
                fallbackMiniWindow();
            }
        } else {
            fallbackMiniWindow();
        }
    }

    function updatePiPView() {
        if (!pipWindow || pipWindow.closed || !activeTimer) return;

        const taskKey = activeTimer.task ? activeTimer.task.key : 'TSK-0';
        const taskTitle = activeTimer.task ? activeTimer.task.title : 'Task';
        const clientName = activeTimer.client ? activeTimer.client.name : 'Client';
        const elapsedStr = formatSeconds(activeTimer.log.elapsed_seconds || 0);

        pipWindow.document.body.innerHTML = `
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 8px;">
                <div style="display:flex; align-items:center; gap: 6px; overflow:hidden;">
                    <span style="background:#1e3e62; color:#93c5fd; font-family:monospace; font-size:10px; font-weight:bold; padding:2px 6px; border-radius:4px;">${taskKey}</span>
                    <span style="font-size:11px; color:#94a3b8; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:180px;">${clientName}</span>
                </div>
                <div style="display:inline-flex; align-items:center; gap: 5px;">
                    <span style="width:7px; height:7px; border-radius:50%; background:#10b981;"></span>
                    <span style="font-size:10px; font-weight:bold; color:#10b981; letter-spacing:0.04em; text-transform:uppercase;">LIVE</span>
                </div>
            </div>

            <div style="font-size:13px; font-weight:700; color:#f8fafc; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin-bottom: 8px;">
                ${taskTitle}
            </div>

            <div style="display:flex; align-items:center; justify-content:space-between; border-top:1px solid #1e3e62; padding-top:8px;">
                <div style="font-family:monospace; font-size:20px; font-weight:800; color:#34d399; letter-spacing:0.05em;">
                    ${elapsedStr}
                </div>
                <button id="pipStopBtn" style="background:#dc2626; color:#ffffff; border:none; border-radius:6px; padding:6px 12px; font-size:11px; font-weight:700; cursor:pointer; font-family:inherit;">
                    Selesai
                </button>
            </div>
        `;

        const stopBtn = pipWindow.document.getElementById('pipStopBtn');
        if (stopBtn) {
            stopBtn.onclick = () => {
                window.focus();
                openStopModal();
            };
        }
    }

    function fallbackMiniWindow() {
        window.open(
            'about:blank',
            'KonsulinTimerFloating',
            'width=360,height=160,menubar=no,toolbar=no,location=no,status=no,resizable=yes'
        );
        if (window.toast) {
            window.toast.success('Gunakan browser Chrome / Edge untuk native Always-On-Top PiP atau jalankan aplikasi desktop Konsulin.');
        }
    }

    function openDesktopAppLink() {
        if (!activeTimer || !activeTimer.task) {
            window.location.href = 'konsulin://open';
        } else {
            window.location.href = 'konsulin://track?task_id=' + activeTimer.task.id;
        }
        setTimeout(() => {
            if (window.toast) {
                window.toast.success('Membuka aplikasi desktop Konsulin. Pastikan aplikasi desktop di folder desktop/ sudah terinstal.');
            }
        }, 500);
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetchActive();
    });

    return {
        start,
        openStopModal,
        submitStop,
        openTaskPickerModal,
        filterTasks,
        promptStartOnInProgress,
        confirmPromptStart,
        togglePiP,
        openDesktopAppLink,
        refresh: fetchActive
    };
})();
</script>
@endif
