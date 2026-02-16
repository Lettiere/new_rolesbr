<?php
namespace App\Controllers\DashClient\Eventos;
use App\Controllers\BaseController;
use App\Models\Eventos\EvtAlbumFotoModel;
use App\Models\Eventos\EvtAlbumModel;
use App\Models\Eventos\EvtFotoLikeModel;
use App\Models\Eventos\EvtFotoComentarioModel;
use App\Models\Eventos\EvtFotoCompartilhamentoModel;
class EvtFotoController extends BaseController
{
    protected function isAllowedOrientation(int $w, int $h): bool
    {
        if ($w <= 0 || $h <= 0) return false;
        $r = $w / $h;
        $ratio169 = 16/9;
        $ratio916 = 9/16;
        $tol = 0.03;
        return (abs($r - $ratio169) <= $tol) || (abs($r - $ratio916) <= $tol);
    }
    protected function resizeToWidth(string $absolutePath, int $targetW = 1024): string
    {
        $info = @getimagesize($absolutePath);
        if (!$info) throw new \RuntimeException('Imagem inválida');
        $w = (int)$info[0]; $h = (int)$info[1];
        $mime = strtolower($info['mime'] ?? 'image/jpeg');
        $src = null;
        if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) $src = @imagecreatefromjpeg($absolutePath);
        elseif ($mime === 'image/png' && function_exists('imagecreatefrompng')) $src = @imagecreatefrompng($absolutePath);
        elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) $src = @imagecreatefromwebp($absolutePath);
        else { $data = @file_get_contents($absolutePath); if ($data) $src = @imagecreatefromstring($data); }
        if (!$src) throw new \RuntimeException('Falha ao carregar imagem');
        $ratio = $targetW / $w;
        $newW = $targetW;
        $newH = max(1,(int)floor($h * $ratio));
        $dst = imagecreatetruecolor($newW,$newH);
        $white = imagecolorallocate($dst,255,255,255); imagefilledrectangle($dst,0,0,$newW,$newH,$white);
        if (!imagecopyresampled($dst,$src,0,0,0,0,$newW,$newH,$w,$h)) { imagedestroy($src); imagedestroy($dst); throw new \RuntimeException('Falha ao redimensionar'); }
        $baseName = dirname($absolutePath).DIRECTORY_SEPARATOR.pathinfo($absolutePath, PATHINFO_FILENAME).'.jpg';
        $qualities=[85,80,75,70,65,60];
        $saved=false;
        foreach($qualities as $q){ if (@imagejpeg($dst,$baseName,$q) && @filesize($baseName)>0) { $saved=true; break; } }
        imagedestroy($src); imagedestroy($dst);
        if (!$saved) throw new \RuntimeException('Falha ao salvar');
        return $baseName;
    }
    protected function makeThumbSquare(string $absolutePath): string
    {
        $info = @getimagesize($absolutePath);
        if (!$info) throw new \RuntimeException('Imagem inválida');
        $w = (int)$info[0]; $h = (int)$info[1];
        $mime = strtolower($info['mime'] ?? 'image/jpeg');
        $src = null;
        if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) $src = @imagecreatefromjpeg($absolutePath);
        elseif ($mime === 'image/png' && function_exists('imagecreatefrompng')) $src = @imagecreatefrompng($absolutePath);
        elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) $src = @imagecreatefromwebp($absolutePath);
        else { $data = @file_get_contents($absolutePath); if ($data) $src = @imagecreatefromstring($data); }
        if (!$src) throw new \RuntimeException('Falha ao carregar imagem');
        $side = min($w,$h);
        $x = (int)floor(($w - $side)/2);
        $y = (int)floor(($h - $side)/2);
        $crop = imagecreatetruecolor($side,$side);
        $white = imagecolorallocate($crop,255,255,255); imagefilledrectangle($crop,0,0,$side,$side,$white);
        if (!imagecopy($crop,$src,0,0,$x,$y,$side,$side)) { imagedestroy($src); imagedestroy($crop); throw new \RuntimeException('Falha ao cortar'); }
        $thumbSize = 512;
        $dst = imagecreatetruecolor($thumbSize,$thumbSize);
        $white2 = imagecolorallocate($dst,255,255,255); imagefilledrectangle($dst,0,0,$thumbSize,$thumbSize,$white2);
        if (!imagecopyresampled($dst,$crop,0,0,0,0,$thumbSize,$thumbSize,$side,$side)) { imagedestroy($src); imagedestroy($crop); imagedestroy($dst); throw new \RuntimeException('Falha ao redimensionar'); }
        $thumbPath = preg_replace('/(\\.[a-z0-9]+)$/i','_thumb.jpg',$absolutePath);
        if (!@imagejpeg($dst,$thumbPath,80) || @filesize($thumbPath)===0) { imagedestroy($src); imagedestroy($crop); imagedestroy($dst); throw new \RuntimeException('Falha ao salvar thumbnail'); }
        imagedestroy($src); imagedestroy($crop); imagedestroy($dst);
        return $thumbPath;
    }
    public function list($albumId)
    {
        $fotoModel = new EvtAlbumFotoModel();
        $fotos = $fotoModel->where('album_id',(int)$albumId)->orderBy('ordem','ASC')->findAll();
        return $this->response->setJSON(['items'=>$fotos]);
    }
    public function upload($albumId)
    {
        $userId = (int)(session()->get('user_id') ?? 0);
        if (!$userId) return $this->response->setStatusCode(401)->setJSON(['error'=>'Não autenticado']);
        $files = $this->request->getFileMultiple('fotos');
        if (empty($files)) return $this->response->setStatusCode(400)->setJSON(['error'=>'Nenhum arquivo']);
        $baseDir = ROOTPATH.'public/uploads/eventos/albuns/'.(int)$albumId;
        if (!is_dir($baseDir)) @mkdir($baseDir,0777,true);
        $fotoModel = new EvtAlbumFotoModel();
        $saved = [];
        $errors = [];
        foreach ($files as $file) {
            if (!$file || !$file->isValid() || $file->hasMoved()) { $errors[] = 'Arquivo inválido'; continue; }
            if ((int)$file->getSize() > (int)(2.5*1024*1024) || (int)$file->getSize()===0) { $errors[] = 'Tamanho inválido'; continue; }
            $ext = strtolower($file->getClientExtension() ?: $file->getExtension());
            $mime = strtolower($file->getMimeType() ?: '');
            $allowedExts = ['jpg','jpeg','png','webp'];
            $allowedMimes = ['image/jpeg','image/jpg','image/pjpeg','image/png','image/webp'];
            if (!in_array($ext,$allowedExts) || !in_array($mime,$allowedMimes)) { $errors[] = 'Formato não permitido'; continue; }
            $name = $file->getRandomName();
            if (!$file->move($baseDir,$name)) { $errors[] = 'Falha ao mover arquivo'; continue; }
            $temp = $baseDir.DIRECTORY_SEPARATOR.$name;
            try {
                $final = $this->resizeToWidth($temp,1024);
                $thumb = $this->makeThumbSquare($final);
                $docroot = rtrim(str_replace(['\\','/'],'/', ROOTPATH.'public'), '/');
                $finalNorm = str_replace(['\\','/'],'/', $final);
                if (strpos($finalNorm, $docroot.'/') === 0) {
                    $rel = substr($finalNorm, strlen($docroot.'/'));
                } else {
                    $rel = 'uploads/eventos/albuns/'.(int)$albumId.'/'.basename($finalNorm);
                }
                $fotoId = $fotoModel->insert([
                    'album_id'=>(int)$albumId,
                    'nome_arquivo'=>$rel,
                    'titulo'=>null,
                    'descricao'=>null,
                    'ordem'=>0,
                    'eh_thumbnail'=>0,
                    'created_at'=>date('Y-m-d H:i:s'),
                ]);
                if ($fotoId) $saved[] = $fotoId;
                if (is_file($temp) && $temp !== $final) @unlink($temp);
            } catch (\Throwable $e) {
                @unlink($temp);
                log_message('error', 'Erro ao processar foto: ' . $e->getMessage());
                $errors[] = 'Erro ao processar: '.$e->getMessage();
            }
        }
        return $this->response->setJSON(['success'=>true,'ids'=>$saved,'errors'=>$errors]);
    }
    public function deleteSelected($albumId)
    {
        $ids = (array)$this->request->getPost('ids');
        $fotoModel = new EvtAlbumFotoModel();
        foreach ($ids as $id) {
            $f = $fotoModel->find((int)$id);
            if (!$f) continue;
            $docroot = rtrim(str_replace(['\\','/'],'/', ROOTPATH.'public'), '/');
            $rel = str_replace(['\\','/'],'/', (string)$f['nome_arquivo']);
            if (strpos($rel, $docroot.'/') === 0) $rel = substr($rel, strlen($docroot.'/'));
            $path = ROOTPATH.'public'.DIRECTORY_SEPARATOR.ltrim(str_replace('/','\\',$rel),'\\');
            $thumb = preg_replace('/(\\.[a-z0-9]+)$/i','_thumb.jpg',$path);
            if (is_file($path)) @unlink($path);
            if (is_file($thumb)) @unlink($thumb);
            $fotoModel->delete((int)$id);
        }
        return $this->response->setJSON(['success'=>true]);
    }
    public function like($fotoId)
    {
        $userId = (int)(session()->get('user_id') ?? 0);
        if (!$userId) return $this->response->setStatusCode(401)->setJSON(['error'=>'Não autenticado']);
        $model = new EvtFotoLikeModel();
        $exists = $model->where('foto_id',(int)$fotoId)->where('user_id',$userId)->first();
        if ($exists) { $model->delete($exists['id']); return $this->response->setJSON(['liked'=>false]); }
        $id = $model->insert(['foto_id'=>(int)$fotoId,'user_id'=>$userId,'created_at'=>date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['liked'=>true,'id'=>$id]);
    }
    public function comentarios($fotoId)
    {
        $limit  = (int) ($this->request->getGet('limit') ?? 10);
        $offset = (int) ($this->request->getGet('offset') ?? 0);
        $db = \Config\Database::connect();
        $builder = $db->table('evt_foto_comentarios_tb c')
            ->select('c.comentario_id, c.foto_id, c.user_id, c.comentario, c.status, c.created_at, u.name AS user_name, pu.foto_perfil AS avatar_url')
            ->join('users u', 'u.id = c.user_id', 'left')
            ->join('perfil_usuarios_tb pu', 'pu.user_id = c.user_id', 'left')
            ->where('c.foto_id', (int)$fotoId)
            ->where('c.status', 'aprovado')
            ->orderBy('c.created_at', 'ASC');
        $items = $builder->limit($limit, $offset)->get()->getResultArray();
        $count = $db->table('evt_foto_comentarios_tb')->where('foto_id', (int)$fotoId)->where('status', 'aprovado')->countAllResults();
        return $this->response->setJSON(['items'=>$items,'count'=>$count,'limit'=>$limit,'offset'=>$offset]);
    }
    public function comentarStore($fotoId)
    {
        $userId = (int)(session()->get('user_id') ?? 0);
        if (!$userId) return $this->response->setStatusCode(401)->setJSON(['error'=>'Não autenticado']);
        $txt = trim($this->request->getPost('comentario') ?? '');
        if ($txt==='') return $this->response->setStatusCode(422)->setJSON(['error'=>'Comentário vazio']);
        $id = (new EvtFotoComentarioModel())->insert([
            'foto_id'=>(int)$fotoId,'user_id'=>$userId,'comentario'=>$txt,'status'=>'aprovado','created_at'=>date('Y-m-d H:i:s')
        ]);
        return $this->response->setJSON(['success'=>true,'id'=>$id]);
    }
    public function comentarEdit($fotoId, $comentarioId)
    {
        $userId = (int)(session()->get('user_id') ?? 0);
        if (!$userId) return $this->response->setStatusCode(401)->setJSON(['error'=>'Não autenticado']);
        $txt = trim($this->request->getPost('comentario') ?? '');
        $status = $this->request->getPost('status') ?? 'aprovado';
        $model = new EvtFotoComentarioModel();
        $ok = $model->update((int)$comentarioId, ['comentario'=>$txt,'status'=>$status,'updated_at'=>date('Y-m-d H:i:s')]);
        if (!$ok) return $this->response->setStatusCode(500)->setJSON(['error'=>'Erro ao editar']);
        return $this->response->setJSON(['success'=>true]);
    }
    public function comentarDelete($fotoId, $comentarioId)
    {
        $model = new EvtFotoComentarioModel();
        $model->delete((int)$comentarioId);
        return $this->response->setJSON(['success'=>true]);
    }
    public function compartilhar($fotoId)
    {
        $userId = (int)(session()->get('user_id') ?? 0);
        $canal = $this->request->getPost('canal') ?? 'link';
        $id = (new EvtFotoCompartilhamentoModel())->insert(['foto_id'=>(int)$fotoId,'user_id'=>$userId ?: null,'canal'=>$canal,'created_at'=>date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['success'=>true,'id'=>$id]);
    }

    public function counts($fotoId)
    {
        $likes = (new EvtFotoLikeModel())->where('foto_id',(int)$fotoId)->countAllResults();
        $comments = (new EvtFotoComentarioModel())->where('foto_id',(int)$fotoId)->where('status','aprovado')->countAllResults();
        return $this->response->setJSON(['likes'=>$likes,'comments'=>$comments]);
    }
    public function reorder($albumId)
    {
        $order = (array) $this->request->getPost('order');
        $fotoModel = new EvtAlbumFotoModel();
        $pos = 0;
        foreach ($order as $id) {
            $idNum = (int)$id;
            if ($idNum <= 0) continue;
            $f = $fotoModel->find($idNum);
            if (!$f || (int)$f['album_id'] !== (int)$albumId) continue;
            $fotoModel->update($idNum, ['ordem'=>$pos]);
            $pos++;
        }
        return $this->response->setJSON(['success'=>true]);
    }
    public function setCover($albumId, $fotoId)
    {
        $albumId = (int)$albumId;
        $fotoId = (int)$fotoId;
        $fotoModel = new EvtAlbumFotoModel();
        $albumModel = new EvtAlbumModel();
        $foto = $fotoModel->find($fotoId);
        if (!$foto || (int)$foto['album_id'] !== $albumId) return $this->response->setStatusCode(404)->setJSON(['error'=>'Foto inválida']);
        $albumModel->update($albumId, ['thumbnail_id'=>$fotoId,'updated_at'=>date('Y-m-d H:i:s')]);
        $fotoModel->where('album_id',$albumId)->set(['eh_thumbnail'=>0])->update();
        $fotoModel->update($fotoId, ['eh_thumbnail'=>1]);
        return $this->response->setJSON(['success'=>true]);
    }
}
