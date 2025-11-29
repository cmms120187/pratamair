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
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Problem Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="problemDowntime" class="block text-sm font-semibold text-gray-700 mb-2">Problem Downtime <span class="text-red-500">*</span></label>
                        <input type="text" name="problemDowntime" id="problemDowntime" value="{{ old('problemDowntime') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('problemDowntime') border-red-500 @enderror">
                        @error('problemDowntime')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="Problem_MM" class="block text-sm font-semibold text-gray-700 mb-2">Problem MM</label>
                        <input type="text" name="Problem_MM" id="Problem_MM" value="{{ old('Problem_MM') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('Problem_MM') border-red-500 @enderror">
                        @error('Problem_MM')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="reasonDowntime" class="block text-sm font-semibold text-gray-700 mb-2">Reason Downtime <span class="text-red-500">*</span></label>
                        <input type="text" name="reasonDowntime" id="reasonDowntime" value="{{ old('reasonDowntime') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('reasonDowntime') border-red-500 @enderror">
                        @error('reasonDowntime')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="actionDowtime" class="block text-sm font-semibold text-gray-700 mb-2">Action Downtime <span class="text-red-500">*</span></label>
                        <input type="text" name="actionDowtime" id="actionDowtime" value="{{ old('actionDowtime') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('actionDowtime') border-red-500 @enderror">
                        @error('actionDowtime')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="Part" class="block text-sm font-semibold text-gray-700 mb-2">Part</label>
                        <input type="text" name="Part" id="Part" value="{{ old('Part') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('Part') border-red-500 @enderror">
                        @error('Part')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="groupProblem" class="block text-sm font-semibold text-gray-700 mb-2">Group Problem <span class="text-red-500">*</span></label>
                        <input type="text" name="groupProblem" id="groupProblem" value="{{ old('groupProblem') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('groupProblem') border-red-500 @enderror">
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

// DOM elements - Machine
const machineSearch = document.getElementById('machine_search');
const machineIdInput = document.getElementById('idMachine');
const machineDropdown = document.getElementById('machine_dropdown');
const selectedMachine = document.getElementById('selected_machine');
const selectedMachineInfo = document.getElementById('selected_machine_info');
const clearMachine = document.getElementById('clear_machine');
const scanBarcodeBtn = document.getElementById('scan_barcode_btn');
const barcodeModal = document.getElementById('barcode_modal');
const barcodeVideo = document.getElementById('barcode_video');
const barcodeCanvas = document.getElementById('barcode_canvas');
const manualBarcodeInput = document.getElementById('manual_barcode_input');

// Machine detail fields
const typeMachine = document.getElementById('typeMachine');
const modelMachine = document.getElementById('modelMachine');
const brandMachine = document.getElementById('brandMachine');
const plant = document.getElementById('plant');
const process = document.getElementById('process');
const line = document.getElementById('line');
const roomName = document.getElementById('roomName');

let stream = null;
let scanning = false;

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

// Machine auto-complete functionality
machineSearch.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();
    
    if (searchTerm.length === 0) {
        machineDropdown.classList.add('hidden');
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
        return;
    }
    
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
            selectMachine(machineId, machineIdMachine, machineType, machineModel, machineBrand, machinePlant, machineProcess, machineLine, machineRoom, machineKodeRoom);
        });
    });
    
    machineDropdown.classList.remove('hidden');
});

// Select machine
function selectMachine(id, idMachine, type, model, brand, plantVal, processVal, lineVal, roomVal, kodeRoomVal) {
    machineIdInput.value = idMachine || '';
    machineSearch.value = idMachine || '';
    selectedMachineInfo.innerHTML = `
        <div class="font-semibold">${idMachine || ''}</div>
        <div class="text-xs text-gray-600">${type || ''} - ${brand || ''} ${model || ''}</div>
        <div class="text-xs text-gray-500">${plantVal || ''} / ${processVal || ''} / ${lineVal || ''} / ${roomVal || ''}</div>
    `;
    selectedMachine.classList.remove('hidden');
    machineDropdown.classList.add('hidden');
    machineSearch.blur();
    
    // Auto-fill machine details
    if (typeMachine) typeMachine.value = type || '';
    if (modelMachine) modelMachine.value = model || '';
    if (brandMachine) brandMachine.value = brand || '';
    
    // Auto-fill location
    if (plant) plant.value = plantVal || '';
    if (process) process.value = processVal || '';
    if (line) line.value = lineVal || '';
    if (roomName) roomName.value = roomVal || '';
    
    // Set kode_room if hidden field exists
    const kodeRoomInput = document.getElementById('kode_room');
    if (kodeRoomInput) {
        kodeRoomInput.value = kodeRoomVal || '';
    }
}

// Clear machine
clearMachine.addEventListener('click', function() {
    machineIdInput.value = '';
    machineSearch.value = '';
    selectedMachine.classList.add('hidden');
    
    // Clear machine details
    if (typeMachine) typeMachine.value = '';
    if (modelMachine) modelMachine.value = '';
    if (brandMachine) brandMachine.value = '';
    
    // Clear location
    if (plant) plant.value = '';
    if (process) process.value = '';
    if (line) line.value = '';
    if (roomName) roomName.value = '';
});

// Close machine dropdown when clicking outside
document.addEventListener('click', function(e) {
    const isClickInside = machineSearch.contains(e.target) || machineDropdown.contains(e.target);
    if (!isClickInside) {
        machineDropdown.classList.add('hidden');
    }
});

// Barcode scanner functions
scanBarcodeBtn.addEventListener('click', function() {
    barcodeModal.classList.remove('hidden');
});

function closeBarcodeModal() {
    barcodeModal.classList.add('hidden');
    stopBarcodeScan();
    manualBarcodeInput.value = '';
}

function startBarcodeScan() {
    scanning = true;
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            facingMode: 'environment' // Use back camera on mobile
        } 
    })
    .then(function(mediaStream) {
        stream = mediaStream;
        barcodeVideo.srcObject = mediaStream;
        barcodeVideo.play();
        scanBarcode();
    })
    .catch(function(err) {
        console.error('Error accessing camera:', err);
        alert('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.');
    });
}

function stopBarcodeScan() {
    scanning = false;
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    barcodeVideo.srcObject = null;
}

function scanBarcode() {
    if (!scanning) return;
    
    const context = barcodeCanvas.getContext('2d');
    barcodeCanvas.width = barcodeVideo.videoWidth;
    barcodeCanvas.height = barcodeVideo.videoHeight;
    context.drawImage(barcodeVideo, 0, 0, barcodeCanvas.width, barcodeCanvas.height);
    
    // Try to read barcode using jsQR library (if available) or manual input
    // For now, we'll use manual input as fallback
    requestAnimationFrame(scanBarcode);
}

// Manual barcode input
manualBarcodeInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const barcodeValue = this.value.trim();
        if (barcodeValue) {
            // Find machine by ID Machine
            const machine = machines.find(m => (m.idMachine && (m.idMachine === barcodeValue || m.idMachine.toLowerCase() === barcodeValue.toLowerCase())));
            if (machine) {
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
                    machine.kodeRoom
                );
                closeBarcodeModal();
            } else {
                alert('Machine dengan ID "' + barcodeValue + '" tidak ditemukan.');
            }
        }
    }
});

// Also allow direct paste/input in search field for barcode
machineSearch.addEventListener('paste', function(e) {
    setTimeout(() => {
        const pastedValue = this.value.trim();
        if (pastedValue) {
            const machine = machines.find(m => (m.idMachine && (m.idMachine === pastedValue || m.idMachine.toLowerCase() === pastedValue.toLowerCase())));
            if (machine) {
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
                    machine.kodeRoom
                );
            }
        }
    }, 100);
});
</script>
@endsection

