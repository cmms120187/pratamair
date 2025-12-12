@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">OEE Report (Overall Equipment Effectiveness)</h1>
        </div>
        
        <!-- OEE Information Card -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow p-6 mb-6 border-l-4 border-blue-600">
            <h2 class="text-lg font-bold text-gray-800 mb-3">Tentang OEE</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                <div>
                    <p class="mb-2"><strong>OEE (Overall Equipment Effectiveness)</strong> adalah metrik standar industri untuk mengukur efisiensi peralatan produksi. OEE dihitung dari tiga komponen utama:</p>
                    <ul class="list-disc list-inside space-y-1 ml-2">
                        <li><strong>Availability (Ketersediaan):</strong> Persentase waktu operasi dibandingkan waktu produksi yang direncanakan</li>
                        <li><strong>Performance (Kinerja):</strong> Persentase output aktual dibandingkan output target</li>
                        <li><strong>Quality (Kualitas):</strong> Persentase produk berkualitas baik (Grade A) dibandingkan total produksi</li>
                    </ul>
                </div>
                <div>
                    <p class="mb-2"><strong>Rumus OEE:</strong></p>
                    <p class="mb-2 font-mono bg-white p-2 rounded">OEE = Availability × Performance × Quality</p>
                    <p class="mb-2"><strong>Target OEE:</strong></p>
                    <ul class="list-disc list-inside space-y-1 ml-2">
                        <li><span class="text-green-600 font-semibold">≥ 85%</span> - World Class (Sangat Baik)</li>
                        <li><span class="text-yellow-600 font-semibold">60-84%</span> - Baik (Perlu Perbaikan)</li>
                        <li><span class="text-red-600 font-semibold">&lt; 60%</span> - Perlu Perhatian Serius</li>
                    </ul>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
            <form method="GET" action="{{ route('oee.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="line_id" class="block text-sm font-medium text-gray-700 mb-2">Line</label>
                    <select name="line_id" id="line_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Semua Line --</option>
                        @foreach($lines as $line)
                            <option value="{{ $line->id }}" {{ $lineId == $line->id ? 'selected' : '' }}>
                                {{ $line->name }} ({{ $line->process->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="process_id" class="block text-sm font-medium text-gray-700 mb-2">Process</label>
                    <select name="process_id" id="process_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Semua Process --</option>
                        @foreach($processes as $process)
                            <option value="{{ $process->id }}" {{ $processId == $process->id ? 'selected' : '' }}>
                                {{ $process->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- OEE Chart by Plant -->
        @if(count($oeeData) > 0 && count($plantOeeData['labels']) > 0)
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Grafik OEE per Plant (Production)</h2>
            <p class="text-sm text-gray-600 mb-4">Rata-rata OEE untuk setiap Plant dengan kategori Production</p>
            <div class="relative" style="height: 400px;">
                <canvas id="plantOeeChart"></canvas>
            </div>
        </div>
        @endif

        <!-- OEE Chart -->
        @if(count($oeeData) > 0 && count($summaryData['labels']) > 0)
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Grafik OEE per Hari</h2>
            <p class="text-sm text-gray-600 mb-4">Trend OEE Components per Hari</p>
            <div class="relative" style="height: 400px;">
                <canvas id="oeeChart"></canvas>
            </div>
        </div>
        @endif

        <!-- OEE Summary Cards -->
        @if(count($oeeData) > 0)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="text-sm font-medium text-gray-600 mb-1">Rata-rata Availability</div>
                <div class="text-3xl font-bold text-blue-600">
                    {{ number_format(collect($oeeData)->avg('availability'), 2, ',', '.') }}%
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <div class="text-sm font-medium text-gray-600 mb-1">Rata-rata Performance</div>
                <div class="text-3xl font-bold text-yellow-600">
                    {{ number_format(collect($oeeData)->avg('performance'), 2, ',', '.') }}%
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="text-sm font-medium text-gray-600 mb-1">Rata-rata Quality</div>
                <div class="text-3xl font-bold text-green-600">
                    {{ number_format(collect($oeeData)->avg('quality'), 2, ',', '.') }}%
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                <div class="text-sm font-medium text-gray-600 mb-1">Rata-rata OEE</div>
                <div class="text-3xl font-bold text-purple-600">
                    {{ number_format(collect($oeeData)->avg('oee'), 2, ',', '.') }}%
                </div>
            </div>
        </div>
        @endif

        <div class="bg-white rounded-lg shadow p-4 sm:p-6 overflow-x-auto">
            @if(count($oeeData) > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Plant</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Process</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Line</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Jam Produksi</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Downtime (Menit)</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Operating Time</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Target Output</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actual Output</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Grade A</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Total Produksi</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Availability (%)</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Performance (%)</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Quality (%)</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">OEE (%)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($oeeData as $index => $data)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center align-middle">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center align-middle">{{ $data['production_date']->format('Y-m-d') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center align-middle">{{ $data['plant']->name ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center align-middle">{{ $data['process']->name ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center align-middle">{{ $data['line']->name ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-center align-middle">
                            {{ number_format($data['production_hours'], 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-center align-middle">
                            {{ number_format($data['total_downtime_minutes'], 0, ',', '.') }}
                            @if($data['downtime_count'] > 0)
                                <span class="text-xs text-gray-500">({{ $data['downtime_count'] }}x)</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-center align-middle">
                            {{ number_format($data['operating_time'], 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-center align-middle">
                            {{ number_format($data['target_output'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-center align-middle">
                            {{ number_format($data['total_production'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-center align-middle">
                            {{ number_format($data['grade_a'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-center align-middle">
                            {{ number_format($data['total_production'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center align-middle">
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $data['availability'] >= 90 ? 'bg-green-100 text-green-800' : ($data['availability'] >= 70 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ number_format($data['availability'], 2, ',', '.') }}%
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center align-middle">
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $data['performance'] >= 90 ? 'bg-green-100 text-green-800' : ($data['performance'] >= 70 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ number_format($data['performance'], 2, ',', '.') }}%
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center align-middle">
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $data['quality'] >= 90 ? 'bg-green-100 text-green-800' : ($data['quality'] >= 70 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ number_format($data['quality'], 2, ',', '.') }}%
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center align-middle">
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $data['oee'] >= 85 ? 'bg-green-100 text-green-800' : ($data['oee'] >= 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ number_format($data['oee'], 2, ',', '.') }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                @if(count($oeeData) > 1)
                <tfoot class="bg-gray-100">
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-sm font-semibold text-gray-700 text-center align-middle">Rata-rata:</td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-700 text-center align-middle">
                            {{ number_format(collect($oeeData)->avg('production_hours'), 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-700 text-center align-middle">
                            {{ number_format(collect($oeeData)->avg('total_downtime_minutes'), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-700 text-center align-middle">
                            {{ number_format(collect($oeeData)->avg('operating_time'), 2, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-700 text-center align-middle">
                            {{ number_format(collect($oeeData)->sum('target_output'), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-700 text-center align-middle">
                            {{ number_format(collect($oeeData)->sum('total_production'), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-700 text-center align-middle">
                            {{ number_format(collect($oeeData)->sum('grade_a'), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-700 text-center align-middle">
                            {{ number_format(collect($oeeData)->sum('total_production'), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-700 text-center align-middle">
                            {{ number_format(collect($oeeData)->avg('availability'), 2, ',', '.') }}%
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-700 text-center align-middle">
                            {{ number_format(collect($oeeData)->avg('performance'), 2, ',', '.') }}%
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-700 text-center align-middle">
                            {{ number_format(collect($oeeData)->avg('quality'), 2, ',', '.') }}%
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-700 text-center align-middle">
                            {{ number_format(collect($oeeData)->avg('oee'), 2, ',', '.') }}%
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
            @else
            <div class="text-center py-8">
                <p class="text-gray-500">Tidak ada data OEE untuk periode yang dipilih.</p>
                <p class="text-sm text-gray-400 mt-2">Pastikan ada data produksi per hari dan downtime dengan status "Include OEE" = Yes.</p>
            </div>
            @endif
        </div>
    </div>
</div>

@if(count($oeeData) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.register(ChartDataLabels);
    
    // Plant OEE Chart
    @if(count($plantOeeData['labels']) > 0)
    const plantCtx = document.getElementById('plantOeeChart');
    if (plantCtx) {
        const plantOeeData = @json($plantOeeData);
        
        new Chart(plantCtx, {
            type: 'bar',
            data: {
                labels: plantOeeData.labels,
                datasets: [
                    {
                        label: 'Availability (%)',
                        data: plantOeeData.availability,
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Performance (%)',
                        data: plantOeeData.performance,
                        backgroundColor: 'rgba(234, 179, 8, 0.7)',
                        borderColor: 'rgba(234, 179, 8, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Quality (%)',
                        data: plantOeeData.quality,
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'OEE (%)',
                        data: plantOeeData.oee,
                        backgroundColor: 'rgba(168, 85, 247, 0.7)',
                        borderColor: 'rgba(168, 85, 247, 1)',
                        borderWidth: 2
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
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        formatter: function(value) {
                            return value.toFixed(2) + '%';
                        },
                        color: '#333',
                        font: {
                            weight: 'bold',
                            size: 11
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        stacked: false,
                    }
                }
            }
        });
    }
    @endif

    // Daily OEE Chart
    @if(count($summaryData['labels']) > 0)
    const ctx = document.getElementById('oeeChart');
    if (ctx) {
        const summaryData = @json($summaryData);
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: summaryData.labels,
                datasets: [
                    {
                        label: 'Availability (%)',
                        data: summaryData.availability,
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Performance (%)',
                        data: summaryData.performance,
                        backgroundColor: 'rgba(234, 179, 8, 0.7)',
                        borderColor: 'rgba(234, 179, 8, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Quality (%)',
                        data: summaryData.quality,
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'OEE (%)',
                        data: summaryData.oee,
                        backgroundColor: 'rgba(168, 85, 247, 0.7)',
                        borderColor: 'rgba(168, 85, 247, 1)',
                        borderWidth: 2
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
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    },
                    x: {
                        stacked: false,
                    }
                }
            }
        });
    }
    @endif
});
</script>
@endif
@endsection
