@extends('layouts.app')
@section('content')
<style>
    /* Fullscreen layout - no scroll */
    body {
        overflow: hidden;
        margin: 0;
        padding: 0;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }
    
    .dashboard-container {
        width: 100%;
        max-width: 100%;
        height: 100vh;
        overflow: hidden;
        position: relative;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-sizing: border-box;
    }
    
    /* Page-specific background gradients - darker for better contrast */
    #page-0 {
        background: linear-gradient(135deg, #4c1d95 0%, #5b21b6 30%, #6d28d9 60%, #7c3aed 100%);
    }
    
    #page-1 {
        background: linear-gradient(135deg, #0c4a6e 0%, #075985 30%, #0369a1 60%, #0284c7 100%);
    }
    
    #page-2 {
        background: linear-gradient(135deg, #991b1b 0%, #b91c1c 30%, #dc2626 60%, #ef4444 100%);
    }
    
    #page-3 {
        background: linear-gradient(135deg, #065f46 0%, #047857 30%, #059669 60%, #10b981 100%);
    }
    
    /* Page container - fullscreen */
    .page-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        max-width: 100%;
        height: 100%;
        opacity: 0;
        visibility: hidden;
        transition: opacity 1s cubic-bezier(0.4, 0, 0.2, 1), visibility 1s cubic-bezier(0.4, 0, 0.2, 1), transform 1s cubic-bezier(0.4, 0, 0.2, 1);
        overflow-y: auto;
        overflow-x: hidden;
        padding: 1.5rem 2rem;
        box-sizing: border-box;
        transform: scale(0.95);
    }
    
    .page-container.active {
        opacity: 1;
        visibility: visible;
        transform: scale(1);
    }
    
    /* Custom scrollbar */
    .page-container::-webkit-scrollbar {
        width: 8px;
    }
    
    .page-container::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
    }
    
    .page-container::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 10px;
    }
    
    .page-container::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }
    
    /* Page indicator */
    .page-indicator {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        display: flex;
        gap: 12px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 12px 18px;
        border-radius: 30px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        align-items: center;
    }
    
    .page-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: rgba(102, 126, 234, 0.3);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
    }
    
    .page-dot::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0);
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: rgba(102, 126, 234, 0.2);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .page-dot:hover::before {
        transform: translate(-50%, -50%) scale(1);
    }
    
    .page-dot.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        transform: scale(1.4);
        box-shadow: 0 0 15px rgba(102, 126, 234, 0.6);
    }
    
    .page-dot:hover {
        transform: scale(1.2);
    }
    
    /* Header */
    .page-header {
        background: rgba(255, 255, 255, 1);
        backdrop-filter: blur(20px);
        padding: 2rem 2.5rem;
        border-radius: 20px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25), 0 0 0 2px rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow: hidden;
        border: 2px solid rgba(255, 255, 255, 0.8);
    }
    
    .page-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #4facfe);
        background-size: 200% 100%;
        animation: gradientShift 3s ease infinite;
    }
    
    @keyframes gradientShift {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }
    
    .page-title {
        font-size: 2.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0;
        letter-spacing: -0.5px;
        text-shadow: 0 2px 10px rgba(102, 126, 234, 0.2);
    }
    
    .page-subtitle {
        font-size: 1.1rem;
        color: #666;
        margin-top: 0.5rem;
        font-weight: 500;
    }
    
    .datetime-display {
        text-align: right;
        font-size: 1rem;
        color: #333;
        font-weight: 600;
    }
    
    .datetime-display div:first-child {
        font-size: 0.9rem;
        color: #667eea;
        margin-bottom: 0.25rem;
    }
    
    /* Grid layouts */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    
    .stat-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        padding: 2rem;
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(255, 255, 255, 0.8);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        border: 2px solid rgba(255, 255, 255, 0.5);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .stat-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 12px 48px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(102, 126, 234, 0.5);
        border-color: rgba(102, 126, 234, 0.5);
        background: rgba(255, 255, 255, 1);
    }
    
    .stat-card:hover::before {
        transform: scaleX(1);
    }
    
    /* Colorful card backgrounds with better contrast */
    .stat-card:nth-child(1) { 
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.98) 100%);
        border-left: 4px solid #667eea;
    }
    .stat-card:nth-child(2) { 
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.98) 100%);
        border-left: 4px solid #4facfe;
    }
    .stat-card:nth-child(3) { 
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.98) 100%);
        border-left: 4px solid #43e97b;
    }
    .stat-card:nth-child(4) { 
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.98) 100%);
        border-left: 4px solid #fa709a;
    }
    .stat-card:nth-child(5) { 
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.98) 100%);
        border-left: 4px solid #30cfd0;
    }
    .stat-card:nth-child(6) { 
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.98) 100%);
        border-left: 4px solid #fbbf24;
    }
    .stat-card:nth-child(7) { 
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.98) 100%);
        border-left: 4px solid #8b5cf6;
    }
    .stat-card:nth-child(8) { 
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.98) 100%);
        border-left: 4px solid #ec4899;
    }
    .stat-card:nth-child(9) { 
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.98) 100%);
        border-left: 4px solid #10b981;
    }
    .stat-card:nth-child(10) { 
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.98) 100%);
        border-left: 4px solid #f59e0b;
    }
    .stat-card:nth-child(11) { 
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.98) 100%);
        border-left: 4px solid #06b6d4;
    }
    .stat-card:nth-child(12) { 
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.98) 100%);
        border-left: 4px solid #6366f1;
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        font-size: 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        border: 2px solid rgba(255, 255, 255, 0.3);
    }
    
    /* Different icon colors for variety */
    .stat-card:nth-child(1) .stat-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .stat-card:nth-child(2) .stat-icon { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stat-card:nth-child(3) .stat-icon { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
    .stat-card:nth-child(4) .stat-icon { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    .stat-card:nth-child(5) .stat-icon { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
    .stat-card:nth-child(6) .stat-icon { background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); }
    .stat-card:nth-child(7) .stat-icon { background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%); }
    .stat-card:nth-child(8) .stat-icon { background: linear-gradient(135deg, #ec4899 0%, #f472b6 100%); }
    .stat-card:nth-child(9) .stat-icon { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .stat-card:nth-child(10) .stat-icon { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .stat-card:nth-child(11) .stat-icon { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
    .stat-card:nth-child(12) .stat-icon { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); }
    
    .stat-value {
        font-size: 3rem;
        font-weight: 800;
        color: #1f2937;
        margin: 0.5rem 0;
        line-height: 1;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    /* Colorful stat values matching icon colors */
    .stat-card:nth-child(1) .stat-value { color: #667eea; }
    .stat-card:nth-child(2) .stat-value { color: #4facfe; }
    .stat-card:nth-child(3) .stat-value { color: #43e97b; }
    .stat-card:nth-child(4) .stat-value { color: #fa709a; }
    .stat-card:nth-child(5) .stat-value { color: #30cfd0; }
    .stat-card:nth-child(6) .stat-value { color: #fbbf24; }
    .stat-card:nth-child(7) .stat-value { color: #8b5cf6; }
    .stat-card:nth-child(8) .stat-value { color: #ec4899; }
    .stat-card:nth-child(9) .stat-value { color: #10b981; }
    .stat-card:nth-child(10) .stat-value { color: #f59e0b; }
    .stat-card:nth-child(11) .stat-value { color: #06b6d4; }
    .stat-card:nth-child(12) .stat-value { color: #6366f1; }
    
    .stat-label {
        font-size: 0.85rem;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        margin-top: 0.5rem;
    }
    
    .chart-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    
    @media (max-width: 1400px) {
        .chart-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .chart-card {
        background: rgba(255, 255, 255, 1);
        backdrop-filter: blur(20px);
        padding: 2rem;
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2), 0 0 0 2px rgba(255, 255, 255, 0.8);
        min-height: 400px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        border: 2px solid rgba(255, 255, 255, 0.8);
    }
    
    .chart-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .chart-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 48px rgba(0, 0, 0, 0.2);
    }
    
    .chart-card:hover::before {
        transform: scaleX(1);
    }
    
    .chart-title {
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .chart-title::before {
        content: '';
        width: 4px;
        height: 24px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 2px;
    }
    
    /* Table styles */
    .data-table {
        background: rgba(255, 255, 255, 1);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2), 0 0 0 2px rgba(255, 255, 255, 0.8);
        overflow-x: auto;
        position: relative;
        border: 2px solid rgba(255, 255, 255, 0.8);
    }
    
    .data-table::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        border-radius: 20px 20px 0 0;
    }
    
    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    th, td {
        padding: 1.25rem 1rem;
        text-align: left;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    th {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        font-weight: 700;
        color: #333;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 1px;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    tr {
        transition: all 0.3s ease;
    }
    
    tr:hover {
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        transform: scale(1.01);
    }
    
    td {
        color: #555;
        font-weight: 500;
    }
    
    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
    }
    
    /* Responsive */
    @media (max-width: 1920px) {
        .page-title {
            font-size: 1.5rem;
        }
        .stat-value {
            font-size: 2rem;
        }
    }
</style>

<div class="dashboard-container">
    <!-- Page Indicator -->
    <div class="page-indicator">
        <div class="page-dot active" data-page="0" title="Database Mesin & Lokasi"></div>
        <div class="page-dot" data-page="1" title="Jadwal Maintenance"></div>
        <div class="page-dot" data-page="2" title="Informasi Downtime"></div>
        <div class="page-dot" data-page="3" title="Spareparts & Standards"></div>
    </div>
    
    <!-- PAGE 1: Database Mesin dan Lokasi -->
    <div class="page-container active" id="page-0">
        <div class="page-header">
            <div>
                <h1 class="page-title">Database Mesin & Lokasi</h1>
                <p class="page-subtitle">Informasi lengkap tentang mesin, lokasi, dan struktur organisasi</p>
            </div>
            <div class="datetime-display">
                <div id="current-datetime-0"></div>
                <div>{{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y') }}</div>
                @if(Auth::check() && Auth::user()->isAdmin())
                <div style="margin-top: 0.5rem;">
                    <a href="{{ route('dashboard-settings.index') }}" 
                       class="text-xs text-blue-600 hover:text-blue-800 font-semibold underline">
                        ⚙️ Settings
                    </a>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🏭</div>
                <div class="stat-value">{{ number_format($totalMachines) }}</div>
                <div class="stat-label">Total Mesin</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🏢</div>
                <div class="stat-value">{{ number_format($totalPlants) }}</div>
                <div class="stat-label">Total Plant</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">⚙️</div>
                <div class="stat-value">{{ number_format($totalProcesses) }}</div>
                <div class="stat-label">Total Process</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📊</div>
                <div class="stat-value">{{ number_format($totalLines) }}</div>
                <div class="stat-label">Total Line</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🚪</div>
                <div class="stat-value">{{ number_format($totalRooms) }}</div>
                <div class="stat-label">Total Room</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🔧</div>
                <div class="stat-value">{{ number_format($totalMachineTypes) }}</div>
                <div class="stat-label">Machine Types</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🏷️</div>
                <div class="stat-value">{{ number_format($totalBrands) }}</div>
                <div class="stat-label">Total Brands</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📦</div>
                <div class="stat-value">{{ number_format($totalModels) }}</div>
                <div class="stat-label">Total Models</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🔗</div>
                <div class="stat-value">{{ number_format($totalSystems) }}</div>
                <div class="stat-label">Total Systems</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">👥</div>
                <div class="stat-value">{{ number_format($totalGroups) }}</div>
                <div class="stat-label">Total Groups</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">✅</div>
                <div class="stat-value">{{ number_format($machinesWithBrand) }}</div>
                <div class="stat-label">Mesin dengan Brand</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📋</div>
                <div class="stat-value">{{ number_format($machinesWithModel) }}</div>
                <div class="stat-label">Mesin dengan Model</div>
            </div>
        </div>
        
        <!-- Additional Info -->
        <div class="chart-grid">
            <div class="chart-card">
                <h3 class="chart-title">Distribusi Mesin</h3>
                <canvas id="machineDistributionChart"></canvas>
            </div>
            <div class="chart-card">
                <h3 class="chart-title">Mesin dengan PM</h3>
                <div style="font-size: 3rem; text-align: center; padding: 2rem; color: #667eea;">
                    {{ number_format($machinesWithPM) }}
                </div>
                <div style="text-align: center; color: #666;">
                    dari {{ number_format($totalMachines) }} mesin
                </div>
            </div>
        </div>
    </div>
    
    <!-- PAGE 2: Jadwal Maintenance -->
    <div class="page-container" id="page-1">
        <div class="page-header">
            <div>
                <h1 class="page-title">Jadwal Maintenance</h1>
                <p class="page-subtitle">Informasi jadwal preventive, predictive maintenance, dan work orders</p>
            </div>
            <div class="datetime-display">
                <div id="current-datetime-1"></div>
                <div>{{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y') }}</div>
                @if(Auth::check() && Auth::user()->isAdmin())
                <div style="margin-top: 0.5rem;">
                    <a href="{{ route('dashboard-settings.index') }}" 
                       class="text-xs text-blue-600 hover:text-blue-800 font-semibold underline">
                        ⚙️ Settings
                    </a>
                </div>
                @endif
            </div>
        </div>
        
        <!-- PM Statistics -->
        <div class="stats-grid">
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📅</div>
                <div class="stat-value">{{ number_format($pmSchedulesThisMonth) }}</div>
                <div class="stat-label">PM This Month</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">⏳</div>
                <div class="stat-value">{{ number_format($pmSchedulesPending) }}</div>
                <div class="stat-label">PM Pending</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">⚙️</div>
                <div class="stat-value">{{ number_format($pmSchedulesInProgress) }}</div>
                <div class="stat-label">PM In Progress</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">✅</div>
                <div class="stat-value">{{ number_format($pmSchedulesCompleted) }}</div>
                <div class="stat-label">PM Completed</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📊</div>
                <div class="stat-value">{{ number_format($pmCompletionRate, 1) }}%</div>
                <div class="stat-label">PM Completion Rate</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🔮</div>
                <div class="stat-value">{{ number_format($pdmSchedulesThisMonth) }}</div>
                <div class="stat-label">PdM This Month</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">⏰</div>
                <div class="stat-value">{{ number_format($pdmSchedulesPending) }}</div>
                <div class="stat-label">PdM Pending</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">✔️</div>
                <div class="stat-value">{{ number_format($pdmSchedulesCompleted) }}</div>
                <div class="stat-label">PdM Completed</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📈</div>
                <div class="stat-value">{{ number_format($pdmCompletionRate, 1) }}%</div>
                <div class="stat-label">PdM Completion Rate</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📋</div>
                <div class="stat-value">{{ number_format($workOrdersTotal) }}</div>
                <div class="stat-label">Work Orders Total</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📆</div>
                <div class="stat-value">{{ number_format($workOrdersThisMonth) }}</div>
                <div class="stat-label">WO This Month</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🎯</div>
                <div class="stat-value">{{ number_format($workOrdersCompleted) }}</div>
                <div class="stat-label">WO Completed</div>
            </div>
        </div>
        
        <!-- Upcoming Schedules -->
        <div class="chart-grid">
            <div class="data-table">
                <h3 class="chart-title">Upcoming PM Schedules</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Mesin</th>
                            <th>Assigned To</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingPMSchedules->take(10) as $schedule)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($schedule->start_date)->format('d/m/Y') }}</td>
                            <td>{{ $schedule->machineErp->idMachine ?? 'N/A' }}</td>
                            <td>{{ $schedule->assignedUser->name ?? 'Unassigned' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #999;">No upcoming PM schedules</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="data-table">
                <h3 class="chart-title">Upcoming PdM Schedules</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Mesin</th>
                            <th>Standard</th>
                            <th>Assigned To</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingPDMSchedules->take(10) as $schedule)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($schedule->start_date)->format('d/m/Y') }}</td>
                            <td>{{ $schedule->machineErp->idMachine ?? 'N/A' }}</td>
                            <td>{{ $schedule->standard->name ?? 'N/A' }}</td>
                            <td>{{ $schedule->assignedUser->name ?? 'Unassigned' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: #999;">No upcoming PdM schedules</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- PAGE 3: Informasi Downtime -->
    <div class="page-container" id="page-2">
        <div class="page-header">
            <div>
                <h1 class="page-title">Informasi Downtime</h1>
                <p class="page-subtitle">Statistik dan analisis downtime untuk bulan ini</p>
            </div>
            <div class="datetime-display">
                <div id="current-datetime-2"></div>
                <div>{{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y') }}</div>
                @if(Auth::check() && Auth::user()->isAdmin())
                <div style="margin-top: 0.5rem;">
                    <a href="{{ route('dashboard-settings.index') }}" 
                       class="text-xs text-blue-600 hover:text-blue-800 font-semibold underline">
                        ⚙️ Settings
                    </a>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Downtime Statistics -->
        <div class="stats-grid">
            <div class="stat-card fade-in-up">
                <div class="stat-icon">⚠️</div>
                <div class="stat-value">{{ number_format($monthDowntimeCount) }}</div>
                <div class="stat-label">Total Downtime</div>
                <div style="font-size: 0.75rem; color: #999; margin-top: 0.25rem;">incidents</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">⏱️</div>
                <div class="stat-value">{{ number_format($monthDowntime, 0) }}</div>
                <div class="stat-label">Total Duration</div>
                <div style="font-size: 0.75rem; color: #999; margin-top: 0.25rem;">minutes</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📊</div>
                <div class="stat-value">{{ number_format($avgDowntimeDuration, 1) }}</div>
                <div class="stat-label">Avg Duration</div>
                <div style="font-size: 0.75rem; color: #999; margin-top: 0.25rem;">min/incident</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📅</div>
                <div class="stat-value">{{ number_format($avgDowntimePerDay, 1) }}</div>
                <div class="stat-label">Avg per Day</div>
                <div style="font-size: 0.75rem; color: #999; margin-top: 0.25rem;">min/day</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🔧</div>
                <div class="stat-value">{{ number_format($monthDowntimeCount > 0 ? $monthDowntimeCount / $daysInMonth : 0, 1) }}</div>
                <div class="stat-label">Breakdowns/Day</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🏆</div>
                <div class="stat-value" style="font-size: 1.5rem;">
                    {{ $mostProblematicMachine->idMachine ?? 'N/A' }}
                </div>
                <div class="stat-label">Top Machine</div>
                <div style="font-size: 0.75rem; color: #999; margin-top: 0.25rem;">
                    {{ $mostProblematicMachine ? number_format((float)($mostProblematicMachine->total_duration ?? 0), 0) . ' min' : '' }}
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">⏰</div>
                <div class="stat-value">{{ $longestDowntime ? number_format((float)($longestDowntime->duration ?? 0), 0) : '-' }}</div>
                <div class="stat-label">Longest DT</div>
                <div style="font-size: 0.75rem; color: #999; margin-top: 0.25rem;">minutes</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">👷</div>
                <div class="stat-value">{{ number_format($activeMechanics) }}</div>
                <div class="stat-label">Active Mechanics</div>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="chart-grid">
            <div class="chart-card">
                <h3 class="chart-title">Top 10 Machine (Downtime)</h3>
                <canvas id="machineDowntimeChart"></canvas>
            </div>
            <div class="chart-card">
                <h3 class="chart-title">Top 5 MTTR</h3>
                <canvas id="mttrChart"></canvas>
            </div>
            <div class="chart-card">
                <h3 class="chart-title">Top 5 Plant (Downtime)</h3>
                <canvas id="plantDowntimeChart"></canvas>
            </div>
            <div class="chart-card">
                <h3 class="chart-title">Downtime Trend</h3>
                <canvas id="downtimeTrendChart"></canvas>
            </div>
            <div class="chart-card">
                <h3 class="chart-title">Top 5 Problems</h3>
                <canvas id="problemsChart"></canvas>
            </div>
        </div>
        
        <!-- Top Lists -->
        <div class="chart-grid">
            <div class="data-table">
                <h3 class="chart-title">Top Mekanik</h3>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Downtime Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topMekanik->take(5) as $index => $mekanik)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $mekanik->nameMekanik ?? 'N/A' }}</td>
                            <td>{{ $mekanik->downtime_count }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #999;">No data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="data-table">
                <h3 class="chart-title">Top Lines</h3>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Line</th>
                            <th>Duration (min)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topLines->take(5) as $index => $line)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $line->line ?? 'N/A' }}</td>
                            <td>{{ number_format((float)($line->total_duration ?? 0), 0) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #999;">No data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- PAGE 4: Spareparts & Standards -->
    <div class="page-container" id="page-3">
        <div class="page-header">
            <div>
                <h1 class="page-title">Spareparts & Standards</h1>
                <p class="page-subtitle">Informasi inventory spareparts dan standards</p>
            </div>
            <div class="datetime-display">
                <div id="current-datetime-3"></div>
                <div>{{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y') }}</div>
                @if(Auth::check() && Auth::user()->isAdmin())
                <div style="margin-top: 0.5rem;">
                    <a href="{{ route('dashboard-settings.index') }}" 
                       class="text-xs text-blue-600 hover:text-blue-800 font-semibold underline">
                        ⚙️ Settings
                    </a>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📦</div>
                <div class="stat-value">{{ number_format($totalSpareparts) }}</div>
                <div class="stat-label">Total Spareparts</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🔴</div>
                <div class="stat-value" style="color: #ef4444;">{{ number_format($lowStockSpareparts) }}</div>
                <div class="stat-label">Low Stock Alert</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">💰</div>
                <div class="stat-value" style="font-size: 2rem;">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</div>
                <div class="stat-label">Total Stock Value</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📋</div>
                <div class="stat-value">{{ number_format($totalStandards) }}</div>
                <div class="stat-label">Total Standards</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">✅</div>
                <div class="stat-value">{{ number_format($activeStandards) }}</div>
                <div class="stat-label">Active Standards</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🚨</div>
                <div class="stat-value" style="color: #ef4444;">{{ number_format($redStatusCount) }}</div>
                <div class="stat-label">Red Status</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">⚠️</div>
                <div class="stat-value" style="color: #ef4444;">{{ number_format($redStatusThisMonth) }}</div>
                <div class="stat-label">Red This Month</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">❌</div>
                <div class="stat-value">{{ number_format($uniqueProblems) }}</div>
                <div class="stat-label">Unique Problems</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">❓</div>
                <div class="stat-value">{{ number_format($uniqueReasons) }}</div>
                <div class="stat-label">Unique Reasons</div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🔧</div>
                <div class="stat-value">{{ number_format($uniqueActions) }}</div>
                <div class="stat-label">Unique Actions</div>
            </div>
        </div>
        
        <!-- Additional Info -->
        <div class="chart-grid">
            <div class="data-table">
                <h3 class="chart-title">Recent Work Orders</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentWorkOrders->take(10) as $wo)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($wo->order_date)->format('d/m/Y') }}</td>
                            <td>
                                <span style="padding: 0.25rem 0.5rem; border-radius: 5px; font-size: 0.8rem;
                                    @if($wo->status == 'completed') background: #10b981; color: white;
                                    @elseif($wo->status == 'in_progress') background: #3b82f6; color: white;
                                    @else background: #f59e0b; color: white; @endif">
                                    {{ ucfirst($wo->status) }}
                                </span>
                            </td>
                            <td>{{ Str::limit($wo->description ?? 'N/A', 50) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #999;">No work orders</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Auto-rotate pages
    const PAGE_ROTATE_INTERVAL = 30000; // 30 seconds
    let currentPage = 0;
    const totalPages = 4;
    let rotateTimer;
    
    function showPage(pageIndex) {
        // Hide all pages
        document.querySelectorAll('.page-container').forEach((page, index) => {
            page.classList.remove('active');
            document.querySelectorAll('.page-dot')[index].classList.remove('active');
        });
        
        // Show selected page
        document.getElementById(`page-${pageIndex}`).classList.add('active');
        document.querySelectorAll('.page-dot')[pageIndex].classList.add('active');
        
        currentPage = pageIndex;
        
        // Reset chart initialization flags when switching pages and reinitialize
        if (pageIndex === 0) {
            // Destroy existing charts from other pages
            Object.keys(chartInstances).forEach(chartId => {
                if (chartId !== 'machineDistributionChart') {
                    destroyChart(chartId);
                }
            });
            chartInitialized['machineDistributionChart'] = false;
            setTimeout(() => {
                initializeMachineDistributionChart();
            }, 300);
        } else if (pageIndex === 2) {
            // Destroy existing charts from other pages
            Object.keys(chartInstances).forEach(chartId => {
                if (!chartId.includes('Downtime') && !chartId.includes('mttr') && !chartId.includes('plant') && !chartId.includes('trend') && !chartId.includes('problems')) {
                    destroyChart(chartId);
                }
            });
            chartInitialized['downtimeCharts'] = false;
            setTimeout(() => {
                initializeDowntimeCharts();
            }, 300);
        } else {
            // Destroy all charts when on other pages
            Object.keys(chartInstances).forEach(chartId => {
                destroyChart(chartId);
            });
        }
    }
    
    function nextPage() {
        const next = (currentPage + 1) % totalPages;
        showPage(next);
    }
    
    function startAutoRotate() {
        rotateTimer = setInterval(nextPage, PAGE_ROTATE_INTERVAL);
    }
    
    function stopAutoRotate() {
        if (rotateTimer) {
            clearInterval(rotateTimer);
        }
    }
    
    // Page dot click handlers
    document.querySelectorAll('.page-dot').forEach((dot, index) => {
        dot.addEventListener('click', () => {
            stopAutoRotate();
            showPage(index);
            startAutoRotate();
        });
    });
    
    // Update datetime every second
    function updateDateTime() {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        const dateTimeStr = now.toLocaleDateString('id-ID', options);
        
        for (let i = 0; i < totalPages; i++) {
            const elem = document.getElementById(`current-datetime-${i}`);
            if (elem) elem.textContent = dateTimeStr;
        }
    }
    
    // Initialize
    showPage(0);
    startAutoRotate();
    updateDateTime();
    setInterval(updateDateTime, 1000);
    
    // Chart instances storage
    const chartInstances = {};
    const chartInitialized = {};
    
    // Destroy existing chart if exists
    function destroyChart(chartId) {
        if (chartInstances[chartId]) {
            try {
                chartInstances[chartId].destroy();
            } catch (e) {
                console.warn('Error destroying chart:', e);
            }
            chartInstances[chartId] = null;
            chartInitialized[chartId] = false;
        }
    }
    
    // Check if element is visible
    function isElementVisible(element) {
        if (!element) return false;
        const rect = element.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0 && 
               window.getComputedStyle(element).display !== 'none' &&
               window.getComputedStyle(element).visibility !== 'hidden';
    }
    
    // Initialize charts for downtime page
    function initializeDowntimeCharts() {
        // Prevent multiple initialization
        if (chartInitialized['downtimeCharts']) {
            return;
        }
        
        // Wait for page to be fully visible
        setTimeout(() => {
            // Top 10 Machine Downtime Chart
            const machineCtx = document.getElementById('machineDowntimeChart');
            if (machineCtx && isElementVisible(machineCtx)) {
                destroyChart('machineDowntimeChart');
                const machineData = @json($topMachines ?? []);
                if (machineData.length > 0) {
                const labels = machineData.map(m => m.idMachine || 'N/A');
                const durations = machineData.map(m => parseFloat(m.total_duration) || 0);
                
                chartInstances['machineDowntimeChart'] = new Chart(machineCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Downtime (minutes)',
                            data: durations,
                            backgroundColor: 'rgba(102, 126, 234, 0.8)',
                            borderColor: 'rgba(102, 126, 234, 1)',
                            borderWidth: 2,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: { size: 14, weight: 'bold' },
                                bodyFont: { size: 13 }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                },
                                ticks: {
                                    font: { size: 11 }
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: {
                                    font: { size: 10 },
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            }
                        },
                        animation: {
                            duration: 1500,
                            easing: 'easeOutQuart'
                        },
                        onResize: function(chart, size) {
                            // Handle resize
                        }
                    }
                });
                }
            }
            
            // MTTR Chart
            const mttrCtx = document.getElementById('mttrChart');
            if (mttrCtx && isElementVisible(mttrCtx)) {
                destroyChart('mttrChart');
                const mttrData = @json($topMTTR ?? []);
                if (mttrData.length > 0) {
                const labels = mttrData.map(m => m.idMachine || 'N/A');
                const mttrValues = mttrData.map(m => parseFloat(m.mttr) || 0);
                
                chartInstances['mttrChart'] = new Chart(mttrCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'MTTR (minutes)',
                            data: mttrValues,
                            backgroundColor: 'rgba(239, 68, 68, 0.8)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 2,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            y: {
                                grid: { display: false },
                                ticks: {
                                    font: { size: 11 }
                                }
                            }
                        },
                        animation: {
                            duration: 1500,
                            easing: 'easeOutQuart'
                        },
                        onResize: function(chart, size) {
                            // Handle resize
                        }
                    }
                });
                }
            }
            
            // Plant Downtime Chart
            const plantCtx = document.getElementById('plantDowntimeChart');
            if (plantCtx && isElementVisible(plantCtx)) {
                destroyChart('plantDowntimeChart');
                const plantData = @json($topPlants ?? []);
                if (plantData.length > 0) {
                const labels = plantData.map(p => p.plant || 'N/A');
                const durations = plantData.map(p => parseFloat(p.total_duration) || 0);
                
                chartInstances['plantDowntimeChart'] = new Chart(plantCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Downtime (minutes)',
                            data: durations,
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 2,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            y: {
                                grid: { display: false },
                                ticks: {
                                    font: { size: 11 }
                                }
                            }
                        },
                        animation: {
                            duration: 1500,
                            easing: 'easeOutQuart'
                        },
                        onResize: function(chart, size) {
                            // Handle resize
                        }
                    }
                });
                }
            }
            
            // Downtime Trend Chart
            const trendCtx = document.getElementById('downtimeTrendChart');
            if (trendCtx && isElementVisible(trendCtx)) {
                destroyChart('downtimeTrendChart');
                const trendData = @json($downtimeTrend ?? []);
                if (trendData.length > 0) {
                const labels = trendData.map(t => {
                    const date = new Date(t.date);
                    return date.getDate().toString().padStart(2, '0') + '/' + (date.getMonth() + 1).toString().padStart(2, '0');
                });
                const counts = trendData.map(t => t.count || 0);
                const durations = trendData.map(t => parseFloat(t.total_duration) || 0);
                
                chartInstances['downtimeTrendChart'] = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Downtime Count',
                                data: counts,
                                borderColor: 'rgba(102, 126, 234, 1)',
                                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                                borderWidth: 3,
                                tension: 0.4,
                                fill: true,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                yAxisID: 'y'
                            },
                            {
                                label: 'Duration (minutes)',
                                data: durations,
                                borderColor: 'rgba(245, 87, 108, 1)',
                                backgroundColor: 'rgba(245, 87, 108, 0.1)',
                                borderWidth: 3,
                                tension: 0.4,
                                fill: true,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                yAxisID: 'y1'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { 
                                display: true,
                                position: 'top',
                                labels: {
                                    font: { size: 12, weight: 'bold' },
                                    padding: 15,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                mode: 'index',
                                intersect: false
                            }
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                position: 'left',
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            y1: {
                                type: 'linear',
                                position: 'right',
                                beginAtZero: true,
                                grid: {
                                    drawOnChartArea: false
                                }
                            },
                            x: {
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            }
                        },
                        animation: {
                            duration: 2000,
                            easing: 'easeOutQuart'
                        },
                        onResize: function(chart, size) {
                            // Handle resize
                        }
                    }
                });
                }
            }
            
            // Problems Chart
            const problemsCtx = document.getElementById('problemsChart');
            if (problemsCtx && isElementVisible(problemsCtx)) {
                destroyChart('problemsChart');
                const problemsData = @json($topProblems ?? []);
                if (problemsData.length > 0) {
                const labels = problemsData.map(p => p.problemDowntime || 'N/A');
                const counts = problemsData.map(p => p.problem_count || 0);
                
                chartInstances['problemsChart'] = new Chart(problemsCtx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: counts,
                            backgroundColor: [
                                'rgba(239, 68, 68, 0.9)',
                                'rgba(245, 158, 11, 0.9)',
                                'rgba(251, 191, 36, 0.9)',
                                'rgba(34, 197, 94, 0.9)',
                                'rgba(59, 130, 246, 0.9)'
                            ],
                            borderColor: '#fff',
                            borderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: { size: 11, weight: 'bold' },
                                    padding: 12,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += context.parsed + ' incidents';
                                        return label;
                                    }
                                }
                            }
                        },
                        animation: {
                            animateRotate: true,
                            animateScale: true,
                            duration: 1500,
                            easing: 'easeOutQuart'
                        },
                        onResize: function(chart, size) {
                            // Handle resize
                        }
                    }
                });
                }
            }
            
            chartInitialized['downtimeCharts'] = true;
        }, 300); // Wait 300ms for page transition to complete
    }
    
    // Initialize machine distribution chart for page 1
    function initializeMachineDistributionChart() {
        // Prevent multiple initialization
        if (chartInitialized['machineDistributionChart']) {
            return;
        }
        
        // Wait for page to be fully visible
        setTimeout(() => {
            const ctx = document.getElementById('machineDistributionChart');
            if (ctx && isElementVisible(ctx)) {
                destroyChart('machineDistributionChart');
                chartInstances['machineDistributionChart'] = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['With Brand', 'With Model', 'With Type'],
                    datasets: [{
                        data: [
                            {{ $machinesWithBrand }},
                            {{ $machinesWithModel }},
                            {{ $machinesWithType }}
                        ],
                        backgroundColor: [
                            'rgba(102, 126, 234, 0.8)',
                            'rgba(118, 75, 162, 0.8)',
                            'rgba(245, 87, 108, 0.8)'
                        ]
                    }]
                },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: { size: 12, weight: 'bold' },
                                    padding: 15,
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12
                            }
                        },
                        animation: {
                            animateRotate: true,
                            animateScale: true,
                            duration: 1500,
                            easing: 'easeOutQuart'
                        },
                        onResize: function(chart, size) {
                            // Handle resize
                        }
                    }
                });
                chartInitialized['machineDistributionChart'] = true;
            }
        }, 300); // Wait 300ms for page transition to complete
    }
    
    // Resize handler for charts
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            // Resize all charts
            Object.keys(chartInstances).forEach(chartId => {
                if (chartInstances[chartId] && typeof chartInstances[chartId].resize === 'function') {
                    try {
                        chartInstances[chartId].resize();
                    } catch (e) {
                        console.warn('Error resizing chart:', e);
                    }
                }
            });
        }, 250);
    });
    
    // Initialize charts on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                if (document.getElementById('page-0') && document.getElementById('page-0').classList.contains('active')) {
                    initializeMachineDistributionChart();
                }
            }, 500);
        });
    } else {
        // DOM already loaded
        setTimeout(() => {
            if (document.getElementById('page-0') && document.getElementById('page-0').classList.contains('active')) {
                initializeMachineDistributionChart();
            }
        }, 500);
    }
</script>
@endsection
