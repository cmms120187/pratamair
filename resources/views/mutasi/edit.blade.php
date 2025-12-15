@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Edit Mutasi Mesin</h1>
            <p class="text-sm text-gray-600">Update informasi mutasi mesin</p>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        <form action="{{ route('mutasi.update', $mutasi->id) }}" method="POST">
            @csrf
            @method('PUT')
            @if(isset($page))
                <input type="hidden" name="page" value="{{ $page }}">
            @endif
            <div class="mb-4">
                <label for="machine_erp_id" class="block text-sm font-semibold text-gray-700 mb-2">Machine ERP <span class="text-red-500">*</span></label>
                <select name="machine_erp_id" 
                        id="machine_erp_id" 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('machine_erp_id') border-red-500 @enderror" 
                        required>
                    <option value="">-- Pilih Machine --</option>
                    @foreach($machineErps as $machine)
                        <option value="{{ $machine->id }}" 
                                {{ old('machine_erp_id', $mutasi->machine_erp_id) == $machine->id ? 'selected' : '' }}>
                            {{ $machine->idMachine }} 
                            @if($machine->room_name)
                                (Room: {{ $machine->room_name }})
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('machine_erp_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="old_room_erp_id" class="block text-sm font-semibold text-gray-700 mb-2">Room Lama (Auto-fill dari lokasi saat ini)</label>
                <select name="old_room_erp_id" 
                        id="old_room_erp_id" 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('old_room_erp_id') border-red-500 @enderror">
                    <option value="">-- Pilih Room Lama (opsional) --</option>
                    @foreach($roomErps as $room)
                        <option value="{{ $room->id }}" {{ old('old_room_erp_id', $mutasi->old_room_erp_id) == $room->id ? 'selected' : '' }}>
                            {{ $room->kode_room ? $room->kode_room . ' - ' : '' }}{{ $room->name }}
                            @if($room->plant_name)
                                ({{ $room->plant_name }})
                            @endif
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Akan otomatis terisi dengan room saat ini dari Machine ERP yang dipilih</p>
                @error('old_room_erp_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="new_room_erp_id" class="block text-sm font-semibold text-gray-700 mb-2">Room Baru <span class="text-red-500">*</span></label>
                <select name="new_room_erp_id" 
                        id="new_room_erp_id" 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('new_room_erp_id') border-red-500 @enderror" 
                        required>
                    <option value="">-- Pilih Room Baru --</option>
                    @foreach($roomErps as $room)
                        <option value="{{ $room->id }}" {{ old('new_room_erp_id', $mutasi->new_room_erp_id) == $room->id ? 'selected' : '' }}>
                            {{ $room->kode_room ? $room->kode_room . ' - ' : '' }}{{ $room->name }}
                            @if($room->plant_name)
                                ({{ $room->plant_name }})
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('new_room_erp_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Mutasi <span class="text-red-500">*</span></label>
                <input type="date" 
                       name="date" 
                       id="date" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('date') border-red-500 @enderror" 
                       value="{{ old('date', $mutasi->date->format('Y-m-d')) }}" 
                       required>
                @error('date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="reason" class="block text-sm font-semibold text-gray-700 mb-2">Alasan Mutasi</label>
                <input type="text" 
                       name="reason" 
                       id="reason" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('reason') border-red-500 @enderror" 
                       value="{{ old('reason', $mutasi->reason) }}" 
                       placeholder="Masukkan alasan mutasi">
                @error('reason')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Deskripsi</label>
                <textarea name="description" 
                          id="description" 
                          rows="4"
                          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('description') border-red-500 @enderror" 
                          placeholder="Masukkan deskripsi mutasi">{{ old('description', $mutasi->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-yellow-800">
                    <strong>Perhatian:</strong> Setelah mutasi diupdate, room_name, plant_name, process_name, dan line_name di Machine ERP akan otomatis diupdate sesuai dengan Room Baru yang dipilih.
                </p>
            </div>

            <div class="flex items-center gap-3 pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition duration-150 ease-in-out flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Mutasi
                </button>
                <a href="{{ route('mutasi.index', isset($page) ? ['page' => $page] : []) }}" class="text-gray-600 hover:text-gray-800 hover:underline transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const machineSelect = document.getElementById('machine_erp_id');
    const oldRoomSelect = document.getElementById('old_room_erp_id');
    
    // Machine data from server
    const machines = @json($machineErps->map(function($m) {
        return [
            'id' => $m->id,
            'idMachine' => $m->idMachine,
            'room_name' => $m->room_name ?? '',
        ];
    }));
    
    // Room ERP data from server
    const rooms = @json($roomErps->map(function($r) {
        return [
            'id' => $r->id,
            'name' => $r->name,
        ];
    }));
    
    // Auto-fill old room when machine is selected
    machineSelect.addEventListener('change', function() {
        const machineId = this.value;
        if (machineId) {
            const machine = machines.find(m => m.id == machineId);
            if (machine && machine.room_name) {
                // Find room by name
                const matchingRoom = rooms.find(r => r.name === machine.room_name || r.name.toLowerCase() === machine.room_name.toLowerCase());
                if (matchingRoom) {
                    oldRoomSelect.value = matchingRoom.id;
                } else {
                    // If room not found, keep current selection or clear
                    if (!oldRoomSelect.value) {
                        oldRoomSelect.value = '';
                    }
                }
            }
        }
    });
    
    // Auto-fill on page load if machine is already selected
    if (machineSelect.value) {
        machineSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection

