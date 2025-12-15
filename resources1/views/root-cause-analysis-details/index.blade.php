@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Root Cause Analysis Detail</h1>
            <a href="{{ route('root-cause-analysis-details.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Buat Analisis RCA
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form method="GET" action="{{ route('root-cause-analysis-details.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                    <select name="month" id="month" class="w-full border rounded px-3 py-2 text-sm" onchange="this.form.submit()">
                        <option value="all" {{ request('month', 'all') == 'all' ? 'selected' : '' }}>All Bulan</option>
                        @foreach($months as $m)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->locale('id')->isoFormat('MMMM') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                    <select name="year" id="year" class="w-full border rounded px-3 py-2 text-sm" onchange="this.form.submit()">
                        <option value="all" {{ request('year', 'all') == 'all' ? 'selected' : '' }}>All Tahun</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="plant_id" class="block text-sm font-medium text-gray-700 mb-2">Plant</label>
                    <select name="plant_id" id="plant_id" class="w-full border rounded px-3 py-2 text-sm" onchange="this.form.submit()">
                        <option value="all" {{ request('plant_id', 'all') == 'all' ? 'selected' : '' }}>All Plant</option>
                        @foreach($plants as $plant)
                            <option value="{{ $plant->id }}" {{ request('plant_id') == $plant->id ? 'selected' : '' }}>
                                {{ $plant->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <a href="{{ route('root-cause-analysis-details.index') }}" class="w-full px-4 py-2 border border-red-300 rounded text-red-700 hover:bg-red-50 text-sm font-semibold text-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        @if($rcaRecords->count() > 0)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mesin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Analisis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($rcaRecords as $rca)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $rca->title }}</div>
                            <div class="text-sm text-gray-500 truncate max-w-md">{{ Str::limit($rca->problem_description, 50) }}</div>
                        </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $rca->getMethodLabel() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $rca->machine->idMachine ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $rca->machine->room->plant->name ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $rca->analysis_date->format('d M Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $rca->getStatusBadgeClass() }}">
                                {{ $rca->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <a href="{{ route('root-cause-analysis-details.show', $rca->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                            <a href="{{ route('root-cause-analysis-details.edit', $rca->id) }}" class="text-green-600 hover:text-green-900 mr-3">Edit</a>
                            <form action="{{ route('root-cause-analysis-details.destroy', $rca->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $rcaRecords->links() }}
        </div>
        @else
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <p class="text-gray-500 mb-4">Belum ada analisis RCA detail.</p>
            <a href="{{ route('root-cause-analysis-details.create') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                Buat Analisis RCA Pertama
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
