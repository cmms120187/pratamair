@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Schedule Report</h1>
                <p class="text-sm text-gray-500 mt-1">Preventive Maintenance Schedules</p>
            </div>
            <a href="{{ route('preventive-maintenance.reporting.index') }}" class="text-gray-600 hover:text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
            <form method="GET" action="{{ route('preventive-maintenance.reporting.schedule') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="machine_id" class="block text-sm font-medium text-gray-700 mb-2">Machine</label>
                    <select name="machine_id" id="machine_id" class="w-full border rounded px-3 py-2">
                        <option value="">All Machines</option>
                        @foreach($machines as $machine)
                            <option value="{{ $machine->id }}" {{ $machineId == $machine->id ? 'selected' : '' }}>
                                {{ $machine->idMachine }} - {{ $machine->machineType->name ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" id="status" class="w-full border rounded px-3 py-2">
                        <option value="">All Status</option>
                        <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="md:col-span-4 flex justify-end gap-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">
                        Filter
                    </button>
                    <a href="{{ route('preventive-maintenance.reporting.schedule') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Report Table -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Jadwal ({{ $jadwalPaginator->total() }})</h3>
                <button onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>
            </div>
            
            @if($jadwalPaginator->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-600">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Machine</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Schedule Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Assigned To</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Points Count</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($jadwalPaginator as $jadwal)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-semibold text-gray-900">{{ $jadwal['machine']->idMachine ?? '-' }}</div>
                                    <div class="text-gray-500">{{ $jadwal['machine']->machineType->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <button onclick="showSchedulePoints({{ $jadwal['machine_id'] }}, '{{ $jadwal['start_date'] }}')" 
                                            class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                        {{ \Carbon\Carbon::parse($jadwal['start_date'])->format('d/m/Y') }}
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    {{ $jadwal['assignedUser']->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-center text-gray-500">
                                    {{ count($jadwal['schedules']) }} point(s)
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button onclick="showSchedulePoints({{ $jadwal['machine_id'] }}, '{{ $jadwal['start_date'] }}')" 
                                            class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
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
                <p class="text-gray-500 text-center py-8">No schedules found for the selected criteria.</p>
            @endif
        </div>
    </div>
</div>

<!-- Modal for Schedule Points -->
<div id="schedulePointsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Maintenance Points</h3>
                <button onclick="closeSchedulePointsModal()" class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="schedulePointsContent" class="space-y-3">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function showSchedulePoints(machineId, scheduleDate) {
    const modal = document.getElementById('schedulePointsModal');
    const content = document.getElementById('schedulePointsContent');
    
    // Show loading
    content.innerHTML = '<p class="text-center py-4">Loading...</p>';
    modal.classList.remove('hidden');
    
    // Fetch maintenance points
    const url = `{{ route('preventive-maintenance.reporting.get-schedule-points-by-machine-and-date') }}?machine_id=${machineId}&schedule_date=${scheduleDate}`;
    
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
                html += `
                    <div class="border rounded p-4 bg-white">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">${point.maintenance_point_name}</h4>
                                ${point.instruction ? `<p class="text-sm text-gray-600 mt-1">${point.instruction}</p>` : ''}
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-600 mt-3">
                            <div><strong>Frequency:</strong> ${point.frequency}</div>
                            <div><strong>Assigned To:</strong> ${point.assigned_to}</div>
                            <div><strong>Preferred Time:</strong> ${point.preferred_time}</div>
                            <div><strong>Estimated Duration:</strong> ${point.estimated_duration}</div>
                        </div>
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

function closeSchedulePointsModal() {
    document.getElementById('schedulePointsModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('schedulePointsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSchedulePointsModal();
    }
});
</script>
@endsection
