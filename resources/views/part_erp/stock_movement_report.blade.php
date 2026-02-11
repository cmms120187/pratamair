@extends('layouts.app')
@section('content')
@php
    $baseParams = request()->only(['part_erp_id', 'type', 'reference_type']);
    $today = now()->format('Y-m-d');
    $weekStart = now()->startOfWeek()->format('Y-m-d');
    $weekEnd = now()->endOfWeek()->format('Y-m-d');
    $monthStart = now()->startOfMonth()->format('Y-m-d');
    $monthEnd = now()->format('Y-m-d');
@endphp
<div class="w-full p-4 sm:p-6 lg:p-8" id="report-content">
    <div class="w-full mx-auto">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Laporan Penggunaan Part</h1>
            <div class="flex flex-wrap items-center gap-2 print:hidden">
                <a href="{{ route('part-erp.stock-movement-report.export', array_merge($baseParams, ['date_from' => request('date_from', $dateFrom ?? ''), 'date_to' => request('date_to', $dateTo ?? '')])) }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Export Excel
                </a>
                <button type="button" onclick="window.print()" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    Cetak
                </button>
                <a href="{{ route('part-erp.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center text-sm">
                    Back to Part ERP
                </a>
            </div>
        </div>
        <p class="text-sm text-gray-600 mb-4">Laporan pergerakan stok part dengan relasi ke Downtime, Preventive Maintenance, Work Order, atau dokumen (MR/PO/MO).</p>

        @if(isset($summary))
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg flex flex-wrap gap-6 print:block">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-700">Total Masuk:</span>
                <span class="text-lg font-bold text-green-700">{{ number_format($summary['total_masuk']) }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-700">Total Keluar:</span>
                <span class="text-lg font-bold text-amber-700">{{ number_format($summary['total_keluar']) }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-700">Net:</span>
                <span class="text-lg font-bold {{ ($summary['total_masuk'] - $summary['total_keluar']) >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ number_format($summary['total_masuk'] - $summary['total_keluar']) }}</span>
            </div>
        </div>
        @endif

        <form method="GET" action="{{ route('part-erp.stock-movement-report') }}" class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200 print:hidden">
            <div class="flex flex-wrap items-end gap-3 mb-3">
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="text-xs font-medium text-gray-600">Cepat:</span>
                    <a href="{{ route('part-erp.stock-movement-report', array_merge($baseParams, ['date_from' => $today, 'date_to' => $today])) }}" class="text-xs px-2 py-1 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">Hari ini</a>
                    <a href="{{ route('part-erp.stock-movement-report', array_merge($baseParams, ['date_from' => $weekStart, 'date_to' => $weekEnd])) }}" class="text-xs px-2 py-1 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">Minggu ini</a>
                    <a href="{{ route('part-erp.stock-movement-report', array_merge($baseParams, ['date_from' => $monthStart, 'date_to' => $monthEnd])) }}" class="text-xs px-2 py-1 rounded bg-gray-200 hover:bg-gray-300 text-gray-700">Bulan ini</a>
                </div>
            </div>
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Dari</label>
                    <input type="date" name="date_from" value="{{ request('date_from', $dateFrom ?? '') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Sampai</label>
                    <input type="date" name="date_to" value="{{ request('date_to', $dateTo ?? '') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Part</label>
                    <select name="part_erp_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Semua Part</option>
                        @foreach($parts as $p)
                            <option value="{{ $p->id }}" {{ request('part_erp_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->part_number }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tipe</label>
                    <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Keluar</option>
                        <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Masuk</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sumber/Relasi</label>
                    <select name="reference_type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        <option value="manual" {{ request('reference_type') === 'manual' ? 'selected' : '' }}>Manual (MR/PO/MO)</option>
                        <option value="downtime_erp2" {{ request('reference_type') === 'downtime_erp2' ? 'selected' : '' }}>Downtime ERP2</option>
                        <option value="downtime_erp" {{ request('reference_type') === 'downtime_erp' ? 'selected' : '' }}>Downtime ERP</option>
                        <option value="preventive_maintenance_execution" {{ request('reference_type') === 'preventive_maintenance_execution' ? 'selected' : '' }}>Preventive Maintenance</option>
                        <option value="work_order" {{ request('reference_type') === 'work_order' ? 'selected' : '' }}>Work Order</option>
                        <option value="other" {{ request('reference_type') === 'other' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2 px-4 rounded-lg">Filter</button>
                <a href="{{ route('part-erp.stock-movement-report') }}" class="text-gray-600 hover:text-gray-800 text-sm py-2">Reset</a>
            </div>
        </form>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-white uppercase">Tanggal</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-white uppercase">Part</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-white uppercase">Tipe</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-white uppercase">Dokumen / Relasi</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-white uppercase">Qty</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-white uppercase">Stok Akhir</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-white uppercase">User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($movements as $m)
                    <tr class="{{ $m->quantity > 0 ? 'bg-green-50' : 'bg-amber-50' }}">
                        <td class="px-3 py-2 text-sm text-gray-700">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2 text-sm">
                            <a href="{{ route('part-erp.show', $m->part_erp_id) }}" class="text-blue-600 hover:underline">{{ $m->partErp->name ?? '-' }}</a>
                            <span class="text-gray-500 text-xs">({{ $m->partErp->part_number ?? '-' }})</span>
                        </td>
                        <td class="px-3 py-2 text-sm">
                            @if($m->quantity > 0)
                                <span class="text-green-700 font-medium">Masuk</span>
                            @else
                                <span class="text-amber-700 font-medium">Keluar</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-sm text-gray-700">
                            @if($m->reference_type && $m->reference_id)
                                @php $ref = $m->getReferenceModel(); @endphp
                                @if($m->reference_type === 'downtime_erp2' && $ref)
                                    <a href="{{ route('downtime-erp2.show', $ref->id) }}" class="text-blue-600 hover:underline">Downtime ERP2 #{{ $ref->id }}</a>
                                    <span class="text-gray-500 text-xs">({{ $ref->date ? $ref->date->format('d/m/Y') : '-' }}, {{ $ref->idMachine ?? '-' }})</span>
                                @elseif($m->reference_type === 'downtime_erp' && $ref)
                                    <a href="{{ route('downtime_erp.show', $ref->id) }}" class="text-blue-600 hover:underline">Downtime ERP #{{ $ref->id }}</a>
                                    <span class="text-gray-500 text-xs">({{ $ref->date ? \Carbon\Carbon::parse($ref->date)->format('d/m/Y') : '-' }})</span>
                                @elseif($m->reference_type === 'preventive_maintenance_execution' && $ref)
                                    <a href="{{ route('preventive-maintenance.updating.index') }}" class="text-blue-600 hover:underline">PM Execution #{{ $ref->id }}</a>
                                    <span class="text-gray-500 text-xs">({{ $ref->scheduled_date ? $ref->scheduled_date->format('d/m/Y') : '-' }})</span>
                                @elseif($m->reference_type === 'work_order' && $ref)
                                    <a href="{{ route('work-orders.show', $ref->id) }}" class="text-blue-600 hover:underline">Work Order #{{ $ref->id }}</a>
                                    <span class="text-gray-500 text-xs">({{ $ref->wo_number ?? '-' }})</span>
                                @else
                                    {{ $m->getReferenceLabel() }}
                                @endif
                            @else
                                {{ $m->document_type ?? '-' }} #{{ $m->document_number ?? '-' }}
                            @endif
                        </td>
                        <td class="px-3 py-2 text-sm text-right font-medium {{ $m->quantity > 0 ? 'text-green-700' : 'text-amber-700' }}">{{ $m->quantity > 0 ? '+' : '' }}{{ $m->quantity }}</td>
                        <td class="px-3 py-2 text-sm text-right text-gray-700">{{ $m->balance_after ?? '-' }}</td>
                        <td class="px-3 py-2 text-sm text-gray-600">{{ $m->user->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada data pergerakan stok.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($movements->hasPages())
                <div class="p-4 print:hidden">{{ $movements->links() }}</div>
            @endif
        </div>
    </div>
</div>

<style>
@media print {
    aside, nav, .print\:hidden, button, [role="navigation"] { display: none !important; }
    body { background: white; }
    .w-full.p-4 { padding: 0.5rem !important; }
}
</style>
@endsection
