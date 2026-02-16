<?php

namespace App\Controllers\DashClient\Perfil;

use App\Controllers\BaseController;
use App\Models\User\UserPostModel;
use App\Models\User\UserPostCommentModel;
use App\Models\User\UserMediaModel;
use App\Models\User\UserPostTextModel;
use App\Models\User\UserPostTagModel;
use App\Models\User\UserStoryModel;
use App\Models\User\UserModel;
use App\Models\Perfil\PerfilBarClientModel;
use App\Models\User\UserPostLikeModel;
use App\Models\Localizacao\BairroModel;
use App\Models\Localizacao\CidadeModel;
use App\Models\Localizacao\RuaModel;
use App\Models\User\FollowModel;
use App\Models\User\PerfilUsuarioModel;
use App\Models\Perfil\BarStoryModel;

class UserPostController extends BaseController
{
    public function ui($routeUserId = null)
    {
        $currentUserId = (int) (session()->get('user_id') ?? 0);
        $getUserId     = (int) ($this->request->getGet('user_id') ?? 0);
        
        // Determine target user ID: Route > GET > Session
        if ($routeUserId) {
            $userId = (int) $routeUserId;
        } elseif ($getUserId) {
            $userId = $getUserId;
        } else {
            $userId = $currentUserId;
        }

        if (!$userId) return redirect()->to('/auth/login');
        
        // Fetch target user details (name/email)
        $userModel = new UserModel();
        $targetUser = $userModel->find($userId);
        
        $perfil = (new PerfilUsuarioModel())->where('user_id', $userId)->first() ?? [];
        
        // Merge user details into profile array for view convenience
        if ($targetUser) {
            $perfil['user_name'] = $targetUser['name'] ?? 'Usuário';
            $perfil['user_email'] = $targetUser['email'] ?? '';
        } else {
            $perfil['user_name'] = 'Usuário';
            $perfil['user_email'] = '';
        }

        // Stats
        $followModel = new FollowModel();
        $followersCount = $followModel->where('target_id', $userId)->where('target_type', 'user')->where('status', 'approved')->countAllResults();
        $followingCount = $followModel->where('follower_id', $userId)->where('follower_type', 'user')->where('status', 'approved')->countAllResults();
        $postsCount = (new UserPostModel())->where('user_id', $userId)->countAllResults();

        // Check if current user is following target
        $isFollowing = false;
        if ($currentUserId && $currentUserId !== $userId) {
            $check = $followModel->where('follower_id', $currentUserId)
                                 ->where('follower_type', 'user')
                                 ->where('target_id', $userId)
                                 ->where('target_type', 'user')
                                 ->where('status', 'approved')
                                 ->first();
            if ($check) $isFollowing = true;
        }

        $barModel = new PerfilBarClientModel();
        try {
            $ads = $barModel->orderBy('RAND()')->findAll(6);
        } catch (\Throwable $e) {
            $ads = $barModel->orderBy('bares_id', 'DESC')->findAll(6);
        }
        
        return view('dash_client/perfil_usuario/posts_instagram', [
            'perfil' => $perfil, 
            'ads' => $ads, 
            'targetUserId' => $userId,
            'isOwner' => ($userId === $currentUserId),
            'stats' => [
                'followers' => $followersCount,
                'following' => $followingCount,
                'posts' => $postsCount,
                'is_following' => $isFollowing
            ]
        ]);
    }

    public function followUser($targetId)
    {
        return $this->handleFollow($targetId, 'user', 'follow');
    }

    public function unfollowUser($targetId)
    {
        return $this->handleFollow($targetId, 'user', 'unfollow');
    }

    public function followBar($targetId)
    {
        return $this->handleFollow($targetId, 'bar', 'follow');
    }

    public function unfollowBar($targetId)
    {
        return $this->handleFollow($targetId, 'bar', 'unfollow');
    }

    protected function handleFollow($targetId, $targetType, $action)
    {
        $currentUserId = (int) (session()->get('user_id') ?? 0);
        if (!$currentUserId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }

        $followModel = new FollowModel();
        
        // Check if exists
        $existing = $followModel->where('follower_id', $currentUserId)
                                ->where('follower_type', 'user')
                                ->where('target_id', $targetId)
                                ->where('target_type', $targetType)
                                ->first();

        if ($action === 'follow') {
            if ($existing) {
                // Already following or pending
                if ($existing['status'] !== 'approved') {
                    $followModel->update($existing['id'], ['status' => 'approved']); // Auto approve for now
                    return $this->response->setJSON(['success' => true, 'status' => 'approved']);
                }
                return $this->response->setJSON(['success' => true, 'status' => 'already_following']);
            } else {
                $followModel->insert([
                    'follower_id'   => $currentUserId,
                    'follower_type' => 'user',
                    'target_id'     => $targetId,
                    'target_type'   => $targetType,
                    'status'        => 'approved' // Auto approve
                ]);

                // Notification
                if ($targetType === 'user' && $targetId !== $currentUserId) {
                    $notifModel = new \App\Models\User\NotificationModel();
                    $followerName = session()->get('name') ?? 'Alguém';
                    $notifModel->addNotification(
                        $targetId,
                        'follow',
                        'Novo seguidor',
                        "{$followerName} começou a seguir você.",
                        '/dashboard/perfil/user/' . $currentUserId,
                        $currentUserId
                    );
                }

                return $this->response->setJSON(['success' => true, 'status' => 'following']);
            }
        } elseif ($action === 'unfollow') {
            if ($existing) {
                $followModel->delete($existing['id']);
                return $this->response->setJSON(['success' => true, 'status' => 'unfollowed']);
            }
            return $this->response->setJSON(['success' => true, 'status' => 'not_following']);
        }
        
        return $this->response->setStatusCode(400)->setJSON(['error' => 'Ação inválida']);
    }

    public function storiesUi()
    {
        $currentUserId = (int) (session()->get('user_id') ?? 0);
        $targetUserId  = (int) ($this->request->getGet('user_id') ?? 0);
        $typeUser      = (int) (session()->get('type_user') ?? 0);

        if ($targetUserId > 0 && $typeUser === 3) {
            $userId = $targetUserId;
        } else {
            $userId = $currentUserId;
        }

        if (!$userId) return redirect()->to('/auth/login');
        $perfil = (new PerfilUsuarioModel())->where('user_id', $userId)->first() ?? [];
        $barModel = new PerfilBarClientModel();
        try {
            $ads = $barModel->orderBy('RAND()')->findAll(6);
        } catch (\Throwable $e) {
            $ads = $barModel->orderBy('bares_id', 'DESC')->findAll(6);
        }
        return view('dash_client/perfil_usuario/stories_instagram', ['perfil' => $perfil, 'ads' => $ads, 'targetUserId' => $userId]);
    }

