<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Event;
use App\Models\EventAlbum;
use App\Models\EventAlbumPhoto;

class EventAlbumController extends Controller
{
    public function index()
    {
        $events = Event::whereHas('establishment', function ($q) {
            $q->where('user_id', Auth::id());
        })->orderBy('data_inicio','desc')->paginate(20);
        return view('dashboard.barista.events.albums.index', compact('events'));
    }

    public function create()
    {
        $events = Event::whereHas('establishment', function ($q) {
            $q->where('user_id', Auth::id());
        })->orderBy('data_inicio','desc')->get(['evento_id','nome','data_inicio','bares_id']);
        return view('dashboard.barista.events.albums.create', compact('events'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'evento_id' => 'required|exists:evt_eventos_tb,evento_id',
            'titulo' => 'nullable|string|max:150',
            'descricao' => 'nullable|string',
        ]);
        $event = Event::whereHas('establishment', function ($q) {
            $q->where('user_id', Auth::id());
        })->findOrFail($data['evento_id']);

        $album = EventAlbum::create([
            'evento_id' => $event->evento_id,
            'fotografo_id' => Auth::id(),
            'titulo' => $data['titulo'] ?? $event->nome,
            'descricao' => $data['descricao'] ?? '',
            'data_fotografia' => optional($event->data_inicio)->format('Y-m-d') ?: date('Y-m-d'),
            'status' => 'rascunho',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->route('dashboard.barista.events.albums.show', $album->album_id);
    }

    public function show($albumId)
    {
        $album = EventAlbum::with(['event.establishment','photos'])->findOrFail($albumId);
        if (!$album->event || !$album->event->establishment || $album->event->establishment->user_id !== Auth::id()) {
            abort(403);
        }
        return view('dashboard.barista.events.albums.show', compact('album'));
    }

    public function upload(Request $request, $albumId)
    {
        $album = EventAlbum::with('event.establishment')->findOrFail($albumId);
        if (!$album->event || !$album->event->establishment || $album->event->establishment->user_id !== Auth::id()) {
            abort(403);
        }
        $request->validate([
            'fotos' => 'required|array|min:1|max:20',
            'fotos.*' => 'file|image|max:3072',
        ]);
        $count = EventAlbumPhoto::where('album_id', $album->album_id)->count();
        $files = $request->file('fotos');
        $maxAdd = max(0, 20 - $count);
        if ($maxAdd <= 0) {
            return back()->with('error','Limite de 20 fotos atingido');
        }
        $files = array_slice($files, 0, $maxAdd);

        $event = $album->event;
        $eventSlug = Str::slug($event->nome ?: 'evento');
        $dir = public_path("uploads/eventos/{$event->evento_id}_{$eventSlug}/albuns/{$album->album_id}");
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $startOrder = $count + 1;
        foreach ($files as $idx => $file) {
            $n = $startOrder + $idx;
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            if (!preg_match('/^(jpe?g|png|webp|gif)$/', $ext)) {
                $ext = 'jpg';
            }
            $slug = Str::slug($album->titulo ?: ('album-'.$album->album_id));
            $target = $dir . DIRECTORY_SEPARATOR . "foto_{$n}_{$slug}." . $ext;
            try {
                $this->processAlbumPhoto($file->getRealPath(), $target, $album->event);
            } catch (\Throwable $e) {
                Log::error('Erro ao processar foto de álbum', [
                    'album_id' => $album->album_id,
                    'evento_id' => $album->evento_id ?? null,
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage(),
                ]);
                $file->move($dir, "foto_{$n}_{$slug}." . $ext);
            }
            $rel = str_replace(public_path(), '', $target);
            $rel = str_replace('\\','/',$rel);
            $rel = $rel[0] === '/' ? $rel : ('/'.$rel);
            EventAlbumPhoto::create([
                'album_id' => $album->album_id,
                'nome_arquivo' => $rel,
                'ordem' => $n,
                'eh_thumbnail' => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return redirect()
            ->route('dashboard.barista.events.albums.show', $album->album_id)
            ->with('ok','Fotos enviadas');
    }

    public function destroyPhoto($albumId, $fotoId)
    {
        $album = EventAlbum::with('event.establishment')->findOrFail($albumId);
        if (!$album->event || !$album->event->establishment || $album->event->establishment->user_id !== Auth::id()) {
            abort(403);
        }
        $foto = EventAlbumPhoto::where('album_id',$album->album_id)->findOrFail($fotoId);
        if ($foto->nome_arquivo && file_exists(public_path($foto->nome_arquivo))) {
            @unlink(public_path($foto->nome_arquivo));
        }
        $foto->delete();
        return back()->with('ok','Foto removida');
    }

    public function destroy($albumId)
    {
        $album = EventAlbum::with(['event.establishment','photos'])->findOrFail($albumId);
        if (!$album->event || !$album->event->establishment || $album->event->establishment->user_id !== Auth::id()) {
            abort(403);
        }
        foreach ($album->photos as $p) {
            if ($p->nome_arquivo && file_exists(public_path($p->nome_arquivo))) {
                @unlink(public_path($p->nome_arquivo));
            }
            $p->delete();
        }
        $event = $album->event;
        $eventSlug = Str::slug($event->nome ?: 'evento');
        $dir = public_path("uploads/eventos/{$event->evento_id}_{$eventSlug}/albuns/{$album->album_id}");
        if (is_dir($dir)) { @rmdir($dir); }
        $album->delete();
        return redirect()->route('dashboard.barista.events.albums.index')->with('ok','Álbum apagado');
    }

    public function updateLogos(Request $request, $albumId)
    {
        $album = EventAlbum::with('event.establishment')->findOrFail($albumId);
        if (!$album->event || !$album->event->establishment || $album->event->establishment->user_id !== Auth::id()) {
            abort(403);
        }
        $data = $request->validate([
            'logo_img_1' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'logo_img_2' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'logo_img_3' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'logo_img_4' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        $event = $album->event;
        $slug = Str::slug($event->nome ?: 'evento');
        $dir = public_path("uploads/eventos/{$event->evento_id}_{$slug}/logos");
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $update = [];
        for ($i=1; $i<=4; $i++) {
            $field = "logo_img_{$i}";
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
                if (!in_array($ext, ['jpg','jpeg','png','webp'])) $ext = 'png';
                $name = "logo{$i}.".$ext;
                $path = $dir . DIRECTORY_SEPARATOR . $name;
                $file->move($dir, $name);
                $rel = str_replace(public_path(), '', $path);
                $rel = str_replace('\\','/',$rel);
                $rel = $rel[0] === '/' ? $rel : ('/'.$rel);
                $update[$field] = $rel;
            }
        }
        if ($update) {
            $event->update($update);
        }
        return back()->with('ok','Logos atualizadas. Novas fotos já usarão essas logos.');
    }

    protected function processAlbumPhoto(string $srcPath, string $dstPath, Event $event): void
    {
        $info = @getimagesize($srcPath);
        if (!$info) { @copy($srcPath, $dstPath); return; }
        $w = (int)$info[0]; $h = (int)$info[1];
        $mime = strtolower($info['mime'] ?? 'image/jpeg');
        $src = null;
        if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) $src = @imagecreatefromjpeg($srcPath);
        elseif ($mime === 'image/png' && function_exists('imagecreatefrompng')) $src = @imagecreatefrompng($srcPath);
        elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) $src = @imagecreatefromwebp($srcPath);
        elseif ($mime === 'image/gif' && function_exists('imagecreatefromgif')) $src = @imagecreatefromgif($srcPath);
        else { $data = @file_get_contents($srcPath); if ($data) $src = @imagecreatefromstring($data); }
        if (!$src) { @copy($srcPath, $dstPath); return; }

        $isLandscape = $w >= $h;
        $targetW = $isLandscape ? 800 : 450;
        $targetH = $isLandscape ? 450 : 800;

        $dst = imagecreatetruecolor($targetW, $targetH);
        imagealphablending($dst, false); imagesavealpha($dst, true);
        $white = imagecolorallocate($dst, 255, 255, 255); imagefilledrectangle($dst, 0, 0, $targetW, $targetH, $white);

        $ratioSrc = $w / $h; $ratioDst = $targetW / $targetH;
        if ($ratioSrc > $ratioDst) {
            $newH = $targetH; $newW = (int) round($targetH * $ratioSrc);
        } else {
            $newW = $targetW; $newH = (int) round($targetW / $ratioSrc);
        }
        $tmp = imagecreatetruecolor($newW, $newH);
        imagealphablending($tmp, false); imagesavealpha($tmp, true);
        imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
        $x = (int) floor(($newW - $targetW) / 2);
        $y = (int) floor(($newH - $targetH) / 2);
        imagecopy($dst, $tmp, 0, 0, $x, $y, $targetW, $targetH);
        imagedestroy($tmp);

        $ext = strtolower(pathinfo($dstPath, PATHINFO_EXTENSION));
        if ($ext === 'png') @imagepng($dst, $dstPath, 6);
        elseif ($ext === 'webp' && function_exists('imagewebp')) @imagewebp($dst, $dstPath, 85);
        else @imagejpeg($dst, $dstPath, 85);
        imagedestroy($src); imagedestroy($dst);
    }
}
