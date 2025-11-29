@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Role Permissions Management</h1>
        </div>
        
        <div class="py-4">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('permissions.update') }}" method="POST" id="permissions-form">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-4">
                                Centang checkbox untuk memberikan akses menu kepada role tertentu. 
                                <strong>Admin selalu memiliki akses penuh</strong> dan tidak perlu diatur di sini.
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse border border-gray-300">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="border border-gray-300 px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider sticky left-0 bg-gray-100 z-10">
                                            Menu
                                        </th>
                                        @foreach($roles as $role)
                                            @if($role !== 'admin')
                                                <th class="border border-gray-300 px-3 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">
                                                    {{ ucfirst(str_replace('_', ' ', $role)) }}
                                                </th>
                                            @endif
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @if(isset($menuKeys) && count($menuKeys) > 0)
                                        @foreach($menuKeys as $menuKey)
                                            <tr class="hover:bg-gray-50">
                                                <td class="border border-gray-300 px-4 py-3 text-sm font-medium text-gray-900 sticky left-0 bg-white z-10">
                                                    {{ isset($menuLabels[$menuKey]) ? $menuLabels[$menuKey] : ucfirst(str_replace(['-', '_'], ' ', $menuKey)) }}
                                                </td>
                                                @if(isset($roles))
                                                    @foreach($roles as $role)
                                                        @if($role !== 'admin')
                                                            @php
                                                                $permissionKey = $role . '_' . $menuKey;
                                                                $isChecked = false;
                                                                if (isset($permissions[$permissionKey]) && $permissions[$permissionKey]->allowed) {
                                                                    $isChecked = true;
                                                                } else {
                                                                    // Check default permission from PermissionHelper
                                                                    try {
                                                                        $defaultPermission = \App\Helpers\PermissionHelper::getMenuPermissions()[$menuKey] ?? null;
                                                                        if ($defaultPermission) {
                                                                            $isChecked = in_array('all', $defaultPermission['allowed_roles']) || 
                                                                                         in_array($role, $defaultPermission['allowed_roles']);
                                                                        }
                                                                    } catch (\Exception $e) {
                                                                        // Fallback if PermissionHelper fails
                                                                    }
                                                                }
                                                            @endphp
                                                            <td class="border border-gray-300 px-3 py-3 text-center">
                                                                <input type="checkbox" 
                                                                       name="permissions[{{ $menuKey }}][{{ $role }}][allowed]"
                                                                       value="1"
                                                                       {{ $isChecked ? 'checked' : '' }}
                                                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                                                <input type="hidden" 
                                                                       name="permissions[{{ $menuKey }}][{{ $role }}][role]" 
                                                                       value="{{ $role }}">
                                                                <input type="hidden" 
                                                                       name="permissions[{{ $menuKey }}][{{ $role }}][menu_key]" 
                                                                       value="{{ $menuKey }}">
                                                            </td>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="{{ isset($roles) ? count(array_filter($roles, function($r) { return $r !== 'admin'; })) + 1 : 1 }}" class="text-center py-8 text-gray-500">
                                                No menu permissions found. Please check the PermissionHelper configuration.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" 
                                    onclick="resetForm()" 
                                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                                Reset
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                Save Permissions
                            </button>
                        </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function resetForm() {
        if (confirm('Are you sure you want to reset all changes?')) {
            location.reload();
        }
    }
</script>
@endsection

