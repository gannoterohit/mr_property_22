@extends('layouts.admin')

@section('title', 'Broker Settings')

@section('admin-content')
<script>
    window.location.replace("{{ route('admin.settings') }}#broker");
</script>
<div class="p-8 text-center">
    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 mb-4">
        <i class="fas fa-handshake text-xl"></i>
    </div>
    <h2 class="text-xl font-bold text-slate-800">Redirecting to Unified Settings...</h2>
    <p class="text-sm text-slate-500 mt-2">Broker settings have been unified into Business Settings.</p>
    <a href="{{ route('admin.settings') }}#broker" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl admin-theme-bg text-white font-bold text-sm">
        Go to Broker Settings <i class="fas fa-arrow-right"></i>
    </a>
</div>
@endsection