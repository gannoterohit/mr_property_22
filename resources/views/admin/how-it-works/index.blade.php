@extends('layouts.admin')

@section('title', 'How It Works')

@section('admin-content')
<div class="space-y-5 p-5 lg:p-6">
    <header class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-[10px] font-extrabold uppercase tracking-[.2em] admin-theme-text">Landing content</p>
            <h1 class="mt-1 text-2xl font-extrabold">How It Works</h1>
            <p class="text-sm text-slate-500">Manage this page with structured fields instead of a free-form editor.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-admin.data-actions dataset="how-it-works" importable />
            <a href="{{ route('pages.how-it-works') }}" target="_blank" class="rounded-xl border bg-white px-4 py-3 text-xs font-bold text-slate-700"><i class="fas fa-up-right-from-square mr-2"></i>Preview</a>
        </div>
    </header>

    @if(isset($errors) && $errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><strong>Please correct the form.</strong><ul class="mt-2 list-disc pl-5 text-xs">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('admin.how-it-works.settings') }}" enctype="multipart/form-data" class="rounded-2xl border bg-white p-5">
        @csrf @method('PUT')
        <div class="grid gap-4 lg:grid-cols-2">
            @foreach([
                'hiw_hero_eyebrow' => 'Hero eyebrow',
                'hiw_hero_title' => 'Hero title',
                'hiw_hero_highlight' => 'Hero highlighted line',
                'hiw_primary_button_label' => 'Primary button',
                'hiw_secondary_button_label' => 'Secondary button',
                'hiw_seeker_eyebrow' => 'Seeker eyebrow',
                'hiw_seeker_title' => 'Seeker title',
                'hiw_owner_eyebrow' => 'Owner eyebrow',
                'hiw_owner_title' => 'Owner title',
                'hiw_owner_button_label' => 'Owner button',
                'hiw_safety_title' => 'Safety title',
                'hiw_safety_button_label' => 'Safety button',
            ] as $key => $label)
                <div><label class="text-xs font-bold">{{ $label }}</label><input name="{{ $key }}" value="{{ old($key, \App\Models\Setting::get($key)) }}" required class="mt-2 h-11 w-full rounded-xl border-slate-200 text-sm"></div>
            @endforeach
            @foreach([
                'hiw_hero_description' => 'Hero description',
                'hiw_seeker_description' => 'Seeker description',
                'hiw_owner_description' => 'Owner description',
                'hiw_safety_description' => 'Safety description',
            ] as $key => $label)
                <div class="lg:col-span-2"><label class="text-xs font-bold">{{ $label }}</label><textarea name="{{ $key }}" rows="3" required class="mt-2 w-full rounded-xl border-slate-200 text-sm">{{ old($key, \App\Models\Setting::get($key)) }}</textarea></div>
            @endforeach

            <div class="lg:col-span-2">
                <label class="text-xs font-bold">Hero background images (slideshow)</label>
                @php
                    $heroImagesRaw = \App\Models\Setting::get('hiw_hero_images');
                    $heroImages = is_string($heroImagesRaw) ? json_decode($heroImagesRaw, true) : (is_array($heroImagesRaw) ? $heroImagesRaw : []);
                    if (!is_array($heroImages) || empty($heroImages)) {
                        $legacy = \App\Models\Setting::get('hiw_hero_image');
                        if ($legacy) $heroImages = [$legacy];
                    }
                @endphp
                @if(count($heroImages))
                    <div class="mt-2 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                        @foreach($heroImages as $img)
                            @php
                                $abs = \Illuminate\Support\Facades\Storage::disk('public')->path($img);
                                $sizeKb = file_exists($abs) ? round(filesize($abs) / 1024, 1) : 0;
                            @endphp
                            <div class="relative">
                                <img src="{{ \App\Models\Setting::mediaUrl($img) }}" alt="Hero image" class="h-24 w-full rounded-xl border border-slate-200 object-cover">
                                <span class="absolute bottom-1 left-1 rounded-md bg-black/70 px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $sizeKb }} KB</span>
                                <label class="absolute right-1 top-1 inline-flex h-6 w-6 cursor-pointer items-center justify-center rounded-full bg-white/90 text-red-600 shadow hover:bg-white" title="Remove permanently">
                                    <input type="checkbox" name="hiw_existing_hero_images_remove[]" value="{{ $img }}" class="hiw-image-remove hidden">
                                    <i class="fas fa-times text-[10px]"></i>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @foreach($heroImages as $img)
                        <input type="hidden" name="hiw_existing_hero_images[]" value="{{ $img }}" class="hiw-existing-input">
                    @endforeach
                    <p class="mt-2 text-[11px] text-slate-500">Tick the red X on an image to remove it, then Save — the file will be permanently deleted from the server.</p>
                @endif
                <input type="file" name="hiw_hero_images[]" multiple accept="image/jpeg,image/png,image/jpg,image/webp" class="mt-3 block w-full text-xs file:mr-3 file:rounded-xl file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-xs file:font-bold file:text-indigo-700 hover:file:bg-indigo-100">
                <div class="mt-2 rounded-lg bg-amber-50 border border-amber-200 px-3 py-2 text-[11px] text-amber-900">
                    <p class="font-bold"><i class="fas fa-image mr-1"></i>Recommended image specs for the hero slideshow</p>
                    <ul class="mt-1 list-disc pl-5 leading-5">
                        <li><b>Size:</b> 1920×720 px or wider (16:6 / 8:3 landscape ratio). Full-width hero looks best at this size.</li>
                        <li><b>Max file size:</b> 4 MB per image (server enforces this).</li>
                        <li><b>Formats:</b> JPEG, PNG, JPG, or WebP.</li>
                        <li><b>Quantity:</b> Upload up to 6 images; they auto-rotate every 4.5 seconds.</li>
                        <li><b>Tip:</b> Use dark or moody photos — a gradient overlay is applied so the white text stays readable.</li>
                    </ul>
                </div>
            </div>
        </div>
        <button class="mt-5 rounded-xl admin-theme-bg px-5 py-3 text-sm font-bold text-white"><i class="fas fa-save mr-2"></i>Save Page Text</button>
    </form>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.hiw-image-remove').forEach(function (cb) {
            cb.addEventListener('change', function () {
                var wrap = this.closest('.relative');
                if (!wrap) return;
                var img = wrap.querySelector('img');
                if (this.checked) {
                    img.style.opacity = '0.3';
                    img.style.filter = 'grayscale(1)';
                } else {
                    img.style.opacity = '';
                    img.style.filter = '';
                }
                var imgPath = this.value;
                document.querySelectorAll('.hiw-existing-input').forEach(function (hi) {
                    if (hi.value === imgPath) hi.disabled = cb.checked;
                });
            });
        });
    });
    </script>
    @endpush

    @foreach($groups as $groupKey => $groupLabel)
        <section class="overflow-hidden rounded-2xl border bg-white">
            <div class="flex items-center justify-between border-b bg-slate-50 px-5 py-4">
                <div><h2 class="text-sm font-extrabold text-slate-950">{{ $groupLabel }}</h2><p class="text-xs text-slate-500">Add, edit, reorder and hide items for this section.</p></div>
                <a href="{{ route('admin.how-it-works.items.create', ['group' => $groupKey]) }}" class="rounded-xl admin-theme-bg px-4 py-2.5 text-xs font-bold text-white"><i class="fas fa-plus mr-2"></i>Add</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[880px] text-left">
                    <thead class="bg-white text-[10px] font-extrabold uppercase tracking-wide text-slate-400"><tr><th class="p-4">Item</th><th class="p-4">Badge</th><th class="p-4">Order</th><th class="p-4">Status</th><th class="p-4 text-right">Actions</th></tr></thead>
                    <tbody class="divide-y">
                        @forelse(($items[$groupKey] ?? collect()) as $item)
                            <tr>
                                <td class="p-4"><div class="flex items-start gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl admin-theme-soft admin-theme-text"><i class="fas {{ $item->icon }}"></i></span><div><p class="text-sm font-extrabold">{{ $item->title }}</p><p class="mt-1 max-w-xl text-xs leading-5 text-slate-500">{{ $item->description }}</p></div></div></td>
                                <td class="p-4 text-xs font-bold text-slate-600">{{ $item->badge ?: '-' }}</td>
                                <td class="p-4 text-xs font-bold text-slate-600">{{ $item->sort_order }}</td>
                                <td class="p-4"><x-admin.status-toggle :active="$item->is_active" :action="route('admin.how-it-works.items.toggle-status', $item)" :data-label="$item->title" /></td>
                                <td class="p-4"><div class="flex justify-end gap-2"><x-admin.action-icon variant="edit" :href="route('admin.how-it-works.items.edit', $item)" /><form method="POST" action="{{ route('admin.how-it-works.items.destroy', $item) }}" class="admin-confirm" data-confirm-title="Delete {{ $item->title }}?" data-confirm-text="This item will be permanently removed." data-confirm-button="Yes, delete item">@csrf @method('DELETE')<x-admin.action-icon variant="delete" type="submit" /></form></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-14 text-center"><i class="fas fa-route text-3xl admin-theme-text"></i><p class="mt-3 text-sm font-extrabold text-slate-800">No steps yet</p><p class="mt-1 text-xs text-slate-500">Add the first step to explain your platform process.</p><a href="{{ route('admin.how-it-works.items.create', ['group' => $groupKey]) }}" class="mt-4 inline-flex items-center gap-2 rounded-xl admin-theme-bg px-4 py-2.5 text-xs font-bold text-white"><i class="fas fa-plus"></i>Add step</a></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endforeach
</div>
@endsection
