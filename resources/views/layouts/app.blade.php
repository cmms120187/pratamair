<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CMMS') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/logo_tpm.png') }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo_tpm.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <!-- Alpine.js x-cloak style -->
        <style>
            [x-cloak] { display: none !important; }
        </style>
        <!-- Alpine.js CDN Fallback (for production) -->
        <script>
            // Check if Alpine is loaded after a delay, if not load from CDN
            setTimeout(function() {
                if (typeof Alpine === 'undefined' || typeof window.Alpine === 'undefined') {
                    console.warn('Alpine.js not loaded from Vite, loading from CDN...');
                    var script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js';
                    script.defer = true;
                    script.onload = function() {
                        console.log('Alpine.js loaded from CDN');
                        if (typeof Alpine !== 'undefined') {
                            // Initialize Alpine store for sidebar state
                            Alpine.store('sidebar', {
                                collapsed: false
                            });
                            Alpine.start();
                        }
                    };
                    document.head.appendChild(script);
                } else {
                    // Initialize Alpine store if Alpine is already loaded
                    if (typeof Alpine !== 'undefined' && Alpine.store) {
                        Alpine.store('sidebar', {
                            collapsed: false
                        });
                    }
                }
            }, 100);
        </script>
        <!-- ZXing Library for Barcode Scanning -->
        <script src="https://cdn.jsdelivr.net/npm/@zxing/library@latest"></script>
        <style>
            /* Ensure sidebar is truly fixed and doesn't scroll with page content */
            aside.fixed {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                bottom: 0 !important;
                height: 100vh !important;
                overflow: visible !important;
                will-change: transform;
            }
            
            /* Ensure sidebar container doesn't cause scrolling but allows submenu to overflow */
            aside.fixed > div {
                height: 100vh !important;
                overflow-y: auto !important;
                overflow-x: visible !important;
                position: relative;
            }
            
            /* Allow submenu to overflow from sidebar */
            aside.fixed nav {
                overflow: visible !important;
            }
            
            /* Prevent body scroll from affecting sidebar */
            body {
                overflow-x: hidden;
            }
            
            /* Ensure main content scrolls independently */
            main {
                overflow-y: auto;
                overflow-x: hidden;
            }
            
            /* Prevent main content from overlapping submenu */
            .main-content-wrapper {
                position: relative;
                z-index: 1;
            }
        </style>
    </head>
    <body class="font-sans antialiased" x-data="{ profileModalOpen: false, sidebarOpen: false }" 
          x-init="$store.sidebar = { collapsed: false }"
          @open-profile-modal.window="profileModalOpen = true">
    <div class="min-h-screen bg-gray-100 flex">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden"></div>
        
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 transform transition-all duration-300 ease-in-out lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               :style="$store.sidebar.collapsed && window.innerWidth >= 1024 ? 'width: 70px; min-width: 70px;' : 'width: 230px; min-width: 200px;'"
               style="overflow: visible;">
            <div class="h-screen bg-white border-r transition-all duration-300" style="overflow: visible;">
                @include('layouts.navigation')
            </div>
        </aside>
        
        <!-- Sidebar Toggle Button (Desktop) -->
        <button @click="$store.sidebar.collapsed = !$store.sidebar.collapsed" 
                class="hidden lg:flex fixed top-4 z-50 bg-white hover:bg-gray-100 text-gray-600 hover:text-gray-900 p-2 rounded-lg shadow-lg transition-all duration-300 items-center justify-center"
                :style="$store.sidebar.collapsed ? 'left: 4px;' : 'left: 230px;'"
                title="Toggle Sidebar">
            <svg class="w-5 h-5 transition-transform duration-300" :class="$store.sidebar.collapsed ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
        </button>
        
        <!-- Main Content -->
        <div class="flex-1 w-full overflow-x-hidden main-content-wrapper transition-all duration-300"
             :class="$store.sidebar.collapsed && window.innerWidth >= 1024 ? 'lg:ml-[70px]' : 'lg:ml-[230px]'">
            <!-- Mobile Header -->
            <header class="lg:hidden bg-white shadow sticky top-0 z-30">
                <div class="flex items-center justify-between px-4 py-3">
                    <button @click="sidebarOpen = !sidebarOpen" 
                            class="text-gray-600 hover:text-gray-900 focus:outline-none focus:text-gray-900">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div class="flex items-center">
                        <img src="{{ asset('images/logo_tpm.png') }}" alt="Logo TPM" class="h-8 w-auto object-contain">
                        <span class="ml-2 font-bold text-gray-700">TPM CMMS</span>
                    </div>
                    <div class="w-6"></div> <!-- Spacer for centering -->
                </div>
            </header>
            
            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow hidden lg:block">
                    <div class="py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset
            <!-- Page Content -->
            <main>
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Profile Modal -->
    <div x-show="profileModalOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="profileModalOpen = false"
         @keydown.escape.window="profileModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <!-- Modal Container -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="profileModalOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <!-- Close Button -->
                <button @click="profileModalOpen = false" 
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                
                <!-- Modal Content -->
                <div class="space-y-4">
                    <div class="text-center">
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">Profile Information</h2>
                        <div class="w-20 h-20 mx-auto bg-blue-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</label>
                            <p class="mt-1 text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                        </div>
                        @if(Auth::user()->nik)
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">NIK</label>
                            <p class="mt-1 text-sm font-medium text-gray-900">{{ Auth::user()->nik }}</p>
                        </div>
                        @endif
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</label>
                            <p class="mt-1 text-sm font-medium text-gray-900">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col space-y-2 pt-4">
                        <a href="{{ route('profile.edit') }}" 
                           class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition text-center">
                            Edit Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" 
                                    class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @stack('scripts')
    
    <!-- Fallback JavaScript for menu submenu (if Alpine.js fails) -->
    <script>
        // Fallback menu toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a bit for Alpine to initialize
            setTimeout(function() {
                // Check if Alpine is working
                var testElement = document.querySelector('[x-data]');
                var alpineWorking = testElement && typeof Alpine !== 'undefined';
                
                if (!alpineWorking) {
                    console.warn('Alpine.js not detected, using fallback menu functionality');
                    
                    // Add fallback click handlers for menu groups
                    var menuToggles = document.querySelectorAll('.menu-group-toggle');
                    menuToggles.forEach(function(toggle) {
                        toggle.addEventListener('click', function(e) {
                            e.stopPropagation();
                            var menuItem = this.closest('.menu-group-item');
                            var submenu = menuItem.querySelector('.menu-submenu');
                            var arrow = menuItem.querySelector('.menu-arrow');
                            
                            if (submenu) {
                                // Close other submenus
                                document.querySelectorAll('.menu-submenu').forEach(function(menu) {
                                    if (menu !== submenu) {
                                        menu.style.display = 'none';
                                        var otherArrow = menu.closest('.menu-group-item')?.querySelector('.menu-arrow');
                                        if (otherArrow) {
                                            otherArrow.style.transform = 'rotate(0deg)';
                                        }
                                    }
                                });
                                
                                // Toggle current submenu
                                if (submenu.style.display === 'none' || !submenu.style.display) {
                                    submenu.style.display = 'block';
                                    if (arrow) {
                                        arrow.style.transform = 'rotate(180deg)';
                                    }
                                } else {
                                    submenu.style.display = 'none';
                                    if (arrow) {
                                        arrow.style.transform = 'rotate(0deg)';
                                    }
                                }
                            }
                        });
                    });
                    
                    // Close submenus when clicking outside
                    document.addEventListener('click', function(e) {
                        if (!e.target.closest('.menu-group-item')) {
                            document.querySelectorAll('.menu-submenu').forEach(function(menu) {
                                menu.style.display = 'none';
                                var arrow = menu.closest('.menu-group-item')?.querySelector('.menu-arrow');
                                if (arrow) {
                                    arrow.style.transform = 'rotate(0deg)';
                                }
                            });
                        }
                    });
                }
            }, 500);
        });
    </script>
    </body>
</html>
