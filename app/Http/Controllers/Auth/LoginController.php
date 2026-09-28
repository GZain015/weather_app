<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create() : View
    {
        return view('auth.login');
    }

    public function store(Request $request) : RedirectResponse
    {
        $validated = $request->validate([
            "email" => ['required', 'email'],
            "password" => ['required'],
        ]);

        Auth::attempt($validated);

        if (Auth::attempt($validated, $request->boolean('remember'))){
            $request->session()->regenerate();

            return redirect()->intended(route('weather.index'));
        }

        return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');

    }

    public function destroy(Request $request) : RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('weather.index');
    }
}
