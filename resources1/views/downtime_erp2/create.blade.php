@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-6xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Create Downtime ERP2</h1>
            <p class="text-sm text-gray-600">Add new downtime ERP2 entry (all fields are manual input)</p>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        <form action="{{ route('downtime-erp2.store') }}" method="POST">
            @csrf
            
            <!-- Machine Information -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Machine Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="machine_search" class="block text-sm font-semibold text-gray-700 mb-2">ID Machine <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" 
                                   id="machine_search" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 pr-20 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('idMachine') border-red-500 @enderror" 
                                   placeholder="Ketik ID Machine atau scan barcode/QR code..."
                                   autocomplete="off">
                            <button type="button" 
                                    id="scan_barcode_btn" 
                                    class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm flex items-center gap-1"
                                    title="Scan Barcode/QR Code">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                Scan
                            </button>
                            <div id="machine_dropdown" class="hidden absolute z-50 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg" style="top: 100%; left: 0; right: 0; width: 100%; max-width: 100%; max-height: 12rem; overflow-y: auto; box-sizing: border-box;"></div>
                        </div>
                        <input type="hidden" name="idMachine" id="idMachine" required>
                        <div id="selected_machine" class="hidden mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">Machine Terpilih:</div>
                                    <div id="selected_machine_info" class="text-sm text-gray-700"></div>
                                </div>
                                <button type="button" id="clear_machine" class="text-red-600 hover:text-red-800 text-sm">Hapus</button>
                            </div>
                        </div>
                        @error('idMachine')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="typeMachine" class="block text-sm font-semibold text-gray-700 mb-2">Type Machine <span class="text-red-500">*</span> (Auto-fill)</label>
                        <input type="text" name="typeMachine" id="typeMachine" value="{{ old('typeMachine') }}" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('typeMachine') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari Machine ERP</p>
                        @error('typeMachine')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modelMachine" class="block text-sm font-semibold text-gray-700 mb-2">Model Machine <span class="text-red-500">*</span> (Auto-fill)</label>
                        <input type="text" name="modelMachine" id="modelMachine" value="{{ old('modelMachine') }}" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('modelMachine') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari Machine ERP</p>
                        @error('modelMachine')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="brandMachine" class="block text-sm font-semibold text-gray-700 mb-2">Brand Machine <span class="text-red-500">*</span> (Auto-fill)</label>
                        <input type="text" name="brandMachine" id="brandMachine" value="{{ old('brandMachine') }}" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('brandMachine') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari Machine ERP</p>
                        @error('brandMachine')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Basic Information -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('date') border-red-500 @enderror">
                        @error('date')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="plant" class="block text-sm font-semibold text-gray-700 mb-2">Plant <span class="text-red-500">*</span> (Auto-fill)</label>
                        <input type="text" name="plant" id="plant" value="{{ old('plant') }}" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('plant') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari Machine ERP</p>
                        @error('plant')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="process" class="block text-sm font-semibold text-gray-700 mb-2">Process <span class="text-red-500">*</span> (Auto-fill)</label>
                        <input type="text" name="process" id="process" value="{{ old('process') }}" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('process') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari Machine ERP</p>
                        @error('process')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="line" class="block text-sm font-semibold text-gray-700 mb-2">Line <span class="text-red-500">*</span> (Auto-fill)</label>
                        <input type="text" name="line" id="line" value="{{ old('line') }}" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('line') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari Machine ERP</p>
                        @error('line')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="roomName" class="block text-sm font-semibold text-gray-700 mb-2">Room Name <span class="text-red-500">*</span> (Auto-fill)</label>
                        <input type="text" name="roomName" id="roomName" value="{{ old('roomName') }}" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('roomName') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari Machine ERP</p>
                        @error('roomName')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                
                <!-- Hidden field for kode_room -->
                <input type="hidden" name="kode_room" id="kode_room" value="{{ old('kode_room') }}">
                <div class="mt-4">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="include_oee" 
                               id="include_oee" 
                               value="1"
                               {{ old('include_oee') ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                        <span class="ml-2 text-sm font-semibold text-gray-700">Include OEE</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-6">Centang untuk memasukkan data ini ke perhitungan OEE</p>
                    @error('include_oee')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Downtime Information -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Downtime Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="stopProduction" class="block text-sm font-semibold text-gray-700 mb-2">Stop Production <span class="text-red-500">*</span></label>
                        <input type="time" name="stopProduction" id="stopProduction" value="{{ old('stopProduction') }}" step="1" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('stopProduction') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Format: HH:MM:SS (menggunakan tanggal dari field Date)</p>
                        @error('stopProduction')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="responMechanic" class="block text-sm font-semibold text-gray-700 mb-2">Respon Mechanic <span class="text-red-500">*</span></label>
                        <input type="time" name="responMechanic" id="responMechanic" value="{{ old('responMechanic') }}" step="1" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('responMechanic') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Format: HH:MM:SS (menggunakan tanggal dari field Date)</p>
                        @error('responMechanic')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="startProduction" class="block text-sm font-semibold text-gray-700 mb-2">Start Production <span class="text-red-500">*</span></label>
                        <input type="time" name="startProduction" id="startProduction" value="{{ old('startProduction') }}" step="1" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('startProduction') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Format: HH:MM:SS (menggunakan tanggal dari field Date)</p>
                        @error('startProduction')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="duration" class="block text-sm font-semibold text-gray-700 mb-2">Duration <span class="text-red-500">*</span> (Auto-calculated)</label>
                        <input type="text" name="duration" id="duration" value="{{ old('duration') }}" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('duration') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis dihitung dari Start Production - Stop Production</p>
                        @error('duration')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="Standar_Time" class="block text-sm font-semibold text-gray-700 mb-2">Standar Time</label>
                        <input type="text" name="Standar_Time" id="Standar_Time" value="{{ old('Standar_Time') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('Standar_Time') border-red-500 @enderror">
                        @error('Standar_Time')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Problem Information -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4 border-b pb-2">
                    <h3 class="text-lg font-semibold text-gray-800">Problem Information</h3>
                    <div class="flex items-center gap-2">
                        <label for="problem_search" class="text-sm font-medium text-gray-700">Cari:</label>
                        <div class="relative" style="width: 300px;">
                            <input type="text" 
                                   id="problem_search" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" 
                                   placeholder="Ketik untuk filter atau cari Action..."
                                   autocomplete="off">
                            <div id="action_suggestions" class="hidden absolute z-50 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg w-full max-h-60 overflow-y-auto"></div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Hidden field for problemDowntime (auto-filled from problem) -->
                    <input type="hidden" name="problemDowntime" id="problemDowntime" value="{{ old('problemDowntime') }}" required>
                    
                    <div>
                        <label for="system_select" class="block text-sm font-semibold text-gray-700 mb-2">System <span class="text-red-500">*</span></label>
                        <select name="system_select" id="system_select" required disabled class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('system_select') border-red-500 @enderror">
                            <option value="">-- Pilih Machine terlebih dahulu --</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Otomatis terfilter berdasarkan machine yang dipilih</p>
                        @error('system_select')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="problem_select" class="block text-sm font-semibold text-gray-700 mb-2">Problem <span class="text-red-500">*</span></label>
                        <select name="problem_select" id="problem_select" required disabled class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('problem_select') border-red-500 @enderror">
                            <option value="">-- Pilih System terlebih dahulu --</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Pilih problem setelah memilih system (akan mengisi Problem Downtime)</p>
                        @error('problem_select')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="Problem_MM" class="block text-sm font-semibold text-gray-700 mb-2">Problem MM</label>
                        <input type="text" name="Problem_MM" id="Problem_MM" value="{{ old('Problem_MM', 'other') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('Problem_MM') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Default: "other" (dapat diedit)</p>
                        @error('Problem_MM')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="reason_select" class="block text-sm font-semibold text-gray-700 mb-2">Reason Downtime <span class="text-red-500">*</span></label>
                        <select name="reason_select" id="reason_select" required disabled class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('reason_select') border-red-500 @enderror">
                            <option value="">-- Pilih Problem terlebih dahulu --</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Pilih reason setelah memilih problem</p>
                        @error('reason_select')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                        <!-- Hidden field for reasonDowntime (auto-filled from reason) -->
                        <input type="hidden" name="reasonDowntime" id="reasonDowntime" value="{{ old('reasonDowntime') }}" required>
                    </div>
                    <div>
                        <label for="action_select" class="block text-sm font-semibold text-gray-700 mb-2">Action Downtime <span class="text-red-500">*</span></label>
                        <select name="action_select" id="action_select" required disabled class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('action_select') border-red-500 @enderror">
                            <option value="">-- Pilih Reason terlebih dahulu --</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Pilih action setelah memilih reason</p>
                        @error('action_select')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                        <!-- Hidden field for actionDowtime (auto-filled from action) -->
                        <input type="hidden" name="actionDowtime" id="actionDowtime" value="{{ old('actionDowtime') }}" required>
                    </div>
                    <div>
                        <label for="part_select" class="block text-sm font-semibold text-gray-700 mb-2">Part</label>
                        <select name="part_select" id="part_select" disabled class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('part_select') border-red-500 @enderror">
                            <option value="">-- Pilih System terlebih dahulu --</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Pilih part setelah memilih system (terfilter berdasarkan system)</p>
                        @error('part_select')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                        <!-- Hidden field for Part (auto-filled from part_select) -->
                        <input type="hidden" name="Part" id="Part" value="{{ old('Part') }}">
                    </div>
                    <div>
                        <label for="groupProblem" class="block text-sm font-semibold text-gray-700 mb-2">Group Problem</label>
                        <input type="text" name="groupProblem" id="groupProblem" value="{{ old('groupProblem') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('groupProblem') border-red-500 @enderror">
                        @error('groupProblem')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Personnel Information -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Personnel Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="mekanik_search" class="block text-sm font-semibold text-gray-700 mb-2">Mekanik (NIK/Nama) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" 
                                   id="mekanik_search" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('idMekanik') border-red-500 @enderror" 
                                   placeholder="Ketik NIK atau nama mekanik..."
                                   autocomplete="off">
                            <div id="mekanik_dropdown" class="hidden absolute z-50 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg" style="top: 100%; left: 0; right: 0; width: 100%; max-width: 100%; max-height: 12rem; overflow-y: auto; box-sizing: border-box;"></div>
                        </div>
                        <input type="hidden" name="idMekanik" id="idMekanik" required>
                        <input type="hidden" name="nameMekanik" id="nameMekanik" required>
                        <div id="selected_mekanik" class="hidden mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">Mekanik Terpilih:</div>
                                    <div id="selected_mekanik_info" class="text-sm text-gray-700"></div>
                                </div>
                                <button type="button" id="clear_mekanik" class="text-red-600 hover:text-red-800 text-sm">Hapus</button>
                            </div>
                        </div>
                        @error('idMekanik')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        @error('nameMekanik')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="idLeader" class="block text-sm font-semibold text-gray-700 mb-2">ID Leader <span class="text-red-500">*</span> (Auto-fill)</label>
                        <input type="text" name="idLeader" id="idLeader" value="{{ old('idLeader') }}" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('idLeader') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari atasan mekanik</p>
                        @error('idLeader')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="nameLeader" class="block text-sm font-semibold text-gray-700 mb-2">Name Leader <span class="text-red-500">*</span> (Auto-fill)</label>
                        <input type="text" name="nameLeader" id="nameLeader" value="{{ old('nameLeader') }}" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('nameLeader') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari atasan mekanik</p>
                        @error('nameLeader')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="idGL" class="block text-sm font-semibold text-gray-700 mb-2">ID GL (Auto-fill)</label>
                        <input type="text" name="idGL" id="idGL" value="{{ old('idGL') }}" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('idGL') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari atasan leader</p>
                        @error('idGL')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="nameGL" class="block text-sm font-semibold text-gray-700 mb-2">Name GL (Auto-fill)</label>
                        <input type="text" name="nameGL" id="nameGL" value="{{ old('nameGL') }}" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('nameGL') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari atasan leader</p>
                        @error('nameGL')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="idCoord" class="block text-sm font-semibold text-gray-700 mb-2">ID Coord <span class="text-red-500">*</span> (Auto-fill)</label>
                        <input type="text" name="idCoord" id="idCoord" value="{{ old('idCoord') }}" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('idCoord') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari atasan GL</p>
                        @error('idCoord')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="nameCoord" class="block text-sm font-semibold text-gray-700 mb-2">Name Coord <span class="text-red-500">*</span> (Auto-fill)</label>
                        <input type="text" name="nameCoord" id="nameCoord" value="{{ old('nameCoord') }}" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('nameCoord') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari atasan GL</p>
                        @error('nameCoord')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition duration-150 ease-in-out flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Create Downtime ERP2
                </button>
                <a href="{{ route('downtime-erp2.index') }}" class="text-gray-600 hover:text-gray-800 hover:underline transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

<!-- Barcode Scanner Modal -->
<div id="barcode_modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="closeBarcodeModal()"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800">Scan Barcode/QR Code</h3>
                <button onclick="closeBarcodeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="mb-4">
                <video id="barcode_video" class="w-full rounded-lg" autoplay playsinline></video>
                <canvas id="barcode_canvas" class="hidden"></canvas>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Atau masukkan manual:</label>
                <input type="text" 
                       id="manual_barcode_input" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Masukkan ID Machine">
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="startBarcodeScan()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition">Mulai Scan</button>
                <button type="button" onclick="stopBarcodeScan()" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg transition">Stop</button>
                <button type="button" onclick="closeBarcodeModal()" class="text-gray-600 hover:text-gray-800">Batal</button>
            </div>
        </div>
    </div>
</div>

<script>
// Mekanik data from server (already mapped in controller)
const mekaniks = @json($mekaniks);

// Machine data from server (already mapped in controller)
const machines = @json($machines);

// System, Problem, Reason, Action, and Part data from server (already mapped in controller)
const systems = @json($systems ?? []);
const problems = @json($problems ?? []);
const reasons = @json($reasons ?? []);
const actions = @json($actions ?? []);
const parts = @json($parts ?? []);

// DOM elements - Mekanik
const mekanikSearch = document.getElementById('mekanik_search');
const mekanikId = document.getElementById('idMekanik');
const mekanikName = document.getElementById('nameMekanik');
const mekanikDropdown = document.getElementById('mekanik_dropdown');
const selectedMekanik = document.getElementById('selected_mekanik');
const selectedMekanikInfo = document.getElementById('selected_mekanik_info');
const clearMekanik = document.getElementById('clear_mekanik');
const idLeader = document.getElementById('idLeader');
const nameLeader = document.getElementById('nameLeader');
const idGL = document.getElementById('idGL');
const nameGL = document.getElementById('nameGL');
const idCoord = document.getElementById('idCoord');
const nameCoord = document.getElementById('nameCoord');

// DOM elements - Machine (will be initialized after DOM ready)
let machineSearch, machineIdInput, machineDropdown, selectedMachine, selectedMachineInfo, clearMachine;
let scanBarcodeBtn, barcodeModal, barcodeVideo, barcodeCanvas, manualBarcodeInput;

// Machine detail fields
const typeMachine = document.getElementById('typeMachine');
const modelMachine = document.getElementById('modelMachine');
const brandMachine = document.getElementById('brandMachine');
const plant = document.getElementById('plant');
const process = document.getElementById('process');
const line = document.getElementById('line');
const roomName = document.getElementById('roomName');

// Auto-complete for mekanik
mekanikSearch.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();
    
    if (searchTerm.length === 0) {
        mekanikDropdown.classList.add('hidden');
        return;
    }
    
    const filtered = mekaniks.filter(m => 
        (m.nik && m.nik.toLowerCase().includes(searchTerm)) ||
        (m.name && m.name.toLowerCase().includes(searchTerm))
    );
    
    if (filtered.length === 0) {
        mekanikDropdown.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">Tidak ada mekanik ditemukan</div>';
        mekanikDropdown.classList.remove('hidden');
        return;
    }
    
    mekanikDropdown.innerHTML = filtered.slice(0, 8).map(m => {
        const displayText = `${m.nik || ''} - ${m.name || ''}`;
        return `
            <div class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors active:bg-blue-100" 
                 data-mekanik-id="${String(m.id)}"
                 data-mekanik-nik="${(m.nik || '').replace(/"/g, '&quot;')}"
                 data-mekanik-name="${(m.name || '').replace(/"/g, '&quot;')}"
                 data-atasan-nik="${(m.atasan_nik || '').replace(/"/g, '&quot;')}"
                 data-atasan-name="${(m.atasan_name || '').replace(/"/g, '&quot;')}"
                 data-gl-nik="${(m.gl_nik || '').replace(/"/g, '&quot;')}"
                 data-gl-name="${(m.gl_name || '').replace(/"/g, '&quot;')}"
                 data-coord-nik="${(m.coord_nik || '').replace(/"/g, '&quot;')}"
                 data-coord-name="${(m.coord_name || '').replace(/"/g, '&quot;')}"
                 style="user-select: none; -webkit-user-select: none;">
                <div class="font-semibold text-gray-900">${m.nik || ''} - ${m.name || ''}</div>
            </div>
        `;
    }).join('');
    
    // Add click event listeners
    mekanikDropdown.querySelectorAll('[data-mekanik-id]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const mekanikId = this.getAttribute('data-mekanik-id');
            const mekanikNik = this.getAttribute('data-mekanik-nik');
            const mekanikName = this.getAttribute('data-mekanik-name');
            const atasanNik = this.getAttribute('data-atasan-nik');
            const atasanName = this.getAttribute('data-atasan-name');
            const glNik = this.getAttribute('data-gl-nik');
            const glName = this.getAttribute('data-gl-name');
            const coordNik = this.getAttribute('data-coord-nik');
            const coordName = this.getAttribute('data-coord-name');
            selectMekanik(mekanikId, mekanikNik, mekanikName, atasanNik, atasanName, glNik, glName, coordNik, coordName);
        });
    });
    
    mekanikDropdown.classList.remove('hidden');
});

