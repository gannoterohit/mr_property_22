@if(isset($topAgencies) && $topAgencies->count() > 0)
@php
    $activeAgencyCity = $homeCity ?? $displayCity ?? null;
@endphp
<section class="market-section soft" style="background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); padding: 56px 0;">
    <div class="market-wrap">
        <div class="market-section-head">
            <div>
                <span class="market-kicker" style="color: #4f46e5; font-weight: 850; letter-spacing: 0.08em; font-size: 11px; text-transform: uppercase;">
                    <i class="fas fa-certificate text-emerald-500 mr-1"></i> Verified Partners Directory
                </span>
                <h2 style="font-size: clamp(24px, 3vw, 32px); font-weight: 900; color: #0f172a; margin: 8px 0 6px;">
                    Top Real Estate Agencies {{ $activeAgencyCity ? 'in ' : '& Consultants' }}
                    @if($activeAgencyCity)
                        <span style="color: #4f46e5;">{{ $activeAgencyCity }}</span>
                    @endif
                </h2>
                <p style="color: #64748b; font-size: 14px; margin: 0; max-width: 600px;">
                    Connect directly with licensed consultants and verified agencies{{ $activeAgencyCity ? ' in ' . $activeAgencyCity : '' }}. Zero unlock charges and instant WhatsApp connect.
                </p>
            </div>
            <a href="{{ route('agencies.index', $activeAgencyCity ? ['city' => $activeAgencyCity] : []) }}" style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 800; color: #4f46e5; text-decoration: none; padding: 10px 18px; border-radius: 12px; background: rgba(79, 70, 229, 0.08); transition: all 0.2s ease;">
                <span>View all {{ $activeAgencyCity ? 'in ' . $activeAgencyCity : 'agencies' }}</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 28px;">
            @foreach($topAgencies as $agency)
                @php
                    $agencyDisplayName = $agency->agency_name ?: ($agency->name . ' Real Estate');
                    $operatingCities = $agency->rooms ? $agency->rooms->pluck('city')->filter()->unique()->values() : collect();
                    $primaryCity = $operatingCities->first() ?: ($agency->agency_address ? Str::limit($agency->agency_address, 24) : 'Verified Partner');
                    $rawPhone = trim((string) ($agency->phone ?? ''));
                    $digits = preg_replace('/\D+/', '', $rawPhone);
                    if (strlen($digits) === 10) $digits = '91' . $digits;
                    elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) $digits = '91' . substr($digits, 1);
                    $waMsg = "Hello {$agencyDisplayName}! Maine aapki agency profile dekhi hai. Mujhe rental properties ke baare mein jaankari chahiye.";
                    $waLink = !empty($digits) ? "https://wa.me/{$digits}?text=" . rawurlencode($waMsg) : null;
                @endphp

                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 20px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04); transition: transform 0.2s ease, box-shadow 0.2s ease; display: flex; flex-direction: column; justify-content: space-between;"
                     onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 24px -4px rgba(15, 23, 42, 0.1)';"
                     onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 12px rgba(15, 23, 42, 0.04)';">
                    
                    <div>
                        {{-- Top Agency Header --}}
                        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 14px;">
                            <div style="position: relative; width: 54px; height: 54px; border-radius: 16px; background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 22px; font-weight: 900; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.25); flex-shrink: 0; overflow: hidden;">
                                @if($agency->avatar)
                                    <img src="{{ asset('storage/' . $agency->avatar) }}" alt="{{ $agencyDisplayName }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <span>{{ strtoupper(substr($agencyDisplayName, 0, 1)) }}</span>
                                @endif
                                <span style="position: absolute; bottom: -2px; right: -2px; width: 18px; height: 18px; border-radius: 50%; background: #10b981; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 8px; border: 2px solid #ffffff;">
                                    <i class="fas fa-check"></i>
                                </span>
                            </div>

                            <div style="min-width: 0; flex: 1;">
                                <h3 style="margin: 0; font-size: 16px; font-weight: 850; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <a href="{{ route('agency.show', $agency) }}" style="color: inherit; text-decoration: none;">{{ $agencyDisplayName }}</a>
                                </h3>
                                <p style="margin: 2px 0 0; font-size: 12px; color: #64748b; display: flex; align-items: center; gap: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <span><i class="fas fa-user-tie text-indigo-500"></i> {{ $agency->name }}</span>
                                    <span style="color: #cbd5e1;">·</span>
                                    <span style="color: #059669; font-weight: 700;"><i class="fas fa-location-dot text-rose-500"></i> {{ $primaryCity }}</span>
                                </p>
                            </div>
                        </div>

                        {{-- Stats Row --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; background: #f8fafc; border-radius: 12px; padding: 10px 12px; margin-bottom: 16px; border: 1px solid #f1f5f9;">
                            <div>
                                <span style="display: block; font-size: 10px; font-weight: 700; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.04em;">Properties</span>
                                <span style="display: flex; align-items: center; gap: 4px; font-size: 13px; font-weight: 900; color: #0f172a; margin-top: 2px;">
                                    <i class="fas fa-house-circle-check text-emerald-500 text-xs"></i>
                                    {{ $agency->active_rooms_count }} Active
                                </span>
                            </div>
                            <div>
                                <span style="display: block; font-size: 10px; font-weight: 700; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.04em;">Status</span>
                                <span style="display: flex; align-items: center; gap: 4px; font-size: 12.5px; font-weight: 800; color: #d97706; margin-top: 2px;">
                                    <i class="fas fa-award text-amber-500 text-xs"></i>
                                    KYC Verified
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <a href="{{ route('agency.show', $agency) }}"
                           style="flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 14px; border-radius: 12px; background: #0f172a; color: #ffffff; font-size: 12.5px; font-weight: 800; text-decoration: none; transition: background 0.2s ease;">
                            <span>View Portfolio</span>
                            <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
                        </a>

                        @if($waLink)
                            <a href="{{ $waLink }}" target="_blank" rel="noopener"
                               style="width: 40px; height: 40px; border-radius: 12px; background: #25D366; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 16px; text-decoration: none; flex-shrink: 0; box-shadow: 0 4px 10px rgba(37, 211, 102, 0.28);"
                               title="Chat on WhatsApp">
                                <i class="fa-brands fa-whatsapp"></i>
                            </a>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
