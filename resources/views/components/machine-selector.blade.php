@props(['machines', 'selectedId' => null, 'name' => 'machine_id', 'id' => 'machine_id', 'required' => true, 'error' => null])

<div class="relative">
    <label for="{{ $id }}_search" class="block text-sm font-medium text-gray-700 mb-1">
        Machine <span class="text-red-500">*</span>
    </label>
    <div class="flex gap-2">
        <div class="flex-1 relative">
            <input type="text" 
                   id="{{ $id }}_search" 
                   placeholder="Cari ID Machine atau scan barcode..."
                   class="w-full border rounded px-3 py-2 pr-10 @error($name) border-red-500 @enderror"
                   autocomplete="off">
            <input type="hidden" 
                   name="{{ $name }}" 
                   id="{{ $id }}" 
                   value="{{ old($name, $selectedId) }}"
                   @if($required) required @endif>
            <div id="{{ $id }}_dropdown" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                <!-- Options will be populated here -->
            </div>
        </div>
        <button type="button" 
                id="{{ $id }}_scan_btn"
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 flex items-center gap-2"
                title="Scan Barcode">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
            </svg>
            <span class="hidden sm:inline">Scan</span>
        </button>
    </div>
    <div id="{{ $id }}_selected" class="mt-2 @if(!$selectedId) hidden @endif">
        <div class="bg-blue-50 border border-blue-200 rounded px-3 py-2 flex items-center justify-between">
            <span class="text-sm text-blue-900">
                <span class="font-semibold" id="{{ $id }}_selected_id">{{ $selectedId ? ($machines->firstWhere('id', $selectedId)->idMachine ?? '-') : '-' }}</span>
                <span class="text-blue-600" id="{{ $id }}_selected_info">
                    @if($selectedId)
                        @php $selectedMachine = $machines->firstWhere('id', $selectedId); @endphp
                        @if($selectedMachine)
                            {{ ($selectedMachine->machineType->name ?? '-') }} - ({{ ($selectedMachine->plant->name ?? '-') }} / {{ ($selectedMachine->process->name ?? '-') }} / {{ ($selectedMachine->line->name ?? '-') }})
                        @endif
                    @endif
                </span>
            </span>
            <button type="button" id="{{ $id }}_clear" class="text-blue-600 hover:text-blue-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
    @error($name)
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<!-- Barcode Scanner Modal for {{ $id }} -->
<div id="{{ $id }}_barcode_modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50" @keydown.escape.window="closeBarcodeModal{{ $id }}()">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Scan Barcode</h3>
                    <button type="button" onclick="closeBarcodeModal{{ $id }}()" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div id="{{ $id }}_barcode_scanner_container" class="mb-4">
                    <video id="{{ $id }}_barcode_video" class="w-full rounded border-2 border-gray-300" autoplay playsinline></video>
                    <canvas id="{{ $id }}_barcode_canvas" class="hidden"></canvas>
                </div>
                <div id="{{ $id }}_barcode_status" class="text-sm text-gray-600 mb-4 text-center"></div>
                <div class="flex gap-2">
                    <button type="button" onclick="closeBarcodeModal{{ $id }}()" class="flex-1 px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" id="{{ $id }}_start_barcode_btn" onclick="startBarcodeScanner{{ $id }}();" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Start Camera
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const selectorId = '{{ $id }}';
    const machines = @json($machines->map(function($machine) {
        return [
            'id' => $machine->id,
            'idMachine' => $machine->idMachine,
            'machineType' => $machine->machineType->name ?? '-',
            'plant' => $machine->plant->name ?? '-',
            'process' => $machine->process->name ?? '-',
            'line' => $machine->line->name ?? '-',
        ];
    }));

    let codeReader{{ $id }} = null;
    if (typeof ZXing !== 'undefined') {
        codeReader{{ $id }} = new ZXing.BrowserMultiFormatReader();
    }

    const machineSearch = document.getElementById(selectorId + '_search');
    const machineId = document.getElementById(selectorId);
    const machineDropdown = document.getElementById(selectorId + '_dropdown');
    const selectedMachine = document.getElementById(selectorId + '_selected');
    const selectedMachineId = document.getElementById(selectorId + '_selected_id');
    const selectedMachineInfo = document.getElementById(selectorId + '_selected_info');
    const clearMachine = document.getElementById(selectorId + '_clear');
    const scanBtn = document.getElementById(selectorId + '_scan_btn');
    const barcodeModal = document.getElementById(selectorId + '_barcode_modal');
    const barcodeVideo = document.getElementById(selectorId + '_barcode_video');
    const barcodeStatus = document.getElementById(selectorId + '_barcode_status');
    const startBarcodeBtn = document.getElementById(selectorId + '_start_barcode_btn');

    machineSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        if (searchTerm.length === 0) {
            machineDropdown.classList.add('hidden');
            return;
        }
        
        const filtered = machines.filter(m => 
            m.idMachine.toLowerCase().includes(searchTerm) ||
            m.machineType.toLowerCase().includes(searchTerm) ||
            m.plant.toLowerCase().includes(searchTerm) ||
            m.process.toLowerCase().includes(searchTerm) ||
            m.line.toLowerCase().includes(searchTerm)
        );
        
        if (filtered.length === 0) {
            machineDropdown.innerHTML = '<div class="px-4 py-2 text-sm text-gray-500">Tidak ada mesin ditemukan</div>';
            machineDropdown.classList.remove('hidden');
            return;
        }
        
        machineDropdown.innerHTML = filtered.slice(0, 10).map(m => {
            const info = `${m.machineType} - (${m.plant} / ${m.process} / ${m.line})`;
            return `
            <div class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0" 
                 onclick="selectMachine{{ $id }}(${m.id}, ${JSON.stringify(m.idMachine)}, ${JSON.stringify(info)})">
                <div class="font-semibold text-gray-900">${m.idMachine}</div>
                <div class="text-xs text-gray-500">${info}</div>
            </div>
        `;
        }).join('');
        
        machineDropdown.classList.remove('hidden');
    });

    window['selectMachine{{ $id }}'] = function(id, idMachine, info) {
        machineId.value = id;
        machineSearch.value = idMachine;
        selectedMachineId.textContent = idMachine;
        selectedMachineInfo.textContent = info;
        selectedMachine.classList.remove('hidden');
        machineDropdown.classList.add('hidden');
        machineSearch.blur();
    };

    clearMachine.addEventListener('click', function() {
        machineId.value = '';
        machineSearch.value = '';
        selectedMachine.classList.add('hidden');
    });

    document.addEventListener('click', function(e) {
        if (!machineSearch.contains(e.target) && !machineDropdown.contains(e.target)) {
            machineDropdown.classList.add('hidden');
        }
    });

    scanBtn.addEventListener('click', function() {
        barcodeModal.classList.remove('hidden');
    });

    window['closeBarcodeModal{{ $id }}'] = function() {
        window['stopBarcodeScanner{{ $id }}']();
        barcodeModal.classList.add('hidden');
    };

    window['startBarcodeScanner{{ $id }}'] = async function() {
        if (!codeReader{{ $id }}) {
            barcodeStatus.textContent = 'Barcode scanner tidak tersedia. Pastikan koneksi internet aktif.';
            return;
        }
        
        try {
            const videoInputDevices = await codeReader{{ $id }}.listVideoInputDevices();
            
            if (videoInputDevices.length === 0) {
                barcodeStatus.textContent = 'Tidak ada kamera ditemukan.';
                return;
            }
            
            barcodeStatus.textContent = 'Mengaktifkan kamera...';
            startBarcodeBtn.disabled = true;
            
            const selectedDeviceId = videoInputDevices[0].deviceId;
            
            codeReader{{ $id }}.decodeFromVideoDevice(selectedDeviceId, selectorId + '_barcode_video', (result, err) => {
                if (result) {
                    const scannedCode = result.getText();
                    barcodeStatus.textContent = 'Barcode terdeteksi: ' + scannedCode;
                    
                    const foundMachine = machines.find(m => m.idMachine === scannedCode);
                    
                    if (foundMachine) {
                        const info = `${foundMachine.machineType} - (${foundMachine.plant} / ${foundMachine.process} / ${foundMachine.line})`;
                        window['selectMachine{{ $id }}'](foundMachine.id, foundMachine.idMachine, info);
                        window['stopBarcodeScanner{{ $id }}']();
                        barcodeModal.classList.add('hidden');
                    } else {
                        barcodeStatus.textContent = 'Mesin dengan ID "' + scannedCode + '" tidak ditemukan.';
                        setTimeout(() => {
                            barcodeStatus.textContent = 'Scan barcode...';
                        }, 2000);
                    }
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

    window['stopBarcodeScanner{{ $id }}'] = function() {
        if (codeReader{{ $id }}) {
            codeReader{{ $id }}.reset();
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
            window['stopBarcodeScanner{{ $id }}']();
            barcodeModal.classList.add('hidden');
        }
    });

    // Initialize selected machine on page load
    @if($selectedId)
        document.addEventListener('DOMContentLoaded', function() {
            const currentMachine = machines.find(m => m.id == {{ $selectedId }});
            if (currentMachine) {
                machineSearch.value = currentMachine.idMachine;
            }
        });
    @endif
})();
</script>

