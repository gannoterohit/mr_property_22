@props([
    'phone' => null,
    'name' => 'there',
    'context' => 'ApnaNest',
    'size' => 'sm',
    'showText' => false,
])

@php
    $rawDigits = preg_replace('/[^0-9]/', '', (string)$phone);
    if (strlen($rawDigits) === 10) {
        $waNumber = '91' . $rawDigits;
    } elseif (strlen($rawDigits) === 11 && str_starts_with($rawDigits, '0')) {
        $waNumber = '91' . substr($rawDigits, 1);
    } elseif (strlen($rawDigits) > 10) {
        $waNumber = $rawDigits;
    } else {
        $waNumber = null;
    }

    $defaultMsg = "Hello " . trim($name) . ", reaching out to you from " . config('app.name', 'ApnaNest') . " regarding " . trim($context) . ".";
    $encodedMsg = urlencode($defaultMsg);
    $waUrl = $waNumber ? "https://wa.me/{$waNumber}?text={$encodedMsg}" : null;

    $sizeClasses = match($size) {
        'xs' => 'h-5 px-1.5 text-[10px] gap-1',
        'md' => 'h-8 px-3 text-xs gap-1.5',
        default => 'h-6 px-2 text-[11px] gap-1',
    };
@endphp

@if($waUrl)
    <a href="{{ $waUrl }}" 
       target="_blank" 
       rel="noopener noreferrer"
       title="Chat with {{ $name }} on WhatsApp" 
       {{ $attributes->merge(['class' => "inline-flex items-center justify-center font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 hover:text-emerald-800 border border-emerald-200/80 rounded-lg transition-all shadow-xs shrink-0 {$sizeClasses}"]) }}>
        <i class="fab fa-whatsapp text-emerald-600 text-xs"></i>
        @if($showText)
            <span>WhatsApp</span>
        @endif
    </a>
@endif
