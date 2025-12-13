@extends('layouts.app')

@section('content')
<div class="w-full p-4 sm:p-6 lg:p-8 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-black mb-2">Profile</h1>
            <p class="text-black font-medium">Manage your account settings and profile information</p>
        </div>

        <!-- Profile Information Form -->
        <div class="bg-white rounded-lg shadow-lg border-2 border-gray-300 p-6">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <!-- Update Password Form -->
        <div class="bg-white rounded-lg shadow-lg border-2 border-gray-300 p-6">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <!-- Delete Account Form -->
        <div class="bg-white rounded-lg shadow-lg border-2 border-gray-300 p-6">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection
