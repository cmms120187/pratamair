@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8" x-data="{ filterModalOpen: false }">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Users</h1>
            <div class="flex items-center gap-3">
                <button type="button" @click="filterModalOpen = true" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                    @if(request()->hasAny(['filter_role', 'filter_atasan']))
                        <span class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 rounded-full border-2 border-white"></span>
                    @endif
                </button>
                <a href="{{ route('users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition flex items-center">
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
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif
        
        <!-- Batch Update Section -->
        <div class="bg-white rounded-lg shadow p-4 mb-4" id="batchUpdateSection" style="display: none;">
            <form id="batchUpdateForm" method="POST" action="{{ route('users.batch-update') }}">
                @csrf
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="selectAll" class="text-sm font-medium text-gray-700">Select All</label>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label for="batchField" class="block text-sm font-medium text-gray-700 mb-1">Update Field:</label>
                        <select name="field" id="batchField" class="w-full border rounded px-3 py-2 text-sm" required>
                            <option value="">-- Pilih Field --</option>
                            <option value="role">Jabatan (Role)</option>
                            <option value="atasan_id">Atasan</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]" id="batchValueContainer" style="display: none;">
                        <label for="batchValue" class="block text-sm font-medium text-gray-700 mb-1">New Value:</label>
                        <select name="value" id="batchValue" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">-- Pilih Value --</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 mt-6">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition text-sm">
                            Update Selected
                        </button>
                        <button type="button" onclick="cancelBatchUpdate()" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded shadow transition text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
                <div id="selectedCount" class="mt-2 text-sm text-gray-600"></div>
            </form>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4 sm:p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider" style="width: 50px;">
                            <input type="checkbox" id="selectAllHeader" onchange="toggleSelectAll()" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">NIK</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Jabatan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Atasan</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                    @php
                        // Mapping role ke jabatan
                        $jabatanMap = [
                            'mekanik' => 'Mekanik (Team Member)',
                            'team_leader' => 'Team Leader',
                            'group_leader' => 'Group Leader',
                            'coordinator' => 'Coordinator/Supervisor',
                            'ast_manager' => 'Assistant Manager',
                            'manager' => 'Manager',
                            'general_manager' => 'General Manager'
                        ];
                        $jabatan = $jabatanMap[$user->role] ?? $user->role;
                        
                        // Tentukan atasan dari relasi atau fallback ke hierarki
                        $atasan = '-';
                        if ($user->atasan) {
                            $atasan = $user->atasan->name;
                        } else {
                            // Fallback ke hierarki jika tidak ada relasi
                            if ($user->role === 'mekanik') {
                                $atasanUser = $teamLeaders->first();
                                $atasan = $atasanUser ? $atasanUser->name : '-';
                            } elseif ($user->role === 'team_leader') {
                                $atasanUser = $groupLeaders->first();
                                $atasan = $atasanUser ? $atasanUser->name : '-';
                            } elseif ($user->role === 'group_leader') {
                                $atasanUser = $coordinators->first();
                                $atasan = $atasanUser ? $atasanUser->name : '-';
                            } elseif ($user->role === 'coordinator') {
                                $astManagers = \App\Models\User::where('role', 'ast_manager')->get();
                                $atasanUser = $astManagers->first();
                                $atasan = $atasanUser ? $atasanUser->name : '-';
                            } elseif ($user->role === 'ast_manager') {
                                $managers = \App\Models\User::where('role', 'manager')->get();
                                $atasanUser = $managers->first();
                                $atasan = $atasanUser ? $atasanUser->name : '-';
                            } elseif ($user->role === 'manager') {
                                $generalManagers = \App\Models\User::where('role', 'general_manager')->get();
                                $atasanUser = $generalManagers->first();
                                $atasan = $atasanUser ? $atasanUser->name : '-';
                            }
                        }
                    @endphp
                    <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100 transition-colors">
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" onchange="updateSelectedCount()">
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-mono">{{ $user->nik ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">
                            <a href="{{ route('users.edit', $user->id) }}{{ request()->hasAny(['filter_role', 'filter_atasan', 'page']) ? '?' . http_build_query(request()->only(['filter_role', 'filter_atasan', 'page'])) : '' }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                {{ $user->name }}
                            </a>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $user->email }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 font-medium">
                            <a href="{{ route('users.organizational-structure.index') }}" class="text-blue-600 hover:text-blue-800 hover:underline" title="Lihat di Struktur Organisasi">
                                {{ $jabatan }}
                            </a>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                            @if($user->atasan)
                                <a href="{{ route('users.edit', $user->atasan->id) }}{{ request()->hasAny(['filter_role', 'filter_atasan', 'page']) ? '?' . http_build_query(request()->only(['filter_role', 'filter_atasan', 'page'])) : '' }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ $atasan }}
                                </a>
                            @else
                                {{ $atasan }}
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                            <div class="flex flex-row justify-center items-center gap-2">
                                <a href="{{ route('users.edit', $user->id) }}{{ request()->hasAny(['filter_role', 'filter_atasan', 'page']) ? '?' . http_build_query(request()->only(['filter_role', 'filter_atasan', 'page'])) : '' }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    @if(request('filter_role'))
                                        <input type="hidden" name="filter_role" value="{{ request('filter_role') }}">
                                    @endif
                                    @if(request('filter_atasan'))
                                        <input type="hidden" name="filter_atasan" value="{{ request('filter_atasan') }}">
                                    @endif
                                    <button type="submit" class="inline-flex items-center justify-center bg-red-600 hover:bg-red-700 text-white p-2 rounded shadow transition duration-150 ease-in-out" title="Delete" onclick="return confirm('Delete this user?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($users->hasPages())
                <div class="mt-4">
                    {{ $users->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
    
    <!-- Filter Modal -->
    <div x-show="filterModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="filterModalOpen = false"
         @keydown.escape.window="filterModalOpen = false"
         class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <!-- Modal Container -->
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="filterModalOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <!-- Close Button -->
                <button @click="filterModalOpen = false" 
                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                
                <!-- Modal Header -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filter Users
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Filter berdasarkan Jabatan dan Atasan</p>
                </div>
                
                <!-- Modal Content -->
                <form method="GET" action="{{ route('users.index') }}" class="space-y-4">
                    <!-- Jabatan Filter -->
                    <div>
                        <label for="filter_role" class="block text-sm font-medium text-gray-700 mb-2">Jabatan</label>
                        <select name="filter_role" id="filter_role" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">Semua Jabatan</option>
                            <option value="mekanik" {{ request('filter_role') == 'mekanik' ? 'selected' : '' }}>Mekanik (Team Member)</option>
                            <option value="team_leader" {{ request('filter_role') == 'team_leader' ? 'selected' : '' }}>Team Leader</option>
                            <option value="group_leader" {{ request('filter_role') == 'group_leader' ? 'selected' : '' }}>Group Leader</option>
                            <option value="coordinator" {{ request('filter_role') == 'coordinator' ? 'selected' : '' }}>Coordinator/Supervisor</option>
                            <option value="ast_manager" {{ request('filter_role') == 'ast_manager' ? 'selected' : '' }}>Assistant Manager</option>
                            <option value="manager" {{ request('filter_role') == 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="general_manager" {{ request('filter_role') == 'general_manager' ? 'selected' : '' }}>General Manager</option>
                        </select>
                    </div>
                    
                    <!-- Atasan Filter -->
                    <div>
                        <label for="filter_atasan" class="block text-sm font-medium text-gray-700 mb-2">Atasan</label>
                        <select name="filter_atasan" id="filter_atasan" class="w-full border rounded px-3 py-2 text-sm">
                            <option value="">Semua Atasan</option>
                            @foreach($allUsers as $atasanUser)
                                <option value="{{ $atasanUser->id }}" {{ request('filter_atasan') == $atasanUser->id ? 'selected' : '' }}>
                                    {{ $atasanUser->name }} 
                                    ({{ $atasanUser->role == 'team_leader' ? 'Team Leader' : ($atasanUser->role == 'group_leader' ? 'Group Leader' : ($atasanUser->role == 'coordinator' ? 'Coordinator/Supervisor' : ($atasanUser->role == 'ast_manager' ? 'Assistant Manager' : ($atasanUser->role == 'manager' ? 'Manager' : ($atasanUser->role == 'general_manager' ? 'General Manager' : $atasanUser->role))))) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 mt-6">
                        <button type="button" @click="filterModalOpen = false" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50 text-sm font-semibold transition">
                            Cancel
                        </button>
                        <a href="{{ route('users.index') }}" class="px-4 py-2 border border-red-300 rounded text-red-700 hover:bg-red-50 text-sm font-semibold transition">
                            Reset Filter
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded text-sm transition flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Apply Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Data untuk dropdown batch update
    const roleOptions = {
        'role': [
            { value: 'mekanik', text: 'Mekanik (Team Member)' },
            { value: 'team_leader', text: 'Team Leader' },
            { value: 'group_leader', text: 'Group Leader' },
            { value: 'coordinator', text: 'Coordinator/Supervisor' },
            { value: 'ast_manager', text: 'Assistant Manager' },
            { value: 'manager', text: 'Manager' },
            { value: 'general_manager', text: 'General Manager' }
        ]
    };
    
    const atasanOptions = @json($atasanOptions ?? []);
    
    // Toggle select all
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const selectAllHeader = document.getElementById('selectAllHeader');
        const checkboxes = document.querySelectorAll('.user-checkbox');
        const isChecked = selectAll.checked || selectAllHeader.checked;
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
        });
        
        if (isChecked) {
            selectAll.checked = true;
            selectAllHeader.checked = true;
            document.getElementById('batchUpdateSection').style.display = 'block';
        } else {
            selectAll.checked = false;
            selectAllHeader.checked = false;
        }
        
        updateSelectedCount();
    }
    
    // Update selected count
    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.user-checkbox:checked');
        const count = checkboxes.length;
        const countElement = document.getElementById('selectedCount');
        
        if (count > 0) {
            document.getElementById('batchUpdateSection').style.display = 'block';
            countElement.textContent = count + ' user(s) selected';
        } else {
            document.getElementById('batchUpdateSection').style.display = 'none';
            countElement.textContent = '';
        }
        
        // Sync select all checkboxes
        const allCheckboxes = document.querySelectorAll('.user-checkbox');
        const allChecked = allCheckboxes.length > 0 && Array.from(allCheckboxes).every(cb => cb.checked);
        document.getElementById('selectAll').checked = allChecked;
        document.getElementById('selectAllHeader').checked = allChecked;
    }
    
    // Cancel batch update
    function cancelBatchUpdate() {
        document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('selectAll').checked = false;
        document.getElementById('selectAllHeader').checked = false;
        document.getElementById('batchUpdateSection').style.display = 'none';
        document.getElementById('batchField').value = '';
        document.getElementById('batchValueContainer').style.display = 'none';
        updateSelectedCount();
    }
    
    // Handle field change
    document.addEventListener('DOMContentLoaded', function() {
        const batchField = document.getElementById('batchField');
        const batchValueContainer = document.getElementById('batchValueContainer');
        const batchValue = document.getElementById('batchValue');
        
        batchField.addEventListener('change', function() {
            const field = this.value;
            batchValue.innerHTML = '<option value="">-- Pilih Value --</option>';
            
            if (field === 'role') {
                batchValueContainer.style.display = 'block';
                roleOptions.role.forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option.value;
                    opt.textContent = option.text;
                    batchValue.appendChild(opt);
                });
            } else if (field === 'atasan_id') {
                batchValueContainer.style.display = 'block';
                // Ambil semua atasan yang sesuai
                atasanOptions.forEach(user => {
                    const opt = document.createElement('option');
                    opt.value = user.id;
                    opt.textContent = user.name + ' (' + user.roleDisplay + ')';
                    batchValue.appendChild(opt);
                });
            } else {
                batchValueContainer.style.display = 'none';
            }
        });
        
        // Handle checkbox changes
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCount);
        });
        
        // Handle form submission
        document.getElementById('batchUpdateForm').addEventListener('submit', function(e) {
            const checked = document.querySelectorAll('.user-checkbox:checked');
            if (checked.length === 0) {
                e.preventDefault();
                alert('Silakan pilih minimal satu user.');
                return false;
            }
            
            const field = document.getElementById('batchField').value;
            const value = document.getElementById('batchValue').value;
            
            if (!field || !value) {
                e.preventDefault();
                alert('Silakan pilih field dan value yang akan diupdate.');
                return false;
            }
            
            // Collect selected user IDs
            const userIds = Array.from(checked).map(cb => cb.value);
            
            // Remove existing user_ids inputs if any
            const existingInputs = document.querySelectorAll('#batchUpdateForm input[name="user_ids[]"]');
            existingInputs.forEach(input => input.remove());
            
            // Add user_ids as hidden inputs
            userIds.forEach(userId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'user_ids[]';
                input.value = userId;
                this.appendChild(input);
            });
        });
    });
</script>
@endsection
