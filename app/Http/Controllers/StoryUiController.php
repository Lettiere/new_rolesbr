<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StoryUiController extends Controller
{
    public function __invoke(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userId = (int) $user->id;

        $perfil = DB::table('perfil_usuarios_tb')->where('user_id', $userId)->first();

        $ads = DB::table('form_perfil_bares_tb')
            ->orderByRaw('RAND()')
            ->limit(6)
            ->get();

        if (!$perfil) {
            $perfil = (object) [
                'user_id' => $userId,
                'cpf' => '',
                'telefone' => '',
                'foto_perfil' => null,
                'bio' => null,
            ];
        }

        return view('dash_client.perfil_usuario.stories_instagram', [
            'perfil' => $perfil,
            'ads' => $ads,
            'targetUserId' => $userId,
        ]);
    }
}

