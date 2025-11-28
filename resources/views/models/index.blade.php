@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Models</h1>
            <div class="flex items-center gap-3">
                <form action="{{ route('models.import-from-machine-erp') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center" onclick="return confirm('Import models dari tabel machine_erp? Ini akan membuat models baru dari model_name yang ada.')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        Import dari Machine ERP
                    </button>
                </form>
                <form action="{{ route('models.merge-duplicates') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center" onclick="return confirm('Merge duplicate models? Ini akan menggabungkan models yang sama (case-insensitive) dan menghapus duplikatnya.')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                        Merge Duplikat
                    </button>
                </form>
                <a href="{{ route('models.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Create
                </a>
            </div>
        </div>
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">No</th>
                        <!-- <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ID</th> -->
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Model Machine</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Type Machine</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Brand Machine</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($models as $model)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration + ($models->currentPage() - 1) * $models->perPage() }}</td>
                        <!-- <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900">{{ $model->id }}</td> -->
                        <td class="px-3 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $model->name }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">{{ $model->machineType->name ?? '-' }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">{{ $model->brand->name ?? '-' }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-center">
                            <div class="flex flex-row justify-center items-center gap-2">
                                <a href="{{ route('models.edit', $model->id) }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <form action="{{ route('models.destroy', $model->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Delete" onclick="return confirm('Delete this model?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No models found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($models->hasPages())
                <div class="mt-4">
                    {{ $models->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
