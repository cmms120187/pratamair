@extends('layouts.app')
@section('content')
<div class="max-w-xl mx-auto p-8 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Create Line</h1>
    <form action="{{ route('lines.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label for="name" class="block font-semibold mb-2">Nama Line</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" required>
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-4">
            <label for="plant_id" class="block font-semibold mb-2">Plant</label>
            <select name="plant_id" id="plant_id" class="w-full border rounded px-3 py-2 @error('plant_id') border-red-500 @enderror" required>
                <option value="">Pilih Plant</option>
                @foreach($plants as $plant)
                    <option value="{{ $plant->id }}" {{ old('plant_id') == $plant->id ? 'selected' : '' }}>{{ $plant->name }}</option>
                @endforeach
            </select>
            @error('plant_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-4">
            <label for="process_id" class="block font-semibold mb-2">Process</label>
            <select name="process_id" id="process_id" class="w-full border rounded px-3 py-2 @error('process_id') border-red-500 @enderror" required>
                <option value="">Pilih Process</option>
                @foreach($processes as $process)
                    <option value="{{ $process->id }}" {{ old('process_id') == $process->id ? 'selected' : '' }}>{{ $process->name }}</option>
                @endforeach
            </select>
            @error('process_id')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded">Create</button>
        <a href="{{ route('lines.index') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
    </form>
</div>
@endsection
