<x-layouts.app title="Staff / Employees - Konsulin Manager">
    <div class="topbar flex-wrap">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">Staff / Employees</h1>
            <p class="muted text-xs sm:text-sm mt-0.5">
                Direktori konsultan, alokasi beban kerja aktif, dan distribusi portofolio klien per anggota tim.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button class="button" type="button" data-open-staff-modal="create">
                <x-heroicon-o-plus class="w-4 h-4 mr-1.5" />
                <span>New Staff</span>
            </button>
        </div>
    </div>

    <!-- Ringkasan Beban Kerja & Portofolio Tim -->
    <div class="overflow-x-auto pb-1 mb-5 -mx-1 px-1">
        <section class="stats stats-row-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 min-w-0" data-animate-children>
            <!-- Card 1: Total Tim Staff -->
            <div class="stat !p-4 !rounded-xl border border-slate-200/90 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-2 mb-1.5">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider truncate">Total Tim Staff</span>
                    <div class="w-7 h-7 rounded-lg bg-[#0b192c]/5 text-[#0b192c] flex items-center justify-center shrink-0">
                        <x-heroicon-o-users class="w-4 h-4" />
                    </div>
                </div>
                <strong class="text-2xl font-bold text-slate-900 block leading-tight">{{ $totalStaffCount }}</strong>
                <span class="text-[11px] text-slate-500 font-medium truncate block mt-1">
                    <span class="text-emerald-700 font-semibold">{{ $activeStaffCount }} aktif</span> &bull; {{ $totalStaffCount - $activeStaffCount }} non-aktif
                </span>
            </div>

            <!-- Card 2: Klien Ditangani -->
            <div class="stat !p-4 !rounded-xl border border-slate-200/90 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-2 mb-1.5">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider truncate">Klien Ditangani</span>
                    <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                        <x-heroicon-o-building-office-2 class="w-4 h-4" />
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <strong class="text-2xl font-bold text-blue-700 block leading-tight">{{ $totalClientsHandled }}</strong>
                    <span class="text-xs text-slate-500 font-medium">/ {{ $totalClientsCount }} Klien</span>
                </div>
                <span class="text-[11px] text-slate-500 font-medium truncate block mt-1">
                    {{ round(($totalClientsHandled / max(1, $totalClientsCount)) * 100) }}% portofolio terpegang PIC staf
                </span>
            </div>

            <!-- Card 3: Total Beban Proyek Aktif -->
            <div class="stat !p-4 !rounded-xl border border-slate-200/90 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-2 mb-1.5">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider truncate">Beban Proyek Aktif</span>
                    <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center shrink-0">
                        <x-heroicon-o-briefcase class="w-4 h-4" />
                    </div>
                </div>
                <div class="flex items-baseline gap-1.5">
                    <strong class="text-2xl font-bold text-slate-900 block leading-tight">{{ $totalActiveAssignments }}</strong>
                    <span class="text-xs text-slate-500 font-medium">Tugas Berjalan</span>
                </div>
                <span class="text-[11px] text-slate-500 font-medium truncate block mt-1">
                    Rata-rata {{ $avgActiveProjects }} proyek / staf aktif
                </span>
            </div>

            <!-- Card 4: Distribusi Kapasitas Tim -->
            <div class="stat !p-4 !rounded-xl border border-slate-200/90 bg-white shadow-xs">
                <div class="flex items-center justify-between gap-2 mb-1.5">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider truncate">Kapasitas Tim</span>
                    <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                        <x-heroicon-o-scale class="w-4 h-4" />
                    </div>
                </div>
                <div class="flex items-center gap-1.5 flex-wrap my-1">
                    @if($overloadCount > 0)
                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200" title="{{ $overloadCount }} staf memegang 6+ proyek">
                            {{ $overloadCount }} Overload
                        </span>
                    @endif
                    @if($heavyCount > 0)
                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200" title="{{ $heavyCount }} staf memegang 4-5 proyek">
                            {{ $heavyCount }} Padat
                        </span>
                    @endif
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200" title="{{ $optimalCount }} staf memegang 1-3 proyek">
                        {{ $optimalCount }} Optimal
                    </span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200" title="{{ $availableCount }} staf siap menerima proyek baru">
                        {{ $availableCount }} Siap
                    </span>
                </div>
                <span class="text-[11px] text-slate-500 font-medium truncate block">
                    {{ $availableCount }} staf siap menerima penugasan klien baru
                </span>
            </div>
        </section>
    </div>

    <!-- Datatable Staff -->
    <x-datatable
        id="staff"
        :columns="[
            'name' => ['label' => 'Staff / Konsultan', 'sortable' => true],
            'type' => ['label' => 'Departemen', 'sortable' => true, 'info' => 'Spesialisasi divisi tim'],
            'workload' => ['label' => 'Beban Kerja', 'sortable' => true, 'info' => 'Proyek aktif vs riwayat selesai & status kapasitas'],
            'clients' => ['label' => 'Klien Ditangani', 'sortable' => true, 'info' => 'Jumlah portofolio klien terhubung'],
            'contact' => ['label' => 'Kontak', 'sortable' => false],
            'status' => ['label' => 'Status', 'sortable' => true, 'sorted' => true, 'direction' => 'desc'],
            'actions' => ['label' => 'Aksi', 'sortable' => false, 'align' => 'right'],
        ]"
        searchPlaceholder="Cari nama staff, klien, posisi, atau email..."
    >
        <x-slot:toolbar>
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Departemen Filter -->
                <div class="relative">
                    <label for="filterDepartment" class="sr-only">Filter Departemen</label>
                    <select id="filterDepartment" class="h-9 pr-7 pl-3 py-1.5 border border-slate-200 rounded-lg bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold cursor-pointer select-none shadow-2xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                        <option value="">Semua Departemen</option>
                        @foreach ($types as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Beban Kerja Filter -->
                <div class="relative">
                    <label for="filterWorkload" class="sr-only">Filter Beban Kerja</label>
                    <select id="filterWorkload" class="h-9 pr-7 pl-3 py-1.5 border border-slate-200 rounded-lg bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold cursor-pointer select-none shadow-2xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                        <option value="">Semua Kapasitas</option>
                        <option value="available">Tersedia (0 Proyek Aktif)</option>
                        <option value="optimal">Optimal (1-3 Proyek Aktif)</option>
                        <option value="heavy">Beban Tinggi (4-5 Proyek Aktif)</option>
                        <option value="overload">Overload (6+ Proyek Aktif)</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="relative">
                    <label for="filterStatus" class="sr-only">Filter Status</label>
                    <select id="filterStatus" class="h-9 pr-7 pl-3 py-1.5 border border-slate-200 rounded-lg bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold cursor-pointer select-none shadow-2xs focus:outline-none focus:ring-1 focus:ring-slate-400">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Non-aktif</option>
                    </select>
                </div>
            </div>
        </x-slot:toolbar>

        @forelse ($staff as $employee)
            @php
                $initials = collect(explode(' ', trim($employee->name)))
                    ->map(fn($part) => strtoupper(substr($part, 0, 1)))
                    ->take(2)
                    ->implode('');

                $deptBadgeStyles = [
                    'tax' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'accounting' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'legal' => 'bg-amber-50 text-amber-800 border-amber-200',
                    'marketing' => 'bg-purple-50 text-purple-700 border-purple-200',
                    'it' => 'bg-cyan-50 text-cyan-800 border-cyan-200',
                ];
                $deptBadge = $deptBadgeStyles[$employee->type] ?? 'bg-slate-100 text-slate-700 border-slate-200';

                $fillPercent = min(100, max(0, round(($employee->active_projects_count / 6) * 100)));
                $barColor = match($employee->workload_status) {
                    'overload' => 'bg-rose-500',
                    'heavy' => 'bg-amber-500',
                    'optimal' => 'bg-emerald-500',
                    default => 'bg-slate-300',
                };
            @endphp
            <tr
                data-row
                data-name="{{ $employee->name }}"
                data-type="{{ $employee->type }}"
                data-email="{{ $employee->email }}"
                data-phone="{{ $employee->phone }}"
                data-position="{{ $employee->position }}"
                data-workload="{{ $employee->active_projects_count }}"
                data-projects="{{ $employee->active_projects_count }}"
                data-clients="{{ $employee->clients_count }}"
                data-workload-status="{{ $employee->workload_status }}"
                data-status="{{ $employee->is_active ? 'active' : 'inactive' }}"
                data-portfolio="{{ json_encode($employee->portfolio_data) }}"
                class="hover:bg-slate-50/80 transition-colors"
            >
                <!-- Kolom Staff / Konsultan -->
                <td data-column="name" class="py-3 px-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-[#0b192c]/5 border border-slate-200 text-[#0b192c] flex items-center justify-center font-bold text-xs shrink-0 select-none">
                            {{ $initials ?: 'ST' }}
                        </div>
                        <div class="min-w-0">
                            <button
                                type="button"
                                class="font-semibold text-slate-900 text-sm hover:text-blue-700 transition-colors text-left block truncate max-w-[200px]"
                                data-open-portfolio-btn
                                title="Lihat detail beban kerja {{ $employee->name }}"
                            >
                                {{ $employee->name }}
                            </button>
                            <div class="text-xs text-slate-500 font-medium truncate max-w-[200px]">
                                {{ $employee->position ?? 'Konsultan' }}
                            </div>
                        </div>
                    </div>
                </td>

                <!-- Kolom Departemen -->
                <td data-column="type" class="py-3 px-4">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $deptBadge }} uppercase tracking-wider">
                        {{ $employee->type }}
                    </span>
                </td>

                <!-- Kolom Beban Kerja -->
                <td data-column="workload" class="py-3 px-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $employee->workload_badge_class }}">
                                {{ $employee->workload_label }} ({{ $employee->active_projects_count }})
                            </span>
                        </div>
                        <!-- Mini meter kapasitas -->
                        <div class="w-28 h-1.5 rounded-full bg-slate-100 overflow-hidden" title="Kapasitas aktif: {{ $employee->active_projects_count }} proyek">
                            <div class="h-full {{ $barColor }} transition-all duration-300" style="width: {{ $fillPercent }}%"></div>
                        </div>
                        <div class="text-[11px] text-slate-500">
                            <strong class="text-slate-800">{{ $employee->active_projects_count }}</strong> aktif &bull; {{ $employee->completed_projects_count }} selesai
                        </div>
                    </div>
                </td>

                <!-- Kolom Klien Ditangani -->
                <td data-column="clients" class="py-3 px-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-1.5">
                            <strong class="text-sm font-bold text-slate-900">{{ $employee->clients_count }}</strong>
                            <span class="text-xs text-slate-500 font-medium">Klien</span>
                        </div>
                        @if($employee->clients_count > 0)
                            <div class="flex flex-wrap items-center gap-1 max-w-[220px]">
                                @foreach($employee->handled_clients->take(2) as $client)
                                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-700 border border-slate-200/80 truncate max-w-[130px]" title="{{ $client->name }}">
                                        {{ $client->name }}
                                    </span>
                                @endforeach
                                @if($employee->clients_count > 2)
                                    <span class="inline-block px-1 py-0.5 rounded text-[10px] font-semibold text-slate-500 bg-slate-100 border border-slate-200/60">
                                        +{{ $employee->clients_count - 2 }}
                                    </span>
                                @endif
                            </div>
                            <button
                                type="button"
                                class="text-[11px] font-semibold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center gap-0.5"
                                data-open-portfolio-btn
                            >
                                Detail Portofolio &rarr;
                            </button>
                        @else
                            <span class="text-xs text-slate-400 italic">Belum ada klien</span>
                        @endif
                    </div>
                </td>

                <!-- Kolom Kontak -->
                <td data-column="contact" class="py-3 px-4">
                    <div class="space-y-1 text-xs text-slate-600">
                        @if($employee->email)
                            <a href="mailto:{{ $employee->email }}" class="flex items-center gap-1.5 hover:text-blue-700 transition-colors truncate max-w-[160px]" title="{{ $employee->email }}">
                                <x-heroicon-o-envelope class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                <span class="truncate">{{ $employee->email }}</span>
                            </a>
                        @else
                            <div class="text-slate-400 flex items-center gap-1.5">
                                <x-heroicon-o-envelope class="w-3.5 h-3.5 text-slate-300 shrink-0" />
                                <span>-</span>
                            </div>
                        @endif
                        @if($employee->phone)
                            <a href="tel:{{ $employee->phone }}" class="flex items-center gap-1.5 text-slate-500 hover:text-slate-900 transition-colors">
                                <x-heroicon-o-phone class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                <span>{{ $employee->phone }}</span>
                            </a>
                        @endif
                    </div>
                </td>

                <!-- Kolom Status -->
                <td data-column="status" class="py-3 px-4">
                    <x-datatable.status :type="$employee->is_active ? 'active' : 'inactive'" :label="$employee->is_active ? 'active' : 'inactive'" />
                </td>

                <!-- Kolom Aksi -->
                <td data-column="actions" class="py-3 px-4 text-right">
                    <div class="actions justify-end">
                        <button
                            class="button secondary small icon-only"
                            type="button"
                            aria-label="Lihat portofolio & beban kerja staff"
                            title="Lihat portofolio & beban kerja staff"
                            data-open-portfolio-btn
                        >
                            <x-heroicon-o-folder-open class="w-4 h-4 text-slate-700" />
                        </button>
                        <button
                            class="button secondary small icon-only"
                            type="button"
                            aria-label="Edit staff"
                            title="Edit staff"
                            data-open-staff-modal="edit"
                            data-action="{{ route('staff.update', $employee) }}"
                            data-name="{{ $employee->name }}"
                            data-email="{{ $employee->email }}"
                            data-phone="{{ $employee->phone }}"
                            data-type="{{ $employee->type }}"
                            data-position="{{ $employee->position }}"
                            data-is-active="{{ $employee->is_active ? '1' : '0' }}"
                        >
                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                        </button>
                        @if (!auth()->check() || auth()->user()->isBoss() || auth()->user()->can('manage staff'))
                            <form method="POST" action="{{ route('staff.destroy', $employee) }}" onsubmit="return confirm('Hapus staff {{ $employee->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button class="button danger small icon-only" type="submit" aria-label="Delete staff" title="Delete staff">
                                    <x-heroicon-o-trash class="w-4 h-4" />
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr data-empty-row>
                <td colspan="7" class="muted py-12 text-center text-xs">
                    <div class="max-w-xs mx-auto text-center">
                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                            <x-heroicon-o-users class="w-5 h-5" />
                        </div>
                        <p class="font-medium text-slate-700 mb-1">Belum ada data staff</p>
                        <p class="text-slate-500 text-[11px] mb-3">Tambahkan anggota tim baru untuk mulai mendistribusikan proyek dan klien.</p>
                        <button class="button small" type="button" data-open-staff-modal="create">New Staff</button>
                    </div>
                </td>
            </tr>
        @endforelse
    </x-datatable>

    <!-- Modal Form Create & Edit Staff -->
    <dialog id="staffModal">
        <div class="modal-head">
            <div>
                <h2 id="staffModalTitle" class="text-base font-bold text-slate-900">New Staff</h2>
                <p class="muted text-xs mt-0.5">Kelola data personal, departemen, dan status keaktifan anggota tim.</p>
            </div>
            <button class="icon-button" type="button" data-close-staff-modal aria-label="Close modal">&times;</button>
        </div>
        <form class="modal-body" id="staffForm" method="POST" action="{{ route('staff.store') }}">
            @csrf
            <input type="hidden" name="_method" id="staffFormMethod" value="POST">
            <div class="form-grid">
                <label>
                    <span class="text-xs font-semibold text-slate-700 mb-1 block">Nama Lengkap <span class="text-rose-600">*</span></span>
                    <input name="name" id="staff_name" placeholder="cth: Ahmad Fauzi" required class="input">
                </label>
                <label>
                    <span class="text-xs font-semibold text-slate-700 mb-1 block">Departemen / Spesialisasi <span class="text-rose-600">*</span></span>
                    <select name="type" id="staff_type" required class="input">
                        @foreach ($types as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="text-xs font-semibold text-slate-700 mb-1 block">Email</span>
                    <input type="email" name="email" id="staff_email" placeholder="staff@konsulin.com" class="input">
                </label>
                <label>
                    <span class="text-xs font-semibold text-slate-700 mb-1 block">Nomor Telepon</span>
                    <input name="phone" id="staff_phone" placeholder="08123456789" class="input">
                </label>
                <label>
                    <span class="text-xs font-semibold text-slate-700 mb-1 block">Jabatan / Posisi</span>
                    <input name="position" id="staff_position" placeholder="cth: Senior Tax Specialist" class="input">
                </label>
                <label>
                    <span class="text-xs font-semibold text-slate-700 mb-1 block">Status Akun <span class="text-rose-600">*</span></span>
                    <select name="is_active" id="staff_is_active" class="input">
                        <option value="1">Aktif</option>
                        <option value="0">Non-aktif</option>
                    </select>
                </label>
            </div>
            <div class="modal-actions">
                <button class="button secondary" type="button" data-close-staff-modal>Batal</button>
                <button class="button" type="submit">Simpan Staff</button>
            </div>
        </form>
    </dialog>

    <!-- Modal Detail Portofolio & Beban Kerja Staff -->
    <dialog id="staffPortfolioModal" class="!max-w-2xl w-full">
        <div class="modal-head">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#0b192c]/5 border border-slate-200 text-[#0b192c] flex items-center justify-center font-bold text-sm select-none" id="portfolioAvatar">
                    ST
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 id="portfolioStaffName" class="text-base font-bold text-slate-900 leading-tight">Nama Staff</h2>
                        <span id="portfolioWorkloadBadge" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border">Optimal</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5" id="portfolioStaffRole">Posisi &bull; Departemen</p>
                </div>
            </div>
            <button class="icon-button" type="button" id="closePortfolioModal" aria-label="Tutup modal">&times;</button>
        </div>

        <div class="modal-body p-5 space-y-4 max-h-[70vh] overflow-y-auto">
            <!-- 3 Stat Metrics -->
            <div class="grid grid-cols-3 gap-3">
                <div class="p-3 rounded-lg border border-slate-200 bg-slate-50/70 text-center">
                    <span class="text-[10.5px] font-bold uppercase tracking-wider text-slate-500 block mb-0.5">Klien Ditangani</span>
                    <strong class="text-xl font-bold text-slate-900" id="portfolioClientCount">0</strong>
                    <span class="text-[10px] text-slate-400 block mt-0.5">Entitas Klien</span>
                </div>
                <div class="p-3 rounded-lg border border-slate-200 bg-slate-50/70 text-center">
                    <span class="text-[10.5px] font-bold uppercase tracking-wider text-slate-500 block mb-0.5">Proyek Aktif</span>
                    <strong class="text-xl font-bold text-blue-700" id="portfolioActiveCount">0</strong>
                    <span class="text-[10px] text-blue-600 font-medium block mt-0.5">Sedang Berjalan</span>
                </div>
                <div class="p-3 rounded-lg border border-slate-200 bg-slate-50/70 text-center">
                    <span class="text-[10.5px] font-bold uppercase tracking-wider text-slate-500 block mb-0.5">Proyek Selesai</span>
                    <strong class="text-xl font-bold text-emerald-700" id="portfolioCompletedCount">0</strong>
                    <span class="text-[10px] text-emerald-600 font-medium block mt-0.5">Riwayat Berhasil</span>
                </div>
            </div>

            <!-- Kontak Singkat -->
            <div class="p-3 rounded-lg border border-slate-100 bg-slate-50/50 flex flex-wrap items-center justify-between text-xs gap-3">
                <div class="flex items-center gap-1.5 text-slate-600">
                    <x-heroicon-o-envelope class="w-4 h-4 text-slate-400" />
                    <span id="portfolioEmail">-</span>
                </div>
                <div class="flex items-center gap-1.5 text-slate-600">
                    <x-heroicon-o-phone class="w-4 h-4 text-slate-400" />
                    <span id="portfolioPhone">-</span>
                </div>
            </div>

            <!-- Daftar Klien & Penugasan -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Daftar Portofolio Klien</h3>
                    <span class="text-[11px] text-slate-500" id="portfolioListCounter">0 Klien</span>
                </div>

                <div id="portfolioClientsList" class="space-y-2.5">
                    <!-- Dinamis terisi oleh JavaScript -->
                </div>
            </div>
        </div>

        <div class="modal-actions border-t border-slate-100 p-4 flex items-center justify-between bg-slate-50/60">
            <span class="text-xs text-slate-500" id="portfolioFooterHint">Data penugasan terupdate secara real-time.</span>
            <button class="button secondary" type="button" id="closePortfolioModalBottom">Tutup</button>
        </div>
    </dialog>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi controller filter tabel staff
            const allRows = Array.from(document.querySelectorAll('#staffTable tbody tr[data-row]'));
            const searchInput = document.getElementById('staffSearch');
            const deptFilter = document.getElementById('filterDepartment');
            const workloadFilter = document.getElementById('filterWorkload');
            const statusFilter = document.getElementById('filterStatus');
            const rowsPerPageSelect = document.getElementById('staffRowsPerPage');
            const paginationInfo = document.getElementById('staffPaginationInfo');
            const prevPageButton = document.getElementById('staffPrevPage');
            const nextPageButton = document.getElementById('staffNextPage');

            let currentPage = 1;
            let sortKey = 'name';
            let sortDirection = 'asc';

            function getFilteredRows() {
                const term = (searchInput?.value || '').toLowerCase().trim();
                const selectedDept = deptFilter?.value || '';
                const selectedWorkload = workloadFilter?.value || '';
                const selectedStatus = statusFilter?.value || '';

                return allRows.filter(row => {
                    if (term && !row.textContent.toLowerCase().includes(term)) {
                        return false;
                    }
                    if (selectedDept && row.dataset.type !== selectedDept) {
                        return false;
                    }
                    if (selectedWorkload && row.dataset.workloadStatus !== selectedWorkload) {
                        return false;
                    }
                    if (selectedStatus && row.dataset.status !== selectedStatus) {
                        return false;
                    }
                    return true;
                }).sort((a, b) => {
                    const left = a.dataset[sortKey] ?? '';
                    const right = b.dataset[sortKey] ?? '';
                    const isNum = Number.isFinite(Number(left)) && Number.isFinite(Number(right));
                    const result = isNum ? Number(left) - Number(right) : left.localeCompare(right);
                    return sortDirection === 'asc' ? result : -result;
                });
            }

            function renderStaffTable() {
                const visibleRows = getFilteredRows();
                const perPage = Number(rowsPerPageSelect?.value || 10);
                const totalPages = Math.max(1, Math.ceil(visibleRows.length / perPage));
                currentPage = Math.min(Math.max(1, currentPage), totalPages);
                const start = (currentPage - 1) * perPage;
                const end = start + perPage;

                allRows.forEach(row => { row.hidden = true; });
                visibleRows.slice(start, end).forEach(row => { row.hidden = false; });

                if (paginationInfo) {
                    paginationInfo.textContent = visibleRows.length
                        ? `${start + 1}-${Math.min(end, visibleRows.length)} of ${visibleRows.length}`
                        : '0 of 0';
                }
                if (prevPageButton) prevPageButton.disabled = currentPage <= 1;
                if (nextPageButton) nextPageButton.disabled = currentPage >= totalPages;

                // Toggle empty state
                let emptyRow = document.getElementById('staffCustomEmptyRow');
                if (visibleRows.length === 0) {
                    if (!emptyRow) {
                        emptyRow = document.createElement('tr');
                        emptyRow.id = 'staffCustomEmptyRow';
                        emptyRow.innerHTML = `
                            <td colspan="7" class="py-10 text-center text-xs text-slate-500">
                                <p class="font-semibold text-slate-700 mb-1">Tidak ada staf yang sesuai dengan filter pencarian.</p>
                                <p class="text-slate-400">Silakan sesuaikan kata kunci atau filter departemen dan beban kerja.</p>
                            </td>
                        `;
                        document.querySelector('#staffTable tbody')?.appendChild(emptyRow);
                    }
                    emptyRow.hidden = false;
                } else if (emptyRow) {
                    emptyRow.hidden = true;
                }
            }

            // Event Listeners untuk Filter & Pagination
            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    currentPage = 1;
                    renderStaffTable();
                });
            }
            if (deptFilter) {
                deptFilter.addEventListener('change', () => {
                    currentPage = 1;
                    renderStaffTable();
                });
            }
            if (workloadFilter) {
                workloadFilter.addEventListener('change', () => {
                    currentPage = 1;
                    renderStaffTable();
                });
            }
            if (statusFilter) {
                statusFilter.addEventListener('change', () => {
                    currentPage = 1;
                    renderStaffTable();
                });
            }
            if (rowsPerPageSelect) {
                rowsPerPageSelect.addEventListener('change', () => {
                    currentPage = 1;
                    renderStaffTable();
                });
            }
            if (prevPageButton) {
                prevPageButton.addEventListener('click', (e) => {
                    e.stopImmediatePropagation();
                    if (currentPage > 1) {
                        currentPage--;
                        renderStaffTable();
                    }
                });
            }
            if (nextPageButton) {
                nextPageButton.addEventListener('click', (e) => {
                    e.stopImmediatePropagation();
                    currentPage++;
                    renderStaffTable();
                });
            }

            // Sorting headers
            document.querySelectorAll('#staffContainer [data-sort]').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.stopImmediatePropagation();
                    const key = button.dataset.sort;
                    if (sortKey === key) {
                        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        sortKey = key;
                        sortDirection = 'asc';
                    }
                    renderStaffTable();
                });
            });

            // Jalankan render awal
            renderStaffTable();

            // Portfolio Modal Logic
            const portfolioModal = document.getElementById('staffPortfolioModal');
            const closePortfolioBtn = document.getElementById('closePortfolioModal');
            const closePortfolioBottom = document.getElementById('closePortfolioModalBottom');

            function openPortfolio(portfolioData) {
                if (!portfolioModal || !portfolioData) return;

                const initials = (portfolioData.name || 'ST')
                    .split(' ')
                    .map(p => p[0] ? p[0].toUpperCase() : '')
                    .slice(0, 2)
                    .join('');

                document.getElementById('portfolioAvatar').textContent = initials || 'ST';
                document.getElementById('portfolioStaffName').textContent = portfolioData.name || '-';
                document.getElementById('portfolioStaffRole').textContent = `${portfolioData.position || 'Konsultan'} \u2022 ${portfolioData.type.toUpperCase()}`;

                const badge = document.getElementById('portfolioWorkloadBadge');
                badge.textContent = `${portfolioData.workload_label} (${portfolioData.active_projects_count} Proyek)`;
                badge.className = `inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border ${portfolioData.workload_badge_class}`;

                document.getElementById('portfolioClientCount').textContent = portfolioData.clients_count || 0;
                document.getElementById('portfolioActiveCount').textContent = portfolioData.active_projects_count || 0;
                document.getElementById('portfolioCompletedCount').textContent = portfolioData.completed_projects_count || 0;

                document.getElementById('portfolioEmail').textContent = portfolioData.email || '-';
                document.getElementById('portfolioPhone').textContent = portfolioData.phone || '-';
                document.getElementById('portfolioListCounter').textContent = `${portfolioData.clients_count || 0} Klien`;

                const clientsList = document.getElementById('portfolioClientsList');
                clientsList.innerHTML = '';

                if (!portfolioData.portfolios || portfolioData.portfolios.length === 0) {
                    clientsList.innerHTML = `
                        <div class="p-6 text-center border border-dashed border-slate-200 rounded-xl bg-slate-50/50 text-xs text-slate-500">
                            <p class="font-medium text-slate-700 mb-1">Belum ada penugasan klien</p>
                            <p class="text-[11px] text-slate-400">Staff ini memiliki kapasitas penuh dan siap untuk menerima penugasan klien baru.</p>
                        </div>
                    `;
                } else {
                    portfolioData.portfolios.forEach(item => {
                        const client = item.client || {};
                        const activeProjects = item.active_projects || [];
                        const completedProjects = item.completed_projects || [];

                        const clientCard = document.createElement('div');
                        clientCard.className = 'p-3.5 rounded-xl border border-slate-200/90 bg-white shadow-2xs hover:border-slate-300 transition-colors';

                        let activeProjectsHtml = '';
                        if (activeProjects.length > 0) {
                            activeProjectsHtml = activeProjects.map(proj => `
                                <div class="flex items-center justify-between text-xs py-1 px-2 rounded bg-blue-50/70 border border-blue-100 text-blue-900 mt-1">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600 shrink-0"></span>
                                        <span class="font-semibold truncate">${proj.name}</span>
                                        <span class="text-[10px] text-blue-600 font-normal">(${proj.service_type || 'General'})</span>
                                    </div>
                                    <span class="text-[10px] font-medium uppercase px-1.5 py-0.2 rounded bg-white text-blue-700 border border-blue-200 shrink-0">${(proj.status || '').replace('_', ' ')}</span>
                                </div>
                            `).join('');
                        } else {
                            activeProjectsHtml = '<p class="text-[11px] text-slate-400 italic mt-1">Tidak ada proyek yang sedang aktif.</p>';
                        }

                        let completedProjectsHtml = '';
                        if (completedProjects.length > 0) {
                            completedProjectsHtml = `
                                <div class="mt-2 pt-2 border-t border-slate-100 text-[11px] text-slate-500">
                                    <span class="font-medium text-slate-600">${completedProjects.length} proyek selesai:</span>
                                    <span class="text-slate-400">${completedProjects.map(p => p.name).join(', ')}</span>
                                </div>
                            `;
                        }

                        clientCard.innerHTML = `
                            <div class="flex items-start justify-between gap-2 mb-1.5">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900 leading-tight">${client.name || 'Klien'}</h4>
                                    <p class="text-[11px] text-slate-500 mt-0.5">${client.business_type || 'Perusahaan'} &bull; Status: ${client.status || 'Aktif'}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 shrink-0">
                                    ${activeProjects.length} Proyek Aktif
                                </span>
                            </div>
                            <div class="space-y-1">
                                ${activeProjectsHtml}
                                ${completedProjectsHtml}
                            </div>
                        `;

                        clientsList.appendChild(clientCard);
                    });
                }

                portfolioModal.showModal();
            }

            document.querySelectorAll('[data-open-portfolio-btn]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const row = btn.closest('tr');
                    if (row && row.dataset.portfolio) {
                        try {
                            const data = JSON.parse(row.dataset.portfolio);
                            openPortfolio(data);
                        } catch (err) {
                            console.error('Failed to parse portfolio data', err);
                        }
                    }
                });
            });

            if (closePortfolioBtn) {
                closePortfolioBtn.addEventListener('click', () => portfolioModal.close());
            }
            if (closePortfolioBottom) {
                closePortfolioBottom.addEventListener('click', () => portfolioModal.close());
            }

            // Staff Form Modal (Create & Edit)
            const staffModal = document.getElementById('staffModal');
            const staffForm = document.getElementById('staffForm');
            const staffMethod = document.getElementById('staffFormMethod');
            const staffTitle = document.getElementById('staffModalTitle');

            document.querySelectorAll('[data-open-staff-modal]').forEach((button) => {
                button.addEventListener('click', () => {
                    staffForm.reset();
                    if (button.dataset.openStaffModal === 'edit') {
                        staffForm.action = button.dataset.action;
                        staffMethod.value = 'PUT';
                        staffTitle.textContent = 'Edit Staff';
                        document.getElementById('staff_name').value = button.dataset.name || '';
                        document.getElementById('staff_email').value = button.dataset.email || '';
                        document.getElementById('staff_phone').value = button.dataset.phone || '';
                        document.getElementById('staff_type').value = button.dataset.type || 'accounting';
                        document.getElementById('staff_position').value = button.dataset.position || '';
                        document.getElementById('staff_is_active').value = button.dataset.isActive || '1';
                    } else {
                        staffForm.action = @js(route('staff.store'));
                        staffMethod.value = 'POST';
                        staffTitle.textContent = 'New Staff';
                    }
                    staffModal.showModal();
                });
            });

            document.querySelectorAll('[data-close-staff-modal]').forEach((button) => {
                button.addEventListener('click', () => staffModal.close());
            });
        });
    </script>
</x-layouts.app>
