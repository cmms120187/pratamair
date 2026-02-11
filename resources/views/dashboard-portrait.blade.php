@extends('layouts.app')
@section('content')
<style>
    /* Portrait layout - optimized for vertical monitors (1080x1920) */
    body {
        overflow: hidden;
        margin: 0;
        padding: 0;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }
    main { overflow: hidden !important; height: 100vh; }
    
    .dashboard-container {
        width: 100%;
        max-width: 100%;
        height: 100vh;
        min-height: 100vh;
        overflow: hidden;
        position: relative;
        background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
        box-sizing: border-box;
    }
    
    /* Page-specific background gradients */
    #page-0 { background: linear-gradient(180deg, #4c1d95 0%, #5b21b6 25%, #6d28d9 50%, #7c3aed 75%, #8b5cf6 100%); }
    #page-1 { background: linear-gradient(180deg, #0c4a6e 0%, #075985 25%, #0369a1 50%, #0284c7 75%, #0ea5e9 100%); }
    #page-2 { background: linear-gradient(180deg, #991b1b 0%, #b91c1c 25%, #dc2626 50%, #ef4444 75%, #f87171 100%); }
    #page-3 { background: linear-gradient(180deg, #7c2d12 0%, #9a3412 25%, #c2410c 50%, #ea580c 75%, #f97316 100%); }
    #page-4 { background: linear-gradient(180deg, #065f46 0%, #047857 25%, #059669 50%, #10b981 75%, #34d399 100%); }
    
    .page-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.8s ease, visibility 0.8s ease, transform 0.8s ease;
        overflow: hidden;
        padding: 0.75rem;
        box-sizing: border-box;
        transform: translateY(20px);
        display: flex;
        flex-direction: column;
    }
    
    .page-container.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .page-content-fill {
        flex: 1;
        min-height: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    /* Page indicator - vertical on right */
    .page-indicator {
        position: fixed;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 15px 10px;
        border-radius: 25px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }
    
    .page-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(102, 126, 234, 0.3);
        transition: all 0.4s ease;
        cursor: pointer;
    }
    
    .page-dot.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transform: scale(1.4);
        box-shadow: 0 0 15px rgba(102, 126, 234, 0.6);
    }
    
    .page-dot:hover { transform: scale(1.2); }
    
    /* Header - compact for portrait */
    .page-header {
        flex-shrink: 0;
        background: rgba(255, 255, 255, 1);
        padding: 0.5rem 0.75rem;
        border-radius: 10px;
        margin-bottom: 0.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 2px solid rgba(255, 255, 255, 0.8);
    }
    
    .page-title {
        font-size: 1.1rem;
        font-weight: 800;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
    }
    
    .page-subtitle {
        font-size: 0.65rem;
        color: #666;
        margin-top: 0.1rem;
    }
    
    .datetime-display {
        text-align: right;
        font-size: 0.65rem;
        color: #333;
    }
    
    /* Stats grid - portrait: 3 columns */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.4rem;
        flex-shrink: 0;
    }
    
    .stats-grid .stat-card:nth-child(n+13) { display: none; }
    
    /* Stat cards - portrait optimized */
    .stat-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0.05) 100%);
        backdrop-filter: blur(10px);
        padding: 1rem 0.75rem;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.5rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        min-height: 150px;
    }
    
    .stat-icon {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border-radius: 50%;
        font-size: 1.25rem;
    }
    
    .stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: white;
        line-height: 1.1;
    }
    
    .stat-label {
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .stat-unit {
        font-size: 0.6rem;
        color: rgba(255, 255, 255, 0.7);
    }
    
    /* Chart grid - portrait: stacked */
    .chart-grid {
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .chart-grid-2 {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
        flex: 1;
        min-height: 0;
    }
    
    .chart-card {
        flex: 1;
        background: rgba(255, 255, 255, 0.98);
        border-radius: 10px;
        padding: 0.6rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    
    .chart-title {
        font-size: 0.8rem;
        font-weight: 700;
        color: #333;
        margin: 0 0 0.4rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-shrink: 0;
    }
    
    .chart-title::before {
        content: '';
        width: 3px;
        height: 14px;
        background: linear-gradient(180deg, #667eea, #764ba2);
        border-radius: 2px;
    }
    
    .chart-card canvas {
        flex: 1;
        min-height: 100px;
    }
    
    /* Data table */
    .data-table {
        flex: 1;
        background: rgba(255, 255, 255, 0.98);
        border-radius: 10px;
        padding: 0.6rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        display: flex;
        flex-direction: column;
        min-height: 0;
        overflow: hidden;
    }
    
    .table-wrapper {
        flex: 1;
        overflow-y: auto;
        min-height: 0;
    }
    
    .data-table table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.65rem;
    }
    
    .data-table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.4rem;
        text-align: left;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .data-table td {
        padding: 0.35rem 0.4rem;
        border-bottom: 1px solid rgba(102, 126, 234, 0.1);
        color: #333;
    }
    
    /* Badge styles */
    .badge {
        display: inline-flex;
        padding: 0.2rem 0.4rem;
        border-radius: 10px;
        font-size: 0.55rem;
        font-weight: 600;
    }
    .badge-success { background: #10b981; color: white; }
    .badge-warning { background: #f59e0b; color: white; }
    .badge-danger { background: #ef4444; color: white; }
    .badge-info { background: #667eea; color: white; }
    
    /* Animation */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .fade-in-up { animation: fadeInUp 0.6s ease forwards; }
</style>

<div class="dashboard-container">
    <!-- Page Indicator -->
    <div class="page-indicator">
        <div class="page-dot active" data-page="0" title="Database Mesin"></div>
        <div class="page-dot" data-page="1" title="Jadwal Maintenance"></div>
        <div class="page-dot" data-page="2" title="Informasi Downtime"></div>
        <div class="page-dot" data-page="3" title="Skill Matrix"></div>
        <div class="page-dot" data-page="4" title="Spareparts"></div>
    </div>
    
    <!-- PAGE 1: Database Mesin & Lokasi -->
    <div class="page-container active" id="page-0">
        <div class="page-header">
            <div>
                <h1 class="page-title">Database Mesin & Lokasi</h1>
                <p class="page-subtitle">Informasi mesin, lokasi, dan struktur</p>
            </div>
            <div class="datetime-display">
                <div id="current-datetime-0"></div>
                <div>{{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y') }}</div>
            </div>
        </div>
        
        <div class="page-content-fill">
            <div class="stats-grid">
                <div class="stat-card fade-in-up"><div class="stat-icon">🏭</div><div class="stat-value">{{ number_format($totalMachines) }}</div><div class="stat-label">Total Mesin</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">🏢</div><div class="stat-value">{{ number_format($totalPlants) }}</div><div class="stat-label">Plant</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">⚙️</div><div class="stat-value">{{ number_format($totalProcesses) }}</div><div class="stat-label">Process</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">📊</div><div class="stat-value">{{ number_format($totalLines) }}</div><div class="stat-label">Line</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">🚪</div><div class="stat-value">{{ number_format($totalRooms) }}</div><div class="stat-label">Room</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">🔧</div><div class="stat-value">{{ number_format($totalMachineTypes) }}</div><div class="stat-label">Types</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">🏷️</div><div class="stat-value">{{ number_format($totalBrands) }}</div><div class="stat-label">Brands</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">📦</div><div class="stat-value">{{ number_format($totalModels) }}</div><div class="stat-label">Models</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">🔗</div><div class="stat-value">{{ number_format($totalSystems) }}</div><div class="stat-label">Systems</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">👥</div><div class="stat-value">{{ number_format($totalGroups) }}</div><div class="stat-label">Groups</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">✅</div><div class="stat-value">{{ number_format($machinesWithBrand) }}</div><div class="stat-label">With Brand</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">📋</div><div class="stat-value">{{ number_format($machinesWithModel) }}</div><div class="stat-label">With Model</div></div>
            </div>
            
            <div class="chart-grid">
                <div class="chart-card">
                    <h3 class="chart-title">Distribusi Mesin</h3>
                    <canvas id="machineDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- PAGE 2: Jadwal Maintenance -->
    <div class="page-container" id="page-1">
        <div class="page-header">
            <div>
                <h1 class="page-title">Jadwal Maintenance</h1>
                <p class="page-subtitle">PM, PdM, dan Work Orders</p>
            </div>
            <div class="datetime-display">
                <div id="current-datetime-1"></div>
                <div>{{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y') }}</div>
            </div>
        </div>
        
        <div class="page-content-fill">
            <div class="stats-grid">
                <div class="stat-card fade-in-up"><div class="stat-icon">📅</div><div class="stat-value">{{ number_format($pmSchedulesThisMonth) }}</div><div class="stat-label">Jadwal PM</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">⏳</div><div class="stat-value">{{ number_format($pmSchedulesPending) }}</div><div class="stat-label">PM Pending</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">✅</div><div class="stat-value">{{ number_format($pmSchedulesCompleted) }}</div><div class="stat-label">PM Selesai</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">📊</div><div class="stat-value">{{ number_format($pmCompletionRate, 1) }}%</div><div class="stat-label">PM Rate</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">🔬</div><div class="stat-value">{{ number_format($pdmSchedulesThisMonth) }}</div><div class="stat-label">Jadwal PdM</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">📋</div><div class="stat-value">{{ number_format($pdmSchedulesCompleted) }}</div><div class="stat-label">PdM Selesai</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">📝</div><div class="stat-value">{{ number_format($workOrdersThisMonth) }}</div><div class="stat-label">WO Bulan Ini</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">🔄</div><div class="stat-value">{{ number_format($workOrdersInProgress) }}</div><div class="stat-label">WO Progress</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">✔️</div><div class="stat-value">{{ number_format($workOrdersCompleted) }}</div><div class="stat-label">WO Selesai</div></div>
            </div>
            
            <div class="chart-grid-2">
                <div class="data-table">
                    <h3 class="chart-title">Jadwal PM Mendatang</h3>
                    <div class="table-wrapper">
                        <table>
                            <thead><tr><th>Mesin</th><th>Tanggal</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($upcomingPMSchedules as $schedule)
                                <tr>
                                    <td>{{ $schedule->machineErp->id_machine ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($schedule->start_date)->format('d/m') }}</td>
                                    <td><span class="badge badge-{{ $schedule->status === 'completed' ? 'success' : 'warning' }}">{{ ucfirst($schedule->status) }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="3" style="text-align:center;color:#999;">Tidak ada jadwal</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="data-table">
                    <h3 class="chart-title">Work Orders Terbaru</h3>
                    <div class="table-wrapper">
                        <table>
                            <thead><tr><th>No</th><th>Tanggal</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($recentWorkOrders as $wo)
                                <tr>
                                    <td>{{ Str::limit($wo->order_number, 10) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($wo->order_date)->format('d/m') }}</td>
                                    <td><span class="badge badge-{{ $wo->status === 'completed' ? 'success' : ($wo->status === 'in_progress' ? 'info' : 'warning') }}">{{ ucfirst($wo->status) }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="3" style="text-align:center;color:#999;">Tidak ada WO</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- PAGE 3: Informasi Downtime -->
    <div class="page-container" id="page-2">
        <div class="page-header">
            <div>
                <h1 class="page-title">Informasi Downtime</h1>
                <p class="page-subtitle">Data downtime bulan ini</p>
            </div>
            <div class="datetime-display">
                <div id="current-datetime-2"></div>
                <div>{{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y') }}</div>
            </div>
        </div>
        
        <div class="page-content-fill">
            <div class="stats-grid">
                <div class="stat-card fade-in-up"><div class="stat-icon">📉</div><div class="stat-value">{{ number_format($monthDowntimeCount ?? 0) }}</div><div class="stat-label">Kejadian</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">⏱️</div><div class="stat-value">{{ number_format($monthDowntime ?? 0, 0) }}</div><div class="stat-label">Total (min)</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">📊</div><div class="stat-value">{{ number_format($avgDowntimeDuration ?? 0, 1) }}</div><div class="stat-label">Avg (min)</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">⚠️</div><div class="stat-value">{{ number_format($machinesWithDowntime) }}</div><div class="stat-label">Mesin DT</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">👷</div><div class="stat-value">{{ number_format($activeMechanics) }}</div><div class="stat-label">Mekanik</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">📍</div><div class="stat-value" style="font-size:0.9rem;">{{ $topLines->first()->line ?? 'N/A' }}</div><div class="stat-label">Top Line</div></div>
            </div>
            
            <div class="chart-grid-2">
                <div class="chart-card">
                    <h3 class="chart-title">Top Mesin Downtime</h3>
                    <canvas id="machineDowntimeChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3 class="chart-title">Top Problems</h3>
                    <canvas id="problemsChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card" style="flex: 1;">
                <h3 class="chart-title">Trend Downtime Harian</h3>
                <canvas id="downtimeTrendChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- PAGE 4: Skill Matrix -->
    <div class="page-container" id="page-3">
        <div class="page-header">
            <div>
                <h1 class="page-title">Skill Matrix</h1>
                <p class="page-subtitle">Kemampuan mekanik per tipe mesin</p>
            </div>
            <div class="datetime-display">
                <div id="current-datetime-3"></div>
                <div>{{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y') }}</div>
            </div>
        </div>
        
        <div class="page-content-fill">
            <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
                <div class="stat-card fade-in-up"><div class="stat-icon">👷</div><div class="stat-value">{{ number_format($mechanicStats->count()) }}</div><div class="stat-label">Mekanik Aktif</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">🔧</div><div class="stat-value">{{ number_format($mechanicStats->sum('total_repairs')) }}</div><div class="stat-label">Total Perbaikan</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">⏱️</div><div class="stat-value">{{ number_format($mechanicStats->sum('total_duration'), 0) }}</div><div class="stat-label">Total Durasi</div></div>
            </div>
            
            <div class="chart-grid-2">
                <div class="chart-card">
                    <h3 class="chart-title">Top 10 MTTR</h3>
                    <canvas id="mttrChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3 class="chart-title">Top 10 MTBF</h3>
                    <canvas id="mtbfChart"></canvas>
                </div>
            </div>
            
            <div class="data-table" style="flex: 1;">
                <h3 class="chart-title">Skill Matrix per Mekanik</h3>
                <div class="table-wrapper">
                    <table>
                        <thead><tr><th>Mekanik</th><th>Tipe Mesin</th><th>Level</th><th>Perbaikan</th><th>Avg (min)</th></tr></thead>
                        <tbody>
                            @foreach($skillMatrix as $idMekanik => $skills)
                                @foreach($skills->take(3) as $skill)
                                <tr>
                                    <td>{{ Str::limit($skill->nameMekanik ?? $idMekanik, 12) }}</td>
                                    <td>{{ Str::limit($skill->typeMachine ?? 'N/A', 15) }}</td>
                                    <td><span class="badge badge-{{ $skill->repair_count >= 10 ? 'success' : ($skill->repair_count >= 5 ? 'info' : 'warning') }}">{{ $skill->repair_count >= 10 ? 'Expert' : ($skill->repair_count >= 5 ? 'Advance' : 'Beginner') }}</span></td>
                                    <td>{{ $skill->repair_count }}</td>
                                    <td>{{ number_format($skill->avg_duration ?? 0, 1) }}</td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- PAGE 5: Spareparts & Standards -->
    <div class="page-container" id="page-4">
        <div class="page-header">
            <div>
                <h1 class="page-title">Spareparts & Standards</h1>
                <p class="page-subtitle">Inventori dan standar pemeliharaan</p>
            </div>
            <div class="datetime-display">
                <div id="current-datetime-4"></div>
                <div>{{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y') }}</div>
            </div>
        </div>
        
        <div class="page-content-fill">
            <div class="stats-grid">
                <div class="stat-card fade-in-up"><div class="stat-icon">🔩</div><div class="stat-value">{{ number_format($totalSpareparts) }}</div><div class="stat-label">Total Parts</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">⚠️</div><div class="stat-value">{{ number_format($lowStockSpareparts) }}</div><div class="stat-label">Low Stock</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">💰</div><div class="stat-value">{{ number_format($totalStockValue / 1000000, 1) }}M</div><div class="stat-label">Stock Value</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">📏</div><div class="stat-value">{{ number_format($totalStandards) }}</div><div class="stat-label">Standards</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">✅</div><div class="stat-value">{{ number_format($activeStandards) }}</div><div class="stat-label">Std Active</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">🔴</div><div class="stat-value">{{ number_format($redStatusCount) }}</div><div class="stat-label">Red Status</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">❓</div><div class="stat-value">{{ number_format($uniqueProblems) }}</div><div class="stat-label">Problems</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">📝</div><div class="stat-value">{{ number_format($uniqueReasons) }}</div><div class="stat-label">Reasons</div></div>
                <div class="stat-card fade-in-up"><div class="stat-icon">⚡</div><div class="stat-value">{{ number_format($uniqueActions) }}</div><div class="stat-label">Actions</div></div>
            </div>
            
            <div class="chart-grid-2">
                <div class="chart-card">
                    <h3 class="chart-title">Status Overview</h3>
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3 class="chart-title">Stock Distribution</h3>
                    <canvas id="stockChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartInstances = {};
    const chartInitialized = {};
    const PAGE_ROTATE_INTERVAL = 25000;
    let currentPage = 0;
    const totalPages = 5;
    let rotateTimer = null;
    
    function showPage(pageIndex) {
        document.querySelectorAll('.page-container').forEach((page, index) => {
            page.classList.toggle('active', index === pageIndex);
        });
        document.querySelectorAll('.page-dot').forEach((dot, index) => {
            dot.classList.toggle('active', index === pageIndex);
        });
        currentPage = pageIndex;
        
        setTimeout(() => {
            if (pageIndex === 0) initializePage0Charts();
            else if (pageIndex === 2) initializePage2Charts();
            else if (pageIndex === 3) initializePage3Charts();
            else if (pageIndex === 4) initializePage4Charts();
        }, 300);
    }
    
    function nextPage() { showPage((currentPage + 1) % totalPages); }
    function startAutoRotate() { stopAutoRotate(); rotateTimer = setInterval(nextPage, PAGE_ROTATE_INTERVAL); }
    function stopAutoRotate() { if (rotateTimer) { clearInterval(rotateTimer); rotateTimer = null; } }
    
    function destroyChart(chartId) {
        if (chartInstances[chartId]) {
            try { chartInstances[chartId].destroy(); } catch (e) {}
            chartInstances[chartId] = null;
            chartInitialized[chartId] = false;
        }
    }
    
    function initializePage0Charts() {
        if (chartInitialized['machineDistributionChart']) return;
        const ctx = document.getElementById('machineDistributionChart');
        if (ctx) {
            destroyChart('machineDistributionChart');
            chartInstances['machineDistributionChart'] = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Dengan Brand', 'Dengan Model', 'Dengan Type'],
                    datasets: [{ data: [{{ $machinesWithBrand }}, {{ $machinesWithModel }}, {{ $machinesWithType }}], backgroundColor: ['rgba(102, 126, 234, 0.8)', 'rgba(16, 185, 129, 0.8)', 'rgba(245, 158, 11, 0.8)'], borderWidth: 2 }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { font: { size: 10 } } } } }
            });
            chartInitialized['machineDistributionChart'] = true;
        }
    }
    
    function initializePage2Charts() {
        // Machine Downtime Chart
        const machineCtx = document.getElementById('machineDowntimeChart');
        if (machineCtx && !chartInitialized['machineDowntimeChart']) {
            destroyChart('machineDowntimeChart');
            const machineData = @json($topMachines ?? []);
            if (machineData && machineData.length > 0) {
                chartInstances['machineDowntimeChart'] = new Chart(machineCtx, {
                    type: 'bar',
                    data: { labels: machineData.slice(0,5).map(m => m.idMachine || 'N/A'), datasets: [{ label: 'Downtime', data: machineData.slice(0,5).map(m => parseFloat(m.total_duration) || 0), backgroundColor: 'rgba(239, 68, 68, 0.8)', borderRadius: 4 }] },
                    options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true }, y: { ticks: { font: { size: 9 } } } } }
                });
                chartInitialized['machineDowntimeChart'] = true;
            }
        }
        
        // Problems Chart
        const problemsCtx = document.getElementById('problemsChart');
        if (problemsCtx && !chartInitialized['problemsChart']) {
            destroyChart('problemsChart');
            const problemsData = @json($topProblems ?? []);
            if (problemsData && problemsData.length > 0) {
                chartInstances['problemsChart'] = new Chart(problemsCtx, {
                    type: 'doughnut',
                    data: { labels: problemsData.slice(0,5).map(p => p.problemDowntime || 'N/A'), datasets: [{ data: problemsData.slice(0,5).map(p => p.problem_count || 0), backgroundColor: ['rgba(239, 68, 68, 0.8)', 'rgba(245, 158, 11, 0.8)', 'rgba(16, 185, 129, 0.8)', 'rgba(102, 126, 234, 0.8)', 'rgba(139, 92, 246, 0.8)'], borderWidth: 2 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { font: { size: 8 } } } } }
                });
                chartInitialized['problemsChart'] = true;
            }
        }
        
        // Trend Chart
        const trendCtx = document.getElementById('downtimeTrendChart');
        if (trendCtx && !chartInitialized['downtimeTrendChart']) {
            destroyChart('downtimeTrendChart');
            const trendData = @json($downtimeTrend ?? []);
            if (trendData && trendData.length > 0) {
                chartInstances['downtimeTrendChart'] = new Chart(trendCtx, {
                    type: 'line',
                    data: { labels: trendData.map(t => new Date(t.date).getDate()), datasets: [{ label: 'Downtime', data: trendData.map(t => parseFloat(t.total_duration) || 0), borderColor: 'rgba(102, 126, 234, 1)', backgroundColor: 'rgba(102, 126, 234, 0.2)', fill: true, tension: 0.4, borderWidth: 2 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { ticks: { font: { size: 9 } } }, y: { beginAtZero: true } } }
                });
                chartInitialized['downtimeTrendChart'] = true;
            }
        }
    }
    
    function initializePage3Charts() {
        // MTTR Chart
        const mttrCtx = document.getElementById('mttrChart');
        if (mttrCtx && !chartInitialized['mttrChart']) {
            destroyChart('mttrChart');
            const mttrData = @json($topMTTR ?? []);
            if (mttrData && mttrData.length > 0) {
                chartInstances['mttrChart'] = new Chart(mttrCtx, {
                    type: 'bar',
                    data: { labels: mttrData.slice(0,5).map(m => m.idMachine || 'N/A'), datasets: [{ label: 'MTTR', data: mttrData.slice(0,5).map(m => parseFloat(m.mttr) || 0), backgroundColor: 'rgba(239, 68, 68, 0.8)', borderRadius: 4 }] },
                    options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true }, y: { ticks: { font: { size: 9 } } } } }
                });
                chartInitialized['mttrChart'] = true;
            }
        }
        
        // MTBF Chart
        const mtbfCtx = document.getElementById('mtbfChart');
        if (mtbfCtx && !chartInitialized['mtbfChart']) {
            destroyChart('mtbfChart');
            const mtbfData = @json($topMTBF ?? []);
            if (mtbfData && mtbfData.length > 0) {
                chartInstances['mtbfChart'] = new Chart(mtbfCtx, {
                    type: 'bar',
                    data: { labels: mtbfData.slice(0,5).map(m => m.idMachine || 'N/A'), datasets: [{ label: 'MTBF', data: mtbfData.slice(0,5).map(m => parseFloat(m.mtbf) || 0), backgroundColor: 'rgba(16, 185, 129, 0.8)', borderRadius: 4 }] },
                    options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true }, y: { ticks: { font: { size: 9 } } } } }
                });
                chartInitialized['mtbfChart'] = true;
            }
        }
    }
    
    function initializePage4Charts() {
        // Status Chart
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx && !chartInitialized['statusChart']) {
            destroyChart('statusChart');
            chartInstances['statusChart'] = new Chart(statusCtx, {
                type: 'doughnut',
                data: { labels: ['PM Selesai', 'PM Pending', 'PdM Selesai', 'WO Selesai'], datasets: [{ data: [{{ $pmSchedulesCompleted }}, {{ $pmSchedulesPending }}, {{ $pdmSchedulesCompleted }}, {{ $workOrdersCompleted }}], backgroundColor: ['rgba(16, 185, 129, 0.8)', 'rgba(245, 158, 11, 0.8)', 'rgba(102, 126, 234, 0.8)', 'rgba(139, 92, 246, 0.8)'], borderWidth: 2 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { font: { size: 9 } } } } }
            });
            chartInitialized['statusChart'] = true;
        }
        
        // Stock Chart
        const stockCtx = document.getElementById('stockChart');
        if (stockCtx && !chartInitialized['stockChart']) {
            destroyChart('stockChart');
            chartInstances['stockChart'] = new Chart(stockCtx, {
                type: 'doughnut',
                data: { labels: ['Normal Stock', 'Low Stock'], datasets: [{ data: [{{ $totalSpareparts - $lowStockSpareparts }}, {{ $lowStockSpareparts }}], backgroundColor: ['rgba(16, 185, 129, 0.8)', 'rgba(239, 68, 68, 0.8)'], borderWidth: 2 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { font: { size: 9 } } } } }
            });
            chartInitialized['stockChart'] = true;
        }
    }
    
    function updateDateTime() {
        const now = new Date();
        const options = { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' };
        const dateStr = now.toLocaleDateString('id-ID', options);
        document.querySelectorAll('[id^="current-datetime"]').forEach(el => { el.textContent = dateStr; });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        updateDateTime();
        setInterval(updateDateTime, 1000);
        initializePage0Charts();
        startAutoRotate();
        
        document.querySelectorAll('.page-dot').forEach(dot => {
            dot.addEventListener('click', function() {
                showPage(parseInt(this.getAttribute('data-page')));
                startAutoRotate();
            });
        });
        
        document.querySelector('.dashboard-container').addEventListener('mouseenter', stopAutoRotate);
        document.querySelector('.dashboard-container').addEventListener('mouseleave', startAutoRotate);
    });
</script>
@endsection
