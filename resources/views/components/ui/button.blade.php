@props(['variant' => 'primary', 'size' => 'md', 'href' => null, 'icon' => null, 'type' => 'submit'])
@php
    $classes = 'hss-btn'
        .($variant === 'navy' ? '' : ' hss-btn-'.$variant)
        .($size === 'sm' ? ' hss-btn-sm' : '');
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<i class="bi {{ $icon }}" aria-hidden="true"></i>@endif{{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<i class="bi {{ $icon }}" aria-hidden="true"></i>@endif{{ $slot }}
    </button>
@endif
