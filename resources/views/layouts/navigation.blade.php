<div class="h-screen flex flex-col" style="height: 100vh; overflow: visible;" x-data="{ sidebarCollapsed: $store.sidebarCollapsed || false }">
    <nav class="bg-white border-r p-3 sm:p-4 h-full w-full flex flex-col transition-all duration-300" 
         style="height: 100%; overflow: visible;"
         :class="$store.sidebarCollapsed && window.innerWidth >= 1024 ? 'items-center px-2' : ''">
        <!-- Header Section - Fixed -->
        <div class="flex-shrink-0" style="position: relative; z-index: 10;">
            <div class="mb-6 sm:mb-8 flex flex-col items-center transition-all duration-300"
                 :class="$store.sidebarCollapsed && window.innerWidth >= 1024 ? 'mb-4' : ''">
                <a href="{{ route('dashboard') }}" @click="sidebarOpen = false" class="flex items-center justify-center mb-4">
                    <img src="{{ asset('images/logo_tpm.png') }}" alt="Logo TPM" 
                         class="transition-all duration-300"
                         :class="$store.sidebarCollapsed && window.innerWidth >= 1024 ? 'h-8 w-8' : 'h-10 sm:h-12 w-auto object-contain'">
                </a>
                <div class="font-bold text-base sm:text-lg text-gray-700 transition-all duration-300 overflow-hidden"
                     :class="$store.sidebarCollapsed && window.innerWidth >= 1024 ? 'hidden' : ''">TPM CMMS</div>
            </div>
        </div>
        
        <!-- Menu Section - Scrollable -->
        <div class="flex-1 overflow-y-auto overflow-x-visible" style="flex: 1 1 0%; min-height: 0; -webkit-overflow-scrolling: touch; overflow-x: visible !important;" x-data="{ activeSubmenu: null }">
            <ul class="space-y-0.5 pb-4" @click.away="activeSubmenu = null">
                @php
                    $currentUrl = request()->path();
                    $userRole = Auth::user()->role ?? 'mekanik';
                    
                    // Function to check menu access
                    function canAccessMenu($menuKey, $userRole) {
                        try {
                            if (class_exists('\App\Helpers\PermissionHelper')) {
                                return \App\Helpers\PermissionHelper::canAccessMenu($userRole, $menuKey);
                            }
                        } catch (\Exception $e) {
                            // If PermissionHelper not available, allow access (fallback)
                        }
                        // Fallback: allow all access if PermissionHelper not available
                        return true;
                    }
                    
                    // Function to filter menu children
                    function filterMenuChildren($children, $userRole) {
                        $filtered = [];
                        foreach ($children as $child) {
                            // Check if menu is admin only
                            if (isset($child['admin_only']) && $child['admin_only'] === true && $userRole !== 'admin') {
                                continue;
                            }
                            
                            $menuKey = $child['menu_key'] ?? strtolower(str_replace([' ', '-'], '_', $child['name']));
                            if (canAccessMenu($menuKey, $userRole)) {
                                $filtered[] = $child;
                            }
                        }
                        return $filtered;
                    }
                    
                    // Load menu configuration from config file
                    $menuGroups = config('menu.menu_groups', []);
                    
                    // Process menu groups to convert route names to actual routes
                    // Use array index to avoid reference issues
                    foreach ($menuGroups as $index => $menu) {
                        // Convert route name to actual route for single type menus
                        if ($menu['type'] === 'single' && isset($menu['route']) && !str_starts_with($menu['route'], '/') && !str_starts_with($menu['route'], 'http')) {
                            try {
                                $menuGroups[$index]['route'] = route($menu['route']);
                            } catch (\Exception $e) {
                                // If route doesn't exist, keep original value
                            }
                        }
                        
                        // Process children for group menus
                        if ($menu['type'] === 'group' && isset($menu['children'])) {
                            foreach ($menu['children'] as $childIndex => $child) {
                                // Convert route paths to full URLs if needed
                                if (isset($child['route']) && str_starts_with($child['route'], '/')) {
                                    // Keep as is, it's already a path
                                } elseif (isset($child['route']) && !str_starts_with($child['route'], 'http')) {
                                    try {
                                        $menuGroups[$index]['children'][$childIndex]['route'] = route($child['route']);
                                    } catch (\Exception $e) {
                                        // If route doesn't exist, keep original value
                                    }
                                }
                            }
                        }
                    }
                    
                    // Add Role Permissions menu for admin users in Users group
                    foreach ($menuGroups as $index => $menu) {
                        if ($menu['menu_key'] === 'users' && $userRole === 'admin') {
                            // Check if permissions menu already exists
                            $hasPermissions = false;
                            if (isset($menu['children'])) {
                                foreach ($menu['children'] as $child) {
                                    if (isset($child['menu_key']) && $child['menu_key'] === 'permissions') {
                                        $hasPermissions = true;
                                        break;
                                    }
                                }
                            }
                            if (!$hasPermissions) {
                                if (!isset($menuGroups[$index]['children'])) {
                                    $menuGroups[$index]['children'] = [];
                                }
                                $menuGroups[$index]['children'][] = [
                                    'name' => 'Role Permissions',
                                    'route' => '/permissions',
                                    'icon' => 'key',
                                    'menu_key' => 'permissions'
                                ];
                            }
                        }
                    }
                    
                    // Check if any child is active for group menus
                    function isGroupActive($group, $currentUrl) {
                        if (!isset($group['children'])) return false;
                        foreach ($group['children'] as $child) {
                            $routePath = trim($child['route'], '/');
                            // Use exact match or ensure the URL starts with the route path
                            // This prevents 'predictive-maintenance' from matching 'preventive-maintenance'
                            if ($currentUrl === $routePath) {
                                return true;
                            }
                            // Check if current URL starts with route path followed by / or ? or end of string
                            $routeLength = strlen($routePath);
                            if (strlen($currentUrl) >= $routeLength && 
                                substr($currentUrl, 0, $routeLength) === $routePath) {
                                $nextChar = strlen($currentUrl) > $routeLength ? $currentUrl[$routeLength] : '';
                                if ($nextChar === '' || $nextChar === '/' || $nextChar === '?') {
                                    return true;
                                }
                            }
                        }
                        return false;
                    }
                    
                    // Check if menu item is active
                    function isMenuActive($route, $name, $currentUrl) {
                        $routePath = trim($route, '/');
                        // Use exact match
                        if ($currentUrl === $routePath) {
                            return true;
                        }
                        // Check if current URL starts with route path followed by / or ? or end of string
                        $routeLength = strlen($routePath);
                        if (strlen($currentUrl) >= $routeLength && 
                            substr($currentUrl, 0, $routeLength) === $routePath) {
                            $nextChar = strlen($currentUrl) > $routeLength ? $currentUrl[$routeLength] : '';
                            if ($nextChar === '' || $nextChar === '/' || $nextChar === '?') {
                                return true;
                            }
                        }
                        return false;
                    }
                @endphp
                
                @foreach($menuGroups as $menuIndex => $menu)
                    @php
                        // Filter menu based on role
                        $menuKey = $menu['menu_key'] ?? strtolower(str_replace([' ', '-'], '_', $menu['name']));
                        
                        // For group menus, check if any child is accessible
                        if ($menu['type'] === 'group') {
                            $filteredChildren = filterMenuChildren($menu['children'] ?? [], $userRole);
                            // Only show group if it has accessible children
                            if (empty($filteredChildren)) {
                                continue;
                            }
                            // Create a copy of menu to avoid modifying original array
                            $displayMenu = $menu;
                            $displayMenu['children'] = $filteredChildren;
                        } else {
                            // For single menus, check direct access
                            if (!canAccessMenu($menuKey, $userRole)) {
                                continue;
                            }
                            $displayMenu = $menu;
                        }
                    @endphp
                    @if($displayMenu['type'] === 'single')
                        <li>
                            <a href="{{ $displayMenu['route'] }}"
                                @click="sidebarOpen = false"
                                class="flex items-center px-2 sm:px-3 py-2 rounded-lg transition-colors duration-150 hover:bg-blue-100 hover:text-blue-700 text-sm sm:text-base {{ isMenuActive($displayMenu['route'], $displayMenu['name'], $currentUrl) ? 'bg-blue-600 text-white' : 'text-gray-700' }}"
                                :class="$store.sidebar.collapsed && window.innerWidth >= 1024 ? 'justify-center px-2' : ''"
                                :title="$store.sidebar.collapsed && window.innerWidth >= 1024 ? '{{ $displayMenu['name'] }}' : ''">
                                <span class="flex-shrink-0" :class="$store.sidebar.collapsed && window.innerWidth >= 1024 ? '' : 'mr-3'">
                                    @include('layouts.partials.menu-icon', ['icon' => $displayMenu['icon']])
                                </span>
                                <span :class="$store.sidebar.collapsed && window.innerWidth >= 1024 ? 'hidden' : ''">{{ $displayMenu['name'] }}</span>
                            </a>
                        </li>
                    @else
                        <li x-data="{ open: false }" 
                            class="relative menu-group-item"
                            data-menu-name="{{ $displayMenu['name'] }}"
                            @close-other-submenus.window="
                                if ($event.detail !== '{{ $displayMenu['name'] }}') {
                                    open = false;
                                }
                            ">
                            <div class="w-full flex items-center justify-between px-2 sm:px-3 py-2 rounded-lg transition-colors duration-150 hover:bg-blue-100 hover:text-blue-700 cursor-pointer text-sm sm:text-base {{ isGroupActive($displayMenu, $currentUrl) ? 'bg-blue-50 text-blue-700' : 'text-gray-700' }} menu-group-toggle"
                                 :class="$store.sidebar.collapsed && window.innerWidth >= 1024 ? 'justify-center px-2' : ''"
                                 :title="$store.sidebar.collapsed && window.innerWidth >= 1024 ? '{{ $displayMenu['name'] }}' : ''"
                                 @click.stop="
                                    if ($store.sidebar.collapsed && window.innerWidth >= 1024) {
                                        // If collapsed, expand sidebar temporarily to show submenu
                                        $store.sidebar.collapsed = false;
                                        setTimeout(() => {
                                            // Close other submenus
                                            $dispatch('close-other-submenus', '{{ $displayMenu['name'] }}');
                                            open = !open;
                                        }, 100);
                                    } else {
                                        // Close other submenus
                                        $dispatch('close-other-submenus', '{{ $displayMenu['name'] }}');
                                        open = !open;
                                    }
                                 ">
                                <div class="flex items-center flex-1 min-w-0" :class="$store.sidebar.collapsed && window.innerWidth >= 1024 ? 'justify-center flex-1' : ''">
                                    <span class="flex-shrink-0" :class="$store.sidebar.collapsed && window.innerWidth >= 1024 ? '' : 'mr-3'">
                                        @include('layouts.partials.menu-icon', ['icon' => $displayMenu['icon']])
                                    </span>
                                    <span class="truncate" :class="$store.sidebar.collapsed && window.innerWidth >= 1024 ? 'hidden' : ''">{{ $displayMenu['name'] }}</span>
                                </div>
                                <svg class="w-4 h-4 transition-transform duration-200 flex-shrink-0 menu-arrow" 
                                     :class="($store.sidebar.collapsed && window.innerWidth >= 1024 ? 'hidden' : '') + ' ' + (open ? 'rotate-180' : '')"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            <!-- Submenu Dropdown - appears below -->
                            <ul x-show="open" 
                                x-cloak
                                class="menu-submenu ml-4 mt-1 space-y-0.5 border-l-2 border-blue-200 pl-2"
                                @click.away="open = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 transform translate-y-0"
                                x-transition:leave-end="opacity-0 transform -translate-y-2"
                                style="display: none;">
                                @foreach($displayMenu['children'] as $child)
                                    <li>
                                        <a href="{{ $child['route'] }}"
                                            @click="sidebarOpen = false; open = false"
                                            class="flex items-center px-2 sm:px-3 py-2 rounded-lg transition-colors duration-150 hover:bg-blue-100 hover:text-blue-700 text-sm {{ isMenuActive($child['route'], $child['name'], $currentUrl) ? 'bg-blue-600 text-white' : 'text-gray-700' }}"
                                            :class="$store.sidebar.collapsed && window.innerWidth >= 1024 ? 'justify-center px-2' : ''"
                                            :title="$store.sidebar.collapsed && window.innerWidth >= 1024 ? '{{ $child['name'] }}' : ''">
                                            <span class="flex-shrink-0" :class="$store.sidebar.collapsed && window.innerWidth >= 1024 ? '' : 'mr-3'">
                                                @include('layouts.partials.menu-icon', ['icon' => $child['icon']])
                                            </span>
                                            <span :class="$store.sidebar.collapsed && window.innerWidth >= 1024 ? 'hidden' : ''">{{ $child['name'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
        
        <!-- Profile Button Section - Fixed at Bottom -->
        <div class="flex-shrink-0 pt-4 border-t border-gray-200 mt-auto" style="position: relative; z-index: 10; background: white;">
            <button @click="$dispatch('open-profile-modal'); sidebarOpen = false" 
                class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-2.5 px-3 sm:px-4 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:scale-[1.02] text-sm sm:text-base flex items-center"
                :class="$store.sidebar.collapsed && window.innerWidth >= 1024 ? 'justify-center px-2' : ''"
                :title="$store.sidebar.collapsed && window.innerWidth >= 1024 ? 'Profile' : ''">
                <svg class="w-5 h-5 flex-shrink-0" :class="$store.sidebar.collapsed && window.innerWidth >= 1024 ? '' : 'mr-2'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <div class="flex-1 text-left" :class="$store.sidebar.collapsed && window.innerWidth >= 1024 ? 'hidden' : ''">
                    <div>Profile</div>
                    @if(Auth::user()->nik)
                        <div class="text-xs opacity-90">{{ Auth::user()->nik }}</div>
                    @endif
                </div>
            </button>
        </div>
    </nav>
</div>
