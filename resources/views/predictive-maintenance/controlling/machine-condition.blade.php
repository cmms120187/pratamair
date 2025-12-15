@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-6xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Detail Kondisi Mesin</h1>
                <p class="text-sm text-gray-500 mt-1">Bulan: {{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y') }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('predictive-maintenance.controlling.index', ['month' => $filterMonth, 'year' => $filterYear]) }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                    Kembali
                </a>
            </div>
        </div>

        <!-- Machine Information -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Mesin</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">ID Mesin</label>
                    <p class="text-sm font-semibold text-gray-900">{{ $machine->idMachine ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Tipe Mesin</label>
                    <p class="text-sm font-semibold text-gray-900">{{ $machine->machineType->name ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Plant</label>
                    <p class="text-sm font-semibold text-gray-900">{{ $machine->plant_name ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Line</label>
                    <p class="text-sm font-semibold text-gray-900">{{ $machine->line_name ?? '-' }}</p>
                </div>
            </div>
            
            <!-- Overall Condition -->
            <div class="mt-4 pt-4 border-t">
                <label class="block text-sm font-medium text-gray-500 mb-2">Kondisi Keseluruhan</label>
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full
                    @if($overallCondition == 'normal') bg-green-100 border border-green-300
                    @elseif($overallCondition == 'warning') bg-yellow-100 border border-yellow-300
                    @elseif($overallCondition == 'caution') bg-orange-100 border border-orange-300
                    @elseif($overallCondition == 'critical') bg-red-100 border border-red-300
                    @else bg-gray-100 border border-gray-300
                    @endif">
                    <div class="w-4 h-4 rounded-full
                        @if($overallCondition == 'normal') bg-green-500
                        @elseif($overallCondition == 'warning') bg-yellow-500
                        @elseif($overallCondition == 'caution') bg-orange-500
                        @elseif($overallCondition == 'critical') bg-red-500
                        @else bg-gray-400
                        @endif"></div>
                    <span class="text-sm font-semibold
                        @if($overallCondition == 'normal') text-green-800
                        @elseif($overallCondition == 'warning') text-yellow-800
                        @elseif($overallCondition == 'caution') text-orange-800
                        @elseif($overallCondition == 'critical') text-red-800
                        @else text-gray-600
                        @endif">
                        @if($overallCondition == 'normal') Aman
                        @elseif($overallCondition == 'warning') Perlu Perhatian
                        @elseif($overallCondition == 'caution') Perlu Pengawasan
                        @elseif($overallCondition == 'critical') Perlu Perbaikan
                        @else Belum Ada Data
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Maintenance Points -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Detail Maintenance Points</h2>
            
            @if(count($maintenancePointsData) > 0)
                <div class="space-y-4">
                    @foreach($maintenancePointsData as $pointData)
                        @php
                            $point = $pointData['maintenance_point'];
                            $standard = $pointData['standard'];
                            $latestExecution = $pointData['latest_execution'];
                            $condition = $pointData['condition'];
                        @endphp
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow
                            @if($condition == 'normal') border-green-200 bg-green-50
                            @elseif($condition == 'warning') border-yellow-200 bg-yellow-50
                            @elseif($condition == 'caution') border-orange-200 bg-orange-50
                            @elseif($condition == 'critical') border-red-200 bg-red-50
                            @else border-gray-200 bg-gray-50
                            @endif">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h3 class="text-base font-semibold text-gray-900 mb-1">
                                        {{ $pointData['point_name'] }}
                                    </h3>
                                    @if($standard)
                                        <p class="text-xs text-gray-600 mb-1">
                                            Standard: <span class="font-medium">{{ $standard->name }}</span>
                                        </p>
                                        <p class="text-xs text-gray-600">
                                            Range: 
                                            @if($standard->min_value !== null && $standard->max_value !== null)
                                                {{ number_format($standard->min_value, 2) }} - {{ number_format($standard->max_value, 2) }} {{ $standard->unit }}
                                            @elseif($standard->min_value !== null)
                                                Min: {{ number_format($standard->min_value, 2) }} {{ $standard->unit }}
                                            @elseif($standard->max_value !== null)
                                                Max: {{ number_format($standard->max_value, 2) }} {{ $standard->unit }}
                                            @else
                                                -
                                            @endif
                                            @if($standard->target_value !== null)
                                                | Target: {{ number_format($standard->target_value, 2) }} {{ $standard->unit }}
                                            @endif
                                        </p>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full
                                        @if($condition == 'normal') bg-green-100 border border-green-300
                                        @elseif($condition == 'warning') bg-yellow-100 border border-yellow-300
                                        @elseif($condition == 'caution') bg-orange-100 border border-orange-300
                                        @elseif($condition == 'critical') bg-red-100 border border-red-300
                                        @else bg-gray-100 border border-gray-300
                                        @endif">
                                        <div class="w-3 h-3 rounded-full
                                            @if($condition == 'normal') bg-green-500
                                            @elseif($condition == 'warning') bg-yellow-500
                                            @elseif($condition == 'caution') bg-orange-500
                                            @elseif($condition == 'critical') bg-red-500
                                            @else bg-gray-400
                                            @endif"></div>
                                        <span class="text-xs font-semibold
                                            @if($condition == 'normal') text-green-800
                                            @elseif($condition == 'warning') text-yellow-800
                                            @elseif($condition == 'caution') text-orange-800
                                            @elseif($condition == 'critical') text-red-800
                                            @else text-gray-600
                                            @endif">
                                            @if($condition == 'normal') Aman
                                            @elseif($condition == 'warning') Perlu Perhatian
                                            @elseif($condition == 'caution') Perlu Pengawasan
                                            @elseif($condition == 'critical') Perlu Perbaikan
                                            @else Belum Ada Data
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            @if($latestExecution)
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Nilai Pengukuran</label>
                                            <p class="text-sm font-semibold text-gray-900">
                                                @if($latestExecution->measured_value !== null)
                                                    {{ number_format($latestExecution->measured_value, 2) }} {{ $standard->unit ?? '' }}
                                                @else
                                                    -
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Pengukuran</label>
                                            <p class="text-sm font-semibold text-gray-900">
                                                {{ $latestExecution->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Dikerjakan Oleh</label>
                                            <p class="text-sm font-semibold text-gray-900">
                                                {{ $latestExecution->performedBy ? $latestExecution->performedBy->name : ($latestExecution->performed_by ? 'User ID: ' . $latestExecution->performed_by : '-') }}
                                            </p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 mb-1">Actions</label>
                                            <a href="{{ route('predictive-maintenance.controlling.edit', $latestExecution->id) }}" 
                                               class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded shadow transition"
                                               title="Edit Execution">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <p class="text-xs text-gray-500 italic">Belum ada data pengukuran untuk maintenance point ini.</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-500">Tidak ada maintenance point untuk mesin ini pada bulan yang dipilih.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

