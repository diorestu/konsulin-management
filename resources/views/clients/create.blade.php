<x-layouts.app title="Tambah Client Baru : Konsulin Manager">
    <div class="topbar">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('clients.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 flex items-center gap-1">
                    <x-heroicon-o-arrow-left class="w-3.5 h-3.5" />
                    <span>Kembali ke Daftar Client</span>
                </a>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900">Tambah Client Baru</h1>
            <p class="muted text-xs sm:text-sm mt-0.5">Lengkapi profil perusahaan, paket kontrak, tim PIC, dan matriks kepatuhan pajak bulanan.</p>
        </div>
    </div>

    <form action="{{ route('clients.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- SECTION 1: INFORMASI DASAR CLIENT -->
        <div class="bg-white border border-slate-200/90 rounded-xl p-6 shadow-sm">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-[#0b192c] text-white flex items-center justify-center">
                    <x-heroicon-o-building-office-2 class="w-4 h-4" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 m-0">1. Identitas & Profil Client</h2>
                    <p class="text-xs text-slate-500 m-0">Informasi entitas legal, PIC perusahaan, dan lokasi operasional.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="client_code" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Client ID (Kode Klien)
                    </label>
                    <input
                        type="text"
                        id="client_code"
                        name="client_code"
                        value="{{ old('client_code') }}"
                        placeholder="Contoh: CLI-2026-001"
                        class="w-full font-mono uppercase"
                    >
                </div>

                <div class="md:col-span-2">
                    <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Client Name (Nama Perusahaan / WP) <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        placeholder="PT Contoh Bisnis Indonesia"
                        class="w-full font-medium"
                    >
                </div>

                <div>
                    <label for="client_pic" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Client PIC (Kontak PIC Klien)
                    </label>
                    <input
                        type="text"
                        id="client_pic"
                        name="client_pic"
                        value="{{ old('client_pic') }}"
                        placeholder="Bpk. Anton (Finance Director)"
                        class="w-full"
                    >
                </div>

                <div>
                    <label for="location" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Client Location (Lokasi / Kota)
                    </label>
                    <input
                        type="text"
                        id="location"
                        name="location"
                        value="{{ old('location') }}"
                        placeholder="Jakarta Selatan, DKI Jakarta"
                        class="w-full"
                    >
                </div>

                <div>
                    <label for="migration_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Migration Date (Tanggal Migrasi)
                    </label>
                    <input
                        type="date"
                        id="migration_date"
                        name="migration_date"
                        value="{{ old('migration_date') }}"
                        class="w-full"
                    >
                </div>

                <div>
                    <label for="client_type" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Client Type (Bentuk Entitas)
                    </label>
                    <select id="client_type" name="client_type" class="w-full">
                        <option value="Badan (PT)" {{ old('client_type') === 'Badan (PT)' ? 'selected' : '' }}>Badan (PT)</option>
                        <option value="Badan (CV)" {{ old('client_type') === 'Badan (CV)' ? 'selected' : '' }}>Badan (CV)</option>
                        <option value="Orang Pribadi (OP)" {{ old('client_type') === 'Orang Pribadi (OP)' ? 'selected' : '' }}>Orang Pribadi (OP)</option>
                        <option value="Yayasan" {{ old('client_type') === 'Yayasan' ? 'selected' : '' }}>Yayasan</option>
                        <option value="Lainnya" {{ old('client_type') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>

                <div>
                    <label for="business_type" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Client Business Type (Bidang Usaha)
                    </label>
                    <input
                        type="text"
                        id="business_type"
                        name="business_type"
                        value="{{ old('business_type') }}"
                        placeholder="Teknologi Informasi, F&B, dsb."
                        class="w-full"
                    >
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Status Klien <span class="text-rose-500">*</span>
                    </label>
                    <select id="status" name="status" required class="w-full font-semibold">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active (Aktif)</option>
                        <option value="onboarding" {{ old('status') === 'onboarding' ? 'selected' : '' }}>Onboarding</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive (Non-aktif)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- SECTION 2: KONTRAK & PAKET LAYANAN -->
        <div class="bg-white border border-slate-200/90 rounded-xl p-6 shadow-sm">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-[#0b192c] text-white flex items-center justify-center">
                    <x-heroicon-o-document-text class="w-4 h-4" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 m-0">2. Kontrak, Paket Layanan & Pajak</h2>
                    <p class="text-xs text-slate-500 m-0">Pengaturan masa kontrak, paket akuntansi/pajak, dan link berkas.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="tax_status" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tax Status (Status Pajak)
                    </label>
                    <select id="tax_status" name="tax_status" class="w-full">
                        <option value="PKP" {{ old('tax_status') === 'PKP' ? 'selected' : '' }}>PKP (Pengusaha Kena Pajak)</option>
                        <option value="Non-PKP" {{ old('tax_status') === 'Non-PKP' ? 'selected' : '' }}>Non-PKP</option>
                        <option value="PP 55 (PPh Final)" {{ old('tax_status') === 'PP 55 (PPh Final)' ? 'selected' : '' }}>PP 55 (PPh Final UMKM)</option>
                    </select>
                </div>

                <div>
                    <label for="contract_status" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Contract Status
                    </label>
                    <select id="contract_status" name="contract_status" class="w-full">
                        <option value="Active" {{ old('contract_status') === 'Active' ? 'selected' : '' }}>Active (Aktif)</option>
                        <option value="Renewal" {{ old('contract_status') === 'Renewal' ? 'selected' : '' }}>Renewal (Perpanjangan)</option>
                        <option value="In Review" {{ old('contract_status') === 'In Review' ? 'selected' : '' }}>In Review</option>
                        <option value="Ended" {{ old('contract_status') === 'Ended' ? 'selected' : '' }}>Ended (Selesai)</option>
                    </select>
                </div>

                <div>
                    <label for="start_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Start Date (Awal Kontrak)
                    </label>
                    <input
                        type="date"
                        id="start_date"
                        name="start_date"
                        value="{{ old('start_date') }}"
                        class="w-full"
                    >
                </div>

                <div>
                    <label for="contract_duration_months" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Contract Durations (Month)
                    </label>
                    <input
                        type="number"
                        id="contract_duration_months"
                        name="contract_duration_months"
                        value="{{ old('contract_duration_months', 12) }}"
                        min="1"
                        placeholder="Contoh: 12"
                        class="w-full"
                    >
                </div>

                <div>
                    <label for="end_contract_due_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        End Contract Due Date
                    </label>
                    <input
                        type="date"
                        id="end_contract_due_date"
                        name="end_contract_due_date"
                        value="{{ old('end_contract_due_date') }}"
                        class="w-full"
                    >
                </div>

                <div>
                    <label for="finance_package" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Finance Package
                    </label>
                    <input
                        type="text"
                        id="finance_package"
                        name="finance_package"
                        value="{{ old('finance_package') }}"
                        placeholder="Laporan Keuangan Bulanan, Audit, dsb."
                        class="w-full"
                    >
                </div>

                <div>
                    <label for="tax_package" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tax Package
                    </label>
                    <input
                        type="text"
                        id="tax_package"
                        name="tax_package"
                        value="{{ old('tax_package') }}"
                        placeholder="All-in Monthly Tax, PPN & PPh, dsb."
                        class="w-full"
                    >
                </div>

                <div>
                    <label for="addon" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Add-On (Layanan Tambahan)
                    </label>
                    <input
                        type="text"
                        id="addon"
                        name="addon"
                        value="{{ old('addon') }}"
                        placeholder="SPT Tahunan, Restitusi, dsb."
                        class="w-full"
                    >
                </div>

                <div>
                    <label for="files" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Files (Tautan Berkas / Cloud Drive)
                    </label>
                    <input
                        type="text"
                        id="files"
                        name="files"
                        value="{{ old('files') }}"
                        placeholder="https://drive.google.com/..."
                        class="w-full"
                    >
                </div>

                <div class="md:col-span-3">
                    <label for="package_detail" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Package Detail (Rincian Paket & Catatan Layanan)
                    </label>
                    <textarea
                        id="package_detail"
                        name="package_detail"
                        rows="2"
                        placeholder="Rincian scope of work, deliverable, batasan transaksi..."
                        class="w-full text-xs"
                    >{{ old('package_detail') }}</textarea>
                </div>
            </div>
        </div>

        <!-- SECTION 3: PIC & REVIEW / APPROVAL -->
        <div class="bg-white border border-slate-200/90 rounded-xl p-6 shadow-sm">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
                <div class="w-7 h-7 rounded-lg bg-[#0b192c] text-white flex items-center justify-center">
                    <x-heroicon-o-user-group class="w-4 h-4" />
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900 m-0">3. Tim Penanggung Jawab (PIC) & Review</h2>
                    <p class="text-xs text-slate-500 m-0">Penugasan konsultan pajak, staf akuntansi, dan status approval.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="tax_pic" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tax PIC (Konsultan Pajak)
                    </label>
                    <input
                        type="text"
                        id="tax_pic"
                        name="tax_pic"
                        value="{{ old('tax_pic') }}"
                        placeholder="Nadia Tax Consultant"
                        class="w-full"
                    >
                </div>

                <div>
                    <label for="accounting_pic" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Accounting PIC (Staf Akuntansi)
                    </label>
                    <input
                        type="text"
                        id="accounting_pic"
                        name="accounting_pic"
                        value="{{ old('accounting_pic') }}"
                        placeholder="Ari Senior Accountant"
                        class="w-full"
                    >
                </div>

                <div>
                    <label for="review_approval" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Review / Approval Status
                    </label>
                    <select id="review_approval" name="review_approval" class="w-full">
                        <option value="Approved" {{ old('review_approval') === 'Approved' ? 'selected' : '' }}>Approved (Disetujui)</option>
                        <option value="Pending Review" {{ old('review_approval') === 'Pending Review' ? 'selected' : '' }}>Pending Review</option>
                        <option value="In Progress" {{ old('review_approval') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Needs Revision" {{ old('review_approval') === 'Needs Revision' ? 'selected' : '' }}>Needs Revision</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- SECTION 4: MATRIKS KEPATUHAN BULANAN (MAR 26 - DEC 26) -->
        <div class="bg-white border border-slate-200/90 rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-[#0b192c] text-white flex items-center justify-center">
                        <x-heroicon-o-table-cells class="w-4 h-4" />
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 m-0">4. Matriks Kepatuhan Pajak & Akuntansi (Mar 26 – Dec 26)</h2>
                        <p class="text-xs text-slate-500 m-0">Tracking checklist bulanan untuk PPh 21, Unifikasi, PPN, PP 55, PPh 25, LK, dan Pending / Notes.</p>
                    </div>
                </div>
                <div class="text-[11px] text-slate-500 font-medium">
                    Nilai saran: <span class="font-mono font-semibold text-slate-700">Done / Pending / N/A / Nihil</span>
                </div>
            </div>

            <div class="overflow-x-auto border border-slate-200 rounded-lg">
                <table class="w-full text-xs text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-200 font-semibold text-slate-600">
                        <tr>
                            <th class="p-2.5 w-24">Bulan</th>
                            <th class="p-2.5 min-w-[120px]">PPh 21</th>
                            <th class="p-2.5 min-w-[130px]">PPh Unifikasi</th>
                            <th class="p-2.5 min-w-[120px]">PPN</th>
                            <th class="p-2.5 min-w-[120px]">PP 55</th>
                            <th class="p-2.5 min-w-[120px]">PPh 25</th>
                            <th class="p-2.5 min-w-[120px]">LK (Lap. Keu)</th>
                            <th class="p-2.5 min-w-[180px]">Pending / Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($periods as $period)
                            <tr class="hover:bg-slate-50/70">
                                <td class="p-2.5 font-bold text-slate-800 bg-slate-50/50 whitespace-nowrap">
                                    {{ $period }}
                                </td>
                                <td class="p-2">
                                    <input
                                        type="text"
                                        name="compliances[{{ $period }}][pph_21]"
                                        value="{{ old("compliances.{$period}.pph_21") }}"
                                        placeholder="Done / -"
                                        class="w-full text-xs py-1 px-2 min-h-0 h-8 rounded border-slate-200"
                                    >
                                </td>
                                <td class="p-2">
                                    <input
                                        type="text"
                                        name="compliances[{{ $period }}][pph_unifikasi]"
                                        value="{{ old("compliances.{$period}.pph_unifikasi") }}"
                                        placeholder="Done / -"
                                        class="w-full text-xs py-1 px-2 min-h-0 h-8 rounded border-slate-200"
                                    >
                                </td>
                                <td class="p-2">
                                    <input
                                        type="text"
                                        name="compliances[{{ $period }}][ppn]"
                                        value="{{ old("compliances.{$period}.ppn") }}"
                                        placeholder="Done / -"
                                        class="w-full text-xs py-1 px-2 min-h-0 h-8 rounded border-slate-200"
                                    >
                                </td>
                                <td class="p-2">
                                    <input
                                        type="text"
                                        name="compliances[{{ $period }}][pp_55]"
                                        value="{{ old("compliances.{$period}.pp_55") }}"
                                        placeholder="Nihil / Done"
                                        class="w-full text-xs py-1 px-2 min-h-0 h-8 rounded border-slate-200"
                                    >
                                </td>
                                <td class="p-2">
                                    <input
                                        type="text"
                                        name="compliances[{{ $period }}][pph_25]"
                                        value="{{ old("compliances.{$period}.pph_25") }}"
                                        placeholder="Done / -"
                                        class="w-full text-xs py-1 px-2 min-h-0 h-8 rounded border-slate-200"
                                    >
                                </td>
                                <td class="p-2">
                                    <input
                                        type="text"
                                        name="compliances[{{ $period }}][lk]"
                                        value="{{ old("compliances.{$period}.lk") }}"
                                        placeholder="Draft / Final"
                                        class="w-full text-xs py-1 px-2 min-h-0 h-8 rounded border-slate-200"
                                    >
                                </td>
                                <td class="p-2">
                                    <input
                                        type="text"
                                        name="compliances[{{ $period }}][notes]"
                                        value="{{ old("compliances.{$period}.notes") }}"
                                        placeholder="Catatan status & kendala..."
                                        class="w-full text-xs py-1 px-2 min-h-0 h-8 rounded border-slate-200"
                                    >
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FORM ACTION BUTTONS -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
            <a href="{{ route('clients.index') }}" class="button secondary">
                Batal
            </a>
            <button type="submit" class="button">
                <x-heroicon-o-check class="w-4 h-4" />
                <span>Simpan Client Baru</span>
            </button>
        </div>
    </form>
</x-layouts.app>
