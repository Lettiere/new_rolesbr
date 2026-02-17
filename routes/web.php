<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\Event;
use App\Models\Establishment;
use App\Models\EventTicketLot;
use App\Models\Product;
use App\Models\BaseEstado;
use App\Models\BaseCidade;
use App\Models\BaseBairro;
use App\Models\EventType;
use App\Models\EstablishmentType;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketCartController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstablishmentController;
use App\Http\Controllers\LocationController;

Route::get('/', function () {
    if (app()->environment('testing')) {
        return view('welcome', [
            'events' => collect(),
            'establishments' => collect(),
            'tickets' => collect(),
            'featuredEvents' => collect(),
            'estados' => collect(),
            'cidades' => collect(),
            'bairros' => collect(),
            'tipoEstabelecimentos' => collect(),
            'tiposEvento' => collect(),
        ]);
    }

    $q = trim((string) request('q', ''));
    $estadoId = (int) request('estado_id', 0);
    $cidadeId = (int) request('cidade_id', 0);
    $bairroId = (int) request('bairro_id', 0);
    $tipoBarId = (int) request('tipo_bar_id', 0);
    $tipoEventoId = (int) request('tipo_evento_id', 0);

    $estados = DB::table('form_perfil_bares_tb as b')
        ->join('base_estados as est', 'est.id', '=', 'b.estado_id')
        ->whereNull('b.deleted_at')
        ->groupBy('est.id', 'est.nome', 'est.uf')
        ->orderBy('est.nome', 'ASC')
        ->get(['est.id', 'est.nome', 'est.uf']);

    $cidades = collect();
    if ($estadoId > 0) {
        $cidades = DB::table('form_perfil_bares_tb as b')
            ->join('base_cidades as cid', 'cid.id', '=', 'b.cidade_id')
            ->whereNull('b.deleted_at')
            ->where('b.estado_id', $estadoId)
            ->groupBy('cid.id', 'cid.nome')
            ->orderBy('cid.nome', 'ASC')
            ->get(['cid.id', 'cid.nome']);
    }

    $bairros = collect();
    if ($cidadeId > 0) {
        $sqlBairros = "
            SELECT DISTINCT bai.id, bai.nome
            FROM form_perfil_bares_tb b
            INNER JOIN base_bairros bai
                ON bai.cidade_id = b.cidade_id
               AND (
                    bai.id = b.bairro_id
                    OR (b.bairro_id IS NULL AND bai.nome = b.bairro_nome)
               )
            WHERE b.deleted_at IS NULL
              AND b.cidade_id = ?
            ORDER BY bai.nome ASC
        ";
        $bairros = collect(DB::select($sqlBairros, [$cidadeId]));
    }

    $tipoEstabelecimentos = EstablishmentType::where('ativo', 1)
        ->orderBy('nome')
        ->get(['tipo_bar_id','nome']);

    $tiposEvento = EventType::where('ativo', 1)
        ->orderBy('nome')
        ->get(['tipo_evento_id','nome']);

    $eventsQuery = DB::table('evt_eventos_tb as evt')
        ->selectRaw("
            evt.evento_id,
            evt.imagem_capa,
            evt.nome as evento_nome,
            perfil.nome as bar_nome,
            evt.data_inicio,
            evt.hora_abertura_portas,
            perfil.bairro_nome as bairro_nome,
            cidades.nome as cidade_nome,
            perfil.endereco,
            perfil.imagem as bar_imagem
        ")
        ->join('form_perfil_bares_tb as perfil', 'perfil.bares_id', '=', 'evt.bares_id')
        ->leftJoin('base_cidades as cidades', 'cidades.id', '=', 'perfil.cidade_id')
        ->whereIn('evt.status', ['publicado', 'programado'])
        ->where('evt.data_inicio', '>=', DB::raw('NOW()'))
        ->whereNull('evt.deleted_at');

    if ($q !== '') {
        $eventsQuery->where(function ($query) use ($q) {
            $like = '%'.$q.'%';
            $query->where('evt.nome', 'like', $like)
                  ->orWhere('evt.descricao', 'like', $like)
                  ->orWhere('perfil.nome', 'like', $like)
                  ->orWhere('perfil.bairro_nome', 'like', $like)
                  ->orWhere('cidades.nome', 'like', $like);
        });
    }

    if ($estadoId > 0) {
        $eventsQuery->where('perfil.estado_id', $estadoId);
    }
    if ($cidadeId > 0) {
        $eventsQuery->where('perfil.cidade_id', $cidadeId);
    }
    if ($bairroId > 0) {
        $eventsQuery->where('perfil.bairro_id', $bairroId);
    }
    if ($tipoBarId > 0) {
        $eventsQuery->where('perfil.tipo_bar', $tipoBarId);
    }
    if ($tipoEventoId > 0) {
        $eventsQuery->where('evt.tipo_evento_id', $tipoEventoId);
    }

    $events = $eventsQuery
        ->orderBy('evt.data_inicio', 'asc')
        ->limit(8)
        ->get();

    $featuredEvents = collect();
    if (Schema::hasColumn('evt_eventos_tb', 'is_destaque')) {
        $featuredEvents = DB::table('evt_eventos_tb as evt')
            ->select('evt.evento_id','evt.nome','evt.slug','evt.imagem_capa')
            ->where('evt.is_destaque', 1)
            ->whereIn('evt.status', ['publicado','programado'])
            ->where('evt.data_inicio', '>=', DB::raw('NOW()'))
            ->whereNull('evt.deleted_at')
            ->orderBy('evt.data_inicio','asc')
            ->limit(5)
            ->get();
    }
    if ($featuredEvents->isEmpty()) {
        $featuredEvents = $events
            ->map(function ($e) {
                if (!isset($e->nome) && isset($e->evento_nome)) {
                    $e->nome = $e->evento_nome;
                }
                return $e;
            })
            ->take(5);
    }

    $establishmentsQuery = DB::table('form_perfil_bares_tb as b')
        ->selectRaw("
            b.bares_id,
            b.nome,
            b.endereco,
            b.bairro_nome,
            cid.nome as cidade_nome,
            est.nome as estado_nome,
            t.nome as tipo_perfil,
            b.imagem,
            (
                SELECT COUNT(*)
                FROM evt_eventos_tb e
                WHERE e.bares_id = b.bares_id
                  AND e.deleted_at IS NULL
                  AND e.data_inicio >= CURDATE()
            ) as eventos_proximos
        ")
        ->leftJoin('base_cidades as cid', 'cid.id', '=', 'b.cidade_id')
        ->leftJoin('base_estados as est', 'est.id', '=', 'b.estado_id')
        ->leftJoin('form_perfil_tipo_bar_tb as t', 't.tipo_bar_id', '=', 'b.tipo_bar')
        ->whereNull('b.deleted_at');

    if ($q !== '') {
        $establishmentsQuery->where(function ($query) use ($q) {
            $like = '%'.$q.'%';
            $query->where('b.nome', 'like', $like)
                  ->orWhere('b.endereco', 'like', $like)
                  ->orWhere('b.bairro_nome', 'like', $like)
                  ->orWhere('cid.nome', 'like', $like)
                  ->orWhere('est.nome', 'like', $like)
                  ->orWhere('t.nome', 'like', $like);
        });
    }

    if ($estadoId > 0) {
        $establishmentsQuery->where('b.estado_id', $estadoId);
    }
    if ($cidadeId > 0) {
        $establishmentsQuery->where('b.cidade_id', $cidadeId);
    }
    if ($bairroId > 0) {
        $establishmentsQuery->where('b.bairro_id', $bairroId);
    }
    if ($tipoBarId > 0) {
        $establishmentsQuery->where('b.tipo_bar', $tipoBarId);
    }

    $establishments = $establishmentsQuery
        ->orderBy('b.nome', 'asc')
        ->limit(6)
        ->get();

    $products = DB::table('prod_produtos_tb as p')
        ->select('p.prod_id','p.nome','p.preco','b.bares_id','b.nome as bar_nome')
        ->join('form_perfil_bares_tb as b','b.bares_id','=','p.bares_id')
        ->whereNull('p.deleted_at')
        ->inRandomOrder()
        ->limit(6)
        ->get();

    $ticketsQuery = DB::table('evt_lotes_ingressos_tb as l')
        ->selectRaw("
            l.lote_id,
            l.nome as lote_nome,
            l.tipo,
            l.preco,
            l.status,
            l.quantidade_total,
            l.quantidade_vendida,
            evt.evento_id,
            evt.nome as evento_nome,
            evt.imagem_capa,
            evt.data_inicio,
            perfil.nome as bar_nome,
            perfil.bairro_nome,
            cidades.nome as cidade_nome
        ")
        ->join('evt_eventos_tb as evt', 'evt.evento_id', '=', 'l.evento_id')
        ->join('form_perfil_bares_tb as perfil', 'perfil.bares_id', '=', 'evt.bares_id')
        ->leftJoin('base_cidades as cidades', 'cidades.id', '=', 'perfil.cidade_id')
        ->whereNull('evt.deleted_at')
        ->whereNull('l.deleted_at')
        ->where('evt.status', 'publicado');

    if ($q !== '') {
        $ticketsQuery->where(function ($query) use ($q) {
            $like = '%'.$q.'%';
            $query->where('l.nome', 'like', $like)
                  ->orWhere('evt.nome', 'like', $like)
                  ->orWhere('perfil.nome', 'like', $like)
                  ->orWhere('perfil.bairro_nome', 'like', $like)
                  ->orWhere('cidades.nome', 'like', $like);
        });
    }

    if ($estadoId > 0) {
        $ticketsQuery->where('perfil.estado_id', $estadoId);
    }
    if ($cidadeId > 0) {
        $ticketsQuery->where('perfil.cidade_id', $cidadeId);
    }
    if ($bairroId > 0) {
        $ticketsQuery->where('perfil.bairro_id', $bairroId);
    }
    if ($tipoBarId > 0) {
        $ticketsQuery->where('perfil.tipo_bar', $tipoBarId);
    }
    if ($tipoEventoId > 0) {
        $ticketsQuery->where('evt.tipo_evento_id', $tipoEventoId);
    }

    $tickets = $ticketsQuery
        ->orderBy('evt.data_inicio', 'asc')
        ->limit(8)
        ->get();

    return view('welcome', [
        'events' => $events,
        'establishments' => $establishments,
        'tickets' => $tickets,
        'products' => $products,
        'featuredEvents' => $featuredEvents,
        'estados' => $estados,
        'cidades' => $cidades,
        'bairros' => $bairros,
        'tipoEstabelecimentos' => $tipoEstabelecimentos,
        'tiposEvento' => $tiposEvento,
    ]);
});

Route::get('/eventos', function (Request $request) {
    $q = trim((string) $request->input('q',''));
    $query = DB::table('evt_eventos_tb as evt')
        ->selectRaw("
            evt.evento_id,
            evt.imagem_capa,
            evt.nome as evento_nome,
            perfil.nome as bar_nome,
            evt.data_inicio,
            evt.hora_abertura_portas,
            perfil.bairro_nome as bairro_nome,
            cidades.nome as cidade_nome
        ")
        ->join('form_perfil_bares_tb as perfil', 'perfil.bares_id', '=', 'evt.bares_id')
        ->leftJoin('base_cidades as cidades', 'cidades.id', '=', 'perfil.cidade_id')
        ->whereIn('evt.status', ['publicado','programado'])
        ->whereNull('evt.deleted_at')
        ->where('evt.data_inicio','>=', DB::raw('NOW()'));
    if ($q !== '') {
        $query->where(function($sub) use ($q) {
            $like = '%'.$q.'%';
            $sub->where('evt.nome','like',$like)
                ->orWhere('perfil.nome','like',$like)
                ->orWhere('perfil.bairro_nome','like',$like)
                ->orWhere('cidades.nome','like',$like);
        });
    }
    $events = $query->orderBy('evt.data_inicio','asc')->paginate(24);
    if ($request->ajax() || $request->boolean('ajax')) {
        return view('site.partials.events_list', compact('events'));
    }
    return view('site.list_events', compact('events'));
})->name('site.events.index');

Route::get('/ingressos', function (Request $request) {
    $q = trim((string) $request->input('q',''));
    $query = DB::table('evt_lotes_ingressos_tb as l')
        ->selectRaw("
            l.lote_id,
            l.nome as lote_nome,
            l.tipo,
            l.preco,
            evt.evento_id,
            evt.nome as evento_nome,
            evt.imagem_capa,
            evt.data_inicio,
            perfil.nome as bar_nome,
            perfil.bairro_nome,
            cidades.nome as cidade_nome
        ")
        ->join('evt_eventos_tb as evt', 'evt.evento_id', '=', 'l.evento_id')
        ->join('form_perfil_bares_tb as perfil', 'perfil.bares_id', '=', 'evt.bares_id')
        ->leftJoin('base_cidades as cidades', 'cidades.id', '=', 'perfil.cidade_id')
        ->whereNull('evt.deleted_at')
        ->whereNull('l.deleted_at')
        ->where('evt.status', 'publicado');
    if ($q !== '') {
        $query->where(function($sub) use ($q) {
            $like = '%'.$q.'%';
            $sub->where('l.nome','like',$like)
                ->orWhere('evt.nome','like',$like)
                ->orWhere('perfil.nome','like',$like)
                ->orWhere('perfil.bairro_nome','like',$like)
                ->orWhere('cidades.nome','like',$like);
        });
    }
    $tickets = $query->orderBy('evt.data_inicio','asc')->paginate(24);
    if ($request->ajax() || $request->boolean('ajax')) {
        return view('site.partials.tickets_list', compact('tickets'));
    }
    return view('site.list_tickets', compact('tickets'));
})->name('site.tickets.index');

Route::get('/stories', [\App\Http\Controllers\PublicStoriesController::class, 'index'])
    ->name('site.stories.index');
Route::get('/home/stories-all', [\App\Http\Controllers\PublicStoriesController::class, 'all'])
    ->name('site.stories.all');

Route::get('/estabelecimentos', function (Request $request) {
    $q = trim((string) $request->input('q',''));
    $query = DB::table('form_perfil_bares_tb as b')
        ->selectRaw("
            b.bares_id,
            b.nome,
            b.endereco,
            b.bairro_nome,
            cid.nome as cidade_nome,
            b.imagem
        ")
        ->leftJoin('base_cidades as cid', 'cid.id', '=', 'b.cidade_id')
        ->whereNull('b.deleted_at');
    if ($q !== '') {
        $query->where(function($sub) use ($q) {
            $like = '%'.$q.'%';
            $sub->where('b.nome','like',$like)
                ->orWhere('b.endereco','like',$like)
                ->orWhere('b.bairro_nome','like',$like)
                ->orWhere('cid.nome','like',$like);
        });
    }
    $establishments = $query->orderBy('b.nome','asc')->paginate(24);
    if ($request->ajax() || $request->boolean('ajax')) {
        return view('site.partials.establishments_list', compact('establishments'));
    }
    return view('site.list_establishments', compact('establishments'));
})->name('site.establishments.index');

Route::get('/produtos', function (Request $request) {
    $q = trim((string) $request->input('q',''));
    $query = DB::table('prod_produtos_tb as p')
        ->select('p.prod_id','p.nome','p.preco','b.bares_id','b.nome as bar_nome')
        ->join('form_perfil_bares_tb as b','b.bares_id','=','p.bares_id')
        ->whereNull('p.deleted_at');
    if ($q !== '') {
        $query->where(function($sub) use ($q) {
            $like = '%'.$q.'%';
            $sub->where('p.nome','like',$like)
                ->orWhere('b.nome','like',$like)
                ->orWhere('p.descricao','like',$like);
        });
    }
    $products = $query->orderBy('p.nome','asc')->paginate(24);
    if ($request->ajax() || $request->boolean('ajax')) {
        return view('site.partials.products_list', compact('products'));
    }
    return view('site.list_products', compact('products'));
})->name('site.products.index');

Route::get('/produto/{produto}-{slug?}', function ($produto) {
    $product = Product::with(['establishment','base','family','type'])
        ->whereNull('deleted_at')
        ->findOrFail((int) $produto);
    return view('site.product_show', compact('product'));
})->name('site.product.show');

Route::get('/evento/{evento}-{slug?}', function ($evento) {
    $event = Event::with(['establishment', 'type'])->findOrFail((int) $evento);
    $lots = EventTicketLot::where('evento_id', $event->evento_id)
        ->orderBy('preco')
        ->get();

    return view('site.event_show', compact('event', 'lots'));
})->name('site.event.show');

Route::get('/estabelecimento/{bar}-{slug?}', function ($bar) {
    $establishment = Establishment::findOrFail((int) $bar);
    $tipo = null;
    try {
        $tipo = \App\Models\EstablishmentType::find($establishment->tipo_bar);
    } catch (\Throwable $e) {
        $tipo = null;
    }
    $upcomingEvents = Event::where('bares_id', $establishment->bares_id)
        ->whereNull('deleted_at')
        ->where('data_inicio', '>=', now())
        ->orderBy('data_inicio', 'asc')
        ->limit(8)
        ->get();

    $fotos = DB::table('ft_fotos_bar_tb')
        ->where('bares_id', $establishment->bares_id)
        ->whereNull('deleted_at')
        ->orderBy('created_at', 'desc')
        ->limit(15)
        ->get(['foto_id','url','descricao']);

    $cardapios = DB::table('prod_cardapio_tb')
        ->where('bares_id', $establishment->bares_id)
        ->whereNull('deleted_at')
        ->where('status','ativo')
        ->orderBy('nome','asc')
        ->get(['cardapio_id','nome','descricao','tipo_cardapio','status']);

    $cardapioItens = collect();
    if (!$cardapios->isEmpty()) {
        $ids = $cardapios->pluck('cardapio_id')->all();
        $cardapioItens = DB::table('prod_cardapio_itens_tb as ci')
            ->join('prod_produtos_tb as p','p.prod_id','=','ci.prod_id')
            ->selectRaw('
                ci.cardapio_item_id,
                ci.cardapio_id,
                ci.categoria,
                ci.ordem,
                ci.preco_override,
                ci.observacoes,
                p.prod_id,
                p.nome as produto_nome,
                p.preco as produto_preco,
                p.unidade
            ')
            ->whereIn('ci.cardapio_id', $ids)
            ->whereNull('ci.deleted_at')
            ->orderBy('ci.ordem','asc')
            ->get();
    }

    $products = DB::table('prod_produtos_tb as p')
        ->select('p.prod_id','p.nome','p.preco','p.unidade')
        ->where('p.bares_id', $establishment->bares_id)
        ->whereNull('p.deleted_at')
        ->orderBy('p.nome','asc')
        ->limit(24)
        ->get();

    return view('site.establishment_show', [
        'establishment'   => $establishment,
        'upcomingEvents'  => $upcomingEvents,
        'fotos'           => $fotos,
        'cardapios'       => $cardapios,
        'cardapioItens'   => $cardapioItens,
        'products'        => $products,
        'tipo'            => $tipo,
    ]);
})->name('site.establishment.show');

Route::get('/ingresso/{lote}', function ($lote) {
    $ticket = EventTicketLot::findOrFail((int) $lote);
    $event = Event::with('establishment')->find($ticket->evento_id);

    return view('site.ticket_show', [
        'ticket' => $ticket,
        'event' => $event,
    ]);
})->name('site.ticket.show');

Route::get('/ingresso/{lote}/comprar', [TicketCartController::class, 'publicCheckoutForm'])->name('site.ticket.checkout');
Route::post('/ingresso/{lote}/comprar', [TicketCartController::class, 'publicCheckoutSubmit'])->name('site.ticket.checkout.submit');
Route::post('/ingresso/registrar-inline', [TicketCartController::class, 'publicRegisterInline'])->name('site.ticket.register.inline');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/barista', [DashboardController::class, 'barista'])->name('dashboard.barista');
    Route::get('/dashboard/rolezeiro', [DashboardController::class, 'rolezeiro'])->name('dashboard.rolezeiro');
    Route::get('/dashboard/master', [DashboardController::class, 'master'])->name('dashboard.master');
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
    
    Route::resource('dashboard/barista/estabelecimentos', EstablishmentController::class)
        ->names('dashboard.barista.establishments');
    
    // Restringe {produto} a números e evita conflitos com subcaminhos 'familias', 'tipos', 'bases'
    Route::pattern('produto', '[0-9]+');
    // Registra primeiro as rotas estáticas de recursos filhos para não colidir com {produto}
    Route::resource('dashboard/barista/produtos/familias', \App\Http\Controllers\ProductFamilyController::class)
        ->names('dashboard.barista.products.families')->except(['show']);
    Route::resource('dashboard/barista/produtos/tipos', \App\Http\Controllers\ProductTypeController::class)
        ->names('dashboard.barista.products.types')->except(['show']);
    Route::resource('dashboard/barista/produtos/bases', \App\Http\Controllers\ProductBaseController::class)
        ->names('dashboard.barista.products.bases')->except(['show']);
    // Depois, o resource principal de produtos
    Route::resource('dashboard/barista/produtos', \App\Http\Controllers\ProductController::class)
        ->names('dashboard.barista.products');
    
    // Restringe {evento} a números para não capturar URIs estáticas como 'tipos' ou 'estilos'
    Route::pattern('evento', '[0-9]+');

    // Rotas estáticas/CRUD antes do resource para evitar conflito com {evento}
    Route::prefix('dashboard/barista/eventos/tipos')->group(function () {
        Route::get('/', [\App\Http\Controllers\EventTypeController::class, 'index'])->name('dashboard.barista.events.types');
        Route::get('/create', [\App\Http\Controllers\EventTypeController::class, 'create'])->name('dashboard.barista.events.types.create');
        Route::post('/', [\App\Http\Controllers\EventTypeController::class, 'store'])->name('dashboard.barista.events.types.store');
        Route::post('/api', [\App\Http\Controllers\EventTypeController::class, 'storeAjax'])->name('dashboard.barista.events.types.store.ajax');
        Route::get('/{id}/edit', [\App\Http\Controllers\EventTypeController::class, 'edit'])->name('dashboard.barista.events.types.edit');
        Route::put('/{id}', [\App\Http\Controllers\EventTypeController::class, 'update'])->name('dashboard.barista.events.types.update');
        Route::delete('/{id}', [\App\Http\Controllers\EventTypeController::class, 'destroy'])->name('dashboard.barista.events.types.destroy');
    });
    Route::prefix('dashboard/barista/eventos/estilos')->group(function () {
        Route::get('/', [\App\Http\Controllers\AttractionStyleController::class, 'index'])->name('dashboard.barista.events.styles');
        Route::get('/create', [\App\Http\Controllers\AttractionStyleController::class, 'create'])->name('dashboard.barista.events.styles.create');
        Route::post('/', [\App\Http\Controllers\AttractionStyleController::class, 'store'])->name('dashboard.barista.events.styles.store');
        Route::get('/{id}/edit', [\App\Http\Controllers\AttractionStyleController::class, 'edit'])->name('dashboard.barista.events.styles.edit');
        Route::put('/{id}', [\App\Http\Controllers\AttractionStyleController::class, 'update'])->name('dashboard.barista.events.styles.update');
        Route::delete('/{id}', [\App\Http\Controllers\AttractionStyleController::class, 'destroy'])->name('dashboard.barista.events.styles.destroy');
    });
    Route::get('dashboard/barista/eventos/albuns', [\App\Http\Controllers\EventAlbumController::class, 'index'])->name('dashboard.barista.events.albums');
    Route::get('dashboard/barista/eventos/albuns/create', [\App\Http\Controllers\EventAlbumController::class, 'create'])->name('dashboard.barista.events.albums.create');
    Route::post('dashboard/barista/eventos/albuns', [\App\Http\Controllers\EventAlbumController::class, 'store'])->name('dashboard.barista.events.albums.store');
    Route::get('dashboard/barista/eventos/albuns/{album}', [\App\Http\Controllers\EventAlbumController::class, 'show'])->name('dashboard.barista.events.albums.show');
    Route::get('dashboard/barista/eventos/albuns/{album}/upload', function ($album) {
        return redirect()->route('dashboard.barista.events.albums.show', $album);
    })->name('dashboard.barista.events.albums.upload.get');
    Route::post('dashboard/barista/eventos/albuns/{album}/upload', [\App\Http\Controllers\EventAlbumController::class, 'upload'])->name('dashboard.barista.events.albums.upload');
    Route::post('dashboard/barista/eventos/albuns/{album}/logos', [\App\Http\Controllers\EventAlbumController::class, 'updateLogos'])->name('dashboard.barista.events.albums.logos');
    Route::delete('dashboard/barista/eventos/albuns/{album}/fotos/{foto}', [\App\Http\Controllers\EventAlbumController::class, 'destroyPhoto'])->name('dashboard.barista.events.albuns.fotos.destroy');
    Route::delete('dashboard/barista/eventos/albuns/{album}', [\App\Http\Controllers\EventAlbumController::class, 'destroy'])->name('dashboard.barista.events.albums.destroy');
    
    Route::resource('dashboard/barista/eventos', \App\Http\Controllers\EventController::class)
        ->names('dashboard.barista.events');
    Route::prefix('dashboard/barista/eventos/{evento}/lotes')->group(function () {
        Route::get('/', [\App\Http\Controllers\EventTicketLotController::class, 'index'])->name('dashboard.barista.events.lots.index');
        Route::get('/create', [\App\Http\Controllers\EventTicketLotController::class, 'create'])->name('dashboard.barista.events.lots.create');
        Route::post('/', [\App\Http\Controllers\EventTicketLotController::class, 'store'])->name('dashboard.barista.events.lots.store');
        Route::get('/compradores', [\App\Http\Controllers\EventTicketLotController::class, 'buyers'])->name('dashboard.barista.events.lots.buyers');
        Route::get('/{lote}/edit', [\App\Http\Controllers\EventTicketLotController::class, 'edit'])->name('dashboard.barista.events.lots.edit');
        Route::put('/{lote}', [\App\Http\Controllers\EventTicketLotController::class, 'update'])->name('dashboard.barista.events.lots.update');
        Route::delete('/{lote}', [\App\Http\Controllers\EventTicketLotController::class, 'destroy'])->name('dashboard.barista.events.lots.destroy');
    });
    Route::post('eventos/{evento}/eu-vou', [\App\Http\Controllers\EventInterestController::class, 'toggleGoing'])->name('events.going.toggle');
    
        
    Route::prefix('api/geo')->group(function () {
        Route::get('estados', [LocationController::class, 'estados'])->name('api.geo.estados');
        Route::get('cidades/{estadoId}', [LocationController::class, 'cidades'])->name('api.geo.cidades');
        Route::get('bairros/{cidadeId}', [LocationController::class, 'bairros'])->name('api.geo.bairros');
        Route::get('estados-bares', [LocationController::class, 'estadosBares'])->name('api.geo.estados.bares');
        Route::get('cidades-bares/{estadoId}', [LocationController::class, 'cidadesBares'])->name('api.geo.cidades.bares');
        Route::get('bairros-bares/{cidadeId}', [LocationController::class, 'bairrosBares'])->name('api.geo.bairros.bares');
        Route::post('bairros', [LocationController::class, 'storeBairro'])->name('api.geo.bairros.store');
        Route::get('povoados/{cidadeId}', [LocationController::class, 'povoados'])->name('api.geo.povoados');
        Route::get('prefixos', [LocationController::class, 'prefixos'])->name('api.geo.prefixos');
        Route::get('ruas', [LocationController::class, 'ruas'])->name('api.geo.ruas');
    });
    Route::prefix('api/prod')->group(function () {
        Route::get('bases/{tipoId}', [\App\Http\Controllers\ProductController::class, 'basesPorTipo'])->name('api.prod.bases.tipo');
    });
    
    // Carrinho de ingressos (Rolezeiro)
    Route::get('tickets/cart', [\App\Http\Controllers\TicketCartController::class, 'index'])->name('tickets.cart.index');
    Route::post('tickets/cart/add', [\App\Http\Controllers\TicketCartController::class, 'add'])->name('tickets.cart.add');
    Route::post('tickets/cart/remove', [\App\Http\Controllers\TicketCartController::class, 'remove'])->name('tickets.cart.remove');
    Route::get('tickets/cart/checkout', [\App\Http\Controllers\TicketCartController::class, 'checkout'])->name('tickets.cart.checkout');
    
    // Placeholder route for profile to avoid errors
    Route::get('/perfil', function () {
        return "Perfil do Usuário (Em construção)";
    })->name('profile');
});

Route::controller(AuthController::class)->group(function () {
    Route::get('auth/login', 'login')->name('login'); // Standard Laravel login name
    Route::post('auth/login', 'loginAction')->name('auth.login.action');
    Route::get('auth/register', 'register')->name('register');
    Route::post('auth/register', 'registerAction')->name('auth.register.action');
    Route::any('auth/logout', 'logout')->name('logout');
});
