<!-- Mobile Skeleton Cards -->
<div class="md:hidden space-y-4">
    @for($i = 0; $i < 3; $i++)
    <div class="skeleton-card">
        <div class="skeleton-image"></div>
        <div class="skeleton-content">
            <div class="skeleton-line title"></div>
            <div class="skeleton-line medium"></div>
            <div class="skeleton-tags">
                <div class="skeleton-tag"></div>
                <div class="skeleton-tag"></div>
            </div>
            <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                <div>
                    <div class="skeleton-line short" style="width: 80px; height: 20px; margin-bottom: 6px;"></div>
                    <div class="skeleton-line short" style="width: 60px; height: 10px;"></div>
                </div>
                <div class="skeleton-button"></div>
            </div>
        </div>
    </div>
    @endfor
</div>

<!-- Desktop Skeleton Grid -->
<div class="hidden md:grid md:grid-cols-2 lg:grid-cols-3 md:gap-6">
    @for($i = 0; $i < 3; $i++)
    <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100">
        <div class="skeleton skeleton-image w-full h-48"></div>
        <div class="p-4 space-y-3">
            <div class="skeleton skeleton-text w-3/4"></div>
            <div class="skeleton skeleton-text w-1/2"></div>
            <div class="flex justify-between items-center pt-2">
                <div class="skeleton skeleton-text w-24"></div>
                <div class="skeleton skeleton-text w-20"></div>
            </div>
        </div>
    </div>
    @endfor
</div>
