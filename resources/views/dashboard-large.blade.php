@extends('layouts.app')
@section('content')
<style>
    /* Fullscreen layout - no scroll, full viewport */
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
        background: linear-gradient(135deg, #7c2d12 0%, #9a3412 30%, #c2410c 60%, #ea580c 100%);
    }
    
    #page-4 {
        background: linear-gradient(135deg, #065f46 0%, #047857 30%, #059669 60%, #10b981 100%);
    }
    
    /* Page container - fullscreen, satu halaman penuh tanpa scroll */
    .page-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        max-width: 100%;
        height: 100%;
        min-height: 100%;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.8s cubic-bezier(0.4, 0, 0.2, 1), transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        padding: 0.5rem 1rem;
        box-sizing: border-box;
        transform: scale(0.98);
        display: flex;
        flex-direction: column;
    }
    
    .page-container.active {
        opacity: 1;
        visibility: visible;
        transform: scale(1);
    }
    
    .page-container .page-content-fill {
        flex: 1;
        min-height: 0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    
    /* Page indicator - dipindah ke bawah */
    .page-indicator {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
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
    
    /* Header - compact untuk full page */
    .page-header {
        flex-shrink: 0;
        background: rgba(255, 255, 255, 1);
        backdrop-filter: blur(20px);
        padding: 0.5rem 1rem;
        border-radius: 12px;
        margin-bottom: 0.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2), 0 0 0 2px rgba(255, 255, 255, 0.8);
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
        height: 3px;
        background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #4facfe);
        background-size: 200% 100%;
        animation: gradientShift 3s ease infinite;
    }
    
    @keyframes gradientShift {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }
    
    .page-title {
        font-size: 1.35rem;
        font-weight: 800;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin: 0;
        letter-spacing: -0.5px;
    }
    
    .page-subtitle {
        font-size: 0.75rem;
        color: #666;
        margin-top: 0.15rem;
        font-weight: 500;
    }
    
    .datetime-display {
        text-align: right;
        font-size: 0.8rem;
        color: #333;
        font-weight: 600;
    }
    
    .datetime-display div:first-child {
        font-size: 0.7rem;
        color: #667eea;
        margin-bottom: 0.1rem;
    }
    
    /* Grid standar: 6 kolom x 2 baris, maksimal 12 info card */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        grid-template-rows: repeat(2, auto);
        gap: 0.6rem;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        flex-shrink: 0;
    }
    .stats-grid .stat-card:nth-child(n+13) {
        display: none;
    }
    @media (max-width: 1600px) {
        .stats-grid { grid-template-columns: repeat(4, 1fr); }
    }
    @media (max-width: 1200px) {
        .stats-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    
    /* Glow animations untuk stat cards - seperti halaman Report */
    @keyframes glow-1 {
        0%, 100% { box-shadow: 0 0 15px rgba(102, 126, 234, 0.4), 0 0 30px rgba(118, 75, 162, 0.2); }
        50% { box-shadow: 0 0 25px rgba(102, 126, 234, 0.6), 0 0 50px rgba(118, 75, 162, 0.4); }
    }
    @keyframes glow-2 {
        0%, 100% { box-shadow: 0 0 15px rgba(79, 172, 254, 0.4), 0 0 30px rgba(0, 242, 254, 0.2); }
        50% { box-shadow: 0 0 25px rgba(79, 172, 254, 0.6), 0 0 50px rgba(0, 242, 254, 0.4); }
    }
    @keyframes glow-3 {
        0%, 100% { box-shadow: 0 0 15px rgba(67, 233, 123, 0.4), 0 0 30px rgba(56, 249, 215, 0.2); }
        50% { box-shadow: 0 0 25px rgba(67, 233, 123, 0.6), 0 0 50px rgba(56, 249, 215, 0.4); }
    }
    @keyframes glow-4 {
        0%, 100% { box-shadow: 0 0 15px rgba(250, 112, 154, 0.4), 0 0 30px rgba(254, 225, 64, 0.2); }
        50% { box-shadow: 0 0 25px rgba(250, 112, 154, 0.6), 0 0 50px rgba(254, 225, 64, 0.4); }
    }
    @keyframes glow-5 {
        0%, 100% { box-shadow: 0 0 15px rgba(48, 207, 208, 0.4), 0 0 30px rgba(51, 8, 103, 0.2); }
        50% { box-shadow: 0 0 25px rgba(48, 207, 208, 0.6), 0 0 50px rgba(51, 8, 103, 0.4); }
    }
    @keyframes glow-6 {
        0%, 100% { box-shadow: 0 0 15px rgba(251, 191, 36, 0.4), 0 0 30px rgba(245, 158, 11, 0.2); }
        50% { box-shadow: 0 0 25px rgba(251, 191, 36, 0.6), 0 0 50px rgba(245, 158, 11, 0.4); }
    }
    @keyframes shimmer {
        0% { left: -100%; }
        100% { left: 100%; }
    }
    
    .stat-card {
        padding: 1.5rem 1.75rem;
        border-radius: 14px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        background-size: 200% 200%;
        color: white;
        min-height: 150px;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }
    
    .stat-card .stat-content {
        flex: 1;
        text-align: right;
    }
    
    /* Shimmer effect on hover */
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.6s;
    }
    .stat-card:hover::before {
        animation: shimmer 0.8s ease forwards;
    }
    
    /* Radial glow on hover */
    .stat-card::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.4s;
    }
    .stat-card:hover::after {
        opacity: 1;
    }
    
    .stat-card:hover {
        transform: translateY(-8px) scale(1.03);
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }
    
    /* Colorful gradient backgrounds - seperti halaman MTTR & Mechanic Performance */
    .stat-card:nth-child(1) { 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        animation: glow-1 3s ease-in-out infinite;
    }
    .stat-card:nth-child(2) { 
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        animation: glow-2 3s ease-in-out infinite 0.3s;
    }
    .stat-card:nth-child(3) { 
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        animation: glow-3 3s ease-in-out infinite 0.6s;
    }
    .stat-card:nth-child(4) { 
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        animation: glow-4 3s ease-in-out infinite 0.9s;
    }
    .stat-card:nth-child(5) { 
        background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
        animation: glow-5 3s ease-in-out infinite 1.2s;
    }
    .stat-card:nth-child(6) { 
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        animation: glow-6 3s ease-in-out infinite 1.5s;
    }
    .stat-card:nth-child(7) { 
        background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
        animation: glow-1 3s ease-in-out infinite 1.8s;
    }
    .stat-card:nth-child(8) { 
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        animation: glow-4 3s ease-in-out infinite 2.1s;
    }
    .stat-card:nth-child(9) { 
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        animation: glow-3 3s ease-in-out infinite 2.4s;
    }
    .stat-card:nth-child(10) { 
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        animation: glow-6 3s ease-in-out infinite 2.7s;
    }
    .stat-card:nth-child(11) { 
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        animation: glow-2 3s ease-in-out infinite 3s;
    }
    .stat-card:nth-child(12) { 
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        animation: glow-1 3s ease-in-out infinite 3.3s;
    }
    
    /* Icon dengan glass-morphism - seperti halaman Report */
    .stat-icon {
        width: 56px;
        height: 56px;
        min-width: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        color: white;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }
    
    .stat-card:hover .stat-icon {
        transform: rotate(5deg) scale(1.1);
        background: rgba(255, 255, 255, 0.3);
    }
    
    .stat-value {
        font-size: 2.25rem;
        font-weight: 800;
        color: white;
        line-height: 1;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }
    
    .stat-label {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.95);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-top: 0.4rem;
    }
    
    .stat-unit {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.85);
        margin-top: 0.2rem;
        font-weight: 500;
    }
    
    .chart-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        flex: 1;
        min-height: 0;
    }
    
    @media (max-width: 1200px) {
        .chart-grid { grid-template-columns: 1fr; }
    }
    
    .chart-card {
        background: rgba(255, 255, 255, 1);
        backdrop-filter: blur(20px);
        padding: 1rem 1.25rem;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15), 0 0 0 2px rgba(255, 255, 255, 0.8);
        min-height: 250px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.8);
        display: flex;
        flex-direction: column;
    }
    
    .chart-card canvas {
        width: 100% !important;
        flex: 1;
        min-height: 200px;
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
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-shrink: 0;
    }
    
    .chart-title::before {
        content: '';
        width: 4px;
        height: 18px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 2px;
    }
    
    /* Chart content wrapper */
    .chart-content {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 0;
    }
    
    /* Table styles - compact */
    .data-table {
        background: rgba(255, 255, 255, 1);
        backdrop-filter: blur(20px);
        border-radius: 12px;
        padding: 1rem 1.25rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15), 0 0 0 2px rgba(255, 255, 255, 0.8);
        overflow: hidden;
        position: relative;
        border: 1px solid rgba(255, 255, 255, 0.8);
        flex: 1;
        min-height: 250px;
        display: flex;
        flex-direction: column;
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
    
    .data-table .table-wrapper {
        flex: 1;
        overflow-y: auto;
        min-height: 0;
    }
    
    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    th, td {
        padding: 0.4rem 0.6rem;
        text-align: left;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        font-size: 0.75rem;
    }
    
    th {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        font-weight: 700;
        color: #333;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
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
    @media (max-width: 1400px) {
        .page-title { font-size: 1.15rem; }
        .stat-value { font-size: 1.5rem; }
        .chart-card { max-height: 200px; min-height: 160px; }
        .chart-card canvas { max-height: 140px !important; height: 140px !important; }
    }
</style>

<div class="dashboard-container">
    <!-- Page Indicator -->
    <div class="page-indicator">
        <div class="page-dot active" data-page="0" title="Database Mesin & Lokasi"></div>
        <div class="page-dot" data-page="1" title="Jadwal Maintenance"></div>
        <div class="page-dot" data-page="2" title="Informasi Downtime"></div>
        <div class="page-dot" data-page="3" title="Informasi User / Skill Matrix"></div>
        <div class="page-dot" data-page="4" title="Spareparts & Standards"></div>
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
        
        <div class="page-content-fill">
        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🏭</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($totalMachines) }}</div>
                    <div class="stat-label">Total Mesin</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🏢</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($totalPlants) }}</div>
                    <div class="stat-label">Total Plant</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">⚙️</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($totalProcesses) }}</div>
                    <div class="stat-label">Total Process</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($totalLines) }}</div>
                    <div class="stat-label">Total Line</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🚪</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($totalRooms) }}</div>
                    <div class="stat-label">Total Room</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🔧</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($totalMachineTypes) }}</div>
                    <div class="stat-label">Machine Types</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🏷️</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($totalBrands) }}</div>
                    <div class="stat-label">Total Brands</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📦</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($totalModels) }}</div>
                    <div class="stat-label">Total Models</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🔗</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($totalSystems) }}</div>
                    <div class="stat-label">Total Systems</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($totalGroups) }}</div>
                    <div class="stat-label">Total Groups</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($machinesWithBrand) }}</div>
                    <div class="stat-label">Mesin dengan Brand</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📋</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($machinesWithModel) }}</div>
                    <div class="stat-label">Mesin dengan Model</div>
                </div>
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
                <div style="font-size: 1.75rem; text-align: center; padding: 0.5rem; color: #667eea;">
                    {{ number_format($machinesWithPM) }}
                </div>
                <div style="text-align: center; color: #666; font-size: 0.8rem;">
                    dari {{ number_format($totalMachines) }} mesin
                </div>
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
        
        <div class="page-content-fill">
        <!-- PM Statistics -->
        <div class="stats-grid">
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📅</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($pmSchedulesThisMonth) }}</div>
                    <div class="stat-label">PM This Month</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">⏳</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($pmSchedulesPending) }}</div>
                    <div class="stat-label">PM Pending</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">⚙️</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($pmSchedulesInProgress) }}</div>
                    <div class="stat-label">PM In Progress</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($pmSchedulesCompleted) }}</div>
                    <div class="stat-label">PM Completed</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($pmCompletionRate, 1) }}%</div>
                    <div class="stat-label">PM Completion Rate</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🔮</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($pdmSchedulesThisMonth) }}</div>
                    <div class="stat-label">PdM This Month</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">⏰</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($pdmSchedulesPending) }}</div>
                    <div class="stat-label">PdM Pending</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">✔️</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($pdmSchedulesCompleted) }}</div>
                    <div class="stat-label">PdM Completed</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📈</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($pdmCompletionRate, 1) }}%</div>
                    <div class="stat-label">PdM Completion Rate</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📋</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($workOrdersTotal) }}</div>
                    <div class="stat-label">Work Orders Total</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📆</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($workOrdersThisMonth) }}</div>
                    <div class="stat-label">WO This Month</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🎯</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($workOrdersCompleted) }}</div>
                    <div class="stat-label">WO Completed</div>
                </div>
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
        
        <div class="page-content-fill">
        <!-- Downtime Statistics -->
        <div class="stats-grid">
            <div class="stat-card fade-in-up">
                <div class="stat-icon">⚠️</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($monthDowntimeCount) }}</div>
                    <div class="stat-label">Total Downtime</div>
                    <div class="stat-unit">incidents</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">⏱️</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($monthDowntime, 0) }}</div>
                    <div class="stat-label">Total Duration</div>
                    <div class="stat-unit">minutes</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($avgDowntimeDuration, 1) }}</div>
                    <div class="stat-label">Avg Duration</div>
                    <div class="stat-unit">min/incident</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📅</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($avgDowntimePerDay, 1) }}</div>
                    <div class="stat-label">Avg per Day</div>
                    <div class="stat-unit">min/day</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🔧</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($monthDowntimeCount > 0 ? $monthDowntimeCount / $daysInMonth : 0, 1) }}</div>
                    <div class="stat-label">Breakdowns/Day</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🏆</div>
                <div class="stat-content">
                    <div class="stat-value" style="font-size: 1.25rem;">{{ $mostProblematicMachine->idMachine ?? 'N/A' }}</div>
                    <div class="stat-label">Top Machine</div>
                    <div class="stat-unit">{{ $mostProblematicMachine ? number_format((float)($mostProblematicMachine->total_duration ?? 0), 0) . ' min' : '' }}</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">⏰</div>
                <div class="stat-content">
                    <div class="stat-value">{{ $longestDowntime ? number_format((float)($longestDowntime->duration ?? 0), 0) : '-' }}</div>
                    <div class="stat-label">Longest DT</div>
                    <div class="stat-unit">minutes</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">👷</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($activeMechanics) }}</div>
                    <div class="stat-label">Active Mechanics</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📍</div>
                <div class="stat-content">
                    <div class="stat-value" style="font-size: 1.25rem;">{{ $topLines->first()->line ?? 'N/A' }}</div>
                    <div class="stat-label">Top Line</div>
                    <div class="stat-unit">{{ $topLines->first() ? number_format((float)($topLines->first()->total_duration ?? 0), 0) . ' min' : '' }}</div>
                </div>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="chart-grid">
            <div class="chart-card">
                <h3 class="chart-title">Top 10 Machine (Downtime)</h3>
                <canvas id="machineDowntimeChart"></canvas>
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
        </div>
    </div>
    
    <!-- PAGE 4: Informasi User / Skill Matrix -->
    <div class="page-container" id="page-3">
        <div class="page-header">
            <div>
                <h1 class="page-title">Informasi User / Skill Matrix</h1>
                <p class="page-subtitle">Kemampuan mekanik per tipe mesin berdasarkan riwayat perbaikan</p>
            </div>
            <div class="datetime-display">
                <div id="current-datetime-3"></div>
                <div>{{ \Carbon\Carbon::create($filterYear, $filterMonth, 1)->locale('id')->translatedFormat('F Y') }}</div>
                @if(Auth::check() && Auth::user()->isAdmin())
                <div style="margin-top: 0.5rem;">
                    <a href="{{ route('mechanic_performance.index', ['month' => $filterMonth, 'year' => $filterYear]) }}" class="text-xs text-blue-600 hover:text-blue-800 font-semibold underline">📊 Detail Kinerja Mekanik</a>
                </div>
                @endif
            </div>
        </div>
        
        <div class="page-content-fill">
        <div class="stats-grid">
            <div class="stat-card fade-in-up">
                <div class="stat-icon">👷</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($mechanicStats->count()) }}</div>
                    <div class="stat-label">Mekanik Aktif</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🔧</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($mechanicStats->sum('total_repairs')) }}</div>
                    <div class="stat-label">Total Perbaikan</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">⏱️</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($mechanicStats->sum('total_duration'), 0) }}</div>
                    <div class="stat-label">Total Durasi (min)</div>
                </div>
            </div>
        </div>
        
        <!-- MTTR & MTBF Charts -->
        <div class="chart-grid">
            <div class="chart-card">
                <h3 class="chart-title">Top 10 MTTR (Highest)</h3>
                <canvas id="mttrChart"></canvas>
            </div>
            <div class="chart-card">
                <h3 class="chart-title">Top 10 MTBF (Highest)</h3>
                <canvas id="mtbfChart"></canvas>
            </div>
        </div>
        
        <div class="chart-grid" style="grid-template-columns: 1fr;">
            <div class="data-table" style="max-height: none;">
                <h3 class="chart-title">Skill Matrix per Mekanik</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Mekanik</th>
                            <th>Tipe Mesin</th>
                            <th>Level</th>
                            <th>Perbaikan</th>
                            <th>Rata-rata (min)</th>
                            <th>ID Mesin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mechanicStats as $stat)
                            @php $skills = $skillMatrix->get($stat->idMekanik) ?? collect(); @endphp
                            @forelse($skills as $idx => $sm)
                                @php
                                    $repairCount = $sm->repair_count ?? 0;
                                    $skillLabel = $repairCount >= 20 ? 'Expert' : ($repairCount >= 10 ? 'Advance' : ($repairCount >= 5 ? 'Intermediate' : 'Beginner'));
                                    $skillClass = $repairCount >= 20 ? 'bg-green-100 text-green-800' : ($repairCount >= 10 ? 'bg-blue-100 text-blue-800' : ($repairCount >= 5 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800'));
                                @endphp
                                <tr>
                                    @if($idx === 0)
                                    <td rowspan="{{ $skills->count() }}" style="vertical-align: top; font-weight: 600;">{{ $stat->nameMekanik }}<br><span class="text-xs text-gray-500">({{ $stat->idMekanik }})</span></td>
                                    @endif
                                    <td>{{ $sm->typeMachine ?? '-' }}</td>
                                    <td><span class="px-2 py-0.5 rounded text-xs font-semibold {{ $skillClass }}">{{ $skillLabel }}</span></td>
                                    <td>{{ $repairCount }}x</td>
                                    <td>{{ number_format($sm->avg_duration ?? 0, 1) }}</td>
                                    <td style="font-size: 0.65rem;">{{ isset($sm->machines_list) && count($sm->machines_list) > 0 ? implode(', ', array_slice($sm->machines_list, 0, 5)) . (count($sm->machines_list) > 5 ? '...' : '') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td>{{ $stat->nameMekanik }} ({{ $stat->idMekanik }})</td>
                                    <td colspan="5" style="text-align: center; color: #999;">Belum ada data skill</td>
                                </tr>
                            @endforelse
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #999;">Tidak ada data mekanik untuk periode ini</td>
                        </tr>
                        @endforelse
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
                <p class="page-subtitle">Informasi inventory spareparts dan standards</p>
            </div>
            <div class="datetime-display">
                <div id="current-datetime-4"></div>
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
        
        <div class="page-content-fill">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📦</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($totalSpareparts) }}</div>
                    <div class="stat-label">Total Spareparts</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🔴</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($lowStockSpareparts) }}</div>
                    <div class="stat-label">Low Stock Alert</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">💰</div>
                <div class="stat-content">
                    <div class="stat-value" style="font-size: 1.5rem;">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</div>
                    <div class="stat-label">Total Stock Value</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">📋</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($totalStandards) }}</div>
                    <div class="stat-label">Total Standards</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($activeStandards) }}</div>
                    <div class="stat-label">Active Standards</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🚨</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($redStatusCount) }}</div>
                    <div class="stat-label">Red Status</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">⚠️</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($redStatusThisMonth) }}</div>
                    <div class="stat-label">Red This Month</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">❌</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($uniqueProblems) }}</div>
                    <div class="stat-label">Unique Problems</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">❓</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($uniqueReasons) }}</div>
                    <div class="stat-label">Unique Reasons</div>
                </div>
            </div>
            <div class="stat-card fade-in-up">
                <div class="stat-icon">🔧</div>
                <div class="stat-content">
                    <div class="stat-value">{{ number_format($uniqueActions) }}</div>
                    <div class="stat-label">Unique Actions</div>
                </div>
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
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Chart instances storage - MUST be declared before any functions that use them
    const chartInstances = {};
    const chartInitialized = {};
    
    // Auto-rotate pages (rotasi otomatis setiap 25 detik)
    const PAGE_ROTATE_INTERVAL = 25000;
    let currentPage = 0;
    const totalPages = 5;
    let rotateTimer = null;
    
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
        } else if (pageIndex === 2) {  // Informasi Downtime
            // Destroy existing charts from other pages
            Object.keys(chartInstances).forEach(chartId => {
                if (!chartId.includes('Downtime') && !chartId.includes('plant') && !chartId.includes('trend') && !chartId.includes('problems')) {
                    destroyChart(chartId);
                }
            });
            // Reset all downtime chart flags
            chartInitialized['downtimeCharts'] = false;
            chartInitialized['machineDowntimeChart'] = false;
            chartInitialized['plantDowntimeChart'] = false;
            chartInitialized['downtimeTrendChart'] = false;
            chartInitialized['problemsChart'] = false;
            setTimeout(() => {
                initializeDowntimeCharts();
            }, 300);
        } else if (pageIndex === 3) {  // Skill Matrix
            // Destroy existing charts from other pages
            Object.keys(chartInstances).forEach(chartId => {
                if (!chartId.includes('mttr') && !chartId.includes('mtbf')) {
                    destroyChart(chartId);
                }
            });
            // Reset MTTR/MTBF chart flags
            chartInitialized['mttrChart'] = false;
            chartInitialized['mtbfChart'] = false;
            setTimeout(() => {
                initializeSkillMatrixCharts();
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
        stopAutoRotate();
        rotateTimer = setInterval(function() {
            nextPage();
        }, PAGE_ROTATE_INTERVAL);
    }
    
    function stopAutoRotate() {
        if (rotateTimer) {
            clearInterval(rotateTimer);
            rotateTimer = null;
        }
    }
    
    // Page dot click: pindah halaman lalu jalankan lagi rotasi
    document.querySelectorAll('.page-dot').forEach(function(dot, index) {
        dot.addEventListener('click', function() {
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
    
    // Initialize: tampilkan halaman 0, jalankan rotasi otomatis, update jam
    showPage(0);
    updateDateTime();
    setInterval(updateDateTime, 1000);
    // Mulai rotasi setelah chart init agar tidak ganggu
    setTimeout(function() {
        startAutoRotate();
    }, 2000);
    
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
        console.log('Initializing downtime charts...');
        
        // Wait for page to be fully visible
        setTimeout(() => {
            console.log('Checking downtime charts visibility...');
            // Top 10 Machine Downtime Chart
            const machineCtx = document.getElementById('machineDowntimeChart');
            console.log('Machine chart context:', machineCtx, 'Visible:', machineCtx ? isElementVisible(machineCtx) : false);
            if (machineCtx) {
                destroyChart('machineDowntimeChart');
                const machineData = @json($topMachines ?? []);
                console.log('Machine data:', machineData);
                if (machineData && machineData.length > 0) {
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
                        maintainAspectRatio: true,
                        aspectRatio: 1.5,
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
                chartInitialized['machineDowntimeChart'] = true;
                } else {
                    // Show message if no data
                    machineCtx.parentElement.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666; font-size: 1.2rem;">Tidak ada data downtime mesin</div>';
                }
            }
            
            // Plant Downtime Chart
            const plantCtx = document.getElementById('plantDowntimeChart');
            if (plantCtx) {
                destroyChart('plantDowntimeChart');
                const plantData = @json($topPlants ?? []);
                console.log('Plant data:', plantData);
                if (plantData && plantData.length > 0) {
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
                        maintainAspectRatio: true,
                        aspectRatio: 1.8,
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
                chartInitialized['plantDowntimeChart'] = true;
                } else {
                    // Show message if no data
                    plantCtx.parentElement.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666; font-size: 1.2rem;">Tidak ada data downtime plant</div>';
                }
            }
            
            // Downtime Trend Chart
            const trendCtx = document.getElementById('downtimeTrendChart');
            if (trendCtx) {
                destroyChart('downtimeTrendChart');
                const trendData = @json($downtimeTrend ?? []);
                console.log('Trend data:', trendData);
                if (trendData && trendData.length > 0) {
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
                        maintainAspectRatio: true,
                        aspectRatio: 1.2,
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
                chartInitialized['downtimeTrendChart'] = true;
                } else {
                    // Show message if no data
                    trendCtx.parentElement.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666; font-size: 1.2rem;">Tidak ada data trend downtime</div>';
                }
            }
            
            // Problems Chart
            const problemsCtx = document.getElementById('problemsChart');
            if (problemsCtx) {
                destroyChart('problemsChart');
                const problemsData = @json($topProblems ?? []);
                console.log('Problems data:', problemsData);
                if (problemsData && problemsData.length > 0) {
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
                        maintainAspectRatio: true,
                        aspectRatio: 1.2,
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
                chartInitialized['problemsChart'] = true;
                } else {
                    // Show message if no data
                    problemsCtx.parentElement.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666; font-size: 1.2rem;">Tidak ada data problem</div>';
                }
            }
            
            chartInitialized['downtimeCharts'] = true;
            console.log('Downtime charts initialization completed');
        }, 500); // Wait 500ms for page transition to complete
    }
    
    // Initialize MTTR and MTBF charts for Skill Matrix page
    function initializeSkillMatrixCharts() {
        console.log('Initializing Skill Matrix charts (MTTR/MTBF)...');
        
        setTimeout(() => {
            // MTTR Chart
            const mttrCtx = document.getElementById('mttrChart');
            if (mttrCtx) {
                destroyChart('mttrChart');
                const mttrData = @json($topMTTR ?? []);
                console.log('MTTR data for Skill Matrix:', mttrData);
                if (mttrData && mttrData.length > 0) {
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
                            maintainAspectRatio: true,
                            aspectRatio: 1.8,
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
                                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                                },
                                y: {
                                    grid: { display: false },
                                    ticks: { font: { size: 11 } }
                                }
                            },
                            animation: {
                                duration: 1500,
                                easing: 'easeOutQuart'
                            }
                        }
                    });
                    chartInitialized['mttrChart'] = true;
                    console.log('MTTR chart initialized successfully');
                } else {
                    mttrCtx.parentElement.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666; font-size: 1.2rem;">Tidak ada data MTTR</div>';
                }
            }
            
            // MTBF Chart
            const mtbfCtx = document.getElementById('mtbfChart');
            if (mtbfCtx) {
                destroyChart('mtbfChart');
                const mtbfData = @json($topMTBF ?? []);
                console.log('MTBF data for Skill Matrix:', mtbfData);
                if (mtbfData && mtbfData.length > 0) {
                    const labels = mtbfData.map(m => m.idMachine || 'N/A');
                    const mtbfValues = mtbfData.map(m => parseFloat(m.mtbf) || 0);
                    
                    chartInstances['mtbfChart'] = new Chart(mtbfCtx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'MTBF (minutes)',
                                data: mtbfValues,
                                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                borderColor: 'rgba(34, 197, 94, 1)',
                                borderWidth: 2,
                                borderRadius: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            aspectRatio: 1.8,
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
                                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                                },
                                y: {
                                    grid: { display: false },
                                    ticks: { font: { size: 11 } }
                                }
                            },
                            animation: {
                                duration: 1500,
                                easing: 'easeOutQuart'
                            }
                        }
                    });
                    chartInitialized['mtbfChart'] = true;
                    console.log('MTBF chart initialized successfully');
                } else {
                    mtbfCtx.parentElement.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666; font-size: 1.2rem;">Tidak ada data MTBF</div>';
                }
            }
            
            console.log('Skill Matrix charts initialization completed');
        }, 500);
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
                
                // Get data from PHP
                const machinesWithBrand = {{ $machinesWithBrand ?? 0 }};
                const machinesWithModel = {{ $machinesWithModel ?? 0 }};
                const machinesWithType = {{ $machinesWithType ?? 0 }};
                const totalMachines = {{ $totalMachines ?? 0 }};
                
                // Calculate machines without these attributes
                const machinesWithoutBrand = totalMachines - machinesWithBrand;
                const machinesWithoutModel = totalMachines - machinesWithModel;
                const machinesWithoutType = totalMachines - machinesWithType;
                
                // Only create chart if there's data
                if (totalMachines > 0) {
                    chartInstances['machineDistributionChart'] = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Dengan Brand', 'Tanpa Brand', 'Dengan Model', 'Tanpa Model', 'Dengan Type', 'Tanpa Type'],
                        datasets: [{
                            data: [
                                machinesWithBrand,
                                machinesWithoutBrand,
                                machinesWithModel,
                                machinesWithoutModel,
                                machinesWithType,
                                machinesWithoutType
                            ],
                            backgroundColor: [
                                'rgba(102, 126, 234, 0.8)',  // With Brand - Blue
                                'rgba(200, 200, 200, 0.5)',  // Without Brand - Gray
                                'rgba(118, 75, 162, 0.8)',   // With Model - Purple
                                'rgba(200, 200, 200, 0.5)',  // Without Model - Gray
                                'rgba(245, 87, 108, 0.8)',   // With Type - Red
                                'rgba(200, 200, 200, 0.5)'   // Without Type - Gray
                            ],
                            borderColor: [
                                'rgba(102, 126, 234, 1)',
                                'rgba(200, 200, 200, 1)',
                                'rgba(118, 75, 162, 1)',
                                'rgba(200, 200, 200, 1)',
                                'rgba(245, 87, 108, 1)',
                                'rgba(200, 200, 200, 1)'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        aspectRatio: 1.2,
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
                } else {
                    // Show message if no data
                    ctx.parentElement.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #666; font-size: 1.2rem;">Tidak ada data mesin</div>';
                }
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
    function initializeChartsOnLoad() {
        setTimeout(() => {
            const page0 = document.getElementById('page-0');
            const page2 = document.getElementById('page-2');
            console.log('Initializing charts on load. Page 0 active:', page0 && page0.classList.contains('active'), 'Page 2 active:', page2 && page2.classList.contains('active'));
            if (page0 && page0.classList.contains('active')) {
                initializeMachineDistributionChart();
            }
            if (page2 && page2.classList.contains('active')) {
                initializeDowntimeCharts();
            }
        }, 800);
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeChartsOnLoad);
    } else {
        initializeChartsOnLoad();
    }
</script>
@endsection
