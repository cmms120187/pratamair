@extends('layouts.app')
@section('content')
<style>
    /* Ensure checkboxes are always clickable */
    .permission-checkbox {
        pointer-events: auto !important;
        cursor: pointer !important;
        position: relative !important;
        z-index: 10 !important;
    }
    
    .permission-checkbox:disabled {
        pointer-events: none !important;
    }
    
    /* Ensure table cells don't block clicks */
    td.permission-cell {
        position: relative;
        z-index: 1;
        pointer-events: none;
    }
    
    td.permission-cell label,
    td.permission-cell input[type="checkbox"] {
        pointer-events: auto;
        cursor: pointer;
    }
</style>
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
                                                                // Only check database, don't fallback to default permissions
                                                                // The management interface should only reflect what's in the database
                                                                if (isset($permissions[$permissionKey]) && $permissions[$permissionKey]->allowed) {
                                                                    $isChecked = true;
                                                                }
                                                            @endphp
                                                            <td class="border border-gray-300 px-3 py-3 text-center permission-cell">
                                                                <label class="inline-flex items-center cursor-pointer">
                                                                    <input type="checkbox" 
                                                                           name="permissions[{{ $menuKey }}][{{ $role }}][allowed]"
                                                                           value="1"
                                                                           {{ $isChecked ? 'checked' : '' }}
                                                                           class="permission-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                                                           style="width: 1.25rem; height: 1.25rem; cursor: pointer;"
                                                                           data-role="{{ $role }}"
                                                                           data-menu-key="{{ $menuKey }}">
                                                                </label>
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

    // Ensure form can be submitted properly and checkboxes are clickable
    (function() {
        'use strict';
        
        function initPermissionsForm() {
            const form = document.getElementById('permissions-form');
            if (!form) {
                console.warn('Permissions form not found');
                return;
            }
            
            // Make sure all checkboxes are enabled and clickable
            const checkboxes = form.querySelectorAll('input[type="checkbox"].permission-checkbox');
            console.log('Found', checkboxes.length, 'checkboxes');
            
            checkboxes.forEach(function(checkbox, index) {
                // Remove any disabled or readonly attributes
                checkbox.removeAttribute('disabled');
                checkbox.removeAttribute('readonly');
                
                // Ensure checkbox is interactive
                checkbox.style.pointerEvents = 'auto';
                checkbox.style.cursor = 'pointer';
                
                // Test click event
                checkbox.addEventListener('change', function(e) {
                    console.log('Checkbox changed:', this.name, this.checked);
                }, false);
            });
            
            // Form submission handler - ensure it works
            form.addEventListener('submit', function(e) {
                // Count checked checkboxes
                const checkedCount = form.querySelectorAll('input[type="checkbox"].permission-checkbox:checked').length;
                const totalCount = form.querySelectorAll('input[type="checkbox"].permission-checkbox').length;
                console.log('Submitting form with', checkedCount, 'checked permissions out of', totalCount, 'total');
                
                // Debug: Log form data structure
                const formData = new FormData(form);
                const permissionsData = {};
                for (let [key, value] of formData.entries()) {
                    if (key.startsWith('permissions[')) {
                        permissionsData[key] = value;
                    }
                }
                console.log('Form data structure:', permissionsData);
                
                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    const originalText = submitBtn.textContent;
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Saving...';
                    
                    // Re-enable after 5 seconds if form doesn't redirect (error case)
                    setTimeout(function() {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }, 5000);
                }
                
                // Allow form to submit normally
                // Unchecked checkboxes won't be sent, which is correct behavior
                // The backend will handle this by deleting all permissions and only inserting checked ones
                return true;
            }, false);
        }
        
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPermissionsForm);
        } else {
            // DOM already loaded
            initPermissionsForm();
        }
        
        // Also try after a short delay in case of race conditions
        setTimeout(initPermissionsForm, 100);
    })();
</script>
@endsection

