@extends('layouts.app')
@section('content')
<div class="max-w-xl mx-auto p-8 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Create Plant</h1>
    <form action="{{ route('plants.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label for="name" class="block font-semibold mb-2">Name</label>
            <input type="text" name="name" id="name" class="w-full border rounded px-3 py-2" required>
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded">Create</button>
        <a href="{{ route('plants.index') }}" class="ml-4 text-gray-600 hover:underline">Cancel</a>
    </form>
</div>
@endsection
