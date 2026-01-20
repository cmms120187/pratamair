@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-4xl">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Group</h1>
                <p class="text-sm text-gray-500 mt-1">Update group information</p>
            </div>
            <a href="{{ route('groups.index') }}" class="text-gray-600 hover:text-gray-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
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

        <form action="{{ route('groups.update', $group->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Group Information</h2>
                
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Group Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="name" 
                        id="name" 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition @error('name') border-red-500 @enderror" 
                        value="{{ old('name', $group->name) }}" 
                        placeholder="e.g., Compressing, Packaging, Production"
                        required
                        autofocus
                    >
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Enter a descriptive name for this group. Groups help organize machine types by function or area.</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Systems
                    </label>
                    <div class="border border-gray-300 rounded-lg p-4 max-h-96 overflow-y-auto bg-gray-50 @error('systems') border-red-500 @enderror">
                        @if($systems->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($systems as $system)
                                    <label class="flex items-center p-3 bg-white rounded-lg border border-gray-200 hover:bg-blue-50 hover:border-blue-300 cursor-pointer transition">
                                        <input 
                                            type="checkbox" 
                                            name="systems[]" 
                                            value="{{ $system->id }}"
                                            {{ in_array($system->id, old('systems', $group->systems->pluck('id')->toArray())) ? 'checked' : '' }}
                                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                        >
                                        <span class="ml-3 text-sm font-medium text-gray-700">{{ $system->nama_sistem }}</span>
                                        @if($system->deskripsi)
                                            <span class="ml-2 text-xs text-gray-500">({{ Str::limit($system->deskripsi, 30) }})</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-4">No systems available. Please create systems first.</p>
                        @endif
                    </div>
                    @error('systems')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-500">
                        <strong>Tip:</strong> Select one or more systems by checking the boxes. Systems selected here will be automatically assigned to machine types in this group.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Group Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Group ID:</span>
                            <span class="font-semibold text-gray-900 ml-2">{{ $group->id }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Created At:</span>
                            <span class="font-semibold text-gray-900 ml-2">
                                {{ $group->created_at ? $group->created_at->format('Y-m-d H:i:s') : '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500">Updated At:</span>
                            <span class="font-semibold text-gray-900 ml-2">
                                {{ $group->updated_at ? $group->updated_at->format('Y-m-d H:i:s') : '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500">Selected Systems:</span>
                            <span class="font-semibold text-gray-900 ml-2">{{ $group->systems->count() }} systems</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a 
                    href="{{ route('groups.index') }}" 
                    class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg shadow transition duration-150 ease-in-out flex items-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancel
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow transition duration-150 ease-in-out flex items-center"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Group
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function selectAllSystems() {
    const checkboxes = document.querySelectorAll('.system-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAllSystems() {
    const checkboxes = document.querySelectorAll('.system-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
}
</script>
@endsection
