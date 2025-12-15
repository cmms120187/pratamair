@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Struktur Organisasi (STO)</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('users.organizational-structure.chart') }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                    <i class="fas fa-diagram-project mr-2"></i>Bagan STO
                </a>
                <a href="{{ route('users.index') }}" class="text-gray-600 hover:text-gray-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <!-- Organizational Structure Tree - Display from top to bottom (General Manager to Mekanik) -->
            <div class="space-y-8">
                @foreach(array_reverse($structure, true) as $roleKey => $roleData)
                    @if(count($roleData['users']) > 0)
                        <div class="border-l-4 {{ 
                            $roleData['level'] == 7 ? 'border-purple-500' : 
                            ($roleData['level'] == 6 ? 'border-indigo-500' : 
                            ($roleData['level'] == 5 ? 'border-blue-500' : 
                            ($roleData['level'] == 4 ? 'border-cyan-500' : 
                            ($roleData['level'] == 3 ? 'border-teal-500' : 
                            ($roleData['level'] == 2 ? 'border-green-500' : 'border-gray-400'))))) 
                        }} pl-4">
                            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold {{ 
                                    $roleData['level'] == 7 ? 'bg-purple-100 text-purple-800' : 
                                    ($roleData['level'] == 6 ? 'bg-indigo-100 text-indigo-800' : 
                                    ($roleData['level'] == 5 ? 'bg-blue-100 text-blue-800' : 
                                    ($roleData['level'] == 4 ? 'bg-cyan-100 text-cyan-800' : 
                                    ($roleData['level'] == 3 ? 'bg-teal-100 text-teal-800' : 
                                    ($roleData['level'] == 2 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'))))) 
                                }}">
                                    Level {{ $roleData['level'] }}
                                </span>
                                <span>{{ $roleData['name'] }}</span>
                                <span class="text-sm font-normal text-gray-500">({{ count($roleData['users']) }})</span>
                            </h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                @foreach($roleData['users'] as $user)
                                    <div class="bg-gradient-to-br {{ 
                                        $roleData['level'] == 7 ? 'from-purple-50 to-indigo-50 border-purple-200' : 
                                        ($roleData['level'] == 6 ? 'from-indigo-50 to-blue-50 border-indigo-200' : 
                                        ($roleData['level'] == 5 ? 'from-blue-50 to-cyan-50 border-blue-200' : 
                                        ($roleData['level'] == 4 ? 'from-cyan-50 to-teal-50 border-cyan-200' : 
                                        ($roleData['level'] == 3 ? 'from-teal-50 to-green-50 border-teal-200' : 
                                        ($roleData['level'] == 2 ? 'from-green-50 to-emerald-50 border-green-200' : 'from-gray-50 to-slate-50 border-gray-200'))))) 
                                    }} rounded-lg p-4 border hover:shadow-lg transition">
                                        <div class="flex items-start justify-between mb-2 gap-3">
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-gray-900 mb-1">
                                                    <a href="{{ route('users.edit', $user->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                                        {{ $user->name }}
                                                    </a>
                                                </h3>
                                                <p class="text-sm text-gray-600">NIK: {{ $user->nik ?? '-' }}</p>
                                                <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                                                <p class="text-xs text-gray-600 mt-1">
                                                    <a href="{{ route('users.index', ['filter_role' => $user->role]) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                                        {{ $roleData['name'] }}
                                                    </a>
                                                </p>
                                            </div>
                                            <div class="flex flex-col items-end gap-2">
                                                <!-- Photo -->
                                                <div class="w-16 h-16 rounded-full overflow-hidden border-2 {{ 
                                                    $roleData['level'] == 7 ? 'border-purple-300' : 
                                                    ($roleData['level'] == 6 ? 'border-indigo-300' : 
                                                    ($roleData['level'] == 5 ? 'border-blue-300' : 
                                                    ($roleData['level'] == 4 ? 'border-cyan-300' : 
                                                    ($roleData['level'] == 3 ? 'border-teal-300' : 
                                                    ($roleData['level'] == 2 ? 'border-green-300' : 'border-gray-300'))))) 
                                                }} bg-gray-100 flex-shrink-0">
                                                    @if($user->photo)
                                                        <img src="{{ asset('storage/' . $user->photo) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center {{ 
                                                            $roleData['level'] == 7 ? 'bg-purple-100 text-purple-600' : 
                                                            ($roleData['level'] == 6 ? 'bg-indigo-100 text-indigo-600' : 
                                                            ($roleData['level'] == 5 ? 'bg-blue-100 text-blue-600' : 
                                                            ($roleData['level'] == 4 ? 'bg-cyan-100 text-cyan-600' : 
                                                            ($roleData['level'] == 3 ? 'bg-teal-100 text-teal-600' : 
                                                            ($roleData['level'] == 2 ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600'))))) 
                                                        }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <!-- Edit Button -->
                                                <a href="{{ route('users.edit', $user->id) }}" class="inline-flex items-center justify-center {{ 
                                                    $roleData['level'] == 7 ? 'bg-purple-600 hover:bg-purple-700' : 
                                                    ($roleData['level'] == 6 ? 'bg-indigo-600 hover:bg-indigo-700' : 
                                                    ($roleData['level'] == 5 ? 'bg-blue-600 hover:bg-blue-700' : 
                                                    ($roleData['level'] == 4 ? 'bg-cyan-600 hover:bg-cyan-700' : 
                                                    ($roleData['level'] == 3 ? 'bg-teal-600 hover:bg-teal-700' : 
                                                    ($roleData['level'] == 2 ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-600 hover:bg-gray-700'))))) 
                                                }} text-white px-3 py-1.5 rounded text-xs font-medium shadow transition" title="Edit User">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </a>
                                            </div>
                                        </div>
                                        
                                        @if($user->atasan)
                                            <div class="mt-3 pt-3 border-t {{ 
                                                $roleData['level'] == 7 ? 'border-purple-200' : 
                                                ($roleData['level'] == 6 ? 'border-indigo-200' : 
                                                ($roleData['level'] == 5 ? 'border-blue-200' : 
                                                ($roleData['level'] == 4 ? 'border-cyan-200' : 
                                                ($roleData['level'] == 3 ? 'border-teal-200' : 
                                                ($roleData['level'] == 2 ? 'border-green-200' : 'border-gray-200'))))) 
                                            }}">
                                                <p class="text-xs text-gray-500 mb-1">Atasan:</p>
                                                <a href="{{ route('users.edit', $user->atasan->id) }}" class="text-sm font-medium {{ 
                                                    $roleData['level'] == 7 ? 'text-purple-700 hover:text-purple-900' : 
                                                    ($roleData['level'] == 6 ? 'text-indigo-700 hover:text-indigo-900' : 
                                                    ($roleData['level'] == 5 ? 'text-blue-700 hover:text-blue-900' : 
                                                    ($roleData['level'] == 4 ? 'text-cyan-700 hover:text-cyan-900' : 
                                                    ($roleData['level'] == 3 ? 'text-teal-700 hover:text-teal-900' : 
                                                    ($roleData['level'] == 2 ? 'text-green-700 hover:text-green-900' : 'text-gray-700 hover:text-gray-900'))))) 
                                                }} hover:underline">{{ $user->atasan->name }}</a>
                                            </div>
                                        @endif
                                        
                                        @if($user->bawahan->count() > 0)
                                            <div class="mt-3 pt-3 border-t {{ 
                                                $roleData['level'] == 7 ? 'border-purple-200' : 
                                                ($roleData['level'] == 6 ? 'border-indigo-200' : 
                                                ($roleData['level'] == 5 ? 'border-blue-200' : 
                                                ($roleData['level'] == 4 ? 'border-cyan-200' : 
                                                ($roleData['level'] == 3 ? 'border-teal-200' : 
                                                ($roleData['level'] == 2 ? 'border-green-200' : 'border-gray-200'))))) 
                                            }}">
                                                <p class="text-xs text-gray-500 mb-1">Bawahan ({{ $user->bawahan->count() }}):</p>
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($user->bawahan->take(3) as $bawahan)
                                                        <a href="{{ route('users.edit', $bawahan->id) }}" class="text-xs {{ 
                                                            $roleData['level'] == 7 ? 'bg-purple-100 text-purple-800 hover:bg-purple-200' : 
                                                            ($roleData['level'] == 6 ? 'bg-indigo-100 text-indigo-800 hover:bg-indigo-200' : 
                                                            ($roleData['level'] == 5 ? 'bg-blue-100 text-blue-800 hover:bg-blue-200' : 
                                                            ($roleData['level'] == 4 ? 'bg-cyan-100 text-cyan-800 hover:bg-cyan-200' : 
                                                            ($roleData['level'] == 3 ? 'bg-teal-100 text-teal-800 hover:bg-teal-200' : 
                                                            ($roleData['level'] == 2 ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'))))) 
                                                        }} px-2 py-1 rounded hover:underline">{{ $bawahan->name }}</a>
                                                    @endforeach
                                                    @if($user->bawahan->count() > 3)
                                                        <a href="{{ route('users.index', ['filter_atasan' => $user->id]) }}" class="text-xs text-gray-500 hover:text-gray-700 hover:underline">+{{ $user->bawahan->count() - 3 }} lainnya</a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            
            @if(count(array_filter($structure, fn($s) => count($s['users']) > 0)) == 0)
                <div class="text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="text-gray-500 text-lg">Belum ada data struktur organisasi.</p>
                    <p class="text-gray-400 text-sm mt-2">Silakan tambahkan user terlebih dahulu di menu Users.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

