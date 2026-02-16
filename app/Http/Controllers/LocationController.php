<?php

namespace App\Http\Controllers;

use App\Models\BaseEstado;
use App\Models\BaseCidade;
use App\Models\BaseBairro;
use App\Models\BasePovoado;
use App\Models\BaseRua;
use App\Models\BaseRuaPrefixo;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function estados()
    {
        return response()->json(BaseEstado::where('status', 1)->orderBy('nome')->get(['id','nome','uf']));
    }

    public function cidades($estadoId)
    {
        return response()->json(BaseCidade::where('estado_id', (int)$estadoId)->orderBy('nome')->get(['id','nome']));
    }

    public function bairros($cidadeId)
    {
        return response()->json(BaseBairro::where('cidade_id', (int)$cidadeId)->orderBy('nome')->get(['id','nome']));
    }

    public function storeBairro(Request $request)
    {
        $data = $request->validate([
            'cidade_id' => 'required|exists:base_cidades,id',
            'nome' => 'required|string|max:150',
        ]);
        $exists = BaseBairro::where('cidade_id', $data['cidade_id'])->whereRaw('LOWER(nome) = ?', [mb_strtolower($data['nome'])])->first();
        if ($exists) {
            return response()->json($exists, 200);
        }
        $bairro = new BaseBairro();
        $bairro->cidade_id = $data['cidade_id'];
        $bairro->nome = $data['nome'];
        $bairro->save();
        return response()->json($bairro, 201);
    }

    public function povoados($cidadeId)
    {
        return response()->json(BasePovoado::where('cidade_id', (int)$cidadeId)->orderBy('nome')->get(['id','nome']));
    }

    public function prefixos()
    {
        return response()->json(BaseRuaPrefixo::orderBy('nome')->get(['prefixo_id','nome','sigla']));
    }

    public function ruas(Request $request)
    {
        $cidadeId = (int) $request->query('cidade_id');
        $bairroId = (int) $request->query('bairro_id', 0);
        $povoadoId = (int) $request->query('povoado_id', 0);
        $query = BaseRua::query()->where('cidade_id', $cidadeId);
        if ($bairroId > 0) {
            $query->where('bairro_id', $bairroId);
        }
        if ($povoadoId > 0) {
            $query->where('povoado_id', $povoadoId);
        }
        return response()->json($query->orderBy('nome')->limit(500)->get(['id','nome','bairro_id','povoado_id']));
    }
}
