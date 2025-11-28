@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Edit Brand</h1>
    <form action="{{ route('brands.update', $brand->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $brand->name }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('brands.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
