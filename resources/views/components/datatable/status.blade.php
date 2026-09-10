@props([
    'type' => 'not_started',
    'label' => null,
])

@php
    $normalized = strtolower(str_replace([' ', '-'], '_', $type));
    $displayLabel = $label ?? ucwords(str_replace('_', ' ', $normalized));
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 text-[13px]']) }}>
    @if(in_array($normalized, ['not_started', 'unassigned', 'draft']))
        <!-- Dark Exclamation Circle (Google Search Console 'Not Started' style) -->
        <span class="w-5 h-5 rounded-full bg-[#374151] text-white flex items-center justify-center text-[11px] font-black shrink-0 select-none shadow-xs">
            !
        </span>
        <span class="font-semibold text-slate-800">{{ $displayLabel }}</span>

    @elseif(in_array($normalized, ['passed', 'completed', 'resolved', 'active', 'true', '1']))
        <!-- Outline Check Circle (Google Search Console 'Passed' style) -->
        <span class="w-5 h-5 rounded-full border-2 border-slate-400 text-slate-500 flex items-center justify-center shrink-0 select-none">
            <x-heroicon-o-check class="w-3 h-3 stroke-[2.5]" />
        </span>
        <span class="text-slate-500 font-medium">{{ $displayLabel }}</span>

    @elseif(in_array($normalized, ['in_progress', 'monitoring', 'processing']))
        <!-- Blue Running Circle -->
        <span class="w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center shrink-0 select-none">
            <x-heroicon-o-arrow-path class="w-3 h-3 stroke-[2.5]" />
        </span>
        <span class="font-semibold text-blue-900">{{ $displayLabel }}</span>

    @elseif(in_array($normalized, ['warning', 'waiting_client', 'pending', 'medium']))
        <!-- Amber Exclamation Circle -->
        <span class="w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center text-[11px] font-black shrink-0 select-none shadow-xs">
            !
        </span>
        <span class="font-semibold text-amber-900">{{ $displayLabel }}</span>

    @elseif(in_array($normalized, ['danger', 'high', 'critical', 'error', 'failed', 'open']))
        <!-- Red Exclamation Circle -->
        <span class="w-5 h-5 rounded-full bg-rose-600 text-white flex items-center justify-center text-[11px] font-black shrink-0 select-none shadow-xs">
            !
        </span>
        <span class="font-semibold text-rose-900">{{ $displayLabel }}</span>

    @else
        <span class="w-2 h-2 rounded-full bg-slate-400 shrink-0"></span>
        <span class="text-slate-700">{{ $displayLabel }}</span>
    @endif
</div>