// Select mekanik
function selectMekanik(id, nik, name, atasanNik, atasanName, glNik, glName, coordNik, coordName) {
    mekanikId.value = nik || '';
    mekanikName.value = name || '';
    mekanikSearch.value = nik ? `${nik} - ${name}` : name;
    selectedMekanikInfo.textContent = nik ? `${nik} - ${name}` : name;
    selectedMekanik.classList.remove('hidden');
    mekanikDropdown.classList.add('hidden');
    mekanikSearch.blur();
    
    // Auto-fill leader
    if (atasanNik && atasanName) {
        idLeader.value = atasanNik;
        nameLeader.value = atasanName;
    } else {
        idLeader.value = '';
        nameLeader.value = '';
    }
    
    // Auto-fill GL
    if (glNik && glName) {
        idGL.value = glNik;
        nameGL.value = glName;
    } else {
        idGL.value = '';
        nameGL.value = '';
    }
    
    // Auto-fill Coordinator
    if (coordNik && coordName) {
        idCoord.value = coordNik;
        nameCoord.value = coordName;
    } else {
        idCoord.value = '';
        nameCoord.value = '';
    }
}

// Clear mekanik
clearMekanik.addEventListener('click', function() {
    mekanikId.value = '';
    mekanikName.value = '';
    mekanikSearch.value = '';
    selectedMekanik.classList.add('hidden');
    idLeader.value = '';
    nameLeader.value = '';
    idGL.value = '';
    nameGL.value = '';
    idCoord.value = '';
    nameCoord.value = '';
});

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const isClickInside = mekanikSearch.contains(e.target) || mekanikDropdown.contains(e.target);
    if (!isClickInside) {
        mekanikDropdown.classList.add('hidden');
    }
});

