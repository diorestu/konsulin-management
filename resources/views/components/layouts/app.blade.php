@props(['title' => 'Konsulin Manager'])

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Konsulin Manager' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --bg: #f6f7f9;
            --surface: #ffffff;
            --surface-alt: #eef3f1;
            --text: #18211f;
            --muted: #65716d;
            --line: #dce4e1;
            --accent: #176b54;
            --accent-dark: #0f4c3d;
            --warning: #b45309;
            --danger: #b42318;
            --radius: 8px;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--text);
        }
        a { color: inherit; text-decoration: none; }
        .shell { display: grid; grid-template-columns: 244px 1fr; min-height: 100vh; }
        .sidebar {
            background: #14211e;
            color: #f4faf7;
            padding: 24px 18px;
        }
        .brand { font-size: 18px; font-weight: 700; margin-bottom: 28px; }
        .nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 12px;
            border-radius: var(--radius);
            color: #dbe7e3;
            font-size: 13px;
            font-weight: 550;
        }
        .nav a.active { background: #213530; color: #ffffff; }
        .main { padding: 28px; }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 22px;
        }
        h1 { margin: 0; font-size: 24px; line-height: 1.2; }
        h2 { margin: 0 0 14px; font-size: 16px; }
        h3 { margin: 0 0 10px; font-size: 13px; }
        .muted { color: var(--muted); }
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 14px;
            border: 1px solid var(--accent);
            border-radius: var(--radius);
            background: var(--accent);
            color: #fff;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
        }
        .button.secondary {
            background: var(--surface);
            color: var(--accent-dark);
            border-color: var(--line);
        }
        .button.danger {
            background: var(--danger);
            border-color: var(--danger);
        }
        .button.small {
            min-height: 34px;
            padding: 0 10px;
            font-size: 12px;
        }
        .button.icon-only {
            width: 34px;
            min-width: 34px;
            min-height: 34px;
            padding: 0;
        }
        .button.icon-only i {
            font-size: 14px;
            line-height: 1;
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
            width: 28px;
            min-width: 28px;
            min-height: 28px;
            border: 0;
            background: transparent;
            color: var(--accent-dark);
            border-radius: 6px;
        }
        td[data-column="actions"] .button.icon-only:hover {
            background: var(--surface-alt);
        }
        td[data-column="actions"] .button.icon-only.danger {
            color: var(--danger);
        }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
        .stat, .panel, .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius);
        }
        .stat { padding: 18px; }
        .stat strong { display: block; font-size: 28px; margin-bottom: 4px; }
        .grid { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(320px, .8fr); gap: 16px; align-items: start; }
        .panel { padding: 18px; margin-bottom: 16px; }
        .project-list { display: grid; gap: 12px; }
        .card { padding: 16px; }
        .card-head { display: flex; justify-content: space-between; gap: 14px; align-items: start; margin-bottom: 12px; }
        .datatable-toolbar {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) auto auto;
            gap: 12px;
            align-items: end;
            margin-bottom: 14px;
        }
        .column-filter {
            position: relative;
        }
        .column-filter summary {
            min-height: 40px;
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: #fff;
            color: var(--accent-dark);
            font-weight: 500;
            cursor: pointer;
            list-style: none;
        }
        .column-filter summary::-webkit-details-marker { display: none; }
        .column-filter-menu {
            position: absolute;
            right: 0;
            z-index: 20;
            display: grid;
            min-width: 180px;
            gap: 8px;
            margin-top: 8px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: #fff;
            box-shadow: 0 16px 40px rgb(20 33 30 / 14%);
        }
        .column-filter-menu label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text);
            font-weight: 600;
        }
        .column-filter-menu input { width: auto; min-height: auto; }
        .table-wrap {
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: #fff;
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
            font-weight: 800;
            text-transform: inherit;
            cursor: pointer;
        }
        .pagination-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 14px;
        }
        .hidden-column { display: none; }
        .label {
            display: inline-flex;
            align-items: center;
            min-height: 26px;
            padding: 0 8px;
            border-radius: 999px;
            background: var(--surface-alt);
            color: var(--accent-dark);
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }
        .label.danger { background: #fee4e2; color: var(--danger); }
        .label.warning { background: #fef3c7; color: var(--warning); }
        .progress {
            width: 100%;
            height: 9px;
            overflow: hidden;
            border-radius: 999px;
            background: #e5ebe8;
            margin: 10px 0 8px;
        }
        .progress span { display: block; height: 100%; background: var(--accent); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 11px 10px; border-bottom: 1px solid var(--line); text-align: left; font-size: 12px; vertical-align: top; }
        th { color: var(--muted); font-size: 11px; text-transform: uppercase; }
        form { display: grid; gap: 12px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        label { display: grid; gap: 6px; color: var(--muted); font-size: 12px; font-weight: 500; }
        input, select, textarea {
            width: 100%;
            min-height: 40px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: #fff;
            color: var(--text);
            padding: 9px 10px;
            font: inherit;
        }
        textarea { min-height: 92px; resize: vertical; }
        dialog {
            width: min(820px, calc(100vw - 32px));
            max-height: calc(100vh - 48px);
            border: 0;
            border-radius: 10px;
            padding: 0;
            box-shadow: 0 24px 80px rgb(20 33 30 / 24%);
        }
        dialog::backdrop { background: rgb(15 23 21 / 55%); }
        .modal-body { padding: 20px; }
        .modal-head {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: start;
            padding: 18px 20px;
            border-bottom: 1px solid var(--line);
        }
        .modal-head h2 { margin: 0; }
        .icon-button {
            width: 36px;
            height: 36px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: #fff;
            cursor: pointer;
            font-size: 20px;
            line-height: 1;
        }
        .wizard-steps {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }
        .wizard-pill {
            flex: 1;
            min-height: 34px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #fff;
            color: var(--muted);
            font-weight: 800;
        }
        .wizard-pill.active {
            border-color: var(--accent);
            background: var(--surface-alt);
            color: var(--accent-dark);
        }
        .wizard-step[hidden] { display: none; }
        .modal-actions {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 6px;
        }
        .alert {
            padding: 12px 14px;
            margin-bottom: 16px;
            border-radius: var(--radius);
            background: #dcfce7;
            color: #166534;
            font-weight: 700;
        }
        .errors {
            padding: 12px 14px;
            margin-bottom: 16px;
            border-radius: var(--radius);
            background: #fee4e2;
            color: var(--danger);
        }
        .chart-wrap {
            position: relative;
            height: 320px;
            min-height: 320px;
        }

        @media (max-width: 880px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar { position: static; padding: 18px; }
            .main { padding: 18px; }
            .stats, .grid, .form-grid, .datatable-toolbar { grid-template-columns: 1fr; }
            .topbar { flex-direction: column; }
            .pagination-bar { align-items: stretch; flex-direction: column; }
        }

        /* Animations */
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fade-out-down {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(12px); }
        }

        @keyframes fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes scale-in {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes scale-out {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.96); }
        }

        @keyframes slide-in-left {
            from { opacity: 0; transform: translateX(-12px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes slide-in-right {
            from { opacity: 0; transform: translateX(12px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .animate-fade-up { animation: fade-in-up 0.35s ease-out forwards; }
        .animate-fade-down { animation: fade-out-down 0.25s ease-in forwards; }
        .animate-fade-in { animation: fade-in 0.35s ease-out forwards; }
        .animate-scale-in { animation: scale-in 0.25s ease-out forwards; }
        .animate-scale-out { animation: scale-out 0.2s ease-in forwards; }
        .animate-slide-left { animation: slide-in-left 0.35s ease-out forwards; }
        .animate-slide-right { animation: slide-in-right 0.35s ease-out forwards; }

        /* Page transition */
        .page-transition {
            animation: fade-in-up 0.35s ease-out forwards;
        }

        .page-transition.is-exiting {
            animation: fade-out-down 0.2s ease-in forwards;
        }

        /* Staggered children animations */
        [data-animate-children] > * {
            animation: fade-in-up 0.35s ease-out backwards;
        }

        [data-animate-children] > *:nth-child(1) { animation-delay: 0ms; }
        [data-animate-children] > *:nth-child(2) { animation-delay: 50ms; }
        [data-animate-children] > *:nth-child(3) { animation-delay: 100ms; }
        [data-animate-children] > *:nth-child(4) { animation-delay: 150ms; }
        [data-animate-children] > *:nth-child(5) { animation-delay: 200ms; }
        [data-animate-children] > *:nth-child(6) { animation-delay: 250ms; }
        [data-animate-children] > *:nth-child(7) { animation-delay: 300ms; }
        [data-animate-children] > *:nth-child(8) { animation-delay: 350ms; }

        /* Modal animations */
        dialog[open] { animation: scale-in 0.25s ease-out; }
        dialog.is-closing { animation: scale-out 0.2s ease-in forwards; }

        /* Table row animations */
        tbody tr {
            transition: opacity 0.2s ease, transform 0.2s ease, background-color 0.15s ease;
        }

        tbody tr.is-exiting {
            opacity: 0;
            transform: translateX(-8px);
        }

        tbody tr.is-entering {
            animation: fade-in-up 0.25s ease-out forwards;
        }

        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
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
                    row.classList.remove('is-exiting');
                    row.classList.add('is-entering');
                    row.addEventListener('animationend', () => row.classList.remove('is-entering'), { once: true });
                    return;
                }

                row.classList.add('is-exiting');
                setTimeout(() => {
                    row.hidden = true;
                    row.classList.remove('is-exiting');
                }, 80);
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
                    ? `Showing ${start + 1}-${Math.min(end, visibleRows.length)} of ${visibleRows.length} rows`
                    : 'Showing 0 rows';
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
    <script>
        // Page and component animation engine
        (function () {
            const pageContent = document.getElementById('pageContent');

            function exitPage() {
                if (!pageContent) return;
                pageContent.classList.add('is-exiting');
            }

            // Animate internal navigation
            document.addEventListener('click', (e) => {
                const link = e.target.closest('a[href]');
                if (!link) return;

                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript:') || link.target === '_blank') return;
                if (e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;

                const url = new URL(href, window.location.href);
                if (url.origin !== window.location.origin) return;

                e.preventDefault();
                exitPage();
                setTimeout(() => { window.location.href = href; }, 200);
            });

            // Animate form submissions
            document.addEventListener('submit', (e) => {
                const form = e.target;
                if (form.method && form.method.toLowerCase() === 'get') return;
                exitPage();
            });

            // Modal open/close animations
            const originalShowModal = HTMLDialogElement.prototype.showModal;
            const originalClose = HTMLDialogElement.prototype.close;

            HTMLDialogElement.prototype.showModal = function () {
                this.classList.remove('is-closing');
                return originalShowModal.call(this);
            };

            HTMLDialogElement.prototype.close = function (returnValue) {
                if (!this.open || this.classList.contains('is-closing')) {
                    return originalClose.call(this, returnValue);
                }

                this.classList.add('is-closing');
                this.addEventListener('animationend', () => {
                    this.classList.remove('is-closing');
                    originalClose.call(this, returnValue);
                }, { once: true });
            };
        })();
    </script>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">Konsulin Manager</div>
            <nav class="nav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('home') || request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('projects.index') }}" class="{{ request()->routeIs('projects.*') ? 'active' : '' }}">Projects</a>
                <a href="{{ route('project-categories.index') }}" class="{{ request()->routeIs('project-categories.*') ? 'active' : '' }}">Project Categories</a>
                <a href="{{ route('staff.index') }}" class="{{ request()->routeIs('staff.*') ? 'active' : '' }}">Staff / Employees</a>
                <a href="{{ route('website-content.index') }}" class="{{ request()->routeIs('website-content.*') ? 'active' : '' }}">Website Content</a>
            </nav>
        </aside>
        <main class="main">
            <div class="page-transition" id="pageContent">
                @if (session('status'))
                    <div class="alert">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="errors">
                        <strong>Please fix these fields:</strong>
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
