<nav class="flex gap-2 overflow-x-auto rounded-2xl border bg-white p-2 shadow-sm">
    @foreach([
        ['admin.members.index','admin.members.index','fa-magnifying-glass-chart','Member 360'],
        ['admin.users','admin.users*','fa-users','Users'],
        ['admin.owners','admin.owners*','fa-user-tie','Owners'],
        ['admin.brokers.index','admin.brokers*','fa-handshake','Brokers'],
    ] as [$routeName,$match,$icon,$label])
        <a href="{{ route($routeName) }}" class="inline-flex min-w-max items-center gap-2 rounded-xl px-4 py-2.5 text-xs font-extrabold transition {{ request()->routeIs($match) ? 'admin-theme-bg' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <i class="fas {{ $icon }}"></i>{{ $label }}
        </a>
    @endforeach
</nav>
