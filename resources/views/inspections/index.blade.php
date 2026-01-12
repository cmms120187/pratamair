@extends('layouts.app')
@section('content')
<div class="p-4 sm:p-6 lg:p-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Daftar Inspeksi</h1>
            <a href="{{ route('inspections.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition">Buat Inspeksi</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded shadow p-4">
            @if($inspections->count())
                <table class="w-full table-auto">
                    <thead>
                        <tr class="text-left text-sm text-gray-600">
                            <th class="p-2">#</th>
                            <th class="p-2">Mesin</th>
                            <th class="p-2">Tanggal</th>
                            <th class="p-2">Dilakukan Oleh</th>
                            <th class="p-2">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inspections as $ins)
                            <tr class="border-t">
                                <td class="p-2">{{ $ins->id }}</td>
                                <td class="p-2">{{ $ins->machine->idMachine ?? 'N/A' }}</td>
                                <td class="p-2">{{ $ins->inspection_date }}</td>
                                <td class="p-2">{{ optional($ins->performedBy)->name ?? ($ins->performed_by ? 'User '.$ins->performed_by : '-') }}</td>
                                <td class="p-2">
                                    <a href="{{ route('inspections.show', $ins->id) }}" class="text-blue-600 hover:text-blue-800">Lihat</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">{{ $inspections->links() }}</div>
            @else
                <div class="text-center text-gray-500 py-8">Belum ada inspeksi.</div>
            @endif
        </div>
    </div>
</div>
@endsection
