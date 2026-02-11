@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8" x-data="{ stockModalOpen: false, movementType: 'in', sourceType: 'manual' }">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Part ERP Detail</h1>
            <div class="flex items-center gap-3">
                <button type="button" @click="movementType = 'in'; stockModalOpen = true" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    Tambah Stok
                </button>
                <button type="button" @click="movementType = 'out'; stockModalOpen = true" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                    Kurangi Stok
                </button>
                <a href="{{ route('part-erp.edit', ['part_erp' => $partErp->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    Edit
                </a>
                <a href="{{ route('part-erp.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    Back to List
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">{{ session('error') }}</div>
        @endif

        @if($partErp->minimum_stock > 0 && $partErp->stock < $partErp->minimum_stock)
            <div class="bg-amber-100 border border-amber-400 text-amber-800 px-4 py-3 rounded mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span><strong>Low stock:</strong> Current stock ({{ $partErp->stock }}) is below minimum ({{ $partErp->minimum_stock }})</span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Part Number</label>
                <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">{{ $partErp->part_number }}</div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Name</label>
                <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">{{ $partErp->name }}</div>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Description</label>
                <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900 min-h-[80px]">{{ $partErp->description ?? '-' }}</div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Category (System)</label>
                <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                    @if($partErp->system)
                        {{ $partErp->system->nama_sistem }}
                    @else
                        {{ $partErp->category ?? '-' }}
                    @endif
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Brand</label>
                <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">{{ $partErp->brand ?? '-' }}</div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Unit</label>
                <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">{{ $partErp->unit ?? '-' }}</div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Stock</label>
                <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">{{ $partErp->stock ?? 0 }}</div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Minimum Stock</label>
                <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">{{ $partErp->minimum_stock ?? 0 }}</div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Price</label>
                <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">{{ $partErp->price ? number_format($partErp->price, 2) : '-' }}</div>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Location (Machine Types)</label>
                <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                    @if($partErp->machineTypes && $partErp->machineTypes->count() > 0)
                        {{ $partErp->machineTypes->pluck('name')->implode(', ') }}
                    @else
                        -
                    @endif
                </div>
            </div>
        </div>

        <!-- Riwayat Pergerakan Stok -->
        @if($partErp->stockMovements && $partErp->stockMovements->count() > 0)
        <div class="mt-8 pt-6 border-t border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Riwayat Pergerakan Stok</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-gray-700">Tanggal</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-700">Tipe</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-700">Dokumen / Relasi</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-700">Qty</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-700">Stok Akhir</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-700">Catatan</th>
                            <th class="px-3 py-2 text-left font-medium text-gray-700">User</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($partErp->stockMovements as $mov)
                        <tr class="{{ $mov->quantity > 0 ? 'bg-green-50' : 'bg-amber-50' }}">
                            <td class="px-3 py-2 text-gray-600">{{ $mov->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2">
                                @if($mov->quantity > 0)
                                    <span class="text-green-700 font-medium">Masuk</span>
                                @else
                                    <span class="text-amber-700 font-medium">Keluar</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-gray-700">
                                @if($mov->reference_type && $mov->reference_id)
                                    @php $ref = $mov->getReferenceModel(); @endphp
                                    @if($mov->reference_type === 'downtime_erp2' && $ref)
                                        <a href="{{ route('downtime-erp2.show', $ref->id) }}" class="text-blue-600 hover:underline">Downtime ERP2 #{{ $ref->id }}</a>
                                        <span class="text-gray-500 text-xs">({{ $ref->date ? $ref->date->format('d/m/Y') : '-' }})</span>
                                    @elseif($mov->reference_type === 'downtime_erp' && $ref)
                                        <a href="{{ route('downtime_erp.show', $ref->id) }}" class="text-blue-600 hover:underline">Downtime ERP #{{ $ref->id }}</a>
                                    @elseif($mov->reference_type === 'preventive_maintenance_execution' && $ref)
                                        <a href="{{ route('preventive-maintenance.updating.index') }}" class="text-blue-600 hover:underline">PM #{{ $ref->id }}</a>
                                    @elseif($mov->reference_type === 'work_order' && $ref)
                                        <a href="{{ route('work-orders.show', $ref->id) }}" class="text-blue-600 hover:underline">WO #{{ $ref->id }}</a>
                                    @else
                                        {{ $mov->getReferenceLabel() }}
                                    @endif
                                @else
                                    {{ $mov->document_type ?? '-' }} #{{ $mov->document_number ?? '-' }}
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right font-medium {{ $mov->quantity > 0 ? 'text-green-700' : 'text-amber-700' }}">{{ $mov->quantity > 0 ? '+' : '' }}{{ $mov->quantity }}</td>
                            <td class="px-3 py-2 text-right text-gray-700">{{ $mov->balance_after ?? '-' }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $mov->notes ?? '-' }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $mov->user->name ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <!-- Modal Tambah/Kurangi Stok dengan Nomor Dokumen -->
    <div x-show="stockModalOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
         @click.self="stockModalOpen = false"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4" @click.stop
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900" x-text="movementType === 'in' ? 'Tambah Stok' : 'Kurangi Stok'"></h3>
                <button type="button" @click="stockModalOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form action="{{ route('part-erp.stock-movement.store', $partErp->id) }}" method="POST" class="p-6" id="stockMovementForm">
                @csrf
                <input type="hidden" name="type" :value="movementType">
                <input type="hidden" name="reference_type" :value="movementType === 'out' ? sourceType : 'manual'">

                <!-- Sumber pengurangan (hanya untuk Kurangi Stok) -->
                <div class="mb-4" x-show="movementType === 'out'">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sumber pengurangan</label>
                    <select x-model="sourceType" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="manual">Manual (MR/PO/MO)</option>
                        <option value="preventive_maintenance_execution">Preventive Maintenance</option>
                        <option value="work_order">Work Order</option>
                        <option value="other">Lainnya</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Pengurangan stok untuk perbaikan/downtime dilakukan saat buat/edit Downtime ERP (pilih part & jumlah di sana).</p>
                </div>

                <!-- Manual: Jenis & No. Dokumen -->
                <template x-if="movementType === 'in' || sourceType === 'manual'">
                    <div class="space-y-4">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Dokumen <span class="text-red-500" x-show="movementType === 'in' || sourceType === 'manual'">*</span></label>
                            <select name="document_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" :required="movementType === 'in' || sourceType === 'manual'">
                                <option value="">Pilih</option>
                                <option value="MR">MR (Material Request)</option>
                                <option value="PO">PO (Purchase Order)</option>
                                <option value="MO">MO (Manufacturing Order)</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Dokumen <span class="text-red-500" x-show="movementType === 'in' || sourceType === 'manual'">*</span></label>
                            <input type="text" name="document_number" maxlength="255" placeholder="Contoh: MR-2025-001"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   value="{{ old('document_number') }}" :required="movementType === 'in' || sourceType === 'manual'">
                            @error('document_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </template>

                <!-- Pilih Preventive Maintenance Execution -->
                <div class="mb-4" x-show="movementType === 'out' && sourceType === 'preventive_maintenance_execution'" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih PM Execution <span class="text-red-500">*</span></label>
                    <select :name="sourceType === 'preventive_maintenance_execution' ? 'reference_id' : 'reference_id_skip'" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih PM --</option>
                        @foreach($recentPmExecutions ?? [] as $pm)
                            <option value="{{ $pm->id }}">#{{ $pm->id }} - {{ $pm->scheduled_date ? $pm->scheduled_date->format('d/m/Y') : '-' }} - {{ $pm->status ?? '-' }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Pilih Work Order -->
                <div class="mb-4" x-show="movementType === 'out' && sourceType === 'work_order'" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Work Order <span class="text-red-500">*</span></label>
                    <select :name="sourceType === 'work_order' ? 'reference_id' : 'reference_id_skip'" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih WO --</option>
                        @foreach($recentWorkOrders ?? [] as $wo)
                            <option value="{{ $wo->id }}">#{{ $wo->id }} - {{ $wo->wo_number ?? '-' }} - {{ Str::limit($wo->description ?? '-', 30) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity" required min="1" placeholder="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="{{ old('quantity', 1) }}">
                    <p class="text-xs text-gray-500 mt-1" x-show="movementType === 'out'">Stok saat ini: {{ $partErp->stock ?? 0 }}</p>
                    @error('quantity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
                    <textarea name="notes" rows="2" maxlength="500" placeholder="Catatan tambahan"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('notes') }}</textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="stockModalOpen = false" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-white font-medium"
                            :class="movementType === 'in' ? 'bg-green-600 hover:bg-green-700' : 'bg-amber-600 hover:bg-amber-700'"
                            x-text="movementType === 'in' ? 'Tambah Stok' : 'Kurangi Stok'"></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
