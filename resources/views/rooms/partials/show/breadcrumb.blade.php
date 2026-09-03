<nav class="mb-4" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-2 text-xs text-gray-500">
        <li><a href="{{ route('home') }}" class="hover:text-blue-600"><i class="fas fa-home mr-1"></i>Home</a></li>
        <li aria-hidden="true">›</li>
        <li><a href="{{ route('rooms.index') }}" class="hover:text-blue-600">Rooms</a></li>
        <li aria-hidden="true">›</li>
        <li class="text-gray-900 font-semibold truncate max-w-xs" aria-current="page">{{ $room->title }}</li>
    </ol>
</nav>
