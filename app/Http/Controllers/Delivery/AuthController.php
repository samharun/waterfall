<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->role === 'delivery_staff') {
            return redirect()->route('delivery.today');
        }

        return view('delivery.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required'],
        ]);

        $loginField = $request->login;

        // Try email first, then name as fallback
        $credentials = filter_var($loginField, FILTER_VALIDATE_EMAIL)
            ? ['email' => $loginField, 'password' => $request->password]
            : ['email' => $loginField, 'password' => $request->password]; // email-based login

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['login' => 'Invalid credentials.'])->withInput();
        }

        $user = Auth::user();

        if ($user->role !== 'delivery_staff') {
            Auth::logout();
            return back()->withErrors(['login' => 'Access denied. This panel is for delivery staff only.'])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->route('delivery.today');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('delivery.login');
    }
}
