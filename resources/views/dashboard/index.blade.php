<x-layouts.app title="Dashboard - Konsulin Manager">
    <div class="topbar">
        <div>
            <h1>Dashboard</h1>
            <p class="muted">Overview of projects, clients, and recent progress.</p>
        </div>
    </div>

    <section class="stats" data-animate-children>
        <div class="stat">
            <strong>{{ $totalProjects }}</strong>
            <span class="muted">Total projects</span>
        </div>
        <div class="stat">
            <strong>{{ $activeProjects }}</strong>
            <span class="muted">Active projects</span>
        </div>
        <div class="stat">
            <strong>{{ $totalClients }}</strong>
            <span class="muted">Clients</span>
        </div>
        <div class="stat">
            <strong>{{ $openThreats }}</strong>
            <span class="muted">Open threats</span>
        </div>
    </section>

    <div class="grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
        <section class="panel" data-animate-children>
            <h2>Projects by Category</h2>
            <div class="chart-wrap">
                <canvas id="projectsByCategoryChart"></canvas>
            </div>
        </section>

        <section class="panel" data-animate-children>
            <h2>Progress Updates (Last 14 Days)</h2>
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
                    backgroundColor: '#176b54',
                    borderColor: '#0f4c3d',
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
                    borderColor: '#176b54',
                    backgroundColor: 'rgba(23, 107, 84, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#176b54',
                    fill: true,
                    tension: 0.3,
                }, {
                    label: 'Updates count',
                    data: progressCount,
                    borderColor: '#b45309',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointBackgroundColor: '#b45309',
                    tension: 0.3,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom' }
                },
                scales: {
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
