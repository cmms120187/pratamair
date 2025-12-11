@extends('layouts.app')
@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8">
    <div class="w-full max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Detail Downtime ERP</h1>
        <div class="bg-white rounded-lg shadow p-6">
            <!-- Informasi Mesin -->
            <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                <h2 class="font-bold mb-4 text-gray-800">Informasi Mesin</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end mb-4">
                    <div class="md:col-span-3">
                        <label class="block mb-2 font-semibold text-gray-700">ID Mesin</label>
                        <input type="text" value="{{ $row->idMachine }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <button type="button" disabled class="w-full bg-gray-400 text-white font-semibold py-2 px-4 rounded shadow cursor-not-allowed">Search</button>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Nama Mesin</label>
                        <input type="text" value="{{ $row->typeMachine }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Model Mesin</label>
                        <input type="text" value="{{ $row->modelMachine }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Brand Mesin</label>
                        <input type="text" value="{{ $row->brandMachine }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Room/Plant</label>
                        <input type="text" value="{{ $row->roomName }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Plant</label>
                        <input type="text" value="{{ $row->plant }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Process</label>
                        <input type="text" value="{{ $row->process }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Line</label>
                        <input type="text" value="{{ $row->line }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                </div>
            </div>

            <!-- Downtime -->
            <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                <h2 class="font-bold mb-4 text-gray-800">Downtime</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Date</label>
                        <input type="date" value="{{ $row->date }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Stop Production</label>
                        <input type="text" value="{{ $row->stopProduction }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Respon Mechanic</label>
                        <input type="text" value="{{ $row->responMechanic }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Start Production</label>
                        <input type="text" value="{{ $row->startProduction }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Duration (minutes)</label>
                        <input type="text" value="{{ $row->duration }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Standard Time</label>
                        <input type="text" value="{{ $row->Standar_Time }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Problem</label>
                        <input type="text" value="{{ $row->problemDowntime }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Problem MM</label>
                        <input type="text" value="{{ $row->Problem_MM }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Reason</label>
                        <input type="text" value="{{ $row->reasonDowntime }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Action</label>
                        <input type="text" value="{{ $row->actionDowtime }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Group Problem</label>
                        <input type="text" value="{{ $row->groupProblem }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                </div>
            </div>

            <!-- Detail -->
            <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                <h2 class="font-bold mb-4 text-gray-800">Detail</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">ID Mekanik</label>
                        <input type="text" value="{{ $row->idMekanik }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Nama Mekanik</label>
                        <input type="text" value="{{ $row->nameMekanik }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">ID Leader</label>
                        <input type="text" value="{{ $row->idLeader }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Nama Leader</label>
                        <input type="text" value="{{ $row->nameLeader }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">ID Coordinator</label>
                        <input type="text" value="{{ $row->idCoord }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Nama Coordinator</label>
                        <input type="text" value="{{ $row->nameCoord }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">Part</label>
                        <input type="text" value="{{ $row->Part }}" disabled class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('downtime_erp.edit', $row->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded shadow transition">Edit</a>
                <a href="{{ route('downtime_erp.index') }}" class="text-gray-600 hover:text-gray-800 font-semibold">Kembali</a>
            </div>
        </div>
    </div>
</div>
@endsection
