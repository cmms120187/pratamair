@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Activity</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('activities.download') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download Excel
                </a>
                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Upload Excel
                </button>
                <a href="{{ route('activities.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
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
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 50px;">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 80px;">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 80px;">Kode Room</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 80px;">Plant</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 80px;">Room Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 50px;">Start</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 50px;">Stop</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 50px;">Duration (mm)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 250px;">Description</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">Nama Mekanik</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($activities as $activity)
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration + ($activities->currentPage() - 1) * $activities->perPage() }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $activity->date }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $activity->kode_room ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $activity->plant }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $activity->room_name }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $activity->start }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $activity->stop }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $activity->duration ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500" style="word-wrap: break-word; overflow-wrap: break-word; max-width: 200px;">{{ $activity->description ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $activity->nama_mekanik }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                            <div class="flex flex-row justify-center items-center gap-2">
                                <a href="{{ route('activities.show', $activity->id) }}" class="inline-flex items-center justify-center bg-green-600 hover:bg-green-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="View">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                                <a href="{{ route('activities.edit', $activity->id) }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <form action="{{ route('activities.destroy', $activity->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Delete" onclick="return confirm('Delete this activity?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-sm text-gray-500">No activity found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($activities->hasPages())
                <div class="mt-4">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black bg-opacity-50" onclick="document.getElementById('uploadModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800">Upload Excel File</h3>
                <button onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form action="{{ route('activities.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Excel File (.xlsx, .xls)</label>
                    <input type="file" name="excel_file" accept=".xlsx,.xls" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-2 text-xs text-gray-500">Format Excel: Kolom pertama harus header (date, kode_room [opsional], plant, process, line, room_name, start, stop, duration, description, remarks, id_mekanik, nama_mekanik). Jika kode_room diisi, akan auto-fill plant, process, line, dan room_name dari RoomERP.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition">Upload</button>
                    <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="text-gray-600 hover:text-gray-800">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

