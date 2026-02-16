<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventType;
use App\Models\Establishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index()
    {
        $userEstablishments = Establishment::where('user_id', Auth::id())->orderBy('nome')->get(['bares_id','nome']);
        $selectedBares = request('bares_id');

        // Base query limitada aos bares do usuário
        $base = Event::query()->whereIn('bares_id', $userEstablishments->pluck('bares_id'));
        if ($selectedBares) $base->where('bares_id', $selectedBares);

        // Estabelecimentos para filtro: apenas os que têm eventos
        $baresComEventosIds = Event::query()
            ->whereIn('bares_id', $userEstablishments->pluck('bares_id'))
            ->distinct()->pluck('bares_id')->all();
        $establishments = $userEstablishments->whereIn('bares_id', $baresComEventosIds)->values();

        // Tipos para filtro: apenas os presentes em eventos (aplica bares se selecionado)
        $tipoIdsQuery = Event::query()->whereIn('bares_id', $userEstablishments->pluck('bares_id'));
        if ($selectedBares) $tipoIdsQuery->where('bares_id', $selectedBares);
        $tiposIds = $tipoIdsQuery->whereNotNull('tipo_evento_id')->distinct()->pluck('tipo_evento_id')->all();
        $types = EventType::whereIn('tipo_evento_id', $tiposIds)->where('ativo',1)->orderBy('nome')->get(['tipo_evento_id','nome']);

        // Aplicar demais filtros
        if ($t = request('tipo_evento_id')) $base->where('tipo_evento_id', $t);
        if ($s = request('status')) $base->where('status', $s);
        if ($v = request('visibilidade')) $base->where('visibilidade', $v);
        if ($mi = request('idade_minima_min')) $base->where('idade_minima', '>=', (int)$mi);
        if ($ma = request('idade_minima_max')) $base->where('idade_minima', '<=', (int)$ma);

        $events = $base->with(['establishment','type'])->orderBy('data_inicio','desc')->paginate(20);

        return view('dashboard.barista.events.index', compact('events','establishments','selectedBares','types'));
    }

    public function show($id)
    {
        $event = Event::whereHas('establishment', function($q){ $q->where('user_id', Auth::id()); })
            ->with(['establishment','type'])
            ->findOrFail($id);
        return view('dashboard.barista.events.show', compact('event'));
    }

    public function create()
    {
        $establishments = Establishment::where('user_id', Auth::id())->orderBy('nome')->get(['bares_id','nome']);
        $types = EventType::where('ativo',1)->orderBy('nome')->get(['tipo_evento_id','nome']);
        return view('dashboard.barista.events.create', compact('establishments','types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bares_id' => 'required|exists:form_perfil_bares_tb,bares_id',
            'tipo_evento_id' => 'nullable|exists:evt_tipo_evento_tb,tipo_evento_id',
            'nome' => 'required|string|max:150',
            'descricao' => 'nullable|string',
            'local_customizado' => 'nullable|string|max:255',
            'endereco_evento' => 'nullable|string|max:255',
            'latitude_evento' => 'nullable|numeric',
            'longitude_evento' => 'nullable|numeric',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'hora_abertura_portas' => 'nullable|date_format:H:i',
            'lotacao_maxima' => 'nullable|integer|min:0',
            'idade_minima' => 'nullable|integer|min:0',
            'status' => 'required|in:rascunho,publicado,encerrado,cancelado',
            'visibilidade' => 'required|in:publico,privado,nao_listado',
            'video_youtube_url' => 'nullable|url|max:255',
            'imagem_capa' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'logo_img_1' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'logo_img_2' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'logo_img_3' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'logo_img_4' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_destaque' => 'nullable|boolean',
            'comprovante_pagamento' => 'nullable|mimes:jpg,jpeg,png,webp,pdf|max:8192',
        ]);
        if (\Illuminate\Support\Facades\Schema::hasColumn('evt_eventos_tb', 'is_destaque')) {
            $data['is_destaque'] = (bool) ($data['is_destaque'] ?? false);
        } else {
            unset($data['is_destaque']);
        }
        $event = Event::create($data);
        if ($request->hasFile('imagem_capa')) {
            $this->handleEventCover($request, $event);
        }
        // Upload de logos
        $this->handleEventLogos($request, $event);
        // Comprovante de pagamento
        $this->handlePaymentReceipt($request, $event);
        return redirect()->route('dashboard.barista.events.index')->with('ok','Evento criado');
    }

    public function edit($id)
    {
        $event = Event::whereHas('establishment', function($q){ $q->where('user_id', Auth::id()); })->findOrFail($id);
        $establishments = Establishment::where('user_id', Auth::id())->orderBy('nome')->get(['bares_id','nome']);
        $types = EventType::where('ativo',1)->orderBy('nome')->get(['tipo_evento_id','nome']);
        return view('dashboard.barista.events.edit', compact('event','establishments','types'));
    }

    public function update(Request $request, $id)
    {
        $event = Event::whereHas('establishment', function($q){ $q->where('user_id', Auth::id()); })->findOrFail($id);
        $data = $request->validate([
            'bares_id' => 'required|exists:form_perfil_bares_tb,bares_id',
            'tipo_evento_id' => 'nullable|exists:evt_tipo_evento_tb,tipo_evento_id',
            'nome' => 'required|string|max:150',
            'descricao' => 'nullable|string',
            'local_customizado' => 'nullable|string|max:255',
            'endereco_evento' => 'nullable|string|max:255',
            'latitude_evento' => 'nullable|numeric',
            'longitude_evento' => 'nullable|numeric',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'hora_abertura_portas' => 'nullable|date_format:H:i',
            'lotacao_maxima' => 'nullable|integer|min:0',
            'idade_minima' => 'nullable|integer|min:0',
            'status' => 'required|in:rascunho,publicado,encerrado,cancelado',
            'visibilidade' => 'required|in:publico,privado,nao_listado',
            'video_youtube_url' => 'nullable|url|max:255',
            'imagem_capa' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'logo_img_1' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'logo_img_2' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'logo_img_3' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'logo_img_4' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'is_destaque' => 'nullable|boolean',
            'comprovante_pagamento' => 'nullable|mimes:jpg,jpeg,png,webp,pdf|max:8192',
        ]);
        if (\Illuminate\Support\Facades\Schema::hasColumn('evt_eventos_tb', 'is_destaque')) {
            $data['is_destaque'] = (bool) ($data['is_destaque'] ?? false);
        } else {
            unset($data['is_destaque']);
        }
        $event->update($data);
        if ($request->hasFile('imagem_capa')) {
            $this->handleEventCover($request, $event);
        }
        $this->handleEventLogos($request, $event);
        $this->handlePaymentReceipt($request, $event);
        return redirect()->route('dashboard.barista.events.index')->with('ok','Evento atualizado');
    }

    public function destroy($id)
    {
        $event = Event::whereHas('establishment', function($q){ $q->where('user_id', Auth::id()); })->findOrFail($id);
        $event->status = 'cancelado';
        $event->save();
        $event->delete();
        return redirect()->route('dashboard.barista.events.index')->with('ok','Evento cancelado');
    }
    
    protected function handleEventLogos(Request $request, Event $event): void
    {
        $slug = \Illuminate\Support\Str::slug($event->nome ?: 'evento');
        $dir = public_path("uploads/eventos/{$event->evento_id}_{$slug}/logos");
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
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
                $event->update([$field => $rel]);
            }
        }
    }

    protected function handleEventCover(Request $request, Event $event): void
    {
        if (!$request->hasFile('imagem_capa')) {
            return;
        }
        $file = $request->file('imagem_capa');
        $slug = \Illuminate\Support\Str::slug($event->nome ?: 'evento');
        $dir = public_path("uploads/eventos/{$event->evento_id}_{$slug}");
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $ext = 'jpg';
        }
        $name = "capa." . $ext;
        $path = $dir . DIRECTORY_SEPARATOR . $name;
        $file->move($dir, $name);
        $this->enforceCoverAspect($path);
        $rel = str_replace(public_path(), '', $path);
        $rel = str_replace('\\', '/', $rel);
        $rel = $rel[0] === '/' ? $rel : ('/' . $rel);
        $event->update(['imagem_capa' => $rel]);
    }

    protected function enforceCoverAspect(string $absolutePath): void
    {
        if (!is_file($absolutePath)) {
            return;
        }
        $info = @getimagesize($absolutePath);
        if (!$info || empty($info[0]) || empty($info[1])) {
            return;
        }
        $w = (int) $info[0];
        $h = (int) $info[1];
        if ($w <= 0 || $h <= 0) {
            return;
        }
        $mime = strtolower((string) ($info['mime'] ?? 'image/jpeg'));
        $src = null;
        if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
            $src = @imagecreatefromjpeg($absolutePath);
        } elseif ($mime === 'image/png' && function_exists('imagecreatefrompng')) {
            $src = @imagecreatefrompng($absolutePath);
        } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
            $src = @imagecreatefromwebp($absolutePath);
        } else {
            $data = @file_get_contents($absolutePath);
            if ($data) {
                $src = @imagecreatefromstring($data);
            }
        }
        if (!$src) {
            return;
        }
        $landscape = $w >= $h;
        $desiredRatio = $landscape ? (16 / 9) : (9 / 16);
        $currentRatio = $w / $h;
        $tol = 0.02;
        if (abs($currentRatio - $desiredRatio) <= $tol) {
            imagedestroy($src);
            return;
        }
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
        if ($cropW <= 0 || $cropH <= 0) {
            imagedestroy($src);
            return;
        }
        $dstW = $cropW;
        $dstH = $cropH;
        $dst = imagecreatetruecolor($dstW, $dstH);
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $transparent);
        }
        if (!imagecopyresampled($dst, $src, 0, 0, $x, $y, $dstW, $dstH, $cropW, $cropH)) {
            imagedestroy($src);
            imagedestroy($dst);
            return;
        }
        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $ok = false;
        if (($ext === 'jpg' || $ext === 'jpeg') && function_exists('imagejpeg')) {
            $ok = @imagejpeg($dst, $absolutePath, 85);
        } elseif ($ext === 'png' && function_exists('imagepng')) {
            $ok = @imagepng($dst, $absolutePath, 6);
        } elseif ($ext === 'webp' && function_exists('imagewebp')) {
            $ok = @imagewebp($dst, $absolutePath, 85);
        } elseif (function_exists('imagejpeg')) {
            $ok = @imagejpeg($dst, $absolutePath, 85);
        }
        imagedestroy($src);
        imagedestroy($dst);
        if (!$ok) {
            return;
        }
    }

    protected function handlePaymentReceipt(Request $request, Event $event): void
    {
        if (!$request->hasFile('comprovante_pagamento')) return;
        $file = $request->file('comprovante_pagamento');
        $slug = \Illuminate\Support\Str::slug($event->nome ?: 'evento');
        $dir = public_path("uploads/eventos/{$event->evento_id}_{$slug}/comprovantes");
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        $ext = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        if (!in_array($ext, ['jpg','jpeg','png','webp','pdf'])) $ext = 'pdf';
        $name = "comprovante_".date('Ymd_His').".".$ext;
        $path = $dir . DIRECTORY_SEPARATOR . $name;
        $file->move($dir, $name);
        $rel = str_replace(public_path(), '', $path);
        $rel = str_replace('\\','/',$rel);
        $rel = $rel[0] === '/' ? $rel : ('/'.$rel);
        $event->update(['comprovante_pagamento' => $rel]);
    }
}
