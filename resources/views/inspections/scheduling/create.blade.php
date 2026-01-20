@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-4xl">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Buat Jadwal Inspeksi</h1>
        
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('inspections.scheduling.store') }}" method="POST">
            @csrf
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Jadwal</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="machine_erp_id" class="block text-sm font-medium text-gray-700 mb-2">Mesin <span class="text-red-500">*</span></label>
                        <select name="machine_erp_id" id="machine_erp_id" class="w-full border rounded px-3 py-2" required>
                            <option value="">Pilih Mesin</option>
                            @foreach($machines as $machine)
                                <option value="{{ $machine->id }}" {{ old('machine_erp_id') == $machine->id ? 'selected' : '' }}>
                                    {{ $machine->idMachine }} - {{ $machine->machineType->name ?? '-' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="template_id" class="block text-sm font-medium text-gray-700 mb-2">Template <span class="text-red-500">*</span></label>
                        <select name="template_id" id="template_id" class="w-full border rounded px-3 py-2" required>
                            <option value="">Pilih Template</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                    {{ $template->name }} ({{ $template->machineType->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Judul <span class="text-red-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea name="description" id="description" rows="3" class="w-full border rounded px-3 py-2">{{ old('description') }}</textarea>
                    </div>
                    
                    <div>
                        <label for="frequency" class="block text-sm font-medium text-gray-700 mb-2">Frekuensi <span class="text-red-500">*</span></label>
                        <select name="frequency" id="frequency" class="w-full border rounded px-3 py-2" required>
                            <option value="daily" {{ old('frequency') == 'daily' ? 'selected' : '' }}>Harian</option>
                            <option value="weekly" {{ old('frequency') == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                            <option value="monthly" {{ old('frequency') == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                            <option value="custom" {{ old('frequency') == 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="frequency_value" class="block text-sm font-medium text-gray-700 mb-2">Nilai Frekuensi <span class="text-red-500">*</span></label>
                        <input type="number" name="frequency_value" id="frequency_value" value="{{ old('frequency_value', 1) }}" min="1" class="w-full border rounded px-3 py-2" required>
                        <p class="text-xs text-gray-500 mt-1">Contoh: 2 untuk setiap 2 hari/minggu/bulan</p>
                    </div>
                    
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', date('Y-m-d')) }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label for="preferred_time" class="block text-sm font-medium text-gray-700 mb-2">Waktu Preferensi</label>
                        <input type="time" name="preferred_time" id="preferred_time" value="{{ old('preferred_time') }}" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label for="estimated_duration" class="block text-sm font-medium text-gray-700 mb-2">Durasi Estimasi (menit)</label>
                        <input type="number" name="estimated_duration" id="estimated_duration" value="{{ old('estimated_duration') }}" min="1" class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">PIC</label>
                        <select name="assigned_to" id="assigned_to" class="w-full border rounded px-3 py-2">
                            <option value="">Pilih PIC</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="status" class="w-full border rounded px-3 py-2" required>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                        <textarea name="notes" id="notes" rows="3" class="w-full border rounded px-3 py-2">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('inspections.scheduling.index') }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">
                    Simpan Jadwal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

