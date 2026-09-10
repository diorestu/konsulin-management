@props(['title' => 'Konsulin Manager'])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Konsulin Manager' }} — Jira Workspace</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        :root {
            --bg: #f8fafc;
            --surface: #ffffff;
            --surface-alt: #f1f5f9;
            --text: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --accent: #0b192c;
            --accent-dark: #070e18;
            --accent-blue: #1e3e62;
            --warning: #b45309;
            --danger: #b42318;
            --radius: 10px;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        .shell { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
        .sidebar {
            background: #0b192c;
            color: #f8fafc;
            padding: 24px 18px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-right: 1px solid #132a48;
        }
        .brand-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
            padding: 0 4px;
        }
        .brand-icon {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            background: #1e3e62;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.25);
        }
        .brand-title {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #ffffff;
            line-height: 1.2;
        }
        .brand-subtitle {
            font-size: 11px;
            font-weight: 500;
            color: #94a3b8;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .nav-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            padding: 10px 12px 6px;
        }
        .nav { display: flex; flex-direction: column; gap: 4px; }
        .nav a {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 9px 12px;
            border-radius: 8px;
            color: #cbd5e1;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.15s ease;
        }
        .nav a:hover {
            background: #132a48;
            color: #ffffff;
        }
        .nav a.active {
            background: #1e3e62;
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .nav a svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            color: #94a3b8;
        }
        .nav a.active svg {
            color: #ffffff;
        }

        .user-panel {
            background: #070e18;
            border: 1px solid #1e3e62;
            border-radius: 10px;
            padding: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 24px;
        }
        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: #1e3e62;
            color: #ffffff;
            font-weight: 700;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .main {
            padding: 24px 32px 48px;
            background: #f8fafc;
            max-width: 100%;
            overflow-x: hidden;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1px solid #e2e8f0;
        }
        h1 { margin: 0; font-size: 22px; font-weight: 700; letter-spacing: -0.02em; color: #0f172a; }
        h2 { margin: 0 0 14px; font-size: 15px; font-weight: 600; color: #0f172a; }
        h3 { margin: 0 0 10px; font-size: 13px; font-weight: 600; color: #334155; }
        .muted { color: var(--muted); }

        /* Shadcn style Buttons */
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 38px;
            padding: 0 14px;
            border: 1px solid #0b192c;
            border-radius: 8px;
            background: #0b192c;
            color: #fff;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .button:hover {
            background: #1e3e62;
            border-color: #1e3e62;
        }
        .button.secondary {
            background: #ffffff;
            color: #0f172a;
            border-color: #e2e8f0;
        }
        .button.secondary:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }
        .button.danger {
            background: #ef4444;
            border-color: #ef4444;
            color: #ffffff;
        }
        .button.danger:hover {
            background: #dc2626;
            border-color: #dc2626;
        }
        .button.small {
            min-height: 32px;
            padding: 0 10px;
            font-size: 12px;
            border-radius: 6px;
        }
        .button.icon-only {
            width: 32px;
            min-width: 32px;
            min-height: 32px;
            padding: 0;
            border-radius: 6px;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        td[data-column="actions"] {
            white-space: nowrap;
        }
        td[data-column="actions"] .actions {
            flex-wrap: nowrap;
            align-items: center;
        }
        td[data-column="actions"] form {
            display: inline-flex;
        }
        td[data-column="actions"] .button.icon-only {
            width: 30px;
            min-width: 30px;
            min-height: 30px;
            border: 1px solid transparent;
            background: transparent;
            color: #475569;
            border-radius: 6px;
        }
        td[data-column="actions"] .button.icon-only:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        td[data-column="actions"] .button.icon-only.danger {
            color: #ef4444;
        }
        td[data-column="actions"] .button.icon-only.danger:hover {
            background: #fee2e2;
        }

        /* Stat cards & panels (White surface with clean borders) */
        .stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat, .panel, .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .stat { padding: 20px; position: relative; overflow: hidden; }
        .stat::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #0b192c;
        }
        .stat strong { display: block; font-size: 26px; font-weight: 700; color: #0f172a; margin-bottom: 2px; }
        .grid { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(320px, .8fr); gap: 20px; align-items: start; }
        .panel { padding: 20px; margin-bottom: 20px; }
        .project-list { display: grid; gap: 12px; }
        .card { padding: 18px; }
        .card-head { display: flex; justify-content: space-between; gap: 14px; align-items: start; margin-bottom: 12px; }

        /* Datatable Toolbar */
        .datatable-toolbar {
            display: grid;
            grid-template-columns: minmax(240px, 1fr) auto auto;
            gap: 12px;
            align-items: end;
            margin-bottom: 14px;
        }
        .column-filter {
            position: relative;
        }
        .column-filter summary {
            min-height: 38px;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
            color: #334155;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            list-style: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .column-filter summary::-webkit-details-marker { display: none; }
        .column-filter-menu {
            position: absolute;
            right: 0;
            z-index: 30;
            display: grid;
            min-width: 200px;
            gap: 8px;
            margin-top: 8px;
            padding: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        .column-filter-menu label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #334155;
            font-size: 12px;
            font-weight: 500;
        }
        .column-filter-menu input { width: auto; min-height: auto; }
        .table-wrap {
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(0,0,0,0.03);
        }
        .sortable {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0;
            border: 0;
            background: transparent;
            color: inherit;
            font: inherit;
            font-weight: 700;
            text-transform: inherit;
            cursor: pointer;
        }
        .pagination-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 14px;
            font-size: 12.5px;
            color: #64748b;
        }
        .hidden-column { display: none; }

        /* Shadcn-like Pills / Badges */
        .label {
            display: inline-flex;
            align-items: center;
            min-height: 22px;
            padding: 0 8px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #334155;
            font-size: 11.5px;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            text-transform: capitalize;
        }
        .label.navy { background: #e2ebf4; color: #0b192c; border-color: #cbd5e1; }
        .label.danger { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
        .label.warning { background: #fef3c7; color: #92400e; border-color: #fde68a; }
        .label.success { background: #dcfce7; color: #166534; border-color: #bbf7d0; }
        .label.blue { background: #dbeafe; color: #1e40af; border-color: #bfdbfe; }

        .progress {
            width: 100%;
            height: 7px;
            overflow: hidden;
            border-radius: 999px;
            background: #f1f5f9;
            margin: 10px 0 8px;
        }
        .progress span { display: block; height: 100%; background: #0b192c; border-radius: 999px; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; text-align: left; font-size: 12.5px; vertical-align: top; }
        th { background: #f8fafc; color: #64748b; font-size: 11px; text-transform: uppercase; font-weight: 600; letter-spacing: 0.04em; }
        tr:hover td { background: #f8fafc; }

        form { display: grid; gap: 14px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        label { display: grid; gap: 5px; color: #475569; font-size: 12px; font-weight: 600; }
        input, select, textarea {
            width: 100%;
            min-height: 38px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            color: #0f172a;
            padding: 8px 12px;
            font: inherit;
            font-size: 13px;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #0b192c;
            box-shadow: 0 0 0 3px rgba(11, 25, 44, 0.1);
        }
        textarea { min-height: 92px; resize: vertical; }

        /* Native Dialog (Shadcn Modal Style) */
        dialog {
            width: min(820px, calc(100vw - 32px));
            max-height: calc(100vh - 48px);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 0;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            background: #ffffff;
        }
        dialog::backdrop {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
        }
        .modal-body { padding: 24px; }
        .modal-head {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
        }
        .modal-head h2 { margin: 0; font-size: 17px; }
        .icon-button {
            width: 32px;
            height: 32px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.15s;
        }
        .icon-button:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .wizard-steps {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }
        .wizard-pill {
            flex: 1;
            min-height: 36px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            color: #64748b;
            font-weight: 600;
            font-size: 12px;
        }
        .wizard-pill.active {
            border-color: #0b192c;
            background: #0b192c;
            color: #ffffff;
        }
        .wizard-step[hidden] { display: none; }
        .modal-actions {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
        }
        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            font-weight: 600;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .errors {
            padding: 14px 16px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            font-size: 13px;
        }
        .errors strong { display: block; margin-bottom: 4px; font-weight: 600; }
        .errors ul { margin: 0; padding-left: 18px; }

        .chart-wrap {
            position: relative;
            height: 320px;
            min-height: 320px;
        }

        @media (max-width: 960px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar { position: static; padding: 18px; }
            .main { padding: 18px; }
            .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .grid, .form-grid, .datatable-toolbar { grid-template-columns: 1fr; }
            .topbar { flex-direction: column; align-items: stretch; }
            .pagination-bar { align-items: stretch; flex-direction: column; }
        }

        @media (max-width: 640px) {
            .stats { grid-template-columns: 1fr; }
        }

        /* Animations */
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .page-transition {
            animation: fade-in-up 0.25s ease-out forwards;
        }
    </style>
    <script>
        function initSimpleTable(prefix) {
            const rows = Array.from(document.querySelectorAll('[data-row]'));
            const searchInput = document.getElementById(`${prefix}Search`);
            const rowsPerPageSelect = document.getElementById(`${prefix}RowsPerPage`);
            const paginationInfo = document.getElementById(`${prefix}PaginationInfo`);
            const prevPageButton = document.getElementById(`${prefix}PrevPage`);
            const nextPageButton = document.getElementById(`${prefix}NextPage`);
            let currentPage = 1;
            let sortKey = 'name';
            let sortDirection = 'asc';

            if (!searchInput || !rowsPerPageSelect) return;

            const normalize = (value) => String(value ?? '').toLowerCase();

            function filteredRows() {
                const term = normalize(searchInput.value);

                return rows
                    .filter((row) => normalize(row.textContent).includes(term))
                    .sort((a, b) => {
                        const left = a.dataset[sortKey] ?? '';
                        const right = b.dataset[sortKey] ?? '';
                        const result = Number.isFinite(Number(left)) && Number.isFinite(Number(right))
                            ? Number(left) - Number(right)
                            : left.localeCompare(right);

                        return sortDirection === 'asc' ? result : -result;
                    });
            }

            function setRowVisibility(row, visible) {
                if (visible) {
                    row.hidden = false;
                    return;
                }
                row.hidden = true;
            }

            function render() {
                const visibleRows = filteredRows();
                const perPage = Number(rowsPerPageSelect.value);
                const totalPages = Math.max(1, Math.ceil(visibleRows.length / perPage));
                currentPage = Math.min(currentPage, totalPages);
                const start = (currentPage - 1) * perPage;
                const end = start + perPage;

                rows.forEach((row) => setRowVisibility(row, false));
                visibleRows.slice(start, end).forEach((row) => setRowVisibility(row, true));

                paginationInfo.textContent = visibleRows.length
                    ? `${start + 1}-${Math.min(end, visibleRows.length)} of ${visibleRows.length}`
                    : '0 of 0';
                prevPageButton.disabled = currentPage <= 1;
                nextPageButton.disabled = currentPage >= totalPages;
            }

            document.querySelectorAll('[data-sort]').forEach((button) => {
                button.addEventListener('click', () => {
                    if (sortKey === button.dataset.sort) {
                        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        sortKey = button.dataset.sort;
                        sortDirection = 'asc';
                    }
                    render();
                });
            });

            document.querySelectorAll('[data-column-toggle]').forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    document.querySelectorAll(`[data-column="${checkbox.dataset.columnToggle}"]`)
                        .forEach((cell) => cell.classList.toggle('hidden-column', !checkbox.checked));
                });
            });

            searchInput.addEventListener('input', () => {
                currentPage = 1;
                render();
            });
            rowsPerPageSelect.addEventListener('change', () => {
                currentPage = 1;
                render();
            });
            prevPageButton.addEventListener('click', () => {
                currentPage -= 1;
                render();
            });
            nextPageButton.addEventListener('click', () => {
                currentPage += 1;
                render();
            });

            render();
        }
    </script>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div>
                <!-- Brand & Workspace Tag -->
                <div class="brand-header">
                    <div class="brand-icon">
                        <x-heroicon-o-squares-2x2 class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <div class="brand-title">Konsulin Manager</div>
                        <div class="brand-subtitle">Jira Workspace</div>
                    </div>
                </div>

                <!-- Navigation Section -->
                <div class="nav-label">Management</div>
                <nav class="nav">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <x-heroicon-o-chart-bar-square />
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('projects.index') }}" class="{{ request()->routeIs('projects.*') ? 'active' : '' }}">
                        <x-heroicon-o-clipboard-document-list />
                        <span>Projects & Boards</span>
                    </a>
                    <a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}">
                        <x-heroicon-o-building-office-2 />
                        <span>Kelola Client</span>
                    </a>
                    <a href="{{ route('project-categories.index') }}" class="{{ request()->routeIs('project-categories.*') ? 'active' : '' }}">
                        <x-heroicon-o-tag />
                        <span>Project Categories</span>
                    </a>
                    <a href="{{ route('staff.index') }}" class="{{ request()->routeIs('staff.*') ? 'active' : '' }}">
                        <x-heroicon-o-user-group />
                        <span>Staff / Employees</span>
                    </a>
                </nav>

                <div class="nav-label" style="margin-top: 14px;">External</div>
                <nav class="nav">
                    <a href="{{ route('website-content.index') }}" class="{{ request()->routeIs('website-content.*') ? 'active' : '' }}">
                        <x-heroicon-o-globe-alt />
                        <span>Website Content</span>
                    </a>
                    <a href="{{ route('home') }}" target="_blank">
                        <x-heroicon-o-arrow-top-right-on-square />
                        <span>Live Landing Page</span>
                    </a>
                </nav>
            </div>

            <!-- Authenticated User & Logout Bar -->
            <div>
                @auth
                    <div class="user-panel">
                        <div class="flex items-center gap-2.5 overflow-hidden">
                            <div class="user-avatar">
                                {{ auth()->user()->initials }}
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                                <div class="flex items-center gap-1 mt-0.5">
                                    @if(auth()->user()->isBoss())
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                            Partner (Boss)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                            Consultant
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="m-0 p-0">
                            @csrf
                            <button
                                type="submit"
                                title="Sign out"
                                class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-rose-900/60 text-slate-400 hover:text-rose-200 flex items-center justify-center transition border border-slate-700 cursor-pointer"
                            >
                                <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="button small w-full flex items-center justify-center gap-2">
                        <x-heroicon-o-lock-closed class="w-4 h-4" />
                        <span>Sign In</span>
                    </a>
                @endauth
            </div>
        </aside>

        <main class="main">
            <div class="page-transition" id="pageContent">
                @if (session('status'))
                    <div class="alert">
                        <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-600 shrink-0" />
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="errors">
                        <div class="flex items-center gap-2 font-bold mb-1 text-rose-800">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-rose-600 shrink-0" />
                            <span>Mohon periksa kesalahan input:</span>
                        </div>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
