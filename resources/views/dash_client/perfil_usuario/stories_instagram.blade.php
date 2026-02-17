@extends('_layout.dashboard.rolezeiro.layout_rolezeiro')

@section('title', 'Stories do Perfil')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 col-lg-4 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle overflow-hidden" style="width:72px;height:72px;background:#e5e7eb;">
                        @php
                            $foto = $perfil->foto_perfil ?? null;
                        @endphp
                        @if($foto)
                            <img src="{{ asset(ltrim($foto, '/')) }}" alt="Perfil" class="w-100 h-100" style="object-fit:cover;">
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                <i class="fas fa-user fa-lg"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold mb-1">{{ auth()->user()->name ?? 'Usuário' }}</div>
                        <div class="small text-muted">
                            Stories ativos por 24 horas após a publicação.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-8 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <h1 class="h5 fw-bold mb-2">Criação de Stories</h1>
                    <p class="small text-muted mb-3">
                        A criação completa de stories está em evolução. Envie uma imagem para publicar um story por 24h.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ url('/stories') }}" class="btn btn-primary btn-sm">
                            Ver stories públicos
                        </a>
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary btn-sm">
                            Voltar para o site
                        </a>
                    </div>
                    @if(session('status'))
                        <div class="alert alert-success mt-3 mb-0">{{ session('status') }}</div>
                    @endif
                    <form class="mt-3" method="post" action="{{ route('dashboard.stories.upload') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-2 align-items-center justify-content-center">
                            <div class="col-12 col-md-6">
                                <input type="file" name="image" class="form-control form-control-sm" accept="image/*" required>
                            </div>
                            <div class="col-12 col-md-auto">
                                <button type="submit" class="btn btn-sm btn-success">Enviar story</button>
                            </div>
                        </div>
                        @error('image')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </form>
                </div>
            </div>
        </div>
    </div>
    @if(!empty($ads))
        <div class="row mt-3">
            @foreach($ads as $ad)
                @php
                    $nomeBar = $ad->nome ?? 'Bar parceiro';
                    $imgBar = $ad->imagem ? asset(ltrim($ad->imagem, '/')) : 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=600&h=400&fit=crop';
                @endphp
                <div class="col-12 col-md-4 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="{{ $imgBar }}" alt="{{ $nomeBar }}" class="card-img-top" style="object-fit:cover;height:140px;">
                        <div class="card-body py-2">
                            <div class="small fw-semibold mb-1 text-truncate">{{ $nomeBar }}</div>
                            <div class="small text-muted">Publicidade de estabelecimentos parceiros.</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
