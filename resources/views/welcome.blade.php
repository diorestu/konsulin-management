<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $contents['hero']->title ?? 'Konsulin Manager : Sistem Operasional & Manajemen Proyek' }}</title>
    <meta name="description" content="{{ $contents['hero']->body ?? 'Sistem manajemen proyek dan operasional terintegrasi khusus untuk kantor konsultan pajak, akuntansi, dan corporate advisory.' }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }
    </style>
</head>
<body class="selection:bg-[#1e3e62] selection:text-white">
    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200/80">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-[#0b192c] text-white flex items-center justify-center shadow-xs">
                    <x-heroicon-o-squares-2x2 class="w-5 h-5" />
                </div>
                <div>
                    <span class="font-bold text-slate-900 text-sm tracking-tight block leading-tight">Konsulin Manager</span>
                    <span class="text-[10px] font-mono text-slate-500 uppercase tracking-wider block">Internal Workspace</span>
                </div>
            </div>

            <nav class="hidden md:flex items-center gap-6 text-xs font-medium text-slate-600">
                <a href="#overview" class="hover:text-slate-900 transition-colors">Fungsi Sistem</a>
                <a href="#roles" class="hover:text-slate-900 transition-colors">Arsitektur Role</a>
                <a href="#modules" class="hover:text-slate-900 transition-colors">Modul Operasional</a>
                <a href="#workflow" class="hover:text-slate-900 transition-colors">Alur Kerja</a>
                <a href="#demo-access" class="hover:text-slate-900 transition-colors">Akses Demo</a>
            </nav>

            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#0b192c] hover:bg-[#1e3e62] text-white text-xs font-semibold shadow-xs transition">
                        <span>Buka Dashboard</span>
                        <x-heroicon-o-arrow-right class="w-3.5 h-3.5" />
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#0b192c] hover:bg-[#1e3e62] text-white text-xs font-semibold shadow-xs transition">
                        <x-heroicon-o-lock-closed class="w-3.5 h-3.5 text-slate-300" />
                        <span>Masuk Workspace</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        <!-- Hero Section : Pragmatic & System-focused -->
        <section id="overview" class="pt-14 pb-16 border-b border-slate-200 bg-white">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 text-center">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold mb-6">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    <span>Platform Operasional Internal: Konsultan Pajak, Akuntansi & Advisory</span>
                </div>

                <h1 class="text-3xl sm:text-5xl font-bold tracking-tight text-slate-950 max-w-3xl mx-auto leading-tight sm:leading-tight">
                    {{ $contents['hero']->title ?? 'Sistem Manajemen Proyek & Operasional Konsultasi.' }}
                </h1>

                <p class="mt-5 text-sm sm:text-base text-slate-600 max-w-2xl mx-auto leading-relaxed">
                    {{ $contents['hero']->body ?? 'Konsulin Manager adalah platform internal terintegrasi untuk mengelola siklus penugasan klien, kepatuhan pajak berkala, mitigasi ancaman operasional, dan pelacakan jam kerja konsultan secara presisi.' }}
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#0b192c] hover:bg-[#1e3e62] text-white text-xs sm:text-sm font-semibold shadow-sm transition">
                        <span>Masuk ke Workspace</span>
                        <x-heroicon-o-arrow-right class="w-4 h-4" />
                    </a>
                    <a href="#roles" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs sm:text-sm font-semibold border border-slate-200 transition">
                        <span>Pelajari Arsitektur Role</span>
                    </a>
                </div>

                <!-- 4 Operational Value Pillars (Strictly Functional) -->
                <div class="mt-14 grid grid-cols-2 md:grid-cols-4 gap-3 text-left">
                    <div class="p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/60">
                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center mb-2">
                            <x-heroicon-o-view-columns class="w-4 h-4" />
                        </div>
                        <div class="text-xs font-bold text-slate-900">Jira-Grade Kanban</div>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">Siklus Todo, In Progress, Review, dan Selesai per proyek klien.</p>
                    </div>

                    <div class="p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/60">
                        <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center mb-2">
                            <x-heroicon-o-clipboard-document-check class="w-4 h-4" />
                        </div>
                        <div class="text-xs font-bold text-slate-900">Matriks Kepatuhan Pajak</div>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">Audit bulanan PPh 21, Unifikasi, PPN, dan LK untuk kepatuhan regulasi.</p>
                    </div>

                    <div class="p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/60">
                        <div class="w-7 h-7 rounded-lg bg-rose-50 text-rose-700 flex items-center justify-center mb-2">
                            <x-heroicon-o-shield-exclamation class="w-4 h-4" />
                        </div>
                        <div class="text-xs font-bold text-slate-900">Radar Risiko & Threats</div>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">Deteksi dini kendala operasional dengan klasifikasi Critical hingga Low.</p>
                    </div>

                    <div class="p-3.5 rounded-xl border border-slate-200/80 bg-slate-50/60">
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center mb-2">
                            <x-heroicon-o-clock class="w-4 h-4" />
                        </div>
                        <div class="text-xs font-bold text-slate-900">Always-on-Top Tracker</div>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">Stopwatch terapung di atas layar (Web PiP & Desktop App Mac/Windows).</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 2: Role Architecture & Segregation of Duties -->
        <section id="roles" class="py-16 border-b border-slate-200 bg-slate-50/50">
            <div class="max-w-5xl mx-auto px-4 sm:px-6">
                <div class="text-center max-w-2xl mx-auto mb-10">
                    <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Arsitektur Pengguna</span>
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 mt-1">
                        Pemisahan Wewenang Berdasarkan 3 Role Resmi
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-2">
                        Setiap role memiliki antarmuka, hak akses data, dan tanggung jawab yang terisolasi secara aman.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <!-- 1. Admin Role -->
                    <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-[#0b192c] text-white">
                                    Role Admin
                                </span>
                                <span class="text-[10px] font-mono text-slate-400">Partner & Manajemen</span>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Tata Kelola & Kontrol Sistem</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Bertanggung jawab atas pengelolaan portofolio bisnis, alokasi sumber daya konsultan, dan integritas data kantor.
                            </p>
                            <ul class="mt-4 space-y-2 text-xs text-slate-700">
                                <li class="flex items-start gap-2">
                                    <x-heroicon-s-check class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                                    <span>Akses penuh seluruh 8 menu sistem dan konfigurasi kategori.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <x-heroicon-s-check class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                                    <span>Manajemen data klien, kontrak, dan matriks kepatuhan.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <x-heroicon-s-check class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                                    <span>Monitoring beban kerja konsultan PIC Tax dan Accounting.</span>
                                </li>
                            </ul>
                        </div>
                        <div class="mt-5 pt-3 border-t border-slate-100 text-[11px] text-slate-400 font-mono">
                            Demo: admin@konsulin.test
                        </div>
                    </div>

                    <!-- 2. Reviewer Role -->
                    <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-purple-100 text-purple-900 border border-purple-200">
                                    Role Reviewer
                                </span>
                                <span class="text-[10px] font-mono text-slate-400">Supervisor & QA</span>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Quality Assurance & Audit Risiko</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Memvalidasi kualitas deliverable, kertas kerja konsultan, dan kepatuhan sebelum laporan disampaikan ke klien.
                            </p>
                            <ul class="mt-4 space-y-2 text-xs text-slate-700">
                                <li class="flex items-start gap-2">
                                    <x-heroicon-s-check class="w-4 h-4 text-purple-600 shrink-0 mt-0.5" />
                                    <span>Supervisi deliverable proyek pada status Review.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <x-heroicon-s-check class="w-4 h-4 text-purple-600 shrink-0 mt-0.5" />
                                    <span>Eskalasi dan penyelesaian kendala aktif (Threats Matrix).</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <x-heroicon-s-check class="w-4 h-4 text-purple-600 shrink-0 mt-0.5" />
                                    <span>Verifikasi pemenuhan batas waktu SPT dan laporan keuangan.</span>
                                </li>
                            </ul>
                        </div>
                        <div class="mt-5 pt-3 border-t border-slate-100 text-[11px] text-slate-400 font-mono">
                            Demo: reviewer@konsulin.test
                        </div>
                    </div>

                    <!-- 3. Staff Role -->
                    <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-blue-100 text-blue-900 border border-blue-200">
                                    Role Staff
                                </span>
                                <span class="text-[10px] font-mono text-slate-400">Konsultan Pelaksana</span>
                            </div>
                            <h3 class="text-base font-bold text-slate-900">Eksekusi Tugas & Time Tracker</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                Antarmuka khusus Staff Workbench tanpa menu administratif, berfokus menyelesaikan penugasan tugas aktif.
                            </p>
                            <ul class="mt-4 space-y-2 text-xs text-slate-700">
                                <li class="flex items-start gap-2">
                                    <x-heroicon-s-check class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                                    <span>Hanya melihat proyek dan tugas yang di-assign untuknya.</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <x-heroicon-s-check class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                                    <span>Lacak waktu kerja Always-on-Top (Web PiP & Desktop App).</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <x-heroicon-s-check class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                                    <span>Pembaruan status tugas (Todo ke In Progress ke Review).</span>
                                </li>
                            </ul>
                        </div>
                        <div class="mt-5 pt-3 border-t border-slate-100 text-[11px] text-slate-400 font-mono">
                            Demo: rafi@konsulin.test
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 3: Core Functional Modules -->
        <section id="modules" class="py-16 border-b border-slate-200 bg-white">
            <div class="max-w-5xl mx-auto px-4 sm:px-6">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Modul Operasional</span>
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 mt-1">
                        {{ $contents['services']->title ?? 'Fungsi & Arsitektur Sistem.' }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-2">
                        {{ $contents['services']->body ?? 'Dirancang dengan presisi gaya Jira untuk menyelaraskan alur kerja antara Partner (Admin), Supervisor (Reviewer), dan Pelaksana (Staff).' }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Module 1 -->
                    <div class="p-6 rounded-2xl border border-slate-200 bg-slate-50/40">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-[#0b192c] text-white flex items-center justify-center">
                                <x-heroicon-o-view-columns class="w-4 h-4" />
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">1. Manajemen Proyek & Kanban Board</h3>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Papan visual memetakan tahapan tugas dari Todo, In Progress, Review, hingga Done. Setiap kartu memuat data prioritas, tanggal tenggat, PIC yang ditugaskan, dan deliverable spesifik.
                        </p>
                        <div class="mt-4 p-3 bg-white rounded-lg border border-slate-200/80 text-[11px] text-slate-500">
                            <strong>Fitur Terkandung:</strong> Filter interaktif berdasarkan status, drag & drop tahapan, dan kalkulasi otomatis persentase kemajuan sprint.
                        </div>
                    </div>

                    <!-- Module 2 -->
                    <div class="p-6 rounded-2xl border border-slate-200 bg-slate-50/40">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-amber-600 text-white flex items-center justify-center">
                                <x-heroicon-o-building-office-2 class="w-4 h-4" />
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">2. Portofolio Klien & Matriks Kepatuhan</h3>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Tabel kepatuhan terstruktur untuk mengontrol pelaporan PPh 21, PPh Unifikasi, PPN, dan Laporan Keuangan per masa pajak. Memastikan tidak ada tenggat DJP yang terlewatkan.
                        </p>
                        <div class="mt-4 p-3 bg-white rounded-lg border border-slate-200/80 text-[11px] text-slate-500">
                            <strong>Fitur Terkandung:</strong> Pemetaan masa pajak (Jan 26 - Jun 26), status PKP/Non-PKP, audit data migrasi, dan paket layanan aktif.
                        </div>
                    </div>

                    <!-- Module 3 -->
                    <div class="p-6 rounded-2xl border border-slate-200 bg-slate-50/40">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-rose-600 text-white flex items-center justify-center">
                                <x-heroicon-o-shield-exclamation class="w-4 h-4" />
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">3. Radar Ancaman & Eskalasi Risiko (Threats Matrix)</h3>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Mekanisme pencatatan kendala (Blockers) yang berpotensi menghambat penyelesaian proyek. Ancaman diklasifikasikan berdasarkan tingkat keparahan (Critical, High, Medium, Low).
                        </p>
                        <div class="mt-4 p-3 bg-white rounded-lg border border-slate-200/80 text-[11px] text-slate-500">
                            <strong>Fitur Terkandung:</strong> Log riwayat eskalasi risiko, pelapor kendala, status penanganan, dan tindakan mitigasi transparan.
                        </div>
                    </div>

                    <!-- Module 4 -->
                    <div class="p-6 rounded-2xl border border-slate-200 bg-slate-50/40">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center">
                                <x-heroicon-o-computer-desktop class="w-4 h-4" />
                            </div>
                            <h3 class="text-sm font-bold text-slate-900">4. Always-on-Top Work Time Tracker</h3>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Fitur pencatatan waktu kerja staf yang dapat dioperasikan sebagai floating window (Picture-in-Picture di web browser) maupun aplikasi desktop mandiri untuk sistem operasi Mac dan Windows.
                        </p>
                        <div class="mt-4 p-3 bg-white rounded-lg border border-slate-200/80 text-[11px] text-slate-500">
                            <strong>Fitur Terkandung:</strong> Tampilan stopwatch selalu di atas layar (*always-on-top*), auto-sync ke database, dan akumulasi jam kerja harian/mingguan.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 4: End-to-End Operational Workflow -->
        <section id="workflow" class="py-16 border-b border-slate-200 bg-slate-50/50">
            <div class="max-w-5xl mx-auto px-4 sm:px-6">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <span class="text-xs font-bold text-blue-700 uppercase tracking-wider">Standar Operasional</span>
                    <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 mt-1">
                        Siklus Alur Kerja Konsultasi
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-2">
                        Alur terstruktur dari pendaftaran klien hingga penyerahan laporan resmi.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                    <div class="p-4 rounded-xl bg-white border border-slate-200">
                        <div class="text-xs font-bold font-mono text-blue-600 mb-1">01</div>
                        <h4 class="text-xs font-bold text-slate-900">Intake Klien</h4>
                        <p class="text-[11px] text-slate-500 mt-1">Registrasi data perusahaan, nomor NPWP, status PKP, dan jadwal kepatuhan.</p>
                    </div>
                    <div class="p-4 rounded-xl bg-white border border-slate-200">
                        <div class="text-xs font-bold font-mono text-blue-600 mb-1">02</div>
                        <h4 class="text-xs font-bold text-slate-900">Setup Proyek</h4>
                        <p class="text-[11px] text-slate-500 mt-1">Admin menentukan deadline, memecah tugas, serta menunjuk PIC dan Reviewer.</p>
                    </div>
                    <div class="p-4 rounded-xl bg-white border border-slate-200">
                        <div class="text-xs font-bold font-mono text-blue-600 mb-1">03</div>
                        <h4 class="text-xs font-bold text-slate-900">Eksekusi Tugas</h4>
                        <p class="text-[11px] text-slate-500 mt-1">Staff konsultan menjalankan tugas dengan stopwatch Always-on-Top aktif.</p>
                    </div>
                    <div class="p-4 rounded-xl bg-white border border-slate-200">
                        <div class="text-xs font-bold font-mono text-blue-600 mb-1">04</div>
                        <h4 class="text-xs font-bold text-slate-900">Quality Review</h4>
                        <p class="text-[11px] text-slate-500 mt-1">Reviewer memvalidasi kertas kerja dan menyelesaikan kendala yang timbul.</p>
                    </div>
                    <div class="p-4 rounded-xl bg-white border border-slate-200">
                        <div class="text-xs font-bold font-mono text-blue-600 mb-1">05</div>
                        <h4 class="text-xs font-bold text-slate-900">Penyelesaian</h4>
                        <p class="text-[11px] text-slate-500 mt-1">Laporan final diterbitkan ke klien dan status kepatuhan terarsip di sistem.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 5: Demo Credentials & Direct Access -->
        <section id="demo-access" class="py-16 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6">
                <div class="p-8 sm:p-10 rounded-2xl bg-[#0b192c] text-white shadow-md">
                    <div class="text-center max-w-xl mx-auto mb-8">
                        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight">
                            {{ $contents['contact']->title ?? 'Akses Workspace Konsulin Manager' }}
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-300 mt-2">
                            {{ $contents['contact']->body ?? 'Gunakan kredensial resmi kantor atau akun demo untuk mengevaluasi fitur manajemen proyek sesuai peran Anda.' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-8">
                        <!-- Admin Demo Card -->
                        <div class="p-3.5 rounded-xl bg-slate-800/80 border border-slate-700">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    Admin
                                </span>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            </div>
                            <div class="text-xs font-bold text-white">Dewi Admin</div>
                            <div class="text-[11px] text-slate-300 font-mono mt-0.5">admin@konsulin.test</div>
                            <div class="text-[10px] text-slate-400 mt-2 pt-2 border-t border-slate-700">
                                Akses penuh manajemen & klien
                            </div>
                        </div>

                        <!-- Reviewer Demo Card -->
                        <div class="p-3.5 rounded-xl bg-slate-800/80 border border-slate-700">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                    Reviewer
                                </span>
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-400"></span>
                            </div>
                            <div class="text-xs font-bold text-white">Nadia Reviewer</div>
                            <div class="text-[11px] text-slate-300 font-mono mt-0.5">reviewer@konsulin.test</div>
                            <div class="text-[10px] text-slate-400 mt-2 pt-2 border-t border-slate-700">
                                Supervisi QA & approval deliverable
                            </div>
                        </div>

                        <!-- Staff Demo Card -->
                        <div class="p-3.5 rounded-xl bg-slate-800/80 border border-slate-700">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                    Staff
                                </span>
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                            </div>
                            <div class="text-xs font-bold text-white">Rafi Staff</div>
                            <div class="text-[11px] text-slate-300 font-mono mt-0.5">rafi@konsulin.test</div>
                            <div class="text-[10px] text-slate-400 mt-2 pt-2 border-t border-slate-700">
                                Workbench tugas & Time Tracker
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-slate-800">
                        <span class="text-xs text-slate-400">Password default semua akun: <code class="text-white bg-slate-800 px-1.5 py-0.5 rounded font-mono text-xs">password</code></span>
                        <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-white hover:bg-slate-100 text-[#0b192c] text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer">
                            <span>{{ $contents['contact']->button_text ?? 'Buka Halaman Login' }}</span>
                            <x-heroicon-o-arrow-right class="w-4 h-4" />
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="py-8 bg-slate-900 text-slate-400 border-t border-slate-800 text-xs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="font-bold text-white">Konsulin Manager</span>
                <span>·</span>
                <span>Jira-Style Consulting & Compliance Workspace</span>
            </div>
            <div class="text-slate-500 text-[11px]">
                Versi 1.2 · Khusus Operasional Internal Kantor Konsultan
            </div>
        </div>
    </footer>
</body>
</html>
