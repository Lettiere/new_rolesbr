@extends('_layout.dashboard.barista.layout_barista')
@section('title','Produto')
@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h3 m-0">{{ $product->nome }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('dashboard.barista.products.edit',$product->prod_id) }}" class="btn btn-outline-primary">Editar</a>
        <a href="{{ route('dashboard.barista.products.index') }}" class="btn btn-outline-secondary">Voltar</a>
    </div>
    </div>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-6"><strong>Estabelecimento:</strong> {{ $product->establishment->nome ?? '-' }}</div>
                    <div class="col-md-6"><strong>Status:</strong> <span class="badge {{ $product->status==='ativo'?'bg-success':'bg-secondary' }}">{{ $product->status }}</span></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Tipo:</strong> {{ $product->type->nome ?? '-' }}</div>
                    <div class="col-md-4"><strong>Família:</strong> {{ $product->family->nome ?? '-' }}</div>
                    <div class="col-md-4"><strong>Base:</strong> {{ $product->base->nome ?? '-' }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4"><strong>Preço:</strong> {{ $product->preco !== null ? 'R$ '.number_format($product->preco,2,',','.') : '-' }}</div>
                    <div class="col-md-4"><strong>Unidade:</strong> {{ $product->unidade ?? '-' }}</div>
                    <div class="col-md-4"><strong>Estoque:</strong> {{ $product->quantidade_estoque ?? '-' }}</div>
                </div>
                <div class="mb-2"><strong>Tipo de Produto:</strong> {{ ucfirst($product->tipo_produto) }} @if($product->tipo_produto==='bebida' && $product->subtipo_bebida) ({{ ucfirst(str_replace('_',' ',$product->subtipo_bebida)) }}) @endif</div>
                <div><strong>Descrição:</strong><br>{{ $product->descricao ?? '-' }}</div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">Imagens</div>
            <div class="card-body">
                @if(empty($images))
                    <div class="text-muted">Nenhuma imagem enviada.</div>
                @else
                <div class="row g-3">
                    @foreach($images as $img)
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="ratio ratio-4x3 border rounded overflow-hidden">
                            <img src="{{ $img }}" alt="Imagem do produto" class="w-100 h-100" style="object-fit:cover;">
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Tags</div>
            <div class="card-body">
                {{ $product->tags ?? '-' }}
            </div>
        </div>
    </div>
</div>
@endsection
