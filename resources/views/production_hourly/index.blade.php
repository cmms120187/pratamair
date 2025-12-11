@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Hasil Produksi Per Jam</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('production-hourly.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Create Single
                </a>
                <a href="{{ route('production-hourly.create-bulk') }}" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Create Multiple Hours
                </a>
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
            <form method="GET" action="{{ route('production-hourly.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                    <input type="date" name="date" id="date" value="{{ request('date') }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="line_id" class="block text-sm font-medium text-gray-700 mb-2">Line</label>
                    <select name="line_id" id="line_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Semua Line --</option>
                        @foreach($lines as $line)
                            <option value="{{ $line->id }}" {{ request('line_id') == $line->id ? 'selected' : '' }}>
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
                            <option value="{{ $process->id }}" {{ request('process_id') == $process->id ? 'selected' : '' }}>
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

        <div class="bg-white rounded-lg shadow p-4 sm:p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Line</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Process</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Target Produksi</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Total Produksi</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Grade A</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Grade B</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Grade C</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Jumlah Jam</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($productionHourly as $data)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration + ($productionHourly->currentPage() - 1) * $productionHourly->perPage() }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $data['production_date']->format('Y-m-d') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $data['line']->name ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $data['process']->name ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-blue-600 text-right font-semibold">{{ number_format($data['target_production'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right font-semibold">{{ number_format($data['total_production'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-green-600 text-right font-semibold">{{ number_format($data['grade_a'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-orange-600 text-right">{{ number_format($data['grade_b'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-red-600 text-right">{{ number_format($data['grade_c'], 0, ',', '.') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center">{{ $data['hour_count'] }} jam</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                            <div class="flex flex-row justify-center items-center gap-2">
                                <a href="{{ route('production-hourly.show-detail', [$data['line_id'], $data['process_id'], $data['production_date']->format('Y-m-d')]) }}" class="inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="View Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                                <a href="{{ route('production-hourly.show-detail', [$data['line_id'], $data['process_id'], $data['production_date']->format('Y-m-d')]) }}?edit=1" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada data produksi ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($productionHourly->hasPages())
                <div class="mt-4">
                    {{ $productionHourly->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

