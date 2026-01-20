@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Detail Jadwal Inspeksi</h1>
            <div class="flex gap-2">
                <a href="{{ route('inspections.scheduling.edit', $scheduling->id) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                    Edit
                </a>
                <a href="{{ route('inspections.scheduling.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                    Kembali
                </a>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Jadwal</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Judul</label>
                    <p class="text-sm font-semibold text-gray-900">{{ $scheduling->title }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                    <p>
                        @if($scheduling->status == 'active')
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 font-semibold">Active</span>
                        @elseif($scheduling->status == 'completed')
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 font-semibold">Completed</span>
                        @elseif($scheduling->status == 'cancelled')
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 font-semibold">Cancelled</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800 font-semibold">Inactive</span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Mesin</label>
                    <p class="text-sm text-gray-900">{{ $scheduling->machineErp->idMachine ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Template</label>
                    <p class="text-sm text-gray-900">{{ $scheduling->template->name ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Frekuensi</label>
                    <p class="text-sm text-gray-900">{{ ucfirst($scheduling->frequency) }} 
                        @if($scheduling->frequency_value > 1)
                            ({{ $scheduling->frequency_value }}x)
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Mulai</label>
                    <p class="text-sm text-gray-900">{{ $scheduling->start_date->format('d/m/Y') }}</p>
                </div>
                @if($scheduling->end_date)
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Selesai</label>
                    <p class="text-sm text-gray-900">{{ $scheduling->end_date->format('d/m/Y') }}</p>
                </div>
                @endif
                @if($scheduling->preferred_time)
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Waktu Preferensi</label>
                    <p class="text-sm text-gray-900">{{ date('H:i', strtotime($scheduling->preferred_time)) }}</p>
                </div>
                @endif
                @if($scheduling->estimated_duration)
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Durasi Estimasi</label>
                    <p class="text-sm text-gray-900">{{ $scheduling->estimated_duration }} menit</p>
                </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">PIC</label>
                    <p class="text-sm text-gray-900">{{ $scheduling->assignedUser->name ?? '-' }}</p>
                </div>
                @if($scheduling->description)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Deskripsi</label>
                    <p class="text-sm text-gray-900">{{ $scheduling->description }}</p>
                </div>
                @endif
                @if($scheduling->notes)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500 mb-1">Catatan</label>
                    <p class="text-sm text-gray-900">{{ $scheduling->notes }}</p>
                </div>
                @endif
            </div>
        </div>
        
        @if($scheduling->inspections->count() > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Inspeksi</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dilakukan Oleh</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($scheduling->inspections as $inspection)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $inspection->inspection_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $inspection->performedBy->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-center">
                                <a href="{{ route('inspections.show', $inspection->id) }}" class="text-blue-600 hover:text-blue-800">Lihat</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

