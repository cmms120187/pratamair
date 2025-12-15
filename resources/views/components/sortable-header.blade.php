@props(['column', 'currentSort' => null, 'currentDir' => 'asc', 'label'])

@php
    $currentSort = $currentSort ?? request()->get('sort_by', '');
    $currentDir = $currentDir ?? request()->get('sort_dir', 'asc');
    $newDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
    $url = request()->fullUrlWithQuery(['sort_by' => $column, 'sort_dir' => $newDir]);
    $isActive = $currentSort === $column;
@endphp

<th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider cursor-pointer hover:bg-blue-700 transition-colors select-none" onclick="window.location.href='{{ $url }}'">
    <div class="flex items-center gap-1">
        <span>{{ $label }}</span>
        @if($isActive)
            @if($currentDir === 'asc')
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                </svg>
            @else
                <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            @endif
        @else
            <svg class="w-4 h-4 inline-block opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
            </svg>
        @endif
    </div>
</th>