// Auto-calculate duration
function calculateDuration() {
    const dateInput = document.getElementById('date');
    const stopInput = document.getElementById('stopProduction');
    const startInput = document.getElementById('startProduction');
    const durationInput = document.getElementById('duration');
    
    if (!dateInput || !stopInput || !startInput || !durationInput) {
        return;
    }
    
    const date = dateInput.value;
    const stopTime = stopInput.value;
    const startTime = startInput.value;
    
    if (!date || !stopTime || !startTime) {
        durationInput.value = '';
        return;
    }
    
    try {
        // Combine date with time
        const stopDateTime = new Date(date + 'T' + stopTime);
        const startDateTime = new Date(date + 'T' + startTime);
        
        // Calculate difference in milliseconds
        const diffMs = startDateTime - stopDateTime;
        
        if (diffMs < 0) {
            // If start is before stop, assume it's next day
            const nextDay = new Date(startDateTime);
            nextDay.setDate(nextDay.getDate() + 1);
            const diffMsNext = nextDay - stopDateTime;
            const diffMinutes = Math.floor(diffMsNext / (1000 * 60));
            durationInput.value = diffMinutes + ' minutes';
        } else {
            const diffMinutes = Math.floor(diffMs / (1000 * 60));
            durationInput.value = diffMinutes + ' minutes';
        }
    } catch (e) {
        console.error('Error calculating duration:', e);
        durationInput.value = '';
    }
}

// Add event listeners for duration calculation
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('date');
    const stopInput = document.getElementById('stopProduction');
    const startInput = document.getElementById('startProduction');
    
    if (dateInput) dateInput.addEventListener('change', calculateDuration);
    if (stopInput) stopInput.addEventListener('change', calculateDuration);
    if (startInput) startInput.addEventListener('change', calculateDuration);
    
    // Calculate on page load if values exist
    if (dateInput && stopInput && startInput && dateInput.value && stopInput.value && startInput.value) {
        calculateDuration();
    }
});

// Initialize machine DOM elements and event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Get machine DOM elements
    machineSearch = document.getElementById('machine_search');
    machineIdInput = document.getElementById('idMachine');
    machineDropdown = document.getElementById('machine_dropdown');
    selectedMachine = document.getElementById('selected_machine');
    selectedMachineInfo = document.getElementById('selected_machine_info');
    clearMachine = document.getElementById('clear_machine');
    scanBarcodeBtn = document.getElementById('scan_barcode_btn');
    barcodeModal = document.getElementById('barcode_modal');
    barcodeVideo = document.getElementById('barcode_video');
    barcodeCanvas = document.getElementById('barcode_canvas');
    manualBarcodeInput = document.getElementById('manual_barcode_input');
    
    // Machine auto-complete functionality
    if (machineSearch && machineDropdown) {
        console.log('Machine search elements found, attaching event listener');
        console.log('Machines data available:', machines ? machines.length : 0);
        console.log('Machines data sample:', machines && machines.length > 0 ? machines[0] : 'No machines');
        
        machineSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            console.log('Search term:', searchTerm);
            console.log('Machine dropdown element:', machineDropdown);
            
            if (searchTerm.length === 0) {
                machineDropdown.classList.add('hidden');
                return;
            }
            
            // Check if machines data is available
            if (!machines || machines.length === 0) {
                console.warn('Machines data is not available');
                machineDropdown.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">Data mesin tidak tersedia</div>';
                machineDropdown.classList.remove('hidden');
                return;
            }
            
            const filtered = machines.filter(m => 
                (m.idMachine && m.idMachine.toLowerCase().includes(searchTerm)) ||
                (m.typeMachine && m.typeMachine.toLowerCase().includes(searchTerm)) ||
                (m.modelMachine && m.modelMachine.toLowerCase().includes(searchTerm)) ||
                (m.brandMachine && m.brandMachine.toLowerCase().includes(searchTerm))
            );
            
            if (filtered.length === 0) {
                machineDropdown.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">Tidak ada mesin ditemukan</div>';
                machineDropdown.classList.remove('hidden');
                console.log('No machines found for search term:', searchTerm);
                return;
            }
            
            console.log('Filtered machines:', filtered.length);
            machineDropdown.innerHTML = filtered.slice(0, 8).map(m => {
                return `
                    <div class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors active:bg-blue-100" 
                         data-machine-id="${String(m.id)}"
                         data-machine-idmachine="${(m.idMachine || '').replace(/"/g, '&quot;')}"
                         data-machine-type="${(m.typeMachine || '').replace(/"/g, '&quot;')}"
                         data-machine-model="${(m.modelMachine || '').replace(/"/g, '&quot;')}"
                         data-machine-brand="${(m.brandMachine || '').replace(/"/g, '&quot;')}"
                         data-machine-plant="${(m.plant || '').replace(/"/g, '&quot;')}"
                         data-machine-process="${(m.process || '').replace(/"/g, '&quot;')}"
                         data-machine-line="${(m.line || '').replace(/"/g, '&quot;')}"
                         data-machine-room="${(m.roomName || '').replace(/"/g, '&quot;')}"
                         data-machine-kode-room="${(m.kodeRoom || '').replace(/"/g, '&quot;')}"
                         style="user-select: none; -webkit-user-select: none;">
                        <div class="font-semibold text-gray-900">${m.idMachine || ''}</div>
                        <div class="text-xs text-gray-600">${m.typeMachine || ''} - ${m.brandMachine || ''} ${m.modelMachine || ''}</div>
                        <div class="text-xs text-gray-500">${m.plant || ''} / ${m.process || ''} / ${m.line || ''} / ${m.roomName || ''}</div>
                    </div>
                `;
            }).join('');
            
            // Add click event listeners
            machineDropdown.querySelectorAll('[data-machine-id]').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const machineId = this.getAttribute('data-machine-id');
                    const machineIdMachine = this.getAttribute('data-machine-idmachine');
                    const machineType = this.getAttribute('data-machine-type');
                    const machineModel = this.getAttribute('data-machine-model');
                    const machineBrand = this.getAttribute('data-machine-brand');
                    const machinePlant = this.getAttribute('data-machine-plant');
                    const machineProcess = this.getAttribute('data-machine-process');
                    const machineLine = this.getAttribute('data-machine-line');
                    const machineRoom = this.getAttribute('data-machine-room');
                    const machineKodeRoom = this.getAttribute('data-machine-kode-room');
                    // Get system_ids from the machine data
                    const selectedMachineData = machines.find(m => String(m.id) === machineId || m.idMachine === machineIdMachine);
                    const systemIds = selectedMachineData ? (selectedMachineData.system_ids || []) : [];
                    selectMachine(machineId, machineIdMachine, machineType, machineModel, machineBrand, machinePlant, machineProcess, machineLine, machineRoom, machineKodeRoom, systemIds);
                });
            });
            
            machineDropdown.classList.remove('hidden');
            console.log('Dropdown shown with', filtered.slice(0, 8).length, 'items');
            console.log('Dropdown classes:', machineDropdown.className);
            console.log('Dropdown style:', machineDropdown.style.cssText);
        });
        console.log('Event listener attached successfully');
    } else {
        console.error('Machine search elements not found:', { 
            machineSearch: !!machineSearch, 
            machineDropdown: !!machineDropdown,
            machineSearchId: machineSearch ? machineSearch.id : 'N/A',
            machineDropdownId: machineDropdown ? machineDropdown.id : 'N/A'
        });
    }
    
    // Select machine function
    window.selectMachine = function(id, idMachine, type, model, brand, plantVal, processVal, lineVal, roomVal, kodeRoomVal, systemIds = []) {
        if (!machineIdInput || !machineSearch || !selectedMachineInfo || !selectedMachine) return;
        
        machineIdInput.value = idMachine || '';
        machineSearch.value = idMachine || '';
        selectedMachineInfo.innerHTML = `
            <div class="font-semibold">${idMachine || ''}</div>
            <div class="text-xs text-gray-600">${type || ''} - ${brand || ''} ${model || ''}</div>
            <div class="text-xs text-gray-500">${plantVal || ''} / ${processVal || ''} / ${lineVal || ''} / ${roomVal || ''}</div>
        `;
        selectedMachine.classList.remove('hidden');
        if (machineDropdown) machineDropdown.classList.add('hidden');
        machineSearch.blur();
        
        // Auto-fill machine details
        const typeMachine = document.getElementById('typeMachine');
        const modelMachine = document.getElementById('modelMachine');
        const brandMachine = document.getElementById('brandMachine');
        const plant = document.getElementById('plant');
        const process = document.getElementById('process');
        const line = document.getElementById('line');
        const roomName = document.getElementById('roomName');
        
        if (typeMachine) typeMachine.value = type || '';
        if (modelMachine) modelMachine.value = model || '';
        if (brandMachine) brandMachine.value = brand || '';
        if (plant) plant.value = plantVal || '';
        if (process) process.value = processVal || '';
        if (line) line.value = lineVal || '';
        if (roomName) roomName.value = roomVal || '';
        
        // Set kode_room if hidden field exists
        const kodeRoomInput = document.getElementById('kode_room');
        if (kodeRoomInput) {
            kodeRoomInput.value = kodeRoomVal || '';
        }
        
        // Filter and populate system dropdown based on selected machine's system_ids
        const systemSelect = document.getElementById('system_select');
        if (systemSelect) {
            // Clear existing options
            systemSelect.innerHTML = '<option value="">-- Pilih System --</option>';
            
            if (systemIds && systemIds.length > 0) {
                // Filter systems based on machine's system_ids
                const filteredSystems = systems.filter(s => 
                    systemIds.includes(String(s.id))
                );
                
                // Populate system dropdown
                filteredSystems.forEach(system => {
                    const option = document.createElement('option');
                    option.value = system.id;
                    option.textContent = system.nama_sistem;
                    systemSelect.appendChild(option);
                });
                
                // Enable system select
                systemSelect.disabled = false;
                systemSelect.classList.remove('bg-gray-100');
            } else {
                // If no systems found for this machine, disable system select
                systemSelect.innerHTML = '<option value="">-- Machine ini tidak memiliki system --</option>';
                systemSelect.disabled = true;
                systemSelect.classList.add('bg-gray-100');
            }
            
            // Reset problem, reason, action, and part dropdowns when system changes
            const problemSelect = document.getElementById('problem_select');
            const reasonSelect = document.getElementById('reason_select');
            const actionSelect = document.getElementById('action_select');
            const partSelect = document.getElementById('part_select');
            
            if (problemSelect) {
                problemSelect.innerHTML = '<option value="">-- Pilih System terlebih dahulu --</option>';
                problemSelect.disabled = true;
                problemSelect.classList.add('bg-gray-100');
            }
            if (reasonSelect) {
                reasonSelect.innerHTML = '<option value="">-- Pilih Problem terlebih dahulu --</option>';
                reasonSelect.disabled = true;
                reasonSelect.classList.add('bg-gray-100');
            }
            if (actionSelect) {
                actionSelect.innerHTML = '<option value="">-- Pilih Reason terlebih dahulu --</option>';
                actionSelect.disabled = true;
                actionSelect.classList.add('bg-gray-100');
            }
            if (partSelect) {
                partSelect.innerHTML = '<option value="">-- Pilih System terlebih dahulu --</option>';
                partSelect.disabled = true;
                partSelect.classList.add('bg-gray-100');
            }
        }
    };
    
    // Clear machine
    if (clearMachine) {
        clearMachine.addEventListener('click', function() {
            if (machineIdInput) machineIdInput.value = '';
            if (machineSearch) machineSearch.value = '';
            if (selectedMachine) selectedMachine.classList.add('hidden');
            
            // Clear machine details
            const typeMachine = document.getElementById('typeMachine');
            const modelMachine = document.getElementById('modelMachine');
            const brandMachine = document.getElementById('brandMachine');
            const plant = document.getElementById('plant');
            const process = document.getElementById('process');
            const line = document.getElementById('line');
            const roomName = document.getElementById('roomName');
            
            if (typeMachine) typeMachine.value = '';
            if (modelMachine) modelMachine.value = '';
            if (brandMachine) brandMachine.value = '';
            if (plant) plant.value = '';
            if (process) process.value = '';
            if (line) line.value = '';
            if (roomName) roomName.value = '';
        });
    }
    
    // Close machine dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (machineSearch && machineDropdown) {
            const isClickInside = machineSearch.contains(e.target) || machineDropdown.contains(e.target);
            if (!isClickInside) {
                machineDropdown.classList.add('hidden');
            }
        }
    });
    
    // Barcode scanner functions
    if (scanBarcodeBtn && barcodeModal) {
        scanBarcodeBtn.addEventListener('click', function() {
            barcodeModal.classList.remove('hidden');
        });
    }
    
    // Manual barcode input
    if (manualBarcodeInput) {
        manualBarcodeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const barcodeValue = this.value.trim();
                if (barcodeValue) {
                    // Find machine by ID Machine
                    const machine = machines.find(m => (m.idMachine && (m.idMachine === barcodeValue || m.idMachine.toLowerCase() === barcodeValue.toLowerCase())));
                    if (machine) {
                        // Get system_ids from the machine data
                        const systemIds = machine.system_ids || [];
                        selectMachine(
                            machine.id, 
                            machine.idMachine, 
                            machine.typeMachine, 
                            machine.modelMachine, 
                            machine.brandMachine,
                            machine.plant,
                            machine.process,
                            machine.line,
                            machine.roomName,
                            machine.kodeRoom,
                            systemIds
                        );
                        closeBarcodeModal();
                    } else {
                        alert('Machine dengan ID "' + barcodeValue + '" tidak ditemukan.');
                    }
                }
            }
        });
    }
    
    // Also allow direct paste/input in search field for barcode
    if (machineSearch) {
        machineSearch.addEventListener('paste', function(e) {
            setTimeout(() => {
                const pastedValue = this.value.trim();
                if (pastedValue) {
                    const machine = machines.find(m => (m.idMachine && (m.idMachine === pastedValue || m.idMachine.toLowerCase() === pastedValue.toLowerCase())));
                    if (machine) {
                        // Get system_ids from the machine data
                        const systemIds = machine.system_ids || [];
                        selectMachine(
                            machine.id, 
                            machine.idMachine, 
                            machine.typeMachine, 
                            machine.modelMachine, 
                            machine.brandMachine,
                            machine.plant,
                            machine.process,
                            machine.line,
                            machine.roomName,
                            machine.kodeRoom,
                            systemIds
                        );
                    }
                }
            }, 100);
        });
    }
});

// Barcode scanner functions (global functions)
let stream = null;
let scanning = false;

function closeBarcodeModal() {
    const barcodeModal = document.getElementById('barcode_modal');
    const manualBarcodeInput = document.getElementById('manual_barcode_input');
    if (barcodeModal) barcodeModal.classList.add('hidden');
    stopBarcodeScan();
    if (manualBarcodeInput) manualBarcodeInput.value = '';
}

function startBarcodeScan() {
    const barcodeVideo = document.getElementById('barcode_video');
    const barcodeCanvas = document.getElementById('barcode_canvas');
    
    scanning = true;
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            facingMode: 'environment' // Use back camera on mobile
        } 
    })
    .then(function(mediaStream) {
        stream = mediaStream;
        if (barcodeVideo) {
            barcodeVideo.srcObject = mediaStream;
            barcodeVideo.play();
        }
        scanBarcode();
    })
    .catch(function(err) {
        console.error('Error accessing camera:', err);
        alert('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.');
    });
}

function stopBarcodeScan() {
    const barcodeVideo = document.getElementById('barcode_video');
    scanning = false;
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    if (barcodeVideo) barcodeVideo.srcObject = null;
}

function scanBarcode() {
    const barcodeVideo = document.getElementById('barcode_video');
    const barcodeCanvas = document.getElementById('barcode_canvas');
    
    if (!scanning || !barcodeVideo || !barcodeCanvas) return;
    
    const context = barcodeCanvas.getContext('2d');
    barcodeCanvas.width = barcodeVideo.videoWidth;
    barcodeCanvas.height = barcodeVideo.videoHeight;
    context.drawImage(barcodeVideo, 0, 0, barcodeCanvas.width, barcodeCanvas.height);
    
    // Try to read barcode using jsQR library (if available) or manual input
    // For now, we'll use manual input as fallback
    requestAnimationFrame(scanBarcode);
}

// System, Problem, Reason, Action, and Part filtering functionality
const systemSelect = document.getElementById('system_select');
const problemSelect = document.getElementById('problem_select');
const reasonSelect = document.getElementById('reason_select');
const actionSelect = document.getElementById('action_select');
const partSelect = document.getElementById('part_select');
const problemDowntime = document.getElementById('problemDowntime');
const reasonDowntime = document.getElementById('reasonDowntime');
const actionDowtime = document.getElementById('actionDowtime');
const partField = document.getElementById('Part');

// Filter problems based on selected system (client-side filtering)
if (systemSelect) {
    systemSelect.addEventListener('change', function() {
        const selectedSystemId = this.value;
        currentSystemId = selectedSystemId; // Store current system ID for filter
        
        // If search filter is active, re-apply it
        if (problemSearchInput && problemSearchInput.value.trim() !== '') {
            filterProblemReasonAction(problemSearchInput.value);
            return; // Don't do normal filtering if search is active
        }
        
        // Clear problem, reason, action, part, and hidden fields
        problemSelect.innerHTML = '<option value="">-- Pilih Problem --</option>';
        problemSelect.value = '';
        reasonSelect.innerHTML = '<option value="">-- Pilih Problem terlebih dahulu --</option>';
        reasonSelect.value = '';
        reasonSelect.disabled = true;
        reasonSelect.classList.add('bg-gray-100');
        actionSelect.innerHTML = '<option value="">-- Pilih Reason terlebih dahulu --</option>';
        actionSelect.value = '';
        actionSelect.disabled = true;
        actionSelect.classList.add('bg-gray-100');
        partSelect.innerHTML = '<option value="">-- Pilih Part --</option>';
        partSelect.value = '';
        partSelect.disabled = true;
        partSelect.classList.add('bg-gray-100');
        if (problemDowntime) problemDowntime.value = '';
        if (reasonDowntime) reasonDowntime.value = '';
        if (actionDowtime) actionDowtime.value = '';
        if (partField) partField.value = '';
        
        if (!selectedSystemId) {
            // Disable problem select if no system selected
            problemSelect.disabled = true;
            problemSelect.classList.add('bg-gray-100');
            return;
        }
        
        // Filter problems that belong to the selected system
        const filteredProblems = problems.filter(problem => {
            return problem.system_ids && problem.system_ids.includes(selectedSystemId);
        });
        
        if (filteredProblems.length === 0) {
            problemSelect.innerHTML = '<option value="">-- Tidak ada problem untuk system ini --</option>';
            problemSelect.disabled = true;
            problemSelect.classList.add('bg-gray-100');
            return;
        }
        
        // Populate problem dropdown (show only problem_header)
        filteredProblems.forEach(problem => {
            const option = document.createElement('option');
            option.value = problem.id;
            option.textContent = problem.problem_header || problem.name; // Show problem_header, fallback to name if header is empty
            option.setAttribute('data-problem-name', problem.name);
            problemSelect.appendChild(option);
        });
        
        // Enable problem select
        problemSelect.disabled = false;
        problemSelect.classList.remove('bg-gray-100');
        
        // Filter and populate part dropdown based on selected system
        // PartErp uses 'category' column which stores nama_sistem (string), not system_ids
        const selectedSystem = systems.find(s => s.id === selectedSystemId);
        const selectedSystemName = selectedSystem ? selectedSystem.nama_sistem : '';
        
        const filteredParts = parts.filter(part => {
            // Compare part.category (nama_sistem) with selected system's nama_sistem
            return part.category && part.category === selectedSystemName;
        });
        
        if (filteredParts.length === 0) {
            partSelect.innerHTML = '<option value="">-- Tidak ada part untuk system ini --</option>';
            partSelect.disabled = true;
            partSelect.classList.add('bg-gray-100');
        } else {
            // Populate part dropdown (show name and description/specification only, without part_number)
            filteredParts.forEach(part => {
                const option = document.createElement('option');
                option.value = part.id;
                // Display: name - description (if description exists)
                const displayText = part.description 
                    ? `${part.name} - ${part.description}` 
                    : part.name;
                option.textContent = displayText;
                option.setAttribute('data-part-name', part.name);
                option.setAttribute('data-part-description', part.description || '');
                partSelect.appendChild(option);
            });
            
            // Enable part select
            partSelect.disabled = false;
            partSelect.classList.remove('bg-gray-100');
        }
    });
}

// Auto-fill problemDowntime and enable reason when problem is selected
if (problemSelect) {
    problemSelect.addEventListener('change', function() {
        const selectedProblemId = this.value;
        
        // Clear reason, action, and hidden fields
        reasonSelect.innerHTML = '<option value="">-- Pilih Reason --</option>';
        reasonSelect.value = '';
        reasonSelect.disabled = true;
        reasonSelect.classList.add('bg-gray-100');
        actionSelect.innerHTML = '<option value="">-- Pilih Reason terlebih dahulu --</option>';
        actionSelect.value = '';
        actionSelect.disabled = true;
        actionSelect.classList.add('bg-gray-100');
        if (reasonDowntime) reasonDowntime.value = '';
        if (actionDowtime) actionDowtime.value = '';
        
        if (!selectedProblemId) {
            if (problemDowntime) problemDowntime.value = '';
            return;
        }
        
        // Find the selected problem
        const selectedProblem = problems.find(p => p.id === selectedProblemId);
        
        if (selectedProblem) {
            // Auto-fill problemDowntime with problem name
            if (problemDowntime) {
                problemDowntime.value = selectedProblem.name;
            }
            
            // Populate reason dropdown (all reasons available)
            reasons.forEach(reason => {
                const option = document.createElement('option');
                option.value = reason.id;
                option.textContent = reason.name;
                option.setAttribute('data-reason-name', reason.name);
                reasonSelect.appendChild(option);
            });
            
            // Enable reason select
            reasonSelect.disabled = false;
            reasonSelect.classList.remove('bg-gray-100');
        }
    });
}

// Auto-fill reasonDowntime and enable action when reason is selected
if (reasonSelect) {
    reasonSelect.addEventListener('change', function() {
        const selectedReasonId = this.value;
        
        // Clear action and hidden field
        actionSelect.innerHTML = '<option value="">-- Pilih Action --</option>';
        actionSelect.value = '';
        actionSelect.disabled = true;
        actionSelect.classList.add('bg-gray-100');
        if (actionDowtime) actionDowtime.value = '';
        
        if (!selectedReasonId) {
            if (reasonDowntime) reasonDowntime.value = '';
            return;
        }
        
        // Find the selected reason
        const selectedReason = reasons.find(r => r.id === selectedReasonId);
        
        if (selectedReason) {
            // Auto-fill reasonDowntime with reason name
            if (reasonDowntime) {
                reasonDowntime.value = selectedReason.name;
            }
            
            // Populate action dropdown (all actions available)
            actions.forEach(action => {
                const option = document.createElement('option');
                option.value = action.id;
                option.textContent = action.name;
                option.setAttribute('data-action-name', action.name);
                actionSelect.appendChild(option);
            });
            
            // Enable action select
            actionSelect.disabled = false;
            actionSelect.classList.remove('bg-gray-100');
        }
    });
}

// Auto-fill actionDowtime when action is selected
if (actionSelect) {
    actionSelect.addEventListener('change', function() {
        const selectedActionId = this.value;
        
        if (!selectedActionId) {
            if (actionDowtime) actionDowtime.value = '';
            return;
        }
        
        // Find the selected action
        const selectedAction = actions.find(a => a.id === selectedActionId);
        
        if (selectedAction && actionDowtime) {
            // Auto-fill actionDowtime with action name
            actionDowtime.value = selectedAction.name;
        }
    });
}

// Auto-fill Part field when part is selected
if (partSelect) {
    partSelect.addEventListener('change', function() {
        const selectedPartId = this.value;
        
        if (!selectedPartId) {
            if (partField) partField.value = '';
            return;
        }
        
        // Find the selected part
        const selectedPart = parts.find(p => p.id === selectedPartId);
        
        if (selectedPart && partField) {
            // Auto-fill Part with part name
            partField.value = selectedPart.name;
        }
    });
}

// Problem search filter
const problemSearchInput = document.getElementById('problem_search');
const actionSuggestionsDiv = document.getElementById('action_suggestions');
let currentSystemId = null; // Store current selected system ID
let isFiltering = false; // Flag to prevent normal filtering when search is active
let selectedActionIndex = -1; // For keyboard navigation

// Function to filter dropdowns based on search text
function filterProblemReasonAction(searchText) {
    const searchLower = (searchText || '').toLowerCase().trim();
    
    if (!searchText || searchLower === '') {
        // If search is empty, restore normal filtering based on system
        isFiltering = false;
        if (currentSystemId && systemSelect && systemSelect.value) {
            // Re-trigger system change to restore normal filtering
            const systemValue = systemSelect.value;
            systemSelect.value = '';
            setTimeout(() => {
                systemSelect.value = systemValue;
                systemSelect.dispatchEvent(new Event('change'));
            }, 10);
        } else if (systemSelect && !systemSelect.value) {
            // If no system selected, clear all dropdowns
            if (problemSelect) {
                problemSelect.innerHTML = '<option value="">-- Pilih System terlebih dahulu --</option>';
                problemSelect.disabled = true;
                problemSelect.classList.add('bg-gray-100');
            }
            if (reasonSelect) {
                reasonSelect.innerHTML = '<option value="">-- Pilih Problem terlebih dahulu --</option>';
                reasonSelect.disabled = true;
                reasonSelect.classList.add('bg-gray-100');
            }
            if (actionSelect) {
                actionSelect.innerHTML = '<option value="">-- Pilih Reason terlebih dahulu --</option>';
                actionSelect.disabled = true;
                actionSelect.classList.add('bg-gray-100');
            }
        }
        return;
    }
    
    isFiltering = true;
    
    // Find matching problems (by name or problem_header)
    const matchingProblems = problems.filter(p => {
        const nameMatch = (p.name || '').toLowerCase().includes(searchLower);
        const headerMatch = (p.problem_header || '').toLowerCase().includes(searchLower);
        return nameMatch || headerMatch;
    });
    
    // Find matching reasons (by name)
    const matchingReasons = reasons.filter(r => {
        return (r.name || '').toLowerCase().includes(searchLower);
    });
    
    // Find matching actions (by name)
    const matchingActions = actions.filter(a => {
        return (a.name || '').toLowerCase().includes(searchLower);
    });
    
    // Determine what to show based on matches
    let problemsToShow = [];
    let reasonsToShow = [];
    let actionsToShow = [];
    
    if (matchingActions.length > 0) {
        // If actions match, show all problems (filtered by system if system selected) and all reasons
        problemsToShow = currentSystemId 
            ? problems.filter(p => p.system_ids && p.system_ids.includes(currentSystemId))
            : problems;
        reasonsToShow = reasons; // All reasons (because action can be selected after any reason)
        actionsToShow = matchingActions;
    } else if (matchingReasons.length > 0) {
        // If reasons match, show all problems (filtered by system if system selected)
        problemsToShow = currentSystemId 
            ? problems.filter(p => p.system_ids && p.system_ids.includes(currentSystemId))
            : problems;
        reasonsToShow = matchingReasons;
        actionsToShow = []; // Clear actions
    } else if (matchingProblems.length > 0) {
        // Only problems match
        problemsToShow = matchingProblems.filter(p => {
            // Still respect system filter if system is selected
            if (currentSystemId) {
                return p.system_ids && p.system_ids.includes(currentSystemId);
            }
            return true;
        });
        reasonsToShow = []; // Clear reasons
        actionsToShow = []; // Clear actions
    } else {
        // No matches
        problemsToShow = [];
        reasonsToShow = [];
        actionsToShow = [];
    }
    
    // Update problem dropdown
    if (problemSelect) {
        problemSelect.innerHTML = '<option value="">-- Pilih Problem --</option>';
        problemsToShow.forEach(problem => {
            const option = document.createElement('option');
            option.value = problem.id;
            option.textContent = problem.problem_header || problem.name;
            option.setAttribute('data-problem-name', problem.name);
            option.setAttribute('data-problem-header', problem.problem_header || '');
            problemSelect.appendChild(option);
        });
        
        if (problemsToShow.length > 0) {
            problemSelect.disabled = false;
            problemSelect.classList.remove('bg-gray-100');
        } else {
            problemSelect.disabled = true;
            problemSelect.classList.add('bg-gray-100');
        }
    }
    
    // Update reason dropdown
    if (reasonSelect) {
        reasonSelect.innerHTML = '<option value="">-- Pilih Reason --</option>';
        if (problemsToShow.length > 0 || matchingReasons.length > 0 || matchingActions.length > 0) {
            reasonsToShow.forEach(reason => {
                const option = document.createElement('option');
                option.value = reason.id;
                option.textContent = reason.name;
                option.setAttribute('data-reason-name', reason.name);
                reasonSelect.appendChild(option);
            });
            
            if (reasonsToShow.length > 0) {
                reasonSelect.disabled = false;
                reasonSelect.classList.remove('bg-gray-100');
            } else {
                reasonSelect.disabled = true;
                reasonSelect.classList.add('bg-gray-100');
            }
        } else {
            reasonSelect.disabled = true;
            reasonSelect.classList.add('bg-gray-100');
        }
    }
    
    // Update action dropdown
    if (actionSelect) {
        actionSelect.innerHTML = '<option value="">-- Pilih Action --</option>';
        if (reasonsToShow.length > 0 || matchingActions.length > 0) {
            actionsToShow.forEach(action => {
                const option = document.createElement('option');
                option.value = action.id;
                option.textContent = action.name;
                option.setAttribute('data-action-name', action.name);
                actionSelect.appendChild(option);
            });
            
            if (actionsToShow.length > 0) {
                actionSelect.disabled = false;
                actionSelect.classList.remove('bg-gray-100');
            } else {
                actionSelect.disabled = true;
                actionSelect.classList.add('bg-gray-100');
            }
        } else {
            actionSelect.disabled = true;
            actionSelect.classList.add('bg-gray-100');
        }
    }
}

// Function to show action suggestions
function showActionSuggestions(searchText) {
    if (!actionSuggestionsDiv || !searchText || searchText.trim() === '') {
        actionSuggestionsDiv.classList.add('hidden');
        return;
    }
    
    const searchLower = searchText.toLowerCase().trim();
    const matchingActions = actions.filter(a => {
        const nameMatch = (a.name || '').toLowerCase().includes(searchLower);
        const systemMatch = (a.system_name || '').toLowerCase().includes(searchLower);
        const problemMatch = (a.problem_name || '').toLowerCase().includes(searchLower) || 
                           (a.problem_header || '').toLowerCase().includes(searchLower);
        const reasonMatch = (a.reason_name || '').toLowerCase().includes(searchLower);
        return nameMatch || systemMatch || problemMatch || reasonMatch;
    });
    
    if (matchingActions.length === 0) {
        actionSuggestionsDiv.classList.add('hidden');
        return;
    }
    
    // Build suggestions HTML
    let suggestionsHTML = '';
    matchingActions.slice(0, 10).forEach((action, index) => {
        const displayText = `${action.name} (${action.system_name || 'N/A'} - ${action.problem_header || action.problem_name || 'N/A'} - ${action.reason_name || 'N/A'})`;
        suggestionsHTML += `
            <div class="action-suggestion-item px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 ${index === selectedActionIndex ? 'bg-blue-100' : ''}" 
                 data-action-id="${action.id}"
                 data-system-id="${action.system_id || ''}"
                 data-system-name="${action.system_name || ''}"
                 data-problem-id="${action.problem_id || ''}"
                 data-problem-name="${action.problem_name || ''}"
                 data-problem-header="${action.problem_header || ''}"
                 data-reason-id="${action.reason_id || ''}"
                 data-reason-name="${action.reason_name || ''}"
                 data-action-name="${action.name || ''}">
                <div class="font-semibold text-sm text-gray-900">${action.name}</div>
                <div class="text-xs text-gray-500">${action.system_name || 'N/A'} → ${action.problem_header || action.problem_name || 'N/A'} → ${action.reason_name || 'N/A'}</div>
            </div>
        `;
    });
    
    actionSuggestionsDiv.innerHTML = suggestionsHTML;
    actionSuggestionsDiv.classList.remove('hidden');
    
    // Add click event listeners to suggestions
    actionSuggestionsDiv.querySelectorAll('.action-suggestion-item').forEach(item => {
        item.addEventListener('click', function() {
            selectActionFromSuggestion(this);
        });
    });
}

// Function to select action from suggestion
function selectActionFromSuggestion(element) {
    const actionId = element.getAttribute('data-action-id');
    const systemId = element.getAttribute('data-system-id');
    const systemName = element.getAttribute('data-system-name');
    const problemId = element.getAttribute('data-problem-id');
    const problemName = element.getAttribute('data-problem-name');
    const problemHeader = element.getAttribute('data-problem-header');
    const reasonId = element.getAttribute('data-reason-id');
    const reasonName = element.getAttribute('data-reason-name');
    const actionName = element.getAttribute('data-action-name');
    
    // Hide suggestions
    actionSuggestionsDiv.classList.add('hidden');
    problemSearchInput.value = '';
    selectedActionIndex = -1;
    
    // Auto-fill System (enable first if machine is selected)
    if (systemSelect && systemId) {
        // Enable system select if machine is already selected
        if (document.getElementById('idMachine') && document.getElementById('idMachine').value) {
            systemSelect.disabled = false;
            systemSelect.classList.remove('bg-gray-100');
        }
        
        // Check if system option exists
        const systemOption = Array.from(systemSelect.options).find(opt => opt.value === systemId);
        if (systemOption) {
            systemSelect.value = systemId;
            currentSystemId = systemId;
            systemSelect.dispatchEvent(new Event('change'));
        }
    }
    
    // Wait a bit for system change to populate problems, then set problem
    setTimeout(() => {
        if (problemSelect && problemId) {
            // Check if problem exists in dropdown
            const problemOption = Array.from(problemSelect.options).find(opt => opt.value === problemId);
            if (problemOption) {
                problemSelect.value = problemId;
                problemSelect.dispatchEvent(new Event('change'));
                
                // Auto-fill Problem Downtime
                const problemDowntimeField = document.getElementById('problemDowntime');
                if (problemDowntimeField) {
                    problemDowntimeField.value = problemHeader || problemName;
                }
                
                // Wait a bit for problem change to populate reasons, then set reason
                setTimeout(() => {
                    if (reasonSelect && reasonId) {
                        // Check if reason exists in dropdown
                        const reasonOption = Array.from(reasonSelect.options).find(opt => opt.value === reasonId);
                        if (reasonOption) {
                            reasonSelect.value = reasonId;
                            reasonSelect.dispatchEvent(new Event('change'));
                            
                            // Auto-fill Reason Downtime
                            const reasonDowntimeField = document.getElementById('reasonDowntime');
                            if (reasonDowntimeField) {
                                reasonDowntimeField.value = reasonName;
                            }
                            
                            // Wait a bit for reason change to populate actions, then set action
                            setTimeout(() => {
                                if (actionSelect && actionId) {
                                    // Check if action exists in dropdown
                                    const actionOption = Array.from(actionSelect.options).find(opt => opt.value === actionId);
                                    if (actionOption) {
                                        actionSelect.value = actionId;
                                        actionSelect.dispatchEvent(new Event('change'));
                                        
                                        // Auto-fill Action Downtime
                                        const actionDowntimeField = document.getElementById('actionDowtime');
                                        if (actionDowntimeField) {
                                            actionDowntimeField.value = actionName;
                                        }
                                    }
                                }
                            }, 300);
                        }
                    }
                }, 300);
            }
        }
    }, 300);
}

// Add event listener for problem search
if (problemSearchInput) {
    problemSearchInput.addEventListener('input', function() {
        const searchText = this.value;
        
        // Show action suggestions if text is entered
        showActionSuggestions(searchText);
        
        // Also filter dropdowns
        filterProblemReasonAction(searchText);
    });
    
    // Keyboard navigation for suggestions
    problemSearchInput.addEventListener('keydown', function(e) {
        const suggestions = actionSuggestionsDiv.querySelectorAll('.action-suggestion-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedActionIndex = Math.min(selectedActionIndex + 1, suggestions.length - 1);
            suggestions[selectedActionIndex]?.scrollIntoView({ block: 'nearest' });
            showActionSuggestions(this.value); // Re-render to highlight
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedActionIndex = Math.max(selectedActionIndex - 1, -1);
            if (selectedActionIndex >= 0) {
                suggestions[selectedActionIndex]?.scrollIntoView({ block: 'nearest' });
                showActionSuggestions(this.value); // Re-render to highlight
            }
        } else if (e.key === 'Enter' && selectedActionIndex >= 0 && suggestions[selectedActionIndex]) {
            e.preventDefault();
            selectActionFromSuggestion(suggestions[selectedActionIndex]);
            selectedActionIndex = -1;
        } else if (e.key === 'Escape') {
            this.value = '';
            actionSuggestionsDiv.classList.add('hidden');
            filterProblemReasonAction('');
            selectedActionIndex = -1;
        }
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!problemSearchInput.contains(e.target) && !actionSuggestionsDiv.contains(e.target)) {
            actionSuggestionsDiv.classList.add('hidden');
            selectedActionIndex = -1;
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // If old value exists for system, trigger change to populate problems
    if (systemSelect && systemSelect.value) {
        currentSystemId = systemSelect.value;
        systemSelect.dispatchEvent(new Event('change'));
        
        // If old value exists for problem, set it
        const oldProblemId = '{{ old("problem_select") }}';
        if (oldProblemId && problemSelect) {
            setTimeout(() => {
                problemSelect.value = oldProblemId;
                problemSelect.dispatchEvent(new Event('change'));
                
                // If old value exists for reason, set it
                const oldReasonId = '{{ old("reason_select") }}';
                if (oldReasonId && reasonSelect) {
                    setTimeout(() => {
                        reasonSelect.value = oldReasonId;
                        reasonSelect.dispatchEvent(new Event('change'));
                        
                        // If old value exists for action, set it
                        const oldActionId = '{{ old("action_select") }}';
                        if (oldActionId && actionSelect) {
                            setTimeout(() => {
                                actionSelect.value = oldActionId;
                                actionSelect.dispatchEvent(new Event('change'));
                            }, 100);
                        }
                    }, 100);
                }
            }, 100);
        }
    }
});
</script>
@endsection

