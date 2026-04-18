<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use App\Models\Dealer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->role === 'dealer'
            && Auth::user()->dealer?->approval_status === 'approved') {
            return redirect()->route('dealer.dashboard');
        }

        return view('dealer.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'mobile'   => ['required', 'string'],
            'password' => ['required'],
        ]);

        // Find dealer by mobile
        $dealer = Dealer::where('mobile', $request->mobile)->first();

        if (! $dealer || ! $dealer->user_id) {
            return back()->withErrors(['mobile' => 'Invalid mobile number or password.'])->withInput();
        }

        if ($dealer->approval_status !== 'approved') {
            $msg = match ($dealer->approval_status) {
                'pending'  => 'Your dealer account is pending admin approval.',
                'rejected' => 'Your dealer registration was not approved. Please contact Waterfall support.',
                'inactive' => 'Your dealer account is inactive. Please contact Waterfall support.',
                default    => 'Your dealer account is not active yet. Please contact Waterfall support.',
            };
            return back()->withErrors(['mobile' => $msg])->withInput();
        }

        if (! Auth::attempt(['id' => $dealer->user_id, 'password' => $request->password], $request->boolean('remember'))) {
            return back()->withErrors(['mobile' => 'Invalid mobile number or password.'])->withInput();
        }

        $user = Auth::user();

        if ($user->role !== 'dealer') {
            Auth::logout();
            return back()->withErrors(['mobile' => 'Access denied. This panel is for dealers only.'])->withInput();
        }

        $request->session()->regenerate();

        return redirect()->route('dealer.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dealer.login');
    }
}
