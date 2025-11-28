<x-guest-layout>
    <div class="animate-fade-in-up">
        <div class="mb-8 text-center">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Buat Akun Baru</h2>
            <p class="text-gray-600">Daftar untuk mulai menggunakan TPM ERP</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-6">
            @csrf

            <!-- Name -->
            <div class="animate-fade-in-up" style="animation-delay: 0.1s;">
                <x-input-label for="name" :value="__('Name')" class="text-gray-700 font-semibold mb-2" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <x-text-input 
                        id="name" 
                        class="block mt-1 w-full pl-10 input-focus transition-all duration-300" 
                        type="text" 
                        name="name" 
                        :value="old('name')" 
                        required 
                        autofocus 
                        autocomplete="name"
                        placeholder="Nama lengkap"
                    />
                </div>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div class="animate-fade-in-up" style="animation-delay: 0.2s;">
                <x-input-label for="email" :value="__('Email')" class="text-gray-700 font-semibold mb-2" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <x-text-input 
                        id="email" 
                        class="block mt-1 w-full pl-10 input-focus transition-all duration-300" 
                        type="email" 
                        name="email" 
                        :value="old('email')" 
                        required 
                        autocomplete="username"
                        placeholder="nama@email.com"
                    />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="animate-fade-in-up" style="animation-delay: 0.3s;">
                <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-semibold mb-2" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <x-text-input 
                        id="password" 
                        class="block mt-1 w-full pl-10 input-focus transition-all duration-300"
                        type="password"
                        name="password"
                        required 
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                    />
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="animate-fade-in-up" style="animation-delay: 0.4s;">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-gray-700 font-semibold mb-2" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <x-text-input 
                        id="password_confirmation" 
                        class="block mt-1 w-full pl-10 input-focus transition-all duration-300"
                        type="password"
                        name="password_confirmation" 
                        required 
                        autocomplete="new-password"
                        placeholder="Ulangi password"
                    />
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <!-- Submit Button -->
            <div class="animate-fade-in-up" style="animation-delay: 0.5s;">
                <x-primary-button class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3 rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-[1.02]">
                    <span class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        {{ __('Register') }}
                    </span>
                </x-primary-button>
            </div>
        </form>

        <!-- Login Link -->
        <div class="mt-6 text-center animate-fade-in-up" style="animation-delay: 0.6s;">
            <p class="text-sm text-gray-600">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-semibold transition-colors duration-200 hover:underline">
                    Masuk di sini
                </a>
            </p>
        </div>

        <!-- Back to Home -->
        <div class="mt-6 text-center animate-fade-in-up" style="animation-delay: 0.7s;">
            <a href="/" class="text-sm text-gray-500 hover:text-gray-700 transition-colors duration-200 inline-flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke beranda
            </a>
        </div>
    </div>
</x-guest-layout>
