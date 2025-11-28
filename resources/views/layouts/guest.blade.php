<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'TPM ERP') }} - Authentication</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
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
            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(50px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-10px); }
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
            .animate-slide-in-right {
                animation: slideInRight 0.8s ease-out forwards;
            }
            .animate-float {
                animation: float 3s ease-in-out infinite;
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
            .input-focus:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            .card-hover {
                transition: all 0.3s ease;
            }
            .card-hover:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex">
            <!-- Left Side - Branding -->
            <div class="hidden lg:flex lg:w-1/2 gradient-bg text-white p-12 flex-col justify-between relative overflow-hidden">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-20 left-20 w-72 h-72 bg-white rounded-full blur-3xl animate-float"></div>
                    <div class="absolute bottom-20 right-20 w-96 h-96 bg-white rounded-full blur-3xl animate-float" style="animation-delay: 1s;"></div>
                </div>
                
                <div class="relative z-10 animate-fade-in">
                    <a href="/" class="flex items-center mb-8">
                        <img src="{{ asset('images/logo_tpm.png') }}" alt="Logo TPM" class="w-16 h-16 object-contain mr-3">
                        <span class="text-3xl font-bold">TPM ERP</span>
                    </a>
                    <h1 class="text-5xl font-bold mb-4 animate-fade-in-up">
                        Total Productive Maintenance
                    </h1>
                    <p class="text-xl text-blue-100 mb-8 animate-fade-in-up" style="animation-delay: 0.2s;">
                        Sistem manajemen maintenance terintegrasi untuk meningkatkan efisiensi operasional
                    </p>
                </div>
                
                <div class="relative z-10 animate-fade-in-up" style="animation-delay: 0.4s;">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                            <div class="text-3xl mb-2">📊</div>
                            <h3 class="font-semibold mb-1">Analytics</h3>
                            <p class="text-sm text-blue-100">Data-driven insights</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                            <div class="text-3xl mb-2">⚡</div>
                            <h3 class="font-semibold mb-1">Real-time</h3>
                            <p class="text-sm text-blue-100">Live monitoring</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                            <div class="text-3xl mb-2">🔧</div>
                            <h3 class="font-semibold mb-1">Maintenance</h3>
                            <p class="text-sm text-blue-100">Preventive care</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                            <div class="text-3xl mb-2">📈</div>
                            <h3 class="font-semibold mb-1">Efficiency</h3>
                            <p class="text-sm text-blue-100">Optimize operations</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="w-full lg:w-1/2 flex flex-col sm:justify-center items-center p-6 sm:p-12 bg-gradient-to-br from-gray-50 to-blue-50">
                <div class="w-full sm:max-w-md">
                    <!-- Mobile Logo -->
                    <div class="lg:hidden mb-8 text-center animate-fade-in">
                        <a href="/" class="inline-flex items-center justify-center">
                            <img src="{{ asset('images/logo_tpm.png') }}" alt="Logo TPM" class="w-16 h-16 object-contain mr-3">
                            <span class="text-2xl font-bold gradient-text">TPM ERP</span>
                        </a>
                    </div>

                    <div class="bg-white rounded-2xl shadow-2xl p-8 card-hover animate-slide-in-right">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
