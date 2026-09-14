@props(['title' => 'Konsulin Manager'])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Konsulin Manager' }} : Jira Workspace</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <script>
        if (localStorage.getItem('sidebar_collapsed') === 'true') {
            document.documentElement.classList.add('sidebar-is-collapsed');
        }
    </script>
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
        .shell {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
            transition: grid-template-columns 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar {
            background: #0b192c;
            color: #f8fafc;
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-right: 1px solid #132a48;
            transition: padding 0.22s ease, width 0.22s cubic-bezier(0.4, 0, 0.2, 1);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 260px;
            height: 100vh;
            max-height: 100vh;
            overflow: hidden;
            overscroll-behavior: none;
            z-index: 40;
            user-select: none;
        }
        .brand-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 24px;
            padding: 0 2px;
        }
        .brand-info {
            display: flex;
            align-items: center;
            gap: 12px;
            overflow: hidden;
        }
        .brand-text {
            overflow: hidden;
            white-space: nowrap;
        }
        .sidebar-toggle-btn {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            background: #132a48;
            border: 1px solid #1e3e62;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 0;
            flex-shrink: 0;
            transition: all 0.15s ease;
        }
        .sidebar-toggle-btn:hover {
            background: #1e3e62;
            color: #ffffff;
            border-color: #38bdf8;
        }
        .sidebar-toggle-btn:focus-visible {
            outline: 2px solid #38bdf8;
            outline-offset: 2px;
        }
        .sidebar-toggle-btn .toggle-icon {
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Sidebar Minimized / Collapsed Rules */
        .shell.sidebar-collapsed,
        html.sidebar-is-collapsed .shell {
            grid-template-columns: 74px 1fr;
        }
        .shell.sidebar-collapsed .sidebar,
        html.sidebar-is-collapsed .sidebar {
            width: 74px;
            padding: 20px 8px;
            align-items: center;
        }
        .shell.sidebar-collapsed .brand-header,
        html.sidebar-is-collapsed .brand-header {
            flex-direction: column;
            gap: 12px;
            padding: 0;
            margin-bottom: 20px;
            align-items: center;
        }
        .shell.sidebar-collapsed .brand-text,
        html.sidebar-is-collapsed .brand-text {
            display: none;
        }
        .shell.sidebar-collapsed .sidebar-toggle-btn,
        html.sidebar-is-collapsed .sidebar-toggle-btn {
            margin: 0 auto;
        }
        .shell.sidebar-collapsed .sidebar-toggle-btn .toggle-icon,
        html.sidebar-is-collapsed .sidebar-toggle-btn .toggle-icon {
            transform: rotate(180deg);
        }
        .shell.sidebar-collapsed .nav-label,
        html.sidebar-is-collapsed .nav-label {
            height: 1px;
            padding: 0;
            margin: 12px 6px;
            background: #1e3e62;
            font-size: 0;
            overflow: hidden;
            border: none;
        }
        .shell.sidebar-collapsed .nav a,
        html.sidebar-is-collapsed .nav a {
            justify-content: center;
            padding: 10px 0;
            width: 44px;
            height: 44px;
            margin: 0 auto;
            border-radius: 9px;
            position: relative;
        }
        .shell.sidebar-collapsed .nav a span,
        html.sidebar-is-collapsed .nav a span {
            display: none;
        }
        .shell.sidebar-collapsed .nav a svg,
        html.sidebar-is-collapsed .nav a svg {
            width: 20px;
            height: 20px;
        }
        .shell.sidebar-collapsed .nav a::after,
        html.sidebar-is-collapsed .nav a::after {
            content: attr(data-tooltip);
            position: absolute;
            left: calc(100% + 12px);
            top: 50%;
            transform: translateY(-50%);
            background: #0f172a;
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.15s ease, transform 0.15s ease;
            box-shadow: 0 6px 16px rgba(0,0,0,0.35);
            border: 1px solid #1e3e62;
            z-index: 9999;
        }
        .shell.sidebar-collapsed .nav a:hover::after,
        html.sidebar-is-collapsed .nav a:hover::after {
            opacity: 1;
            visibility: visible;
            transform: translateY(-50%) translateX(2px);
        }
        .shell.sidebar-collapsed .user-panel,
        html.sidebar-is-collapsed .user-panel {
            flex-direction: column;
            gap: 10px;
            padding: 10px 4px;
            align-items: center;
            background: transparent;
            border: none;
        }
        .shell.sidebar-collapsed .user-info,
        html.sidebar-is-collapsed .user-info {
            display: none;
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
            grid-column: 2;
            min-width: 0;
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

        /* Shadcn style Buttons - Strict Contrast (Grey=Black text, Dark=White text) */
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 38px;
            padding: 0 16px;
            border: 1px solid #0f172a;
            border-radius: 8px;
            background: #0f172a;
            color: #ffffff !important;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.18), 0 1px 2px rgba(15, 23, 42, 0.1);
        }
        .button svg {
            color: #ffffff !important;
        }
        .button:hover {
            background: #1e293b;
            border-color: #1e293b;
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.22), 0 2px 4px -2px rgba(15, 23, 42, 0.12);
        }
        .button:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.15);
        }
        .button:focus-visible {
            outline: 2px solid #0284c7;
            outline-offset: 2px;
        }

        /* Filter Pill / Badge High Contrast States */
        .filter-badge.active,
        a.filter-badge.active,
        a.filter-badge.active:link,
        a.filter-badge.active:visited,
        a.filter-badge.active:hover,
        a.filter-badge.active:active,
        a.filter-badge.active:focus {
            background-color: #0b192c !important;
            color: #ffffff !important;
            border-color: #0b192c !important;
            font-weight: 600 !important;
            box-shadow: 0 1px 3px rgba(11, 25, 44, 0.25) !important;
        }
        .filter-badge:not(.active),
        a.filter-badge:not(.active) {
            color: #334155 !important;
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
        }
        a.filter-badge:not(.active):hover {
            color: #0f172a !important;
            background-color: #f8fafc !important;
            border-color: #cbd5e1 !important;
        }

        /* Grey / Light Buttons: ALWAYS SOLID BLACK TEXT */
        .button.secondary,
        .button.light,
        .button.outline,
        button.secondary,
        a.button.secondary {
            background: #f1f5f9 !important;
            color: #000000 !important;
            border: 1px solid #cbd5e1 !important;
            font-weight: 600;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        .button.secondary svg,
        .button.light svg,
        .button.outline svg,
        button.secondary svg,
        a.button.secondary svg {
            color: #000000 !important;
        }
        .button.secondary:hover,
        .button.light:hover,
        .button.outline:hover,
        button.secondary:hover,
        a.button.secondary:hover {
            background: #e2e8f0 !important;
            border-color: #94a3b8 !important;
            color: #000000 !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }
        .button.secondary:hover svg,
        .button.light:hover svg,
        .button.outline:hover svg {
            color: #000000 !important;
        }
        .button.secondary:active,
        .button.light:active {
            background: #cbd5e1 !important;
            color: #000000 !important;
            transform: translateY(0);
        }

        /* Danger / Dark Red Button: WHITE TEXT */
        .button.danger {
            background: #dc2626 !important;
            border: 1px solid #b91c1c !important;
            color: #ffffff !important;
            box-shadow: 0 1px 3px rgba(220, 38, 38, 0.25);
        }
        .button.danger svg {
            color: #ffffff !important;
        }
        .button.danger:hover {
            background: #b91c1c !important;
            border-color: #991b1b !important;
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.3);
        }
        .button.danger:active {
            transform: translateY(0);
        }
        .button.small {
            min-height: 32px;
            padding: 0 12px;
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
            width: 32px;
            min-width: 32px;
            min-height: 32px;
            border: 1px solid #cbd5e1 !important;
            background: #f8fafc !important;
            color: #000000 !important;
            border-radius: 6px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        td[data-column="actions"] .button.icon-only svg {
            color: #000000 !important;
        }
        td[data-column="actions"] .button.icon-only:hover {
            background: #e2e8f0 !important;
            border-color: #94a3b8 !important;
            color: #000000 !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
        }
        td[data-column="actions"] .button.icon-only:hover svg {
            color: #000000 !important;
        }
        td[data-column="actions"] .button.icon-only.danger {
            background: #fef2f2 !important;
            border-color: #fecaca !important;
            color: #dc2626 !important;
        }
        td[data-column="actions"] .button.icon-only.danger svg {
            color: #dc2626 !important;
        }
        td[data-column="actions"] .button.icon-only.danger:hover {
            background: #dc2626 !important;
            border-color: #b91c1c !important;
            color: #ffffff !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(220, 38, 38, 0.25);
        }
        td[data-column="actions"] .button.icon-only.danger:hover svg {
            color: #ffffff !important;
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
        .grid:not([class*="grid-cols-"]) { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(320px, .8fr); gap: 20px; align-items: start; }
        .stats-row-6 {
            display: grid !important;
            grid-template-columns: repeat(6, minmax(0, 1fr)) !important;
            gap: 10px !important;
        }
        .stats-row-4 {
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: 12px !important;
        }
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
        input:not([type="checkbox"]):not([type="radio"]):not(.datatable-search-input), select, textarea {
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
        input:not([type="checkbox"]):not([type="radio"]):not(.datatable-search-input):focus, select:focus, textarea:focus {
            outline: none;
            border-color: #0b192c;
            box-shadow: 0 0 0 3px rgba(11, 25, 44, 0.1);
        }
        textarea { min-height: 92px; resize: vertical; }

        /* Hallmark & Antislop Datatable Precision Styling */
        .datatable-search-input {
            width: 100% !important;
            height: 36px !important;
            min-height: 36px !important;
            padding: 6px 36px 6px 38px !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            background: #f8fafc !important;
            color: #0f172a !important;
            font-size: 12.5px !important;
            line-height: normal !important;
            box-sizing: border-box !important;
            transition: all 0.15s ease !important;
        }
        .datatable-search-input:hover {
            background: #ffffff !important;
            border-color: #94a3b8 !important;
        }
        .datatable-search-input:focus {
            background: #ffffff !important;
            border-color: #0b192c !important;
            box-shadow: 0 0 0 2px rgba(11, 25, 44, 0.1) !important;
            outline: none !important;
        }
        .datatable-search-input::-webkit-search-decoration,
        .datatable-search-input::-webkit-search-cancel-button,
        .datatable-search-input::-webkit-search-results-button,
        .datatable-search-input::-webkit-search-results-decoration {
            display: none !important;
        }

        /* Hallmark Dropdown & Filter Details */
        details.column-filter[open] > summary {
            background: #f8fafc !important;
            border-color: #0b192c !important;
            box-shadow: 0 0 0 2px rgba(11, 25, 44, 0.08) !important;
        }
        details.column-filter[open] > summary .chevron-icon {
            transform: rotate(180deg);
        }
        .column-filter-menu {
            box-shadow: 0 14px 34px -4px rgba(15, 23, 42, 0.16), 0 6px 14px -4px rgba(15, 23, 42, 0.08) !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            z-index: 50 !important;
            animation: menu-appear 0.15s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes menu-appear {
            0% { opacity: 0; transform: translateY(-4px) scale(0.98); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Native Dialog (Shadcn Modal Style) - Perfect Viewport Middle-Center & Animations */
        dialog {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            margin: 0;
            width: min(820px, calc(100vw - 32px));
            max-height: calc(100vh - 48px);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 0;
            box-shadow: 0 25px 50px -12px rgba(11, 25, 44, 0.28), 0 0 0 1px rgba(11, 25, 44, 0.06);
            background: #ffffff;
            overflow: hidden;
            z-index: 999;
        }
        dialog:not([open]) {
            display: none;
        }
        dialog[open] {
            display: flex;
            flex-direction: column;
            animation: modal-popup-center 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        dialog::backdrop {
            background: rgba(11, 25, 44, 0.65);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            animation: modal-backdrop-fade 0.2s ease-out forwards;
        }

        @keyframes modal-popup-center {
            0% {
                opacity: 0;
                transform: translate(-50%, calc(-50% + 16px)) scale(0.95);
            }
            100% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        @keyframes modal-backdrop-fade {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        .modal-body {
            padding: 24px;
            overflow-y: auto;
            flex: 1 1 auto;
            max-height: calc(100vh - 150px);
        }
        .modal-head {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            padding: 18px 24px;
            border-bottom: 1px solid #e2e8f0;
            background: #ffffff;
            flex-shrink: 0;
        }
        .modal-head h2 { margin: 0; font-size: 17px; font-weight: 700; color: #0f172a; }
        .icon-button {
            width: 32px;
            height: 32px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            color: #475569;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.15s;
        }
        .icon-button:hover {
            background: #e2e8f0;
            border-color: #94a3b8;
            color: #0f172a;
        }

        .wizard-steps {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
        }
        .wizard-pill {
            flex: 1;
            min-height: 38px;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px;
            background: #f1f5f9 !important;
            color: #000000 !important;
            font-weight: 600;
            font-size: 12.5px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .wizard-pill:hover {
            background: #e2e8f0 !important;
            color: #000000 !important;
            border-color: #94a3b8 !important;
        }
        .wizard-pill.active {
            border-color: #0f172a !important;
            background: #0f172a !important;
            color: #ffffff !important;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.2);
        }
        .wizard-step[hidden] { display: none; }
        .modal-actions {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
        }

        /* Client Selection & Empty State in Project Wizard */
        .client-alert-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            color: #1e3a8a;
            margin-bottom: 16px;
        }
        .client-alert-banner .banner-icon {
            flex-shrink: 0;
            color: #2563eb;
            display: flex;
        }
        .client-alert-banner .banner-text {
            flex: 1;
            font-size: 12.5px;
            line-height: 1.4;
        }
        .client-alert-banner .banner-text strong {
            display: block;
            color: #1e40af;
            font-size: 13px;
            margin-bottom: 2px;
        }
        .client-mode-selector {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .client-tabs {
            display: flex;
            gap: 6px;
        }
        .client-tab {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 13px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid #cbd5e1 !important;
            background: #f1f5f9 !important;
            color: #000000 !important;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .client-tab svg {
            color: #000000 !important;
        }
        .client-tab:hover {
            background: #e2e8f0 !important;
            color: #000000 !important;
            border-color: #94a3b8 !important;
        }
        .client-tab:hover svg {
            color: #000000 !important;
        }
        .client-tab.active {
            background: #0f172a !important;
            color: #ffffff !important;
            border-color: #0f172a !important;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.2);
        }
        .client-tab.active svg {
            color: #ffffff !important;
        }
        .client-preview-card {
            margin-top: 12px;
            padding: 12px 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
        .client-preview-card .preview-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .client-preview-card .preview-header strong {
            font-size: 13px;
            color: #0f172a;
        }
        .client-preview-card .preview-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 6px 16px;
            font-size: 12px;
            color: #334155;
        }
        .btn-text-link {
            background: none;
            border: none;
            padding: 0;
            color: #0284c7;
            font-size: inherit;
            font-weight: 600;
            text-decoration: underline;
            cursor: pointer;
            display: inline;
        }
        .btn-text-link:hover {
            color: #0369a1;
        }
        .form-grid .full-width {
            grid-column: 1 / -1;
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
            .sidebar { position: static; width: 100%; height: auto; max-height: none; overflow: visible; padding: 18px; }
            .main { grid-column: 1; padding: 18px; }
            .stats:not(.stats-row-6):not(.stats-row-4) { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .grid:not([class*="grid-cols-"]), .form-grid, .datatable-toolbar { grid-template-columns: 1fr; }
            .topbar { flex-direction: column; align-items: stretch; }
            .pagination-bar { align-items: stretch; flex-direction: column; }
        }

        @media (max-width: 640px) {
            .stats:not(.stats-row-6):not(.stats-row-4) { grid-template-columns: 1fr; }
        }

        /* Animations */
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        /* Toastify & Toast System Customization (Tactile Antislop Styling) */
        .toastify {
            padding: 12px 16px !important;
            color: #ffffff !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 10px !important;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.4), 0 8px 10px -6px rgba(15, 23, 42, 0.2) !important;
            background: #0f172a !important;
            position: fixed !important;
            opacity: 0;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1) !important;
            border-radius: 10px !important;
            cursor: pointer !important;
            text-decoration: none !important;
            max-width: 420px !important;
            min-width: 280px !important;
            z-index: 999999 !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            font-family: inherit !important;
        }
        .toastify.on {
            opacity: 1 !important;
        }
        .toastify-right {
            right: 20px !important;
        }
        .toastify-top {
            top: 20px !important;
        }
        .toastify-close {
            background: transparent !important;
            border: 0 !important;
            color: #94a3b8 !important;
            cursor: pointer !important;
            font-family: inherit !important;
            font-size: 18px !important;
            line-height: 1 !important;
            padding: 0 0 0 12px !important;
            opacity: 0.8 !important;
            transition: color 0.15s ease, opacity 0.15s ease !important;
            margin-left: auto !important;
        }
        .toastify-close:hover {
            color: #ffffff !important;
            opacity: 1 !important;
        }
        .toast-success {
            border-color: #10b981 !important;
        }
        .toast-error {
            border-color: #ef4444 !important;
        }
        .toast-warning {
            border-color: #f59e0b !important;
        }
        .toast-info {
            border-color: #38bdf8 !important;
        }
    </style>
    <script>
        if (!window.toast) {
            window.toast = {
                show(options) {
                    if (typeof Toastify === 'function') {
                        const type = options.type || 'info';
                        let borderColor = '#38bdf8';
                        let iconSvg = '';
                        if (type === 'success') {
                            borderColor = '#10b981';
                            iconSvg = '<svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                        } else if (type === 'error') {
                            borderColor = '#ef4444';
                            iconSvg = '<svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                        } else if (type === 'warning') {
                            borderColor = '#f59e0b';
                            iconSvg = '<svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>';
                        } else {
                            iconSvg = '<svg class="w-5 h-5 text-sky-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                        }
                        const titleText = options.title || (type === 'success' ? 'Berhasil' : type === 'error' ? 'Gagal' : type === 'warning' ? 'Peringatan' : 'Notifikasi');
                        const messageText = options.message || options.text || '';
                        const node = document.createElement('div');
                        node.className = 'flex items-start gap-2.5';
                        node.innerHTML = `${iconSvg}<div class="flex-1 min-w-0"><div class="font-bold text-xs text-white uppercase tracking-wider mb-0.5">${titleText}</div><div class="text-xs text-slate-200 leading-snug">${messageText}</div></div>`;
                        Toastify({
                            node: node,
                            duration: options.duration || 3500,
                            close: true,
                            gravity: 'top',
                            position: 'right',
                            stopOnFocus: true,
                            className: `toastify toast-${type}`,
                            style: { background: '#0f172a', borderColor: borderColor }
                        }).showToast();
                    } else {
                        console.log('Toast:', options);
                    }
                },
                success(msg, title = 'Berhasil') { this.show({ type: 'success', message: msg, title }); },
                error(msg, title = 'Gagal') { this.show({ type: 'error', message: msg, title }); },
                warning(msg, title = 'Peringatan') { this.show({ type: 'warning', message: msg, title }); },
                info(msg, title = 'Informasi') { this.show({ type: 'info', message: msg, title }); }
            };
        }
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

        // Global backdrop click-to-close for all native dialogs
        document.addEventListener('click', function (event) {
            if (event.target && event.target.tagName === 'DIALOG' && event.target.open) {
                const rect = event.target.getBoundingClientRect();
                const isClickInside = (
                    rect.top <= event.clientY &&
                    event.clientY <= rect.bottom &&
                    rect.left <= event.clientX &&
                    event.clientX <= rect.right
                );
                if (!isClickInside) {
                    event.target.close();
                }
            }

            // Close any open column-filter details when clicking outside
            document.querySelectorAll('details.column-filter[open]').forEach(details => {
                if (!details.contains(event.target)) {
                    details.removeAttribute('open');
                }
            });
        });

        // Close open details or modals on Escape
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('details.column-filter[open]').forEach(details => {
                    details.removeAttribute('open');
                });
            }
        });
        // Sidebar Minimization Controller
        function toggleSidebar() {
            const shell = document.getElementById('appShell');
            const toggleBtn = document.getElementById('sidebarToggleBtn');
            if (!shell) return;
            const isCollapsed = shell.classList.toggle('sidebar-collapsed');
            document.documentElement.classList.toggle('sidebar-is-collapsed', isCollapsed);
            localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
            if (toggleBtn) {
                toggleBtn.title = isCollapsed ? 'Perluas sidebar' : 'Minimize sidebar';
                toggleBtn.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const shell = document.getElementById('appShell');
            const toggleBtn = document.getElementById('sidebarToggleBtn');
            if (localStorage.getItem('sidebar_collapsed') === 'true' && shell) {
                shell.classList.add('sidebar-collapsed');
                if (toggleBtn) {
                    toggleBtn.title = 'Perluas sidebar';
                    toggleBtn.setAttribute('aria-expanded', 'false');
                }
            }

            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.addEventListener('wheel', (e) => {
                    if (window.innerWidth > 960) {
                        e.preventDefault();
                    }
                }, { passive: false });
            }
        });
    </script>
</head>
<body>
    <div class="shell" id="appShell">
        <aside class="sidebar">
            <div>
                <!-- Brand & Workspace Tag -->
                <div class="brand-header">
                    <div class="brand-info">
                        <div class="brand-icon">
                            <x-heroicon-o-squares-2x2 class="w-5 h-5 text-white" />
                        </div>
                        <div class="brand-text">
                            <div class="brand-title">Konsulin Manager</div>
                            <div class="brand-subtitle">Jira Workspace</div>
                        </div>
                    </div>
                    <button
                        type="button"
                        id="sidebarToggleBtn"
                        class="sidebar-toggle-btn"
                        title="Minimize sidebar"
                        aria-label="Toggle minimize sidebar"
                        onclick="toggleSidebar()"
                    >
                        <x-heroicon-o-chevron-double-left class="toggle-icon w-4 h-4" />
                    </button>
                </div>

                <!-- Navigation Section -->
                <div class="nav-label">Management</div>
                <nav class="nav">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" data-tooltip="Dashboard">
                        <x-heroicon-o-chart-bar-square />
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('kanban.index') }}" class="{{ request()->routeIs('kanban.*') ? 'active' : '' }}" data-tooltip="Kanban Board">
                        <x-heroicon-o-view-columns />
                        <span>Kanban Board</span>
                    </a>
                    <a href="{{ route('projects.index') }}" class="{{ request()->routeIs('projects.*') ? 'active' : '' }}" data-tooltip="{{ auth()->check() && auth()->user()->isStaff() ? 'Proyek Saya' : 'Projects & Compliance' }}">
                        <x-heroicon-o-clipboard-document-list />
                        <span>{{ auth()->check() && auth()->user()->isStaff() ? 'Proyek Saya' : 'Projects & Compliance' }}</span>
                    </a>
                    @if(auth()->check() && !auth()->user()->isStaff())
                        <a href="{{ route('clients.index') }}" class="{{ request()->routeIs('clients.*') ? 'active' : '' }}" data-tooltip="Kelola Client">
                            <x-heroicon-o-building-office-2 />
                            <span>Kelola Client</span>
                        </a>
                    @endif
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <a href="{{ route('project-categories.index') }}" class="{{ request()->routeIs('project-categories.*') ? 'active' : '' }}" data-tooltip="Project Categories">
                            <x-heroicon-o-tag />
                            <span>Project Categories</span>
                        </a>
                        <a href="{{ route('staff.index') }}" class="{{ request()->routeIs('staff.*') ? 'active' : '' }}" data-tooltip="Staff / Employees">
                            <x-heroicon-o-user-group />
                            <span>Staff / Employees</span>
                        </a>
                    @endif
                </nav>

                <div class="nav-label" style="margin-top: 14px;">External</div>
                <nav class="nav">
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <a href="{{ route('website-content.index') }}" class="{{ request()->routeIs('website-content.*') ? 'active' : '' }}" data-tooltip="Website Content">
                            <x-heroicon-o-globe-alt />
                            <span>Website Content</span>
                        </a>
                    @endif
                    <a href="{{ route('home') }}" target="_blank" data-tooltip="Live Landing Page">
                        <x-heroicon-o-arrow-top-right-on-square />
                        <span>Live Landing Page</span>
                    </a>
                </nav>
            </div>

            <!-- Authenticated User & Logout Bar -->
            <div>
                @auth
                    <div class="user-panel">
                        <div class="user-profile-wrap flex items-center gap-2.5 overflow-hidden">
                            <div class="user-avatar" title="{{ auth()->user()->name }}">
                                {{ auth()->user()->initials }}
                            </div>
                            <div class="user-info overflow-hidden">
                                <div class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                                <div class="flex items-center gap-1 mt-0.5">
                                    @if(auth()->user()->isAdmin())
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                            Admin
                                        </span>
                                    @elseif(auth()->user()->isReviewer())
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                            Reviewer
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                            Staff
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
                    <a href="{{ route('login') }}" class="button small w-full flex items-center justify-center gap-2" data-tooltip="Login">
                        <x-heroicon-o-lock-closed class="w-4 h-4" />
                        <span class="auth-btn-text">Masuk</span>
                    </a>
                @endauth
            </div>
        </aside>

        <main class="main">
            <div class="page-transition" id="pageContent">
                <div class="sr-only" aria-live="polite">
                    @if (session('status'))
                        <p>{{ session('status') }}</p>
                    @endif
                    @if (session('success'))
                        <p>{{ session('success') }}</p>
                    @endif
                    @if (session('error'))
                        <p>{{ session('error') }}</p>
                    @endif
                    @if ($errors->any())
                        <p>{{ $errors->first() }}</p>
                    @endif
                </div>

                @if (session('status') || session('success') || session('error') || $errors->any())
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            @if (session('status'))
                                window.toast.success(@json(session('status')));
                            @elseif (session('success'))
                                window.toast.success(@json(session('success')));
                            @endif

                            @if (session('error'))
                                window.toast.error(@json(session('error')));
                            @endif

                            @if ($errors->any())
                                window.toast.error(@json($errors->first()), 'Kesalahan Input');
                            @endif
                        });
                    </script>
                @endif

                {{ $slot }}
                <x-task-timer-widget />
            </div>
        </main>
    </div>
</body>
</html>
