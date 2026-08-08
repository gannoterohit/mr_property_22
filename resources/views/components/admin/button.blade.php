@props([
    'href' => null,
    'variant' => 'default',
    'icon' => null,
    'type' => 'button',
    'target' => null,
])

@php
    $variantClass = match ($variant) {
        'primary' => 'admin-btn-primary',
        'danger' => 'admin-btn-danger',
        default => '',
    };
@endphp

@if($href)
    <a href="{{ $href }}" @if($target) target="{{ $target }}" @endif {{ $attributes->merge(['class' => trim("admin-btn {$variantClass}")]) }}>
        @if($icon)<i class="fas {{ $icon }}"></i>@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => trim("admin-btn {$variantClass}")]) }}>
        @if($icon)<i class="fas {{ $icon }}"></i>@endif
        {{ $slot }}
    </button>
@endif
