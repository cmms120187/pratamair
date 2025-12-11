@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">{{ $rca->title }}</h1>
            <div class="flex items-center gap-3">
                <a href="{{ route('root-cause-analysis-details.edit', $rca->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                    Edit
                </a>
                <a href="{{ route('root-cause-analysis-details.index') }}" class="text-gray-600 hover:text-gray-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 space-y-6">
            <!-- Header Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pb-4 border-b">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Metode Analisis</label>
                    <div class="text-lg font-bold text-gray-900">{{ $rca->getMethodLabel() }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $rca->getStatusBadgeClass() }}">
                        {{ $rca->getStatusLabel() }}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Analisis</label>
                    <div class="text-gray-900">{{ $rca->analysis_date->format('d F Y') }}</div>
                </div>
            </div>

            <!-- Problem Description -->
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Deskripsi Masalah</h2>
                <div class="bg-gray-50 rounded p-4">
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $rca->problem_description }}</p>
                </div>
            </div>

            <!-- Machine & Downtime Info -->
            @if($rca->machine || $rca->downtime)
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Terkait</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($rca->machine)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Mesin</label>
                        <div class="text-gray-900">{{ $rca->machine->idMachine }}</div>
                    </div>
                    @endif
                    @if($rca->downtime)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Downtime</label>
                        <div class="text-gray-900">{{ $rca->downtime->date->format('d F Y') }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- 5 Whys Visualization -->
            @if($rca->analysis_method === '5_whys' && $rca->fiveWhys->count() > 0)
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">5 Whys Analysis</h2>
                <div class="space-y-4">
                    @foreach($rca->fiveWhys as $why)
                    <div class="border-l-4 border-blue-500 pl-4 py-2 bg-blue-50 rounded-r">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">
                                {{ $why->why_level }}
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-gray-800 mb-1">{{ $why->question }}</div>
                                <div class="text-gray-700">{{ $why->answer }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Root Cause -->
            @if($rca->root_cause)
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Root Cause (Akar Masalah)</h2>
                <div class="bg-red-50 border-l-4 border-red-500 rounded p-4">
                    <p class="text-gray-900 whitespace-pre-wrap font-medium">{{ $rca->root_cause }}</p>
                </div>
            </div>
            @endif

            <!-- Corrective Action -->
            @if($rca->corrective_action)
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Corrective Action (Tindakan Korektif)</h2>
                <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded p-4">
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $rca->corrective_action }}</p>
                </div>
            </div>
            @endif

            <!-- Preventive Action -->
            @if($rca->preventive_action)
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Preventive Action (Tindakan Pencegahan)</h2>
                <div class="bg-green-50 border-l-4 border-green-500 rounded p-4">
                    <p class="text-gray-900 whitespace-pre-wrap">{{ $rca->preventive_action }}</p>
                </div>
            </div>
            @endif

            <!-- Created By -->
            <div class="pt-4 border-t">
                <div class="text-sm text-gray-500">
                    Dibuat oleh: <span class="font-medium text-gray-700">{{ $rca->createdBy->name }}</span>
                    pada {{ $rca->created_at->format('d F Y H:i') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
