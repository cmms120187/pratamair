@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Performance Report</h1>
                <p class="text-sm text-gray-500 mt-1">Monthly Completion Rates & Machine Performance</p>
            </div>
            <a href="{{ route('predictive-maintenance.reporting.index') }}" class="text-gray-600 hover:text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6">
            <form method="GET" action="{{ route('predictive-maintenance.reporting.performance') }}" id="filterForm" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                        <a href="{{ route('predictive-maintenance.reporting.performance') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded shadow transition">
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
                                            @if($data['completion_rate'] >= 80) bg-green-500
                                            @elseif($data['completion_rate'] >= 50) bg-yellow-500
                                            @else bg-red-500
                                            @endif"
                                            style="width: {{ min($data['completion_rate'], 100) }}%">
                                            @if($data['completion_rate'] > 0){{ number_format($data['completion_rate'], 1) }}%@endif
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
                                <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($machinePerformancePaginator as $perf)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-semibold text-gray-900">{{ $perf['machine']->idMachine ?? '-' }}</div>
                                    <div class="text-gray-500">{{ $perf['machine']->machineType->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-400">
                                        {{ $perf['machine']->plant_name ?? '-' }} /
                                        {{ $perf['machine']->line_name ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $perf['total_jadwal'] }}</td>
                                <td class="px-4 py-3 text-sm text-center text-green-600 font-semibold">{{ $perf['completed_jadwal'] }}</td>
                                <td class="px-4 py-3 text-sm text-center">
                                    <div class="flex items-center justify-center">
                                        <div class="w-full bg-gray-200 rounded-full h-6 mr-2 max-w-xs">
                                            <div class="h-6 rounded-full flex items-center justify-center text-xs font-semibold text-white transition-all duration-300
                                                @if($perf['completion_rate'] >= 80) bg-green-500
                                                @elseif($perf['completion_rate'] >= 50) bg-yellow-500
                                                @else bg-red-500
                                                @endif"
                                                style="width: {{ min($perf['completion_rate'], 100) }}%">
                                                @if($perf['completion_rate'] > 0){{ number_format($perf['completion_rate'], 1) }}%@endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button onclick="showPointTrends({{ $perf['machine_id'] }}, '{{ $perf['machine']->idMachine ?? 'Machine' }}')" 
                                            class="inline-flex items-center justify-center bg-purple-600 hover:bg-purple-700 text-white p-2 rounded shadow transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        <span class="ml-2 text-xs">View Trends</span>
                                    </button>
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

<!-- Modal for Standard Detail -->
<div id="standardDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-[60] flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800" id="standardModalTitle">Standard Detail</h3>
                <div class="flex items-center gap-2">
                    <div id="standardPhotoThumbnail" class="hidden">
                        <!-- Photo thumbnail will be inserted here -->
                    </div>
                    <button onclick="closeStandardDetailModal()" class="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div id="standardDetailContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Modal for Photo Zoom/Lightbox -->
<div id="photoZoomModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-[70] flex items-center justify-center p-4">
    <div class="relative max-w-7xl w-full max-h-[95vh] flex items-center justify-center">
        <button onclick="closePhotoZoomModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-2 z-10">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <img id="zoomedPhoto" src="" alt="Zoomed Photo" class="max-w-full max-h-[95vh] object-contain rounded-lg">
    </div>
</div>

<!-- Modal for Point Trends Detail -->
<div id="pointTrendsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800" id="modalMachineName">Point Trends Detail</h3>
                <button onclick="closePointTrendsModal()" class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="pointTrendsContent" class="space-y-6">
                <p class="text-center py-4 text-gray-500">Loading...</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Store chart instances for cleanup (global scope)
let pointTrendCharts = [];

// Function to show point trends modal (global scope)
function showPointTrends(machineId, machineName) {
        const modal = document.getElementById('pointTrendsModal');
        const content = document.getElementById('pointTrendsContent');
        const modalTitle = document.getElementById('modalMachineName');
        
        modalTitle.textContent = 'Point Trends Detail - ' + machineName;
        content.innerHTML = '<p class="text-center py-4 text-gray-500">Loading...</p>';
        modal.classList.remove('hidden');
        
        // Destroy existing charts
        pointTrendCharts.forEach(chart => chart.destroy());
        pointTrendCharts = [];
        
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        const url = `{{ route('predictive-maintenance.reporting.get-point-trends-by-machine') }}?machine_id=${machineId}&start_date=${startDate}&end_date=${endDate}`;
        
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.point_trends && data.point_trends.length > 0) {
                let html = '';
                data.point_trends.forEach((pointData, index) => {
                    const chartId = 'pointChart_' + pointData.point_id + '_' + index;
                    const dataId = 'standardData_' + pointData.point_id + '_' + index;
                    // Store data in window object for easy access
                    window['standardData_' + pointData.point_id + '_' + index] = pointData;
                    
                    html += `
                        <div class="border rounded-lg p-4 bg-gray-50 relative">
                            <div class="mb-3 flex items-start justify-between">
                                <h4 class="text-md font-semibold text-gray-900">
                                    ${pointData.point_name}
                                </h4>
                                <button onclick="showStandardDetail('${dataId}')" 
                                        class="ml-2 p-2 text-purple-600 hover:text-purple-800 hover:bg-purple-100 rounded transition"
                                        title="View Standard Details">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </div>
                            <div class="h-64">
                                <canvas id="${chartId}"></canvas>
                            </div>
                        </div>
                    `;
                });
                content.innerHTML = html;
                
                // Create charts after DOM is updated
                setTimeout(() => {
                    data.point_trends.forEach((pointData, index) => {
                        const chartId = 'pointChart_' + pointData.point_id + '_' + index;
                        const ctx = document.getElementById(chartId);
                        
                        if (ctx && pointData.dates && pointData.values) {
                            const datasets = [];
                            const dateCount = pointData.dates.length;
                            
                            // Store variants data for plugin access (must be declared first)
                            const chartVariants = pointData.variants || [];
                            
                            // Add measured value line (zones will be drawn by plugin)
                            datasets.push({
                                label: pointData.point_name + ' (Measured Value)',
                                data: pointData.values,
                                borderColor: 'rgb(147, 51, 234)',
                                backgroundColor: 'rgba(147, 51, 234, 0.1)',
                                tension: 0.4,
                                fill: false,
                                pointRadius: 3,
                                pointHoverRadius: 5,
                                order: 10, // Render on top
                            });
                            
                            // Calculate Y-axis min and max based on data
                            let yMin = 0;
                            let yMax = 0;
                            
                            // Special case for "Temperatur Motor Penggerak"
                            const isTemperature = pointData.point_name && 
                                pointData.point_name.toLowerCase().includes('temperatur') && 
                                pointData.point_name.toLowerCase().includes('motor penggerak');
                            
                            // Check if there are variants to determine max
                            let variantMax = 0;
                            if (chartVariants && chartVariants.length > 0) {
                                const sortedVariants = [...chartVariants].sort((a, b) => (a.order || 0) - (b.order || 0));
                                const lastVariant = sortedVariants[sortedVariants.length - 1];
                                if (lastVariant && lastVariant.max_value !== null) {
                                    variantMax = lastVariant.max_value;
                                }
                            }
                            
                            if (pointData.values && pointData.values.length > 0) {
                                const minValue = Math.min(...pointData.values);
                                const maxValue = Math.max(...pointData.values);
                                
                                if (isTemperature) {
                                    // For Temperatur Motor Penggerak: min 40, max 120
                                    yMin = 40;
                                    yMax = 120;
                                } else {
                                    // For other points: min 0, max = largest value + 10 or variant max + 10 (whichever is larger)
                                    yMin = 0;
                                    const dataMax = maxValue + 10;
                                    yMax = variantMax > 0 ? Math.max(dataMax, variantMax + 10) : dataMax;
                                }
                            } else {
                                // Default values if no data
                                if (isTemperature) {
                                    yMin = 40;
                                    yMax = 120;
                                } else {
                                    yMin = 0;
                                    yMax = variantMax > 0 ? variantMax + 10 : 10;
                                }
                            }
                            
                            // Custom plugin to draw background zones
                            const zonePlugin = {
                                id: 'zoneBackground_' + chartId,
                                beforeDatasetsDraw(chart) {
                                    if (!chartVariants || chartVariants.length === 0) return;
                                    
                                    const {ctx, chartArea, scales} = chart;
                                    if (!chartArea) return;
                                    
                                    const yScale = scales.y;
                                    const sortedVariants = [...chartVariants].sort((a, b) => (a.order || 0) - (b.order || 0));
                                    
                                    sortedVariants.forEach((variant, idx) => {
                                        const minVal = variant.min_value !== null ? variant.min_value : 0;
                                        const maxVal = variant.max_value !== null ? variant.max_value : Infinity;
                                        
                                        if (maxVal === Infinity) return;
                                        
                                        // Determine actual zone boundaries (prev variant max to this variant max)
                                        const prevMax = idx > 0 
                                            ? (sortedVariants[idx - 1].max_value !== null ? sortedVariants[idx - 1].max_value : 0)
                                            : (minVal >= 0 ? minVal : 0);
                                        
                                        const zoneMin = Math.max(minVal, prevMax);
                                        const zoneMax = maxVal;
                                        
                                        // Convert color to rgba
                                        let bgColor = 'rgba(156, 163, 175, 0.3)';
                                        if (variant.color) {
                                            if (variant.color.startsWith('#')) {
                                                const hex = variant.color.replace('#', '');
                                                const r = parseInt(hex.substr(0, 2), 16);
                                                const g = parseInt(hex.substr(2, 2), 16);
                                                const b = parseInt(hex.substr(4, 2), 16);
                                                bgColor = `rgba(${r}, ${g}, ${b}, 0.3)`;
                                            } else if (variant.color.startsWith('rgba')) {
                                                const rgbaMatch = variant.color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
                                                if (rgbaMatch) {
                                                    const r = rgbaMatch[1];
                                                    const g = rgbaMatch[2];
                                                    const b = rgbaMatch[3];
                                                    bgColor = `rgba(${r}, ${g}, ${b}, 0.3)`;
                                                }
                                            } else if (variant.color.startsWith('rgb')) {
                                                const rgbMatch = variant.color.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/);
                                                if (rgbMatch) {
                                                    bgColor = `rgba(${rgbMatch[1]}, ${rgbMatch[2]}, ${rgbMatch[3]}, 0.3)`;
                                                }
                                            }
                                        }
                                        
                                        // Calculate Y positions (note: y=0 is at top in canvas, so lower values are at bottom)
                                        const yTop = yScale.getPixelForValue(zoneMax);      // Top of zone (higher value)
                                        const yBottom = yScale.getPixelForValue(zoneMin);   // Bottom of zone (lower value)
                                        const zoneHeight = yBottom - yTop;
                                        
                                        // Draw rectangle for zone
                                        if (zoneHeight > 0) {
                                            ctx.save();
                                            ctx.fillStyle = bgColor;
                                            ctx.fillRect(chartArea.left, yTop, chartArea.right - chartArea.left, zoneHeight);
                                            ctx.restore();
                                        }
                                    });
                                }
                            };
                            
                            const chart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: pointData.dates_display,
                                    datasets: datasets
                                },
                                plugins: [zonePlugin],
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: true,
                                            position: 'top',
                                        },
                                        tooltip: {
                                            mode: 'index',
                                            intersect: false,
                                            callbacks: {
                                                label: function(context) {
                                                    let label = context.dataset.label || '';
                                                    if (label) {
                                                        label += ': ';
                                                    }
                                                    if (context.parsed.y !== null) {
                                                        label += parseFloat(context.parsed.y).toFixed(2) + ' ' + (pointData.standard_unit || '');
                                                    }
                                                    return label;
                                                }
                                            }
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: false,
                                            min: yMin,
                                            max: yMax,
                                            title: {
                                                display: true,
                                                text: 'Value (' + (pointData.standard_unit || '') + ')'
                                            },
                                            stacked: false
                                        },
                                        x: {
                                            title: {
                                                display: true,
                                                text: 'Date'
                                            },
                                            stacked: false
                                        }
                                    },
                                    interaction: {
                                        mode: 'nearest',
                                        axis: 'x',
                                        intersect: false
                                    }
                                }
                            });
                            
                            pointTrendCharts.push(chart);
                        }
                    });
                }, 100);
            } else {
                content.innerHTML = '<p class="text-center py-4 text-gray-500">No point trends data found for this machine.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<p class="text-center py-4 text-red-500">Error loading point trends data.</p>';
        });
}

