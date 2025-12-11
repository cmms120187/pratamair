@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8" x-data="{
            idMachine: '{{ $downtime->machine->idMachine ?? '' }}',
            machineId: '{{ $downtime->machine_id }}',
            namaMesin: '{{ $downtime->machine->machineType->name ?? '' }}',
            modelMesin: '{{ $downtime->machine->model->name ?? '' }}',
            brand: '{{ $downtime->machine->brand->name ?? '' }}',
            room: '{{ $downtime->machine->room->name ?? '' }}',
            plant: '{{ $downtime->machine->plant->name ?? '' }}',
            process: '{{ $downtime->machine->process->name ?? '' }}',
            line: '{{ $downtime->machine->line->name ?? '' }}',
            isLocked: true,
            isLoading: false,
            errorMessage: '',
            showMutasiModal: false,
            mutasiForm: {
                plant_id: '',
                process_id: '',
                line_id: '',
                room_id: ''
            },
            mutasiLoading: false,
            mutasiError: '',
            date: '{{ $downtime->date->format('Y-m-d') }}',
            stopProduction: '{{ $downtime->stopProduction ? $downtime->stopProduction->format('H:i:s') : '' }}',
            startProduction: '{{ $downtime->startProduction ? $downtime->startProduction->format('H:i:s') : '' }}',
            duration: '{{ $downtime->duration }}',
            calculateDuration() {
                if (!this.date || !this.stopProduction || !this.startProduction) {
                    this.duration = '';
                    return;
                }
                
                try {
                    // Gabungkan tanggal dengan waktu
                    const stopDateTime = new Date(this.date + 'T' + this.stopProduction);
                    const startDateTime = new Date(this.date + 'T' + this.startProduction);
                    
                    // Hitung selisih dalam milidetik
                    const diffMs = startDateTime - stopDateTime;
                    
                    // Konversi ke menit (dibulatkan)
                    const diffMinutes = Math.round(diffMs / (1000 * 60));
                    
                    // Set duration jika hasilnya valid (positif)
                    if (diffMinutes >= 0) {
                        this.duration = diffMinutes.toString();
                    } else {
                        this.duration = '';
                    }
                } catch (error) {
                    this.duration = '';
                }
            },
            init() {
                // Watch perubahan pada date, stopProduction, dan startProduction
                this.$watch('date', () => this.calculateDuration());
                this.$watch('stopProduction', () => this.calculateDuration());
                this.$watch('startProduction', () => this.calculateDuration());
            },
            async searchMachine() {
                if (!this.idMachine.trim()) {
                    this.errorMessage = 'Masukkan ID Mesin terlebih dahulu';
                    return;
                }
                
                this.isLoading = true;
                this.errorMessage = '';
                
                try {
                    const response = await fetch('{{ route('downtimes.search-machine') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ idMachine: this.idMachine })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.machineId = data.machine.id;
                        this.namaMesin = data.machine.nama_mesin;
                        this.modelMesin = data.machine.model_mesin;
                        this.brand = data.machine.brand;
                        this.room = data.machine.room;
                        this.plant = data.machine.plant;
                        this.process = data.machine.process;
                        this.line = data.machine.line;
                        // Pre-fill form mutasi dengan lokasi saat ini
                        this.mutasiForm.plant_id = data.machine.plant_id || '';
                        this.mutasiForm.process_id = data.machine.process_id || '';
                        this.mutasiForm.line_id = data.machine.line_id || '';
                        this.mutasiForm.room_id = data.machine.room_id || '';
                        this.isLocked = true;
                        this.errorMessage = '';
                    } else {
                        this.errorMessage = data.message || 'Machine not found';
                        this.clearMachineData();
                    }
                } catch (error) {
                    this.errorMessage = 'Error searching machine: ' + error.message;
                    this.clearMachineData();
                } finally {
                    this.isLoading = false;
                }
            },
            clearMachineData() {
                this.machineId = '';
                this.namaMesin = '';
                this.modelMesin = '';
                this.brand = '';
                this.room = '';
                this.plant = '';
                this.process = '';
                this.line = '';
                this.isLocked = false;
            },
            resetSearch() {
                this.idMachine = '';
                this.clearMachineData();
                this.errorMessage = '';
            },
            async updateMachineLocation() {
                if (!this.machineId) {
                    this.mutasiError = 'Pilih mesin terlebih dahulu';
                    return;
                }
                
                if (!this.mutasiForm.plant_id || !this.mutasiForm.process_id || !this.mutasiForm.line_id || !this.mutasiForm.room_id) {
                    this.mutasiError = 'Semua field lokasi harus diisi';
                    return;
                }
                
                this.mutasiLoading = true;
                this.mutasiError = '';
                
                try {
                    const response = await fetch('{{ route('downtimes.update-machine-location') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            machine_id: this.machineId,
                            plant_id: this.mutasiForm.plant_id,
                            process_id: this.mutasiForm.process_id,
                            line_id: this.mutasiForm.line_id,
                            room_id: this.mutasiForm.room_id
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Update tampilan dengan data baru
                        this.plant = data.machine.plant;
                        this.process = data.machine.process;
                        this.line = data.machine.line;
                        this.room = data.machine.room;
                        this.showMutasiModal = false;
                        this.mutasiError = '';
                        
                        // Reload data mesin untuk mendapatkan ID lokasi terbaru
                        if (this.idMachine) {
                            // Re-search mesin untuk mendapatkan data terbaru
                            const searchResponse = await fetch('{{ route('downtimes.search-machine') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ idMachine: this.idMachine })
                            });
                            
                            const searchData = await searchResponse.json();
                            if (searchData.success) {
                                // Update form mutasi dengan lokasi terbaru
                                this.mutasiForm.plant_id = searchData.machine.plant_id || '';
                                this.mutasiForm.process_id = searchData.machine.process_id || '';
                                this.mutasiForm.line_id = searchData.machine.line_id || '';
                                this.mutasiForm.room_id = searchData.machine.room_id || '';
                            }
                        }
                        
                        alert('Lokasi mesin berhasil diupdate!');
                    } else {
                        this.mutasiError = data.message || 'Gagal update lokasi mesin';
                    }
                } catch (error) {
                    this.mutasiError = 'Error: ' + error.message;
                } finally {
                    this.mutasiLoading = false;
                }
            }
        }">
    <div class="w-full mx-auto max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">View Downtime</h1>
            <div class="flex gap-2">
                <a href="{{ route('downtimes.edit', $downtime->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded shadow transition">
                    Edit
                </a>
                <a href="{{ route('downtimes.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                    Back
                </a>
            </div>
        </div>
        <div>
            <!-- Hidden input untuk machine_id (readonly) -->
            <input type="hidden" name="machine_id" x-model="machineId">
            
            <div class="bg-white rounded-lg shadow p-6 space-y-6">
                <!-- Section: Informasi Mesin -->
                <div class="border-b pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Mesin</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <!-- ID Mesin dengan Search -->
                        <div class="mb-4">
                            <label for="idMachine" class="block font-semibold mb-2">ID Mesin <span class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                <input type="text" 
                                       id="idMachine" 
                                       x-model="idMachine"
                                       class="flex-1 border rounded px-3 py-2 bg-gray-100 cursor-not-allowed"
                                       readonly
                                       disabled>
                            </div>
                            <p x-show="errorMessage" class="text-red-500 text-sm mt-1" x-text="errorMessage"></p>
                        </div>
                        
                        <!-- Nama Mesin (Readonly) -->
                        <div class="mb-4">
                            <label for="namaMesin" class="block font-semibold mb-2">Nama Mesin</label>
                            <input type="text" 
                                   id="namaMesin" 
                                   x-model="namaMesin"
                                   readonly
                                   class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed">
                        </div>
                        
                        <!-- Model Mesin (Readonly) -->
                        <div class="mb-4">
                            <label for="modelMesin" class="block font-semibold mb-2">Model Mesin</label>
                            <input type="text" 
                                   id="modelMesin" 
                                   x-model="modelMesin"
                                   readonly
                                   class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed">
                        </div>
                        
                        <!-- Brand (Readonly) -->
                        <div class="mb-4">
                            <label for="brand" class="block font-semibold mb-2">Brand</label>
                            <input type="text" 
                                   id="brand" 
                                   x-model="brand"
                                   readonly
                                   class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed">
                        </div>
                        
                        <!-- Room (Readonly) -->
                        <div class="mb-4">
                            <label for="room" class="block font-semibold mb-2">Room</label>
                            <input type="text" 
                                   id="room" 
                                   x-model="room"
                                   readonly
                                   class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed">
                        </div>
                        
                        <!-- Plant (Readonly) -->
                        <div class="mb-4">
                            <label for="plant" class="block font-semibold mb-2">Plant</label>
                            <input type="text" 
                                   id="plant" 
                                   x-model="plant"
                                   readonly
                                   class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed">
                        </div>
                        
                        <!-- Process, Line, dan Tombol MUTASI -->
                        <div class="mb-4 flex gap-4">
                            <div class="flex-1">
                                <label for="process" class="block font-semibold mb-2">Process</label>
                                <input type="text" 
                                       id="process" 
                                       x-model="process"
                                       readonly
                                       class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed">
                            </div>
                            
                            <div class="flex-1">
                                <label for="line" class="block font-semibold mb-2">Line</label>
                                <input type="text" 
                                       id="line" 
                                       x-model="line"
                                       readonly
                                       class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed">
                            </div>
                            
                        </div>
                    </div>
                </div>
                
                <!-- Section: Informasi Downtime -->
                <div class="border-b pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Downtime</h2>
                    <!-- Baris 1: Date, Stop, Respon, Start (4 kolom) -->
                    <div class="grid grid-cols-4 gap-4 mb-4">
                        <div>
                            <label for="date" class="block font-semibold mb-2 text-sm">Date <span class="text-red-500">*</span></label>
                            <input type="date" 
                                   name="date" 
                                   id="date" 
                                   x-model="date"
                                   value="{{ $downtime->date->format('Y-m-d') }}" 
                                   class="w-full border rounded px-2 py-2 text-sm bg-gray-100 cursor-not-allowed" 
                                   readonly
                                   disabled>
                            @error('date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="stopProduction" class="block font-semibold mb-2 text-sm">Stop (hh:mm:ss) <span class="text-red-500">*</span></label>
                            <input type="time" 
                                   step="1" 
                                   name="stopProduction" 
                                   id="stopProduction" 
                                   x-model="stopProduction"
                                   value="{{ $downtime->stopProduction ? $downtime->stopProduction->format('H:i:s') : '' }}" 
                                   class="w-full border rounded px-2 py-2 text-sm bg-gray-100 cursor-not-allowed" 
                                   readonly
                                   disabled>
                            @error('stopProduction')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="responMechanic" class="block font-semibold mb-2 text-sm">Respon (hh:mm:ss) <span class="text-red-500">*</span></label>
                            <input type="time" 
                                   step="1" 
                                   name="responMechanic" 
                                   id="responMechanic" 
                                   value="{{ $downtime->responMechanic ? $downtime->responMechanic->format('H:i:s') : '' }}" 
                                   class="w-full border rounded px-2 py-2 text-sm bg-gray-100 cursor-not-allowed" 
                                   readonly
                                   disabled>
                            @error('responMechanic')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="startProduction" class="block font-semibold mb-2 text-sm">Start (hh:mm:ss) <span class="text-red-500">*</span></label>
                            <input type="time" 
                                   step="1" 
                                   name="startProduction" 
                                   id="startProduction" 
                                   x-model="startProduction"
                                   value="{{ $downtime->startProduction ? $downtime->startProduction->format('H:i:s') : '' }}" 
                                   class="w-full border rounded px-2 py-2 text-sm bg-gray-100 cursor-not-allowed" 
                                   readonly
                                   disabled>
                            @error('startProduction')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <!-- Baris 2: Duration dan Standard Time -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="duration" class="block font-semibold mb-2">Duration (minutes) <span class="text-red-500">*</span></label>
                            <input type="number" 
                                   name="duration" 
                                   id="duration" 
                                   x-model="duration"
                                   value="{{ $downtime->duration }}" 
                                   class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" 
                                   readonly
                                   disabled>
                            <p class="text-xs text-gray-500 mt-1">Otomatis dihitung dari Start - Stop</p>
                            @error('duration')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="standard_time" class="block font-semibold mb-2">Standard Time (minutes)</label>
                            <input type="number" name="standard_time" id="standard_time" value="{{ $downtime->standard_time }}" class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" readonly disabled>
                            @error('standard_time')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="problem_id" class="block font-semibold mb-2">Problem <span class="text-red-500">*</span></label>
                            <input type="text" value="{{ $downtime->problem->name ?? '-' }}" class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" readonly disabled>
                            @error('problem_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="problem_mm_id" class="block font-semibold mb-2">Problem MM</label>
                            <input type="text" value="{{ $downtime->problemMm->name ?? '-' }}" class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" readonly disabled>
                            @error('problem_mm_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="reason_id" class="block font-semibold mb-2">Reason <span class="text-red-500">*</span></label>
                            <input type="text" value="{{ $downtime->reason->name ?? '-' }}" class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" readonly disabled>
                            @error('reason_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="action_id" class="block font-semibold mb-2">Action <span class="text-red-500">*</span></label>
                            <input type="text" value="{{ $downtime->action->name ?? '-' }}" class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" readonly disabled>
                            @error('action_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="group_id" class="block font-semibold mb-2">Group <span class="text-red-500">*</span></label>
                            <input type="text" value="{{ $downtime->group->name ?? '-' }}" class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" readonly disabled>
                            @error('group_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="part" class="block font-semibold mb-2">Part</label>
                            <input type="text" name="part" id="part" value="{{ $downtime->part ?? '-' }}" class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" readonly disabled>
                            @error('part')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Section: Personil -->
                <div class="pb-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Personil</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label for="mekanik_id" class="block font-semibold mb-2">Mekanik <span class="text-red-500">*</span></label>
                            <input type="text" value="{{ $downtime->mekanik->name ?? '-' }}" class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" readonly disabled>
                            @error('mekanik_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="leader_id" class="block font-semibold mb-2">Leader <span class="text-red-500">*</span></label>
                            <input type="text" value="{{ $downtime->leader->name ?? '-' }}" class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" readonly disabled>
                            @error('leader_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="coord_id" class="block font-semibold mb-2">Coordinator <span class="text-red-500">*</span></label>
                            <input type="text" value="{{ $downtime->coord->name ?? '-' }}" class="w-full border rounded px-3 py-2 bg-gray-100 cursor-not-allowed" readonly disabled>
                            @error('coord_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Modal MUTASI -->
    <div x-show="showMutasiModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="showMutasiModal = false"
         @keydown.escape.window="showMutasiModal = false"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <!-- Modal Container -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="showMutasiModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 @click.stop
                 class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">MUTASI LOKASI MESIN</h3>
                <button @click="showMutasiModal = false" 
                        type="button"
                        class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Form -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plant <span class="text-red-500">*</span></label>
                    <select x-model="mutasiForm.plant_id" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">Pilih Plant</option>
                        @foreach($plants as $plant)
                            <option value="{{ $plant->id }}">{{ $plant->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Process <span class="text-red-500">*</span></label>
                    <select x-model="mutasiForm.process_id" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">Pilih Process</option>
                        @foreach($processes as $process)
                            <option value="{{ $process->id }}">{{ $process->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Line <span class="text-red-500">*</span></label>
                    <select x-model="mutasiForm.line_id" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">Pilih Line</option>
                        @foreach($lines as $line)
                            <option value="{{ $line->id }}">{{ $line->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Room <span class="text-red-500">*</span></label>
                    <select x-model="mutasiForm.room_id" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">Pilih Room</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <p x-show="mutasiError" class="text-red-500 text-sm" x-text="mutasiError"></p>
            </div>
            
            <!-- Footer -->
            <div class="flex justify-end gap-3 mt-6">
                <button @click="showMutasiModal = false" 
                        type="button"
                        class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 focus:outline-none">
                    Batal
                </button>
                <button @click="updateMachineLocation()" 
                        type="button"
                        :disabled="mutasiLoading"
                        class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-semibold rounded transition focus:outline-none">
                    <span x-show="!mutasiLoading">Update Lokasi</span>
                    <span x-show="mutasiLoading">Loading...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { 
        display: none !important; 
    }
</style>

<!-- Barcode Scanner Modal -->
<div id="barcode_modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @keydown.escape.window="closeBarcodeModal()">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Scan Barcode Machine</h3>
                    <button type="button" onclick="closeBarcodeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div id="barcode_scanner_container" class="mb-4">
                    <video id="barcode_video" class="w-full rounded border-2 border-gray-300" autoplay playsinline></video>
                </div>
                <div id="barcode_status" class="text-sm text-gray-600 mb-4 text-center"></div>
                <div class="flex gap-2">
                    <button type="button" onclick="closeBarcodeModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" id="start_barcode_btn" onclick="startBarcodeScanner();" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Start Camera
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let codeReader = null;
if (typeof ZXing !== 'undefined') {
    codeReader = new ZXing.BrowserMultiFormatReader();
}

const scanBarcodeBtn = document.getElementById('scan_barcode_btn');
const barcodeModal = document.getElementById('barcode_modal');
const barcodeVideo = document.getElementById('barcode_video');
const barcodeStatus = document.getElementById('barcode_status');
const startBarcodeBtn = document.getElementById('start_barcode_btn');

scanBarcodeBtn.addEventListener('click', function() {
    barcodeModal.classList.remove('hidden');
});

window.closeBarcodeModal = function() {
    stopBarcodeScanner();
    barcodeModal.classList.add('hidden');
};

window.startBarcodeScanner = async function() {
    if (!codeReader) {
        barcodeStatus.textContent = 'Barcode scanner tidak tersedia. Pastikan koneksi internet aktif.';
        return;
    }
    
    try {
        const videoInputDevices = await codeReader.listVideoInputDevices();
        if (videoInputDevices.length === 0) {
            barcodeStatus.textContent = 'Tidak ada kamera ditemukan.';
            return;
        }
        
        barcodeStatus.textContent = 'Mengaktifkan kamera...';
        startBarcodeBtn.disabled = true;
        
        const selectedDeviceId = videoInputDevices[0].deviceId;
        
        codeReader.decodeFromVideoDevice(selectedDeviceId, 'barcode_video', (result, err) => {
            if (result) {
                const scannedCode = result.getText();
                barcodeStatus.textContent = 'Barcode terdeteksi: ' + scannedCode;
                
                // Set idMachine value and trigger search
                const idMachineInput = document.getElementById('idMachine');
                if (idMachineInput) {
                    idMachineInput.value = scannedCode;
                    // Trigger Alpine.js update
                    idMachineInput.dispatchEvent(new Event('input', { bubbles: true }));
                    // Auto search after scan
                    setTimeout(() => {
                        const searchBtn = document.querySelector('button[\\@click="searchMachine()"]');
                        if (searchBtn && !searchBtn.disabled) {
                            searchBtn.click();
                        }
                    }, 500);
                }
                
                stopBarcodeScanner();
                barcodeModal.classList.add('hidden');
            }
            
            if (err && !(err instanceof ZXing.NotFoundException)) {
                console.error('Barcode scan error:', err);
            }
        });
        
        barcodeStatus.textContent = 'Arahkan kamera ke barcode...';
    } catch (error) {
        console.error('Error starting barcode scanner:', error);
        barcodeStatus.textContent = 'Error: ' + error.message;
        startBarcodeBtn.disabled = false;
    }
};

window.stopBarcodeScanner = function() {
    if (codeReader) {
        codeReader.reset();
    }
    if (barcodeVideo.srcObject) {
        barcodeVideo.srcObject.getTracks().forEach(track => track.stop());
        barcodeVideo.srcObject = null;
    }
    barcodeStatus.textContent = '';
    startBarcodeBtn.disabled = false;
};

barcodeModal.addEventListener('click', function(e) {
    if (e.target === barcodeModal) {
        stopBarcodeScanner();
        barcodeModal.classList.add('hidden');
    }
});
</script>
@endsection
