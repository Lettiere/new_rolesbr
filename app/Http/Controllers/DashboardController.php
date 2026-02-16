<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function barista()
    {
        if (Auth::user()->type_user != 1) {
            return redirect('/');
        }
        return view('dashboard.barista.dash_barista');
    }

    public function rolezeiro()
    {
        if (Auth::user()->type_user != 2) {
            return redirect('/');
        }
        return view('dashboard.rolezeiro.dash_rolezeiro');
    }

    public function master()
    {
        // Check for admin role or specific type_user (e.g., 3)
        // Adjust condition based on actual 'master' definition
        if (Auth::user()->role !== 'admin' && Auth::user()->type_user != 3) {
             return redirect('/');
        }
        return view('dashboard.master.dash_master');
    }

    public function admin()
    {
        return $this->master();
    }
}
