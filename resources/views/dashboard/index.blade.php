<x-layouts.app title="Dashboard — Konsulin Manager">
    <div class="topbar">
        <div>
            <h1>Dashboard</h1>
            <p class="muted">Ringkasan performa portofolio proyek, klien, dan pemantauan risiko.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('projects.index') }}" class="button">
                <x-heroicon-o-squares-2x2 class="w-4 h-4" />
                <span>Buka Jira Board</span>
            </a>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <section class="stats" data-animate-children>
        <div class="stat">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total projects</span>
                <div class="w-8 h-8 rounded-lg bg-[#0b192c]/5 text-[#0b192c] flex items-center justify-center">
                    <x-heroicon-o-folder class="w-4 h-4" />
                </div>
            </div>
            <strong>{{ $totalProjects }}</strong>
            <span class="text-xs text-slate-500">Semua portofolio klien</span>
        </div>

        <div class="stat">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Active projects</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
                    <x-heroicon-o-arrow-path class="w-4 h-4" />
                </div>
            </div>
            <strong>{{ $activeProjects }}</strong>
            <span class="text-xs text-blue-600 font-medium">Sedang berjalan</span>
        </div>

        <div class="stat">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Clients</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
                    <x-heroicon-o-building-office-2 class="w-4 h-4" />
                </div>
            </div>
            <strong>{{ $totalClients }}</strong>
            <span class="text-xs text-slate-500">Perusahaan terdaftar</span>
        </div>

        <div class="stat">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Open threats</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-700 flex items-center justify-center">
                    <x-heroicon-o-shield-exclamation class="w-4 h-4" />
                </div>
            </div>
            <strong class="{{ $openThreats > 0 ? 'text-rose-600' : '' }}">{{ $openThreats }}</strong>
            <span class="text-xs {{ $openThreats > 0 ? 'text-rose-600 font-medium' : 'text-slate-500' }}">
                {{ $openThreats > 0 ? 'Perlu mitigasi segera' : 'Kondisi aman' }}
            </span>
        </div>
    </section>

    <!-- Charts Grid -->
    <div class="grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
        <section class="panel" data-animate-children>
            <div class="flex items-center justify-between mb-3">
                <h2>Projects by Category</h2>
                <span class="text-xs text-slate-400 font-medium">Distribusi Layanan</span>
            </div>
            <div class="chart-wrap">
                <canvas id="projectsByCategoryChart"></canvas>
            </div>
        </section>

        <section class="panel" data-animate-children>
            <div class="flex items-center justify-between mb-3">
                <h2>Progress Updates (Last 14 Days)</h2>
                <span class="text-xs text-slate-400 font-medium">Aktivitas Tim</span>
            </div>
            <div class="chart-wrap">
                <canvas id="progressUpdatesChart"></canvas>
            </div>
        </section>
    </div>

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
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
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
                    borderWidth: 2.5,
                    pointBackgroundColor: '#0b192c',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    fill: true,
                    tension: 0.35,
                }, {
                    label: 'Updates count',
                    data: progressCount,
                    borderColor: '#f59e0b',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [4, 4],
                    pointBackgroundColor: '#f59e0b',
                    pointRadius: 3,
                    tension: 0.35,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12 } }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        title: { display: true, text: 'Avg progress %' }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Updates' },
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    </script>
</x-layouts.app>
