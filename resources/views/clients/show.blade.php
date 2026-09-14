<x-layouts.app :title="$client->name . ' : Detail Client & Matriks Kepatuhan'">
    <div class="topbar flex-wrap">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('clients.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 flex items-center gap-1">
                    <x-heroicon-o-arrow-left class="w-3.5 h-3.5" />
                    <span>Kembali ke Daftar Client</span>
                </a>
                @if($client->client_code)
                    <span class="font-mono text-xs font-bold text-slate-700 bg-slate-200/80 px-2 py-0.5 rounded">
                        {{ $client->client_code }}
                    </span>
                @endif
                <span class="label {{ strtolower($client->status) === 'active' ? 'success' : 'navy' }} text-xs">
                    {{ ucfirst($client->status) }}
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">{{ $client->name }}</h1>
            <p class="muted text-xs sm:text-sm mt-0.5">
                {{ $client->client_type ?? 'Badan Usaha' }} • {{ $client->business_type ?? 'Jasa / Dagang' }} • {{ $client->location ?? 'Indonesia' }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('clients.edit', $client) }}" class="button small">
                <x-heroicon-o-pencil-square class="w-4 h-4" />
                <span>Edit Data & Matriks</span>
            </a>
            @if($client->files)
                <a href="{{ $client->files }}" target="_blank" class="button small secondary" title="Buka Cloud Files">
                    <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4" />
                    <span>Buka Berkas</span>
                </a>
            @endif
        </div>
    </div>

    <!-- CLIENT KPI CARDS -->
    <section class="stats" data-animate-children>
        <div class="stat">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status Kontrak</span>
                <div class="w-8 h-8 rounded-lg bg-[#0b192c]/5 text-[#0b192c] flex items-center justify-center">
                    <x-heroicon-o-document-check class="w-4 h-4" />
                </div>
            </div>
            <strong>{{ $client->contract_status ?? 'Active' }}</strong>
            <span class="text-xs text-slate-500">
                Durasi: {{ $client->contract_duration_months ? $client->contract_duration_months . ' Bulan' : '-' }}
            </span>
        </div>

        <div class="stat">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Jatuh Tempo Kontrak</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
                    <x-heroicon-o-calendar-days class="w-4 h-4" />
                </div>
            </div>
            <strong>{{ $client->end_contract_due_date ? $client->end_contract_due_date->format('d M Y') : '-' }}</strong>
            <span class="text-xs text-blue-600 font-medium">
                Mulai: {{ $client->start_date ? $client->start_date->format('d/m/Y') : '-' }}
            </span>
        </div>

        <div class="stat">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status Pajak</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
                    <x-heroicon-o-receipt-percent class="w-4 h-4" />
                </div>
            </div>
            <strong>{{ $client->tax_status ?? 'Non-PKP' }}</strong>
            <span class="text-xs text-slate-500 truncate block">
                NPWP: {{ $client->tax_id ?? 'Belum ada NPWP' }}
            </span>
        </div>

        <div class="stat">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Review & Approval</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <x-heroicon-o-shield-check class="w-4 h-4" />
                </div>
            </div>
            <strong class="text-emerald-700">{{ $client->review_approval ?? 'Approved' }}</strong>
            <span class="text-xs text-slate-500">Kesesuaian SLA layanan</span>
        </div>
    </section>

    <!-- METADATA INFORMATION BAR -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- PIC Card -->
        <div class="bg-white border border-slate-200/90 rounded-xl p-4 shadow-sm">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Kontak & PIC Client</div>
            <div class="space-y-1.5 text-xs text-slate-700">
                <div class="flex justify-between">
                    <span class="text-slate-500">PIC Utama:</span>
                    <span class="font-semibold text-slate-900">{{ $client->client_pic ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Lokasi:</span>
                    <span class="font-medium text-slate-800">{{ $client->location ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Email:</span>
                    <span class="font-medium text-slate-800">{{ $client->email ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Telepon:</span>
                    <span class="font-medium text-slate-800">{{ $client->phone ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Packages Card -->
        <div class="bg-white border border-slate-200/90 rounded-xl p-4 shadow-sm">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Paket Layanan Terdaftar</div>
            <div class="space-y-1.5 text-xs text-slate-700">
                <div class="flex justify-between">
                    <span class="text-slate-500">Finance:</span>
                    <span class="font-semibold text-slate-900">{{ $client->finance_package ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Tax Package:</span>
                    <span class="font-semibold text-slate-900">{{ $client->tax_package ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Add-On:</span>
                    <span class="font-medium text-slate-800">{{ $client->addon ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Migrasi:</span>
                    <span class="font-medium text-slate-800">{{ $client->migration_date ? $client->migration_date->format('d/m/Y') : '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Internal PIC Card -->
        <div class="bg-white border border-slate-200/90 rounded-xl p-4 shadow-sm">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tim Konsulin Bertugas</div>
            <div class="space-y-1.5 text-xs text-slate-700">
                <div class="flex justify-between">
                    <span class="text-slate-500">Tax PIC:</span>
                    <span class="font-semibold text-blue-800">{{ $client->tax_pic ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Accounting PIC:</span>
                    <span class="font-semibold text-indigo-800">{{ $client->accounting_pic ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Proyek Berjalan:</span>
                    <span class="font-bold text-slate-900">{{ $client->projects->count() }} Proyek Jira</span>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN SECTION: SPREADSHEET MATRIX KEPATUHAN PAJAK & AKUNTANSI (MAR 26 - DEC 26) -->
    <section class="panel p-5 bg-white border border-slate-200/90 rounded-xl mb-6 shadow-sm">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 flex-wrap gap-2">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                    <h2 class="text-base font-bold text-slate-900 m-0">Matriks Kepatuhan Pajak & Akuntansi (Mar 26 – Dec 26)</h2>
                </div>
                <p class="text-xs text-slate-500 m-0 mt-0.5">
                    Tabel pelaporan periodik bulanan untuk kepatuhan PPh 21, PPh Unifikasi, PPN, PP 55, PPh 25, Laporan Keuangan, dan Pending Notes.
                </p>
            </div>
            <a href="{{ route('clients.edit', $client) }}" class="button small secondary">
                <x-heroicon-o-pencil class="w-3.5 h-3.5" />
                <span>Ubah Status Matriks</span>
            </a>
        </div>

        <div class="overflow-x-auto border border-slate-200 rounded-lg">
            <table class="w-full text-xs text-left border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 font-semibold text-slate-600">
                    <tr>
                        <th class="p-3 w-28">Periode</th>
                        <th class="p-3">PPh 21</th>
                        <th class="p-3">PPh Unifikasi</th>
                        <th class="p-3">PPN</th>
                        <th class="p-3">PP 55</th>
                        <th class="p-3">PPh 25</th>
                        <th class="p-3">Laporan Keuangan (LK)</th>
                        <th class="p-3 min-w-[220px]">Pending / Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($periods as $period)
                        @php
                            $comp = $compliancesMap[$period] ?? null;
                            $renderBadge = function($val) {
                                if (! $val || $val === '-' || strtolower($val) === 'n/a') {
                                    return '<span class="text-slate-400 font-normal">-</span>';
                                }
                                $lower = strtolower($val);
                                if (str_contains($lower, 'done') || str_contains($lower, 'selesai') || str_contains($lower, 'nihil') || str_contains($lower, 'final')) {
                                    return '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">✓ ' . e($val) . '</span>';
                                }
                                if (str_contains($lower, 'pending') || str_contains($lower, 'tunda') || str_contains($lower, 'kurang')) {
                                    return '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">! ' . e($val) . '</span>';
                                }
                                return '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">' . e($val) . '</span>';
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="p-3 font-bold text-slate-900 bg-slate-50/60 whitespace-nowrap border-r border-slate-100">
                                {{ $period }}
                            </td>
                            <td class="p-3 whitespace-nowrap">{!! $renderBadge($comp?->pph_21) !!}</td>
                            <td class="p-3 whitespace-nowrap">{!! $renderBadge($comp?->pph_unifikasi) !!}</td>
                            <td class="p-3 whitespace-nowrap">{!! $renderBadge($comp?->ppn) !!}</td>
                            <td class="p-3 whitespace-nowrap">{!! $renderBadge($comp?->pp_55) !!}</td>
                            <td class="p-3 whitespace-nowrap">{!! $renderBadge($comp?->pph_25) !!}</td>
                            <td class="p-3 whitespace-nowrap">{!! $renderBadge($comp?->lk) !!}</td>
                            <td class="p-3 text-slate-600">
                                {{ $comp?->notes ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <!-- SECTION: PROYEK JIRA TERKAIT -->
    @if($client->projects->isNotEmpty())
        <section class="panel p-5 bg-white border border-slate-200/90 rounded-xl mb-6 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-base font-bold text-slate-900 m-0">Proyek & Board Terkait Client</h2>
                    <p class="text-xs text-slate-500 m-0 mt-0.5">Daftar pengerjaan tugas di Jira Workspace.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($client->projects as $p)
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 hover:bg-slate-50 transition">
                        <div class="flex items-center justify-between mb-2">
                            <span class="label navy text-xs">{{ $p->service_type }}</span>
                            <span class="text-xs text-slate-500">Due: {{ $p->due_date?->format('d M Y') ?? '-' }}</span>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900 m-0">
                            <a href="{{ route('projects.show', $p) }}" class="hover:underline text-[#0b192c]">
                                {{ $p->name }}
                            </a>
                        </h3>
                        <div class="mt-3 flex items-center justify-between text-xs">
                            <span class="text-slate-600">Progres: <strong>{{ $p->progressPercent() }}%</strong></span>
                            <a href="{{ route('projects.show', $p) }}" class="font-semibold text-navy-700 hover:underline">
                                Buka Jira Board →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
