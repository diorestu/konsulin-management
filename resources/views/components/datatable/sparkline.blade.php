@props([
    'trend' => 'flat', // 'flat', 'peak', 'dip', 'up', 'down'
    'width' => 75,
    'height' => 16,
    'color' => '#6b7280',
])

@php
    $midY = $height / 2;
    $strokeWidth = 2;

    switch ($trend) {
        case 'peak':
            // Flat with spike near the end like row 3 in Google Search Console screenshot
            $path = "M 0 {$midY} L 50 {$midY} L 56 3 L 62 3 L 66 {$midY} L {$width} {$midY}";
            break;
        case 'dip':
            $path = "M 0 {$midY} L 45 {$midY} L 52 " . ($height - 3) . " L 58 " . ($height - 3) . " L 64 {$midY} L {$width} {$midY}";
            break;
        case 'up':
            $path = "M 0 " . ($height - 4) . " L 35 " . ($height - 4) . " L " . ($width - 10) . " 4 L {$width} 4";
            break;
        case 'down':
            $path = "M 0 4 L 35 4 L " . ($width - 10) . " " . ($height - 4) . " L {$width} " . ($height - 4);
            break;
        case 'flat':
        default:
            $path = "M 0 {$midY} L {$width} {$midY}";
            break;
    }
@endphp

<svg
    width="{{ $width }}"
    height="{{ $height }}"
    viewBox="0 0 {{ $width }} {{ $height }}"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    class="inline-block text-slate-500 overflow-visible select-none"
    aria-hidden="true"
>
    <path
        d="{{ $path }}"
        stroke="{{ $color }}"
        stroke-width="{{ $strokeWidth }}"
        stroke-linecap="round"
        stroke-linejoin="round"
    />
</svg>
