<?php

namespace App\Controllers\DashClient\Produtos;

use App\Controllers\BaseController;
use App\Models\Produtos\FamiliaProdutoModel;
use App\Models\Produtos\TipoProdutoModel;
use App\Models\Produtos\BaseProdutoModel;

class ConfigProdutoController extends BaseController
{
    protected $familiaModel;
    protected $tipoModel;
    protected $baseModel;

    public function __construct()
    {
        $this->familiaModel = new FamiliaProdutoModel();
        $this->tipoModel = new TipoProdutoModel();
        $this->baseModel = new BaseProdutoModel();
    }

    public function index()
    {
        $familias = $this->familiaModel->findAll();
        $tipos = $this->tipoModel->findAll();
        $bases = $this->baseModel->findAll();

        return view('dash_client/produtos/config/index', [
            'familias' => $familias,
            'tipos' => $tipos,
            'bases' => $bases
        ]);
    }
}
