<?php

namespace App\Controllers\DashUser\Eventos;

use App\Controllers\BaseController;
use App\Models\Eventos\EvtInteresseEventoModel;
use App\Models\User\PerfilUsuarioModel;

class InteresseController extends BaseController
{
    public function toggle($eventoId)
    {
        $session = session();
        if (!$session->get('logged')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }

        $userId = (int) $session->get('user_id');
        $eventoId = (int) $eventoId;
        
        // Get type from request body
        $json = $this->request->getJSON(true);
        $type = $json['type'] ?? 'like'; // 'like' or 'going'

        if (!in_array($type, ['like', 'going'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Tipo inválido']);
        }

        if ($eventoId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Evento inválido']);
        }

        $model = new EvtInteresseEventoModel();
        $existing = $model->where('evento_id', $eventoId)
                          ->where('user_id', $userId)
                          ->where('type', $type)
                          ->first();

        if ($existing) {
            // Remove (unlike/not going)
            $model->delete($existing['id']);
            $active = false;
        } else {
            // Add (like/going)
            $model->insert([
                'evento_id' => $eventoId,
                'user_id' => $userId,
                'type' => $type
            ]);
            $active = true;
        }

        // Count totals
        $likesCount = $model->where('evento_id', $eventoId)->where('type', 'like')->countAllResults();
        $goingCount = $model->where('evento_id', $eventoId)->where('type', 'going')->countAllResults();

        return $this->response->setJSON([
            'success' => true,
            'active' => $active,
            'likes_count' => $likesCount,
            'going_count' => $goingCount,
            'type' => $type
        ]);
    }

    public function listGoing($eventoId)
    {
        $session = session();
        if (!$session->get('logged')) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }
        
        $eventoId = (int) $eventoId;
        $model = new EvtInteresseEventoModel();
        
        // Join with user profile to get names and images
        $users = $model->select('evt_interesse_evento_tb.*, p.nome, p.foto_perfil')
                       ->join('perfil_usuario_tb p', 'p.user_id = evt_interesse_evento_tb.user_id')
                       ->where('evt_interesse_evento_tb.evento_id', $eventoId)
                       ->where('evt_interesse_evento_tb.type', 'going')
                       ->orderBy('evt_interesse_evento_tb.created_at', 'DESC')
                       ->findAll();
                       
        // Format image URLs
        foreach ($users as &$u) {
            if (!empty($u['foto_perfil'])) {
                $u['foto_perfil'] = '/' . ltrim(str_replace('\\', '/', $u['foto_perfil']), '/');
            } else {
                $u['foto_perfil'] = 'https://ui-avatars.com/api/?name=' . urlencode($u['nome']) . '&background=random';
            }
        }
        
        return $this->response->setJSON(['users' => $users]);
    }
}
