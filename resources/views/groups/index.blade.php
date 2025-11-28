@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Groups</h1>
            <a href="{{ route('groups.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Create
            </a>
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Name Group</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Systems</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Created At</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Updated At</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($groups as $group)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration + ($groups->currentPage() - 1) * $groups->perPage() }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $group->id }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $group->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            @if($group->systems->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach($group->systems as $system)
                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $system->nama_sistem }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400 italic">No systems</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $group->created_at ? $group->created_at->format('Y-m-d H:i') : '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $group->updated_at ? $group->updated_at->format('Y-m-d H:i') : '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                            <div class="flex flex-row justify-center items-center gap-2">
                                <a href="{{ route('groups.edit', $group->id) }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <form action="{{ route('groups.destroy', $group->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Delete" onclick="return confirm('Delete this group?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">No groups found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($groups->hasPages())
                <div class="mt-4">
                    {{ $groups->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
