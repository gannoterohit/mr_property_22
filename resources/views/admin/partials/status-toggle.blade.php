@php
    $activeText = $activeText ?? 'Active';
    $inactiveText = $inactiveText ?? 'Inactive';
    $method = strtoupper($method ?? 'POST');
    $active = (bool) $active;
    $actionText = $active ? 'Deactivate' : 'Activate';
@endphp

<form action="{{ $action }}" method="POST" class="admin-confirm inline-flex"
      data-confirm-title="{{ $actionText }} {{ $label }}?"
      data-confirm-text="{{ $active ? $deactivateText : $activateText }}"
      data-confirm-button="Yes, {{ strtolower($actionText) }}"
      data-confirm-color="{{ $active ? '#dc2626' : '#059669' }}">
    @csrf
    @if($method !== 'POST') @method($method) @endif
    <button type="submit"
            class="inline-flex h-8 w-[104px] items-center justify-start gap-2 rounded-full border px-2 text-[10px] font-bold transition-colors {{ $active ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' }}"
            title="Click to {{ strtolower($actionText) }}">
        <span class="relative inline-flex h-4 w-7 shrink-0 rounded-full transition-colors {{ $active ? 'bg-emerald-500' : 'bg-red-500' }}">
            <span class="absolute left-0.5 top-0.5 h-3 w-3 rounded-full bg-white shadow-sm transition-transform duration-200 {{ $active ? 'translate-x-3' : 'translate-x-0' }}"></span>
        </span>
        <span class="inline-block w-12 truncate text-left">{{ $active ? $activeText : $inactiveText }}</span>
    </button>
</form>
