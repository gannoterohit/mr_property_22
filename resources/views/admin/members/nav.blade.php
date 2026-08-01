@pushOnce('styles')
<style>
    .admin-member-nav-active { background: var(--admin-primary); color: #fff; box-shadow: 0 8px 18px rgba(var(--admin-primary-rgb), .14); }
</style>
@endPushOnce

<nav class="flex gap-2 overflow-x-auto rounded-2xl border bg-white p-2 shadow-sm">
    @foreach([
        ['admin.members.index','admin.members.index','fa-magnifying-glass-chart','Member 360'],
        ['admin.users','admin.users*','fa-users','Users'],
        ['admin.owners','admin.owners*','fa-user-tie','Owners'],
    ] as [$routeName,$match,$icon,$label])
        <a href="{{ route($routeName) }}" class="inline-flex min-w-max items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-extrabold transition {{ request()->routeIs($match) ? 'admin-member-nav-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <i class="fas {{ $icon }}"></i>{{ $label }}
        </a>
    @endforeach
</nav>
