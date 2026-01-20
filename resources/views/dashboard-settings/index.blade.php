@extends('layouts.app')

@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Dashboard Large Settings</h1>
            <p class="text-gray-600 font-medium">Configure default data source and date filter for Dashboard Large</p>
        </div>

        <!-- Settings Form -->
        <div class="bg-white rounded-lg shadow-lg border-2 border-gray-300 p-6">
            <form method="POST" action="{{ route('dashboard-settings.update') }}" class="space-y-6">
                @csrf
                @method('POST')
                
                <!-- Data Source Selection -->
                <div>
                    <label for="data_source" class="block text-sm font-semibold text-gray-700 mb-2">
                        Data Source
                    </label>
                    <select name="data_source" id="data_source" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base">
                        <option value="downtime_erp2" {{ $settings['data_source'] === 'downtime_erp2' ? 'selected' : '' }}>
                            Downtime ERP2
                        </option>
                        <option value="downtime_erp" {{ $settings['data_source'] === 'downtime_erp' ? 'selected' : '' }}>
                            Downtime ERP
                        </option>
                        <option value="downtime" {{ $settings['data_source'] === 'downtime' ? 'selected' : '' }}>
                            Downtime
                        </option>
                    </select>
                    <p class="mt-2 text-sm text-gray-500">
                        Pilih sumber data downtime yang akan ditampilkan di Dashboard Large secara default.
                    </p>
                </div>

                <!-- Month Selection -->
                <div>
                    <label for="month" class="block text-sm font-semibold text-gray-700 mb-2">
                        Default Month
                    </label>
                    <select name="month" id="month" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $settings['month'] == $i ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $i, 1)->locale('id')->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                    <p class="mt-2 text-sm text-gray-500">
                        Pilih bulan default yang akan ditampilkan. Default: Bulan berjalan ({{ \Carbon\Carbon::now()->locale('id')->translatedFormat('F') }}).
                    </p>
                </div>

                <!-- Year Selection -->
                <div>
                    <label for="year" class="block text-sm font-semibold text-gray-700 mb-2">
                        Default Year
                    </label>
                    <select name="year" id="year" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-base">
                        @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                            <option value="{{ $y }}" {{ $settings['year'] == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                    <p class="mt-2 text-sm text-gray-500">
                        Pilih tahun default yang akan ditampilkan. Default: Tahun berjalan ({{ now()->year }}).
                    </p>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Catatan:</strong> Pengaturan ini akan digunakan sebagai default untuk Dashboard Large. 
                                Pengaturan disimpan di profil Anda dan akan digunakan setiap kali Anda membuka Dashboard Large.
                                Anda masih bisa mengubah filter bulan dan tahun secara manual di halaman Dashboard Large jika diperlukan.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('dashboard.large') }}" 
                       class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-[1.02] transition-all duration-300">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>

        <!-- Current Settings Display -->
        <div class="bg-white rounded-lg shadow-lg border-2 border-gray-300 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Current Settings</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500 mb-1">Data Source</div>
                    <div class="text-lg font-bold text-gray-900">
                        {{ ucfirst(str_replace('_', ' ', $settings['data_source'])) }}
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500 mb-1">Default Month</div>
                    <div class="text-lg font-bold text-gray-900">
                        {{ \Carbon\Carbon::create(null, $settings['month'], 1)->locale('id')->translatedFormat('F') }}
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500 mb-1">Default Year</div>
                    <div class="text-lg font-bold text-gray-900">{{ $settings['year'] }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

