<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate(); // Authenticate user credentials

        $request->session()->regenerate(); // Prevent session fixation attacks

        // Redirect based on role
        $role = auth()->user()->role;

        switch ($role) {
            case 'administrator':
                return redirect()->route('users.index'); // Redirect to User Management
            case 'editor':
                return redirect()->route('posts.index'); // Redirect to Post Management
            default:
                abort(403, 'Unauthorized access.'); // Block invalid roles
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Logged out successfully!');
    }
}
