@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Machine ERP Details</h1>
                <p class="text-sm text-gray-600">View complete information about the machine</p>
            </div>
            <a href="{{ route('machine-erp.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Header Section -->
            <div class="bg-blue-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white">{{ $machineErp->idMachine }}</h2>
                <p class="text-blue-100 text-sm mt-1">{{ $machineErp->type_name ?? 'No Type' }}</p>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column: Basic Information -->
                    <div class="space-y-6">
                        <!-- Machine Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Machine Information</h3>
                            <div class="space-y-3">
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">ID Machine:</span>
                                    <span class="w-2/3 text-sm text-gray-900 font-semibold">{{ $machineErp->idMachine }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Status:</span>
                                    <span class="w-2/3">
                                        @if($machineErp->status)
                                            @php
                                                $statusColors = [
                                                    'Running' => 'bg-green-100 text-green-800',
                                                    'Standby' => 'bg-yellow-100 text-yellow-800',
                                                    'Damage' => 'bg-red-100 text-red-800',
                                                    'Destroy' => 'bg-gray-100 text-gray-800',
                                                    'Other' => 'bg-blue-100 text-blue-800'
                                                ];
                                                $statusColor = $statusColors[$machineErp->status] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                                {{ $machineErp->status }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500">-</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Type Name:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->type_name ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Brand Name:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->brand_name ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Model Name:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->model_name ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Serial Number:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->serial_number ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Tahun Production:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->tahun_production ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">No Document:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->no_document ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Location Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Location Information</h3>
                            <div class="space-y-3">
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Plant Name:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->plant_name ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Process Name:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->process_name ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Line Name:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->line_name ?? '-' }}</span>
                                </div>
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Room Name:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->room_name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Machine Type Information -->
                        @if($machineErp->machineType)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Machine Type Details</h3>
                            <div class="space-y-3">
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Name:</span>
                                    <span class="w-2/3 text-sm text-gray-900 font-semibold">{{ $machineErp->machineType->name }}</span>
                                </div>
                                @if($machineErp->machineType->brand)
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Brand:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->machineType->brand }}</span>
                                </div>
                                @endif
                                @if($machineErp->machineType->model)
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Model:</span>
                                    <span class="w-2/3 text-sm text-gray-900">{{ $machineErp->machineType->model }}</span>
                                </div>
                                @endif
                                @if($machineErp->machineType->description)
                                <div class="flex flex-col">
                                    <span class="w-full text-sm font-medium text-gray-600 mb-1">Description:</span>
                                    <span class="w-full text-sm text-gray-900">{{ $machineErp->machineType->description }}</span>
                                </div>
                                @endif
                                @if($machineErp->machineType->groupRelation)
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Group:</span>
                                    <span class="w-2/3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ $machineErp->machineType->groupRelation->name }}
                                        </span>
                                    </span>
                                </div>
                                @endif
                                @if($machineErp->machineType->systems->isNotEmpty())
                                <div class="flex">
                                    <span class="w-1/3 text-sm font-medium text-gray-600">Systems:</span>
                                    <span class="w-2/3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($machineErp->machineType->systems as $system)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $system->nama_sistem }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Status & Maintenance Information -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Status & Maintenance</h3>
                            
                            <!-- Status Mesin -->
                            <div class="mb-4">
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <span class="text-sm font-medium text-gray-700">Status Mesin:</span>
                                    @if($machineErp->status)
                                        @php
                                            $statusColors = [
                                                'Running' => 'bg-green-100 text-green-800',
                                                'Standby' => 'bg-yellow-100 text-yellow-800',
                                                'Damage' => 'bg-red-100 text-red-800',
                                                'Destroy' => 'bg-gray-100 text-gray-800',
                                                'Other' => 'bg-blue-100 text-blue-800'
                                            ];
                                            $statusColor = $statusColors[$machineErp->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $statusColor }}">
                                            {{ $machineErp->status }}
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-500">-</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Last Maintenance -->
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Last Maintenance</h4>
                                <p class="text-xs text-gray-500 mb-3">Menampilkan last maintenance untuk setiap jenis jika tersedia</p>
                                <div class="space-y-2">
                                    {{-- Last Downtime --}}
                                    @if($lastMaintenance['downtime'])
                                        <div class="p-3 bg-blue-50 rounded-lg border border-blue-200 hover:bg-blue-100 transition">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-semibold text-blue-900 flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Downtime
                                                </span>
                                                <span class="text-xs font-medium text-blue-700">
                                                    @if($lastMaintenance['downtime']['date'] instanceof \Carbon\Carbon)
                                                        {{ $lastMaintenance['downtime']['date']->format('d M Y') }}
                                                    @else
                                                        {{ is_string($lastMaintenance['downtime']['date']) ? \Carbon\Carbon::parse($lastMaintenance['downtime']['date'])->format('d M Y') : $lastMaintenance['downtime']['date'] }}
                                                    @endif
                                                </span>
                                            </div>
                                            @if(isset($lastMaintenance['downtime']['problem']) && $lastMaintenance['downtime']['problem'])
                                                <p class="text-xs text-gray-700 mt-1">
                                                    <strong>Problem:</strong> {{ $lastMaintenance['downtime']['problem'] }}
                                                </p>
                                            @endif
                                            @if(isset($lastMaintenance['downtime']['action']) && $lastMaintenance['downtime']['action'])
                                                <p class="text-xs text-gray-700">
                                                    <strong>Action:</strong> {{ $lastMaintenance['downtime']['action'] }}
                                                </p>
                                            @endif
                                        </div>
                                    @else
                                        <div class="p-2 bg-gray-50 rounded-lg border border-gray-200">
                                            <span class="text-xs text-gray-500 flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Downtime: <span class="ml-1 text-gray-400">-</span>
                                            </span>
                                        </div>
                                    @endif

                                    {{-- Last Preventive Maintenance (PM) --}}
                                    @if($lastMaintenance['preventive'])
                                        <div class="p-3 bg-green-50 rounded-lg border border-green-200 hover:bg-green-100 transition">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-semibold text-green-900 flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                    </svg>
                                                    PM (Preventive Maintenance)
                                                </span>
                                                <span class="text-xs font-medium text-green-700">
                                                    @if($lastMaintenance['preventive']['date'] instanceof \Carbon\Carbon)
                                                        {{ $lastMaintenance['preventive']['date']->format('d M Y H:i') }}
                                                    @else
                                                        {{ is_string($lastMaintenance['preventive']['date']) ? \Carbon\Carbon::parse($lastMaintenance['preventive']['date'])->format('d M Y H:i') : $lastMaintenance['preventive']['date'] }}
                                                    @endif
                                                </span>
                                            </div>
                                            @if(isset($lastMaintenance['preventive']['title']) && $lastMaintenance['preventive']['title'])
                                                <p class="text-xs text-gray-700 mt-1">
                                                    <strong>Title:</strong> {{ $lastMaintenance['preventive']['title'] }}
                                                </p>
                                            @endif
                                            @if(isset($lastMaintenance['preventive']['performed_by']) && $lastMaintenance['preventive']['performed_by'])
                                                <p class="text-xs text-gray-700">
                                                    <strong>Performed By:</strong> {{ $lastMaintenance['preventive']['performed_by'] }}
                                                </p>
                                            @endif
                                        </div>
                                    @else
                                        <div class="p-2 bg-gray-50 rounded-lg border border-gray-200">
                                            <span class="text-xs text-gray-500 flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                </svg>
                                                PM (Preventive Maintenance): <span class="ml-1 text-gray-400">-</span>
                                            </span>
                                        </div>
                                    @endif

                                    {{-- Last Predictive Maintenance (PDM) --}}
                                    @if($lastMaintenance['predictive'])
                                        <div class="p-3 bg-purple-50 rounded-lg border border-purple-200 hover:bg-purple-100 transition">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-semibold text-purple-900 flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                    </svg>
                                                    PDM (Predictive Maintenance)
                                                </span>
                                                <span class="text-xs font-medium text-purple-700">
                                                    @if($lastMaintenance['predictive']['date'] instanceof \Carbon\Carbon)
                                                        {{ $lastMaintenance['predictive']['date']->format('d M Y H:i') }}
                                                    @else
                                                        {{ is_string($lastMaintenance['predictive']['date']) ? \Carbon\Carbon::parse($lastMaintenance['predictive']['date'])->format('d M Y H:i') : $lastMaintenance['predictive']['date'] }}
                                                    @endif
                                                </span>
                                            </div>
                                            @if(isset($lastMaintenance['predictive']['title']) && $lastMaintenance['predictive']['title'])
                                                <p class="text-xs text-gray-700 mt-1">
                                                    <strong>Title:</strong> {{ $lastMaintenance['predictive']['title'] }}
                                                </p>
                                            @endif
                                            @if(isset($lastMaintenance['predictive']['performed_by']) && $lastMaintenance['predictive']['performed_by'])
                                                <p class="text-xs text-gray-700">
                                                    <strong>Performed By:</strong> {{ $lastMaintenance['predictive']['performed_by'] }}
                                                </p>
                                            @endif
                                            @if(isset($lastMaintenance['predictive']['measurement_status']) && $lastMaintenance['predictive']['measurement_status'])
                                                @php
                                                    $measurementColors = [
                                                        'normal' => 'bg-green-100 text-green-800',
                                                        'warning' => 'bg-yellow-100 text-yellow-800',
                                                        'critical' => 'bg-red-100 text-red-800'
                                                    ];
                                                    $measurementColor = $measurementColors[$lastMaintenance['predictive']['measurement_status']] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <p class="text-xs text-gray-700 mt-1">
                                                    <strong>Measurement Status:</strong> 
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $measurementColor }}">
                                                        {{ ucfirst($lastMaintenance['predictive']['measurement_status']) }}
                                                    </span>
                                                </p>
                                            @endif
                                        </div>
                                    @else
                                        <div class="p-2 bg-gray-50 rounded-lg border border-gray-200">
                                            <span class="text-xs text-gray-500 flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                </svg>
                                                PDM (Predictive Maintenance): <span class="ml-1 text-gray-400">-</span>
                                            </span>
                                        </div>
                                    @endif

                                    {{-- Last Inspection/Checklist --}}
                                    @if($lastMaintenance['checklist'])
                                        <div class="p-3 bg-orange-50 rounded-lg border border-orange-200 hover:bg-orange-100 transition">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-semibold text-orange-900 flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                    </svg>
                                                    Inspeksi/Checklist
                                                </span>
                                                <span class="text-xs font-medium text-orange-700">
                                                    @if(isset($lastMaintenance['checklist']['date']) && $lastMaintenance['checklist']['date'] instanceof \Carbon\Carbon)
                                                        {{ $lastMaintenance['checklist']['date']->format('d M Y') }}
                                                    @elseif(isset($lastMaintenance['checklist']['date']) && is_string($lastMaintenance['checklist']['date']))
                                                        {{ \Carbon\Carbon::parse($lastMaintenance['checklist']['date'])->format('d M Y') }}
                                                    @else
                                                        {{ $lastMaintenance['checklist']['date'] ?? '-' }}
                                                    @endif
                                                </span>
                                            </div>
                                            @if(isset($lastMaintenance['checklist']['template']) && $lastMaintenance['checklist']['template'])
                                                <p class="text-xs text-gray-700 mt-1">
                                                    <strong>Template:</strong> {{ $lastMaintenance['checklist']['template'] }}
                                                </p>
                                            @endif
                                            @if(isset($lastMaintenance['checklist']['performed_by']) && $lastMaintenance['checklist']['performed_by'])
                                                <p class="text-xs text-gray-700">
                                                    <strong>Performed By:</strong> {{ $lastMaintenance['checklist']['performed_by'] }}
                                                </p>
                                            @endif
                                        </div>
                                    @else
                                        <div class="p-2 bg-gray-50 rounded-lg border border-gray-200">
                                            <span class="text-xs text-gray-500 flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                                </svg>
                                                Inspeksi/Checklist: <span class="ml-1 text-gray-400">-</span>
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Sparepart Usage -->
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Sparepart Usage</h4>
                                <p class="text-xs text-gray-500 mb-3">Sparepart yang sudah digunakan untuk mesin ini</p>
                                @if(!empty($sparepartUsage) && count($sparepartUsage) > 0)
                                    <div class="max-h-64 overflow-y-auto space-y-2">
                                        @foreach($sparepartUsage as $part)
                                            @if(isset($part['is_string']) && $part['is_string'])
                                                {{-- Display as string if not found in PartErp --}}
                                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <span class="text-sm font-semibold text-gray-900">{{ $part['name'] }}</span>
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-700">
                                                            Not in Inventory
                                                        </span>
                                                    </div>
                                                </div>
                                            @else
                                                {{-- Display from PartErp model --}}
                                                <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition">
                                                    <div class="flex items-center justify-between mb-1">
                                                        <span class="text-sm font-semibold text-gray-900">{{ $part->name }}</span>
                                                        @if($part->stock !== null)
                                                            @php
                                                                $stockStatus = '';
                                                                if ($part->minimum_stock && $part->stock < $part->minimum_stock) {
                                                                    $stockStatus = 'bg-red-100 text-red-800';
                                                                } elseif ($part->minimum_stock && $part->stock == $part->minimum_stock) {
                                                                    $stockStatus = 'bg-yellow-100 text-yellow-800';
                                                                } else {
                                                                    $stockStatus = 'bg-green-100 text-green-800';
                                                                }
                                                            @endphp
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $stockStatus }}">
                                                                Stock: {{ $part->stock }} {{ $part->unit ?? 'pcs' }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if($part->part_number)
                                                        <p class="text-xs text-gray-600">Part Number: {{ $part->part_number }}</p>
                                                    @endif
                                                    @if($part->minimum_stock !== null)
                                                        <p class="text-xs text-gray-600">Min. Stock: {{ $part->minimum_stock }} {{ $part->unit ?? 'pcs' }}</p>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-center">
                                        <p class="text-xs text-gray-500">Belum ada sparepart yang digunakan untuk mesin ini</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Photos -->
                    <div class="space-y-6">
                        <!-- Photo dari Machine Type -->
                        @if($machineErp->machineType)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                                Photo dari Machine Type
                                <span class="text-xs font-normal text-gray-500 block mt-1">(Photo default dari Machine Type: {{ $machineErp->machineType->name }})</span>
                            </h3>
                            @php
                                $machineTypePhotoUrl = null;
                                // Use photo_url accessor from MachineType model (prioritizes photo_id, fallback to photo path)
                                if ($machineErp->machineType) {
                                    if ($machineErp->machineType->photo_id && $machineErp->machineType->photoModel) {
                                        $machineTypePhotoUrl = route('photos.show', $machineErp->machineType->photo_id);
                                    } elseif ($machineErp->machineType->photo) {
                                        // Try to find in photos table
                                        $photo = \App\Models\Photo::where('file_path', $machineErp->machineType->photo)
                                            ->orWhere('file_path', 'like', '%' . basename($machineErp->machineType->photo))
                                            ->first();
                                        if ($photo) {
                                            $machineTypePhotoUrl = route('photos.show', $photo->id);
                                        } else {
                                            // Fallback to old path
                                            $photoPath = $machineErp->machineType->photo;
                                            $webpPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $photoPath);
                                            $photoExists = \Illuminate\Support\Facades\Storage::disk('public')->exists($photoPath);
                                            $webpExists = \Illuminate\Support\Facades\Storage::disk('public')->exists($webpPath);
                                            
                                            $actualPath = $webpExists ? $webpPath : $photoPath;
                                            if ($photoExists || $webpExists) {
                                                $machineTypePhotoUrl = asset('public-storage/' . $actualPath);
                                            }
                                        }
                                    }
                                }
                            @endphp
                            @if($machineTypePhotoUrl)
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <img src="{{ $machineTypePhotoUrl }}" 
                                         alt="Machine Type Photo" 
                                         class="w-full h-auto rounded-lg shadow-md object-cover"
                                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <div style="display:none;" class="text-sm text-red-500 mt-2">
                                        Photo tidak dapat dimuat
                                    </div>
                                </div>
                            @else
                                <div class="bg-gray-50 rounded-lg p-8 border border-gray-200 text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-gray-500 text-sm">Machine Type belum memiliki photo</p>
                                </div>
                            @endif
                        </div>
                        @endif

                        <!-- Photo khusus per ID Mesin -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                                Photo Khusus Mesin
                                <span class="text-xs font-normal text-gray-500 block mt-1">(Photo khusus untuk ID Mesin ini)</span>
                            </h3>
                            @php
                                $machinePhotoUrl = null;
                                // Prioritize photo_id (new system), then photo field (legacy)
                                if ($machineErp->photo_id && $machineErp->photoModel) {
                                    $machinePhotoUrl = route('photos.show', $machineErp->photo_id);
                                } elseif ($machineErp->photo) {
                                    // Try to find in photos table
                                    $photo = \App\Models\Photo::where('file_path', $machineErp->photo)
                                        ->orWhere('file_path', 'like', '%' . basename($machineErp->photo))
                                        ->first();
                                    if ($photo) {
                                        $machinePhotoUrl = route('photos.show', $photo->id);
                                    } else {
                                        // Fallback to old path
                                        $photoPath = $machineErp->photo;
                                        $webpPath = preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $photoPath);
                                        $photoExists = \Illuminate\Support\Facades\Storage::disk('public')->exists($photoPath);
                                        $webpExists = \Illuminate\Support\Facades\Storage::disk('public')->exists($webpPath);
                                        
                                        $actualPath = $webpExists ? $webpPath : $photoPath;
                                        if ($photoExists || $webpExists) {
                                            $machinePhotoUrl = asset('public-storage/' . $actualPath);
                                        }
                                    }
                                }
                            @endphp
                            @if($machinePhotoUrl)
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <img src="{{ $machinePhotoUrl }}" 
                                         alt="Machine Photo (Khusus)" 
                                         class="w-full h-auto rounded-lg shadow-md object-cover"
                                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='block';">
                                    <div style="display:none;" class="text-sm text-red-500 mt-2">
                                        Photo tidak dapat dimuat
                                    </div>
                                </div>
                            @else
                                <div class="bg-gray-50 rounded-lg p-8 border border-gray-200 text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-gray-500 text-sm">Belum ada photo khusus untuk mesin ini</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 pt-6 border-t border-gray-200 flex items-center gap-3">
                    <a href="{{ route('machine-erp.edit', $machineErp->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit
                    </a>
                    <form action="{{ route('machine-erp.destroy', $machineErp->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center" onclick="return confirm('Delete this machine ERP?')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

