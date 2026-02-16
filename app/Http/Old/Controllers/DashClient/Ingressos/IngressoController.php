<?php

namespace App\Controllers\DashClient\Ingressos;

use App\Controllers\BaseController;
use App\Models\Eventos\EvtEventoModel;
use App\Models\Eventos\EvtLoteIngressoModel;
use App\Models\Eventos\EvtIngressoVendidoModel;
use App\Models\Perfil\PerfilBarClientModel;
use App\Models\User\PerfilUsuarioModel;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class IngressoController extends BaseController
{
    protected $eventoModel;
    protected $loteModel;
    protected $ingressoVendidoModel;
    protected $barModel;
    protected $perfilUsuarioModel;

    public function __construct()
    {
        $this->eventoModel = new EvtEventoModel();
        $this->loteModel = new EvtLoteIngressoModel();
        $this->ingressoVendidoModel = new EvtIngressoVendidoModel();
        $this->barModel = new PerfilBarClientModel();
        $this->perfilUsuarioModel = new PerfilUsuarioModel();
    }

    private function checkPermission($barId)
    {
        $userId = session()->get('user_id');
        $bar = $this->barModel->where('user_id', $userId)->find($barId);
        return $bar;
    }

    // Listar eventos para gerenciar ingressos
    public function index($barId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $eventos = $this->eventoModel->where('bares_id', $barId)->findAll();

        return view('dash_client/ingressos/index', [
            'bar_id' => $barId,
            'eventos' => $eventos
        ]);
    }

    // Gerenciar lotes e vendas de um evento específico
    public function manage($barId, $eventoId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $evento = $this->eventoModel->find($eventoId);
        if (!$evento || $evento['bares_id'] != $barId) {
            return redirect()->back()->with('error', 'Evento não encontrado.');
        }

        $lotes = $this->loteModel->where('evento_id', $eventoId)->findAll();
        
        // Contagem de ingressos vendidos por lote
        foreach ($lotes as &$lote) {
            $vendidos = $this->ingressoVendidoModel->where('lote_id', $lote['lote_id'])->countAllResults();
            $lote['vendidos_real'] = $vendidos;
        }

        return view('dash_client/ingressos/manage', [
            'bar_id' => $barId,
            'evento' => $evento,
            'lotes' => $lotes
        ]);
    }

    // Formulário para criar novo lote
    public function createLote($barId, $eventoId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $evento = $this->eventoModel->find($eventoId);

        return view('dash_client/ingressos/cadastro_lote', [
            'bar_id' => $barId,
            'evento' => $evento
        ]);
    }

    // Salvar novo lote
    public function storeLote($barId, $eventoId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $data = [
            'evento_id' => $eventoId,
            'nome' => $this->request->getPost('nome'), // Ex: VIP, Pista, Estudante
            'preco' => str_replace(',', '.', $this->request->getPost('preco')),
            'quantidade_total' => $this->request->getPost('quantidade_total'),
            'quantidade_vendida' => 0,
            'data_inicio_vendas' => $this->request->getPost('data_inicio'),
            'data_fim_vendas' => $this->request->getPost('data_fim'),
            'ativo' => 1
        ];

        $this->loteModel->insert($data);

        return redirect()->to("/dashboard/perfil/bar/{$barId}/ingressos/manage/{$eventoId}")->with('success', 'Lote criado com sucesso.');
    }

    // Excluir lote
    public function deleteLote($barId, $eventoId, $loteId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $this->loteModel->delete($loteId);

        return redirect()->back()->with('success', 'Lote removido.');
    }

    // View de Insights (Lista de Compradores)
    public function show($barId, $eventoId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $evento = $this->eventoModel->find($eventoId);
        
        // Buscar todos os ingressos vendidos com detalhes do lote
        $vendas = $this->ingressoVendidoModel
            ->select('evt_ingressos_vendidos_tb.*, evt_lotes_ingressos_tb.nome as nome_lote')
            ->join('evt_lotes_ingressos_tb', 'evt_lotes_ingressos_tb.lote_id = evt_ingressos_vendidos_tb.lote_id')
            ->where('evt_ingressos_vendidos_tb.evento_id', $eventoId)
            ->orderBy('evt_ingressos_vendidos_tb.data_compra', 'DESC')
            ->findAll();

        return view('dash_client/ingressos/show', [
            'bar_id' => $barId,
            'evento' => $evento,
            'vendas' => $vendas
        ]);
    }

    // Simulação de Venda Manual (POS)
    public function venderManual($barId, $eventoId)
    {
        if (!$this->checkPermission($barId)) {
            return redirect()->back()->with('error', 'Permissão negada.');
        }

        $loteId = $this->request->getPost('lote_id');
        $cpf = $this->request->getPost('cpf_comprador');

        $lote = $this->loteModel->find($loteId);

        if (!$lote) {
            return redirect()->back()->with('error', 'Lote inválido.');
        }

        // Verifica se o CPF tem cadastro
        $perfil = $this->perfilUsuarioModel->where('cpf', $cpf)->first();

        if (!$perfil) {
            return redirect()->back()->with('error', 'Usuário não encontrado. O comprador deve ter cadastro no sistema.');
        }

        // Verifica se já comprou para este evento (opcional, regra de negócio)
        // $jaComprou = $this->ingressoVendidoModel->where('evento_id', $eventoId)->where('user_id', $perfil['user_id'])->first();
        // if ($jaComprou) { return redirect()->back()->with('error', 'Este usuário já possui ingresso.'); }

        // Gerar código único
        $codigo = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

        $this->ingressoVendidoModel->insert([
            'evento_id' => $eventoId,
            'lote_id' => $loteId,
            'user_id' => $perfil['user_id'],
            'nome_comprador' => $this->request->getPost('nome_comprador'), // Ou pegar do user->name
            'email_comprador' => $this->request->getPost('email_comprador'),
            'codigo_unico' => $codigo,
            'status' => 'pago',
            'valor_pago' => $lote['preco'],
            'data_compra' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Ingresso vendido. Código: ' . $codigo);
    }
    
    public function gerarQrCode($codigo)
    {
        $qrCode = new \Endroid\QrCode\QrCode((string) $codigo);
        $writer = new PngWriter();
        $png = $writer->write($qrCode)->getString();
        if (isset($this->response) && $this->response) {
            return $this->response->setHeader('Content-Type', 'image/png')->setBody($png);
        }
        return $png;
    }
}
