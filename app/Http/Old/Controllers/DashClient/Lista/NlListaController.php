<?php

namespace App\Controllers\DashClient\Lista;

use App\Controllers\BaseController;
use App\Models\Lista\NlListaModel;
use App\Models\Lista\NlBeneficioModel;
use App\Models\Lista\NlListaUsuarioModel;
use App\Models\Perfil\PerfilBarClientModel;

class NlListaController extends BaseController
{
    protected $listaModel;
    protected $beneficioModel;
    protected $listaUsuarioModel;
    protected $barModel;

    public function __construct()
    {
        $this->listaModel = new NlListaModel();
        $this->beneficioModel = new NlBeneficioModel();
        $this->listaUsuarioModel = new NlListaUsuarioModel();
        $this->barModel = new PerfilBarClientModel();
    }

    private function checkPermission($barId)
    {
        $userId = session()->get('user_id');
        $bar = $this->barModel->where('user_id', $userId)->find($barId);
        return $bar;
    }

    public function index($barId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $listas = $this->listaModel->where('bares_id', $barId)->orderBy('created_at', 'DESC')->findAll();

        return view('dash_client/lista/index', [
            'bar_id' => $barId,
            'listas' => $listas
        ]);
    }

    public function create($barId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        return view('dash_client/lista/cadastro', ['bar_id' => $barId]);
    }

    public function store($barId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $data = [
            'bares_id' => $barId,
            'nome' => $this->request->getPost('nome'),
            'descricao' => $this->request->getPost('descricao'),
            'tipo' => $this->request->getPost('tipo'),
            'ativo' => $this->request->getPost('ativo') ? 1 : 0,
            'data_inicio' => $this->request->getPost('data_inicio'),
            'data_fim' => $this->request->getPost('data_fim')
        ];

        if ($this->listaModel->insert($data)) {
            return redirect()->to("/dashboard/perfil/bar/{$barId}/listas")->with('success', 'Lista/Evento criado com sucesso.');
        }

        return redirect()->back()->withInput()->with('error', 'Erro ao criar lista.');
    }

    public function edit($barId, $listaId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $lista = $this->listaModel->find($listaId);
        if (!$lista || $lista['bares_id'] != $barId) {
            return redirect()->back()->with('error', 'Lista não encontrada.');
        }

        return view('dash_client/lista/edit', ['bar_id' => $barId, 'lista' => $lista]);
    }

    public function update($barId, $listaId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $data = [
            'nome' => $this->request->getPost('nome'),
            'descricao' => $this->request->getPost('descricao'),
            'tipo' => $this->request->getPost('tipo'),
            'ativo' => $this->request->getPost('ativo') ? 1 : 0,
            'data_inicio' => $this->request->getPost('data_inicio'),
            'data_fim' => $this->request->getPost('data_fim')
        ];

        if ($this->listaModel->update($listaId, $data)) {
            return redirect()->to("/dashboard/perfil/bar/{$barId}/listas")->with('success', 'Lista atualizada com sucesso.');
        }

        return redirect()->back()->withInput()->with('error', 'Erro ao atualizar lista.');
    }

    public function show($barId, $listaId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $lista = $this->listaModel->find($listaId);
        if (!$lista || $lista['bares_id'] != $barId) {
            return redirect()->back()->with('error', 'Lista não encontrada.');
        }

        $beneficios = $this->beneficioModel->where('lista_id', $listaId)->findAll();
        // Aqui você pode adicionar lógica para buscar os usuários da lista com join na tabela users
        // Por enquanto, vou apenas buscar os registros da tabela de ligação
        $usuarios = $this->listaUsuarioModel->where('lista_id', $listaId)->findAll(); 

        return view('dash_client/lista/show', [
            'bar_id' => $barId,
            'lista' => $lista,
            'beneficios' => $beneficios,
            'usuarios' => $usuarios
        ]);
    }

    public function delete($barId, $listaId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }
        
        $lista = $this->listaModel->find($listaId);
        if (!$lista || $lista['bares_id'] != $barId) {
            return redirect()->back()->with('error', 'Lista não encontrada.');
        }
        
        $this->listaModel->delete($listaId);
        
        return redirect()->to("/dashboard/perfil/bar/{$barId}/listas")->with('success', 'Lista removida.');
    }
}
