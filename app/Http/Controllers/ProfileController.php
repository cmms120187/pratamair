<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Helpers\ImageHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                ImageHelper::deleteOldImage($user->photo);
            }
            
            // Upload new photo using ImageHelper with auto-compress to max 1MB
            $photoPath = ImageHelper::convertToWebP($request->file('photo'), 'users', 85, 1048576); // 1MB = 1048576 bytes
            if ($photoPath) {
                $validated['photo'] = $photoPath;
            } else {
                // Fallback if ImageHelper fails
                $validated['photo'] = $request->file('photo')->store('users', 'public');
            }
        } else {
            // Keep existing photo if no new photo uploaded
            unset($validated['photo']);
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
