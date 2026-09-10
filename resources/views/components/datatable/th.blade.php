@props([
    'column' => null,
    'sortable' => true,
    'sorted' => false,
    'direction' => 'desc',
    'align' => 'left',
    'info' => null,
])

<th
    @if($column) data-column="{{ $column }}" @endif
    class="py-3 px-4 text-[13px] font-medium text-slate-500 {{ $align === 'right' ? 'text-right' : 'text-left' }} border-b border-slate-200 select-none"
>
    <div class="inline-flex items-center gap-1.5 {{ $align === 'right' ? 'justify-end' : 'justify-start' }}">
        @if($sortable && $column)
            <button
                type="button"
                data-sort="{{ $column }}"
                class="sortable group inline-flex items-center gap-1 text-[13px] font-medium text-slate-500 hover:text-slate-900 cursor-pointer transition border-0 bg-transparent p-0"
            >
                <span>{{ $slot->isNotEmpty() ? $slot : $attributes->get('label') }}</span>

                @if($sorted)
                    <!-- Sorted Down/Up Indicator arrow (exact match to Validation ↓) -->
                    <span class="text-slate-800 font-bold ml-0.5">
                        {{ $direction === 'asc' ? '↑' : '↓' }}
                    </span>
                @else
                    <span class="text-slate-400 group-hover:text-slate-700 opacity-0 group-hover:opacity-100 transition-opacity text-xs ml-0.5">
                        ↕
                    </span>
                @endif
            </button>
        @else
            <span>{{ $slot->isNotEmpty() ? $slot : $attributes->get('label') }}</span>
        @endif

        @if($info)
            <!-- Help Tooltip (?) Icon (exact match to Source (?)) -->
            <span
                class="inline-flex items-center justify-center w-4 h-4 rounded-full text-slate-400 hover:text-slate-600 cursor-help"
                title="{{ is_string($info) ? $info : 'Informasi kolom' }}"
            >
                <x-heroicon-o-question-mark-circle class="w-4 h-4" />
            </span>
        @endif
    </div>
</th>
