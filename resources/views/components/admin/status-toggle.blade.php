@props([
    'active' => false,
    'activeLabel' => 'Active',
    'inactiveLabel' => 'Inactive',
    'action' => null,
    'form' => null,
    'method' => 'PATCH',
    'title' => null,
    'formClass' => '',
    'dataLabel' => '',
])

@php
    $isActive = (bool) $active;
    $label = $isActive ? $activeLabel : $inactiveLabel;
    $buttonTitle = $title ?: 'Click to '.($isActive ? 'deactivate' : 'activate');
    
    // CSS Classes based on state
    $stateClass = $isActive ? 'toggle-btn-active' : 'toggle-btn-inactive';
    $trackClass = $isActive ? 'toggle-track-active' : 'toggle-track-inactive';
    $knobClass = $isActive ? 'toggle-knob-active' : 'toggle-knob-inactive';
@endphp

<link rel="stylesheet" href="{{ asset('css/status-toggle.css') }}">

@if($action)
    <form action="{{ $action }}" method="POST" class="toggle-form {{ $formClass }}" data-label="{{ $dataLabel }}" data-active="{{ $isActive ? '1' : '0' }}">
        @csrf
        @if(strtoupper($method) !== 'POST')
            @method($method)
        @endif
        
        <button type="submit" class="toggle-btn {{ $stateClass }}" title="{{ $buttonTitle }}">
            <span class="toggle-track {{ $trackClass }}">
                <span class="toggle-knob {{ $knobClass }}"></span>
            </span>
            <span class="toggle-label">{{ $label }}</span>
        </button>
    </form>
@else
    <button type="submit" @if($form) form="{{ $form }}" @endif class="toggle-btn {{ $stateClass }}" title="{{ $buttonTitle }}">
        <span class="toggle-track {{ $trackClass }}">
            <span class="toggle-knob {{ $knobClass }}"></span>
        </span>
        <span class="toggle-label">{{ $label }}</span>
    </button>
@endif
