<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function loginAction(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'is_active' => 1])) {
            $request->session()->regenerate();

            return $this->redirectBasedOnRole();
        }

        return back()->withErrors([
            'email' => 'As credenciais fornecidas estão incorretas ou a conta está inativa.',
        ])->onlyInput('email');
    }

    public function register()
    {
        return view('auth.register');
    }

    public function registerAction(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'type_user' => 'required|integer|in:1,2', // 1=Barista, 2=User
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'client', // Default role
            'type_user' => $request->type_user,
            'is_active' => 1,
        ]);

        Auth::login($user);

        return $this->redirectBasedOnRole();
    }

    protected function redirectBasedOnRole()
    {
        $user = Auth::user();

        if ($user->type_user == 1) { // Barista
            return redirect()->route('dashboard.barista');
        } elseif ($user->type_user == 2) { // Rolezeiro/User
            return redirect()->route('dashboard.rolezeiro');
        } elseif ($user->role === 'admin' || $user->type_user == 3) { // Admin/Master
            return redirect()->route('dashboard.master');
        }

        return redirect('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
