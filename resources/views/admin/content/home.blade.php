@extends('layouts.admin')
@section('title', 'Home Page Manager')
@section('admin-content')
<form action="{{ route('admin.home-page.update') }}" method="POST" class="space-y-5">
    @csrf @method('PUT')
    <div class="flex items-center justify-between gap-4"><div><h2 class="text-xl font-bold text-slate-900">Current landing page content</h2><p class="text-xs text-slate-500 mt-1">Manage only the text used by the current design. Listings, categories, counts, areas, prices, search options and blogs update automatically from their respective modules.</p></div><div class="flex gap-2"><a href="{{ route('home') }}" target="_blank" class="px-4 py-2.5 rounded-lg border border-slate-200 bg-white text-slate-600 text-xs font-bold">Preview</a><button class="px-5 py-2.5 rounded-lg admin-theme-bg text-xs font-bold"><i class="fas fa-save mr-1"></i>Save changes</button></div></div>
    @foreach($sections as $section => $fields)
        <section class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden"><div class="px-5 py-3 border-b border-slate-100"><h3 class="font-bold text-slate-900">{{ $section }}</h3></div><div class="p-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($fields as $key => $field)
                <div class="{{ !empty($field['textarea']) ? 'md:col-span-2' : '' }}"><label class="block text-xs font-bold text-slate-600 mb-1.5">{{ $field['label'] }}</label>@if(!empty($field['textarea']))<textarea name="{{ $key }}" rows="3" class="w-full rounded-lg text-sm">{{ old($key, \App\Models\Setting::get($key, $field['default'])) }}</textarea>@else<input type="text" name="{{ $key }}" value="{{ old($key, \App\Models\Setting::get($key, $field['default'])) }}" class="w-full rounded-lg text-sm">@endif</div>
            @endforeach
        </div></section>
    @endforeach
    <div class="flex justify-end"><button class="px-6 py-3 rounded-lg admin-theme-bg text-xs font-bold"><i class="fas fa-save mr-1"></i>Save home page</button></div>
</form>
@endsection
