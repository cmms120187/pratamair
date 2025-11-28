<div class="h-screen flex flex-col" style="height: 100vh; overflow: visible;">
    <nav class="bg-white border-r p-3 sm:p-4 h-full w-full flex flex-col" style="height: 100%; overflow: visible;">
        <!-- Header Section - Fixed -->
        <div class="flex-shrink-0" style="position: relative; z-index: 10;">
            <div class="mb-6 sm:mb-8 flex flex-col items-center">
                <a href="{{ route('dashboard') }}" @click="sidebarOpen = false" class="flex items-center justify-center mb-4">
                    <img src="{{ asset('images/logo_tpm.png') }}" alt="Logo TPM" class="h-10 sm:h-12 w-auto object-contain">
                </a>
                <div class="font-bold text-base sm:text-lg text-gray-700">TPM CMMS</div>
            </div>
        </div>
        
        <!-- Menu Section - Scrollable -->
        <div class="flex-1 overflow-y-auto overflow-x-visible" style="flex: 1 1 0%; min-height: 0; -webkit-overflow-scrolling: touch; overflow-x: visible !important;" x-data="{ activeSubmenu: null }">
            <ul class="space-y-0.5 pb-4" @click.away="activeSubmenu = null">
                @php
                    $currentUrl = request()->path();
                    
                    $menuGroups = [
                        [
                            'name' => 'Dashboard',
                            'route' => route('dashboard'),
                            'icon' => 'home',
                            'type' => 'single'
                        ],
                        // [
                        //     'name' => 'Room ERP',
                        //     'route' => route('room-erp.index'),
                        //     'icon' => 'server',
                        //     'type' => 'single'
                        // ],
                        // [
                        //     'name' => 'Machine ERP',
                        //     'route' => route('machine-erp.index'),
                        //     'icon' => 'server',
                        //     'type' => 'single'
                        // ],
                        [
                            'name' => 'Part ERP',
                            'route' => route('part-erp.index'),
                            'icon' => 'server',
                            'type' => 'single'
                        ],
                        [
                            'name' => 'Location',
                            'icon' => 'building',
                            'type' => 'group',
                            'children' => [
                                ['name' => 'Plants', 'route' => '/plants', 'icon' => 'leaf'],
                                ['name' => 'Processes', 'route' => '/processes', 'icon' => 'cog'],
                                ['name' => 'Lines', 'route' => '/lines', 'icon' => 'bars'],
                                // ['name' => 'Rooms', 'route' => '/rooms', 'icon' => 'building'],
                                ['name' => 'Room ERP', 'route' => '/room-erp', 'icon' => 'server'],
                            ]
                        ],
                        [
                            'name' => 'Machinary',
                            'icon' => 'server',
                            'type' => 'group',
                            'children' => [
                                ['name' => 'Systems', 'route' => '/systems', 'icon' => 'cog'],
                                ['name' => 'Groups', 'route' => '/groups', 'icon' => 'users'],
                                ['name' => 'Machine Types', 'route' => '/machine-types', 'icon' => 'chip'],
                                ['name' => 'Brands', 'route' => '/brands', 'icon' => 'tag'],
                                ['name' => 'Models', 'route' => '/models', 'icon' => 'cube'],
                                // ['name' => 'Machines', 'route' => '/machines', 'icon' => 'server'],
                                ['name' => 'Machine ERP', 'route' => '/machine-erp', 'icon' => 'server'],
                                ['name' => 'Mutasi', 'route' => '/mutasi', 'icon' => 'exchange'],
                            ]
                        ],
                        // [
                        //     'name' => 'Sparepart',
                        //     'icon' => 'cube',
                        //     'type' => 'group',
                        //     'children' => [
                        //         ['name' => 'Parts', 'route' => '/parts', 'icon' => 'puzzle'],
                        //         ['name' => 'Part ERP', 'route' => '/part-erp', 'icon' => 'server'],
                        //     ]
                        // ],
                        [
                            'name' => 'Downtime',
                            'icon' => 'clock',
                            'type' => 'group',
                            'children' => [
                                ['name' => 'Problems', 'route' => '/problems', 'icon' => 'exclamation'],
                                // ['name' => 'Problem MMS', 'route' => '/problem-mms', 'icon' => 'bug'],
                                ['name' => 'Reasons', 'route' => '/reasons', 'icon' => 'question'],
                                ['name' => 'Actions', 'route' => '/actions', 'icon' => 'bolt'],
                                // ['name' => 'Downtimes', 'route' => '/downtimes', 'icon' => 'clock'],
                                // ['name' => 'Downtime ERP', 'route' => '/downtime_erp', 'icon' => 'server'],
                                ['name' => 'Downtime ERP2', 'route' => '/downtime-erp2', 'icon' => 'server'],
                                ['name' => 'Work Orders', 'route' => '/work-orders', 'icon' => 'clipboard-list'],
                            ]
                        ],
                        // [
                        //     'name' => 'Downtime ERP2',
                        //     'route' => route('downtime-erp2.index'),
                        //     'icon' => 'server',
                        //     'type' => 'single'
                        // ],
                        [
                            'name' => 'Users',
                            'icon' => 'user',
                            'type' => 'group',
                            'children' => [
                                ['name' => 'Users', 'route' => '/users', 'icon' => 'user'],
                                ['name' => 'Struktur Organisasi', 'route' => '/users/organizational-structure', 'icon' => 'sitemap'],
                                ['name' => 'Bagan STO', 'route' => '/users/organizational-structure/chart', 'icon' => 'sitemap'],
                                ['name' => 'Activity', 'route' => '/activities', 'icon' => 'clock'],
                            ]
                        ],
                        [
                            'name' => 'Preventive Maintenance',
                            'icon' => 'wrench',
                            'type' => 'group',
                            'children' => [
                                ['name' => 'Scheduling', 'route' => '/preventive-maintenance/scheduling', 'icon' => 'calendar'],
                                ['name' => 'Controlling', 'route' => '/preventive-maintenance/controlling', 'icon' => 'cog'],
                                ['name' => 'Monitoring', 'route' => '/preventive-maintenance/monitoring', 'icon' => 'chart'],
                                ['name' => 'Updating', 'route' => '/preventive-maintenance/updating', 'icon' => 'edit'],
                                ['name' => 'Reporting', 'route' => '/preventive-maintenance/reporting', 'icon' => 'document'],
                            ]
                        ],
                        [
                            'name' => 'Predictive Maintenance',
                            'icon' => 'chart-line',
                            'type' => 'group',
                            'children' => [
                                ['name' => 'Standards', 'route' => '/standards', 'icon' => 'clipboard-check'],
                                ['name' => 'Scheduling', 'route' => '/predictive-maintenance/scheduling', 'icon' => 'calendar'],
                                ['name' => 'Controlling', 'route' => '/predictive-maintenance/controlling', 'icon' => 'cog'],
                                ['name' => 'Monitoring', 'route' => '/predictive-maintenance/monitoring', 'icon' => 'chart'],
                                ['name' => 'Updating', 'route' => '/predictive-maintenance/updating', 'icon' => 'edit'],
                                ['name' => 'Reporting', 'route' => '/predictive-maintenance/reporting', 'icon' => 'document'],
                            ]
                        ],
                        [
                            'name' => 'Report and Analytics',
                            'icon' => 'chart-bar',
                            'type' => 'group',
                            'children' => [
                                [
                                    'name' => 'MTTR & MTBF',
                                    'route' => '/mttr-mtbf',
                                    'icon' => 'chart-line',
                                    'type' => 'single'
                                ],
                                [
                                    'name' => 'Pareto Mesin',
                                    'route' => '/pareto-machine',
                                    'icon' => 'chart-bar',
                                    'type' => 'single'
                                ],
                                [
                                    'name' => 'Summary Downtime',
                                    'route' => '/summary-downtime',
                                    'icon' => 'chart',
                                    'type' => 'single'
                                ],
                                [
                                    'name' => 'Kinerja Mekanik',
                                    'route' => '/mechanic-performance',
                                    'icon' => 'users',
                                    'type' => 'single'
                                ],
                                [
                                    'name' => 'Root Cause Analysis',
                                    'route' => '/root-cause-analysis',
                                    'icon' => 'search',
                                    'type' => 'single'
                                ],
                            ]
                        ],
                    ];
                    
                    // Check if any child is active for group menus
                    function isGroupActive($group, $currentUrl) {
                        if (!isset($group['children'])) return false;
                        foreach ($group['children'] as $child) {
                            $routePath = trim($child['route'], '/');
                            $routeName = strtolower(str_replace([' ', '-'], '', $child['name']));
                            if ($currentUrl === $routePath || 
                                Str::contains($currentUrl, $routePath) ||
                                Str::contains($currentUrl, $routeName)) {
                                return true;
                            }
                        }
                        return false;
                    }
                    
                    // Check if menu item is active
                    function isMenuActive($route, $name, $currentUrl) {
                        $routePath = trim($route, '/');
                        $routeName = strtolower(str_replace([' ', '-'], '', $name));
                        return $currentUrl === $routePath || 
                               Str::contains($currentUrl, $routePath) ||
                               Str::contains($currentUrl, $routeName);
                    }
                @endphp
                
                @foreach($menuGroups as $menu)
                    @if($menu['type'] === 'single')
                        <li>
                            <a href="{{ $menu['route'] }}"
                                @click="sidebarOpen = false"
                                class="flex items-center px-2 sm:px-3 py-2 rounded-lg transition-colors duration-150 hover:bg-blue-100 hover:text-blue-700 text-sm sm:text-base {{ isMenuActive($menu['route'], $menu['name'], $currentUrl) ? 'bg-blue-600 text-white' : 'text-gray-700' }}"
                                class="flex items-center">
                                <span class="flex-shrink-0 mr-3">
                                    @include('layouts.partials.menu-icon', ['icon' => $menu['icon']])
                                </span>
                                <span>{{ $menu['name'] }}</span>
                            </a>
                        </li>
                    @else
                        <li x-data="{ 
                            open: false,
                            menuTop: 0
                        }" 
                            class="relative group-menu"
                            @close-other-submenus.window="
                                if ($event.detail !== '{{ $menu['name'] }}') {
                                    open = false;
                                }
                            ">
                            <div class="w-full flex items-center justify-between px-2 sm:px-3 py-2 rounded-lg transition-colors duration-150 hover:bg-blue-100 hover:text-blue-700 cursor-pointer text-sm sm:text-base {{ isGroupActive($menu, $currentUrl) ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }}"
                                 @click.stop="
                                    // Close other submenus
                                    $dispatch('close-other-submenus', '{{ $menu['name'] }}');
                                    // Toggle this submenu
                                    if (!open) {
                                        const rect = $el.closest('li').getBoundingClientRect();
                                        menuTop = Math.max(0, rect.top);
                                    }
                                    open = !open;
                                 ">
                                <div class="flex items-center flex-1 min-w-0">
                                    <span class="flex-shrink-0 mr-3">
                                        @include('layouts.partials.menu-icon', ['icon' => $menu['icon']])
                                    </span>
                                    <span class="truncate">{{ $menu['name'] }}</span>
                                </div>
                                <svg class="w-4 h-4 transition-transform duration-200 flex-shrink-0" 
                                     :class="{ 'rotate-180': open }"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            <!-- Submenu Modal - appears on the right -->
                            <div x-show="open" 
                                x-cloak
                                @click.away="open = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform translate-x-4"
                                x-transition:enter-end="opacity-100 transform translate-x-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform translate-x-0"
                                x-transition:leave-end="opacity-0 transform translate-x-4"
                                class="fixed bg-white shadow-2xl border-l border-gray-200 overflow-y-auto"
                                style="z-index: 9999; display: none;"
                                :style="`left: 230px; top: ${menuTop}px; width: 280px; max-height: calc(100vh - ${menuTop}px);`">
                                <div class="p-4 border-b border-gray-200 bg-blue-50 sticky top-0 z-10">
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-semibold text-gray-800 text-sm sm:text-base">{{ $menu['name'] }}</h3>
                                        <button @click.stop="open = false" class="text-gray-500 hover:text-gray-700 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <ul class="p-2 space-y-1">
                                    @foreach($menu['children'] as $child)
                                        <li>
                                            <a href="{{ $child['route'] }}"
                                                @click="sidebarOpen = false; open = false"
                                                class="flex items-center px-3 py-2.5 rounded-lg transition-colors duration-150 hover:bg-blue-100 hover:text-blue-700 text-sm {{ isMenuActive($child['route'], $child['name'], $currentUrl) ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
                                                <span class="mr-3">
                                                    @include('layouts.partials.menu-icon', ['icon' => $child['icon']])
                                                </span>
                                                <span>{{ $child['name'] }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
        
        <!-- Profile Button Section - Fixed at Bottom -->
        <div class="flex-shrink-0 pt-4 border-t border-gray-200 mt-auto" style="position: relative; z-index: 10; background: white;">
            <button @click="$dispatch('open-profile-modal'); sidebarOpen = false" 
                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-2.5 px-3 sm:px-4 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:scale-[1.02] text-sm sm:text-base flex items-center"
                class="flex items-center justify-center">
                <svg class="w-5 h-5 flex-shrink-0 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span>Profile</span>
            </button>
        </div>
    </nav>
</div>
