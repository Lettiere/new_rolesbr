<?php
namespace App\Controllers\DashClient\Eventos;
use App\Controllers\BaseController;
use App\Models\Eventos\EvtAlbumModel;
use App\Models\Eventos\EvtAlbumFotoModel;
use App\Models\Eventos\EvtEventoModel;
use App\Models\Perfil\PerfilBarClientModel;

class EvtAlbumController extends BaseController
{
    /**
     * Lista álbuns de um evento específico
     */
    public function listByEvento($eventoId)
    {
        $model = new EvtAlbumModel();
        $albums = $model->where('evento_id', (int)$eventoId)->orderBy('created_at','DESC')->findAll();
        return $this->response->setJSON(['items'=>$albums]);
    }
    public function index($eventoId)
    {
        $model = new EvtAlbumModel();
        $albuns = $model->where('evento_id',(int)$eventoId)->orderBy('created_at','DESC')->findAll();
        return view('dash_client/eventos/albuns/index', ['evento_id'=>(int)$eventoId,'albuns'=>$albuns]);
    }

    /**
     * Busca eventos de um bar específico para exibir no modal
     */
    public function getEventosByBar($barId)
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }

        // Verificar se o bar pertence ao usuário
        $barModel = new PerfilBarClientModel();
        $bar = $barModel->where('user_id', $userId)->find((int)$barId);
        if (!$bar) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Permissão negada']);
        }

        $evtModel = new EvtEventoModel();
        $eventos = $evtModel->where('bares_id', (int)$barId)
            ->orderBy('data_inicio', 'DESC')
            ->findAll();

        // Formatar dados dos eventos
        $eventosFormatados = [];
        foreach ($eventos as $evt) {
            $eventosFormatados[] = [
                'evento_id' => (int)$evt['evento_id'],
                'nome' => $evt['nome'],
                'data_inicio' => $evt['data_inicio'],
                'data_fim' => $evt['data_fim'] ?? null,
                'descricao' => $evt['descricao'] ?? '',
                'status' => $evt['status'] ?? 'rascunho'
            ];
        }

        return $this->response->setJSON(['success' => true, 'eventos' => $eventosFormatados]);
    }

    /**
     * Cria um novo álbum a partir de um evento
     */
    public function store()
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }

        $eventoId = (int) ($this->request->getPost('evento_id') ?? 0);
        if (!$eventoId) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Evento é obrigatório']);
        }

        // Verificar se o evento existe e pertence ao usuário
        $evtModel = new EvtEventoModel();
        $evento = $evtModel->find($eventoId);
        if (!$evento) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Evento não encontrado']);
        }

        // Verificar permissão
        $barModel = new PerfilBarClientModel();
        $bar = $barModel->where('user_id', $userId)->find($evento['bares_id']);
        if (!$bar) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Permissão negada']);
        }

        // Criar álbum com dados do evento
        $model = new EvtAlbumModel();
        $albumId = $model->insert([
            'evento_id' => $eventoId,
            'fotografo_id' => $userId,
            'titulo' => $evento['nome'] ?? ('Álbum do Evento #' . $eventoId),
            'descricao' => $evento['descricao'] ?? '',
            'data_fotografia' => !empty($evento['data_inicio']) ? date('Y-m-d', strtotime($evento['data_inicio'])) : date('Y-m-d'),
            'status' => 'rascunho',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        if (!$albumId) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Erro ao criar álbum']);
        }

        // Redirecionar para a view do álbum
        return $this->response->setJSON([
            'success' => true,
            'album_id' => $albumId,
            'evento_id' => $eventoId,
            'redirect' => "/dashboard/perfil/eventos/{$eventoId}/albuns/show/{$albumId}"
        ]);
    }
    public function create($eventoId)
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        $barModel = new PerfilBarClientModel();
        $evtModel = new EvtEventoModel();
        $eventos = [];
        if ($userId) {
            $bares = $barModel->where('user_id',$userId)->findAll();
            foreach ($bares as $b) {
                $list = $evtModel->where('bares_id',(int)$b['bares_id'])->orderBy('data_inicio','DESC')->findAll();
                foreach ($list as $ev) {
                    $eventos[] = [
                        'evento_id'=>(int)$ev['evento_id'],
                        'nome'=>$ev['nome'],
                        'data_inicio'=>$ev['data_inicio'] ?? null,
                        'created_at'=>$ev['created_at'] ?? null,
                    ];
                }
            }
        }
        return view('dash_client/eventos/albuns/create', ['evento_id'=>(int)$eventoId,'eventos'=>$eventos]);
    }
    public function createFromEvent($eventoId)
    {
        $this->request->setGlobal('post',['evento_id'=>(int)$eventoId]);
        return $this->store();
    }
    public function edit($eventoId, $albumId)
    {
        $model = new EvtAlbumModel();
        $album = $model->find((int)$albumId);
        if (!$album) return redirect()->back()->with('error','Álbum não encontrado');
        return view('dash_client/eventos/albuns/edit', ['evento_id'=>$eventoId,'album'=>$album]);
    }
    public function update($eventoId, $albumId)
    {
        $model = new EvtAlbumModel();
        $titulo = trim($this->request->getPost('titulo') ?? '');
        $descricao = trim($this->request->getPost('descricao') ?? '');
        $status = $this->request->getPost('status') ?? 'rascunho';
        $ok = $model->update((int)$albumId, [
            'titulo'=>$titulo,'descricao'=>$descricao,'status'=>$status,'updated_at'=>date('Y-m-d H:i:s')
        ]);
        if (!$ok) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON(['error'=>'Erro ao atualizar álbum']);
            }
            return redirect()->back()->with('error','Erro ao atualizar álbum');
        }
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success'=>true]);
        }
        return redirect()->to("/dashboard/perfil/eventos/{$eventoId}/albuns/show/{$albumId}")->with('success','Álbum atualizado');
    }
    public function delete($eventoId, $albumId)
    {
        $model = new EvtAlbumModel();
        $fotoModel = new EvtAlbumFotoModel();
        $fotos = $fotoModel->where('album_id',(int)$albumId)->findAll();
        foreach ($fotos as $f) {
            $docroot = rtrim(str_replace(['\\','/'],'/', ROOTPATH.'public'), '/');
            $rel = str_replace(['\\','/'],'/', (string)$f['nome_arquivo']);
            if (strpos($rel, $docroot.'/') === 0) $rel = substr($rel, strlen($docroot.'/'));
            $path = ROOTPATH.'public'.DIRECTORY_SEPARATOR.ltrim(str_replace('/','\\',$rel),'\\');
            $thumb = preg_replace('/(\\.[a-z0-9]+)$/i','_thumb$1',$path);
            if (is_file($path)) @unlink($path);
            if (is_file($thumb)) @unlink($thumb);
        }
        $fotoModel->where('album_id',(int)$albumId)->delete();
        $model->delete((int)$albumId);
        return $this->response->setJSON(['success'=>true]);
    }
    public function show($eventoId, $albumId)
    {
        $model = new EvtAlbumModel();
        $fotoModel = new EvtAlbumFotoModel();
        $album = $model->find((int)$albumId);
        if (!$album) return redirect()->back()->with('error','Álbum não encontrado');
        $fotos = $fotoModel->where('album_id',(int)$albumId)->orderBy('ordem','ASC')->findAll();
        return view('dash_client/eventos/albuns/show', ['evento_id'=>$eventoId,'album'=>$album,'fotos'=>$fotos]);
    }

    public function purgeExpired()
    {
        $model = new EvtAlbumModel();
        $fotoModel = new EvtAlbumFotoModel();
        $limitDate = date('Y-m-d H:i:s', strtotime('-12 months'));
        $expired = $model->where('created_at <', $limitDate)->findAll();
        foreach ($expired as $album) {
            $albumId = (int)($album['album_id'] ?? 0);
            if (!$albumId) continue;
            $fotos = $fotoModel->where('album_id', $albumId)->findAll();
            foreach ($fotos as $f) {
                $docroot = rtrim(str_replace(['\\','/'],'/', ROOTPATH.'public'), '/');
                $rel = str_replace(['\\','/'],'/', (string)$f['nome_arquivo']);
                if (strpos($rel, $docroot.'/') === 0) $rel = substr($rel, strlen($docroot.'/'));
                $path = ROOTPATH.'public'.DIRECTORY_SEPARATOR.ltrim(str_replace('/','\\',$rel),'\\');
                $thumb = preg_replace('/(\\.[a-z0-9]+)$/i','_thumb$1',$path);
                if (is_file($path)) @unlink($path);
                if (is_file($thumb)) @unlink($thumb);
            }
            $fotoModel->where('album_id', $albumId)->delete();
            $model->delete($albumId);
        }
        return $this->response->setJSON(['success'=>true,'purged'=>count($expired)]);
    }
}
