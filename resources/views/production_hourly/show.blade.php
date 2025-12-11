@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">Detail Hasil Produksi Per Jam</h1>
                    <p class="text-sm text-gray-600">
                        Line: <span class="font-semibold">{{ $productionHourly->first()->line->name ?? '-' }}</span> | 
                        Process: <span class="font-semibold">{{ $productionHourly->first()->process->name ?? '-' }}</span> | 
                        Tanggal: <span class="font-semibold">{{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</span>
                    </p>
                </div>
                <a href="{{ route('production-hourly.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded shadow transition">
                    Kembali
                </a>
            </div>
        </div>

        @if(request('edit'))
            <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                <p class="font-semibold">Mode Edit</p>
                <p class="text-sm">Klik pada jam yang ingin diedit untuk mengubah data produksi.</p>
            </div>
        @endif

        <!-- Summary Card -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <p class="text-sm text-blue-600 font-medium">Total Produksi</p>
                <p class="text-2xl font-bold text-blue-800">{{ number_format($totalProduction, 0, ',', '.') }}</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                <p class="text-sm text-green-600 font-medium">Grade A</p>
                <p class="text-2xl font-bold text-green-800">{{ number_format($gradeA, 0, ',', '.') }}</p>
            </div>
            <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                <p class="text-sm text-orange-600 font-medium">Grade B (Defect)</p>
                <p class="text-2xl font-bold text-orange-800">{{ number_format($gradeB, 0, ',', '.') }}</p>
            </div>
            <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                <p class="text-sm text-red-600 font-medium">Grade C (Defect)</p>
                <p class="text-2xl font-bold text-red-800">{{ number_format($gradeC, 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Hours Table -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Detail Produksi Per Jam</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                    <thead class="bg-blue-600">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-white uppercase tracking-wider border-r border-blue-500">Jam</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-white uppercase tracking-wider border-r border-blue-500">Target per Jam</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-white uppercase tracking-wider border-r border-blue-500">Grade A per Jam</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-white uppercase tracking-wider bg-blue-100 text-blue-800">Total Produksi per Jam</th>
                            <th class="px-3 py-2 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($productionHourly as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-2 whitespace-nowrap text-sm font-semibold text-gray-900 border-r border-gray-200">
                                {{ str_pad($item->hour, 2, '0', STR_PAD_LEFT) }}:00
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 text-right border-r border-gray-200">
                                {{ $item->target_per_hour ? number_format($item->target_per_hour, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-green-600 text-right font-semibold border-r border-gray-200">
                                @if($item->total_production === '(istirahat)')
                                    <span class="text-gray-500 italic">(istirahat)</span>
                                @else
                                    {{ number_format($item->total_production, 0, ',', '.') }}
                                @endif
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-blue-600 text-right font-semibold">
                                @php
                                    $itemProduction = $item->total_production;
                                    // Total Produksi per jam = Grade A per jam + (Grade B + Grade C) / jumlah jam
                                    // Tapi karena Grade B dan C adalah total per hari, kita hitung proporsional
                                    if ($itemProduction === '(istirahat)' || !is_numeric($itemProduction)) {
                                        $itemTotalProduction = '-';
                                    } else {
                                        $itemGradeA = (int)$itemProduction;
                                        // Hitung proporsi Grade B dan C per jam (dibagi jumlah jam yang valid)
                                        $validHoursCount = $productionHourly->where('total_production', '!=', '(istirahat)')->where(function($item) {
                                            return is_numeric($item->total_production);
                                        })->count();
                                        $gradeBPerHour = $validHoursCount > 0 ? ($gradeB / $validHoursCount) : 0;
                                        $gradeCPerHour = $validHoursCount > 0 ? ($gradeC / $validHoursCount) : 0;
                                        $itemTotalProduction = $itemGradeA + $gradeBPerHour + $gradeCPerHour;
                                    }
                                @endphp
                                {{ is_numeric($itemTotalProduction) ? number_format(round($itemTotalProduction), 0, ',', '.') : $itemTotalProduction }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-sm text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('production-hourly.edit', $item->id) }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-1.5 rounded shadow transition" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </a>
                                    <form action="{{ route('production-hourly.destroy', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white p-1.5 rounded shadow transition" title="Delete" onclick="return confirm('Hapus data produksi jam {{ str_pad($item->hour, 2, '0', STR_PAD_LEFT) }}:00?')">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Daily Grades Info -->
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Total Grade Per Hari</h4>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Grade B (Defect): <span class="font-semibold text-orange-600">{{ number_format($gradeB, 0, ',', '.') }}</span></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Grade C (Defect): <span class="font-semibold text-red-600">{{ number_format($gradeC, 0, ',', '.') }}</span></p>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Catatan: Grade B dan Grade C adalah total untuk seluruh hari, bukan per jam</p>
        </div>
    </div>
</div>
@endsection

