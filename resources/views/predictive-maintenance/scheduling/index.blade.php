@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8" x-data="{ filterModalOpen: false }">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Predictive Maintenance - Scheduling</h1>
            <div class="flex items-center gap-3">
                <button type="button" @click="filterModalOpen = true" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                    @if(request()->hasAny(['period_type', 'period_month', 'period_year', 'plant', 'line', 'machine_type', 'search_id_machine']))
                        <span class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 rounded-full border-2 border-white"></span>
                    @endif
                </button>
                <a href="{{ route('predictive-maintenance.scheduling.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Create Schedule
                </a>
            </div>
        </div>
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filter Modal -->
        <div x-show="filterModalOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50"
             @click.away="filterModalOpen = false">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full" @click.stop>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Filter Schedules</h3>
                            <button type="button" @click="filterModalOpen = false" class="text-gray-400 hover:text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <form method="GET" action="{{ route('predictive-maintenance.scheduling.index') }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Period Type</label>
                                    <select name="period_type" class="w-full border rounded px-3 py-2">
                                        <option value="year" {{ $periodType == 'year' ? 'selected' : '' }}>Year</option>
                                        <option value="month" {{ $periodType == 'month' ? 'selected' : '' }}>Month</option>
                                    </select>
                                </div>
                                @if($periodType == 'month')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                                    <select name="period_month" class="w-full border rounded px-3 py-2">
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}" {{ $periodMonth == $i ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create(null, $i, 1)->locale('id')->translatedFormat('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                @endif
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                                    <select name="period_year" class="w-full border rounded px-3 py-2">
                                        @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                                            <option value="{{ $y }}" {{ $periodYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Plant</label>
                                    <select name="plant" class="w-full border rounded px-3 py-2">
                                        <option value="">All Plants</option>
                                        @foreach($plants as $plant)
                                            <option value="{{ $plant->id }}" {{ $plantId == $plant->id ? 'selected' : '' }}>{{ $plant->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Line</label>
                                    <select name="line" class="w-full border rounded px-3 py-2">
                                        <option value="">All Lines</option>
                                        @foreach($lines as $line)
                                            <option value="{{ $line->id }}" {{ $lineId == $line->id ? 'selected' : '' }}>{{ $line->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Machine Type</label>
                                    <select name="machine_type" class="w-full border rounded px-3 py-2">
                                        <option value="">All Machine Types</option>
                                        @foreach($machineTypes as $type)
                                            <option value="{{ $type->id }}" {{ $machineTypeId == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Search ID Machine</label>
                                    <input type="text" name="search_id_machine" value="{{ $searchIdMachine }}" placeholder="ID Machine..." class="w-full border rounded px-3 py-2">
                                </div>
                            </div>
                            <div class="mt-6 flex justify-end gap-3">
                                <a href="{{ route('predictive-maintenance.scheduling.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">
                                    Reset
                                </a>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">
                                    Apply Filter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6 overflow-x-auto">
            @if(count($machinesData) > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">No</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ID Mesin</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama Mesin</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Plant</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Line</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Date</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Schedules</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($machinesData as $key => $data)
                    @php
                        $machine = $data['machine'];
                        $schedules = $data['schedules'];
                        $date = $data['date'];
                        $machineSchedules = $schedulesDataForJs[$machine->id][$key] ?? [];
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-3 text-sm text-gray-900">{{ $loop->iteration }}</td>
                        <td class="px-3 py-3 text-sm font-semibold text-gray-900">{{ $machine->idMachine ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ $machine->machineType->name ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ $machine->room->plant->name ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ $machine->room->line->name ?? '-' }}</td>
                        <td class="px-3 py-3 text-sm text-gray-500">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
                        <td class="px-3 py-3 text-sm">
                            <div class="space-y-1">
                                @foreach($machineSchedules as $schedule)
                                <div class="text-xs bg-gray-100 px-2 py-1 rounded">
                                    <span class="font-semibold">{{ $schedule['maintenance_point_name'] }}</span>
                                    <span class="text-gray-500"> - {{ $schedule['standard_name'] }}</span>
                                    <span class="text-gray-400">({{ $schedule['standard_min'] }}-{{ $schedule['standard_max'] }} {{ $schedule['standard_unit'] }})</span>
                                    <span class="ml-2 px-2 py-0.5 rounded text-xs
                                        @if($schedule['execution_status'] == 'completed') bg-green-100 text-green-800
                                        @elseif($schedule['execution_status'] == 'in_progress') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($schedule['execution_status']) }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('predictive-maintenance.controlling.create', ['type_machine_id' => $machine->machine_type_id, 'machine_id' => $machine->id, 'scheduled_date' => $date]) }}"
                                   class="bg-yellow-600 hover:bg-yellow-700 text-white text-xs px-3 py-1 rounded">
                                    Execute
                                </a>
                                @if(count($schedules) > 0)
                                <a href="{{ route('predictive-maintenance.scheduling.show', $schedules[0]->id) }}"
                                   class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1 rounded">
                                    View
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center py-8">
                <p class="text-gray-500">No schedules found. <a href="{{ route('predictive-maintenance.scheduling.create') }}" class="text-blue-600 hover:underline">Create a new schedule</a></p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
