<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the current user's profile page.
     */
    public function index()
    {
        $user = auth()->user();
        return view('profile.index', compact('user'));
    }

    /**
     * Update the current user's profile.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'email'              => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password'           => 'nullable|string|min:6|confirmed',
            'telegram_username'  => 'nullable|string|max:255',
        ]);

        // Handle password
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Handle telegram_active toggle (checkbox)
        $validated['telegram_active'] = $request->has('telegram_active') ? 1 : 0;

        // If telegram is deactivated, clear the chat mapping
        if (!$validated['telegram_active']) {
            $validated['telegram_chat_id'] = null;
        }

        $user->update($validated);

        return redirect()->route('profile.index')
            ->with('success', 'Profile updated successfully.');
    }
}
