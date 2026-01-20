@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-6xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex-1">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-2">Mutasi Mesin Massal</h1>
                <p class="text-xs sm:text-sm text-gray-600">Scan mesin di lapangan (aktual) menggunakan kamera handphone untuk mutasi massal</p>
            </div>
            <a href="{{ route('mutasi.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded shadow transition text-sm sm:text-base whitespace-nowrap w-full sm:w-auto text-center">
                Back to List
            </a>
        </div>
        
        <!-- Mode Selection Tabs -->
        <div class="mb-6 border-b border-gray-200 overflow-x-auto">
            <nav class="flex space-x-4 sm:space-x-8 min-w-max">
                <button type="button" id="tab_mode_individual" class="py-3 sm:py-4 px-2 sm:px-1 border-b-2 border-blue-600 font-medium text-xs sm:text-sm text-blue-600 transition whitespace-nowrap">
                    <span class="hidden sm:inline">Mode Individual</span>
                    <span class="sm:hidden">Individual</span>
                </button>
                <button type="button" id="tab_mode_bulk" class="py-3 sm:py-4 px-2 sm:px-1 border-b-2 border-transparent font-medium text-xs sm:text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 transition whitespace-nowrap">
                    <span class="hidden sm:inline">Mode Massal (Lokasi Sama)</span>
                    <span class="sm:hidden">Massal</span>
                </button>
            </nav>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        <!-- Mode Individual (Existing) -->
        <div id="mode_individual" class="mode-section">
        <!-- Scanner Section -->
        <div class="mb-6 bg-gray-50 rounded-lg p-4 sm:p-6 border border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800">Scanner Mesin</h3>
                <button type="button" id="btn_open_scanner" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center justify-center gap-2 w-full sm:w-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    <span class="hidden sm:inline">Buka Scanner</span>
                    <span class="sm:hidden">Scanner</span>
                </button>
            </div>
            
            <!-- Manual Input -->
            <div class="mb-4">
                <label for="manual_machine_id" class="block text-sm font-semibold text-gray-700 mb-2">
                    Atau Input Manual ID Machine
                </label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" 
                           id="manual_machine_id" 
                           placeholder="Masukkan ID Machine (contoh: AA-00508)"
                           class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-sm sm:text-base focus:ring-2 focus:ring-green-500 focus:border-green-500 transition">
                    <button type="button" id="btn_manual_add" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition w-full sm:w-auto whitespace-nowrap">
                        Tambah
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Scanned Machines List -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800">
                    Daftar Mesin yang Di-scan (<span id="scanned_count">0</span>)
                </h3>
                <button type="button" id="btn_clear_all" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-3 rounded shadow transition text-sm w-full sm:w-auto">
                    Clear All
                </button>
            </div>
            
            <div id="scanned_machines_list" class="space-y-3 min-h-[200px] overflow-x-auto">
                <div class="text-center text-gray-500 py-8 border-2 border-dashed border-gray-300 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    <p>Belum ada mesin yang di-scan. Gunakan scanner atau input manual untuk menambahkan mesin.</p>
                </div>
            </div>
        </div>
        
        <!-- Bulk Save Form -->
        <div id="bulk_save_section" class="hidden">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 sm:p-6 mb-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Informasi Mutasi Massal</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="mutasi_date" class="block text-sm font-semibold text-gray-700 mb-2">
                            Tanggal Mutasi <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="mutasi_date" 
                               value="{{ date('Y-m-d') }}"
                               required
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label for="mutasi_reason" class="block text-sm font-semibold text-gray-700 mb-2">
                            Alasan Mutasi
                        </label>
                        <input type="text" 
                               id="mutasi_reason" 
                               placeholder="Contoh: Relokasi mesin ke area baru"
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    </div>
                    <div class="md:col-span-2">
                        <label for="mutasi_description" class="block text-sm font-semibold text-gray-700 mb-2">
                            Deskripsi
                        </label>
                        <textarea id="mutasi_description" 
                                  rows="3"
                                  placeholder="Tambahkan catatan tambahan jika diperlukan"
                                  class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm sm:text-base focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 mt-6">
                <button type="button" id="btn_cancel_save" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition w-full sm:w-auto text-sm sm:text-base">
                    Batal
                </button>
                <button type="button" id="btn_save_bulk" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded shadow transition flex items-center justify-center gap-2 w-full sm:w-auto text-sm sm:text-base">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="hidden sm:inline">Simpan Mutasi Massal</span>
                    <span class="sm:hidden">Simpan</span>
                </button>
            </div>
        </div>
        </div>
        
        <!-- Mode Bulk (New Simple Mode) -->
        <div id="mode_bulk" class="mode-section hidden" role="tabpanel">
            <div class="mb-6 bg-gray-50 rounded-lg p-4 sm:p-6 border border-gray-200">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Mutasi Massal - Lokasi Sama</h3>
                <p class="text-xs sm:text-sm text-gray-600 mb-4 sm:mb-6">Semua mesin yang di-scan akan dipindahkan ke lokasi yang sama dengan status yang sama.</p>
                
                <!-- Form Selection -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="bulk_status" class="block text-sm font-semibold text-gray-700 mb-2">
                            Status Mesin <span class="text-red-500">*</span>
                        </label>
                        <select id="bulk_status" 
                                required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                            <option value="">-- Pilih Status --</option>
                            <option value="Running">Running</option>
                            <option value="Standby">Standby</option>
                            <option value="Damage">Damage</option>
                            <option value="Destroy">Destroy</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label for="bulk_new_room" class="block text-sm font-semibold text-gray-700 mb-2">
                            Lokasi Tujuan <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="flex flex-col sm:flex-row gap-2">
                                <div class="relative flex-1">
                                    <input type="text" 
                                           id="bulk_new_room_search" 
                                           required
                                           placeholder="Ketik kode room atau nama room..."
                                           class="w-full border border-gray-300 rounded-lg px-4 py-2 pr-28 sm:pr-32 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm sm:text-base"
                                           autocomplete="off">
                                    <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2 sm:hidden">
                                        <button type="button" 
                                                id="btn_bulk_scan_room_mobile"
                                                class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded shadow transition">
                                            Scan
                                        </button>
                                    </div>
                                    <div id="bulk_room_dropdown" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg" style="max-height: 200px; overflow-y: auto;"></div>
                                </div>
                                <button type="button" 
                                        id="btn_bulk_scan_room"
                                        class="hidden sm:flex px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg shadow transition whitespace-nowrap items-center justify-center">
                                    Scan Room
                                </button>
                            </div>
                        </div>
                        <input type="hidden" id="bulk_new_room_id" value="">
                        <div id="bulk_selected_room" class="hidden mt-2 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="text-sm">
                                <span class="font-semibold">Room Terpilih:</span>
                                <span id="bulk_selected_room_info"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Scanner Section -->
                <div class="mb-6 bg-white rounded-lg p-4 border border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        <h4 class="text-sm sm:text-base font-semibold text-gray-800">Scanner Mesin</h4>
                        <button type="button" id="btn_bulk_open_scanner" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center justify-center gap-2 w-full sm:w-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            <span class="hidden sm:inline">Buka Scanner</span>
                            <span class="sm:hidden">Scanner</span>
                        </button>
                    </div>
                    
                    <!-- Manual Input -->
                    <div>
                        <label for="bulk_manual_machine_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            Atau Input Manual ID Machine
                        </label>
                        <div class="relative">
                            <div class="flex flex-col sm:flex-row gap-2">
                                <input type="text" 
                                       id="bulk_manual_machine_id" 
                                       placeholder="Ketik ID Machine, Type, atau Model..."
                                       class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 transition text-sm sm:text-base"
                                       autocomplete="off">
                                <button type="button" id="btn_bulk_manual_add" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition w-full sm:w-auto whitespace-nowrap">
                                    Tambah
                                </button>
                            </div>
                            <div id="bulk_machine_dropdown" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg" style="top: 100%; left: 0; max-height: 12rem; overflow-y: auto;"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Ketik ID Machine, Type, atau Model untuk melihat suggestion</p>
                    </div>
                </div>
                
                <!-- Scanned Machines List -->
                <div class="mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                        <h4 class="text-sm sm:text-base font-semibold text-gray-800">
                            Daftar Mesin yang Di-scan (<span id="bulk_scanned_count">0</span>)
                        </h4>
                        <button type="button" id="btn_bulk_clear_all" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-3 rounded shadow transition text-sm w-full sm:w-auto">
                            Clear All
                        </button>
                    </div>
                    
                    <div id="bulk_scanned_machines_list" class="space-y-2 min-h-[200px] bg-white border border-gray-200 rounded-lg p-2 sm:p-4 overflow-x-auto">
                        <div class="text-center text-gray-500 py-8 border-2 border-dashed border-gray-300 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            <p>Belum ada mesin yang di-scan. Gunakan scanner atau input manual untuk menambahkan mesin.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Process Button -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-4 border-t">
                    <button type="button" id="btn_bulk_reset" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition w-full sm:w-auto">
                        Reset
                    </button>
                    <button type="button" id="btn_bulk_process" disabled class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded shadow transition flex items-center justify-center gap-2 opacity-50 cursor-not-allowed w-full sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="hidden sm:inline">Proses Mutasi</span>
                        <span class="sm:hidden">Proses</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scanner Modal -->
<div id="scanner_modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-90 flex items-center justify-center p-0 sm:p-4">
    <div class="bg-white rounded-lg sm:rounded-lg shadow-xl max-w-2xl w-full h-full sm:h-auto m-0 sm:m-4 flex flex-col">
        <div class="p-4 sm:p-6 flex-shrink-0">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">Scan Barcode/QR Code Mesin</h3>
                <button type="button" id="btn_close_scanner" class="text-gray-400 hover:text-gray-600 p-2 -mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        
        <div id="scanner_container" class="flex-1 mb-4 relative bg-black rounded-lg overflow-hidden mx-4 sm:mx-6 min-h-[300px] sm:min-h-[400px]">
            <video id="scanner_video" class="w-full h-full object-cover" autoplay playsinline></video>
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="border-2 border-green-500 rounded-lg w-48 h-48 sm:w-64 sm:h-64"></div>
            </div>
        </div>
        
        <div id="scanner_status" class="text-xs sm:text-sm text-gray-600 mb-4 text-center px-4 sm:px-6"></div>
        
        <div class="p-4 sm:p-6 pt-0 flex-shrink-0">
            <div class="flex flex-col sm:flex-row gap-2">
                <button type="button" id="btn_start_scanner" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 sm:py-2 px-4 rounded shadow transition text-sm sm:text-base">
                    Start Camera
                </button>
                <button type="button" id="btn_stop_scanner" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 sm:py-2 px-4 rounded shadow transition hidden text-sm sm:text-base">
                    Stop Camera
                </button>
                <button type="button" id="btn_close_scanner_modal" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2.5 sm:py-2 px-4 rounded shadow transition text-sm sm:text-base">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include ZXing library for barcode scanning -->