    public function audioProxy()
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }
        $url = (string) ($this->request->getGet('url') ?? '');
        $url = trim($url);
        if ($url === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'URL ausente']);
        }
        $parts = parse_url($url);
        if (!$parts || !in_array(strtolower($parts['scheme'] ?? ''), ['http','https'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'URL inválida']);
        }
        $host = strtolower($parts['host'] ?? '');
        $whitelist = [
            'cdn.pixabay.com',
            'pixabay.com',
            'files.freemusicarchive.org',
            'freemusicarchive.org',
            'cdn.jsdelivr.net',
            'raw.githubusercontent.com',
        ];
        $allowed = false;
        foreach ($whitelist as $w) {
            if ($host === $w || str_ends_with($host, '.' . $w)) { $allowed = true; break; }
        }
        if (!$allowed) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Host não permitido']);
        }
        $ch = curl_init($url);
        $headers = [
            'Accept: audio/*;q=0.9,*/*;q=0.5',
            'Referer: https://pixabay.com/',
            'Origin: https://bares.acheisp.online',
        ];
        $rangeHeader = $this->request->getHeaderLine('Range');
        if (!empty($rangeHeader)) {
            $headers[] = 'Range: ' . $rangeHeader;
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36 RolesBR-AudioProxy');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $data = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ctype= curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: 'audio/mpeg';
        curl_close($ch);
        if ($err || !$data || ($code < 200 || $code >= 400)) {
            return $this->response->setStatusCode(502)->setJSON(['error' => 'Falha ao obter áudio remoto']);
        }
        $resp = $this->response
            ->setStatusCode($code)
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Accept-Ranges', 'bytes')
            ->setContentType($ctype)
            ->setBody($data);
        return $resp;
    }
    protected function rateLimit(string $key, int $limit, int $windowSeconds): bool
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) return false;
        $now = time();
        $bucketKey = 'rate_' . $key . '_' . $userId;
        $bucket = session()->get($bucketKey);
        if (!$bucket || !is_array($bucket) || ($now - ($bucket['start'] ?? 0)) > $windowSeconds) {
            session()->set($bucketKey, ['start' => $now, 'count' => 1]);
            return true;
        }
        $count = (int) ($bucket['count'] ?? 0);
        if ($count >= $limit) return false;
        $bucket['count'] = $count + 1;
        session()->set($bucketKey, $bucket);
        return true;
    }

    protected function moderateText(?string $text): bool
    {
        $t = trim((string) $text);
        if ($t === '') return true;
        $apiKey = getenv('PERSPECTIVE_API_KEY') ?: env('PERSPECTIVE_API_KEY');
        if (!$apiKey) return true;

        $body = [
            'comment' => ['text' => $t],
            'languages' => ['pt', 'en'],
            'requestedAttributes' => [
                'TOXICITY'          => new \stdClass(),
                'SEXUALLY_EXPLICIT' => new \stdClass(),
            ],
        ];

        $ch = curl_init('https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze?key=' . urlencode($apiKey));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err || !$res) return true;

        $json = json_decode($res, true);
        $tox = $json['attributeScores']['TOXICITY']['summaryScore']['value'] ?? 0.0;
        $sex = $json['attributeScores']['SEXUALLY_EXPLICIT']['summaryScore']['value'] ?? 0.0;

        return ($tox < 0.8) && ($sex < 0.8);
    }

    protected function resizeImage(string $absolutePath, int $maxW = 1080, int $maxH = 1080): void
    {
        if (!is_file($absolutePath)) { log_message('error', "resizeImage: arquivo não existe {$absolutePath}"); return; }
        $info = @getimagesize($absolutePath);
        if (!$info || !is_array($info) || empty($info[0]) || empty($info[1])) { log_message('error', "resizeImage: getimagesize falhou em {$absolutePath}"); return; }
        $w = (int) $info[0]; $h = (int) $info[1]; if ($w <= $maxW && $h <= $maxH) return;
        $mime = (string) ($info['mime'] ?? 'image/jpeg');
        $src = null;
        if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) $src = @imagecreatefromjpeg($absolutePath);
        elseif ($mime === 'image/png' && function_exists('imagecreatefrompng')) $src = @imagecreatefrompng($absolutePath);
        elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) $src = @imagecreatefromwebp($absolutePath);
        else { $data = @file_get_contents($absolutePath); if ($data) $src = @imagecreatefromstring($data); }
        if (!$src) { log_message('error', "resizeImage: falha ao carregar imagem ({$mime}) em {$absolutePath}"); return; }
        $ratio = min($maxW / $w, $maxH / $h);
        $newW  = max(1, (int) floor($w * $ratio));
        $newH  = max(1, (int) floor($h * $ratio));
        $dst = imagecreatetruecolor($newW, $newH);
        if ($mime === 'image/png' || $mime === 'image/webp') { imagealphablending($dst, false); imagesavealpha($dst, true); } else { $white = imagecolorallocate($dst, 255, 255, 255); imagefilledrectangle($dst, 0, 0, $newW, $newH, $white); }
        if (!imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h)) { log_message('error', "resizeImage: imagecopyresampled falhou para {$absolutePath}"); imagedestroy($src); imagedestroy($dst); return; }
        $ok = false;
        if ($mime === 'image/jpeg' && function_exists('imagejpeg')) $ok = @imagejpeg($dst, $absolutePath, 85);
        elseif ($mime === 'image/png' && function_exists('imagepng')) $ok = @imagepng($dst, $absolutePath, 6);
        elseif ($mime === 'image/webp' && function_exists('imagewebp')) $ok = @imagewebp($dst, $absolutePath, 85);
        if (!$ok || (@filesize($absolutePath) === 0)) { log_message('error', "resizeImage: falha ao salvar em {$absolutePath} ({$mime})"); }
        imagedestroy($src); imagedestroy($dst);
    }

    // Use targetW/targetH para controlar 4:5 (1080x1350) ou 9:16 (1080x1920)
    protected function enforcePortrait(string $absolutePath, int $targetW = 1080, int $targetH = 1920): void
    {
        if (!is_file($absolutePath)) { log_message('error', "enforcePortrait: arquivo não existe {$absolutePath}"); return; }
        $info = @getimagesize($absolutePath);
        if (!$info || !is_array($info) || empty($info[0]) || empty($info[1])) { log_message('error', "enforcePortrait: getimagesize falhou em {$absolutePath}"); return; }
        $w    = (int) $info[0]; $h = (int) $info[1];
        $mime = (string) ($info['mime'] ?? 'image/jpeg');
        $src = null;
        if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) $src = @imagecreatefromjpeg($absolutePath);
        elseif ($mime === 'image/png' && function_exists('imagecreatefrompng')) $src = @imagecreatefrompng($absolutePath);
        elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) $src = @imagecreatefromwebp($absolutePath);
        else { $data = @file_get_contents($absolutePath); if ($data) $src = @imagecreatefromstring($data); }
        if (!$src) { log_message('error', "enforcePortrait: falha ao carregar imagem ({$mime}) em {$absolutePath}"); return; }
        $desiredRatio  = $targetW / $targetH; $currentRatio  = $w / $h;
        if ($currentRatio > $desiredRatio) { $cropW = (int) floor($h * $desiredRatio); $cropH = $h; $x = (int) floor(($w - $cropW) / 2); $y = 0; }
        else { $cropW = $w; $cropH = (int) floor($w / $desiredRatio); $x = 0; $y = (int) floor(($h - $cropH) / 2); }
        $crop = imagecreatetruecolor($cropW, $cropH);
        if ($mime === 'image/png' || $mime === 'image/webp') { imagealphablending($crop, false); imagesavealpha($crop, true); }
        if (!imagecopy($crop, $src, 0, 0, $x, $y, $cropW, $cropH)) { log_message('error', "enforcePortrait: imagecopy falhou em {$absolutePath}"); imagedestroy($src); imagedestroy($crop); return; }
        $dst = imagecreatetruecolor($targetW, $targetH);
        if ($mime === 'image/png' || $mime === 'image/webp') { imagealphablending($dst, false); imagesavealpha($dst, true); }
        else { $white = imagecolorallocate($dst, 255, 255, 255); imagefilledrectangle($dst, 0, 0, $targetW, $targetH, $white); }
        if (!imagecopyresampled($dst, $crop, 0, 0, 0, 0, $targetW, $targetH, $cropW, $cropH)) { log_message('error', "enforcePortrait: imagecopyresampled falhou em {$absolutePath}"); imagedestroy($src); imagedestroy($crop); imagedestroy($dst); return; }
        $ok = false;
        if ($mime === 'image/jpeg' && function_exists('imagejpeg')) $ok = @imagejpeg($dst, $absolutePath, 85);
        elseif ($mime === 'image/png' && function_exists('imagepng')) $ok = @imagepng($dst, $absolutePath, 6);
        elseif ($mime === 'image/webp' && function_exists('imagewebp')) $ok = @imagewebp($dst, $absolutePath, 85);
        if (!$ok || (@filesize($absolutePath) === 0)) { log_message('error', "enforcePortrait: falha ao salvar em {$absolutePath} ({$mime})"); }
        imagedestroy($src); imagedestroy($crop); imagedestroy($dst);
    }

    protected function portraitSave(string $absolutePath, int $targetW = 1080, int $targetH = 1920, int $maxBytes = 5242880): string
    {
        if (!is_file($absolutePath)) {
            throw new \RuntimeException('Arquivo não encontrado: ' . $absolutePath);
        }

        $info = @getimagesize($absolutePath);
        if (!$info || !is_array($info)) {
            throw new \RuntimeException('Não foi possível ler informações da imagem');
        }

        $mime = $info['mime'] ?? '';
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array(strtolower($mime), $allowedMimes)) {
            throw new \RuntimeException('Tipo de imagem não suportado: ' . $mime);
        }

        $src = null;
        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

        // Tenta carregar a imagem baseado no MIME type
        try {
            switch (strtolower($mime)) {
                case 'image/jpeg':
                case 'image/jpg':
                    $src = @imagecreatefromjpeg($absolutePath);
                    if (!$src) {
                        // Tenta com imagecreatefromstring como fallback
                        $data = @file_get_contents($absolutePath);
                        if ($data) {
                            $src = @imagecreatefromstring($data);
                        }
                    }
                    break;
                case 'image/png':
                    $src = @imagecreatefrompng($absolutePath);
                    if (!$src) {
                        $data = @file_get_contents($absolutePath);
                        if ($data) {
                            $src = @imagecreatefromstring($data);
                        }
                    }
                    break;
                case 'image/gif':
                    $src = @imagecreatefromgif($absolutePath);
                    if (!$src) {
                        $data = @file_get_contents($absolutePath);
                        if ($data) {
                            $src = @imagecreatefromstring($data);
                        }
                    }
                    break;
                case 'image/webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $src = @imagecreatefromwebp($absolutePath);
                    }
                    if (!$src) {
                        $data = @file_get_contents($absolutePath);
                        if ($data) {
                            $src = @imagecreatefromstring($data);
                        }
                    }
                    break;
            }
        } catch (\Throwable $e) {
            // Se falhar, tenta com imagecreatefromstring
            $data = @file_get_contents($absolutePath);
            if ($data) {
                $src = @imagecreatefromstring($data);
            }
        }

        // Se ainda não conseguiu, tenta com Imagick
        if (!$src && class_exists('\Imagick')) {
            try {
                $im = new \Imagick($absolutePath);
                $im->setImageFormat('jpeg');
                $im->setImageCompressionQuality(85);
                $tmp = $absolutePath . '.tmp.jpg';
                $im->writeImage($tmp);
                $im->clear();
                $im->destroy();
                if (is_file($tmp)) {
                    @unlink($absolutePath);
                    $absolutePath = $tmp;
                    $src = @imagecreatefromjpeg($absolutePath);
                }
            } catch (\Throwable $e) {
                // Ignora erro do Imagick
            }
        }

        if (!$src) {
            throw new \RuntimeException('Não foi possível processar a imagem. Arquivo pode estar corrompido.');
        }

        $w = imagesx($src);
        $h = imagesy($src);
        if ($w <= 0 || $h <= 0) {
            imagedestroy($src);
            throw new \RuntimeException('Dimensões inválidas da imagem');
        }

        $desiredRatio = $targetW / $targetH;
        $currentRatio = $w / $h;
        if ($currentRatio > $desiredRatio) {
            $cropW = (int) floor($h * $desiredRatio);
            $cropH = $h;
            $x = (int) floor(($w - $cropW) / 2);
            $y = 0;
        } else {
            $cropW = $w;
            $cropH = (int) floor($w / $desiredRatio);
            $x = 0;
            $y = (int) floor(($h - $cropH) / 2);
        }

        $crop = @imagecreatetruecolor($cropW, $cropH);
        if (!$crop) {
            imagedestroy($src);
            throw new \RuntimeException('Erro ao criar imagem de corte');
        }

        // Preserva transparência para PNG
        if (strtolower($mime) === 'image/png') {
            imagealphablending($crop, false);
            imagesavealpha($crop, true);
            $transparent = imagecolorallocatealpha($crop, 0, 0, 0, 127);
            imagefill($crop, 0, 0, $transparent);
        }

        if (!@imagecopy($crop, $src, 0, 0, $x, $y, $cropW, $cropH)) {
            imagedestroy($src);
            imagedestroy($crop);
            throw new \RuntimeException('Erro ao copiar imagem');
        }

        $dst = @imagecreatetruecolor($targetW, $targetH);
        if (!$dst) {
            imagedestroy($src);
            imagedestroy($crop);
            throw new \RuntimeException('Erro ao criar imagem de destino');
        }

        // Preserva transparência para PNG
        if (strtolower($mime) === 'image/png') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $transparent);
        }

        if (!@imagecopyresampled($dst, $crop, 0, 0, 0, 0, $targetW, $targetH, $cropW, $cropH)) {
            imagedestroy($src);
            imagedestroy($crop);
            imagedestroy($dst);
            throw new \RuntimeException('Erro ao redimensionar imagem');
        }

        // Salva preferencialmente como JPEG (com fallback para WEBP/PNG)
        $baseName = dirname($absolutePath) . DIRECTORY_SEPARATOR . pathinfo($absolutePath, PATHINFO_FILENAME);
        $newAbs = $baseName . '.jpg';
        $qualities = [85, 80, 75, 70, 65, 60, 55, 50, 45, 40];
        $saved = false;
        if (function_exists('imagejpeg')) {
            foreach ($qualities as $q) {
                if (@imagejpeg($dst, $newAbs, $q)) {
                    clearstatcache(true, $newAbs);
                    $sz = @filesize($newAbs);
                    if ($sz !== false && $sz > 0 && $sz <= $maxBytes && @getimagesize($newAbs)) { $saved = true; break; }
                }
            }
        }
        if (!$saved && function_exists('imagewebp')) {
            $newAbs = $baseName . '.webp';
            if (@imagewebp($dst, $newAbs, 85) && @filesize($newAbs) > 0 && @getimagesize($newAbs)) { $saved = true; }
        }
        if (!$saved && function_exists('imagepng')) {
            $newAbs = $baseName . '.png';
            if (@imagepng($dst, $newAbs, 6) && @filesize($newAbs) > 0 && @getimagesize($newAbs)) { $saved = true; }
        }
        if (!$saved) { imagedestroy($src); imagedestroy($crop); imagedestroy($dst); throw new \RuntimeException('Erro ao salvar imagem processada'); }

        if (!is_file($newAbs) || @filesize($newAbs) === 0) { imagedestroy($src); imagedestroy($crop); imagedestroy($dst); throw new \RuntimeException('Arquivo processado está vazio ou corrompido'); }
        $finalInfo = @getimagesize($newAbs);
        if (!$finalInfo || !is_array($finalInfo)) { @unlink($newAbs); imagedestroy($src); imagedestroy($crop); imagedestroy($dst); throw new \RuntimeException('Imagem processada é inválida'); }

        if ($newAbs !== $absolutePath && is_file($absolutePath)) {
            @unlink($absolutePath);
        }

        imagedestroy($src);
        imagedestroy($crop);
        imagedestroy($dst);

        return $newAbs;
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

    public function index()
    {
        $limit  = (int) ($this->request->getGet('limit') ?? 10);
        $offset = (int) ($this->request->getGet('offset') ?? 0);
        $userId = (int) ($this->request->getGet('user_id') ?? 0);

        $model = new UserPostModel();
        if ($userId > 0) {
            $model->where('user_id', $userId);
        }
        $posts = $model->orderBy('created_at', 'DESC')
                       ->findAll($limit, $offset);

        // --- Likes Batch Query (Added to match Home::postsPublic) ---
        $postIds = array_column($posts, 'post_id');
        $likesMap = [];
        $likedByMeMap = [];

        if (!empty($postIds)) {
            $likeModel = new \App\Models\User\UserPostLikeModel();
            
            // Count likes
            $counts = $likeModel->select('post_id, COUNT(*) as total')
                                ->whereIn('post_id', $postIds)
                                ->groupBy('post_id')
                                ->findAll();
            foreach ($counts as $c) {
                $likesMap[$c['post_id']] = (int)$c['total'];
            }

            // Check if liked by current user
            $currentUserId = (int) (session()->get('user_id') ?? 0);
            if ($currentUserId) {
                $myLikes = $likeModel->select('post_id')
                                     ->where('user_id', $currentUserId)
                                     ->whereIn('post_id', $postIds)
                                     ->findAll();
                foreach ($myLikes as $ml) {
                    $likedByMeMap[$ml['post_id']] = true;
                }
            }
        }
        // -------------------------

        $textModel = new UserPostTextModel();
        $userIds   = [];

        foreach ($posts as &$p) {
            $text        = $textModel->where('post_id', (int) $p['post_id'])->orderBy('created_at', 'DESC')->first();
            $p['caption'] = $text['text_body'] ?? ($p['caption'] ?? null);
            
            // Add like info
            $pid = $p['post_id'];
            $p['likes_count'] = $likesMap[$pid] ?? 0;
            $p['liked_by_me'] = isset($likedByMeMap[$pid]);

            $uid         = (int) ($p['user_id'] ?? 0);
            if ($uid) $userIds[$uid] = true;
            
            // Garante que image_url está correta e verifica se o arquivo existe
            if (!empty($p['image_url'])) {
                $cleanUrl = str_replace('\\', '/', $p['image_url']);
                
                // Robust Path Cleaning for Cross-Platform/Legacy Data
                // If path contains 'uploads/', strip everything before it
                if (($pos = strpos($cleanUrl, 'uploads/')) !== false) {
                    $cleanUrl = substr($cleanUrl, $pos);
                } else {
                    // Fallback cleanup
                    $publicRoot = str_replace('\\', '/', ROOTPATH . 'public/');
                    if (strpos($cleanUrl, $publicRoot) === 0) {
                        $cleanUrl = substr($cleanUrl, strlen($publicRoot));
                    }
                    $cleanUrl = ltrim($cleanUrl, '/');
                    if (strpos($cleanUrl, 'public/') === 0) {
                        $cleanUrl = substr($cleanUrl, 7);
                    }
                }
                
                $p['image_url'] = $cleanUrl;
                
                $imagePath = ROOTPATH . 'public' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $cleanUrl);
                
                // Relaxed check: Only log error, don't nullify. 
                // This allows frontend to try loading even if backend file check fails (e.g. permission issues)
                if (!is_file($imagePath)) {
                    // log_message('error', "Image check failed: " . $imagePath);
                }
            }
        }
        unset($p);

        $userMap   = [];
        $avatarMap = [];
        if (!empty($userIds)) {
            $ids   = array_keys($userIds);
            $users = (new UserModel())->whereIn('id', $ids)->findAll();
            foreach ($users as $u) {
                $userMap[(int) $u['id']] = [
                    'name' => $u['name'] ?? 'Usuário',
                ];
            }
            $perfis = (new PerfilUsuarioModel())->whereIn('user_id', $ids)->findAll();
            foreach ($perfis as $pf) {
                $fp = $pf['foto_perfil'] ?? null;
                if ($fp === 'null' || $fp === '' || $fp === false) $fp = null;
                $avatarMap[(int) $pf['user_id']] = $fp;
            }
        }

        foreach ($posts as &$p) {
            $uid  = (int) ($p['user_id'] ?? 0);
            $name = $userMap[$uid]['name'] ?? 'Usuário';
            $foto = $avatarMap[$uid] ?? null;

            $p['author_id']         = $uid;
            $p['author_name']       = $name;
            $p['author_avatar']     = $foto ? base_url($foto) : ("https://ui-avatars.com/api/?name=" . urlencode($name) . "&size=128&background=random");
            $p['author_profile_url'] = '/dashboard/perfil/user/' . $uid;
        }
        unset($p);

        return $this->response->setJSON([
            'items'  => $posts,
            'count'  => count($posts),
            'limit'  => $limit,
            'offset' => $offset,
        ]);
    }

    public function store()
    {
        $currentUserId = session()->get('user_id');
        $typeUser      = (int)session()->get('type_user');
        $targetUserId  = $this->request->getPost('target_user_id');

        if ($targetUserId && $typeUser === 3) {
            $userId = (int) $targetUserId;
        } else {
            $userId = $currentUserId;
        }

        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }
        if (!$this->rateLimit('post_store', 10, 60)) {
            return $this->response->setStatusCode(429)->setJSON(['error' => 'Muitas requisições']);
        }

        $caption   = $this->request->getPost('caption');
        $tagsUsers = (array) $this->request->getPost('tags_users');
        $tagsBars  = (array) $this->request->getPost('tags_bars');
        $lat       = $this->request->getPost('location_lat');
        $lng       = $this->request->getPost('location_lng');
        $neigh     = $this->request->getPost('neighborhood');
        $file      = $this->request->getFile('image');
        ini_set('memory_limit', '256M');

        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Imagem inválida ou não enviada']);
        }

        // Validação de tamanho antes do upload
        if ((int) $file->getSize() > 50 * 1024 * 1024) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Imagem muito grande. Máximo: 50MB']);
        }

        if ((int) $file->getSize() === 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Arquivo vazio ou corrompido']);
        }

        // Validação de tipo MIME (mesma metodologia de stories)
        $mime = strtolower($file->getMimeType());
        if ($mime === 'image/svg+xml') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'SVG não é suportado em posts. Use JPG, PNG, WEBP ou GIF']);
        }

        // Removida validação por extensão para simplificar

        if (!$this->moderateText($caption)) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Conteúdo não permitido']);
        }

        $baseDir = ROOTPATH . 'public/uploads/perfis/' . $userId . '/posts';
        if (!is_dir($baseDir)) {
            if (!@mkdir($baseDir, 0777, true)) {
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Erro ao criar diretório de upload']);
            }
        }

        $newName = $file->getRandomName();
        $tempPath = $baseDir . DIRECTORY_SEPARATOR . $newName;

        try {
            if (!$file->move($baseDir, $newName)) {
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Erro ao mover arquivo']);
            }

            // Verifica se o arquivo foi movido corretamente
            clearstatcache(true, $tempPath);
            if (!is_file($tempPath) || filesize($tempPath) === 0) {
                @unlink($tempPath);
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Arquivo corrompido ou vazio']);
            }
            $imgInfo = @getimagesize($tempPath);
            if (!$imgInfo || !is_array($imgInfo) || empty($imgInfo[0]) || empty($imgInfo[1])) {
                @unlink($tempPath);
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Arquivo não é uma imagem válida']);
            }

            // Processa a imagem
            $info = @getimagesize($tempPath);
            $w = (int) ($info[0] ?? 0);
            $h = (int) ($info[1] ?? 0);
            $targetW = ($h >= $w) ? 419 : 520;
            $targetH = ($h >= $w) ? 520 : 419;
            $finalAbs = $this->portraitSave($tempPath, $targetW, $targetH, 5 * 1024 * 1024);

            // Verifica se o processamento foi bem-sucedido
            if (!is_file($finalAbs) || filesize($finalAbs) === 0) {
                @unlink($tempPath);
                @unlink($finalAbs);
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Erro ao processar imagem. Arquivo pode estar corrompido.']);
            }

            // Verifica se a imagem processada é válida
            $finalInfo = @getimagesize($finalAbs);
            if (!$finalInfo || !is_array($finalInfo)) {
                @unlink($tempPath);
                @unlink($finalAbs);
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Imagem processada é inválida ou corrompida']);
            }

            $relative = str_replace(ROOTPATH . 'public' . DIRECTORY_SEPARATOR, '', $finalAbs);
            $relative = str_replace('\\', '/', $relative);

            // Remove barra inicial se houver
            $relative = ltrim($relative, '/');

        } catch (\Throwable $e) {
            // Limpa arquivos temporários em caso de erro
            if (isset($tempPath) && is_file($tempPath)) {
                @unlink($tempPath);
            }
            if (isset($finalAbs) && is_file($finalAbs) && $finalAbs !== $tempPath) {
                @unlink($finalAbs);
            }
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Erro ao processar imagem: ' . $e->getMessage()
            ]);
        }

        try {
            $postModel = new UserPostModel();
            $postId    = $postModel->insert([
                'user_id'      => $userId,
                'image_url'    => $relative,
                'location_lat' => !empty($lat) ? (float) $lat : null,
                'location_lng' => !empty($lng) ? (float) $lng : null,
                'neighborhood' => !empty($neigh) ? $neigh : null,
                'caption'      => !empty($caption) ? $caption : null,
            ]);

            if (!$postId) {
                @unlink($finalAbs);
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Erro ao salvar post no banco de dados']);
            }

            if (!empty($caption)) {
                $textModel = new UserPostTextModel();
                $textModel->insert([
                    'post_id'   => (int) $postId,
                    'text_body' => $caption,
                ]);
            }

            $tagModel  = new UserPostTagModel();
            $userModel = new UserModel();
            $barModel  = new PerfilBarClientModel();

            foreach ($tagsUsers as $term) {
                $term = is_string($term) ? trim($term) : (string) $term;
                if ($term === '') continue;

                $targetId = null;
                if (ctype_digit($term)) {
                    $targetId = (int) $term;
                } else {
                    $found = $userModel->like('name', $term)->first();
                    if ($found && isset($found['id'])) {
                        $targetId = (int) $found['id'];
                    }
                }

                if ($targetId) {
                    $tagModel->insert([
                        'post_id'     => (int) $postId,
                        'target_type' => 'user',
                        'target_id'   => $targetId,
                    ]);
                }
            }

            foreach ($tagsBars as $term) {
                $term = is_string($term) ? trim($term) : (string) $term;
                if ($term === '') continue;

                $targetId = null;
                if (ctype_digit($term)) {
                    $targetId = (int) $term;
                } else {
                    $found = $barModel->like('nome', $term)->first();
                    if ($found && isset($found['bares_id'])) {
                        $targetId = (int) $found['bares_id'];
                    }
                }

                if ($targetId) {
                    $tagModel->insert([
                        'post_id'     => (int) $postId,
                        'target_type' => 'bar',
                        'target_id'   => $targetId,
                    ]);
                }
            }

            $mediaModel = new UserMediaModel();
            $mediaModel->insert([
                'user_id' => $userId,
                'tipo'    => 'post_imagem',
                'url'     => $relative,
            ]);

            return $this->response->setJSON([
                'success'   => true,
                'post_id'   => $postId,
                'image_url' => $relative,
            ]);

        } catch (\Throwable $e) {
            // Em caso de erro no banco, tenta limpar a imagem
            if (isset($finalAbs) && is_file($finalAbs)) {
                @unlink($finalAbs);
            }
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Erro ao salvar post: ' . $e->getMessage()
            ]);
        }
    }

    public function edit($id)
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        $typeUser = (int) (session()->get('type_user') ?? 0);

        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }
        if (!$this->rateLimit('post_edit', 20, 60)) {
            return $this->response->setStatusCode(429)->setJSON(['error' => 'Muitas requisições']);
        }

        $model = new UserPostModel();
        $post  = $model->find($id);
        if (!$post || ((int) $post['user_id'] !== $userId && $typeUser !== 3)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sem permissão']);
        }

        $caption = $this->request->getPost('caption');
        $lat     = $this->request->getPost('location_lat');
        $lng     = $this->request->getPost('location_lng');
        $neigh   = $this->request->getPost('neighborhood');

        if (!$this->moderateText($caption)) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Conteúdo não permitido']);
        }

        $file   = $this->request->getFile('image');
        $update = [
            'caption'      => !empty($caption) ? $caption : $post['caption'],
            'location_lat' => !empty($lat) ? (float) $lat : $post['location_lat'],
            'location_lng' => !empty($lng) ? (float) $lng : $post['location_lng'],
            'neighborhood' => !empty($neigh) ? $neigh : $post['neighborhood'],
        ];

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $mimeEdit = strtolower($file->getMimeType() ?? '');
            if ($mimeEdit === 'image/svg+xml') {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'SVG não é suportado em posts. Use JPG, PNG, WEBP ou GIF']);
            }
            if ((int) $file->getSize() > 12 * 1024 * 1024) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Imagem excede 12MB']);
            }

            $baseDir = ROOTPATH . 'public/uploads/perfis/' . $userId . '/posts';
            if (!is_dir($baseDir)) { if (!@mkdir($baseDir, 0777, true)) return $this->response->setStatusCode(500)->setJSON(['error' => 'Erro ao criar diretório']); }

            try {
                $newName = $file->getRandomName();
                $tempPath = $baseDir . DIRECTORY_SEPARATOR . $newName;
                if (!$file->move($baseDir, $newName)) {
                    return $this->response->setStatusCode(500)->setJSON(['error' => 'Erro ao mover arquivo']);
                }
                if (!is_file($tempPath) || @filesize($tempPath) === 0) {
                    @unlink($tempPath);
                    return $this->response->setStatusCode(400)->setJSON(['error' => 'Arquivo vazio ou corrompido']);
                }
                $info = @getimagesize($tempPath);
                $w = (int) ($info[0] ?? 0);
                $h = (int) ($info[1] ?? 0);
                $targetW = ($h >= $w) ? 419 : 520;
                $targetH = ($h >= $w) ? 520 : 419;
                $finalAbs = $this->portraitSave($tempPath, $targetW, $targetH);
                if (!is_file($finalAbs) || @filesize($finalAbs) === 0 || !@getimagesize($finalAbs)) {
                    @unlink($tempPath);
                    if ($finalAbs !== $tempPath) @unlink($finalAbs);
                    return $this->response->setStatusCode(500)->setJSON(['error' => 'Falha ao processar imagem']);
                }
                $update['image_url'] = str_replace(ROOTPATH . 'public' . DIRECTORY_SEPARATOR, '', $finalAbs);
                $update['image_url'] = ltrim(str_replace('\\', '/', $update['image_url']), '/');
            } catch (\Throwable $e) {
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Erro ao processar imagem: ' . $e->getMessage()]);
            }
        }

        $model->update($id, $update);
        return $this->response->setJSON(['success' => true]);
    }

    public function delete($id)
    {
        $userId   = (int) (session()->get('user_id') ?? 0);
        $typeUser = (int) (session()->get('type_user') ?? 0);
        $model    = new UserPostModel();
        $post     = $model->find($id);

        if (!$post || ((int) ($post['user_id'] ?? 0) !== $userId && $typeUser !== 3)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sem permissão']);
        }

        $image = $post['image_url'] ?? null;
        $model->delete($id);

        if ($image) {
            $abs = ROOTPATH . 'public' . DIRECTORY_SEPARATOR . ltrim($image, DIRECTORY_SEPARATOR);
            if (is_file($abs)) @unlink($abs);
        }

        return $this->response->setJSON(['success' => true]);
    }

    public function commentsUi($postId)
    {
        $currentUserId = (int) (session()->get('user_id') ?? 0);
        if (!$currentUserId) return redirect()->to('/auth/login');

        $model    = new UserPostCommentModel();
        // Join to get user details
        $allComments = $model->select('user_post_comments_tb.*, u.name as author_name, p.foto_perfil as author_avatar')
                          ->join('users u', 'u.id = user_post_comments_tb.user_id', 'left')
                          ->join('perfil_usuarios_tb p', 'p.user_id = user_post_comments_tb.user_id', 'left')
                          ->where('post_id', $postId)
                          ->orderBy('created_at', 'ASC')
                          ->findAll();
        
        // Helper to format avatar and build tree
        $commentsMap = [];
        foreach ($allComments as $c) {
            $name = $c['author_name'] ?? 'Usuário';
            if (empty($c['author_avatar']) || $c['author_avatar'] === 'null') {
                $c['author_avatar'] = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=random";
            } else {
                 $c['author_avatar'] = base_url($c['author_avatar']);
            }
            if (empty($c['author_name'])) $c['author_name'] = 'Usuário';
            
            $c['children'] = [];
            $commentsMap[$c['comment_id']] = $c;
        }

        $tree = [];
        foreach ($commentsMap as $id => $c) {
            if (!empty($c['parent_id']) && isset($commentsMap[$c['parent_id']])) {
                $commentsMap[$c['parent_id']]['children'][] = &$commentsMap[$id];
            } else {
                $tree[] = &$commentsMap[$id];
            }
        }

        return view('dash_client/perfil_usuario/comments_modal', ['comments' => $tree, 'postId' => $postId]);
    }

    public function comments($postId)
    {
        $limit  = (int) ($this->request->getGet('limit') ?? 10);
        $offset = (int) ($this->request->getGet('offset') ?? 0);

        $model    = new UserPostCommentModel();
        $comments = $model->where('post_id', $postId)
                          ->orderBy('created_at', 'ASC')
                          ->findAll($limit, $offset);

        return $this->response->setJSON([
            'items'  => $comments,
            'count'  => count($comments),
            'limit'  => $limit,
            'offset' => $offset,
        ]);
    }

    public function commentStore($postId)
    {
        $currentUserId = session()->get('user_id');
        $typeUser      = (int)session()->get('type_user');
        $targetUserId  = $this->request->getPost('target_user_id');

        if ($targetUserId && $typeUser === 3) {
            $userId = (int) $targetUserId;
        } else {
            $userId = $currentUserId;
        }

        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }

        if (!$this->rateLimit('comment_store', 30, 60)) {
            return $this->response->setStatusCode(429)->setJSON(['error' => 'Muitas requisições']);
        }

        $content  = trim($this->request->getPost('content') ?? '');
        $parentId = $this->request->getPost('parent_id');

        if ($content === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Conteúdo vazio']);
        }

        if (!$this->moderateText($content)) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Conteúdo não permitido']);
        }

        try {
            $model     = new UserPostCommentModel();
            $commentId = $model->insert([
                'post_id'   => (int) $postId,
                'user_id'   => (int) $userId,
                'parent_id' => !empty($parentId) ? (int) $parentId : null,
                'content'   => $content,
            ]);

            if (!$commentId) {
                // If insert returns false but no exception
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Erro ao salvar comentário (DB insert failed)']);
            }

            // Notification
            try {
                $postModel = new UserPostModel();
                $post = $postModel->find($postId);
                $currentUserName = session()->get('name') ?? 'Alguém';
                $notifModel = new \App\Models\User\NotificationModel();

                // Notify Post Owner
                if ($post && (int)$post['user_id'] !== (int)$userId) {
                    $notifModel->addNotification(
                        $post['user_id'],
                        'comment',
                        'Novo comentário',
                        "{$currentUserName} comentou na sua publicação.",
                        '/dashboard/perfil/posts/' . $postId,
                        $userId
                    );
                }

                // Notify Parent Comment Owner (if reply)
                if (!empty($parentId)) {
                    $parentComment = $model->find($parentId);
                    if ($parentComment && (int)$parentComment['user_id'] !== (int)$userId && ($post && (int)$parentComment['user_id'] !== (int)$post['user_id'])) {
                        $notifModel->addNotification(
                            $parentComment['user_id'],
                            'comment',
                            'Nova resposta',
                            "{$currentUserName} respondeu ao seu comentário.",
                            '/dashboard/perfil/posts/' . $postId,
                            $userId
                        );
                    }
                }
            } catch (\Throwable $e) {
                // Ignore notification errors
                log_message('error', 'Notification Error: ' . $e->getMessage());
            }

            return $this->response->setJSON(['success' => true, 'comment_id' => $commentId]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Erro no servidor: ' . $e->getMessage()]);
        }
    }

    public function likesUi($postId)
    {
        $currentUserId = (int) (session()->get('user_id') ?? 0);
        if (!$currentUserId) return redirect()->to('/auth/login');

        $model = new UserPostLikeModel();
        $likes = $model->select('user_post_likes_tb.*, u.name as author_name, p.foto_perfil as author_avatar')
                       ->join('users u', 'u.id = user_post_likes_tb.user_id', 'left')
                       ->join('perfil_usuarios_tb p', 'p.user_id = user_post_likes_tb.user_id', 'left')
                       ->where('post_id', $postId)
                       ->orderBy('created_at', 'DESC')
                       ->findAll(50); // Limit to 50 for now

        foreach ($likes as &$l) {
            $name = $l['author_name'] ?? 'Usuário';
            if (empty($l['author_avatar']) || $l['author_avatar'] === 'null') {
                $l['author_avatar'] = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=random";
            } else {
                 $l['author_avatar'] = base_url($l['author_avatar']);
            }
            if (empty($l['author_name'])) $l['author_name'] = 'Usuário';
        }

        return view('dash_client/perfil_usuario/likes_modal', ['likes' => $likes, 'postId' => $postId]);
    }

    public function commentEdit($commentId)
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        $typeUser = (int) (session()->get('type_user') ?? 0);
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }

        if (!$this->rateLimit('comment_edit', 30, 60)) {
            return $this->response->setStatusCode(429)->setJSON(['error' => 'Muitas requisições']);
        }

        $model   = new UserPostCommentModel();
        $comment = $model->find($commentId);

        if (!$comment || ((int) $comment['user_id'] !== $userId && $typeUser !== 3)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sem permissão']);
        }

        $content = trim($this->request->getPost('content') ?? '');
        if ($content === '' || !$this->moderateText($content)) {
            return $this->response->setStatusCode(422)->setJSON(['error' => 'Conteúdo inválido']);
        }

        $model->update($commentId, ['content' => $content]);
        return $this->response->setJSON(['success' => true]);
    }

    public function commentDelete($commentId)
    {
        $userId   = session()->get('user_id');
        $typeUser = (int) (session()->get('type_user') ?? 0);
        $model    = new UserPostCommentModel();
        $comment  = $model->find($commentId);

        if (!$comment || ($comment['user_id'] !== $userId && $typeUser !== 3)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sem permissão']);
        }

        $model->delete($commentId);
        return $this->response->setJSON(['success' => true]);
    }

    public function reply($postId)
    {
        return $this->commentStore($postId);
    }

    public function like($postId)
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }

        if (!$this->rateLimit('post_like', 60, 60)) {
            return $this->response->setStatusCode(429)->setJSON(['error' => 'Muitas requisições']);
        }

        $likeModel = new UserPostLikeModel();
        $existing  = $likeModel->where('post_id', (int) $postId)->where('user_id', $userId)->first();
        $liked     = false;

        try {
            if ($existing) {
                $likeModel->delete($existing['id']);
                $liked = false;
            } else {
                $likeModel->insert(['post_id' => (int) $postId, 'user_id' => $userId]);
                $liked = true;

                // Notification
                try {
                    $postModel = new UserPostModel();
                    $post = $postModel->find($postId);
                    if ($post && (int)$post['user_id'] !== (int)$userId) {
                        $notifModel = new \App\Models\User\NotificationModel();
                        $likerName = session()->get('name') ?? 'Alguém';
                        $notifModel->addNotification(
                            $post['user_id'],
                            'like',
                            'Nova curtida',
                            "{$likerName} curtiu sua publicação.",
                            '/dashboard/perfil/posts/' . $postId,
                            $userId
                        );
                    }
                } catch (\Throwable $e) {
                    log_message('error', 'Like Notification Error: ' . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Falha ao registrar like']);
        }

        $count = (int) $likeModel->where('post_id', (int) $postId)->countAllResults();
        return $this->response->setJSON(['success' => true, 'liked' => $liked, 'count' => $count]);
    }

    public function searchTags()
    {
        $q     = trim((string) ($this->request->getGet('q') ?? ''));
        $limit = (int) ($this->request->getGet('limit') ?? 5);
        if ($q === '') return $this->response->setJSON(['items' => []]);

        $userModel = new UserModel();
        $barModel  = new PerfilBarClientModel();

        $users = $userModel->like('name', $q)->findAll($limit);
        $bars  = $barModel->like('nome', $q)->findAll($limit);

        $items = [];
        foreach ($users as $u) {
            $items[] = [
                'type'   => 'user',
                'id'     => (int) ($u['id'] ?? 0),
                'name'   => $u['name'] ?? '',
                'avatar' => null,
            ];
        }
        foreach ($bars as $b) {
            $fp = $b['foto_principal'] ?? null;
            if ($fp === 'null' || $fp === '' || $fp === false) $fp = null;
            $items[] = [
                'type'   => 'bar',
                'id'     => (int) ($b['bares_id'] ?? 0),
                'name'   => $b['nome'] ?? '',
                'avatar' => $fp,
            ];
        }

        return $this->response->setJSON(['items' => $items]);
    }

    public function searchGeo()
    {
        $q     = trim((string) ($this->request->getGet('q') ?? ''));
        $limit = (int) ($this->request->getGet('limit') ?? 5);
        if ($q === '') return $this->response->setJSON(['items' => []]);

        $bairro = (new BairroModel())->like('nome', $q)->findAll($limit);
        $cidade = (new CidadeModel())->like('nome', $q)->findAll($limit);
        $rua    = (new RuaModel())->like('nome', $q)->findAll($limit);

        $items = [];
        foreach ($bairro as $b) {
            $items[] = [
                'type' => 'bairro',
                'id'   => (int) ($b['bairro_id'] ?? 0),
                'name' => $b['nome'] ?? '',
            ];
        }
        foreach ($cidade as $c) {
            $items[] = [
                'type' => 'cidade',
                'id'   => (int) ($c['cidade_id'] ?? 0),
                'name' => $c['nome'] ?? '',
            ];
        }
        foreach ($rua as $r) {
            $items[] = [
                'type' => 'rua',
                'id'   => (int) ($r['rua_id'] ?? 0),
                'name' => $r['nome'] ?? '',
            ];
        }

        return $this->response->setJSON(['items' => $items]);
    }

    // Stories
    public function stories()
    {
        $userId = (int) ($this->request->getGet('user_id') ?? session()->get('user_id'));
        $this->purgeExpired(); // limpeza rápida antes de listar
        $model  = new UserStoryModel();
        $stories = $model->where('user_id', $userId)
                         ->where('expires_at >=', date('Y-m-d H:i:s'))
                         ->orderBy('created_at', 'DESC')
                         ->findAll(20);

        foreach ($stories as &$s) {
            if (!empty($s['image_url'])) {
                $val = str_replace('\\', '/', (string) $s['image_url']);
                $prefix = str_replace('\\', '/', ROOTPATH . 'public' . DIRECTORY_SEPARATOR);
                if (strpos($val, $prefix) === 0) {
                    $val = substr($val, strlen($prefix));
                }
                $val = ltrim($val, '/');
                $abs = ROOTPATH . 'public' . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $val), DIRECTORY_SEPARATOR);
                if (!is_file($abs) || @filesize($abs) === 0) {
                    $s['image_url'] = null;
                } else {
                    $s['image_url'] = $val;
                }
            }
        }
        unset($s);
        return $this->response->setJSON(['items' => $stories]);
    }

    public function storyStore()
    {
        $currentUserId = session()->get('user_id');
        $typeUser      = (int)session()->get('type_user');
        $targetUserId  = $this->request->getPost('target_user_id');

        if ($targetUserId && $typeUser === 3) {
            $userId = (int) $targetUserId;
        } else {
            $userId = $currentUserId;
        }

        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }

        if (!$this->rateLimit('story_store', 20, 60)) {
            return $this->response->setStatusCode(429)->setJSON(['error' => 'Muitas requisições']);
        }

        $file = $this->request->getFile('story_image');
        if (!$file) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Imagem não enviada']);
        }
        if (!$file->isValid()) {
            $err = method_exists($file, 'getErrorString') ? $file->getErrorString() : 'Upload inválido';
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Imagem inválida: ' . $err]);
        }
        if ($file->hasMoved()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Imagem já processada']);
        }

        // Valida tamanho de arquivo
        $sizeBytes = (int) $file->getSize();
        if ($sizeBytes <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Arquivo vazio ou corrompido']);
        }
        if ($sizeBytes > 50 * 1024 * 1024) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Imagem muito grande (máx 50MB)']);
        }

        $baseDir = ROOTPATH . 'public/uploads/perfis/' . $userId . '/stories';
        if (!is_dir($baseDir)) mkdir($baseDir, 0777, true);

        $newName = $file->getRandomName();
        // Valida extensão/MIME como no upload de álbum
        $ext = strtolower($file->getClientExtension() ?: $file->getExtension());
        $mime = strtolower($file->getMimeType() ?: '');
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $allowedMimes = ['image/jpeg','image/jpg','image/pjpeg','image/png','image/webp','image/gif'];
        if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Formato de imagem não permitido']);
        }
        if (!$file->move($baseDir, $newName)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Erro ao mover arquivo']);
        }
        $tempPath = $baseDir . DIRECTORY_SEPARATOR . $newName;
        if (!is_file($tempPath) || @filesize($tempPath) === 0) {
            @unlink($tempPath);
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Arquivo vazio ou corrompido']);
        }
        try {
            $finalAbs = $this->portraitSave($tempPath, 540, 900, 5 * 1024 * 1024);
            if (!is_file($finalAbs) || @filesize($finalAbs) === 0 || !@getimagesize($finalAbs)) {
                @unlink($tempPath);
                if ($finalAbs !== $tempPath) @unlink($finalAbs);
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Falha ao processar story']);
            }
            try { $this->makeThumbSquare($finalAbs); } catch (\Throwable $e) { /* ignore */ }
            $relative = str_replace(ROOTPATH . 'public' . DIRECTORY_SEPARATOR, '', $finalAbs);
            $relative = ltrim(str_replace('\\', '/', $relative), '/');
        } catch (\Throwable $e) {
            @unlink($tempPath);
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Erro ao processar story: ' . $e->getMessage()]);
        }

        $expires = date('Y-m-d H:i:s', time() + 24 * 3600);
        $overlayJson = $this->request->getPost('overlay_json');
        if ($overlayJson && strlen($overlayJson) > (2 * 1024 * 1024)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Overlay muito grande']);
        }
        $overlayArr = [];
        if (!empty($overlayJson)) {
            $tmp = json_decode($overlayJson, true);
            if (is_array($tmp)) $overlayArr = $tmp;
        }
        $audioExternal = trim((string) ($this->request->getPost('audio_external_url') ?? ''));
        if ($audioExternal !== '') {
            $overlayArr['audio'] = ['src' => $audioExternal, 'type' => 'external'];
        }
        $audioFile = $this->request->getFile('story_audio');
        if ($audioFile && $audioFile->isValid() && !$audioFile->hasMoved()) {
            if ((int) $audioFile->getSize() > 10 * 1024 * 1024) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Áudio muito grande (máx 10MB)']);
            }
            $mime = strtolower($audioFile->getMimeType() ?? '');
            $allowedAudio = ['audio/mpeg','audio/mp3','audio/ogg','audio/webm','audio/wav'];
            if (!in_array($mime, $allowedAudio)) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Tipo de áudio não permitido']);
            }
            $audioDir = ROOTPATH . 'public/uploads/perfis/' . $userId . '/stories/audio';
            if (!is_dir($audioDir)) mkdir($audioDir, 0777, true);
            $audioName = $audioFile->getRandomName();
            if (!$audioFile->move($audioDir, $audioName)) {
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Erro ao mover áudio']);
            }
            $audioAbs = $audioDir . DIRECTORY_SEPARATOR . $audioName;
            if (!is_file($audioAbs) || @filesize($audioAbs) === 0) {
                @unlink($audioAbs);
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Arquivo de áudio vazio ou corrompido']);
            }
            $audioRel = str_replace(ROOTPATH . 'public' . DIRECTORY_SEPARATOR, '', $audioAbs);
            $audioRel = ltrim(str_replace('\\', '/', $audioRel), '/');
            $overlayArr['audio'] = ['src' => '/' . $audioRel, 'type' => 'upload'];
        }
        $finalOverlayJson = !empty($overlayArr) ? json_encode($overlayArr) : ($overlayJson ?: null);

        $model = new UserStoryModel();
        $model->insert([
            'user_id'      => $userId,
            'image_url'    => $relative,
            'expires_at'   => $expires,
            'overlay_json' => $finalOverlayJson,
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    public function storyDelete($id)
    {
        $userId   = (int) (session()->get('user_id') ?? 0);
        $typeUser = (int) (session()->get('type_user') ?? 0);
        $model    = new UserStoryModel();
        $story    = $model->find($id);

        if (!$story || ((int) $story['user_id'] !== $userId && $typeUser !== 3)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sem permissão']);
        }

        // Remove arquivo físico
        if (!empty($story['image_url'])) {
            $abs = ROOTPATH . 'public' . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $story['image_url']), DIRECTORY_SEPARATOR);
            if (is_file($abs)) @unlink($abs);
            $thumb = preg_replace('/(\\.[a-z0-9]+)$/i','_thumb.jpg',$abs);
            if (is_file($thumb)) @unlink($thumb);
        }
        // Remove áudio físico se existir e for upload
        if (!empty($story['overlay_json'])) {
            $ov = json_decode($story['overlay_json'], true);
            if (is_array($ov) && isset($ov['audio']['type']) && $ov['audio']['type'] === 'upload') {
                $src = (string) ($ov['audio']['src'] ?? '');
                if ($src !== '') {
                    $aabs = ROOTPATH . 'public' . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $src), DIRECTORY_SEPARATOR);
                    if (is_file($aabs)) @unlink($aabs);
                }
            }
        }
        $model->delete($id);
        return $this->response->setJSON(['success' => true]);
    }

    public function purgeExpired()
    {
        // Usuário
        $userModel = new UserStoryModel();
        $expiredUsers = $userModel->where('expires_at <', date('Y-m-d H:i:s'))->findAll(100);
        foreach ($expiredUsers as $s) {
            if (!empty($s['image_url'])) {
                $abs = ROOTPATH . 'public' . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $s['image_url']), DIRECTORY_SEPARATOR);
                if (is_file($abs)) @unlink($abs);
                $thumb = preg_replace('/(\\.[a-z0-9]+)$/i','_thumb.jpg',$abs);
                if (is_file($thumb)) @unlink($thumb);
            }
            $userModel->delete($s['story_id']);
        }
        // Estabelecimento
        $barModel = new BarStoryModel();
        $expiredBars = $barModel->where('expires_at <', date('Y-m-d H:i:s'))->findAll(100);
        foreach ($expiredBars as $s) {
            if (!empty($s['image_url'])) {
                $abs = ROOTPATH . 'public' . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $s['image_url']), DIRECTORY_SEPARATOR);
                if (is_file($abs)) @unlink($abs);
                $thumb = preg_replace('/(\\.[a-z0-9]+)$/i','_thumb.jpg',$abs);
                if (is_file($thumb)) @unlink($thumb);
            }
            $barModel->delete($s['story_id']);
        }
        return $this->response->setJSON(['success' => true]);
    }

    public function barStories($baresId)
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        // Verifica se usuário é dono do bar
        $bar = (new PerfilBarClientModel())->find((int) $baresId);
        if (!$bar || (int) $bar['user_id'] !== $userId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sem permissão']);
        }
        $this->purgeExpired();
        $model = new BarStoryModel();
        $items = $model->where('bares_id', (int) $baresId)
                       ->where('expires_at >=', date('Y-m-d H:i:s'))
                       ->orderBy('created_at', 'DESC')
                       ->findAll(20);
        foreach ($items as &$s) {
            if (!empty($s['image_url'])) {
                $val = str_replace('\\', '/', (string) $s['image_url']);
                $prefix = str_replace('\\', '/', ROOTPATH . 'public' . DIRECTORY_SEPARATOR);
                if (strpos($val, $prefix) === 0) {
                    $val = substr($val, strlen($prefix));
                }
                $val = ltrim($val, '/');
                $abs = ROOTPATH . 'public' . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $val), DIRECTORY_SEPARATOR);
                if (!is_file($abs) || @filesize($abs) === 0) {
                    $s['image_url'] = null;
                } else {
                    $s['image_url'] = $val;
                }
            }
        }
        unset($s);
        return $this->response->setJSON(['items' => $items]);
    }

    public function barStoryStore($baresId)
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        $bar = (new PerfilBarClientModel())->find((int) $baresId);
        if (!$bar || (int) $bar['user_id'] !== $userId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sem permissão']);
        }
        if (!$this->rateLimit('bar_story_store', 20, 60)) {
            return $this->response->setStatusCode(429)->setJSON(['error' => 'Muitas requisições']);
        }
        $file = $this->request->getFile('story_image');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Imagem inválida']);
        }
        if ((int) $file->getSize() > 50 * 1024 * 1024) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Imagem muito grande']);
        }
        $baseDir = ROOTPATH . 'public/uploads/bares/' . (int) $baresId . '/stories';
        if (!is_dir($baseDir)) mkdir($baseDir, 0777, true);
        $newName = $file->getRandomName();
        if (!$file->move($baseDir, $newName)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Erro ao mover arquivo']);
        }
        $tempPath = $baseDir . DIRECTORY_SEPARATOR . $newName;
        if (!is_file($tempPath) || @filesize($tempPath) === 0) {
            @unlink($tempPath);
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Arquivo vazio ou corrompido']);
        }
        try {
            $finalAbs = $this->portraitSave($tempPath, 540, 900, 5 * 1024 * 1024);
            if (!is_file($finalAbs) || @filesize($finalAbs) === 0 || !@getimagesize($finalAbs)) {
                @unlink($tempPath);
                if ($finalAbs !== $tempPath) @unlink($finalAbs);
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Falha ao processar story']);
            }
            try { $this->makeThumbSquare($finalAbs); } catch (\Throwable $e) { /* ignore */ }
            $relative = str_replace(ROOTPATH . 'public' . DIRECTORY_SEPARATOR, '', $finalAbs);
            $relative = ltrim(str_replace('\\', '/', $relative), '/');
        } catch (\Throwable $e) {
            @unlink($tempPath);
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Erro ao processar story: ' . $e->getMessage()]);
        }
        $expires = date('Y-m-d H:i:s', time() + 24 * 3600);
        $overlayJson = $this->request->getPost('overlay_json');
        if ($overlayJson && strlen($overlayJson) > (2 * 1024 * 1024)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Overlay muito grande']);
        }
        $model = new BarStoryModel();
        $model->insert([
            'bares_id'     => (int) $baresId,
            'user_id'      => $userId,
            'image_url'    => $relative,
            'overlay_json' => $overlayJson ?: null,
            'expires_at'   => $expires,
        ]);
        return $this->response->setJSON(['success' => true]);
    }

    public function barStoryDelete($baresId, $storyId)
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        $bar = (new PerfilBarClientModel())->find((int) $baresId);
        if (!$bar || (int) $bar['user_id'] !== $userId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sem permissão']);
        }
        $model = new BarStoryModel();
        $story = $model->find((int) $storyId);
        if (!$story || (int) $story['bares_id'] !== (int) $baresId) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Story não encontrado']);
        }
        if (!empty($story['image_url'])) {
            $abs = ROOTPATH . 'public' . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $story['image_url']), DIRECTORY_SEPARATOR);
            if (is_file($abs)) @unlink($abs);
        }
        $model->delete((int) $storyId);
        return $this->response->setJSON(['success' => true]);
    }

    public function follow()
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }

        if (!$this->rateLimit('follow', 60, 60)) {
            return $this->response->setStatusCode(429)->setJSON(['error' => 'Muitas requisições']);
        }

        $targetId   = (int) ($this->request->getPost('target_id') ?? 0);
        $targetType = $this->request->getPost('target_type') === 'bar' ? 'bar' : 'user';

        if ($targetId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Destino inválido']);
        }

        $model    = new FollowModel();
        $existing = $model->where('follower_id', $userId)
                          ->where('follower_type', 'user')
                          ->where('target_id', $targetId)
                          ->where('target_type', $targetType)
                          ->first();

        if ($existing) {
            return $this->response->setJSON(['success' => true, 'status' => $existing['status']]);
        }

        $status    = 'approved';
        $userModel = new UserModel();

        if ($targetType === 'user') {
            $u        = $userModel->find($targetId);
            $isPrivate = isset($u['is_private']) ? (bool) $u['is_private'] : false;
            $status   = $isPrivate ? 'pending' : 'approved';
        }

        $model->insert([
            'follower_id'   => $userId,
            'follower_type' => 'user',
            'target_id'     => $targetId,
            'target_type'   => $targetType,
            'status'        => $status,
        ]);

        return $this->response->setJSON(['success' => true, 'status' => $status]);
    }

    public function unfollow()
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }

        $targetId   = (int) ($this->request->getPost('target_id') ?? 0);
        $targetType = $this->request->getPost('target_type') === 'bar' ? 'bar' : 'user';

        $model    = new FollowModel();
        $existing = $model->where('follower_id', $userId)
                          ->where('follower_type', 'user')
                          ->where('target_id', $targetId)
                          ->where('target_type', $targetType)
                          ->first();

        if ($existing) $model->delete($existing['id']);

        return $this->response->setJSON(['success' => true]);
    }

    public function followers()
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }

        $model = new FollowModel();
        $items = $model->where('target_id', $userId)
                       ->where('target_type', 'user')
                       ->findAll(50);

        return $this->response->setJSON(['items' => $items]);
    }

    public function following()
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }

        $model = new FollowModel();
        $items = $model->where('follower_id', $userId)
                       ->where('follower_type', 'user')
                       ->findAll(50);

        return $this->response->setJSON(['items' => $items]);
    }

    public function approveFollow($id)
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }

        $model = new FollowModel();
        $rel   = $model->find($id);

        if (!$rel || $rel['target_type'] !== 'user' || (int) $rel['target_id'] !== $userId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sem permissão']);
        }

        if ($rel['status'] !== 'pending') {
            return $this->response->setJSON(['success' => true, 'status' => $rel['status']]);
        }

        $model->update($id, ['status' => 'approved']);
        return $this->response->setJSON(['success' => true]);
    }

    public function rejectFollow($id)
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }

        $model = new FollowModel();
        $rel   = $model->find($id);

        if (!$rel || $rel['target_type'] !== 'user' || (int) $rel['target_id'] !== $userId) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Sem permissão']);
        }

        $model->update($id, ['status' => 'blocked']);
        return $this->response->setJSON(['success' => true]);
    }

    public function feed()
    {
        $userId = (int) (session()->get('user_id') ?? 0);
        if (!$userId) {
            return $this->response->setStatusCode(401)->setJSON(['error' => 'Não autenticado']);
        }

        $limit       = (int) ($this->request->getGet('limit') ?? 20);
        $followModel = new FollowModel();

        $approved = $followModel->where('follower_id', $userId)
                                ->where('follower_type', 'user')
                                ->where('status', 'approved')
                                ->findAll(200);

        $followUserIds = [];
        foreach ($approved as $f) {
            if (($f['target_type'] ?? '') === 'user') {
                $followUserIds[] = (int) ($f['target_id'] ?? 0);
            }
        }

        $postModel     = new UserPostModel();
        $postsFollowed = [];
        if (!empty($followUserIds)) {
            $postsFollowed = $postModel->whereIn('user_id', $followUserIds)
                                       ->orderBy('created_at', 'DESC')
                                       ->findAll($limit);
        }

        $tagModel     = new UserPostTagModel();
        $barTaggedIds = $tagModel->where('target_type', 'bar')
                                 ->orderBy('post_id', 'DESC')
                                 ->findAll($limit * 2);

        $barPostIds = array_values(array_unique(array_map(function ($r) {
            return (int) ($r['post_id'] ?? 0);
        }, $barTaggedIds)));

        $postsBars = [];
        if (!empty($barPostIds)) {
            $postsBars = $postModel->whereIn('post_id', $barPostIds)
                                   ->orderBy('created_at', 'DESC')
                                   ->findAll($limit);
        }

        $takeBars   = (int) floor($limit * 0.6);
        $takeFollow = $limit - $takeBars;

        $barsSel   = array_slice($postsBars, 0, $takeBars);
        $followSel = array_slice($postsFollowed, 0, $takeFollow);

        $combined = [];
        $i = 0;
        $j = 0;
        while (count($combined) < $limit && ($i < count($barsSel) || $j < count($followSel))) {
            if ($i < count($barsSel)) {
                $combined[] = $barsSel[$i];
                $i++;
            }
            if (count($combined) >= $limit) break;
            if ($j < count($followSel)) {
                $combined[] = $followSel[$j];
                $j++;
            }
        }

        return $this->response->setJSON([
            'items' => $combined,
            'count' => count($combined),
            'limit' => $limit,
        ]);
    }
}
