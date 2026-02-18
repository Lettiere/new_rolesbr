<?php

namespace App\Http\Controllers;

use App\Models\ProfileUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProfileUserController extends Controller
{
    public function showCurrent()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $profile = ProfileUser::query()
            ->with('user')
            ->select([
                'perfil_usuarios_tb.*',
                'base_estados.nome as estado_nome',
                'base_cidades.nome as cidade_nome',
                'base_genero_usuario.nome as genero_nome',
                'base_bairros.nome as bairro_nome_rel',
            ])
            ->leftJoin('base_cidades', 'base_cidades.id', '=', 'perfil_usuarios_tb.cidade_id')
            ->leftJoin('base_estados', 'base_estados.id', '=', 'base_cidades.estado_id')
            ->leftJoin('base_genero_usuario', 'base_genero_usuario.genero_id', '=', 'perfil_usuarios_tb.genero_id')
            ->leftJoin('base_bairros', 'base_bairros.id', '=', 'perfil_usuarios_tb.bairro_id')
            ->where('perfil_usuarios_tb.user_id', $user->id)
            ->first();

        if (!$profile) {
            $profile = ProfileUser::create([
                'user_id' => $user->id,
                'cpf' => '00000000000000',
                'telefone' => '',
            ]);
            $profile->load('user');
        }

        return view('profile.show', compact('profile', 'user'));
    }

    public function show($perfilId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $profile = ProfileUser::query()
            ->with('user')
            ->select([
                'perfil_usuarios_tb.*',
                'base_estados.nome as estado_nome',
                'base_cidades.nome as cidade_nome',
                'base_genero_usuario.nome as genero_nome',
                'base_bairros.nome as bairro_nome_rel',
            ])
            ->leftJoin('base_cidades', 'base_cidades.id', '=', 'perfil_usuarios_tb.cidade_id')
            ->leftJoin('base_estados', 'base_estados.id', '=', 'base_cidades.estado_id')
            ->leftJoin('base_genero_usuario', 'base_genero_usuario.genero_id', '=', 'perfil_usuarios_tb.genero_id')
            ->leftJoin('base_bairros', 'base_bairros.id', '=', 'perfil_usuarios_tb.bairro_id')
            ->where('perfil_usuarios_tb.perfil_id', (int) $perfilId)
            ->firstOrFail();

        if ((int) ($user->type_user ?? 0) !== 3 && (int) $profile->user_id !== (int) $user->id) {
            abort(403);
        }

        return view('profile.show', compact('profile', 'user'));
    }

    public function index(Request $request)
    {
        $query = ProfileUser::with('user');

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if ((int) ($user->type_user ?? 0) !== 3) {
            $query->where('user_id', $user->id);
        }

        if ($cpf = trim((string) $request->get('cpf', ''))) {
            $query->where('cpf', 'like', '%' . $cpf . '%');
        }

        if ($name = trim((string) $request->get('name', ''))) {
            $query->whereHas('user', function ($q) use ($name) {
                $q->where('name', 'like', '%' . $name . '%');
            });
        }

        $profiles = $query->orderByDesc('perfil_id')->paginate(20)->withQueryString();

        return view('profile.index', compact('profiles', 'user'));
    }

    public function edit($perfilId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $profile = ProfileUser::with('user')->findOrFail((int) $perfilId);

        if ((int) ($user->type_user ?? 0) !== 3 && (int) $profile->user_id !== (int) $user->id) {
            abort(403);
        }

        $genders = DB::table('base_genero_usuario')
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get();

        return view('profile.edit', compact('profile', 'user', 'genders'));
    }

    public function update(Request $request, $perfilId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $profile = ProfileUser::findOrFail((int) $perfilId);

        if ((int) ($user->type_user ?? 0) !== 3 && (int) $profile->user_id !== (int) $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'cpf' => ['nullable', 'string', 'max:20'],
            'rg' => ['nullable', 'string', 'max:20'],
            'telefone' => ['nullable', 'string', 'max:30'],
            'data_nascimento' => ['nullable', 'date'],
            'genero_id' => ['nullable', 'integer'],
            'estado_id' => ['nullable', 'integer'],
            'cidade_id' => ['nullable', 'integer'],
            'bairro_id' => ['nullable', 'integer'],
            'bairro_nome' => ['nullable', 'string', 'max:255'],
            'rua_id' => ['nullable', 'integer'],
            'bio' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('foto_perfil')) {
            $file = $request->file('foto_perfil');
            if ($file && $file->isValid()) {
                $mime = (string) $file->getMimeType();
                $allowedMimes = [
                    'image/jpeg',
                    'image/jpg',
                    'image/pjpeg',
                    'image/png',
                    'image/gif',
                    'image/webp',
                ];
                if (!in_array(strtolower($mime), $allowedMimes, true)) {
                    return back()
                        ->withErrors(['foto_perfil' => 'Envie uma imagem nos formatos JPG, PNG, GIF ou WEBP.'])
                        ->withInput();
                }

                $maxBytes = 5 * 1024 * 1024;
                if ($file->getSize() > $maxBytes) {
                    return back()
                        ->withErrors(['foto_perfil' => 'A imagem de perfil não pode ultrapassar 5MB.'])
                        ->withInput();
                }

                $userId = (int) $profile->user_id;
                $baseDir = public_path('uploads/perfis/' . $userId . '/perfil');
                if (!is_dir($baseDir)) {
                    mkdir($baseDir, 0775, true);
                }

                $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg';
                $extension = strtolower($extension);
                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    $extension = 'jpg';
                }

                $filename = Str::uuid()->toString() . '.' . $extension;
                $file->move($baseDir, $filename);

                if ($profile->foto_perfil) {
                    $old = ltrim($profile->foto_perfil, '/');
                    $oldPath = public_path($old);
                    if (is_file($oldPath) && str_contains($old, 'uploads/perfis')) {
                        @unlink($oldPath);
                    }
                }

                $relativePath = 'uploads/perfis/' . $userId . '/perfil/' . $filename;
                $data['foto_perfil'] = $relativePath;
            }
        }

        $profile->fill($data);
        $profile->save();

        return redirect()
            ->route('profile.edit', $profile->perfil_id)
            ->with('success', 'Perfil atualizado com sucesso.');
    }

    public function destroy($perfilId)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $profile = ProfileUser::findOrFail((int) $perfilId);

        if ((int) ($user->type_user ?? 0) !== 3 && (int) $profile->user_id !== (int) $user->id) {
            abort(403);
        }

        $profile->delete();

        return redirect()
            ->route('profile')
            ->with('success', 'Perfil inativado com sucesso.');
    }
}
