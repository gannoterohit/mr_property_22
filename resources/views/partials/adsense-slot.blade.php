@php
    $finalSlotId = $slotId ?? null;
    $placement = $placement ?? null;

    if ($placement) {
        $finalSlotId = \App\Models\Setting::get('adsense_' . $placement . '_id');
    }
@endphp

@if(app()->environment('production') && \App\Models\Setting::get('adsense_enabled') == '1' && \App\Models\Setting::get('adsense_client_id') && $finalSlotId)
    <div class="adsense-slot-container my-6 mx-auto text-center overflow-hidden" style="min-height: 100px;">
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="{{ \App\Models\Setting::get('adsense_client_id') }}"
             data-ad-slot="{{ $finalSlotId }}"
             data-ad-format="auto"
             data-full-width-responsive="true"></ins>
        <script>
             (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
        <p class="text-[10px] text-gray-400 uppercase tracking-widest mt-1">Advertisement</p>
    </div>
@endif
