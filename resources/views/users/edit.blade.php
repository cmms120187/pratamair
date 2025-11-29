@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto max-w-2xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Edit User</h1>
            <a href="{{ route('users.index', request()->only(['filter_role', 'filter_atasan', 'page'])) }}" class="text-gray-600 hover:text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data" x-data="{ 
                role: '{{ old('role', $user->role) }}',
                atasanId: '{{ old('atasan_id', $user->atasan_id ?? '') }}',
                updateAtasanOptions() {
                    const select = this.$refs.atasanSelect;
                    if (!select) return;
                    
                    // Sembunyikan semua option kecuali yang pertama
                    Array.from(select.options).forEach((option, index) => {
                        if (index === 0) {
                            option.style.display = '';
                            return;
                        }
                        
                        const optionRole = option.getAttribute('data-role');
                        if (optionRole === this.role) {
                            option.style.display = '';
                        } else {
                            option.style.display = 'none';
                            // Unselect jika tidak sesuai role
                            if (option.selected) {
                                option.selected = false;
                                this.atasanId = '';
                            }
                        }
                    });
                },
                init() {
                    // Update options saat pertama kali load
                    this.updateAtasanOptions();
                    
                    // Update options saat role berubah
                    this.$watch('role', (newRole) => {
                        this.updateAtasanOptions();
                        // Reset atasan_id jika role berubah
                        const select = this.$refs.atasanSelect;
                        if (select) {
                            const currentValue = select.value;
                            const currentOption = select.options[select.selectedIndex];
                            if (currentOption && currentOption.getAttribute('data-role') !== newRole) {
                                select.value = '';
                                this.atasanId = '';
                            }
                        }
                    });
                }
            }">
                @csrf
                @method('PUT')
                
                <!-- Preserve filter parameters -->
                @if(request('filter_role'))
                    <input type="hidden" name="filter_role" value="{{ request('filter_role') }}">
                @endif
                @if(request('filter_atasan'))
                    <input type="hidden" name="filter_atasan" value="{{ request('filter_atasan') }}">
                @endif
                @if(request('page'))
                    <input type="hidden" name="page" value="{{ request('page') }}">
                @endif

                <div class="space-y-4">
                    <!-- NIK -->
                    <div>
                        <label for="nik" class="block text-sm font-medium text-gray-700 mb-1">NIK <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="nik" 
                               id="nik" 
                               value="{{ old('nik', $user->nik) }}" 
                               maxlength="5"
                               pattern="[0-9]{5}"
                               class="w-full border rounded px-3 py-2 @error('nik') border-red-500 @enderror" 
                               placeholder="12345"
                               required>
                        <p class="text-xs text-gray-500 mt-1">5 digit angka (contoh: 12345)</p>
                        @error('nik')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name', $user->name) }}" 
                               class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" 
                               required>
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               value="{{ old('email', $user->email) }}" 
                               class="w-full border rounded px-3 py-2 @error('email') border-red-500 @enderror" 
                               required>
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               class="w-full border rounded px-3 py-2 @error('password') border-red-500 @enderror" 
                               minlength="6"
                               placeholder="Kosongkan jika tidak ingin mengubah">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah password</p>
                        @error('password')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Photo -->
                    <div>
                        <label for="photo" class="block text-sm font-medium text-gray-700 mb-1">Photo</label>
                        @if($user->photo)
                            <div class="mb-2">
                                <p class="text-xs text-gray-500 mb-2">Photo saat ini:</p>
                                <img src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}" class="w-32 h-32 object-cover rounded-full border-2 border-gray-300" id="current-photo">
                            </div>
                        @endif
                        <input type="file" 
                               name="photo" 
                               id="photo" 
                               accept="image/jpeg,image/png,image/jpg,image/gif"
                               class="w-full border rounded px-3 py-2 @error('photo') border-red-500 @enderror"
                               onchange="previewPhoto(this)">
                        <p class="text-xs text-gray-500 mt-1">Format: JPEG, PNG, JPG, GIF (Max: 2MB). Kosongkan jika tidak ingin mengubah.</p>
                        <div id="photo-preview" class="mt-2 hidden">
                            <p class="text-xs text-gray-500 mb-2">Preview photo baru:</p>
                            <img id="photo-preview-img" src="" alt="Preview" class="w-32 h-32 object-cover rounded-full border-2 border-blue-300">
                        </div>
                        @error('photo')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jabatan -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Jabatan <span class="text-red-500">*</span></label>
                        <select name="role" 
                                id="role" 
                                x-model="role"
                                class="w-full border rounded px-3 py-2 @error('role') border-red-500 @enderror" 
                                required>
                            <option value="">Pilih Jabatan</option>
                            <option value="mekanik" {{ old('role', $user->role) == 'mekanik' ? 'selected' : '' }}>Mekanik (Team Member)</option>
                            <option value="team_leader" {{ old('role', $user->role) == 'team_leader' ? 'selected' : '' }}>Team Leader</option>
                            <option value="group_leader" {{ old('role', $user->role) == 'group_leader' ? 'selected' : '' }}>Group Leader</option>
                            <option value="coordinator" {{ old('role', $user->role) == 'coordinator' ? 'selected' : '' }}>Coordinator/Supervisor</option>
                            <option value="ast_manager" {{ old('role', $user->role) == 'ast_manager' ? 'selected' : '' }}>Assistant Manager</option>
                            <option value="manager" {{ old('role', $user->role) == 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="general_manager" {{ old('role', $user->role) == 'general_manager' ? 'selected' : '' }}>General Manager</option>
                            @if(auth()->user()->isAdmin())
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin (Full Access)</option>
                            @endif
                        </select>
                        @error('role')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Atasan -->
                    <div>
                        <label for="atasan_id" class="block text-sm font-medium text-gray-700 mb-1">Atasan</label>
                        <select name="atasan_id" 
                                id="atasan_id" 
                                x-model="atasanId"
                                class="w-full border rounded px-3 py-2 @error('atasan_id') border-red-500 @enderror"
                                :disabled="role === 'general_manager' || role === 'admin' || role === ''"
                                x-ref="atasanSelect">
                            <option value="">Tidak ada atasan</option>
                            <!-- Team Leader (untuk Mekanik) -->
                            @foreach($teamLeaders as $teamLeader)
                            <option value="{{ $teamLeader->id }}" 
                                    data-role="mekanik"
                                    {{ old('atasan_id', $user->atasan_id) == $teamLeader->id ? 'selected' : '' }}
                                    style="display: none;">
                                {{ $teamLeader->name }}
                            </option>
                            @endforeach
                            <!-- Group Leader (untuk Team Leader) -->
                            @foreach($groupLeaders as $groupLeader)
                            <option value="{{ $groupLeader->id }}" 
                                    data-role="team_leader"
                                    {{ old('atasan_id', $user->atasan_id) == $groupLeader->id ? 'selected' : '' }}
                                    style="display: none;">
                                {{ $groupLeader->name }}
                            </option>
                            @endforeach
                            <!-- Coordinator (untuk Group Leader) -->
                            @foreach($coordinators as $coordinator)
                            <option value="{{ $coordinator->id }}" 
                                    data-role="group_leader"
                                    {{ old('atasan_id', $user->atasan_id) == $coordinator->id ? 'selected' : '' }}
                                    style="display: none;">
                                {{ $coordinator->name }}
                            </option>
                            @endforeach
                            <!-- Assistant Manager (untuk Coordinator) -->
                            @foreach($astManagers as $astManager)
                            <option value="{{ $astManager->id }}" 
                                    data-role="coordinator"
                                    {{ old('atasan_id', $user->atasan_id) == $astManager->id ? 'selected' : '' }}
                                    style="display: none;">
                                {{ $astManager->name }}
                            </option>
                            @endforeach
                            <!-- Manager (untuk Assistant Manager) -->
                            @foreach($managers as $manager)
                            <option value="{{ $manager->id }}" 
                                    data-role="ast_manager"
                                    {{ old('atasan_id', $user->atasan_id) == $manager->id ? 'selected' : '' }}
                                    style="display: none;">
                                {{ $manager->name }}
                            </option>
                            @endforeach
                            <!-- General Manager (untuk Manager) -->
                            @foreach($generalManagers as $generalManager)
                            <option value="{{ $generalManager->id }}" 
                                    data-role="manager"
                                    {{ old('atasan_id', $user->atasan_id) == $generalManager->id ? 'selected' : '' }}
                                    style="display: none;">
                                {{ $generalManager->name }}
                            </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1" x-show="role === 'general_manager'">General Manager tidak memiliki atasan</p>
                        <p class="text-xs text-gray-500 mt-1" x-show="role === 'mekanik' && {{ $teamLeaders->count() }} === 0">Belum ada Team Leader</p>
                        <p class="text-xs text-gray-500 mt-1" x-show="role === 'team_leader' && {{ $groupLeaders->count() }} === 0">Belum ada Group Leader</p>
                        <p class="text-xs text-gray-500 mt-1" x-show="role === 'group_leader' && {{ $coordinators->count() }} === 0">Belum ada Coordinator</p>
                        <p class="text-xs text-gray-500 mt-1" x-show="role === 'coordinator' && {{ $astManagers->count() }} === 0">Belum ada Assistant Manager</p>
                        <p class="text-xs text-gray-500 mt-1" x-show="role === 'ast_manager' && {{ $managers->count() }} === 0">Belum ada Manager</p>
                        <p class="text-xs text-gray-500 mt-1" x-show="role === 'manager' && {{ $generalManagers->count() }} === 0">Belum ada General Manager</p>
                        @error('atasan_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('users.index', request()->only(['filter_role', 'filter_atasan', 'page'])) }}" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewPhoto(input) {
    const preview = document.getElementById('photo-preview');
    const previewImg = document.getElementById('photo-preview-img');
    const currentPhoto = document.getElementById('current-photo');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
            if (currentPhoto) {
                currentPhoto.classList.add('opacity-50');
            }
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.classList.add('hidden');
        if (currentPhoto) {
            currentPhoto.classList.remove('opacity-50');
        }
    }
}
</script>
@endsection

