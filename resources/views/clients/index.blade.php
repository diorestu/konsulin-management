<x-layouts.app title="Kelola Client - Konsulin Manager">
    <div class="topbar flex-wrap">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">Kelola Client</h1>
            <p class="muted text-xs sm:text-sm mt-0.5">
                Direktori klien perpajakan & akuntansi, monitoring kontrak, paket layanan, dan matriks kepatuhan bulanan.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('clients.create') }}" class="button small">
                <x-heroicon-o-plus class="w-4 h-4" />
                <span>Tambah Client Baru</span>
            </a>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <section class="stats" data-animate-children>
        <div class="stat">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Client</span>
                <div class="w-8 h-8 rounded-lg bg-[#0b192c]/5 text-[#0b192c] flex items-center justify-center">
                    <x-heroicon-o-building-office-2 class="w-4 h-4" />
                </div>
            </div>
            <strong>{{ $totalClients }}</strong>
            <span class="text-xs text-slate-500">Perusahaan terdaftar</span>
        </div>

        <div class="stat">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Kontrak Aktif</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
                    <x-heroicon-o-check-badge class="w-4 h-4" />
                </div>
            </div>
            <strong>{{ $activeContracts }}</strong>
            <span class="text-xs text-blue-600 font-medium">Dalam masa layanan</span>
        </div>

        <div class="stat">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status Pajak PKP</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
                    <x-heroicon-o-receipt-percent class="w-4 h-4" />
                </div>
            </div>
            <strong>{{ $pkpCount }}</strong>
            <span class="text-xs text-amber-600 font-medium">Wajib Pajak PKP</span>
        </div>

        <div class="stat">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Perlu Review</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-700 flex items-center justify-center">
                    <x-heroicon-o-clipboard-document-check class="w-4 h-4" />
                </div>
            </div>
            <strong class="{{ $pendingReviews > 0 ? 'text-rose-600' : '' }}">{{ $pendingReviews }}</strong>
            <span class="text-xs {{ $pendingReviews > 0 ? 'text-rose-600 font-medium' : 'text-slate-500' }}">
                {{ $pendingReviews > 0 ? 'Menunggu persetujuan' : 'Semua tervalidasi' }}
            </span>
        </div>
    </section>

    <!-- Quick Filters -->
    <div class="mb-4 flex items-center flex-wrap gap-2 text-xs">
        <span class="font-semibold text-slate-500 uppercase tracking-wider mr-1">Filter:</span>
        @php
            $isAllActive = !request()->hasAny(['contract_status', 'tax_status']);
            $isContractActive = request('contract_status') === 'Active';
            $isPkpActive = request('tax_status') === 'PKP';
            $isNonPkpActive = request('tax_status') === 'Non-PKP';
        @endphp
        <a
            href="{{ route('clients.index') }}"
            class="filter-badge px-3 py-1.5 rounded-full border text-xs transition-all duration-150 inline-flex items-center gap-1.5 select-none {{ $isAllActive ? 'active !bg-[#0b192c] !text-white !border-[#0b192c] font-semibold shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300 font-medium' }}"
            style="{{ $isAllActive ? 'background-color: #0b192c !important; color: #ffffff !important; border-color: #0b192c !important;' : '' }}"
        >
            @if($isAllActive)
                <span class="w-1.5 h-1.5 rounded-full bg-white shrink-0"></span>
            @endif
            <span>Semua</span>
        </a>
        <a
            href="{{ route('clients.index', ['contract_status' => 'Active']) }}"
            class="filter-badge px-3 py-1.5 rounded-full border text-xs transition-all duration-150 inline-flex items-center gap-1.5 select-none {{ $isContractActive ? 'active !bg-[#0b192c] !text-white !border-[#0b192c] font-semibold shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300 font-medium' }}"
            style="{{ $isContractActive ? 'background-color: #0b192c !important; color: #ffffff !important; border-color: #0b192c !important;' : '' }}"
        >
            @if($isContractActive)
                <span class="w-1.5 h-1.5 rounded-full bg-white shrink-0"></span>
            @endif
            <span>Kontrak Aktif</span>
        </a>
        <a
            href="{{ route('clients.index', ['tax_status' => 'PKP']) }}"
            class="filter-badge px-3 py-1.5 rounded-full border text-xs transition-all duration-150 inline-flex items-center gap-1.5 select-none {{ $isPkpActive ? 'active !bg-[#0b192c] !text-white !border-[#0b192c] font-semibold shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300 font-medium' }}"
            style="{{ $isPkpActive ? 'background-color: #0b192c !important; color: #ffffff !important; border-color: #0b192c !important;' : '' }}"
        >
            @if($isPkpActive)
                <span class="w-1.5 h-1.5 rounded-full bg-white shrink-0"></span>
            @endif
            <span>PKP</span>
        </a>
        <a
            href="{{ route('clients.index', ['tax_status' => 'Non-PKP']) }}"
            class="filter-badge px-3 py-1.5 rounded-full border text-xs transition-all duration-150 inline-flex items-center gap-1.5 select-none {{ $isNonPkpActive ? 'active !bg-[#0b192c] !text-white !border-[#0b192c] font-semibold shadow-xs' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300 font-medium' }}"
            style="{{ $isNonPkpActive ? 'background-color: #0b192c !important; color: #ffffff !important; border-color: #0b192c !important;' : '' }}"
        >
            @if($isNonPkpActive)
                <span class="w-1.5 h-1.5 rounded-full bg-white shrink-0"></span>
            @endif
            <span>Non-PKP</span>
        </a>
    </div>

    <!-- Clients Master Datatable -->
    <section class="panel p-5 bg-white border border-slate-200/90 rounded-xl mb-6 shadow-sm">
        <x-datatable
            id="clients-datatable"
            search-placeholder="Cari nama client, client ID, PIC, atau lokasi..."
            empty-message="Belum ada data client yang sesuai kriteria."
        >
            <x-slot:thead>
                <x-datatable.th column="client_code" sortable :sorted="true" direction="asc">Client ID & Nama</x-datatable.th>
                <x-datatable.th column="pic" sortable info="PIC Client dan Lokasi Operasional">PIC & Lokasi</x-datatable.th>
                <x-datatable.th column="tax_status" sortable info="Status PKP dan Bidang Usaha">Pajak & Usaha</x-datatable.th>
                <x-datatable.th column="contract" sortable info="Status Kontrak dan Masa Berlaku">Kontrak & Durasi</x-datatable.th>
                <x-datatable.th column="packages" sortable info="Paket Finance, Tax, dan Add-on">Paket Layanan</x-datatable.th>
                <x-datatable.th column="internal_pic" sortable info="PIC Pajak dan PIC Akuntansi Konsulin">Tim Konsulin</x-datatable.th>
                <x-datatable.th column="status" sortable info="Status Layanan & Approval">Status & Approval</x-datatable.th>
                <x-datatable.th align="right" :sortable="false">Aksi</x-datatable.th>
            </x-slot:thead>

            <x-slot:tbody>
                @foreach($clients as $index => $client)
                    <tr class="hover:bg-slate-50/70 border-b border-slate-100 transition-colors" data-row data-name="{{ $client->name }} {{ $client->client_code }}">
                        <td class="px-4 py-3" data-column="Client ID & Nama">
                            <div class="flex items-start gap-2">
                                <span class="text-[11px] font-bold text-slate-400 mt-0.5">{{ $index + 1 }}.</span>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                                        <a href="{{ route('clients.show', $client) }}" class="hover:underline text-[#0b192c]">
                                            {{ $client->name }}
                                        </a>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1.5 flex-wrap">
                                        @if($client->client_code)
                                            <span class="font-mono font-semibold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded text-[10px]">
                                                {{ $client->client_code }}
                                            </span>
                                        @endif
                                        <span>•</span>
                                        <span>Tipe: <strong class="text-slate-700">{{ $client->client_type ?? 'Badan' }}</strong></span>
                                        @if($client->migration_date)
                                            <span>• Migrasi: {{ $client->migration_date->format('d/m/Y') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="px-4 py-3 text-xs" data-column="PIC & Lokasi">
                            <div class="font-semibold text-slate-800">{{ $client->client_pic ?? '-' }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                                <x-heroicon-o-map-pin class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                <span>{{ $client->location ?? '-' }}</span>
                            </div>
                        </td>

                        <td class="px-4 py-3 text-xs" data-column="Pajak & Usaha">
                            <div class="flex items-center gap-1 mb-0.5">
                                @if(strtoupper($client->tax_status ?? '') === 'PKP')
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        PKP
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ $client->tax_status ?? 'Non-PKP' }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-500">{{ $client->business_type ?? '-' }}</div>
                        </td>

                        <td class="px-4 py-3 text-xs" data-column="Kontrak & Durasi">
                            <div class="font-semibold text-slate-800 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full {{ strtolower($client->contract_status ?? '') === 'active' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                <span>{{ $client->contract_status ?? 'Active' }}</span>
                                @if($client->contract_duration_months)
                                    <span class="text-[11px] text-slate-400 font-normal">({{ $client->contract_duration_months }} bln)</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                Due: <span class="font-medium text-slate-700">{{ $client->end_contract_due_date ? $client->end_contract_due_date->format('d M Y') : '-' }}</span>
                            </div>
                        </td>

                        <td class="px-4 py-3 text-xs" data-column="Paket Layanan">
                            <div class="flex items-center gap-1 flex-wrap">
                                @if($client->tax_package)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                        Tax: {{ $client->tax_package }}
                                    </span>
                                @endif
                                @if($client->finance_package)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        Fin: {{ $client->finance_package }}
                                    </span>
                                @endif
                            </div>
                            @if($client->addon)
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    Add-on: {{ $client->addon }}
                                </div>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-xs" data-column="Tim Konsulin">
                            <div class="text-slate-800">
                                <span class="text-[10px] font-semibold text-slate-400 uppercase">Tax:</span> {{ $client->tax_pic ?? '-' }}
                            </div>
                            <div class="text-slate-800 mt-0.5">
                                <span class="text-[10px] font-semibold text-slate-400 uppercase">Acc:</span> {{ $client->accounting_pic ?? '-' }}
                            </div>
                        </td>

                        <td class="px-4 py-3" data-column="Status & Approval">
                            <div class="mb-1">
                                <x-datatable.status 
                                    :type="strtolower($client->status) === 'active' ? 'passed' : (strtolower($client->status) === 'inactive' ? 'not_started' : 'warning')" 
                                    :label="ucfirst($client->status)" 
                                />
                            </div>
                            @if($client->review_approval)
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold {{ strtolower($client->review_approval) === 'approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $client->review_approval }}
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right" data-column="Aksi">
                            <div class="flex items-center justify-end gap-1.5">
                                <a
                                    href="{{ route('clients.show', $client) }}"
                                    class="button small secondary icon-only"
                                    title="Buka Matriks Kepatuhan (Mar 26 - Dec 26)"
                                >
                                    <x-heroicon-o-table-cells class="w-3.5 h-3.5 text-slate-600" />
                                </a>
                                <a
                                    href="{{ route('clients.edit', $client) }}"
                                    class="button small secondary icon-only"
                                    title="Edit Client"
                                >
                                    <x-heroicon-o-pencil class="w-3.5 h-3.5 text-slate-600" />
                                </a>
                                @if(auth()->user()?->isBoss())
                                    <form action="{{ route('clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Hapus client {{ $client->name }}? Data kepatuhan dan riwayatnya akan ikut terhapus.')" class="m-0 inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="button small danger icon-only" title="Hapus Client">
                                            <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-slot:tbody>
        </x-datatable>
    </section>
</x-layouts.app>
