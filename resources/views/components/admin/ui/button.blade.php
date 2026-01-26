@props([
    'href' => null,
    'icon' => null,
    'color' => 'green',
    'type' => null,
])

@php
    $colorClasses = match($color) {
        'green' => 'bg-green-600 hover:bg-green-700',
        'primary' => 'bg-primary hover:bg-teal-700',
        'red' => 'bg-red-600 hover:bg-red-700',
        default => 'bg-green-600 hover:bg-green-700',
    };
    $baseClasses = "inline-flex items-center gap-2 px-5 py-2.5 {$colorClasses} text-white font-medium rounded-xl transition-all duration-200 shadow-sm hover:shadow-md hover:-translate-y-0.5";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        @if($icon)
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button {{ $type ? "type={$type}" : '' }} {{ $attributes->merge(['class' => $baseClasses]) }}>
        @if($icon)
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif
