@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-6xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Edit Downtime ERP2</h1>
            <p class="text-sm text-gray-600">Update downtime ERP2 information</p>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        <form action="{{ route('downtime-erp2.update', $downtimeErp2->id) }}" method="POST">
            @csrf
            @method('PUT')
            @if(isset($page))
                <input type="hidden" name="page" value="{{ $page }}">
            @endif
            
            <!-- Basic Information -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Basic Information</h3>
                
                <div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <label for="room_erp_select" class="block text-sm font-semibold text-gray-700 mb-2">
                        Pilih Room ERP (untuk auto-fill Plant/Process/Line/Room)
                    </label>
                    <select id="room_erp_select" 
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        <option value="">-- Pilih Room ERP atau isi manual --</option>
                        @foreach($roomErps as $roomErp)
                            <option value="{{ $roomErp->id }}" 
                                    data-plant="{{ $roomErp->plant_name ?? '' }}"
                                    data-process="{{ $roomErp->process_name ?? '' }}"
                                    data-line="{{ $roomErp->line_name ?? '' }}"
                                    data-room="{{ $roomErp->name ?? '' }}"
                                    data-kode-room="{{ $roomErp->kode_room ?? '' }}"
                                    {{ old('room_erp_id') == $roomErp->id || 
                                       ($downtimeErp2->plant == $roomErp->plant_name && 
                                        $downtimeErp2->process == $roomErp->process_name && 
                                        $downtimeErp2->line == $roomErp->line_name && 
                                        $downtimeErp2->roomName == $roomErp->name) ? 'selected' : '' }}>
                                {{ $roomErp->kode_room ? $roomErp->kode_room . ' - ' : '' }}{{ $roomErp->name }}
                                @if($roomErp->plant_name)
                                    ({{ $roomErp->plant_name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Pilih room untuk mengisi otomatis field Plant, Process, Line, dan Room Name</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">Date <span class="text-red-500">*</span></label>
                        <input type="date" name="date" id="date" value="{{ old('date', $downtimeErp2->date) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('date') border-red-500 @enderror">
                        @error('date')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="plant" class="block text-sm font-semibold text-gray-700 mb-2">Plant <span class="text-red-500">*</span></label>
                        <input type="text" name="plant" id="plant" value="{{ old('plant', $downtimeErp2->plant) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('plant') border-red-500 @enderror">
                        @error('plant')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="process" class="block text-sm font-semibold text-gray-700 mb-2">Process <span class="text-red-500">*</span></label>
                        <input type="text" name="process" id="process" value="{{ old('process', $downtimeErp2->process) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('process') border-red-500 @enderror">
                        @error('process')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="line" class="block text-sm font-semibold text-gray-700 mb-2">Line <span class="text-red-500">*</span></label>
                        <input type="text" name="line" id="line" value="{{ old('line', $downtimeErp2->line) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('line') border-red-500 @enderror">
                        @error('line')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="roomName" class="block text-sm font-semibold text-gray-700 mb-2">Room Name <span class="text-red-500">*</span></label>
                        <input type="text" name="roomName" id="roomName" value="{{ old('roomName', $downtimeErp2->roomName) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('roomName') border-red-500 @enderror">
                        @error('roomName')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                
                <!-- Hidden field for kode_room -->
                <input type="hidden" name="kode_room" id="kode_room" value="{{ old('kode_room', $downtimeErp2->kode_room) }}">
                <div class="mt-4">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="include_oee" 
                               id="include_oee" 
                               value="1"
                               {{ old('include_oee', $downtimeErp2->include_oee) ? 'checked' : '' }}
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
                        <label for="idMachine" class="block text-sm font-semibold text-gray-700 mb-2">ID Machine <span class="text-red-500">*</span></label>
                        <input type="text" name="idMachine" id="idMachine" value="{{ old('idMachine', $downtimeErp2->idMachine) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('idMachine') border-red-500 @enderror">
                        @error('idMachine')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="group_id" class="block text-sm font-semibold text-gray-700 mb-2">Group</label>
                        <select name="group_id" 
                                id="group_id" 
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('group_id') border-red-500 @enderror">
                            <option value="">Pilih Group</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('group_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <!-- Display systems information -->
                        <div id="systems_info" class="hidden mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="text-sm font-semibold text-gray-900 mb-2">Sistem yang Digunakan:</div>
                            <div id="systems_list" class="text-sm text-gray-700"></div>
                        </div>
                    </div>
                    <div>
                        <label for="typeMachine" class="block text-sm font-semibold text-gray-700 mb-2">Type Machine <span class="text-red-500">*</span></label>
                        <input type="text" name="typeMachine" id="typeMachine" value="{{ old('typeMachine', $downtimeErp2->typeMachine) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('typeMachine') border-red-500 @enderror">
                        @error('typeMachine')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modelMachine" class="block text-sm font-semibold text-gray-700 mb-2">Model Machine <span class="text-red-500">*</span></label>
                        <input type="text" name="modelMachine" id="modelMachine" value="{{ old('modelMachine', $downtimeErp2->modelMachine) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('modelMachine') border-red-500 @enderror">
                        @error('modelMachine')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                    <div>
                        <label for="brandMachine" class="block text-sm font-semibold text-gray-700 mb-2">Brand Machine <span class="text-red-500">*</span></label>
                        <input type="text" name="brandMachine" id="brandMachine" value="{{ old('brandMachine', $downtimeErp2->brandMachine) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('brandMachine') border-red-500 @enderror">
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
                        <input type="time" name="stopProduction" id="stopProduction" value="{{ old('stopProduction', $stopTime) }}" step="1" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('stopProduction') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Format: HH:MM:SS (menggunakan tanggal dari field Date)</p>
                        @error('stopProduction')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="responMechanic" class="block text-sm font-semibold text-gray-700 mb-2">Respon Mechanic <span class="text-red-500">*</span></label>
                        <input type="time" name="responMechanic" id="responMechanic" value="{{ old('responMechanic', $responTime) }}" step="1" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('responMechanic') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Format: HH:MM:SS (menggunakan tanggal dari field Date)</p>
                        @error('responMechanic')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="startProduction" class="block text-sm font-semibold text-gray-700 mb-2">Start Production <span class="text-red-500">*</span></label>
                        <input type="time" name="startProduction" id="startProduction" value="{{ old('startProduction', $startTime) }}" step="1" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('startProduction') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Format: HH:MM:SS (menggunakan tanggal dari field Date)</p>
                        @error('startProduction')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="duration" class="block text-sm font-semibold text-gray-700 mb-2">Duration <span class="text-red-500">*</span> (Auto-calculated)</label>
                        <input type="text" name="duration" id="duration" value="{{ old('duration', $downtimeErp2->duration) }}" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('duration') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis dihitung dari Start Production - Stop Production</p>
                        @error('duration')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="Standar_Time" class="block text-sm font-semibold text-gray-700 mb-2">Standar Time</label>
                        <input type="text" name="Standar_Time" id="Standar_Time" value="{{ old('Standar_Time', $downtimeErp2->Standar_Time) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('Standar_Time') border-red-500 @enderror">
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
                        <input type="text" name="problemDowntime" id="problemDowntime" value="{{ old('problemDowntime', $downtimeErp2->problemDowntime) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('problemDowntime') border-red-500 @enderror">
                        @error('problemDowntime')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="Problem_MM" class="block text-sm font-semibold text-gray-700 mb-2">Problem MM</label>
                        <input type="text" name="Problem_MM" id="Problem_MM" value="{{ old('Problem_MM', $downtimeErp2->Problem_MM) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('Problem_MM') border-red-500 @enderror">
                        @error('Problem_MM')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="reasonDowntime" class="block text-sm font-semibold text-gray-700 mb-2">Reason Downtime <span class="text-red-500">*</span></label>
                        <input type="text" name="reasonDowntime" id="reasonDowntime" value="{{ old('reasonDowntime', $downtimeErp2->reasonDowntime) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('reasonDowntime') border-red-500 @enderror">
                        @error('reasonDowntime')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="actionDowtime" class="block text-sm font-semibold text-gray-700 mb-2">Action Downtime <span class="text-red-500">*</span></label>
                        <input type="text" name="actionDowtime" id="actionDowtime" value="{{ old('actionDowtime', $downtimeErp2->actionDowtime) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('actionDowtime') border-red-500 @enderror">
                        @error('actionDowtime')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="Part" class="block text-sm font-semibold text-gray-700 mb-2">Part</label>
                        <input type="text" name="Part" id="Part" value="{{ old('Part', $downtimeErp2->Part) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('Part') border-red-500 @enderror">
                        @error('Part')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="groupProblem" class="block text-sm font-semibold text-gray-700 mb-2">Group Problem <span class="text-red-500">*</span></label>
                        <input type="text" name="groupProblem" id="groupProblem" value="{{ old('groupProblem', $downtimeErp2->groupProblem) }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('groupProblem') border-red-500 @enderror">
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
                                   value="{{ old('mekanik_search', $downtimeErp2->idMekanik ? $downtimeErp2->idMekanik . ' - ' . $downtimeErp2->nameMekanik : '') }}"
                                   autocomplete="off">
                            <div id="mekanik_dropdown" class="hidden absolute z-50 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg" style="top: 100%; left: 0; right: 0; width: 100%; max-width: 100%; max-height: 12rem; overflow-y: auto; box-sizing: border-box;"></div>
                        </div>
                        <input type="hidden" name="idMekanik" id="idMekanik" value="{{ old('idMekanik', $downtimeErp2->idMekanik) }}" required>
                        <input type="hidden" name="nameMekanik" id="nameMekanik" value="{{ old('nameMekanik', $downtimeErp2->nameMekanik) }}" required>
                        <div id="selected_mekanik" class="{{ old('idMekanik', $downtimeErp2->idMekanik) ? '' : 'hidden' }} mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900">Mekanik Terpilih:</div>
                                    <div id="selected_mekanik_info" class="text-sm text-gray-700">{{ old('idMekanik', $downtimeErp2->idMekanik) ? $downtimeErp2->idMekanik . ' - ' . $downtimeErp2->nameMekanik : '' }}</div>
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
                        <input type="text" name="idLeader" id="idLeader" value="{{ old('idLeader', $downtimeErp2->idLeader) }}" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('idLeader') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari atasan mekanik</p>
                        @error('idLeader')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="nameLeader" class="block text-sm font-semibold text-gray-700 mb-2">Name Leader <span class="text-red-500">*</span> (Auto-fill)</label>
                        <input type="text" name="nameLeader" id="nameLeader" value="{{ old('nameLeader', $downtimeErp2->nameLeader) }}" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('nameLeader') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari atasan mekanik</p>
                        @error('nameLeader')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="idGL" class="block text-sm font-semibold text-gray-700 mb-2">ID GL (Auto-fill)</label>
                        <input type="text" name="idGL" id="idGL" value="{{ old('idGL', $downtimeErp2->idGL) }}" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('idGL') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari atasan leader</p>
                        @error('idGL')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="nameGL" class="block text-sm font-semibold text-gray-700 mb-2">Name GL (Auto-fill)</label>
                        <input type="text" name="nameGL" id="nameGL" value="{{ old('nameGL', $downtimeErp2->nameGL) }}" readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('nameGL') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari atasan leader</p>
                        @error('nameGL')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="idCoord" class="block text-sm font-semibold text-gray-700 mb-2">ID Coord <span class="text-red-500">*</span> (Auto-fill)</label>
                        <input type="text" name="idCoord" id="idCoord" value="{{ old('idCoord', $downtimeErp2->idCoord) }}" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('idCoord') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Otomatis terisi dari atasan GL</p>
                        @error('idCoord')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="nameCoord" class="block text-sm font-semibold text-gray-700 mb-2">Name Coord <span class="text-red-500">*</span> (Auto-fill)</label>
                        <input type="text" name="nameCoord" id="nameCoord" value="{{ old('nameCoord', $downtimeErp2->nameCoord) }}" required readonly class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('nameCoord') border-red-500 @enderror">
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
                    Update Downtime ERP2
                </button>
                <a href="{{ route('downtime-erp2.index') }}" class="text-gray-600 hover:text-gray-800 hover:underline transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Room ERP selection handler
const roomErpSelect = document.getElementById('room_erp_select');
const plantInput = document.getElementById('plant');
const processInput = document.getElementById('process');
const lineInput = document.getElementById('line');
const roomNameInput = document.getElementById('roomName');
const kodeRoomInput = document.getElementById('kode_room');

// Handle Room ERP selection change
if (roomErpSelect && plantInput && processInput && lineInput && roomNameInput && kodeRoomInput) {
    roomErpSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            plantInput.value = selectedOption.dataset.plant || '';
            processInput.value = selectedOption.dataset.process || '';
            lineInput.value = selectedOption.dataset.line || '';
            roomNameInput.value = selectedOption.dataset.room || '';
            kodeRoomInput.value = selectedOption.dataset.kodeRoom || '';
        } else {
            // Clear fields if no selection
            plantInput.value = '';
            processInput.value = '';
            lineInput.value = '';
            roomNameInput.value = '';
            kodeRoomInput.value = '';
        }
    });
}

// Mekanik data from server (already mapped in controller)
const mekaniks = @json($mekaniks);

// Groups data from server (already mapped in controller)
const groupsData = @json($groupsData);

// DOM elements
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

// Group selection handler
const groupSelect = document.getElementById('group_id');
const systemsInfo = document.getElementById('systems_info');
const systemsList = document.getElementById('systems_list');

if (groupSelect && systemsInfo && systemsList) {
    // Handle group selection change
    groupSelect.addEventListener('change', function() {
        const selectedGroupId = this.value;
        
        if (!selectedGroupId) {
            systemsInfo.classList.add('hidden');
            return;
        }
        
        // Find selected group
        const selectedGroup = groupsData.find(g => g.id === selectedGroupId);
        
        if (selectedGroup && selectedGroup.systems && selectedGroup.systems.length > 0) {
            // Display systems
            systemsList.innerHTML = selectedGroup.systems.map(system => {
                return `<div class="mb-1">
                    <span class="font-semibold">${system.nama_sistem || ''}</span>
                    ${system.deskripsi ? `<span class="text-gray-600"> - ${system.deskripsi}</span>` : ''}
                </div>`;
            }).join('');
            systemsInfo.classList.remove('hidden');
        } else {
            systemsInfo.classList.add('hidden');
        }
    });

    // Trigger on page load if group is already selected (from old input)
    if (groupSelect.value) {
        groupSelect.dispatchEvent(new Event('change'));
    }
}
</script>
@endsection

