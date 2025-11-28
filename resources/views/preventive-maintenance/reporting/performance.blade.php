@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Performance Report</h1>
                <p class="text-sm text-gray-500 mt-1">Monthly Completion Rates & Machine Performance</p>
            </div>
            <a href="{{ route('preventive-maintenance.reporting.index') }}" class="text-gray-600 hover:text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
            <form method="GET" action="{{ route('preventive-maintenance.reporting.performance') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="w-full border rounded px-3 py-2">
                </div>
                <div class="flex items-end">
                    <div class="flex gap-2">
                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-6 rounded shadow transition">
                            Filter
                        </button>
                        <a href="{{ route('preventive-maintenance.reporting.performance') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Monthly Completion Chart -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Completion Rate</h3>
            <div class="h-64">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- Monthly Data Table -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Statistics</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-purple-600">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Month</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Total</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Completed</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Completion Rate</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($monthlyData as $data)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $data['month'] }}</td>
                            <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $data['total'] }}</td>
                            <td class="px-4 py-3 text-sm text-center text-green-600 font-semibold">{{ $data['completed'] }}</td>
                            <td class="px-4 py-3 text-sm text-center">
                                <div class="flex items-center justify-center">
                                    <div class="w-full bg-gray-200 rounded-full h-6 mr-2 max-w-xs">
                                        <div class="h-6 rounded-full flex items-center justify-center text-xs font-semibold text-white transition-all duration-300
                                            @if($data['rate'] >= 80) bg-green-500
                                            @elseif($data['rate'] >= 50) bg-yellow-500
                                            @else bg-red-500
                                            @endif" 
                                            style="width: {{ min($data['rate'], 100) }}%">
                                            @if($data['rate'] > 0){{ number_format($data['rate'], 1) }}%@endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Machine Performance -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Machine Performance ({{ $machinePerformancePaginator->total() }})</h3>
            @if($machinePerformancePaginator->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-purple-600">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Machine</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Total Executions</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Completed</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Completion Rate</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($machinePerformancePaginator as $perf)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-semibold text-gray-900">{{ $perf['machine']->idMachine ?? '-' }}</div>
                                    <div class="text-gray-500">{{ $perf['machine']->machineType->name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $perf['total'] }}</td>
                                <td class="px-4 py-3 text-sm text-center text-green-600 font-semibold">{{ $perf['completed'] }}</td>
                                <td class="px-4 py-3 text-sm text-center">
                                    <div class="flex items-center justify-center">
                                        <div class="w-full bg-gray-200 rounded-full h-6 mr-2 max-w-xs">
                                            <div class="h-6 rounded-full flex items-center justify-center text-xs font-semibold text-white transition-all duration-300
                                                @if($perf['rate'] >= 80) bg-green-500
                                                @elseif($perf['rate'] >= 50) bg-yellow-500
                                                @else bg-red-500
                                                @endif" 
                                                style="width: {{ min($perf['rate'], 100) }}%">
                                                @if($perf['rate'] > 0){{ number_format($perf['rate'], 1) }}%@endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-4">
                    {{ $machinePerformancePaginator->links() }}
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No machine performance data found.</p>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthlyCtx = document.getElementById('monthlyChart');
    if (monthlyCtx) {
        const monthlyData = @json($monthlyData);
        
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: monthlyData.map(d => d.month),
                datasets: [
                    {
                        label: 'Total',
                        data: monthlyData.map(d => d.total),
                        backgroundColor: 'rgb(156, 163, 175)',
                    },
                    {
                        label: 'Completed',
                        data: monthlyData.map(d => d.completed),
                        backgroundColor: 'rgb(34, 197, 94)',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
</script>
@endsection