// Function to close point trends modal (global scope)
function closePointTrendsModal() {
    const modal = document.getElementById('pointTrendsModal');
    modal.classList.add('hidden');
    
    // Destroy all charts when closing
    pointTrendCharts.forEach(chart => chart.destroy());
    pointTrendCharts = [];
}

// Function to show standard detail modal (global scope)
function showStandardDetail(dataId) {
    const modal = document.getElementById('standardDetailModal');
    const content = document.getElementById('standardDetailContent');
    const title = document.getElementById('standardModalTitle');
    
    if (!modal || !content || !title) {
        console.error('Modal elements not found');
        return;
    }
    
    // Get standard data from window object
    const pointData = window[dataId];
    if (!pointData) {
        console.error('Standard data not found:', dataId);
        content.innerHTML = '<p class="text-red-500">Standard data not found.</p>';
        modal.classList.remove('hidden');
        return;
    }
    
    // Debug: log photo URL
    console.log('Point Data:', pointData);
    console.log('Standard Photo URL:', pointData.standard_photo_url);
    
    title.textContent = 'Standard Detail - ' + pointData.point_name;
    modal.classList.remove('hidden');
    
    // Add photo thumbnail to header if available
    const photoThumbnail = document.getElementById('standardPhotoThumbnail');
    if (photoThumbnail) {
        if (pointData.standard_photo_url && pointData.standard_photo_url !== 'null' && pointData.standard_photo_url !== '') {
            photoThumbnail.classList.remove('hidden');
            // Escape quotes for JavaScript
            const safePhotoUrl = pointData.standard_photo_url.replace(/'/g, "\\'");
            const safePhotoAlt = (pointData.standard_name || 'Standard Photo').replace(/'/g, "\\'");
            photoThumbnail.innerHTML = `
                <img src="${safePhotoUrl}" 
                     alt="${safePhotoAlt}" 
                     class="w-20 h-20 object-cover rounded-lg border-2 border-gray-300 shadow-md cursor-pointer hover:border-purple-500 hover:shadow-lg transition-all"
                     onclick="openPhotoZoomModal('${safePhotoUrl}', '${safePhotoAlt}')"
                     onerror="this.style.display='none'; this.parentElement.classList.add('hidden');">
            `;
        } else {
            photoThumbnail.classList.add('hidden');
            photoThumbnail.innerHTML = '';
        }
    }
    
    let html = '<div class="space-y-6">';
    
    // Standard Name
    if (pointData.standard_name) {
        html += `
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Standard Name</label>
                <p class="text-gray-900 text-lg font-semibold">${pointData.standard_name}</p>
            </div>
        `;
    }
    
    // Class
    if (pointData.standard_class) {
        html += `
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Class</label>
                <p class="text-gray-900">${pointData.standard_class}</p>
            </div>
        `;
    }
    
    // Reference Information
    if (pointData.standard_reference) {
        html += `
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Reference</label>
                <p class="text-gray-900">${pointData.standard_reference}</p>
            </div>
        `;
    }
    
    // Standard Values
    html += `
        <div>
            <h4 class="text-md font-semibold text-gray-800 mb-3">Standard Values</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Min Value</label>
                    <p class="text-gray-900 font-semibold">${pointData.standard_min !== null ? pointData.standard_min : '-'} ${pointData.standard_unit || ''}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Max Value</label>
                    <p class="text-gray-900 font-semibold">${pointData.standard_max !== null ? pointData.standard_max : '-'} ${pointData.standard_unit || ''}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Target Value</label>
                    <p class="text-gray-900 font-semibold">${pointData.standard_target !== null ? pointData.standard_target : '-'} ${pointData.standard_unit || ''}</p>
                </div>
            </div>
        </div>
    `;
    
    // Standard Variants
    if (pointData.variants && pointData.variants.length > 0) {
        const sortedVariants = [...pointData.variants].sort((a, b) => (a.order || 0) - (b.order || 0));
        
        html += `
            <div>
                <h4 class="text-md font-semibold text-gray-800 mb-3">Standard Variants (Zones/Levels)</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-purple-600">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Order</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Variant Name</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Min Value</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Max Value</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase">Color</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
        `;
        
        sortedVariants.forEach(variant => {
            html += `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-900">${variant.order || '-'}</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">${variant.name}</td>
                    <td class="px-4 py-3 text-sm text-center text-gray-500">${variant.min_value !== null ? variant.min_value + ' ' + (pointData.standard_unit || '') : '-'}</td>
                    <td class="px-4 py-3 text-sm text-center text-gray-500">${variant.max_value !== null ? variant.max_value + ' ' + (pointData.standard_unit || '') : '-'}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <div class="w-6 h-6 rounded border border-gray-300" style="background-color: ${variant.color || '#22C55E'}"></div>
                            <span class="text-xs text-gray-600">${variant.color || '#22C55E'}</span>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        html += `
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }
    
    html += '</div>';
    content.innerHTML = html;
}

// Function to close standard detail modal
function closeStandardDetailModal() {
    const modal = document.getElementById('standardDetailModal');
    modal.classList.add('hidden');
    // Clear photo thumbnail when closing
    const photoThumbnail = document.getElementById('standardPhotoThumbnail');
    if (photoThumbnail) {
        photoThumbnail.classList.add('hidden');
        photoThumbnail.innerHTML = '';
    }
}

// Function to open photo zoom modal (global scope)
function openPhotoZoomModal(photoUrl, photoAlt) {
    const modal = document.getElementById('photoZoomModal');
    const img = document.getElementById('zoomedPhoto');
    if (modal && img) {
        img.src = photoUrl;
        img.alt = photoAlt || 'Zoomed Photo';
        modal.classList.remove('hidden');
    }
}

// Function to close photo zoom modal (global scope)
function closePhotoZoomModal() {
    const modal = document.getElementById('photoZoomModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Initialize on DOM ready
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
    
    // Close modal when clicking outside
    const modal = document.getElementById('pointTrendsModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closePointTrendsModal();
            }
        });
    }
    
    // Close standard detail modal when clicking outside
    const standardModal = document.getElementById('standardDetailModal');
    if (standardModal) {
        standardModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeStandardDetailModal();
            }
        });
    }
    
    // Photo zoom modal - close on background click or ESC key
    const photoZoomModal = document.getElementById('photoZoomModal');
    if (photoZoomModal) {
        photoZoomModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closePhotoZoomModal();
            }
        });
        
        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !photoZoomModal.classList.contains('hidden')) {
                closePhotoZoomModal();
            }
        });
    }
});
</script>
@endsection
