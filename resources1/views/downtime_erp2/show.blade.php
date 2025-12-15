@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="max-w-6xl mx-auto bg-white rounded-lg shadow p-6 sm:p-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">View Downtime ERP2</h1>
                <p class="text-sm text-gray-600">Detail informasi downtime ERP2</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('downtime-erp2.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded shadow transition">
                    Back
                </a>
                @if(Auth::user()->role !== 'mekanik')
                    <a href="{{ route('downtime-erp2.edit', ['derp2' => $downtimeErp2->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition">
                        Edit
                    </a>
                @endif
            </div>
        </div>
        
        <!-- Basic Information -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Basic Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Date</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->date }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Plant</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->plant }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Process</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->process }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Line</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->line }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Room Name</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->roomName }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Kode Room</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->kode_room ?? '-' }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Include OEE</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->include_oee ? 'Yes' : 'No' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Machine Information -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Machine Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">ID Machine</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->idMachine }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Type Machine</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->typeMachine }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Model Machine</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->modelMachine }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Brand Machine</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->brandMachine }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Downtime Information -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Downtime Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Stop Production</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->stopProduction }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Respon Mechanic</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->responMechanic }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Start Production</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->startProduction }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Duration</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->duration }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Standar Time</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->Standar_Time ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Problem Information -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Problem Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Problem Downtime</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->problemDowntime }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Problem MM</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->Problem_MM ?? '-' }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Reason Downtime</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->reasonDowntime }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Action Downtime</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->actionDowtime }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Part</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->Part ?? '-' }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Group Problem</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->groupProblem ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Personnel Information -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Personnel Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">ID Mekanik</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->idMekanik }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Name Mekanik</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->nameMekanik }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">ID Leader</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->idLeader }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Name Leader</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->nameLeader }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">ID GL</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->idGL ?? '-' }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Name GL</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->nameGL ?? '-' }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">ID Coord</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->idCoord }}
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Name Coord</label>
                    <div class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-900">
                        {{ $downtimeErp2->nameCoord }}
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-4 border-t">
            <a href="{{ route('downtime-erp2.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg shadow transition">
                Back to List
            </a>
            @if(Auth::user()->role !== 'mekanik')
                <a href="{{ route('downtime-erp2.edit', ['derp2' => $downtimeErp2->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg shadow transition">
                    Edit
                </a>
            @endif
        </div>
    </div>
</div>
@endsection

