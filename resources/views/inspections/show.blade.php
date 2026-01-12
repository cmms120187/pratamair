@extends('layouts.app')
@section('content')
<div class="p-4 sm:p-6 lg:p-8">
    <div class="max-w-6xl mx-auto bg-white rounded shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold">Detail Inspeksi #{{ $inspection->id }}</h1>
            <div class="space-x-2">
                <a href="{{ route('inspections.index') }}" class="px-3 py-2 border rounded">Kembali</a>
                <a href="{{ route('inspections.edit', $inspection->id) }}" class="px-3 py-2 bg-yellow-500 text-white rounded">Edit</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-700">Mesin</h3>
                <p>{{ $inspection->machine->idMachine ?? '-' }}</p>

                <h3 class="text-sm font-semibold text-gray-700 mt-4">Tanggal</h3>
                <p>{{ $inspection->inspection_date }}</p>

                <h3 class="text-sm font-semibold text-gray-700 mt-4">Dilakukan Oleh</h3>
                <p>{{ optional($inspection->performedBy)->name ?? ($inspection->performed_by ? 'User '.$inspection->performed_by : '-') }}</p>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-700">Catatan</h3>
                <p>{{ $inspection->notes ?? '-' }}</p>

                <h3 class="text-sm font-semibold text-gray-700 mt-4">Template</h3>
                <p>{{ $inspection->template->name ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
