<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle admin login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('username', $request->username)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Log in the admin using the 'admin' guard
        Auth::guard('admin')->login($admin, $request->boolean('remember'));

        // Regenerate session to prevent session fixation
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $admin->id,
                'username' => $admin->username,
                'email' => $admin->email,
            ],
        ]);
    }

    /**
     * Handle admin logout request.
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get the authenticated admin.
     */
    public function me(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'user' => [
                'id' => $admin->id,
                'username' => $admin->username,
                'email' => $admin->email,
            ],
        ]);
    }
}
