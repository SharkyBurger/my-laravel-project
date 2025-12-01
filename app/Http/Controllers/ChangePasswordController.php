<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    /**
     * Show the form for changing the user's password.
     */
    public function edit()
    {
        return view('auth.change-password'); // Create this view in the next step
    }

    /**
     * Update the user's password.
     */
    public function update(Request $request)
    {
       

        // 1. Validate the input
        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', Password::defaults()],
        ], [
            // Custom message for the current_password rule
            'current_password.current_password' => 'The provided current password does not match your records.',
        ]);

        // 2. Update the password
        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        // 3. Redirect back with a success message
        return back()->with('success', 'Your password has been successfully updated!');
    }
}