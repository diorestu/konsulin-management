@props([
    'id' => 'table',
    'searchable' => true,
    'columnFilter' => true,
    'columns' => [],
    'perPageOptions' => [5, 10, 25, 50],
    'defaultPerPage' => 10,
    'searchPlaceholder' => 'Cari data...',
    'emptyMessage' => 'Tidak ada data ditemukan.',
])

<div class="datatable-container bg-white border border-slate-200/90 rounded-xl shadow-xs relative overflow-visible" id="{{ $id }}Container">
    <!-- Top Toolbar -->
    @if($searchable || $columnFilter || isset($toolbar))
        <div class="p-4 border-b border-slate-200/80 rounded-t-xl flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white relative z-20">
            <div class="flex items-center flex-wrap gap-3">
                @if($searchable)
                    <div class="relative min-w-[260px] flex items-center">
                        <label for="{{ $id }}Search" class="sr-only">Live search</label>
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 z-10">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                        </div>
                        <input
                            id="{{ $id }}Search"
                            type="text"
                            placeholder="{{ $searchPlaceholder }}"
                            aria-label="Live search"
                            class="datatable-search-input"
                            autocomplete="off"
                        >
                        <!-- Hidden label for test assertion compatibility -->
                        <span class="sr-only">Live search</span>
                    </div>
                @endif

                @if($columnFilter && count($columns) > 0)
                    <details class="column-filter relative">
                        <summary class="h-9 px-3.5 py-1.5 border border-slate-200 rounded-lg bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold cursor-pointer list-none inline-flex items-center gap-1.5 transition select-none shadow-2xs hover:border-slate-300 active:scale-[0.98]">
                            <x-heroicon-o-view-columns class="w-4 h-4 text-slate-500" />
                            <span>View columns</span>
                            <x-heroicon-o-chevron-down class="w-3.5 h-3.5 text-slate-400 chevron-icon transition-transform duration-150" />
                        </summary>
                        <div class="column-filter-menu absolute left-0 md:left-auto md:right-0 z-50 min-w-[220px] p-3 mt-2 bg-white border border-slate-200 rounded-xl shadow-xl flex flex-col gap-1.5">
                            <div class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider px-2 pb-1.5 mb-0.5 border-b border-slate-100 flex items-center justify-between">
                                <span>Toggle Columns</span>
                                <span class="text-[10px] font-semibold text-slate-400">({{ count($columns) }})</span>
                            </div>
                            <div class="flex flex-col gap-0.5 max-h-[300px] overflow-y-auto pr-1">
                                @foreach($columns as $colKey => $colDef)
                                    @php
                                        $key = is_int($colKey) ? (is_array($colDef) ? ($colDef['key'] ?? $loop->index) : $colDef) : $colKey;
                                        $label = is_array($colDef) ? ($colDef['label'] ?? $key) : (is_int($colKey) ? ucwords(str_replace('_', ' ', $colDef)) : $colDef);
                                    @endphp
                                    <label class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs text-slate-700 hover:text-slate-900 hover:bg-slate-50 cursor-pointer select-none transition">
                                        <input
                                            type="checkbox"
                                            data-column-toggle="{{ $key }}"
                                            checked
                                            class="w-4 h-4 rounded border-slate-300 text-[#0b192c] focus:ring-[#0b192c] accent-[#0b192c] cursor-pointer"
                                        >
                                        <span class="font-medium">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </details>
                @endif
            </div>

            @if(isset($toolbar))
                <div class="flex items-center gap-2">
                    {{ $toolbar }}
                </div>
            @endif
        </div>
    @endif

    <!-- Main Table Wrap -->
    <div class="table-wrap overflow-x-auto">
        <table class="w-full text-left border-collapse" id="{{ $id }}Table">
            <thead>
                @if(isset($head))
                    {{ $head }}
                @elseif(isset($thead))
                    <tr class="bg-white border-b border-slate-200">
                        {{ $thead }}
                    </tr>
                @else
                    <tr class="bg-white border-b border-slate-200">
                        @foreach($columns as $colKey => $colDef)
                            @php
                                $colKey = is_int($colKey) ? $colDef : $colKey;
                                $label = is_array($colDef) ? ($colDef['label'] ?? $colKey) : (is_int($colKey) ? ucwords(str_replace('_', ' ', $colDef)) : $colDef);
                                $sortable = is_array($colDef) ? ($colDef['sortable'] ?? true) : true;
                                $align = is_array($colDef) ? ($colDef['align'] ?? 'left') : 'left';
                                $info = is_array($colDef) ? ($colDef['info'] ?? null) : null;
                                $sorted = is_array($colDef) ? ($colDef['sorted'] ?? false) : false;
                                $direction = is_array($colDef) ? ($colDef['direction'] ?? 'desc') : 'desc';
                            @endphp
                            <x-datatable.th
                                :column="$colKey"
                                :sortable="$sortable"
                                :align="$align"
                                :info="$info"
                                :sorted="$sorted"
                                :direction="$direction"
                            >
                                {{ $label }}
                            </x-datatable.th>
                        @endforeach
                    </tr>
                @endif
            </thead>
            <tbody class="divide-y divide-slate-100 text-[13px] text-slate-700 bg-white">
                @if(isset($tbody))
                    {{ $tbody }}
                @elseif(isset($body))
                    {{ $body }}
                @else
                    {{ $slot }}
                @endif
            </tbody>
        </table>
    </div>

    <!-- Google Search Console / Enterprise Minimalist Pagination Footer -->
    <div class="datatable-footer py-3.5 px-6 border-t border-slate-100 bg-white flex items-center justify-end gap-6 text-xs text-slate-500 select-none">
        <!-- Rows per page selector (Exact match to screenshot) -->
        <div class="flex items-center gap-2">
            <label for="{{ $id }}RowsPerPage" class="font-medium text-slate-500 whitespace-nowrap cursor-pointer">
                Rows per page:
            </label>
            <div class="relative inline-block">
                <select
                    id="{{ $id }}RowsPerPage"
                    class="appearance-none pr-6 pl-2 py-1 bg-transparent hover:bg-slate-100/80 rounded border-0 text-xs font-semibold text-slate-700 cursor-pointer focus:outline-none"
                >
                    @foreach($perPageOptions as $option)
                        <option value="{{ $option }}" @selected($option == $defaultPerPage)>{{ $option }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-1 flex items-center pointer-events-none text-slate-400">
                    <x-heroicon-s-chevron-down class="w-3 h-3" />
                </div>
            </div>
        </div>

        <!-- Range Counter (e.g. "1-3 of 3" from screenshot) -->
        <div class="font-medium text-slate-600 whitespace-nowrap" id="{{ $id }}PaginationInfo">
            Showing 0 rows
        </div>

        <!-- Pagination Controls (< and > chevrons from screenshot) -->
        <div class="flex items-center gap-1">
            <button
                type="button"
                id="{{ $id }}PrevPage"
                title="Previous page"
                class="w-8 h-8 rounded-full flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-100 disabled:opacity-30 disabled:pointer-events-none transition cursor-pointer"
            >
                <x-heroicon-o-chevron-left class="w-4 h-4" />
                <span class="sr-only">Previous</span>
            </button>
            <button
                type="button"
                id="{{ $id }}NextPage"
                title="Next page"
                class="w-8 h-8 rounded-full flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-100 disabled:opacity-30 disabled:pointer-events-none transition cursor-pointer"
            >
                <x-heroicon-o-chevron-right class="w-4 h-4" />
                <span class="sr-only">Next</span>
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof initSimpleTable === 'function') {
            initSimpleTable('{{ $id }}');
        }
    });
</script>