<script src="https://cdn.jsdelivr.net/npm/@zxing/library@latest"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roomsData = @json($roomsData ?? []);
    const machinesData = @json($machinesData ?? []);
    const scannedMachines = [];
    let codeReader = null;
    let scannerStream = null;
    
    // Initialize ZXing code reader
    if (typeof ZXing !== 'undefined') {
        codeReader = new ZXing.BrowserMultiFormatReader();
    }
    
    // Modal elements
    const scannerModal = document.getElementById('scanner_modal');
    const btnOpenScanner = document.getElementById('btn_open_scanner');
    const btnCloseScanner = document.getElementById('btn_close_scanner');
    const btnCloseScannerModal = document.getElementById('btn_close_scanner_modal');
    const btnStartScanner = document.getElementById('btn_start_scanner');
    const btnStopScanner = document.getElementById('btn_stop_scanner');
    const scannerVideo = document.getElementById('scanner_video');
    const scannerStatus = document.getElementById('scanner_status');
    
    // Form elements
    const manualMachineId = document.getElementById('manual_machine_id');
    const btnManualAdd = document.getElementById('btn_manual_add');
    const scannedMachinesList = document.getElementById('scanned_machines_list');
    const scannedCount = document.getElementById('scanned_count');
    const bulkSaveSection = document.getElementById('bulk_save_section');
    const btnClearAll = document.getElementById('btn_clear_all');
    const btnSaveBulk = document.getElementById('btn_save_bulk');
    const btnCancelSave = document.getElementById('btn_cancel_save');
    
    // Open scanner modal
    if (btnOpenScanner) {
        btnOpenScanner.addEventListener('click', function() {
            scannerModal.classList.remove('hidden');
            scannerStatus.textContent = 'Tekan "Start Camera" untuk memulai scanner';
        });
    }
    
    // Close scanner modal
    const closeScannerModal = function() {
        stopScanner();
        scannerModal.classList.add('hidden');
    };
    
    if (btnCloseScanner) {
        btnCloseScanner.addEventListener('click', closeScannerModal);
    }
    if (btnCloseScannerModal) {
        btnCloseScannerModal.addEventListener('click', closeScannerModal);
    }
    
    // Start scanner
    if (btnStartScanner) {
        btnStartScanner.addEventListener('click', function() {
            startScanner();
        });
    }
    
    // Stop scanner
    if (btnStopScanner) {
        btnStopScanner.addEventListener('click', function() {
            stopScanner();
        });
    }
    
    function startScanner() {
        if (!codeReader) {
            scannerStatus.textContent = 'Error: ZXing library tidak tersedia';
            return;
        }
        
        scannerStatus.textContent = 'Mengakses kamera...';
        btnStartScanner.disabled = true;
        
        // Request camera access
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'environment' // Use back camera on mobile
            } 
        })
        .then(stream => {
            scannerStream = stream;
            scannerVideo.srcObject = stream;
            scannerVideo.play();
            
            scannerStatus.textContent = 'Arahkan kamera ke barcode/QR code mesin...';
            btnStartScanner.classList.add('hidden');
            btnStopScanner.classList.remove('hidden');
            
            // Start barcode detection
            if (codeReader) {
                codeReader.decodeFromVideoDevice(null, 'scanner_video', (result, err) => {
                    if (result) {
                        const scannedCode = result.getText();
                        scannerStatus.textContent = 'Barcode terdeteksi: ' + scannedCode;
                        
                        // Add scanned machine
                        addScannedMachine(scannedCode);
                        
                        // Stop scanner after successful scan
                        stopScanner();
                        closeScannerModal();
                    }
                    
                    if (err && !(err instanceof ZXing.NotFoundException)) {
                        console.error('Barcode scan error:', err);
                        scannerStatus.textContent = 'Error: ' + err.message;
                    }
                });
            }
        })
        .catch(err => {
            console.error('Error accessing camera:', err);
            scannerStatus.textContent = 'Error: Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.';
            btnStartScanner.disabled = false;
        });
    }
    
    function stopScanner() {
        if (codeReader) {
            codeReader.reset();
        }
        
        if (scannerStream) {
            scannerStream.getTracks().forEach(track => track.stop());
            scannerStream = null;
        }
        
        if (scannerVideo && scannerVideo.srcObject) {
            scannerVideo.srcObject = null;
        }
        
        btnStartScanner.disabled = false;
        btnStartScanner.classList.remove('hidden');
        btnStopScanner.classList.add('hidden');
        scannerStatus.textContent = '';
    }
    
    // Manual add machine
    if (btnManualAdd) {
        btnManualAdd.addEventListener('click', function() {
            const machineId = manualMachineId.value.trim();
            if (machineId) {
                addScannedMachine(machineId);
                manualMachineId.value = '';
                manualMachineId.focus();
            } else {
                alert('Masukkan ID Machine terlebih dahulu');
            }
        });
    }
    
    // Enter key on manual input
    if (manualMachineId) {
        manualMachineId.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                btnManualAdd.click();
            }
        });
    }
    
    // Add scanned machine
    async function addScannedMachine(machineId) {
        // Check if already exists
        if (scannedMachines.find(m => m.idMachine === machineId)) {
            alert('Mesin dengan ID "' + machineId + '" sudah ada dalam daftar');
            return;
        }
        
        // Fetch machine data
        try {
            const response = await fetch('{{ route("mutasi.scan-machine") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ idMachine: machineId })
            });
            
            const data = await response.json();
            
            if (data.success && data.machine) {
                scannedMachines.push({
                    ...data.machine,
                    new_room_erp_id: null,
                    temp_id: Date.now() // For unique identification
                });
                renderScannedMachines();
                updateBulkSaveSection();
            } else {
                alert(data.message || 'Mesin dengan ID "' + machineId + '" tidak ditemukan');
            }
        } catch (error) {
            console.error('Error fetching machine:', error);
            alert('Terjadi kesalahan saat mencari mesin. Silakan coba lagi.');
        }
    }
    
    // Remove scanned machine
    function removeScannedMachine(tempId) {
        const index = scannedMachines.findIndex(m => m.temp_id === tempId);
        if (index > -1) {
            scannedMachines.splice(index, 1);
            renderScannedMachines();
            updateBulkSaveSection();
        }
    }
    
    // Render scanned machines list
    function renderScannedMachines() {
        scannedCount.textContent = scannedMachines.length;
        
        if (scannedMachines.length === 0) {
            scannedMachinesList.innerHTML = `
                <div class="text-center text-gray-500 py-8 border-2 border-dashed border-gray-300 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    <p>Belum ada mesin yang di-scan. Gunakan scanner atau input manual untuk menambahkan mesin.</p>
                </div>
            `;
            return;
        }
        
        scannedMachinesList.innerHTML = scannedMachines.map((machine, index) => {
            const currentLocation = machine.current_location || {};
            const locationStr = [
                currentLocation.plant_name,
                currentLocation.process_name,
                currentLocation.line_name,
                currentLocation.room_name
            ].filter(Boolean).join(' / ') || '-';
            
            return `
                <div class="bg-white border border-gray-300 rounded-lg p-4 hover:shadow-md transition" data-temp-id="${machine.temp_id}">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-blue-600 text-white text-xs font-semibold px-2 py-1 rounded">${index + 1}</span>
                                <h4 class="text-lg font-bold text-gray-900">${machine.idMachine || '-'}</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-600">
                                <div><span class="font-semibold">Type:</span> ${machine.type_name || '-'}</div>
                                <div><span class="font-semibold">Brand:</span> ${machine.brand_name || '-'}</div>
                                <div><span class="font-semibold">Model:</span> ${machine.model_name || '-'}</div>
                                <div><span class="font-semibold">Lokasi Saat Ini:</span> ${locationStr}</div>
                            </div>
                        </div>
                        <button type="button" onclick="removeScannedMachineByTempId(${machine.temp_id})" class="text-red-600 hover:text-red-800 p-2" title="Hapus">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Lokasi Baru <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="flex flex-col sm:flex-row gap-2">
                                <div class="relative flex-1">
                                    <input type="text" 
                                           class="w-full border border-gray-300 rounded-lg px-4 py-2 pr-20 sm:pr-32 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition room-search-input text-sm sm:text-base" 
                                           placeholder="Ketik kode room atau nama room..."
                                           data-temp-id="${machine.temp_id}"
                                           autocomplete="off">
                                    <div class="absolute right-0 top-0 bottom-0 flex items-center pr-2 sm:hidden">
                                        <button type="button" 
                                                class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded shadow transition room-scan-btn-mobile"
                                                data-temp-id="${machine.temp_id}">
                                            Scan
                                        </button>
                                    </div>
                                    <div class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg room-dropdown" data-temp-id="${machine.temp_id}" style="max-height: 200px; overflow-y: auto;"></div>
                                </div>
                                <button type="button" 
                                        class="hidden sm:flex px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg shadow transition room-scan-btn whitespace-nowrap items-center justify-center"
                                        data-temp-id="${machine.temp_id}">
                                    Scan Room
                                </button>
                            </div>
                            <input type="hidden" class="room-id-input" data-temp-id="${machine.temp_id}" value="">
                            <div class="hidden mt-2 p-2 sm:p-3 bg-green-50 border border-green-200 rounded-lg selected-room" data-temp-id="${machine.temp_id}">
                                <div class="text-xs sm:text-sm">
                                    <span class="font-semibold">Room Terpilih:</span>
                                    <span class="selected-room-info"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        // Attach room search event listeners
        attachRoomSearchListeners();
    }
    
    // Attach room search listeners
    function attachRoomSearchListeners() {
        document.querySelectorAll('.room-search-input').forEach(input => {
            const tempId = input.getAttribute('data-temp-id');
            const dropdown = document.querySelector(`.room-dropdown[data-temp-id="${tempId}"]`);
            const roomIdInput = document.querySelector(`.room-id-input[data-temp-id="${tempId}"]`);
            const selectedRoomDiv = document.querySelector(`.selected-room[data-temp-id="${tempId}"]`);
            const selectedRoomInfo = selectedRoomDiv?.querySelector('.selected-room-info');
            
            input.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                
                if (searchTerm.length === 0) {
                    if (dropdown) dropdown.classList.add('hidden');
                    return;
                }
                
                const filtered = roomsData.filter(r => 
                    (r.kode_room && r.kode_room.toLowerCase().includes(searchTerm)) ||
                    (r.name && r.name.toLowerCase().includes(searchTerm)) ||
                    (r.plant_name && r.plant_name.toLowerCase().includes(searchTerm)) ||
                    (r.process_name && r.process_name.toLowerCase().includes(searchTerm)) ||
                    (r.line_name && r.line_name.toLowerCase().includes(searchTerm))
                );
                
                if (filtered.length === 0) {
                    if (dropdown) {
                        dropdown.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">Tidak ada room ditemukan</div>';
                        dropdown.classList.remove('hidden');
                    }
                    return;
                }
                
                if (dropdown) {
                    dropdown.innerHTML = filtered.slice(0, 8).map(r => {
                        const location = [
                            r.plant_name || '',
                            r.process_name || '',
                            r.line_name || '',
                            r.name || ''
                        ].filter(Boolean).join(' / ') || '-';
                        
                        return `
                            <div class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors" 
                                 data-room-id="${r.id}"
                                 data-room-kode="${(r.kode_room || '').replace(/"/g, '&quot;')}"
                                 data-room-name="${(r.name || '').replace(/"/g, '&quot;')}"
                                 data-room-plant="${(r.plant_name || '').replace(/"/g, '&quot;')}"
                                 data-room-process="${(r.process_name || '').replace(/"/g, '&quot;')}"
                                 data-room-line="${(r.line_name || '').replace(/"/g, '&quot;')}">
                                <div class="font-semibold text-gray-900 text-sm mb-1">${r.kode_room || ''} - ${r.name || ''}</div>
                                <div class="text-xs text-gray-500">${location}</div>
                            </div>
                        `;
                    }).join('');
                    
                    // Add click listeners
                    dropdown.querySelectorAll('[data-room-id]').forEach(item => {
                        item.addEventListener('click', function() {
                            const roomId = this.getAttribute('data-room-id');
                            const roomKode = this.getAttribute('data-room-kode');
                            const roomName = this.getAttribute('data-room-name');
                            const roomPlant = this.getAttribute('data-room-plant');
                            const roomProcess = this.getAttribute('data-room-process');
                            const roomLine = this.getAttribute('data-room-line');
                            
                            // Update machine
                            const machine = scannedMachines.find(m => m.temp_id == tempId);
                            if (machine) {
                                machine.new_room_erp_id = roomId;
                            }
                            
                            // Update UI
                            if (roomIdInput) roomIdInput.value = roomId;
                            if (input) input.value = roomKode || roomName;
                            if (selectedRoomInfo) {
                                selectedRoomInfo.textContent = `${roomKode || ''} - ${roomName || ''} (${[roomPlant, roomProcess, roomLine].filter(Boolean).join(' / ')})`;
                            }
                            if (selectedRoomDiv) selectedRoomDiv.classList.remove('hidden');
                            if (dropdown) dropdown.classList.add('hidden');
                            
                            updateBulkSaveSection();
                        });
                    });
                    
                    dropdown.classList.remove('hidden');
                }
            });
            
            // Hide dropdown on blur
            input.addEventListener('blur', function() {
                setTimeout(() => {
                    if (dropdown && !dropdown.contains(document.activeElement)) {
                        dropdown.classList.add('hidden');
                    }
                }, 200);
            });
        });
    }
    
    // Update bulk save section visibility
    function updateBulkSaveSection() {
        const allMachinesHaveRoom = scannedMachines.length > 0 && scannedMachines.every(m => m.new_room_erp_id);
        
        if (scannedMachines.length > 0) {
            bulkSaveSection.classList.remove('hidden');
        } else {
            bulkSaveSection.classList.add('hidden');
        }
        
        // Update save button state
        if (btnSaveBulk) {
            btnSaveBulk.disabled = !allMachinesHaveRoom;
            if (!allMachinesHaveRoom) {
                btnSaveBulk.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                btnSaveBulk.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
    }
    
    // Remove machine by temp ID (global function for onclick)
    window.removeScannedMachineByTempId = function(tempId) {
        removeScannedMachine(tempId);
    };
    
    // Clear all
    if (btnClearAll) {
        btnClearAll.addEventListener('click', function() {
            if (confirm('Hapus semua mesin yang sudah di-scan?')) {
                scannedMachines.length = 0;
                renderScannedMachines();
                updateBulkSaveSection();
            }
        });
    }
    
    // Cancel save
    if (btnCancelSave) {
        btnCancelSave.addEventListener('click', function() {
            if (confirm('Batalkan mutasi massal? Semua data yang sudah di-scan akan dihapus.')) {
                scannedMachines.length = 0;
                renderScannedMachines();
                updateBulkSaveSection();
            }
        });
    }
    
    // Save bulk mutations
    if (btnSaveBulk) {
        btnSaveBulk.addEventListener('click', async function() {
            const date = document.getElementById('mutasi_date').value;
            const reason = document.getElementById('mutasi_reason').value;
            const description = document.getElementById('mutasi_description').value;
            
            if (!date) {
                alert('Tanggal mutasi harus diisi');
                return;
            }
            
            // Check all machines have room
            const machinesWithoutRoom = scannedMachines.filter(m => !m.new_room_erp_id);
            if (machinesWithoutRoom.length > 0) {
                alert('Semua mesin harus memiliki lokasi baru. Silakan pilih lokasi baru untuk setiap mesin.');
                return;
            }
            
            // Prepare mutations data
            const mutations = scannedMachines.map(m => ({
                machine_erp_id: m.id,
                new_room_erp_id: m.new_room_erp_id,
                date: date,
                reason: reason || null,
                description: description || null
            }));
            
            // Disable button
            btnSaveBulk.disabled = true;
            btnSaveBulk.textContent = 'Menyimpan...';
            
            try {
                const response = await fetch('{{ route("mutasi.bulk-store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ mutations: mutations })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(`Berhasil menyimpan ${data.success_count} dari ${data.total_count} mutasi.${data.errors && data.errors.length > 0 ? '\n\nError:\n' + data.errors.map(e => `- ${e.idMachine}: ${e.message}`).join('\n') : ''}`);
                    
                    // Reset form
                    scannedMachines.length = 0;
                    renderScannedMachines();
                    updateBulkSaveSection();
                    document.getElementById('mutasi_date').value = '{{ date('Y-m-d') }}';
                    document.getElementById('mutasi_reason').value = '';
                    document.getElementById('mutasi_description').value = '';
                    
                    // Redirect to mutasi index
                    window.location.href = '{{ route("mutasi.index") }}';
                } else {
                    alert('Error: ' + (data.message || 'Terjadi kesalahan saat menyimpan mutasi massal'));
                    btnSaveBulk.disabled = false;
                    btnSaveBulk.textContent = 'Simpan Mutasi Massal';
                }
            } catch (error) {
                console.error('Error saving bulk mutations:', error);
                alert('Terjadi kesalahan saat menyimpan mutasi massal. Silakan coba lagi.');
                btnSaveBulk.disabled = false;
                btnSaveBulk.textContent = 'Simpan Mutasi Massal';
            }
        });
    }
    
    // Stop scanner when page unloads
    window.addEventListener('beforeunload', function() {
        stopScanner();
    });
    
    // ============================================
    // MODE BULK (SIMPLE MODE) - NEW FUNCTIONALITY
    // ============================================
    
    // Mode switching
    const tabModeIndividual = document.getElementById('tab_mode_individual');
    const tabModeBulk = document.getElementById('tab_mode_bulk');
    const modeIndividual = document.getElementById('mode_individual');
    const modeBulk = document.getElementById('mode_bulk');
    
    if (tabModeIndividual && tabModeBulk) {
        tabModeIndividual.addEventListener('click', function() {
            modeIndividual.classList.remove('hidden');
            modeBulk.classList.add('hidden');
            tabModeIndividual.classList.add('border-blue-600', 'text-blue-600');
            tabModeIndividual.classList.remove('border-transparent', 'text-gray-500');
            tabModeBulk.classList.remove('border-blue-600', 'text-blue-600');
            tabModeBulk.classList.add('border-transparent', 'text-gray-500');
            // Stop bulk scanner if running
            stopBulkScanner();
        });
        
        tabModeBulk.addEventListener('click', function() {
            modeIndividual.classList.add('hidden');
            modeBulk.classList.remove('hidden');
            tabModeBulk.classList.add('border-blue-600', 'text-blue-600');
            tabModeBulk.classList.remove('border-transparent', 'text-gray-500');
            tabModeIndividual.classList.remove('border-blue-600', 'text-blue-600');
            tabModeIndividual.classList.add('border-transparent', 'text-gray-500');
            // Stop individual scanner if running
            stopScanner();
        });
    }
    
    // Bulk mode variables
    const bulkScannedMachines = [];
    let bulkCodeReader = null;
    let bulkScannerStream = null;
    
    // Initialize ZXing code reader for bulk mode
    if (typeof ZXing !== 'undefined') {
        bulkCodeReader = new ZXing.BrowserMultiFormatReader();
    }
    
    // Bulk mode elements
    const bulkManualMachineId = document.getElementById('bulk_manual_machine_id');
    const btnBulkManualAdd = document.getElementById('btn_bulk_manual_add');
    const btnBulkOpenScanner = document.getElementById('btn_bulk_open_scanner');
    const bulkScannedMachinesList = document.getElementById('bulk_scanned_machines_list');
    const bulkScannedCount = document.getElementById('bulk_scanned_count');
    const btnBulkClearAll = document.getElementById('btn_bulk_clear_all');
    const btnBulkProcess = document.getElementById('btn_bulk_process');
    const btnBulkReset = document.getElementById('btn_bulk_reset');
    const bulkStatus = document.getElementById('bulk_status');
    const bulkNewRoomSearch = document.getElementById('bulk_new_room_search');
    const bulkNewRoomId = document.getElementById('bulk_new_room_id');
    const bulkRoomDropdown = document.getElementById('bulk_room_dropdown');
    const bulkSelectedRoom = document.getElementById('bulk_selected_room');
    const bulkSelectedRoomInfo = document.getElementById('bulk_selected_room_info');
    const btnBulkScanRoom = document.getElementById('btn_bulk_scan_room');
    
    // Bulk room search functionality
    if (bulkNewRoomSearch && bulkRoomDropdown) {
        bulkNewRoomSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            if (searchTerm.length === 0) {
                bulkRoomDropdown.classList.add('hidden');
                return;
            }
            
            const filtered = roomsData.filter(r => 
                (r.kode_room && r.kode_room.toLowerCase().includes(searchTerm)) ||
                (r.name && r.name.toLowerCase().includes(searchTerm)) ||
                (r.plant_name && r.plant_name.toLowerCase().includes(searchTerm)) ||
                (r.process_name && r.process_name.toLowerCase().includes(searchTerm)) ||
                (r.line_name && r.line_name.toLowerCase().includes(searchTerm))
            );
            
            if (filtered.length === 0) {
                bulkRoomDropdown.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">Tidak ada room ditemukan</div>';
                bulkRoomDropdown.classList.remove('hidden');
                return;
            }
            
            bulkRoomDropdown.innerHTML = filtered.slice(0, 8).map(r => {
                const location = [
                    r.plant_name || '',
                    r.process_name || '',
                    r.line_name || '',
                    r.name || ''
                ].filter(Boolean).join(' / ') || '-';
                
                return `
                    <div class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors" 
                         data-room-id="${r.id}"
                         data-room-kode="${(r.kode_room || '').replace(/"/g, '&quot;')}"
                         data-room-name="${(r.name || '').replace(/"/g, '&quot;')}"
                         data-room-plant="${(r.plant_name || '').replace(/"/g, '&quot;')}"
                         data-room-process="${(r.process_name || '').replace(/"/g, '&quot;')}"
                         data-room-line="${(r.line_name || '').replace(/"/g, '&quot;')}">
                        <div class="font-semibold text-gray-900 text-sm mb-1">${r.kode_room || ''} - ${r.name || ''}</div>
                        <div class="text-xs text-gray-500">${location}</div>
                    </div>
                `;
            }).join('');
            
            // Add click listeners
            bulkRoomDropdown.querySelectorAll('[data-room-id]').forEach(item => {
                item.addEventListener('click', function() {
                    const roomId = this.getAttribute('data-room-id');
                    const roomKode = this.getAttribute('data-room-kode');
                    const roomName = this.getAttribute('data-room-name');
                    const roomPlant = this.getAttribute('data-room-plant');
                    const roomProcess = this.getAttribute('data-room-process');
                    const roomLine = this.getAttribute('data-room-line');
                    
                    bulkNewRoomId.value = roomId;
                    bulkNewRoomSearch.value = roomKode || roomName;
                    bulkSelectedRoomInfo.textContent = `${roomKode || ''} - ${roomName || ''} (${[roomPlant, roomProcess, roomLine].filter(Boolean).join(' / ')})`;
                    bulkSelectedRoom.classList.remove('hidden');
                    bulkRoomDropdown.classList.add('hidden');
                    
                    updateBulkProcessButton();
                });
            });
            
            bulkRoomDropdown.classList.remove('hidden');
        });
        
        bulkNewRoomSearch.addEventListener('blur', function() {
            setTimeout(() => {
                if (bulkRoomDropdown && !bulkRoomDropdown.contains(document.activeElement)) {
                    bulkRoomDropdown.classList.add('hidden');
                }
            }, 200);
        });
    }
    
    // Bulk scanner
    if (btnBulkOpenScanner) {
        btnBulkOpenScanner.addEventListener('click', function() {
            scannerModal.classList.remove('hidden');
            scannerStatus.textContent = 'Tekan "Start Camera" untuk memulai scanner';
        });
    }
    
    function startBulkScanner() {
        if (!bulkCodeReader) {
            scannerStatus.textContent = 'Error: ZXing library tidak tersedia';
            return;
        }
        
        scannerStatus.textContent = 'Mengakses kamera...';
        btnStartScanner.disabled = true;
        
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'environment' 
            } 
        })
        .then(stream => {
            bulkScannerStream = stream;
            scannerVideo.srcObject = stream;
            scannerVideo.play();
            
            scannerStatus.textContent = 'Arahkan kamera ke barcode/QR code mesin...';
            btnStartScanner.classList.add('hidden');
            btnStopScanner.classList.remove('hidden');
            
            if (bulkCodeReader) {
                bulkCodeReader.decodeFromVideoDevice(null, 'scanner_video', (result, err) => {
                    if (result) {
                        const scannedCode = result.getText();
                        scannerStatus.textContent = 'Barcode terdeteksi: ' + scannedCode;
                        
                        addBulkScannedMachine(scannedCode);
                        
                        // Continue scanning (don't stop after one scan)
                        // stopBulkScanner();
                        // closeScannerModal();
                    }
                    
                    if (err && !(err instanceof ZXing.NotFoundException)) {
                        console.error('Barcode scan error:', err);
                    }
                });
            }
        })
        .catch(err => {
            console.error('Error accessing camera:', err);
            scannerStatus.textContent = 'Error: Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.';
            btnStartScanner.disabled = false;
        });
    }
    
    function stopBulkScanner() {
        if (bulkCodeReader) {
            bulkCodeReader.reset();
        }
        
        if (bulkScannerStream) {
            bulkScannerStream.getTracks().forEach(track => track.stop());
            bulkScannerStream = null;
        }
        
        if (scannerVideo && scannerVideo.srcObject) {
            scannerVideo.srcObject = null;
        }
        
        btnStartScanner.disabled = false;
        btnStartScanner.classList.remove('hidden');
        btnStopScanner.classList.add('hidden');
        scannerStatus.textContent = '';
    }
    
    // Update scanner button to use bulk scanner
    if (btnStartScanner) {
        btnStartScanner.addEventListener('click', function() {
            if (modeBulk && !modeBulk.classList.contains('hidden')) {
                startBulkScanner();
            } else {
                startScanner();
            }
        });
    }
    
    if (btnStopScanner) {
        btnStopScanner.addEventListener('click', function() {
            if (modeBulk && !modeBulk.classList.contains('hidden')) {
                stopBulkScanner();
            } else {
                stopScanner();
            }
        });
    }
    
    // Bulk machine suggestion dropdown
    const bulkMachineDropdown = document.getElementById('bulk_machine_dropdown');
    
    if (bulkManualMachineId && bulkMachineDropdown) {
        bulkManualMachineId.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            if (searchTerm.length === 0) {
                bulkMachineDropdown.classList.add('hidden');
                return;
            }
            
            // Check if machines data is available
            if (!machinesData || machinesData.length === 0) {
                bulkMachineDropdown.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">Data mesin tidak tersedia</div>';
                bulkMachineDropdown.classList.remove('hidden');
                return;
            }
            
            const filtered = machinesData.filter(m => 
                (m.idMachine && m.idMachine.toLowerCase().includes(searchTerm)) ||
                (m.typeMachine && m.typeMachine.toLowerCase().includes(searchTerm)) ||
                (m.modelMachine && m.modelMachine.toLowerCase().includes(searchTerm)) ||
                (m.brandMachine && m.brandMachine.toLowerCase().includes(searchTerm))
            );
            
            if (filtered.length === 0) {
                bulkMachineDropdown.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">Tidak ada mesin ditemukan</div>';
                bulkMachineDropdown.classList.remove('hidden');
                return;
            }
            
            bulkMachineDropdown.innerHTML = filtered.slice(0, 8).map(m => {
                // Format: Type - Brand Model
                const typeBrandModel = [];
                if (m.typeMachine) typeBrandModel.push(m.typeMachine);
                if (m.brandMachine || m.modelMachine) {
                    const brandModel = [m.brandMachine, m.modelMachine].filter(Boolean).join(' ');
                    if (brandModel) typeBrandModel.push(brandModel);
                }
                const typeBrandModelStr = typeBrandModel.length > 0 ? typeBrandModel.join(' - ') : '-';
                
                // Format: Plant / Process / Line / Room Name
                const location = [
                    m.plant || '',
                    m.process || '',
                    m.line || '',
                    m.roomName || ''
                ].filter(Boolean).join(' / ') || '-';
                
                return `
                    <div class="px-4 py-2.5 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors active:bg-blue-100" 
                         data-machine-id="${String(m.id)}"
                         data-machine-idmachine="${(m.idMachine || '').replace(/"/g, '&quot;')}"
                         style="user-select: none; -webkit-user-select: none;">
                        <div class="font-semibold text-gray-900 text-sm mb-1">${m.idMachine || '-'}</div>
                        <div class="text-xs text-gray-500">${typeBrandModelStr}</div>
                        <div class="text-xs text-gray-400">${location}</div>
                    </div>
                `;
            }).join('');
            
            // Add click event listeners
            bulkMachineDropdown.querySelectorAll('[data-machine-id]').forEach(item => {
                item.addEventListener('click', function() {
                    const idMachine = this.getAttribute('data-machine-idmachine');
                    bulkManualMachineId.value = idMachine;
                    bulkMachineDropdown.classList.add('hidden');
                    // Auto add machine
                    addBulkScannedMachine(idMachine);
                    bulkManualMachineId.value = '';
                    bulkManualMachineId.focus();
                });
            });
            
            bulkMachineDropdown.classList.remove('hidden');
        });
        
        // Hide dropdown on blur
        bulkManualMachineId.addEventListener('blur', function() {
            setTimeout(() => {
                if (bulkMachineDropdown && !bulkMachineDropdown.contains(document.activeElement)) {
                    bulkMachineDropdown.classList.add('hidden');
                }
            }, 200);
        });
        
        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (bulkManualMachineId && bulkMachineDropdown) {
                const isClickInside = bulkManualMachineId.contains(e.target) || bulkMachineDropdown.contains(e.target);
                if (!isClickInside) {
                    bulkMachineDropdown.classList.add('hidden');
                }
            }
        });
    }
    
    // Bulk manual add machine
    if (btnBulkManualAdd) {
        btnBulkManualAdd.addEventListener('click', function() {
            const machineId = bulkManualMachineId.value.trim();
            if (machineId) {
                addBulkScannedMachine(machineId);
                bulkManualMachineId.value = '';
                bulkManualMachineId.focus();
            } else {
                alert('Masukkan ID Machine terlebih dahulu');
            }
        });
    }
    
    if (bulkManualMachineId) {
        bulkManualMachineId.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                // If dropdown is visible and has selected item, use that
                if (bulkMachineDropdown && !bulkMachineDropdown.classList.contains('hidden')) {
                    const firstItem = bulkMachineDropdown.querySelector('[data-machine-id]');
                    if (firstItem) {
                        firstItem.click();
                        return;
                    }
                }
                // Otherwise, add manually
                btnBulkManualAdd.click();
            }
        });
    }
    
    // Add bulk scanned machine
    async function addBulkScannedMachine(machineId) {
        // Check if already exists
        if (bulkScannedMachines.find(m => m.idMachine === machineId)) {
            alert('Mesin dengan ID "' + machineId + '" sudah ada dalam daftar');
            return;
        }
        
        try {
            const response = await fetch('{{ route("mutasi.scan-machine") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ idMachine: machineId })
            });
            
            const data = await response.json();
            
            if (data.success && data.machine) {
                bulkScannedMachines.push({
                    ...data.machine,
                    temp_id: Date.now()
                });
                renderBulkScannedMachines();
                updateBulkProcessButton();
            } else {
                alert(data.message || 'Mesin dengan ID "' + machineId + '" tidak ditemukan');
            }
        } catch (error) {
            console.error('Error fetching machine:', error);
            alert('Terjadi kesalahan saat mencari mesin. Silakan coba lagi.');
        }
    }
    
    // Remove bulk scanned machine
    function removeBulkScannedMachine(tempId) {
        const index = bulkScannedMachines.findIndex(m => m.temp_id === tempId);
        if (index > -1) {
            bulkScannedMachines.splice(index, 1);
            renderBulkScannedMachines();
            updateBulkProcessButton();
        }
    }
    
    // Render bulk scanned machines list (simpler format)
    function renderBulkScannedMachines() {
        bulkScannedCount.textContent = bulkScannedMachines.length;
        
        if (bulkScannedMachines.length === 0) {
            bulkScannedMachinesList.innerHTML = `
                <div class="text-center text-gray-500 py-8 border-2 border-dashed border-gray-300 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    <p>Belum ada mesin yang di-scan. Gunakan scanner atau input manual untuk menambahkan mesin.</p>
                </div>
            `;
            return;
        }
        
        // Check if mobile view (screen width < 640px)
        const isMobile = window.innerWidth < 640;
        
        if (isMobile) {
            // Mobile: Card layout
            bulkScannedMachinesList.innerHTML = bulkScannedMachines.map((machine, index) => `
                <div class="bg-white border border-gray-200 rounded-lg p-3 mb-2">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-blue-600 text-white text-xs font-semibold px-2 py-1 rounded">${index + 1}</span>
                                <span class="text-sm font-bold text-gray-900">${machine.idMachine || '-'}</span>
                            </div>
                            <div class="text-xs text-gray-600 space-y-1">
                                <div><span class="font-semibold">Type:</span> ${machine.type_name || '-'}</div>
                                <div><span class="font-semibold">Model:</span> ${machine.model_name || '-'}</div>
                            </div>
                        </div>
                        <button type="button" onclick="removeBulkScannedMachineByTempId(${machine.temp_id})" class="text-red-600 hover:text-red-800 p-2 ml-2" title="Hapus">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            // Desktop: Table layout
            bulkScannedMachinesList.innerHTML = `
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-600">
                            <tr>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs font-medium text-white uppercase">No</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs font-medium text-white uppercase">ID Mesin</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs font-medium text-white uppercase">Machine Type</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-left text-xs font-medium text-white uppercase">Model</th>
                                <th class="px-3 sm:px-4 py-2 sm:py-3 text-center text-xs font-medium text-white uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            ${bulkScannedMachines.map((machine, index) => `
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap text-xs sm:text-sm text-gray-900">${index + 1}</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap text-xs sm:text-sm font-semibold text-gray-900">${machine.idMachine || '-'}</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap text-xs sm:text-sm text-gray-900">${machine.type_name || '-'}</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap text-xs sm:text-sm text-gray-900">${machine.model_name || '-'}</td>
                                    <td class="px-3 sm:px-4 py-2 sm:py-3 whitespace-nowrap text-center text-xs sm:text-sm">
                                        <button type="button" onclick="removeBulkScannedMachineByTempId(${machine.temp_id})" class="text-red-600 hover:text-red-800 p-1 sm:p-2" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }
    }
    
    // Update bulk process button state
    function updateBulkProcessButton() {
        const hasStatus = bulkStatus && bulkStatus.value;
        const hasRoom = bulkNewRoomId && bulkNewRoomId.value;
        const hasMachines = bulkScannedMachines.length > 0;
        
        const canProcess = hasStatus && hasRoom && hasMachines;
        
        if (btnBulkProcess) {
            btnBulkProcess.disabled = !canProcess;
            if (canProcess) {
                btnBulkProcess.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                btnBulkProcess.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }
    }
    
    // Status and room change listeners
    if (bulkStatus) {
        bulkStatus.addEventListener('change', updateBulkProcessButton);
    }
    
    if (bulkNewRoomId) {
        bulkNewRoomId.addEventListener('change', updateBulkProcessButton);
    }
    
    // Remove bulk machine by temp ID (global function)
    window.removeBulkScannedMachineByTempId = function(tempId) {
        removeBulkScannedMachine(tempId);
    };
    
    // Clear all bulk machines
    if (btnBulkClearAll) {
        btnBulkClearAll.addEventListener('click', function() {
            if (confirm('Hapus semua mesin yang sudah di-scan?')) {
                bulkScannedMachines.length = 0;
                renderBulkScannedMachines();
                updateBulkProcessButton();
            }
        });
    }
    
    // Reset bulk form
    if (btnBulkReset) {
        btnBulkReset.addEventListener('click', function() {
            if (confirm('Reset form? Semua data yang sudah di-scan akan dihapus.')) {
                bulkScannedMachines.length = 0;
                bulkStatus.value = '';
                bulkNewRoomSearch.value = '';
                bulkNewRoomId.value = '';
                bulkSelectedRoom.classList.add('hidden');
                renderBulkScannedMachines();
                updateBulkProcessButton();
            }
        });
    }
    
    // Process bulk mutations
    if (btnBulkProcess) {
        btnBulkProcess.addEventListener('click', async function() {
            const status = bulkStatus.value;
            const newRoomId = bulkNewRoomId.value;
            const date = '{{ date('Y-m-d') }}';
            const reason = 'Mutasi massal - Status: ' + status;
            const description = 'Mutasi massal ' + bulkScannedMachines.length + ' mesin ke lokasi yang sama dengan status ' + status;
            
            if (!status || !newRoomId || bulkScannedMachines.length === 0) {
                alert('Pastikan semua field sudah diisi dan ada mesin yang di-scan');
                return;
            }
            
            // Prepare mutations data
            const machineIds = bulkScannedMachines.map(m => m.id);
            
            // Disable button
            btnBulkProcess.disabled = true;
            btnBulkProcess.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Memproses...';
            
            try {
                const response = await fetch('{{ route("mutasi.bulk-store-simple") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        machine_ids: machineIds,
                        status: status,
                        new_room_erp_id: newRoomId,
                        date: date,
                        reason: reason,
                        description: description
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(`Berhasil memproses ${data.success_count} dari ${data.total_count} mutasi.${data.errors && data.errors.length > 0 ? '\n\nError:\n' + data.errors.map(e => `- ${e.idMachine}: ${e.message}`).join('\n') : ''}`);
                    
                    // Reset form
                    bulkScannedMachines.length = 0;
                    bulkStatus.value = '';
                    bulkNewRoomSearch.value = '';
                    bulkNewRoomId.value = '';
                    bulkSelectedRoom.classList.add('hidden');
                    renderBulkScannedMachines();
                    updateBulkProcessButton();
                    
                    // Redirect to mutasi index
                    window.location.href = '{{ route("mutasi.index") }}';
                } else {
                    alert('Error: ' + (data.message || 'Terjadi kesalahan saat memproses mutasi massal'));
                    btnBulkProcess.disabled = false;
                    btnBulkProcess.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Proses Mutasi';
                }
            } catch (error) {
                console.error('Error processing bulk mutations:', error);
                alert('Terjadi kesalahan saat memproses mutasi massal. Silakan coba lagi.');
                btnBulkProcess.disabled = false;
                btnBulkProcess.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Proses Mutasi';
            }
        });
    }
    
    // Stop bulk scanner when page unloads
    window.addEventListener('beforeunload', function() {
        stopBulkScanner();
    });
});
</script>
@endsection

