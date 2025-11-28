<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'TPM CMMS') }} - Total Productive Maintenance</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
            @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
            <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        .animate-fade-in {
            animation: fadeIn 1s ease-out forwards;
        }
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        .animate-pulse-slow {
            animation: pulse 3s ease-in-out infinite;
        }
        .delay-100 { animation-delay: 0.1s; opacity: 0; }
        .delay-200 { animation-delay: 0.2s; opacity: 0; }
        .delay-300 { animation-delay: 0.3s; opacity: 0; }
        .delay-400 { animation-delay: 0.4s; opacity: 0; }
        .delay-500 { animation-delay: 0.5s; opacity: 0; }
        .delay-600 { animation-delay: 0.6s; opacity: 0; }
        .feature-card {
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-size: 200% 200%;
            animation: gradient 15s ease infinite;
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .gradient-text-gold {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dashboard-preview {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            transform: perspective(1000px) rotateY(-5deg);
            transition: transform 0.3s ease;
        }
        .dashboard-preview:hover {
            transform: perspective(1000px) rotateY(0deg);
        }
        .preview-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 1rem;
            border-radius: 12px;
            color: white;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .preview-card.card-2 {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .preview-card.card-3 {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        .demo-tab {
            padding: 0.75rem 1.5rem;
            border: none;
            background: #f1f5f9;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .demo-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .demo-panel {
            display: none;
        }
        .demo-panel.active {
            display: block;
        }
        .pricing-card {
            transition: all 0.3s ease;
        }
        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .pricing-card.featured {
            border: 3px solid #667eea;
            transform: scale(1.05);
        }
        html {
            scroll-behavior: smooth;
        }
            </style>
    </head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <img src="{{ asset('images/logo_tpm.png') }}" alt="TPM Logo" class="h-10 w-auto mr-3">
                    <span class="text-2xl font-bold gradient-text">TPM CMMS</span>
                </div>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="#home" class="text-gray-700 hover:text-blue-600 font-medium transition">Beranda</a>
                    <a href="#features" class="text-gray-700 hover:text-blue-600 font-medium transition">Fitur</a>
                    <a href="#demo" class="text-gray-700 hover:text-blue-600 font-medium transition">Demo</a>
                    <a href="#benefits" class="text-gray-700 hover:text-blue-600 font-medium transition">Manfaat</a>
                    <a href="#pricing" class="text-gray-700 hover:text-blue-600 font-medium transition">Harga</a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition shadow-md hover:shadow-lg">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600 font-medium transition">
                            Login
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition shadow-md hover:shadow-lg">
                                Register
                            </a>
                        @endif
                    @endauth
                </div>
                <button class="md:hidden text-gray-700" id="mobileMenuBtn">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div class="hidden md:hidden bg-white border-t" id="mobileMenu">
            <div class="px-4 py-4 space-y-3">
                <a href="#home" class="block text-gray-700 hover:text-blue-600 font-medium">Beranda</a>
                <a href="#features" class="block text-gray-700 hover:text-blue-600 font-medium">Fitur</a>
                <a href="#demo" class="block text-gray-700 hover:text-blue-600 font-medium">Demo</a>
                <a href="#benefits" class="block text-gray-700 hover:text-blue-600 font-medium">Manfaat</a>
                <a href="#pricing" class="block text-gray-700 hover:text-blue-600 font-medium">Harga</a>
                @auth
                    <a href="{{ url('/dashboard') }}" class="block bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold text-center">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="block text-gray-700 hover:text-blue-600 font-medium">Login</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="block bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold text-center">Register</a>
                    @endif
                @endauth
            </div>
        </div>
                </nav>

    <!-- Hero Section -->
    <section id="home" class="gradient-bg text-white py-20 min-h-screen flex items-center">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-left animate-fade-in">
                    <h1 class="text-5xl md:text-6xl font-bold mb-6 animate-fade-in-up delay-100">
                        <span class="gradient-text-gold">Sistem Manajemen Maintenance</span><br>
                        <span class="text-4xl md:text-5xl mt-2">Terintegrasi untuk Lini Produksi</span>
                    </h1>
                    <p class="text-xl md:text-2xl mb-8 text-blue-100 animate-fade-in-up delay-200 max-w-3xl mx-auto lg:mx-0">
                        Tingkatkan efisiensi, kurangi downtime, dan optimalkan kinerja mesin produksi dengan sistem CMMS berbasis Total Productive Maintenance (TPM) yang terdepan.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start animate-fade-in-up delay-300 mb-8">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold text-lg hover:bg-gray-100 transition shadow-lg hover:shadow-xl">
                                <i class="fas fa-tachometer-alt mr-2"></i>Masuk ke Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold text-lg hover:bg-gray-100 transition shadow-lg hover:shadow-xl">
                                <i class="fas fa-sign-in-alt mr-2"></i>Masuk
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="bg-blue-700 hover:bg-blue-800 text-white px-8 py-3 rounded-lg font-semibold text-lg transition shadow-lg hover:shadow-xl border-2 border-white">
                                    <i class="fas fa-user-plus mr-2"></i>Daftar Sekarang
                                </a>
            @endif
                        @endauth
                    </div>
                    <div class="grid grid-cols-3 gap-6 animate-fade-in-up delay-400">
                        <div class="text-center">
                            <div class="stat-number text-4xl font-bold gradient-text-gold mb-2" data-target="99.5">0</div>
                            <div class="text-sm text-blue-100">% Uptime</div>
                        </div>
                        <div class="text-center">
                            <div class="stat-number text-4xl font-bold gradient-text-gold mb-2" data-target="45">0</div>
                            <div class="text-sm text-blue-100">% Penghematan</div>
                        </div>
                        <div class="text-center">
                            <div class="stat-number text-4xl font-bold gradient-text-gold mb-2" data-target="500">0</div>
                            <div class="text-sm text-blue-100">+ Perusahaan</div>
                        </div>
                    </div>
                </div>
                <div class="animate-fade-in-up delay-500">
                    <div class="dashboard-preview">
                        <div class="flex justify-end mb-4">
                            <div class="flex gap-2">
                                <span class="w-3 h-3 rounded-full bg-gray-300"></span>
                                <span class="w-3 h-3 rounded-full bg-gray-300"></span>
                                <span class="w-3 h-3 rounded-full bg-gray-300"></span>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3 mb-4">
                            <div class="preview-card">
                                <i class="fas fa-chart-line text-2xl"></i>
                                <div>
                                    <div class="text-xs opacity-90">Total Breakdown</div>
                                    <div class="text-xl font-bold">24</div>
                                </div>
                            </div>
                            <div class="preview-card card-2">
                                <i class="fas fa-clock text-2xl"></i>
                                <div>
                                    <div class="text-xs opacity-90">MTTR</div>
                                    <div class="text-xl font-bold">2.5h</div>
                                </div>
                            </div>
                            <div class="preview-card card-3">
                                <i class="fas fa-check-circle text-2xl"></i>
                                <div>
                                    <div class="text-xs opacity-90">PM Completion</div>
                                    <div class="text-xl font-bold">87%</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4" style="height: 200px; position: relative;">
                            <canvas id="heroChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-fade-in">
                <h2 class="text-4xl font-bold gradient-text mb-4">Fitur Unggulan Sistem</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Solusi lengkap untuk manajemen maintenance dan optimasi produksi
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 shadow-lg animate-fade-in-up delay-100">
                    <div class="bg-blue-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 animate-float">
                        <i class="fas fa-chart-pie text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Dashboard Real-Time</h3>
                    <p class="text-gray-600 mb-4">
                        Monitor kinerja mesin, downtime, dan KPI maintenance secara real-time dengan visualisasi interaktif.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><i class="fas fa-check text-green-600 mr-2"></i> Analytics & Reporting</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i> Custom Dashboard</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i> Data Visualization</li>
                    </ul>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-6 shadow-lg animate-fade-in-up delay-200">
                    <div class="bg-purple-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 animate-float" style="animation-delay: 0.2s;">
                        <i class="fas fa-calendar-check text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Preventive Maintenance</h3>
                    <p class="text-gray-600 mb-4">
                        Sistem penjadwalan maintenance otomatis dengan kategori AM, PM, dan Predictive Maintenance.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><i class="fas fa-check text-green-600 mr-2"></i> Automated Scheduling</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i> Maintenance Points</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i> Execution Tracking</li>
                    </ul>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card bg-gradient-to-br from-green-50 to-teal-50 rounded-xl p-6 shadow-lg animate-fade-in-up delay-300">
                    <div class="bg-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 animate-float" style="animation-delay: 0.4s;">
                        <i class="fas fa-exclamation-triangle text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Downtime Management</h3>
                    <p class="text-gray-600 mb-4">
                        Tracking dan analisis downtime dengan detail breakdown, problem identification, dan action tracking.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><i class="fas fa-check text-green-600 mr-2"></i> Breakdown Tracking</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i> Root Cause Analysis</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i> Performance Metrics</li>
                    </ul>
                </div>

                <!-- Feature 4 -->
                <div class="feature-card bg-gradient-to-br from-orange-50 to-red-50 rounded-xl p-6 shadow-lg animate-fade-in-up delay-400">
                    <div class="bg-orange-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 animate-float" style="animation-delay: 0.6s;">
                        <i class="fas fa-tachometer-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">MTTR & MTBF Analysis</h3>
                    <p class="text-gray-600 mb-4">
                        Kalkulasi dan monitoring Mean Time To Repair (MTTR) dan Mean Time Between Failures (MTBF).
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><i class="fas fa-check text-green-600 mr-2"></i> Performance Metrics</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i> Trend Analysis</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i> Benchmarking</li>
                    </ul>
                </div>

                <!-- Feature 5 -->
                <div class="feature-card bg-gradient-to-br from-cyan-50 to-blue-50 rounded-xl p-6 shadow-lg animate-fade-in-up delay-500">
                    <div class="bg-cyan-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 animate-float" style="animation-delay: 0.8s;">
                        <i class="fas fa-users-cog text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Skill Matrix</h3>
                    <p class="text-gray-600 mb-4">
                        Manajemen kompetensi teknisi dengan skill matrix berdasarkan history perbaikan mesin.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><i class="fas fa-check text-green-600 mr-2"></i> Competency Tracking</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i> Performance Analysis</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i> Training Management</li>
                    </ul>
                </div>

                <!-- Feature 6 -->
                <div class="feature-card bg-gradient-to-br from-yellow-50 to-amber-50 rounded-xl p-6 shadow-lg animate-fade-in-up delay-600">
                    <div class="bg-yellow-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 animate-float" style="animation-delay: 1s;">
                        <i class="fas fa-map-marked-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Asset Location Management</h3>
                    <p class="text-gray-600 mb-4">
                        Tracking lokasi mesin dan mutasi asset dengan detail plant, process, line, dan room.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><i class="fas fa-check text-green-600 mr-2"></i> Location Tracking</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i> Asset Migration</li>
                        <li><i class="fas fa-check text-green-600 mr-2"></i> Multi-Location Support</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Demo Section -->
    <section id="demo" class="py-20 bg-gradient-to-br from-gray-50 to-blue-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-fade-in">
                <h2 class="text-4xl font-bold gradient-text mb-4">Coba Sistem Secara Interaktif</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Jelajahi fitur-fitur utama sistem melalui simulasi interaktif
                </p>
            </div>
            <div class="bg-white rounded-2xl shadow-2xl p-8">
                <div class="flex flex-wrap gap-3 mb-8">
                    <button class="demo-tab active" data-tab="dashboard">
                        <i class="fas fa-chart-line mr-2"></i> Dashboard
                    </button>
                    <button class="demo-tab" data-tab="pm">
                        <i class="fas fa-calendar-alt mr-2"></i> Preventive Maintenance
                    </button>
                    <button class="demo-tab" data-tab="downtime">
                        <i class="fas fa-exclamation-circle mr-2"></i> Downtime Tracking
                    </button>
                    <button class="demo-tab" data-tab="reports">
                        <i class="fas fa-file-chart-line mr-2"></i> Reports & Analytics
                    </button>
                    <button class="demo-tab" data-tab="skill-matrix">
                        <i class="fas fa-users-cog mr-2"></i> Skill Matrix
                    </button>
                </div>
                <div class="demo-content">
                    <!-- Dashboard Demo -->
                    <div class="demo-panel active" id="demo-dashboard">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                                <div class="flex items-center justify-between mb-2">
                                    <i class="fas fa-tools text-2xl"></i>
                                </div>
                                <div class="text-3xl font-bold">156</div>
                                <div class="text-sm opacity-90">Total Mesin</div>
                            </div>
                            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-4 text-white">
                                <div class="flex items-center justify-between mb-2">
                                    <i class="fas fa-check-circle text-2xl"></i>
                                </div>
                                <div class="text-3xl font-bold">98.2%</div>
                                <div class="text-sm opacity-90">Uptime</div>
                            </div>
                            <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg p-4 text-white">
                                <div class="flex items-center justify-between mb-2">
                                    <i class="fas fa-clock text-2xl"></i>
                                </div>
                                <div class="text-3xl font-bold">2.3h</div>
                                <div class="text-sm opacity-90">Avg MTTR</div>
                            </div>
                            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg p-4 text-white">
                                <div class="flex items-center justify-between mb-2">
                                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                                </div>
                                <div class="text-3xl font-bold">12</div>
                                <div class="text-sm opacity-90">Breakdown Bulan Ini</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-bold mb-4">Downtime Trend</h4>
                                <div style="height: 250px; position: relative;">
                                    <canvas id="demoChart1"></canvas>
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-bold mb-4">Top 5 Machines (Downtime)</h4>
                                <div style="height: 250px; position: relative;">
                                    <canvas id="demoChart2"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PM Demo -->
                    <div class="demo-panel" id="demo-pm">
                        <div class="space-y-4">
                            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                                <div class="flex justify-between items-center mb-2">
                                    <div>
                                        <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-bold">Pending</span>
                                        <span class="text-sm text-gray-600 ml-4">15 Nov 2025</span>
                                    </div>
                                </div>
                                <h4 class="font-bold text-lg">Hydraulic Press Automatic</h4>
                                <p class="text-sm text-gray-600 mb-2">ID: HPA-001</p>
                                <div class="bg-gray-200 rounded-full h-2 mb-2">
                                    <div class="bg-yellow-500 h-2 rounded-full" style="width: 0%"></div>
                                </div>
                                <div class="flex flex-wrap gap-2 text-sm">
                                    <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i> Safety Check</span>
                                    <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i> Display Check</span>
                                    <span class="text-gray-600"><i class="fas fa-clock mr-1"></i> Lubrication</span>
                                </div>
                            </div>
                            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                                <div class="flex justify-between items-center mb-2">
                                    <div>
                                        <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-bold">In Progress</span>
                                        <span class="text-sm text-gray-600 ml-4">16 Nov 2025</span>
                                    </div>
                                </div>
                                <h4 class="font-bold text-lg">Conveyor Belt System</h4>
                                <p class="text-sm text-gray-600 mb-2">ID: CBS-002</p>
                                <div class="bg-gray-200 rounded-full h-2 mb-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: 65%"></div>
                                </div>
                                <div class="flex flex-wrap gap-2 text-sm">
                                    <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i> Belt Inspection</span>
                                    <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i> Motor Check</span>
                                    <span class="text-gray-600"><i class="fas fa-clock mr-1"></i> Alignment</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Downtime Demo -->
                    <div class="demo-panel" id="demo-downtime">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Mesin</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Problem</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">18 Nov 2025</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">HPA-001</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">Hydraulic Leak</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">2.5 jam</td>
                                        <td class="px-6 py-4 whitespace-nowrap"><span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-bold">Resolved</span></td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">17 Nov 2025</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">CBS-002</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">Belt Misalignment</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">1.2 jam</td>
                                        <td class="px-6 py-4 whitespace-nowrap"><span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-bold">Resolved</span></td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">16 Nov 2025</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold">PKG-003</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">Sensor Failure</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">3.8 jam</td>
                                        <td class="px-6 py-4 whitespace-nowrap"><span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-bold">Resolved</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Reports Demo -->
                    <div class="demo-panel" id="demo-reports">
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-xl font-bold mb-6">Performance Report - November 2025</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div style="height: 300px; position: relative;">
                                    <canvas id="reportChart1"></canvas>
                                </div>
                                <div style="height: 300px; position: relative;">
                                    <canvas id="reportChart2"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Skill Matrix Demo -->
                    <div class="demo-panel" id="demo-skill-matrix">
                        <div class="space-y-6">
                            <!-- Summary Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-4 text-white">
                                    <div class="flex items-center justify-between mb-2">
                                        <i class="fas fa-users text-2xl"></i>
                                    </div>
                                    <div class="text-3xl font-bold">24</div>
                                    <div class="text-sm opacity-90">Total Teknisi</div>
                                </div>
                                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-4 text-white">
                                    <div class="flex items-center justify-between mb-2">
                                        <i class="fas fa-star text-2xl"></i>
                                    </div>
                                    <div class="text-3xl font-bold">18</div>
                                    <div class="text-sm opacity-90">Expert Level</div>
                                </div>
                                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg p-4 text-white">
                                    <div class="flex items-center justify-between mb-2">
                                        <i class="fas fa-graduation-cap text-2xl"></i>
                                    </div>
                                    <div class="text-3xl font-bold">6</div>
                                    <div class="text-sm opacity-90">Need Training</div>
                                </div>
                                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-4 text-white">
                                    <div class="flex items-center justify-between mb-2">
                                        <i class="fas fa-chart-line text-2xl"></i>
                                    </div>
                                    <div class="text-3xl font-bold">87%</div>
                                    <div class="text-sm opacity-90">Avg Competency</div>
                                </div>
                            </div>

                            <!-- Skill Matrix Table -->
                            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                                <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                                    <h3 class="text-xl font-bold">Skill Matrix Overview</h3>
                                    <p class="text-sm opacity-90 mt-1">Kompetensi teknisi berdasarkan jenis mesin</p>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teknisi</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Hydraulic</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Conveyor</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Packaging</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Welding</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Assembly</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Score</th>
                                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr class="hover:bg-gray-50 transition">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                                                                AB
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">Ahmad Budiman</div>
                                                            <div class="text-sm text-gray-500">Senior Technician</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                        <i class="fas fa-star mr-1"></i> Expert
                                </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                        <i class="fas fa-star mr-1"></i> Expert
                            </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                                        <i class="fas fa-check-circle mr-1"></i> Advanced
                            </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                        <i class="fas fa-star mr-1"></i> Expert
                                </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                                        <i class="fas fa-check-circle mr-1"></i> Advanced
                            </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <div class="text-sm font-bold text-gray-900">92%</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                        Excellent
                            </span>
                                                </td>
                                            </tr>
                                            <tr class="hover:bg-gray-50 transition">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <div class="h-10 w-10 rounded-full bg-purple-500 flex items-center justify-center text-white font-bold">
                                                                CS
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">Cahyo Santoso</div>
                                                            <div class="text-sm text-gray-500">Technician</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                                        <i class="fas fa-check-circle mr-1"></i> Advanced
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                        <i class="fas fa-star mr-1"></i> Expert
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-circle mr-1"></i> Intermediate
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                                        <i class="fas fa-check-circle mr-1"></i> Advanced
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-circle mr-1"></i> Intermediate
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <div class="text-sm font-bold text-gray-900">78%</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                                        Good
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr class="hover:bg-gray-50 transition">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <div class="h-10 w-10 rounded-full bg-green-500 flex items-center justify-center text-white font-bold">
                                                                DW
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">Budi Darmaji</div>
                                                            <div class="text-sm text-gray-500">Technician</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-circle mr-1"></i> Intermediate
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                                        <i class="fas fa-check-circle mr-1"></i> Advanced
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                        <i class="fas fa-star mr-1"></i> Expert
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-circle mr-1"></i> Intermediate
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                        <i class="fas fa-star mr-1"></i> Expert
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <div class="text-sm font-bold text-gray-900">82%</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                                        Good
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr class="hover:bg-gray-50 transition">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <div class="h-10 w-10 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold">
                                                                EP
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">Eko Prasetyo</div>
                                                            <div class="text-sm text-gray-500">Junior Technician</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                                        <i class="fas fa-dot-circle mr-1"></i> Beginner
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-circle mr-1"></i> Intermediate
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-circle mr-1"></i> Intermediate
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                                        <i class="fas fa-dot-circle mr-1"></i> Beginner
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-circle mr-1"></i> Intermediate
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <div class="text-sm font-bold text-gray-900">58%</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i> Needs Training
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr class="hover:bg-gray-50 transition">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold">
                                                                FR
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">Fajar Rahman</div>
                                                            <div class="text-sm text-gray-500">Technician</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                        <i class="fas fa-star mr-1"></i> Expert
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                                        <i class="fas fa-check-circle mr-1"></i> Advanced
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                                        <i class="fas fa-check-circle mr-1"></i> Advanced
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                        <i class="fas fa-star mr-1"></i> Expert
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                        <i class="fas fa-star mr-1"></i> Expert
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <div class="text-sm font-bold text-gray-900">95%</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                        Excellent
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Training Recommendations -->
                            <!-- <div class="bg-white rounded-lg shadow-md p-6">
                                <h3 class="text-xl font-bold mb-4 flex items-center">
                                    <i class="fas fa-graduation-cap text-blue-600 mr-2"></i>
                                    Training Recommendations
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="border-l-4 border-yellow-500 bg-yellow-50 p-4 rounded">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-bold text-gray-900">Eko Prasetyo</h4>
                                            <span class="text-xs text-gray-600">Priority: High</span>
                                        </div>
                                        <p class="text-sm text-gray-700 mb-2">Recommended Training:</p>
                                        <ul class="text-sm text-gray-600 space-y-1">
                                            <li><i class="fas fa-check-circle text-yellow-600 mr-2"></i> Hydraulic System Fundamentals</li>
                                            <li><i class="fas fa-check-circle text-yellow-600 mr-2"></i> Welding Machine Operation</li>
                    </ul>
                                    </div>
                                    <div class="border-l-4 border-blue-500 bg-blue-50 p-4 rounded">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-bold text-gray-900">Cahyo Santoso</h4>
                                            <span class="text-xs text-gray-600">Priority: Medium</span>
                                        </div>
                                        <p class="text-sm text-gray-700 mb-2">Recommended Training:</p>
                                        <ul class="text-sm text-gray-600 space-y-1">
                                            <li><i class="fas fa-check-circle text-blue-600 mr-2"></i> Packaging Machine Advanced</li>
                                            <li><i class="fas fa-check-circle text-blue-600 mr-2"></i> Assembly Line Optimization</li>
                    </ul>
                </div>
                                </div>
                            </div> -->

                            <!-- Performance Metrics -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-bold text-gray-900">Top Performer</h4>
                                        <i class="fas fa-trophy text-yellow-500 text-xl"></i>
                                    </div>
                                    <p class="text-2xl font-bold text-blue-600 mb-1">Fajar Rahman</p>
                                    <p class="text-sm text-gray-600">95% Avg Competency</p>
                                </div>
                                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border border-green-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-bold text-gray-900">Most Versatile</h4>
                                        <i class="fas fa-star text-green-500 text-xl"></i>
                                    </div>
                                    <p class="text-2xl font-bold text-green-600 mb-1">Ahmad Budiman</p>
                                    <p class="text-sm text-gray-600">Expert in 3+ Machine Types</p>
                                </div>
                                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-bold text-gray-900">Rising Star</h4>
                                        <i class="fas fa-arrow-up text-purple-500 text-xl"></i>
                                    </div>
                                    <p class="text-2xl font-bold text-purple-600 mb-1">Budi Darmaji</p>
                                    <p class="text-sm text-gray-600">+15% Improvement This Month</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-fade-in">
                <h2 class="text-4xl font-bold gradient-text mb-4">Manfaat Implementasi Sistem</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Dapatkan ROI yang signifikan dalam 6 bulan pertama
                </p>
                </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg p-6 shadow-md text-center animate-fade-in-up delay-100 border-l-4 border-blue-500">
                    <div class="text-4xl mb-4 text-blue-600"><i class="fas fa-arrow-up"></i></div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">99.5% Uptime</h3>
                    <p class="text-gray-600 text-sm">Meningkatkan availability mesin produksi hingga 99.5% dengan predictive maintenance</p>
        </div>

                <div class="bg-white rounded-lg p-6 shadow-md text-center animate-fade-in-up delay-200 border-l-4 border-green-500">
                    <div class="text-4xl mb-4 text-green-600"><i class="fas fa-dollar-sign"></i></div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">45% Cost Reduction</h3>
                    <p class="text-gray-600 text-sm">Mengurangi biaya maintenance hingga 45% melalui optimasi scheduling dan inventory</p>
                </div>
                
                <div class="bg-white rounded-lg p-6 shadow-md text-center animate-fade-in-up delay-300 border-l-4 border-yellow-500">
                    <div class="text-4xl mb-4 text-yellow-600"><i class="fas fa-clock"></i></div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">60% Faster Response</h3>
                    <p class="text-gray-600 text-sm">Mempercepat waktu response terhadap breakdown dengan sistem tracking real-time</p>
                </div>
                
                <div class="bg-white rounded-lg p-6 shadow-md text-center animate-fade-in-up delay-400 border-l-4 border-purple-500">
                    <div class="text-4xl mb-4 text-purple-600"><i class="fas fa-chart-bar"></i></div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Data-Driven Decisions</h3>
                    <p class="text-gray-600 text-sm">Mendukung keputusan berbasis data dengan analytics dan reporting yang komprehensif</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 bg-gradient-to-br from-gray-50 to-blue-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-fade-in">
                <h2 class="text-4xl font-bold gradient-text mb-4">Paket Harga</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Pilih paket yang sesuai dengan kebutuhan perusahaan Anda
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="pricing-card bg-white rounded-xl p-8 shadow-lg">
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Starter</h3>
                        <div class="mb-4">
                            <span class="text-2xl font-bold gradient-text">Rp</span>
                            <span class="text-5xl font-bold gradient-text">25</span>
                            <span class="text-xl text-gray-600">jt/bulan</span>
                        </div>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> Hingga 50 Mesin</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> Dashboard Basic</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> Preventive Maintenance</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> Downtime Tracking</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> Support Email</li>
                    </ul>
                    <a href="#contact" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-3 rounded-lg font-semibold transition">
                        Mulai Sekarang
                    </a>
                </div>
                <div class="pricing-card featured bg-white rounded-xl p-8 shadow-lg relative">
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-1 rounded-full text-sm font-bold">
                        Paling Populer
                    </div>
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Professional</h3>
                        <div class="mb-4">
                            <span class="text-2xl font-bold gradient-text">Rp</span>
                            <span class="text-5xl font-bold gradient-text">50</span>
                            <span class="text-xl text-gray-600">jt/bulan</span>
                        </div>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> Hingga 200 Mesin</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> Dashboard Advanced</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> Full PM System</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> MTTR/MTBF Analysis</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> Skill Matrix</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> Custom Reports</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> Priority Support</li>
                    </ul>
                    <a href="#contact" class="block w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white text-center py-3 rounded-lg font-semibold transition">
                        Mulai Sekarang
                    </a>
                </div>
                <div class="pricing-card bg-white rounded-xl p-8 shadow-lg">
                    <div class="text-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Enterprise</h3>
                        <div class="mb-4">
                            <span class="text-5xl font-bold gradient-text">Custom</span>
                        </div>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> Unlimited Mesin</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> Custom Dashboard</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> Multi-Location</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> API Integration</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> Dedicated Support</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> On-Site Training</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-600 mr-3"></i> Custom Development</li>
                    </ul>
                    <a href="#contact" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-3 rounded-lg font-semibold transition">
                        Hubungi Sales
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <div>
                    <h2 class="text-4xl font-bold gradient-text mb-4">Siap Meningkatkan Efisiensi Produksi?</h2>
                    <p class="text-xl text-gray-600 mb-8">Hubungi kami hari ini untuk konsultasi gratis dan demo sistem</p>
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="bg-blue-100 p-3 rounded-lg mr-4">
                                <i class="fas fa-phone text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 mb-1">Telepon</h4>
                                <p class="text-gray-600">+62 818 0512 0187</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-blue-100 p-3 rounded-lg mr-4">
                                <i class="fas fa-envelope text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 mb-1">Email</h4>
                                <p class="text-gray-600">wahid@tpmcmms.com</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="bg-blue-100 p-3 rounded-lg mr-4">
                                <i class="fas fa-map-marker-alt text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900 mb-1">Alamat</h4>
                                <p class="text-gray-600">Serpong, Tangerang Selatan, Indonesia</p>
                            </div>
                        </div>
                    </div>
                    <!-- WhatsApp Button -->
                    <div class="mt-8">
                        <a href="https://wa.me/6281805120187?text=Halo%2C%20saya%20tertarik%20dengan%20TPM%20ERP%20dan%20ingin%20konsultasi" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="inline-flex items-center justify-center w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-6 py-4 rounded-lg font-semibold text-lg transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                            <i class="fab fa-whatsapp text-2xl mr-3"></i>
                            <span>Chat via WhatsApp</span>
                        </a>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-8">
                    <form class="space-y-4" id="contactForm">
                        <div>
                            <input type="text" placeholder="Nama Lengkap" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <input type="email" placeholder="Email Perusahaan" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <input type="tel" placeholder="Nomor Telepon" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <input type="text" placeholder="Nama Perusahaan" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <select required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Pilih Paket</option>
                                <option value="starter">Starter</option>
                                <option value="professional">Professional</option>
                                <option value="enterprise">Enterprise</option>
                            </select>
                        </div>
                        <div>
                            <textarea placeholder="Pesan" rows="4" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white py-3 rounded-lg font-semibold transition shadow-lg hover:shadow-xl">
                            <i class="fas fa-paper-plane mr-2"></i>Kirim Pesan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-blue-600 to-purple-600 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold mb-6 animate-fade-in-up delay-100">
                Siap untuk Meningkatkan Efisiensi Maintenance?
            </h2>
            <p class="text-xl mb-8 text-blue-100 animate-fade-in-up delay-200">
                Mulai gunakan TPM ERP sekarang dan rasakan perbedaannya
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center animate-fade-in-up delay-300">
                @auth
                    <a href="{{ url('/dashboard') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold text-lg hover:bg-gray-100 transition shadow-lg hover:shadow-xl">
                        Masuk ke Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold text-lg hover:bg-gray-100 transition shadow-lg hover:shadow-xl">
                        Login Sekarang
                    </a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="bg-blue-700 hover:bg-blue-800 text-white px-8 py-3 rounded-lg font-semibold text-lg transition shadow-lg hover:shadow-xl border-2 border-white">
                            Daftar Gratis
                        </a>
        @endif
                @endauth
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <img src="{{ asset('images/logo_tpm.png') }}" alt="TPM Logo" class="h-8 w-auto mr-2">
                        <span class="text-xl font-bold">TPM CMMS</span>
                    </div>
                    <p class="text-gray-400">
                        Sistem manajemen maintenance terintegrasi untuk meningkatkan produktivitas dan efisiensi operasional.
                    </p>
                </div>
                
                <div>
                    <h3 class="text-lg font-bold mb-4">Produk</h3>
                    <ul class="space-y-2">
                        <li><a href="#features" class="text-gray-400 hover:text-white transition">Fitur</a></li>
                        <li><a href="#demo" class="text-gray-400 hover:text-white transition">Demo</a></li>
                        <li><a href="#pricing" class="text-gray-400 hover:text-white transition">Harga</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-bold mb-4">Perusahaan</h3>
                    <ul class="space-y-2">
                        <li><a href="#contact" class="text-gray-400 hover:text-white transition">Kontak</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Tentang Kami</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Karir</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-bold mb-4">Ikuti Kami</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-linkedin text-2xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-facebook text-2xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-twitter text-2xl"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition"><i class="fab fa-instagram text-2xl"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} TPM CMMS. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile Menu Toggle
            document.getElementById('mobileMenuBtn')?.addEventListener('click', () => {
                document.getElementById('mobileMenu')?.classList.toggle('hidden');
            });

            // Smooth Scroll
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        document.getElementById('mobileMenu')?.classList.add('hidden');
                    }
                });
            });

            // Navbar Scroll Effect
            window.addEventListener('scroll', () => {
                const navbar = document.getElementById('navbar');
                if (window.scrollY > 50) {
                    navbar.classList.add('shadow-xl');
                } else {
                    navbar.classList.remove('shadow-xl');
                }
            });

            // Stats Counter Animation
            const animateCounter = (element, target, duration = 2000) => {
                let start = 0;
                const increment = target / (duration / 16);
                const timer = setInterval(() => {
                    start += increment;
                    if (start >= target) {
                        element.textContent = target % 1 === 0 ? target : target.toFixed(1);
                        clearInterval(timer);
                    } else {
                        element.textContent = Math.floor(start);
                    }
                }, 16);
            };

            const observeStats = () => {
                const stats = document.querySelectorAll('.stat-number');
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const target = parseFloat(entry.target.getAttribute('data-target'));
                            animateCounter(entry.target, target);
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.5 });
                stats.forEach(stat => observer.observe(stat));
            };
            observeStats();

            // Hero Chart
            const initHeroChart = () => {
                const ctx = document.getElementById('heroChart');
                if (!ctx) return;
                
                // Destroy existing chart if any
                if (window.heroChartInstance) {
                    window.heroChartInstance.destroy();
                }
                
                window.heroChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
                        datasets: [{
                            label: 'Downtime (jam)',
                            data: [2, 1.5, 3, 2.5, 1.8, 2.2],
                            borderColor: 'rgb(99, 102, 241)',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'rgb(99, 102, 241)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { 
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 10,
                                titleFont: { size: 14, weight: 'bold' },
                                bodyFont: { size: 13 }
                            }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                grid: { display: true, color: 'rgba(0, 0, 0, 0.05)' },
                                ticks: {
                                    stepSize: 0.5,
                                    callback: function(value) {
                                        return value + ' jam';
                                    }
                                }
                            },
                            x: { 
                                grid: { display: false },
                                ticks: {
                                    font: { size: 11 }
                                }
                            }
                        }
                    }
                });
            };
            
            // Initialize hero chart after a small delay to ensure container is rendered
            setTimeout(() => {
                initHeroChart();
            }, 100);

        // Demo Tabs
        let demoChart1Instance = null;
        let demoChart2Instance = null;
        let reportChart1Instance = null;
        let reportChart2Instance = null;

        const initDemoTabs = () => {
            const tabs = document.querySelectorAll('.demo-tab');
            const panels = document.querySelectorAll('.demo-panel');
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const targetTab = tab.getAttribute('data-tab');
                    tabs.forEach(t => t.classList.remove('active'));
                    panels.forEach(p => p.classList.remove('active'));
                    tab.classList.add('active');
                    document.getElementById(`demo-${targetTab}`)?.classList.add('active');
                    if (targetTab === 'dashboard') {
                        setTimeout(() => initDemoCharts(), 100);
                    } else if (targetTab === 'reports') {
                        setTimeout(() => initReportCharts(), 100);
                    }
                });
            });
        };

        const initDemoCharts = () => {
            // Destroy existing charts
            if (demoChart1Instance) {
                demoChart1Instance.destroy();
                demoChart1Instance = null;
            }
            if (demoChart2Instance) {
                demoChart2Instance.destroy();
                demoChart2Instance = null;
            }

            // Wait a bit for panel to be visible
            setTimeout(() => {
                const ctx1 = document.getElementById('demoChart1');
                if (ctx1) {
                    const parent = ctx1.parentElement;
                    if (parent && parent.offsetHeight > 0) {
                        demoChart1Instance = new Chart(ctx1, {
                            type: 'line',
                            data: {
                                labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'],
                                datasets: [{
                                    label: 'Downtime (jam)',
                                    data: [8, 12, 6, 10],
                                    borderColor: 'rgb(99, 102, 241)',
                                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 5,
                                    pointHoverRadius: 7,
                                    pointBackgroundColor: 'rgb(99, 102, 241)',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { 
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                        padding: 10
                                    }
                                },
                                scales: {
                                    y: { 
                                        beginAtZero: true,
                                        grid: { display: true, color: 'rgba(0, 0, 0, 0.05)' },
                                        ticks: {
                                            stepSize: 2,
                                            callback: function(value) {
                                                return value + ' jam';
                                            }
                                        }
                                    },
                                    x: { 
                                        grid: { display: false },
                                        ticks: { font: { size: 11 } }
                                    }
                                }
                            }
                        });
                    }
                }

                const ctx2 = document.getElementById('demoChart2');
                if (ctx2) {
                    const parent = ctx2.parentElement;
                    if (parent && parent.offsetHeight > 0) {
                        demoChart2Instance = new Chart(ctx2, {
                            type: 'pie',
                            data: {
                                labels: ['HPA-001', 'CBS-002', 'PKG-003', 'WLD-004', 'ASM-005'],
                                datasets: [{
                                    data: [30, 25, 20, 15, 10],
                                    backgroundColor: [
                                        'rgb(99, 102, 241)', 
                                        'rgb(16, 185, 129)', 
                                        'rgb(251, 191, 36)',
                                        'rgb(239, 68, 68)', 
                                        'rgb(139, 92, 246)'
                                    ],
                                    borderWidth: 2,
                                    borderColor: '#fff'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { 
                                    legend: { 
                                        position: 'bottom',
                                        labels: {
                                            padding: 15,
                                            font: { size: 11 },
                                            usePointStyle: true
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                        padding: 10,
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || '';
                                                const value = context.parsed || 0;
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const percentage = ((value / total) * 100).toFixed(1);
                                                return label + ': ' + value + ' jam (' + percentage + '%)';
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                }
            }, 150);
        };

        const initReportCharts = () => {
            // Destroy existing charts
            if (reportChart1Instance) {
                reportChart1Instance.destroy();
                reportChart1Instance = null;
            }
            if (reportChart2Instance) {
                reportChart2Instance.destroy();
                reportChart2Instance = null;
            }

            // Wait a bit for panel to be visible
            setTimeout(() => {
                const ctx1 = document.getElementById('reportChart1');
                if (ctx1) {
                    const parent = ctx1.parentElement;
                    if (parent && parent.offsetHeight > 0) {
                        reportChart1Instance = new Chart(ctx1, {
                            type: 'bar',
                            data: {
                                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                                datasets: [{
                                    label: 'MTTR (jam)',
                                    data: [3.2, 2.8, 2.5, 2.3, 2.1, 2.0],
                                    backgroundColor: 'rgba(99, 102, 241, 0.8)',
                                    borderRadius: 8,
                                    borderSkipped: false
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { 
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                        padding: 10,
                                        callbacks: {
                                            label: function(context) {
                                                return 'MTTR: ' + context.parsed.y + ' jam';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: { 
                                        beginAtZero: true,
                                        grid: { display: true, color: 'rgba(0, 0, 0, 0.05)' },
                                        ticks: {
                                            stepSize: 0.5,
                                            callback: function(value) {
                                                return value + ' jam';
                                            }
                                        }
                                    },
                                    x: {
                                        grid: { display: false },
                                        ticks: { font: { size: 11 } }
                                    }
                                }
                            }
                        });
                    }
                }

                const ctx2 = document.getElementById('reportChart2');
                if (ctx2) {
                    const parent = ctx2.parentElement;
                    if (parent && parent.offsetHeight > 0) {
                        reportChart2Instance = new Chart(ctx2, {
                            type: 'line',
                            data: {
                                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                                datasets: [{
                                    label: 'Uptime %',
                                    data: [96.5, 97.2, 97.8, 98.1, 98.5, 98.8],
                                    borderColor: 'rgb(16, 185, 129)',
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 5,
                                    pointHoverRadius: 7,
                                    pointBackgroundColor: 'rgb(16, 185, 129)',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { 
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                        padding: 10,
                                        callbacks: {
                                            label: function(context) {
                                                return 'Uptime: ' + context.parsed.y + '%';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: { 
                                        beginAtZero: false, 
                                        min: 95, 
                                        max: 100,
                                        grid: { display: true, color: 'rgba(0, 0, 0, 0.05)' },
                                        ticks: {
                                            stepSize: 1,
                                            callback: function(value) {
                                                return value + '%';
                                            }
                                        }
                                    },
                                    x: {
                                        grid: { display: false },
                                        ticks: { font: { size: 11 } }
                                    }
                                }
                            }
                        });
                    }
                }
            }, 150);
        };

        initDemoTabs();
        
        // Initialize demo charts only if dashboard tab is active
        const dashboardPanel = document.getElementById('demo-dashboard');
        if (dashboardPanel && dashboardPanel.classList.contains('active')) {
            initDemoCharts();
        }

        // Contact Form
        document.getElementById('contactForm')?.addEventListener('submit', (e) => {
            e.preventDefault();
            alert('Terima kasih! Pesan Anda telah dikirim. Tim kami akan menghubungi Anda segera.');
            e.target.reset();
        });
        });
    </script>
    </body>
</html>
