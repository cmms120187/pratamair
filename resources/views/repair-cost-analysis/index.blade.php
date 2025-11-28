@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Analisa Biaya Perbaikan</h1>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form method="GET" action="{{ route('repair-cost-analysis.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                    <select name="month" id="month" class="w-full border rounded px-3 py-2 text-sm" onchange="this.form.submit()">
                        <option value="all" {{ $selectedMonth == 'all' ? 'selected' : '' }}>All Bulan</option>
                        @foreach($months as $m)
                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                    <select name="year" id="year" class="w-full border rounded px-3 py-2 text-sm" onchange="this.form.submit()">
                        <option value="all" {{ $selectedYear == 'all' ? 'selected' : '' }}>All Tahun</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="plant" class="block text-sm font-medium text-gray-700 mb-2">Plant</label>
                    <select name="plant" id="plant" class="w-full border rounded px-3 py-2 text-sm" onchange="this.form.submit()">
                        <option value="all" {{ ($selectedPlant ?? 'all') == 'all' ? 'selected' : '' }}>All Plant</option>
                        @foreach($plants as $plant)
                            <option value="{{ $plant->id }}" {{ ($selectedPlant ?? 'all') == $plant->id ? 'selected' : '' }}>
                                {{ $plant->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="include_service_cost" class="block text-sm font-medium text-gray-700 mb-2">Include Biaya Jasa</label>
                    <select name="include_service_cost" id="include_service_cost" class="w-full border rounded px-3 py-2 text-sm" onchange="this.form.submit()">
                        <option value="1" {{ $includeServiceCost ? 'selected' : '' }}>Ya (Include)</option>
                        <option value="0" {{ !$includeServiceCost ? 'selected' : '' }}>Tidak (Exclude)</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <a href="{{ route('repair-cost-analysis.index') }}" class="w-full px-4 py-2 border border-red-300 rounded text-red-700 hover:bg-red-50 text-sm font-semibold text-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Info Banner -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm text-blue-700">
                    <strong>Mode:</strong> {{ $includeServiceCost ? 'Include Biaya Jasa' : 'Exclude Biaya Jasa (Hanya Sparepart)' }}
                    @if(!$includeServiceCost)
                        <span class="text-xs italic"> - Hanya menghitung biaya dari sparepart yang digunakan</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 mb-1">Total Biaya</div>
                <div class="text-2xl font-bold text-red-600">Rp {{ number_format($stats['total_cost'], 0, ',', '.') }}</div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 mb-1">Biaya Downtime</div>
                <div class="text-2xl font-bold text-blue-600">Rp {{ number_format($stats['downtime_cost'], 0, ',', '.') }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ $stats['downtime_count'] }} downtime</div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500 mb-1">Biaya Work Order</div>
                <div class="text-2xl font-bold text-green-600">Rp {{ number_format($stats['work_order_cost'], 0, ',', '.') }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ $stats['work_order_count'] }} work order</div>
                <div class="text-xs text-gray-400 mt-1 italic">*Termasuk biaya dari AM, PM, dan PdM</div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Cost by Category -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Biaya per Kategori</h2>
                <p class="text-xs text-gray-500 mb-2 italic">*Work Order mencakup biaya perbaikan dari AM, PM, dan PdM</p>
                <div class="relative" style="height: 350px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <!-- Cost by Plant -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Biaya per Plant</h2>
                <div class="relative" style="height: 350px;">
                    <canvas id="plantChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Cost by Problem -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Top 10 Biaya per Problem</h2>
                <div class="relative" style="height: 400px;">
                    <canvas id="problemChart"></canvas>
                </div>
            </div>

            <!-- Cost by Machine -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Top 15 Biaya per Mesin</h2>
                <div class="relative" style="height: 400px;">
                    <canvas id="machineChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Trend -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Trend Biaya Bulanan</h2>
            <div class="relative" style="height: 400px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const costByCategory = @json($costByCategory);
    const costByPlant = @json($costByPlant);
    const costByProblem = @json($costByProblem);
    const costByMachine = @json($costByMachine);
    const monthlyTrend = @json($monthlyTrend);

    // Category Chart (Doughnut)
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        const labels = Object.keys(costByCategory);
        const data = Object.values(costByCategory);
        const colors = [
            'rgba(59, 130, 246, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(245, 158, 11, 0.8)'
        ];

        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderColor: colors.map(c => c.replace('0.8', '1')),
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: Rp ${value.toLocaleString('id-ID')} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Plant Chart (Bar)
    const plantCtx = document.getElementById('plantChart');
    if (plantCtx && Object.keys(costByPlant).length > 0) {
        const labels = Object.keys(costByPlant);
        const data = Object.values(costByPlant);

        new Chart(plantCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Biaya (Rp)',
                    data: data,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Biaya: Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            }
                        }
                    }
                }
            }
        });
    }

    // Problem Chart (Bar)
    const problemCtx = document.getElementById('problemChart');
    if (problemCtx && costByProblem.length > 0) {
        const labels = costByProblem.map(p => p.name.length > 20 ? p.name.substring(0, 20) + '...' : p.name);
        const data = costByProblem.map(p => p.total_cost);

        new Chart(problemCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Biaya (Rp)',
                    data: data,
                    backgroundColor: 'rgba(239, 68, 68, 0.7)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Biaya: Rp ' + context.parsed.x.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            }
                        }
                    }
                }
            }
        });
    }

    // Machine Chart (Bar)
    const machineCtx = document.getElementById('machineChart');
    if (machineCtx && Object.keys(costByMachine).length > 0) {
        const labels = Object.keys(costByMachine);
        const data = Object.values(costByMachine);

        new Chart(machineCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Biaya (Rp)',
                    data: data,
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Biaya: Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45,
                            font: { size: 10 }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            }
                        }
                    }
                }
            }
        });
    }

    // Monthly Trend Chart
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx && Object.keys(monthlyTrend).length > 0) {
        const trendData = Object.values(monthlyTrend);
        const labels = trendData.map(t => t.label);
        const downtimeData = trendData.map(t => t.downtime);
        const workOrderData = trendData.map(t => t.work_order);

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Downtime',
                        data: downtimeData,
                        borderColor: 'rgba(59, 130, 246, 1)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Work Order (AM, PM, PdM)',
                        data: workOrderData,
                        borderColor: 'rgba(16, 185, 129, 1)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
