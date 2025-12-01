@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Predictive Maintenance - Updating</h1>
                <p class="text-sm text-gray-500 mt-1">Tanggal Hari Ini: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Pending</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] ?? 0 }}</p>
                    </div>
                    <div class="bg-gray-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">In Progress</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $stats['in_progress'] ?? 0 }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Completed</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] ?? 0 }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total to Update</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jadwal to Update Table -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Jadwal to Update</h3>
            @if($jadwalPaginator->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-600">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Machine</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Schedule Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($jadwalPaginator as $jadwal)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-semibold text-gray-900">{{ $jadwal['machine']->idMachine ?? '-' }}</div>
                                    <div class="text-gray-500">{{ $jadwal['machine']->machineType->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">
                                        {{ $jadwal['machine']->plant_name ?? '-' }} / {{ $jadwal['machine']->line_name ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <button onclick="showMaintenancePoints({{ $jadwal['machine_id'] }}, '{{ $jadwal['scheduled_date'] }}')"
                                            class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                        {{ \Carbon\Carbon::parse($jadwal['scheduled_date'])->format('d/m/Y') }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($jadwal['status'] == 'pending')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Pending</span>
                                    @elseif($jadwal['status'] == 'in_progress')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">In Progress</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($jadwal['status']) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $firstExecution = $jadwal['executions'][0] ?? null;
                                    @endphp
                                    @if($firstExecution)
                                        <a href="{{ route('predictive-maintenance.updating.edit', $firstExecution->id) }}"
                                           class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $jadwalPaginator->links() }}
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No jadwal to update.</p>
            @endif
        </div>

        <!-- Completed Jadwal (Information Only) -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Completed Jadwal <span class="text-sm font-normal text-gray-500">(Information Only)</span></h3>
            @if($completedJadwalPaginator->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-green-600">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Machine</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Schedule Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">End Time</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($completedJadwalPaginator as $jadwal)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-semibold text-gray-900">{{ $jadwal['machine']->idMachine ?? '-' }}</div>
                                    <div class="text-gray-500">{{ $jadwal['machine']->machineType->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    <button onclick="showMaintenancePoints({{ $jadwal['machine_id'] }}, '{{ $jadwal['scheduled_date'] }}')"
                                            class="text-green-600 hover:text-green-800 hover:underline">
                                        {{ \Carbon\Carbon::parse($jadwal['scheduled_date'])->format('d/m/Y') }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $jadwal['latest_end_time'] ? \Carbon\Carbon::parse($jadwal['latest_end_time'])->format('d/m/Y H:i') : '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($stats['completed'] > 20)
                    <p class="text-sm text-gray-500 text-center mt-4">Menampilkan 20 jadwal terbaru dari total {{ $stats['completed'] }} completed executions.</p>
                @endif
                <div class="mt-4">
                    {{ $completedJadwalPaginator->links() }}
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No completed jadwal.</p>
            @endif
        </div>
    </div>
</div>

<!-- Modal for Maintenance Points -->
<div id="maintenancePointsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Maintenance Points</h3>
                <button onclick="closeMaintenancePointsModal()" class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="maintenancePointsContent" class="space-y-3">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function showMaintenancePoints(machineId, scheduledDate) {
    const modal = document.getElementById('maintenancePointsModal');
    const content = document.getElementById('maintenancePointsContent');

    content.innerHTML = '<p class="text-center py-4">Loading...</p>';
    modal.classList.remove('hidden');

    const url = `{{ route('predictive-maintenance.updating.get-maintenance-points-by-machine-and-date') }}?machine_id=${machineId}&scheduled_date=${scheduledDate}`;

    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.maintenance_points && data.maintenance_points.length > 0) {
            let html = '';
            data.maintenance_points.forEach((point, index) => {
                const statusClass = point.status === 'completed' ? 'bg-green-100 text-green-800' :
                                   point.status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' :
                                   'bg-gray-100 text-gray-800';

                // Measurement status badge
                let measurementStatusBadge = '';
                if (point.measurement_status) {
                    const statusColors = {
                        'normal': 'bg-green-100 text-green-800',
                        'warning': 'bg-yellow-100 text-yellow-800',
                        'critical': 'bg-red-100 text-red-800'
                    };
                    const statusText = {
                        'normal': 'Normal',
                        'warning': 'Warning',
                        'critical': 'Critical'
                    };
                    measurementStatusBadge = `<span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full ${statusColors[point.measurement_status] || 'bg-gray-100 text-gray-800'}">${statusText[point.measurement_status] || point.measurement_status}</span>`;
                }

                html += `
                    <div class="border rounded p-4 bg-white">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">${point.maintenance_point_name}</h4>
                                ${point.standard_name && point.standard_name !== '-' ? `
                                    <div class="mt-1 text-sm text-blue-600">
                                        <strong>Standard:</strong> ${point.standard_name}
                                        ${point.standard_min !== null && point.standard_max !== null ? ` (${point.standard_min} - ${point.standard_max} ${point.standard_unit || ''})` : ''}
                                    </div>
                                ` : ''}
                                ${point.instruction ? `<p class="text-sm text-gray-600 mt-1">${point.instruction}</p>` : ''}
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">${point.status}</span>
                                ${measurementStatusBadge}
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-600 mt-3">
                            <div><strong>Measured Value:</strong> ${point.measured_value !== null ? point.measured_value + ' ' + (point.standard_unit || '') : '-'}</div>
                            <div><strong>Performed By:</strong> ${point.performed_by || '-'}</div>
                            <div><strong>Start Time:</strong> ${point.actual_start_time || '-'}</div>
                            <div><strong>End Time:</strong> ${point.actual_end_time || '-'}</div>
                        </div>
                        ${point.execution_id ? `
                            <div class="mt-3">
                                <a href="{{ url('predictive-maintenance/updating') }}/${point.execution_id}/edit"
                                   class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </a>
                            </div>
                        ` : ''}
                    </div>
                `;
            });
            content.innerHTML = html;
        } else {
            content.innerHTML = '<p class="text-center py-4 text-gray-500">No maintenance points found for this date.</p>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = '<p class="text-center py-4 text-red-500">Error loading maintenance points.</p>';
    });
}

function closeMaintenancePointsModal() {
    document.getElementById('maintenancePointsModal').classList.add('hidden');
}

document.getElementById('maintenancePointsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMaintenancePointsModal();
    }
});
</script>
@endsection
