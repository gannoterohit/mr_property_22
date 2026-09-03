@extends('layouts.admin')

@section('title', 'Business Settings')

@section('admin-content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-settings.css') }}">
    <link rel="stylesheet" href="{{ asset('css/status-toggle.css') }}">
@endpush
<div id="business-settings-tabs" class="flex flex-col min-h-0 bg-gray-50">
    
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 shadow-sm z-10 w-full">
        <div class="max-w-7xl mx-auto px-8 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Business Settings</h1>
                <p class="text-gray-500 text-sm mt-1">Manage global configurations for your platform</p>
            </div>
             <div class="flex gap-3">
                 <form action="{{ route('admin.settings.ping') }}" method="POST">
                    @csrf
                    <button type="submit" class="admin-theme-text admin-theme-hover-text font-medium text-sm flex items-center px-4 py-2 rounded-lg hover:bg-slate-50 transition border border-transparent hover:border-slate-200" title="Notify Google/Bing about new content">
                        <i class="fas fa-satellite-dish mr-2"></i> Ping Search Engines
                    </button>
                </form>
                      </div>
     </div>


    <!-- Horizontal Settings Navigation -->
    <div class="sticky top-16 z-20 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur md:px-6">
        <div class="mx-auto flex max-w-7xl gap-2 overflow-x-auto pb-1" role="tablist" aria-label="Business settings sections">
            @foreach([
                ['general', 'fa-sliders-h', 'General & Fees'],
                ['broker', 'fa-handshake text-emerald-600', 'Broker & Agent'],
                ['appearance', 'fa-paint-brush', 'Appearance'],
                ['payment', 'fa-credit-card', 'Payment'],
                ['integrations', 'fa-plug', 'Integrations'],
                ['firebase', 'fa-fire text-amber-500', 'Firebase Push'],
                ['sms', 'fa-comment-sms text-indigo-500', 'SMS & OTP'],
                ['seo', 'fa-search', 'SEO & Analytics'],
                ['mail', 'fa-envelope', 'Mail Server'],
                ['referral', 'fa-toggle-on', 'Feature Toggles'],
                ['modal', 'fa-window-restore text-blue-500', 'Promo Popup Modal'],
            ] as [$tabKey, $tabIcon, $tabLabel])
                <button type="button" data-settings-tab="{{ $tabKey }}" class="inline-flex h-10 shrink-0 items-center gap-2 whitespace-nowrap rounded-lg bg-slate-50 px-4 text-xs font-bold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900" role="tab" aria-selected="false">
                    <i class="fas {{ $tabIcon }} text-[11px]"></i><span>{{ $tabLabel }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Layout Container -->
    <div class="flex flex-1 items-start">

        <!-- Main Content Area -->
        <div class="flex-1 min-w-0 bg-gray-50 p-4 md:p-6 flex flex-col items-center">

            <form id="settings-form" action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="w-full max-w-5xl space-y-8 pb-12">
                @csrf
                {{-- Active tab tracker: JS sets this before submit so controller can redirect back to same tab --}}
                <input type="hidden" name="_active_tab" id="active_tab_input" value="general">

                <!-- General Section -->
                <div data-settings-panel="general" class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <div class="flex items-center mb-6 pb-4 border-b border-gray-100">
                             <div class="h-10 w-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center mr-4">
                                <i class="fas fa-rupee-sign text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">Fee Configuration</h2>
                                <p class="text-sm text-gray-500">Set the pricing model for your platform</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="rounded-xl border border-gray-200 p-4">
                                <label class="mb-4 flex items-start justify-between gap-4">
                                    <span>
                                        <span class="block text-sm font-bold text-gray-800">Enable Listing Fee</span>
                                        <span class="mt-1 block text-xs text-gray-500">OFF = every new listing is free</span>
                                    </span>
                                    <span class="relative inline-flex shrink-0 items-center">
                                        <input type="checkbox" name="listing_fee_enabled" value="1" class="peer sr-only" @checked(filter_var(\App\Models\Setting::get('listing_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN))>
                                        <span class="admin-switch-track h-6 w-11 rounded-full transition"></span>
                                        <span class="absolute left-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                                    </span>
                                </label>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Listing Fee</label>
                                <div class="relative rounded-lg shadow-sm group transition-all focus-within:ring-2 ring-[rgba(var(--admin-primary-rgb),.2)]">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 font-medium sm:text-sm">&#8377;</span>
                                    </div>
                                    <input type="number" name="listing_fee" value="{{ \App\Models\Setting::get('listing_fee', 199) }}" class="block w-full pl-8 pr-3 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm font-medium text-gray-900" placeholder="0.00">
                                </div>
                                <p class="mt-2 text-xs text-gray-500">Amount is saved even while the fee toggle is OFF.</p>
                            </div>
                            
                             <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Featured Fee</label>
                                <div class="relative rounded-lg shadow-sm group transition-all focus-within:ring-2 ring-[rgba(var(--admin-primary-rgb),.2)]">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 font-medium sm:text-sm">&#8377;</span>
                                    </div>
                                    <input type="number" name="featured_fee" value="{{ \App\Models\Setting::get('featured_fee', 99) }}" class="block w-full pl-8 pr-3 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm font-medium text-gray-900" placeholder="0.00">
                                </div>
                                <p class="mt-2 text-xs text-gray-500">To highlight a property</p>
                            </div>
                            
                             <div class="rounded-xl border border-gray-200 p-4">
                                <label class="mb-4 flex items-start justify-between gap-4">
                                    <span>
                                        <span class="block text-sm font-bold text-gray-800">Enable Contact Unlock Fee</span>
                                        <span class="mt-1 block text-xs text-gray-500">OFF = every contact unlock is free</span>
                                    </span>
                                    <span class="relative inline-flex shrink-0 items-center">
                                        <input type="checkbox" name="unlock_fee_enabled" value="1" class="peer sr-only" @checked(filter_var(\App\Models\Setting::get('unlock_fee_enabled', '0'), FILTER_VALIDATE_BOOLEAN))>
                                        <span class="admin-switch-track h-6 w-11 rounded-full transition"></span>
                                        <span class="absolute left-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                                    </span>
                                </label>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Unlock Fee</label>
                                <div class="relative rounded-lg shadow-sm group transition-all focus-within:ring-2 ring-[rgba(var(--admin-primary-rgb),.2)]">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 font-medium sm:text-sm">&#8377;</span>
                                    </div>
                                    <input type="number" name="unlock_fee" value="{{ \App\Models\Setting::get('unlock_fee', 49) }}" class="block w-full pl-8 pr-3 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm font-medium text-gray-900" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Appearance Section -->
                <div data-settings-panel="appearance" class="space-y-6" hidden>
                    
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                         <div class="flex items-center mb-6 pb-4 border-b border-gray-100">
                              <div class="h-10 w-10 rounded-lg admin-theme-soft flex items-center justify-center mr-4">
                                 <i class="fas fa-palette text-xl"></i>
                             </div>
                             <div>
                                 <h2 class="text-xl font-bold text-gray-800">Branding & Identity</h2>
                                 <p class="text-sm text-gray-500">Look and feel of your website</p>
                             </div>
                         </div>

                         <div class="settings-subtabs mb-6" role="tablist" aria-label="Appearance settings sections">
                             <button type="button" data-appearance-tab="branding" aria-selected="true"><i class="fas fa-image"></i>Branding</button>
                             <button type="button" data-appearance-tab="colors" aria-selected="false"><i class="fas fa-droplet"></i>Colors</button>
                             <button type="button" data-appearance-tab="contact" aria-selected="false"><i class="fas fa-address-book"></i>Contact</button>
                             <button type="button" data-appearance-tab="social" aria-selected="false"><i class="fas fa-share-nodes"></i>Social</button>
                         </div>
                         
                         <div data-appearance-subpanel="branding" class="space-y-8">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Website Logo (Navbar)</label>
                                <div class="flex items-start gap-6">
                                    <div class="h-24 w-24 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden relative group">
                                         @if(\App\Models\Setting::get('navbar_logo'))
                                            <img src="{{ \App\Models\Setting::mediaUrl(\App\Models\Setting::get('navbar_logo')) }}" class="h-full w-full object-contain p-2">
                                        @else
                                            <i class="fas fa-image text-gray-300 text-3xl"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                         <label class="cursor-pointer bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg inline-flex items-center transition shadow-sm admin-theme-hover-card admin-theme-hover-text">
                                            <i class="fas fa-upload mr-2"></i> Upload Navbar Logo
                                            <input type="file" name="navbar_logo" class="hidden">
                                        </label>
                                        <p class="mt-2 text-xs text-gray-500">Used in the header/navbar. Recommended: 40x40px.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Footer Logo</label>
                                <div class="flex items-start gap-6">
                                    <div class="h-24 w-24 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden relative group">
                                         @if(\App\Models\Setting::get('footer_logo'))
                                            <img src="{{ \App\Models\Setting::mediaUrl(\App\Models\Setting::get('footer_logo')) }}" class="h-full w-full object-contain p-2">
                                        @else
                                            <i class="fas fa-image text-gray-300 text-3xl"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                         <label class="cursor-pointer bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg inline-flex items-center transition shadow-sm admin-theme-hover-card admin-theme-hover-text">
                                            <i class="fas fa-upload mr-2"></i> Upload Footer Logo
                                            <input type="file" name="footer_logo" class="hidden">
                                        </label>
                                        <p class="mt-2 text-xs text-gray-500">Used in the footer. Recommended: 48x48px.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Website Logo (General)</label>
                                <div class="flex items-start gap-6">
                                    <div class="h-24 w-24 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden relative group">
                                         @if(\App\Models\Setting::get('website_logo'))
                                            <img src="{{ \App\Models\Setting::mediaUrl(\App\Models\Setting::get('website_logo')) }}" class="h-full w-full object-contain p-2">
                                        @else
                                            <i class="fas fa-image text-gray-300 text-3xl"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                         <label class="cursor-pointer bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg inline-flex items-center transition shadow-sm admin-theme-hover-card admin-theme-hover-text">
                                            <i class="fas fa-upload mr-2"></i> Upload New Logo
                                            <input type="file" name="website_logo" class="hidden">
                                        </label>
                                        <p class="mt-2 text-xs text-gray-500">Recommended size: 200x50px. Max: 2MB. Formats: PNG, JPG.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-3">Favicon (Browser Icon)</label>
                                <div class="flex items-start gap-6">
                                    <div class="h-24 w-24 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden relative group">
                                         @if(\App\Models\Setting::get('website_favicon'))
                                            <img src="{{ \App\Models\Setting::mediaUrl(\App\Models\Setting::get('website_favicon'), 'favicon.ico') }}" class="h-full w-full object-contain p-2">
                                        @else
                                            <i class="fas fa-star text-gray-300 text-3xl"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                         <label class="cursor-pointer bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg inline-flex items-center transition shadow-sm admin-theme-hover-card admin-theme-hover-text">
                                            <i class="fas fa-upload mr-2"></i> Upload Favicon
                                            <input type="file" name="website_favicon" class="hidden" accept="image/x-icon,image/png">
                                        </label>
                                        <p class="mt-2 text-xs text-gray-500">Recommended: 32x32px or 64x64px. Formats: ICO, PNG, SVG. Max: 1MB.</p>
                                        <p class="mt-1 text-xs admin-theme-text"><i class="fas fa-info-circle mr-1"></i>Shows in browser tab next to page title</p>
                                    </div>
                                </div>
                             </div>
                             
                             <div>
                                 <label class="block text-sm font-semibold text-gray-700 mb-3">Owner CTA Section Image</label>
                                 <div class="flex items-start gap-6">
                                     <div class="h-24 w-24 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden relative group">
                                         @if(\App\Models\Setting::get('owner_cta_image'))
                                             <img src="{{ \App\Models\Setting::mediaUrl(\App\Models\Setting::get('owner_cta_image'), 'assets/images/proparty.png') }}" class="h-full w-full object-cover">
                                         @else
                                             <i class="fas fa-image text-gray-300 text-3xl"></i>
                                         @endif
                                     </div>
                                     <div class="flex-1">
                                          <label class="cursor-pointer bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg inline-flex items-center transition shadow-sm admin-theme-hover-card admin-theme-hover-text">
                                             <i class="fas fa-upload mr-2"></i> Upload Owner CTA Image
                                             <input type="file" name="owner_cta_image" class="hidden">
                                         </label>
                                         <p class="mt-2 text-xs text-gray-500">Shown in the "For Property Owners" section. Recommended: 600x400px. Max: 2MB.</p>
                                     </div>
                                 </div>
                             </div>
                             
                              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                 <div>
                                     <label class="block text-sm font-semibold text-gray-700 mb-2">Website Name</label>
                                     <input type="text" name="website_name" value="{{ \App\Models\Setting::get('website_name', 'RoomRental') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm">
                                 </div>
                             </div>
                             <div>
                                 <label class="block text-sm font-semibold text-gray-700 mb-3">Default Hero Image</label>
                                 <div class="flex items-start gap-6">
                                     <div class="h-24 w-40 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden relative group">
                                         @if(\App\Models\Setting::get('default_hero_image'))
                                             <img src="{{ \App\Models\Setting::mediaUrl(\App\Models\Setting::get('default_hero_image')) }}" class="h-full w-full object-cover">
                                         @else
                                             <i class="fas fa-panorama text-gray-300 text-3xl"></i>
                                         @endif
                                     </div>
                                     <div class="flex-1">
                                         <label class="cursor-pointer bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg inline-flex items-center transition shadow-sm admin-theme-hover-card admin-theme-hover-text">
                                             <i class="fas fa-upload mr-2"></i> Upload Default Hero Image
                                             <input type="file" name="default_hero_image" class="hidden" accept="image/jpeg,image/png,image/webp">
                                         </label>
                                         <p class="mt-2 text-xs text-gray-500">Shown on the homepage when no city-specific hero image is available. Recommended: 1920x800px. Max: 3MB.</p>
                                     </div>
                                 </div>
                             </div>
                         </div>

                         <div data-appearance-subpanel="colors" class="space-y-6" hidden>
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                 <div>
                                     <label class="block text-sm font-semibold text-gray-700 mb-2">Primary Color</label>
                                     <div class="flex items-center gap-3">
                                          <input type="color" id="primary_color" value="{{ \App\Models\Setting::get('primary_color', '#4F46E5') }}" class="h-12 w-16 rounded-lg cursor-pointer border border-gray-200 p-1">
                                          <input type="text" name="primary_color" value="{{ \App\Models\Setting::get('primary_color', '#4F46E5') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="#4F46E5" id="primary_color_text">
                                     </div>
                                     <p class="mt-1 text-xs text-gray-500">Main brand color for headers, buttons, links</p>
                                 </div>
                                 <div>
                                     <label class="block text-sm font-semibold text-gray-700 mb-2">Secondary Color</label>
                                     <div class="flex items-center gap-3">
                                          <input type="color" id="secondary_color" value="{{ \App\Models\Setting::get('secondary_color', '#10B981') }}" class="h-12 w-16 rounded-lg cursor-pointer border border-gray-200 p-1">
                                          <input type="text" name="secondary_color" value="{{ \App\Models\Setting::get('secondary_color', '#10B981') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="#10B981" id="secondary_color_text">
                                     </div>
                                     <p class="mt-1 text-xs text-gray-500">Accent color for success states, highlights</p>
                                 </div>
                             </div>
                         </div>

                         <div data-appearance-subpanel="contact" class="space-y-6" hidden>
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                 <div>
                                     <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Phone</label>
                                     <input type="text" name="contact_phone" value="{{ \App\Models\Setting::get('contact_phone') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm" placeholder="+91 9340058914">
                                 </div>
                                 <div>
                                     <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Email</label>
                                     <input type="email" name="contact_email" value="{{ \App\Models\Setting::get('contact_email') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm" placeholder="support@roomrental.com">
                                 </div>
                             </div>
                             
                             <div>
                                 <label class="block text-sm font-semibold text-gray-700 mb-2">Company Address</label>
                                 <textarea name="company_address" rows="3" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm" placeholder="Enter your company address">{{ \App\Models\Setting::get('company_address') }}</textarea>
                                 <p class="mt-1 text-xs text-gray-500">This will be displayed on the Contact Us page and footer</p>
                             </div>

                             <div>
                                 <label class="block text-sm font-semibold text-gray-700 mb-2">Business Hours</label>
                                 <input type="text" name="business_hours" value="{{ \App\Models\Setting::get('business_hours') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm" placeholder="Mon - Sun: 9AM - 8PM">
                                 <p class="mt-1 text-xs text-gray-500">Displayed on the Contact Us page and footer</p>
                             </div>
                         </div>

                         <div data-appearance-subpanel="social" class="space-y-6" hidden>
                             <div>
                                 <label class="block text-sm font-semibold text-gray-700 mb-3">Social Media Links</label>
                                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                     <div>
                                         <label class="block text-xs text-gray-600 mb-1">Facebook URL</label>
                                         <input type="url" name="facebook_url" value="{{ \App\Models\Setting::get('facebook_url') }}" class="block w-full px-4 py-2 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white text-sm" placeholder="https://facebook.com/yourpage">
                                     </div>
                                     <div>
                                         <label class="block text-xs text-gray-600 mb-1">Twitter URL</label>
                                         <input type="url" name="twitter_url" value="{{ \App\Models\Setting::get('twitter_url') }}" class="block w-full px-4 py-2 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white text-sm" placeholder="https://twitter.com/yourhandle">
                                     </div>
                                     <div>
                                         <label class="block text-xs text-gray-600 mb-1">Instagram URL</label>
                                         <input type="url" name="instagram_url" value="{{ \App\Models\Setting::get('instagram_url') }}" class="block w-full px-4 py-2 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white text-sm" placeholder="https://instagram.com/yourprofile">
                                     </div>
                                     <div>
                                         <label class="block text-xs text-gray-600 mb-1">LinkedIn URL</label>
                                         <input type="url" name="linkedin_url" value="{{ \App\Models\Setting::get('linkedin_url') }}" class="block w-full px-4 py-2 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white text-sm" placeholder="https://linkedin.com/company/yourcompany">
                                     </div>
                                 </div>
                                 <p class="mt-2 text-xs text-gray-500">Social media links will appear in the footer. Leave blank to hide.</p>
                             </div>
                         </div>
                    </div>
                </div>
                
                <!-- Mail Section -->
                 <div data-settings-panel="mail" class="space-y-6 w-full" hidden>
                     <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 w-full">
                         <div class="flex items-center mb-6 pb-4 border-b border-gray-100">
                             <div class="h-10 w-10 rounded-lg admin-theme-soft flex items-center justify-center mr-4">
                                <i class="fas fa-envelope text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">Mail Server (SMTP)</h2>
                                <p class="text-sm text-gray-500">Email delivery configuration</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Host</label>
                                <input type="text" name="mail_host" value="{{ \App\Models\Setting::get('mail_host', 'smtp.gmail.com') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm">
                            </div>
                             <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Port</label>
                                <input type="number" name="mail_port" value="{{ \App\Models\Setting::get('mail_port', '587') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                                <input type="text" name="mail_username" value="{{ \App\Models\Setting::get('mail_username') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm">
                            </div>
                             <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                                <input type="password" name="mail_password" value="" autocomplete="new-password" placeholder="Leave blank to keep the current password" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm">
                            </div>
                        </div>
                        
                         <div class="mt-4 p-4 admin-theme-soft rounded-lg flex items-start gap-3">
                             <i class="fas fa-info-circle admin-theme-text mt-1"></i>
                             <p class="text-sm admin-theme-text">You may need to clear cache after updating mail settings.</p>
                         </div>
                    </div>
                </div>

                <!-- Referral Section (Feature Toggles) -->
                 <div data-settings-panel="referral" class="space-y-6 w-full" hidden>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 w-full">
                        <div class="flex items-center mb-6 pb-4 border-b border-gray-100">
                             <div class="h-10 w-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center mr-4">
                                <i class="fas fa-toggle-on text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">Feature Toggles</h2>
                                <p class="text-sm text-gray-500">Control active platform components</p>
                            </div>
                            <span id="toggle-save-status" class="hidden text-xs font-bold text-emerald-600 ml-auto"></span>
                        </div>
                        
                        <div class="space-y-5">
                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800">Referral System</h4>
                                    <p class="text-xs text-slate-500 mt-1">Allow renters to refer friends and get 1 Free Contact Unlock per join</p>
                                </div>
                                <input type="hidden" name="referral_enabled" value="{{ \App\Models\Setting::isEnabled('referral_enabled', true) ? '1' : '0' }}">
                                <button type="button" class="toggle-btn feature-toggle" data-target="referral_enabled" data-default="1"><span class="toggle-track"><span class="toggle-knob"></span></span><span class="toggle-label">ON</span></button>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800">Wallet System</h4>
                                    <p class="text-xs text-slate-500 mt-1">Allow renters to use balance and view logs</p>
                                </div>
                                <input type="hidden" name="wallet_enabled" value="{{ \App\Models\Setting::isEnabled('wallet_enabled', true) ? '1' : '0' }}">
                                <button type="button" class="toggle-btn feature-toggle" data-target="wallet_enabled" data-default="1"><span class="toggle-track"><span class="toggle-knob"></span></span><span class="toggle-label">ON</span></button>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800">Promo Codes</h4>
                                    <p class="text-xs text-slate-500 mt-1">Allow users to enter coupons during payments</p>
                                </div>
                                <input type="hidden" name="promo_enabled" value="{{ \App\Models\Setting::isEnabled('promo_enabled', true) ? '1' : '0' }}">
                                <button type="button" class="toggle-btn feature-toggle" data-target="promo_enabled" data-default="1"><span class="toggle-track"><span class="toggle-knob"></span></span><span class="toggle-label">ON</span></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Section -->
                <div data-settings-panel="payment" class="space-y-6" hidden>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                         <div class="flex items-center mb-6 pb-4 border-b border-gray-100">
                             <div class="h-10 w-10 rounded-lg admin-theme-soft flex items-center justify-center mr-4">
                                <i class="fas fa-credit-card text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">Razorpay Configuration</h2>
                                <p class="text-sm text-gray-500">Secure payments integration</p>
                            </div>
                        </div>
                         <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Key ID</label>
                                <input type="text" name="razorpay_key" value="{{ \App\Models\Setting::get('razorpay_key') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="rzp_test_...">
                            </div>
                             <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Key Secret</label>
                                <input type="password" name="razorpay_secret" value="" autocomplete="new-password" placeholder="Leave blank to keep the current secret" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Webhook Secret</label>
                                <input type="password" name="razorpay_webhook_secret" value="" autocomplete="new-password" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="Leave blank to keep the current secret">
                                <p class="mt-2 text-xs text-gray-500">Razorpay Dashboard - Webhooks - create secret. Webhook URL: <code class="bg-gray-100 px-1 rounded">{{ url('/api/v1/webhook/razorpay') }}</code></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                 <!-- Integrations Section -->
                 <div data-settings-panel="integrations" class="space-y-6" hidden>
                     <div class="settings-subtabs" role="tablist" aria-label="Integrations settings sections">
                         <button type="button" data-integrations-tab="maps" aria-selected="true"><i class="fas fa-map-marked-alt"></i>Google Maps</button>
                         <button type="button" data-integrations-tab="mobile_app" aria-selected="false"><i class="fas fa-mobile-screen-button"></i>Mobile App</button>
                         <button type="button" data-integrations-tab="social_login" aria-selected="false"><i class="fas fa-share-nodes"></i>Social Login</button>
                     </div>

                     <div data-integrations-subpanel="maps" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                         <div class="flex items-center mb-6 pb-4 border-b border-gray-100">
                             <div class="h-10 w-10 rounded-lg bg-red-100 text-red-600 flex items-center justify-center mr-4">
                                 <i class="fas fa-map-marked-alt text-xl"></i>
                             </div>
                             <div>
                                 <h2 class="text-xl font-bold text-gray-800">Google Maps Integration</h2>
                                 <p class="text-sm text-gray-500">For location services and maps</p>
                             </div>
                         </div>
                         <div>
                             <label class="block text-sm font-semibold text-gray-700 mb-2">API Key</label>
                             <input type="text" name="google_maps_api_key" value="{{ \App\Models\Setting::get('google_maps_api_key') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono">
                         </div>
                     </div>

                     <div data-integrations-subpanel="mobile_app" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                         <div class="flex items-center mb-6 pb-4 border-b border-gray-100">
                             <div class="h-10 w-10 rounded-lg admin-theme-soft flex items-center justify-center mr-4">
                                 <i class="fas fa-mobile-screen-button text-xl"></i>
                             </div>
                             <div>
                                 <h2 class="text-xl font-bold text-gray-800">Mobile App Links</h2>
                                 <p class="text-sm text-gray-500">Footer buttons appear only after a store link is saved</p>
                             </div>
                         </div>
                         <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                             <div>
                                 <label class="block text-sm font-semibold text-gray-700 mb-2">Google Play Store Link</label>
                                 <input type="url" name="play_store_url" value="{{ \App\Models\Setting::get('play_store_url') }}" class="block w-full rounded-lg border-gray-200 bg-gray-50 px-4 py-3 text-sm transition-colors  focus:bg-white focus:ring-0" placeholder="https://play.google.com/store/apps/details?id=...">
                             </div>
                             <div>
                                 <label class="block text-sm font-semibold text-gray-700 mb-2">Apple App Store Link</label>
                                 <input type="url" name="app_store_url" value="{{ \App\Models\Setting::get('app_store_url') }}" class="block w-full rounded-lg border-gray-200 bg-gray-50 px-4 py-3 text-sm transition-colors  focus:bg-white focus:ring-0" placeholder="https://apps.apple.com/app/...">
                             </div>
                         </div>
                     </div>

                     <div data-integrations-subpanel="social_login" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                         <div class="flex items-center mb-6 pb-4 border-b border-gray-100">
                             <div class="h-10 w-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mr-4">
                                 <i class="fas fa-share-nodes text-xl"></i>
                             </div>
                             <div>
                                 <h2 class="text-xl font-bold text-gray-800">Social Login</h2>
                                 <p class="text-sm text-gray-500">Enable Google / Facebook quick login for users</p>
                             </div>
                         </div>

                         <div class="grid grid-cols-1 gap-6">
                             <div class="rounded-xl border border-gray-200 p-5">
                                 <div class="flex items-center justify-between mb-4">
                                     <div>
                                         <span class="block text-sm font-bold text-gray-800">Google Login</span>
                                         <span class="mt-1 block text-xs text-gray-500">Allow users to sign in with their Google account</span>
                                     </div>
                                     <span class="relative inline-flex shrink-0 items-center">
                                         <input type="checkbox" name="google_login_enabled" value="1" class="peer sr-only" @checked(\App\Models\Setting::isEnabled('google_login_enabled', false))>
                                         <span class="admin-switch-track h-6 w-11 rounded-full bg-gray-200 transition"></span>
                                         <span class="absolute left-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                                     </span>
                                 </div>
                                 <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                     <div>
                                         <label class="block text-xs font-semibold text-gray-600 mb-1">Client ID</label>
                                         <input type="text" name="google_client_id" value="{{ \App\Models\Setting::get('google_client_id') }}" class="block w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-2.5 text-sm transition-colors focus:bg-white focus:ring-0 font-mono" placeholder="xxxx.apps.googleusercontent.com">
                                     </div>
                                     <div>
                                         <label class="block text-xs font-semibold text-gray-600 mb-1">Client Secret</label>
                                         <input type="password" name="google_client_secret" value="" autocomplete="new-password" class="block w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-2.5 text-sm transition-colors focus:bg-white focus:ring-0 font-mono" placeholder="Leave blank to keep current secret">
                                     </div>
                                 </div>
                                 <div class="mt-3">
                                     <label class="block text-xs font-semibold text-gray-600 mb-1">Redirect URL</label>
                                     <input type="url" name="google_redirect_url" value="{{ \App\Models\Setting::get('google_redirect_url', url('/auth/google/callback')) }}" class="block w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-2.5 text-sm transition-colors focus:bg-white focus:ring-0 font-mono" placeholder="https://yourdomain.com/auth/google/callback">
                                 </div>
                             </div>

                             <div class="rounded-xl border border-gray-200 p-5">
                                 <div class="flex items-center justify-between mb-4">
                                     <div>
                                         <span class="block text-sm font-bold text-gray-800">Facebook Login</span>
                                         <span class="mt-1 block text-xs text-gray-500">Allow users to sign in with their Facebook account</span>
                                     </div>
                                     <span class="relative inline-flex shrink-0 items-center">
                                         <input type="checkbox" name="facebook_login_enabled" value="1" class="peer sr-only" @checked(\App\Models\Setting::isEnabled('facebook_login_enabled', false))>
                                         <span class="admin-switch-track h-6 w-11 rounded-full bg-gray-200 transition"></span>
                                         <span class="absolute left-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                                     </span>
                                 </div>
                                 <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                     <div>
                                         <label class="block text-xs font-semibold text-gray-600 mb-1">App ID</label>
                                         <input type="text" name="facebook_client_id" value="{{ \App\Models\Setting::get('facebook_client_id') }}" class="block w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-2.5 text-sm transition-colors focus:bg-white focus:ring-0 font-mono" placeholder="Facebook App ID">
                                     </div>
                                     <div>
                                         <label class="block text-xs font-semibold text-gray-600 mb-1">App Secret</label>
                                         <input type="password" name="facebook_client_secret" value="" autocomplete="new-password" class="block w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-2.5 text-sm transition-colors focus:bg-white focus:ring-0 font-mono" placeholder="Leave blank to keep current secret">
                                     </div>
                                 </div>
                                 <div class="mt-3">
                                     <label class="block text-xs font-semibold text-gray-600 mb-1">Redirect URL</label>
                                     <input type="url" name="facebook_redirect_url" value="{{ \App\Models\Setting::get('facebook_redirect_url', url('/auth/facebook/callback')) }}" class="block w-full rounded-lg border-gray-200 bg-gray-50 px-3 py-2.5 text-sm transition-colors focus:bg-white focus:ring-0 font-mono" placeholder="https://yourdomain.com/auth/facebook/callback">
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>

                <!-- Firebase Push Notification Section -->
                <div data-settings-panel="firebase" class="space-y-6" hidden>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <div class="flex items-center mb-6 pb-4 border-b border-gray-100">
                            <div class="h-10 w-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center mr-4">
                                <i class="fas fa-fire text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">Firebase Push Notifications (FCM)</h2>
                                <p class="text-sm text-gray-500">Configure FCM credentials for Mobile App & Web Browser Push Notifications</p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="flex items-center justify-between p-4 bg-amber-50 rounded-xl border border-amber-200">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-800">Firebase Push Notifications</h4>
                                    <p class="text-xs text-slate-500 mt-1">Enable/Disable FCM push alerts globally across Web and App</p>
                                </div>
                                <select name="firebase_push_enabled" class="rounded-lg border-slate-200 text-xs font-bold text-slate-700 bg-white">
                                    <option value="1" @selected(\App\Models\Setting::get('firebase_push_enabled', '1') === '1')>Active (ON)</option>
                                    <option value="0" @selected(\App\Models\Setting::get('firebase_push_enabled', '1') === '0')>Inactive (OFF)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">FCM Server Key (Legacy HTTP Key / Secret)</label>
                                <input type="password" name="firebase_server_key" value="" autocomplete="new-password" placeholder="Leave blank to keep current server key" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono">
                                <p class="mt-1 text-xs text-gray-500">Found in Firebase Console &rarr; Project Settings &rarr; Cloud Messaging &rarr; Server Key</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Firebase Project ID</label>
                                    <input type="text" name="firebase_project_id" value="{{ \App\Models\Setting::get('firebase_project_id') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="e.g. apnanest-12345">
                                    <p class="mt-1 text-xs text-gray-500">Found in Firebase Console &rarr; Project Settings &rarr; General</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Web API Key (for Web Browser Push)</label>
                                    <input type="text" name="firebase_web_api_key" value="{{ \App\Models\Setting::get('firebase_web_api_key') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="AIzaSy...">
                                    <p class="mt-1 text-xs text-gray-500">Found under Firebase Console &rarr; Your apps &rarr; Web app config</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">App ID</label>
                                    <input type="text" name="firebase_app_id" value="{{ \App\Models\Setting::get('firebase_app_id') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="1:1234567890:web:abcdef...">
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Messaging Sender ID</label>
                                    <input type="text" name="firebase_messaging_sender_id" value="{{ \App\Models\Setting::get('firebase_messaging_sender_id') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="1234567890">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">VAPID Key (Web Push Key Pair)</label>
                                <input type="text" name="firebase_vapid_key" value="{{ \App\Models\Setting::get('firebase_vapid_key') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="BEl6...">
                                <p class="mt-1 text-xs text-gray-500">Found in Firebase Console &rarr; Project Settings &rarr; Cloud Messaging &rarr; Web Configuration &rarr; Web Push Certificates</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SMS Gateway & OTP Delivery Settings -->
                <div data-settings-panel="sms" class="space-y-6" hidden>
                    <div class="rounded-xl border bg-white shadow-sm p-6 space-y-6">
                        <div class="flex items-start gap-4 border-b pb-5">
                            <div class="h-12 w-12 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                                <i class="fas fa-comment-sms text-xl text-indigo-600"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">SMS Gateway & OTP Delivery Mode</h2>
                                <p class="text-sm text-gray-500 mt-1">Control how OTP is sent to users — via Email, Mobile SMS, or both simultaneously. Switch gateways (MSG91, Twilio, Fast2SMS) without changing any code.</p>
                            </div>
                        </div>

                        <!-- OTP Delivery Mode -->
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">OTP Delivery Mode</label>
                                <select name="otp_delivery" class="block w-full rounded-lg border-gray-200 focus:ring-0 bg-gray-50 focus:bg-white sm:text-sm">
                                    <option value="email" @selected(\App\Models\Setting::get('otp_delivery', 'email') === 'email')>📧 Email Only (Default)</option>
                                    <option value="phone" @selected(\App\Models\Setting::get('otp_delivery', 'email') === 'phone')>📱 Mobile Phone (SMS) Only</option>
                                    <option value="both" @selected(\App\Models\Setting::get('otp_delivery', 'email') === 'both')>📧+📱 Both Email & SMS</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">"Email Only" uses your mail server. "SMS Only" requires a gateway below. "Both" sends OTP on both simultaneously.</p>
                            </div>

                            <!-- SMS Gateway Provider -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">SMS Gateway Provider</label>
                                <select name="sms_gateway" class="block w-full rounded-lg border-gray-200 focus:ring-0 bg-gray-50 focus:bg-white sm:text-sm">
                                    <option value="log" @selected(\App\Models\Setting::get('sms_gateway', 'log') === 'log')>🧪 Log / Demo Mode (No real SMS)</option>
                                    <option value="msg91" @selected(\App\Models\Setting::get('sms_gateway', 'log') === 'msg91')>MSG91</option>
                                    <option value="twilio" @selected(\App\Models\Setting::get('sms_gateway', 'log') === 'twilio')>Twilio</option>
                                    <option value="fast2sms" @selected(\App\Models\Setting::get('sms_gateway', 'log') === 'fast2sms')>Fast2SMS</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">"Log Mode" records OTPs in laravel.log for testing. Switch to a real gateway when ready.</p>
                            </div>

                            <!-- SMS API Key -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">SMS API Key / Auth Token <span class="text-xs font-normal text-gray-400">(Encrypted)</span></label>
                                <input type="password" name="sms_api_key" value="" autocomplete="new-password" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="Leave blank to keep existing key">
                                <p class="mt-1 text-xs text-gray-500">MSG91: Auth Key &nbsp;|&nbsp; Twilio: Auth Token &nbsp;|&nbsp; Fast2SMS: API Key</p>
                            </div>

                            <!-- Sender ID / Account SID -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Sender ID / Account SID</label>
                                <input type="text" name="sms_sender_id" value="{{ \App\Models\Setting::get('sms_sender_id') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="e.g. APNANEST or Twilio Account SID">
                                <p class="mt-1 text-xs text-gray-500">Twilio: Account SID &nbsp;|&nbsp; MSG91 / Fast2SMS: Sender Name (e.g. APNANEST)</p>
                            </div>

                            <!-- DLT Template ID -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">DLT Template ID / Twilio From Number</label>
                                <input type="text" name="sms_dlt_te_id" value="{{ \App\Models\Setting::get('sms_dlt_te_id') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="e.g. 1234567890 or +1415XXXXXXX">
                                <p class="mt-1 text-xs text-gray-500">MSG91: DLT Approved Template ID &nbsp;|&nbsp; Twilio: From Phone Number &nbsp;|&nbsp; Fast2SMS: Leave blank</p>
                            </div>
                        </div>

                        <div class="rounded-xl bg-amber-50 border border-amber-200 p-4 text-xs text-amber-800">
                            <strong><i class="fas fa-circle-info mr-1"></i> How to switch from Email OTP to SMS OTP:</strong>
                            <ol class="mt-2 ml-4 list-decimal space-y-1">
                                <li>Set <strong>OTP Delivery Mode</strong> to <em>Mobile Phone (SMS) Only</em> or <em>Both</em>.</li>
                                <li>Set <strong>SMS Gateway Provider</strong> to your chosen service (MSG91 / Twilio / Fast2SMS).</li>
                                <li>Fill in the API Key, Sender ID, and DLT Template ID provided by your gateway.</li>
                                <li>Save — no code changes needed!</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- SEO Section -->
                 <div data-settings-panel="seo" class="space-y-6" hidden>
                    <div class="settings-subtabs" role="tablist" aria-label="SEO settings sections">
                        <button type="button" data-seo-tab="basics" aria-selected="true"><i class="fas fa-search"></i>SEO Basics</button>
                        <button type="button" data-seo-tab="google_ads" aria-selected="false"><i class="fas fa-bullseye"></i>Google Ads</button>
                        <button type="button" data-seo-tab="meta_pixel" aria-selected="false"><i class="fab fa-meta"></i>Meta Pixel</button>
                        <button type="button" data-seo-tab="adsense" aria-selected="false"><i class="fas fa-rectangle-ad"></i>AdSense</button>
                    </div>

                    <div data-seo-subpanel="basics" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                         <div class="flex items-center mb-6 pb-4 border-b border-gray-100">
                             <div class="h-10 w-10 rounded-lg bg-yellow-100 text-yellow-600 flex items-center justify-center mr-4">
                                <i class="fas fa-search text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">SEO & Analytics</h2>
                                <p class="text-sm text-gray-500">Optimize for search engines</p>
                            </div>
                        </div>
                         <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">GA4 Measurement ID</label>
                                    <input type="text" name="ga4_measurement_id" value="{{ \App\Models\Setting::get('ga4_measurement_id') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="G-XXXXXXXXXX">
                                    <p class="mt-1 text-xs text-gray-500">For Traffic Analytics (G-XXXX or UA-XXXX)</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Search Console Code</label>
                                    <input type="text" name="google_search_console_code" value="{{ \App\Models\Setting::get('google_search_console_code') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="Ex: Zxsdf-Asw3... (Only the content code)">
                                    <p class="mt-1 text-xs text-gray-500">Paste the <strong>content</strong> value from the meta tag.</p>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Website URL</label>
                                    <input type="url" name="website_url" value="{{ \App\Models\Setting::get('website_url') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="https://yourwebsite.com">
                                    <p class="mt-1 text-xs text-gray-500">Used for canonical URLs, Open Graph URLs, robots.txt and sitemap.xml on production.</p>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Global Meta Description</label>
                                <textarea name="seo_meta_description" rows="3" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm">{{ \App\Models\Setting::get('seo_meta_description') }}</textarea>
                            </div>
                             <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Meta Keywords</label>
                                <input type="text" name="seo_meta_keywords" value="{{ \App\Models\Setting::get('seo_meta_keywords') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm" placeholder="rental, rooms, apartment...">
                            </div>
                        </div>
                    </div>

                    <!-- Google Ads Section -->
                    <div data-seo-subpanel="tracking" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mt-6">
                         <div class="flex items-center mb-6 pb-4 border-b border-gray-100">
                             <div class="h-10 w-10 rounded-lg admin-theme-soft flex items-center justify-center mr-4">
                                <i id="seoTrackingIcon" class="fas fa-ad text-xl"></i>
                            </div>
                            <div>
                                <h2 id="seoTrackingTitle" class="text-xl font-bold text-gray-800">Google Ads Integration</h2>
                                <p id="seoTrackingSubtitle" class="text-sm text-gray-500">Track conversions and manage ad campaigns</p>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div data-seo-part="google_ads" class="flex items-center gap-3 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <input type="checkbox" name="google_ads_enabled" value="1" id="google_ads_enabled" {{ \App\Models\Setting::get('google_ads_enabled', '0') == '1' ? 'checked' : '' }} class="w-5 h-5 admin-theme-text rounded ">
                                <label for="google_ads_enabled" class="text-sm font-semibold text-gray-700 cursor-pointer">
                                    Enable Google Ads Tracking
                                    <span class="text-xs text-gray-500 block font-normal mt-1">Only works on production environment</span>
                                </label>
                            </div>

                                <div data-seo-part="google_ads">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Google Ads Tag ID</label>
                                    <input type="text" name="google_ads_tag_id" value="{{ \App\Models\Setting::get('google_ads_tag_id') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="AW-XXXXXXXXX">
                                    <p class="mt-1 text-xs text-gray-500">For Ads Tracking. Starts with <strong>AW-</strong></p>
                                </div>

                            <div data-seo-part="google_ads" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Payment Conversion Label</label>
                                    <input type="text" name="google_ads_conversion_label" value="{{ \App\Models\Setting::get('google_ads_conversion_label') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm" placeholder="abc123xyz">
                                    <p class="mt-1 text-xs text-gray-500">Label for successful payments</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Signup Conversion Label</label>
                                    <input type="text" name="google_ads_signup_label" value="{{ \App\Models\Setting::get('google_ads_signup_label') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm" placeholder="signup_xyz123">
                                    <p class="mt-1 text-xs text-gray-500">Label for new user registrations</p>
                                </div>
                            </div>

                            <div data-seo-part="google_ads">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Property View Conversion Label</label>
                                <input type="text" name="google_ads_room_view_label" value="{{ \App\Models\Setting::get('google_ads_room_view_label') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm" placeholder="view_xyz123">
                                <p class="mt-1 text-xs text-gray-500">Label for property detail page views</p>
                            </div>

                            <div data-seo-part="meta_pixel" class="space-y-6">
                                <div class="hidden">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fab fa-meta text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900">Meta Pixel</h3>
                                        <p class="text-xs text-gray-500">Collect visitor, property-view, search and unlock conversion data for future ads.</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                                    <input type="checkbox" name="meta_pixel_enabled" value="1" id="meta_pixel_enabled" {{ \App\Models\Setting::get('meta_pixel_enabled', '0') == '1' ? 'checked' : '' }} class="w-5 h-5 admin-theme-text rounded ">
                                    <label for="meta_pixel_enabled" class="text-sm font-semibold text-gray-700 cursor-pointer">
                                        Enable Meta Pixel Tracking
                                        <span class="text-xs text-gray-500 block font-normal mt-1">Paste your Pixel ID below after creating it in Meta Business Manager.</span>
                                    </label>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Meta Pixel ID</label>
                                    <input type="text" name="meta_pixel_id" value="{{ \App\Models\Setting::get('meta_pixel_id') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="123456789012345">
                                    <p class="mt-1 text-xs text-gray-500">Events used: PageView, Search, ViewContent, InitiateCheckout and Purchase.</p>
                                </div>
                            </div>

                            <div data-seo-part="google_ads" class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-xs text-blue-800 flex items-start gap-2">
                                    <i class="fas fa-info-circle mt-0.5"></i>
                                    <span><strong>Note:</strong> Google Ads tracking will only work when <code class="bg-blue-100 px-1 rounded">APP_ENV=production</code> in your <code class="bg-blue-100 px-1 rounded">.env</code> file. This prevents tracking during development.</span>
                                </p>
                            </div>
                        </div>

                        <!-- Google AdSense Section -->
                        <div data-seo-part="adsense" class="space-y-6">
                            <div class="hidden">
                                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-ad text-yellow-600"></i>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Google AdSense</h3>
                            </div>

                            <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl border border-gray-200">
                                <input type="checkbox" name="adsense_enabled" value="1" id="adsense_enabled" {{ \App\Models\Setting::get('adsense_enabled', '0') == '1' ? 'checked' : '' }} class="w-5 h-5 admin-theme-text rounded ">
                                <label for="adsense_enabled" class="text-sm font-semibold text-gray-700 cursor-pointer">
                                    Enable Google AdSense (Production Only)
                                </label>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">AdSense Client ID (Publisher ID)</label>
                                <input type="text" name="adsense_client_id" value="{{ \App\Models\Setting::get('adsense_client_id') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="ca-pub-XXXXXXXXXXXXXXXX">
                                <p class="mt-1 text-xs text-gray-500">Found in your AdSense account (e.g., ca-pub-1234567890123456)</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Home Page: Top Slot ID</label>
                                    <input type="text" name="adsense_home_top_id" value="{{ \App\Models\Setting::get('adsense_home_top_id') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="1234567890">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Home Page: Bottom Slot ID</label>
                                    <input type="text" name="adsense_home_bottom_id" value="{{ \App\Models\Setting::get('adsense_home_bottom_id') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="1234567890">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Property Detail: Content Slot ID</label>
                                    <input type="text" name="adsense_room_content_id" value="{{ \App\Models\Setting::get('adsense_room_content_id') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="1234567890">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Property Detail: Sidebar Slot ID</label>
                                    <input type="text" name="adsense_room_sidebar_id" value="{{ \App\Models\Setting::get('adsense_room_sidebar_id') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0  transition-colors bg-gray-50 focus:bg-white sm:text-sm font-mono" placeholder="1234567890">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Promo Popup Modal Section -->
                <div data-settings-panel="modal" class="space-y-6" hidden>
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mr-4">
                                    <i class="fas fa-window-restore text-xl"></i>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-800">Promo Popup Modal Settings</h2>
                                    <p class="text-sm text-gray-500">Configure the Booking.com-style dynamic promotion/login modal</p>
                                </div>
                            </div>
                            <label class="relative inline-flex shrink-0 items-center cursor-pointer">
                                <input type="checkbox" name="promo_modal_enabled" value="1" class="peer sr-only" @checked(filter_var(\App\Models\Setting::get('promo_modal_enabled', '1'), FILTER_VALIDATE_BOOLEAN))>
                                <span class="admin-switch-track h-6 w-11 rounded-full transition bg-slate-200 peer-checked:bg-blue-600"></span>
                                <span class="absolute left-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Target Audience</label>
                                <select name="promo_modal_audience" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 bg-gray-50 focus:bg-white text-sm font-medium text-gray-900">
                                    <option value="guests_only" @selected(\App\Models\Setting::get('promo_modal_audience', 'guests_only') === 'guests_only')>Guests Only (Non-Logged in Visitors)</option>
                                    <option value="logged_in" @selected(\App\Models\Setting::get('promo_modal_audience') === 'logged_in')>Logged-In Members Only</option>
                                    <option value="all" @selected(\App\Models\Setting::get('promo_modal_audience') === 'all')>All Visitors (Everyone)</option>
                                </select>
                                <p class="mt-1.5 text-xs text-gray-400">Choose who should see this popup modal.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Modal Display Format</label>
                                <select name="promo_modal_type" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 bg-gray-50 focus:bg-white text-sm font-medium text-gray-900">
                                    <option value="text_card" @selected(\App\Models\Setting::get('promo_modal_type', 'text_card') === 'text_card')>Clean Text Card (Booking.com style)</option>
                                    <option value="banner_image" @selected(\App\Models\Setting::get('promo_modal_type') === 'banner_image')>Graphic Banner Image Only</option>
                                    <option value="both" @selected(\App\Models\Setting::get('promo_modal_type') === 'both')>Both (Image Banner + Text Content)</option>
                                </select>
                                <p class="mt-1.5 text-xs text-gray-400">Select whether to display text, graphic banner, or both.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Badge Text (Top Pill)</label>
                                <input type="text" name="promo_modal_badge" value="{{ \App\Models\Setting::get('promo_modal_badge', 'FarmStayGo Perks') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 bg-gray-50 focus:bg-white text-sm font-medium text-gray-900" placeholder="e.g. FarmStayGo Perks / Festival Special">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Modal Headline / Title</label>
                                <input type="text" name="promo_modal_title" value="{{ \App\Models\Setting::get('promo_modal_title', 'Sign in, save money') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 bg-gray-50 focus:bg-white text-sm font-bold text-gray-900" placeholder="e.g. Sign in, save money">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Modal Description / Message</label>
                            <textarea name="promo_modal_description" rows="2" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 bg-gray-50 focus:bg-white text-sm font-medium text-gray-900 resize-none" placeholder="e.g. Sign in or create a free account to unlock exclusive member discounts & free room contacts.">{{ \App\Models\Setting::get('promo_modal_description', 'Sign in or create a free account to unlock exclusive member discounts & free room contacts.') }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Button Text</label>
                                <input type="text" name="promo_modal_btn_text" value="{{ \App\Models\Setting::get('promo_modal_btn_text', 'Sign in or register') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 bg-gray-50 focus:bg-white text-sm font-bold text-gray-900" placeholder="e.g. Sign in or register / Claim Deal">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Button Link URL</label>
                                <input type="text" name="promo_modal_btn_url" value="{{ \App\Models\Setting::get('promo_modal_btn_url', '/login') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 bg-gray-50 focus:bg-white text-sm font-medium text-gray-900 font-mono" placeholder="e.g. /login, /register, /plans, or https://...">
                            </div>
                        </div>

                        {{-- Image Upload --}}
                        <div class="border-t border-gray-100 pt-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Custom Banner Image (Optional)</label>
                            <div class="flex items-center gap-6">
                                <div class="h-24 w-40 rounded-xl border border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden shrink-0">
                                    @php $modalImg = \App\Models\Setting::get('promo_modal_image'); @endphp
                                    @if($modalImg)
                                        <img src="{{ \App\Models\Setting::mediaUrl($modalImg, 'assets/images/proparty.png') }}" class="h-full w-full object-cover">
                                    @else
                                        <span class="text-xs text-gray-400 font-medium">No Banner</span>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="promo_modal_image" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    <p class="mt-1.5 text-xs text-gray-400">Upload a custom promotional banner image (PNG, JPG, WebP - Recommended ratio 16:9 or 4:3).</p>
                                </div>
                            </div>
                        </div>

                        {{-- Timing & Frequency --}}
                        <div class="border-t border-gray-100 pt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Popup Delay (Seconds)</label>
                                <div class="relative">
                                    <input type="number" name="promo_modal_delay" step="0.5" min="0" max="60" value="{{ \App\Models\Setting::get('promo_modal_delay', '2.5') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 bg-gray-50 focus:bg-white text-sm font-bold text-gray-900">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-400">sec</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-400">How long to wait after page load before showing the modal (Default: 2.5s).</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Dismiss Cooldown (Hours)</label>
                                <div class="relative">
                                    <input type="number" name="promo_modal_cooldown_hours" step="1" min="0" max="720" value="{{ \App\Models\Setting::get('promo_modal_cooldown_hours', '24') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg focus:ring-0 bg-gray-50 focus:bg-white text-sm font-bold text-gray-900">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-400">hours</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-400">Once dismissed, how many hours to wait before showing again (Default: 24h). Set 0 for every session.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Broker & Agent Section -->
                <div data-settings-panel="broker" class="space-y-6" hidden>
                    @php
                        $brokerOn = fn ($key, $default = '1') => \App\Models\BrokerSetting::isEnabled($key, $default);
                        $brokerVal = fn ($key, $default = '') => \App\Models\BrokerSetting::get($key, $default);
                    @endphp

                    <!-- 1. Broker Fee Configuration (with Direct ON/OFF Toggles) -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <div class="flex items-center mb-6 pb-4 border-b border-gray-100">
                            <div class="h-10 w-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center mr-4">
                                <i class="fas fa-indian-rupee-sign text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">Broker Fee Configuration</h2>
                                <p class="text-sm text-gray-500">Toggle fees ON/OFF directly. If OFF, the service will be 100% free for brokers.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Fee 1: Broker Listing Fee -->
                            <div class="rounded-xl border border-gray-200 p-5 bg-slate-50/50">
                                <label class="mb-4 flex items-start justify-between gap-4">
                                    <span>
                                        <span class="block text-sm font-bold text-gray-800">Enable Listing Fee</span>
                                        <span class="mt-1 block text-xs text-gray-500">OFF = all broker listings are free</span>
                                    </span>
                                    <span class="relative inline-flex shrink-0 items-center">
                                        <input type="checkbox" name="broker_listing_charges_enabled" value="1" class="peer sr-only" @checked($brokerOn('broker_listing_charges_enabled', '0'))>
                                        <span class="admin-switch-track h-6 w-11 rounded-full transition"></span>
                                        <span class="absolute left-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                                    </span>
                                </label>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Listing Fee (per room)</label>
                                <div class="relative rounded-lg shadow-sm group transition-all focus-within:ring-2 ring-[rgba(var(--admin-primary-rgb),.2)]">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 font-medium">₹</div>
                                    <input type="number" step="0.01" min="0" name="broker_per_listing_charge" value="{{ $brokerVal('broker_per_listing_charge', '199') }}" class="block w-full pl-8 pr-3 py-3 border-gray-200 rounded-lg bg-white focus:bg-white text-sm font-bold text-gray-900" placeholder="0.00">
                                </div>
                                <p class="mt-2 text-xs text-gray-400">Price is saved even when toggle is OFF.</p>
                            </div>

                            <!-- Fee 2: Broker Featured Listing Fee -->
                            <div class="rounded-xl border border-gray-200 p-5 bg-slate-50/50">
                                <label class="mb-4 flex items-start justify-between gap-4">
                                    <span>
                                        <span class="block text-sm font-bold text-gray-800">Enable Featured Fee</span>
                                        <span class="mt-1 block text-xs text-gray-500">OFF = brokers cannot feature listings</span>
                                    </span>
                                    <span class="relative inline-flex shrink-0 items-center">
                                        <input type="checkbox" name="broker_featured_enabled" value="1" class="peer sr-only" @checked($brokerOn('broker_featured_enabled', '1'))>
                                        <span class="admin-switch-track h-6 w-11 rounded-full transition"></span>
                                        <span class="absolute left-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                                    </span>
                                </label>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Featured Listing Fee</label>
                                <div class="relative rounded-lg shadow-sm group transition-all focus-within:ring-2 ring-[rgba(var(--admin-primary-rgb),.2)]">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 font-medium">₹</div>
                                    <input type="number" step="0.01" min="0" name="broker_featured_charge" value="{{ $brokerVal('broker_featured_charge', '99') }}" class="block w-full pl-8 pr-3 py-3 border-gray-200 rounded-lg bg-white focus:bg-white text-sm font-bold text-gray-900" placeholder="0.00">
                                </div>
                                <p class="mt-2 text-xs text-gray-400">Promotes broker listing to top badge.</p>
                            </div>

                            <!-- Fee 3: Broker Lead Unlock Fee -->
                            <div class="rounded-xl border border-gray-200 p-5 bg-slate-50/50">
                                <label class="mb-4 flex items-start justify-between gap-4">
                                    <span>
                                        <span class="block text-sm font-bold text-gray-800">Enable Lead Charge</span>
                                        <span class="mt-1 block text-xs text-gray-500">OFF = tenant leads sent free</span>
                                    </span>
                                    <span class="relative inline-flex shrink-0 items-center">
                                        <input type="checkbox" name="broker_lead_charge_enabled" value="1" class="peer sr-only" @checked($brokerOn('broker_lead_charge_enabled', '0'))>
                                        <span class="admin-switch-track h-6 w-11 rounded-full transition"></span>
                                        <span class="absolute left-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                                    </span>
                                </label>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Lead Unlock Fee</label>
                                <div class="relative rounded-lg shadow-sm group transition-all focus-within:ring-2 ring-[rgba(var(--admin-primary-rgb),.2)]">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 font-medium">₹</div>
                                    <input type="number" step="0.01" min="0" name="broker_lead_charge" value="{{ $brokerVal('broker_lead_charge', '49') }}" class="block w-full pl-8 pr-3 py-3 border-gray-200 rounded-lg bg-white focus:bg-white text-sm font-bold text-gray-900" placeholder="0.00">
                                </div>
                                <p class="mt-2 text-xs text-gray-400">Charged per tenant lead inquiry unlocked.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Broker Quotas & Validity Rules -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <div class="flex items-center mb-6 pb-4 border-b border-gray-100">
                            <div class="h-10 w-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mr-4">
                                <i class="fas fa-sliders text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">Broker Quotas & Validity</h2>
                                <p class="text-sm text-gray-500">Set free listing allowance and listing active duration</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Free Listing Quota -->
                            <div class="rounded-xl border border-gray-200 p-5 bg-slate-50/50">
                                <label class="block text-sm font-bold text-gray-800 mb-1">Free Listing Quota</label>
                                <p class="text-xs text-gray-500 mb-3">Number of initial listings given for free before charging fee.</p>
                                <div class="relative rounded-lg shadow-sm">
                                    <input type="number" step="1" min="0" name="broker_free_listing_limit" value="{{ $brokerVal('broker_free_listing_limit', '0') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg bg-white text-sm font-bold text-gray-900" placeholder="0">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-400">listings (0 = None)</span>
                                </div>
                            </div>

                            <!-- Listing Expiry Duration -->
                            <div class="rounded-xl border border-gray-200 p-5 bg-slate-50/50">
                                <label class="block text-sm font-bold text-gray-800 mb-1">Listing Expiry Validity</label>
                                <p class="text-xs text-gray-500 mb-3">Duration after which a broker property expires and requires renewal.</p>
                                <div class="relative rounded-lg shadow-sm">
                                    <input type="number" step="1" min="1" name="broker_listing_expiry_days" value="{{ $brokerVal('broker_listing_expiry_days', '30') }}" class="block w-full px-4 py-3 border-gray-200 rounded-lg bg-white text-sm font-bold text-gray-900" placeholder="30">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-400">days</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Module & Policy Controls -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <div class="flex items-center mb-6 pb-4 border-b border-gray-100">
                            <div class="h-10 w-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mr-4">
                                <i class="fas fa-user-shield text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-800">Module & Verification Policies</h2>
                                <p class="text-sm text-gray-500">Configure broker onboarding and commission tracking policies</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Broker Module Global -->
                            <div class="rounded-xl border border-gray-200 p-4 bg-slate-50/50">
                                <label class="flex items-start justify-between gap-4">
                                    <span>
                                        <span class="block text-sm font-bold text-gray-800">Broker Module Active</span>
                                        <span class="mt-1 block text-xs text-gray-500">Enable/disable entire agent module</span>
                                    </span>
                                    <span class="relative inline-flex shrink-0 items-center">
                                        <input type="checkbox" name="broker_module_enabled" value="1" class="peer sr-only" @checked($brokerOn('broker_module_enabled', '1'))>
                                        <span class="admin-switch-track h-6 w-11 rounded-full transition"></span>
                                        <span class="absolute left-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                                    </span>
                                </label>
                            </div>

                            <!-- Broker Verification -->
                            <div class="rounded-xl border border-gray-200 p-4 bg-slate-50/50">
                                <label class="flex items-start justify-between gap-4">
                                    <span>
                                        <span class="block text-sm font-bold text-gray-800">Admin Approval Required</span>
                                        <span class="mt-1 block text-xs text-gray-500">OFF = instant auto-approval on register</span>
                                    </span>
                                    <span class="relative inline-flex shrink-0 items-center">
                                        <input type="checkbox" name="broker_verification_enabled" value="1" class="peer sr-only" @checked($brokerOn('broker_verification_enabled', '1'))>
                                        <span class="admin-switch-track h-6 w-11 rounded-full transition"></span>
                                        <span class="absolute left-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                                    </span>
                                </label>
                            </div>

                            <!-- Future Brokerage System -->
                            <div class="rounded-xl border border-gray-200 p-4 bg-slate-50/50">
                                <label class="flex items-start justify-between gap-4">
                                    <span>
                                        <span class="block text-sm font-bold text-gray-800">Commission Tracking</span>
                                        <span class="mt-1 block text-xs text-gray-500">Track deals and brokerage rates</span>
                                    </span>
                                    <span class="relative inline-flex shrink-0 items-center">
                                        <input type="checkbox" name="broker_future_brokerage_enabled" value="1" class="peer sr-only" @checked($brokerOn('broker_future_brokerage_enabled', '0'))>
                                        <span class="admin-switch-track h-6 w-11 rounded-full transition"></span>
                                        <span class="absolute left-1 h-4 w-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="settings-save-bar" class="sticky bottom-4 z-20 mt-6 items-center justify-end border-t border-slate-200 bg-gray-50/95 py-4 backdrop-blur">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl admin-theme-bg px-7 py-3 text-sm font-bold text-white shadow-lg  transition ">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function syncColorInputs(colorId, textId) {
    const colorInput = document.getElementById(colorId);
    const textInput = document.getElementById(textId);
    if (colorInput && textInput) {
        // Picker → text (both input and change)
        colorInput.addEventListener('input', () => { textInput.value = colorInput.value; });
        colorInput.addEventListener('change', () => { textInput.value = colorInput.value; });
        // Text → picker (both input and change)
        const applyText = () => {
            const v = textInput.value.trim();
            if (/^#[0-9A-Fa-f]{6}$/.test(v)) colorInput.value = v;
        };
        textInput.addEventListener('input', applyText);
        textInput.addEventListener('change', applyText);
    }
}
document.addEventListener('DOMContentLoaded', () => {
    syncColorInputs('primary_color', 'primary_color_text');
    syncColorInputs('secondary_color', 'secondary_color_text');

    // Before form submit: force-sync text boxes → color pickers
    // so the correct hex value is always what gets posted.
    const settingsForm = document.getElementById('settings-form');
    if (settingsForm) {
        settingsForm.addEventListener('submit', () => {
            // Set active tab before submit so controller can redirect back to same tab
            const activeTabInput = document.getElementById('active_tab_input');
            if (activeTabInput) {
                activeTabInput.value = location.hash.replace('#', '') || 'general';
            }
            // Sync color pickers
            [['primary_color','primary_color_text'], ['secondary_color','secondary_color_text']].forEach(([cId, tId]) => {
                const c = document.getElementById(cId);
                const t = document.getElementById(tId);
                if (c && t && /^#[0-9A-Fa-f]{6}$/.test(t.value.trim())) {
                    c.value = t.value.trim();
                }
            });
        });
    }

    const root = document.getElementById('business-settings-tabs');
    if (!root) return;

    const tabs = [...root.querySelectorAll('[data-settings-tab]')];
    const panels = [...root.querySelectorAll('[data-settings-panel]')];
    const validTabs = tabs.map((tab) => tab.dataset.settingsTab);
    const seoTabs = [...root.querySelectorAll('[data-seo-tab]')];
    const seoBasicPanel = root.querySelector('[data-seo-subpanel="basics"]');
    const seoTrackingPanel = root.querySelector('[data-seo-subpanel="tracking"]');
    const seoParts = [...root.querySelectorAll('[data-seo-part]')];
    const validSeoTabs = seoTabs.map((tab) => tab.dataset.seoTab);
    const seoTrackingTitle = document.getElementById('seoTrackingTitle');
    const seoTrackingSubtitle = document.getElementById('seoTrackingSubtitle');
    const seoTrackingIcon = document.getElementById('seoTrackingIcon');
    const seoTrackingMeta = {
        google_ads: ['Google Ads Integration', 'Track conversions and manage ad campaigns.', 'fas fa-ad text-xl'],
        meta_pixel: ['Meta Pixel', 'Collect visitor, room-view, search and unlock conversion data.', 'fab fa-meta text-xl'],
        adsense: ['Google AdSense', 'Configure publisher and ad slot IDs for public pages.', 'fas fa-rectangle-ad text-xl'],
    };

    const appearanceTabs = [...root.querySelectorAll('[data-appearance-tab]')];
    const appearancePanels = [...root.querySelectorAll('[data-appearance-subpanel]')];
    const validAppearanceTabs = appearanceTabs.map((tab) => tab.dataset.appearanceTab);

    const activateAppearanceTab = (tabName) => {
        const activeAppearanceTab = validAppearanceTabs.includes(tabName) ? tabName : 'branding';

        appearanceTabs.forEach((tab) => {
            tab.setAttribute('aria-selected', tab.dataset.appearanceTab === activeAppearanceTab ? 'true' : 'false');
        });

        appearancePanels.forEach((panel) => {
            panel.hidden = panel.dataset.appearanceSubpanel !== activeAppearanceTab;
        });
    };

    const activateSeoTab = (tabName) => {
        const activeSeoTab = validSeoTabs.includes(tabName) ? tabName : 'basics';

        seoTabs.forEach((tab) => {
            tab.setAttribute('aria-selected', tab.dataset.seoTab === activeSeoTab ? 'true' : 'false');
        });

        if (seoBasicPanel) {
            seoBasicPanel.hidden = activeSeoTab !== 'basics';
        }
        if (seoTrackingPanel) {
            seoTrackingPanel.hidden = activeSeoTab === 'basics';
        }

        seoParts.forEach((part) => {
            part.hidden = part.dataset.seoPart !== activeSeoTab;
        });

        if (activeSeoTab !== 'basics' && seoTrackingMeta[activeSeoTab]) {
            const [title, subtitle, iconClass] = seoTrackingMeta[activeSeoTab];
            if (seoTrackingTitle) seoTrackingTitle.textContent = title;
            if (seoTrackingSubtitle) seoTrackingSubtitle.textContent = subtitle;
            if (seoTrackingIcon) seoTrackingIcon.className = iconClass;
        }
    };

    const integrationsTabs = [...root.querySelectorAll('[data-integrations-tab]')];
    const integrationsPanels = [...root.querySelectorAll('[data-integrations-subpanel]')];
    const validIntegrationsTabs = integrationsTabs.map((tab) => tab.dataset.integrationsTab);

    const activateIntegrationsTab = (tabName) => {
        const activeIntegrationsTab = validIntegrationsTabs.includes(tabName) ? tabName : 'maps';

        integrationsTabs.forEach((tab) => {
            tab.setAttribute('aria-selected', tab.dataset.integrationsTab === activeIntegrationsTab ? 'true' : 'false');
        });

        integrationsPanels.forEach((panel) => {
            panel.hidden = panel.dataset.integrationsSubpanel !== activeIntegrationsTab;
        });
    };

    const activateTab = (tabName, updateUrl = true) => {
        const activeTab = validTabs.includes(tabName) ? tabName : 'general';

        tabs.forEach((tab) => {
            const selected = tab.dataset.settingsTab === activeTab;
            tab.setAttribute('aria-selected', selected ? 'true' : 'false');
            tab.classList.toggle('admin-theme-bg', selected);
            tab.classList.toggle('text-white', selected);
            tab.classList.toggle('shadow-sm', selected);
            tab.classList.toggle('bg-slate-50', !selected);
            tab.classList.toggle('text-slate-600', !selected);
        });

        panels.forEach((panel) => {
            panel.hidden = panel.dataset.settingsPanel !== activeTab;
        });

        if (activeTab === 'appearance') {
            activateAppearanceTab(appearanceTabs.find((tab) => tab.getAttribute('aria-selected') === 'true')?.dataset.appearanceTab || 'branding');
        }

        if (activeTab === 'integrations') {
            activateIntegrationsTab(integrationsTabs.find((tab) => tab.getAttribute('aria-selected') === 'true')?.dataset.integrationsTab || 'maps');
        }

        if (updateUrl) {
            history.replaceState(null, '', `${location.pathname}${location.search}#${activeTab}`);
        }
    };

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => activateTab(tab.dataset.settingsTab));
    });

    integrationsTabs.forEach((tab) => {
        tab.addEventListener('click', () => activateIntegrationsTab(tab.dataset.integrationsTab));
    });

    seoTabs.forEach((tab) => {
        tab.addEventListener('click', () => activateSeoTab(tab.dataset.seoTab));
    });

    appearanceTabs.forEach((tab) => {
        tab.addEventListener('click', () => activateAppearanceTab(tab.dataset.appearanceTab));
    });

    activateSeoTab('basics');

    activateAppearanceTab('branding');

    activateIntegrationsTab('maps');

    activateTab(location.hash.replace('#', '') || 'general', false);

    function initToggle(btn) {
        var targetName = btn.dataset.target;
        var input = document.querySelector('input[name="' + targetName + '"]');
        if (!input) return;
        var track = btn.querySelector('.toggle-track');
        var knob = btn.querySelector('.toggle-knob');
        var label = btn.querySelector('.toggle-label');
        var current = input.value;
        btn.className = 'toggle-btn feature-toggle ' + (current === '1' ? 'toggle-btn-active' : 'toggle-btn-inactive');
        if (track) track.className = 'toggle-track ' + (current === '1' ? 'toggle-track-active' : 'toggle-track-inactive');
        if (knob) knob.className = 'toggle-knob ' + (current === '1' ? 'toggle-knob-active' : 'toggle-knob-inactive');
        if (label) label.textContent = current === '1' ? 'ON' : 'OFF';
    }

    document.querySelectorAll('.feature-toggle').forEach(function (btn) {
        initToggle(btn);
        btn.addEventListener('click', function () {
            var targetName = btn.dataset.target;
            var input = document.querySelector('input[name="' + targetName + '"]');
            if (!input) return;
            var next = input.value === '1' ? '0' : '1';
            input.value = next;
            btn.className = 'toggle-btn feature-toggle ' + (next === '1' ? 'toggle-btn-active' : 'toggle-btn-inactive');
            var track = btn.querySelector('.toggle-track');
            var knob = btn.querySelector('.toggle-knob');
            var label = btn.querySelector('.toggle-label');
            if (track) track.className = 'toggle-track ' + (next === '1' ? 'toggle-track-active' : 'toggle-track-inactive');
            if (knob) knob.className = 'toggle-knob ' + (next === '1' ? 'toggle-knob-active' : 'toggle-knob-inactive');
            if (label) label.textContent = next === '1' ? 'ON' : 'OFF';
            autoSaveToggle(targetName, next);
        });
    });

    var saveTimeout = null;
    function autoSaveToggle(key, value) {
        var statusEl = document.getElementById('toggle-save-status');
        if (statusEl) {
            statusEl.textContent = 'Saving...';
            statusEl.classList.remove('hidden');
        }
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(function () {
            var form = document.getElementById('settings-form');
            if (!form) return;
            var formData = new FormData(form);
            formData.set(key, value);
            formData.set('_active_tab', 'referral');
            fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData,
            }).then(function (r) { return r.json(); })
              .then(function (data) {
                  if (statusEl) {
                      statusEl.textContent = 'Saved';
                      setTimeout(function () { statusEl.classList.add('hidden'); }, 1200);
                  }
              })
              .catch(function () {
                  if (statusEl) {
                      statusEl.textContent = 'Error';
                      setTimeout(function () { statusEl.classList.add('hidden'); }, 2000);
                  }
              });
        }, 400);
    }
});

function setupImagePreview(inputName) {
    const input = document.querySelector(`input[name="${inputName}"]`);
    if (!input) return;

    input.addEventListener('change', () => {
        const file = input.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            const container = input.closest('.flex').querySelector('.rounded-xl');
            if (container) {
                container.innerHTML = `<img src="${e.target.result}" class="h-full w-full object-cover">`;
            }
        };
        reader.readAsDataURL(file);
    });
}

setupImagePreview('navbar_logo');
setupImagePreview('footer_logo');
setupImagePreview('website_logo');
setupImagePreview('website_favicon');
setupImagePreview('owner_cta_image');
setupImagePreview('promo_modal_image');
setupImagePreview('default_hero_image');

const settingsForm = document.getElementById('settings-form');
if (settingsForm) {
    settingsForm.addEventListener('submit', () => {
        const btn = settingsForm.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
        }
        console.log('Settings form submitted');
    });
}

</script>
@endpush
