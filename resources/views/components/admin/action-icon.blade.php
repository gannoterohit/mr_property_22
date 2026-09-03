@props([
    'href' => null,
    'variant' => 'edit',
    'title' => null,
    'icon' => null,
    'type' => 'button',
    'form' => null,
    'target' => null,
])

@php
    $icons = [
        'view' => 'fa-eye',
        'edit' => 'fa-pen',
        'delete' => 'fa-trash-can',
        'remove' => 'fa-trash-can',
    ];
    $labels = [
        'view' => 'View',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'remove' => 'Remove',
    ];
    $isDanger = in_array($variant, ['delete', 'remove'], true);
    $iconClass = $icon ?: ($icons[$variant] ?? 'fa-circle');
    $label = $title ?: ($labels[$variant] ?? ucfirst($variant));
    $classes = $isDanger ? 'admin-action-icon admin-action-icon-danger' : 'admin-action-icon';
@endphp

@if($href)
    <a href="{{ $href }}" @if($target) target="{{ $target }}" @endif {{ $attributes->merge(['class' => $classes, 'title' => $label, 'aria-label' => $label]) }}>
        <i class="fas {{ $iconClass }}"></i>
    </a>
@else
    <button type="{{ $type }}" @if($form) form="{{ $form }}" @endif {{ $attributes->merge(['class' => $classes, 'title' => $label, 'aria-label' => $label]) }}>
        <i class="fas {{ $iconClass }}"></i>
    </button>
@endif
