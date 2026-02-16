<?php

namespace App\Http\Controllers;

use App\Models\Establishment;
use App\Models\EstablishmentType;
use App\Models\EstablishmentSocialLink;
use App\Models\EstablishmentFacility;
use App\Models\BaseEstado;
use App\Models\BaseCidade;
use App\Models\BaseBairro;
use App\Models\BasePovoado;
use App\Models\BaseRua;
use App\Models\BaseRuaPrefixo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EstablishmentController extends Controller
{
    public function index()
    {
        $establishments = Establishment::where('user_id', Auth::id())->get();
        return view('dashboard.barista.establishments.index', compact('establishments'));
    }

    public function create()
    {
        $types = EstablishmentType::where('ativo', 1)->get();
        $facilities = EstablishmentFacility::where('ativo', 1)->orderBy('ordem')->orderBy('nome')->get();
        return view('dashboard.barista.establishments.create', compact('types', 'facilities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:150',
            'endereco' => 'required|string|max:255',
            'tipo_bar' => 'required|exists:form_perfil_tipo_bar_tb,tipo_bar_id',
            'imagem' => 'nullable|image|max:2048',
            'estado_id' => 'nullable|exists:base_estados,id',
            'cidade_id' => 'nullable|exists:base_cidades,id',
            'bairro_id' => 'nullable|exists:base_bairros,id',
            'povoado_id' => 'nullable|exists:base_povoados,id',
            'prefixo_rua_id' => 'nullable|exists:base_ruas_prefixos,prefixo_id',
            'rua_id' => 'nullable|exists:base_ruas,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'bebidas' => 'nullable|string|max:255',
            'beneficios' => 'nullable|string',
            'capacidade' => 'nullable|integer',
            'facilities' => 'nullable|array',
            'facilities.*' => 'integer|exists:establishment_facilities,id',
            'socials_type' => 'nullable|array',
            'socials_type.*' => 'nullable|string|max:50',
            'socials_value' => 'nullable|array',
            'socials_value.*' => 'nullable|string|max:255',
        ]);

        $data = $request->except('imagem');
        $data['user_id'] = Auth::id();
        $data['status'] = 'ativo';
        
        $data['nome_na_lista'] = $request->has('nome_na_lista');

        $establishment = Establishment::create($data);

        $types = $request->input('socials_type', []);
        $values = $request->input('socials_value', []);
        $socials = [];
        foreach ($types as $idx => $network) {
            $handle = $values[$idx] ?? null;
            $network = trim((string)$network);
            $handle = trim((string)$handle);
            if ($network === '' || $handle === '') {
                continue;
            }
            $socials[] = [
                'bares_id' => $establishment->bares_id,
                'network' => $network,
                'handle' => $handle,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($socials)) {
            EstablishmentSocialLink::insert($socials);
        }

        $facilities = $request->input('facilities', []);
        if (is_array($facilities)) {
            $establishment->facilities()->sync($facilities);
        }

        if ($request->hasFile('imagem')) {
            $image = $request->file('imagem');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $slug = Str::slug($establishment->nome);
            $path = "uploads/bares/{$establishment->bares_id}_{$slug}/perfil";
            $fullPath = public_path($path);
            if (!is_dir($fullPath)) {
                @mkdir($fullPath, 0775, true);
            }
            
            $image->move($fullPath, $filename);
            
            $establishment->update(['imagem' => $path . '/' . $filename]);
        }

        return redirect()->route('dashboard.barista.establishments.index')
            ->with('success', 'Estabelecimento criado com sucesso!');
    }

    public function edit($id)
    {
        $establishment = Establishment::where('user_id', Auth::id())->findOrFail($id);
        $types = EstablishmentType::where('ativo', 1)->get();
        $facilities = EstablishmentFacility::where('ativo', 1)->orderBy('ordem')->orderBy('nome')->get();
        $selectedFacilities = $establishment->facilities()->pluck('establishment_facilities.id')->all();
        $socialLinks = $establishment->socialLinks()->orderBy('network')->orderBy('id')->get();
        $initialSocials = $socialLinks->map(function ($link) {
            return ['network' => $link->network, 'handle' => $link->handle];
        })->values()->all();
        return view('dashboard.barista.establishments.edit', compact('establishment', 'types', 'facilities', 'selectedFacilities', 'socialLinks', 'initialSocials'));
    }

    public function update(Request $request, $id)
    {
        $establishment = Establishment::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'nome' => 'required|string|max:150',
            'endereco' => 'required|string|max:255',
            'tipo_bar' => 'required|exists:form_perfil_tipo_bar_tb,tipo_bar_id',
            'imagem' => 'nullable|image|max:2048',
            'estado_id' => 'nullable|exists:base_estados,id',
            'cidade_id' => 'nullable|exists:base_cidades,id',
            'bairro_id' => 'nullable|exists:base_bairros,id',
            'povoado_id' => 'nullable|exists:base_povoados,id',
            'prefixo_rua_id' => 'nullable|exists:base_ruas_prefixos,prefixo_id',
            'rua_id' => 'nullable|exists:base_ruas,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'bebidas' => 'nullable|string|max:255',
            'beneficios' => 'nullable|string',
            'capacidade' => 'nullable|integer',
            'facilities' => 'nullable|array',
            'facilities.*' => 'integer|exists:establishment_facilities,id',
            'socials_type' => 'nullable|array',
            'socials_type.*' => 'nullable|string|max:50',
            'socials_value' => 'nullable|array',
            'socials_value.*' => 'nullable|string|max:255',
        ]);

        $data = $request->except('imagem');
        $data['nome_na_lista'] = $request->has('nome_na_lista');

        $establishment->update($data);

        EstablishmentSocialLink::where('bares_id', $establishment->bares_id)->delete();
        $types = $request->input('socials_type', []);
        $values = $request->input('socials_value', []);
        $socials = [];
        foreach ($types as $idx => $network) {
            $handle = $values[$idx] ?? null;
            $network = trim((string)$network);
            $handle = trim((string)$handle);
            if ($network === '' || $handle === '') {
                continue;
            }
            $socials[] = [
                'bares_id' => $establishment->bares_id,
                'network' => $network,
                'handle' => $handle,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($socials)) {
            EstablishmentSocialLink::insert($socials);
        }

        $facilities = $request->input('facilities', []);
        if (is_array($facilities)) {
            $establishment->facilities()->sync($facilities);
        } else {
            $establishment->facilities()->sync([]);
        }

        if ($request->hasFile('imagem')) {
            if ($establishment->imagem && file_exists(public_path($establishment->imagem))) {
                unlink(public_path($establishment->imagem));
            }

            $image = $request->file('imagem');
            $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $slug = Str::slug($establishment->nome);
            $path = "uploads/bares/{$establishment->bares_id}_{$slug}/perfil";
            $fullPath = public_path($path);
            if (!is_dir($fullPath)) {
                @mkdir($fullPath, 0775, true);
            }
            
            $image->move($fullPath, $filename);
            
            $establishment->update(['imagem' => $path . '/' . $filename]);
        }

        return redirect()->route('dashboard.barista.establishments.index')
            ->with('success', 'Estabelecimento atualizado com sucesso!');
    }

    public function show($id)
    {
        $establishment = Establishment::where('user_id', Auth::id())->findOrFail($id);
        $geo = [
            'estado' => $establishment->estado_id ? BaseEstado::find($establishment->estado_id) : null,
            'cidade' => $establishment->cidade_id ? BaseCidade::find($establishment->cidade_id) : null,
            'bairro' => $establishment->bairro_id ? BaseBairro::find($establishment->bairro_id) : null,
            'povoado' => $establishment->povoado_id ? BasePovoado::find($establishment->povoado_id) : null,
            'rua' => $establishment->rua_id ? BaseRua::find($establishment->rua_id) : null,
            'prefixo' => $establishment->prefixo_rua_id ? BaseRuaPrefixo::find($establishment->prefixo_rua_id) : null,
        ];
        return view('dashboard.barista.establishments.show', compact('establishment', 'geo'));
    }

    public function destroy($id)
    {
        $establishment = Establishment::where('user_id', Auth::id())->findOrFail($id);
        $establishment->status = 'inativo';
        $establishment->save();
        $establishment->delete();
        
        return redirect()->route('dashboard.barista.establishments.index')
            ->with('success', 'Estabelecimento inativado com sucesso!');
    }
}
