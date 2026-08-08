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

<style>
    /* Form Styling */
    .toggle-form {
        display: inline-flex;
    }

    /* Base Button Styling */
    .toggle-btn {
        display: inline-flex;
        height: 24px; /* h-6 */
        min-width: 90px;
        align-items: center;
        gap: 6px; /* gap-1.5 */
        border-radius: 9999px; /* rounded-full */
        border: 1px solid;
        padding: 0 8px; /* px-2 */
        font-size: 10px;
        font-weight: 800; /* font-extrabold */
        cursor: pointer;
        transition: all 0.2s ease;
    }

    /* Active State Button */
    .toggle-btn-active {
        border-color: #a7f3d0; /* emerald-200 */
        background-color: #ecfdf5; /* emerald-50 */
        color: #047857; /* emerald-700 */
    }
    .toggle-btn-active:hover {
        border-color: #6ee7b7; /* emerald-300 */
        background-color: #d1fae5; /* emerald-100 */
    }

    /* Inactive State Button */
    .toggle-btn-inactive {
        border-color: #fecaca; /* red-200 */
        background-color: #fef2f2; /* red-50 */
        color: #b91c1c; /* red-700 */
    }
    .toggle-btn-inactive:hover {
        border-color: #fca5a5; /* red-300 */
        background-color: #fee2e2; /* red-100 */
    }

    /* Track (Background of switch) */
    .toggle-track {
        position: relative;
        display: inline-flex;
        height: 16px; /* h-4 */
        width: 28px; /* w-7 */
        flex-shrink: 0;
        cursor: pointer;
        border-radius: 9999px;
        transition: background-color 0.3s ease-in-out;
        overflow: hidden;
    }
    .toggle-track-active { background-color: #10b981; /* emerald-500 */ }
    .toggle-track-inactive { background-color: #ef4444; /* red-500 */ }

    /* Knob (The moving circle) */
    .toggle-knob {
        position: absolute;
        left: 2px;
        top: 3px;
        height: 10px; /* h-2.5 */
        width: 10px; /* w-2.5 */
        border-radius: 9999px;
        background-color: #ffffff;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05); /* shadow-sm */
        transition: transform 0.3s ease-in-out;
    }
    .toggle-knob-active { transform: translateX(14px); }
    .toggle-knob-inactive { transform: translateX(0); }

    /* Label Text */
    .toggle-label {
        min-width: 0;
        line-height: 1;
    }
</style>

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
