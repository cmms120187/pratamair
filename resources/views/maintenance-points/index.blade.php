@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Maintenance Points</h1>
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
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ID</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Type Machine</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Total Points</th>
                        <th class="px-3 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($machineTypes as $machineType)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration + ($machineTypes->currentPage() - 1) * $machineTypes->perPage() }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-900">{{ $machineType->id }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $machineType->name }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">{{ $machineType->maintenance_points_count ?? 0 }}</td>
                        <td class="px-3 py-3 whitespace-nowrap text-sm text-center">
                            <a href="{{ route('maintenance-points.manage', $machineType->id) }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition duration-150 ease-in-out" title="Manage Points">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Manage Points
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">No machine types found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($machineTypes->hasPages())
                <div class="mt-4">
                    {{ $machineTypes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

