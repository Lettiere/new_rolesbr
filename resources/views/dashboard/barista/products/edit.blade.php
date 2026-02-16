@extends('_layout.dashboard.barista.layout_barista')

@section('title', 'Editar Produto')

@section('content')
<div class="pt-3 pb-2 mb-3 border-bottom d-flex align-items-center justify-content-between">
    <h1 class="h3 m-0">Editar Produto</h1>
    <a href="{{ route('dashboard.barista.products.index') }}" class="btn btn-outline-secondary">Voltar</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('dashboard.barista.products.update', $product->prod_id) }}" method="POST" enctype="multipart/form-data" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-6">
                <label class="form-label">Estabelecimento</label>
                <select name="bares_id" class="form-select" required>
                    <option value="">Selecione...</option>
                    @foreach($establishments as $e)
                        <option value="{{ $e->bares_id }}" @selected(old('bares_id',$product->bares_id)==$e->bares_id)>{{ $e->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" value="{{ old('nome', $product->nome) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tipo de Produto</label>
                <select name="tipo_produto" id="tipo_produto" class="form-select" required>
                    @foreach(['bebida','alimento','lanche','salgado','sobremesa','outro'] as $tp)
                        <option value="{{ $tp }}" @selected(old('tipo_produto',$product->tipo_produto)==$tp)>{{ ucfirst($tp) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6" id="subtipo_wrap" style="display:none;">
                <label class="form-label">Subtipo (Bebida)</label>
                <select name="subtipo_bebida" class="form-select">
                    <option value="">Selecione...</option>
                    @foreach(['alcoolica','nao_alcoolica','cafe','cha','outro'] as $st)
                        <option value="{{ $st }}" @selected(old('subtipo_bebida',$product->subtipo_bebida)==$st)>{{ ucfirst(str_replace('_',' ',$st)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tipo Base</label>
                <select name="tipo_id" id="tipo_id" class="form-select">
                    <option value="">Selecione...</option>
                    @foreach($types as $t)
                        <option value="{{ $t->tipo_id }}" @selected(old('tipo_id',$product->tipo_id)==$t->tipo_id)>{{ $t->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Base do Produto</label>
                <select name="base_id" id="base_id" class="form-select">
                    <option value="">Selecione...</option>
                    @foreach($bases as $b)
                        <option value="{{ $b->base_id }}" @selected(old('base_id',$product->base_id)==$b->base_id)>{{ $b->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Família</label>
                <select name="familia_id" class="form-select">
                    <option value="">Selecione...</option>
                    @foreach($families as $f)
                        <option value="{{ $f->familia_id }}" @selected(old('familia_id',$product->familia_id)==$f->familia_id)>{{ $f->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Preço</label>
                <input type="number" step="0.01" name="preco" value="{{ old('preco',$product->preco) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Quantidade em Estoque</label>
                <input type="number" name="quantidade_estoque" value="{{ old('quantidade_estoque',$product->quantidade_estoque) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Unidade</label>
                <input type="text" name="unidade" value="{{ old('unidade',$product->unidade) }}" class="form-control" placeholder="ex.: dose, garrafa, porção">
            </div>
            <div class="col-12">
                <label class="form-label">Descrição</label>
                <textarea name="descricao" rows="3" class="form-control">{{ old('descricao',$product->descricao) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Tags</label>
                <input type="text" name="tags" value="{{ old('tags',$product->tags) }}" class="form-control" placeholder="ex.: vodka, drink, doce">
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(['ativo','inativo'] as $st)
                        <option value="{{ $st }}" @selected(old('status',$product->status)==$st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary" type="submit">Salvar</button>
            </div>
        </form>
        <hr class="my-4">
        <h5 class="mb-3">Imagens do Produto</h5>
        @if(!empty($images))
            <div class="row g-2 mb-3">
                @foreach($images as $img)
                    <div class="col-6 col-md-3">
                        <div class="ratio ratio-4x3 border rounded overflow-hidden">
                            <img src="{{ $img }}" class="w-100 h-100" style="object-fit:cover;">
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-muted mb-3">Nenhuma imagem enviada ainda.</div>
        @endif
        <form action="{{ route('dashboard.barista.products.update', $product->prod_id) }}" method="POST" enctype="multipart/form-data" class="row g-2">
            @csrf @method('PUT')
            <div class="col-12">
                <label class="form-label">Adicionar novas imagens (até 6)</label>
                <input type="file" name="fotos[]" class="form-control" accept="image/*" multiple>
                <div class="form-text">As imagens são redimensionadas para 800x600px.</div>
            </div>
            <div class="col-12 form-check">
                <input type="checkbox" class="form-check-input" id="limparImagens" name="limpar_imagens" value="1">
                <label for="limparImagens" class="form-check-label">Substituir imagens existentes</label>
            </div>
            <div class="col-12">
                <button class="btn btn-outline-primary">Enviar Imagens</button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipoSelect = document.getElementById('tipo_produto');
    const subtipoWrap = document.getElementById('subtipo_wrap');
    const tipoBase = document.getElementById('tipo_id');
    const baseSel = document.getElementById('base_id');
    function toggleSubtipo() {
        subtipoWrap.style.display = (tipoSelect.value === 'bebida') ? '' : 'none';
    }
    tipoSelect.addEventListener('change', toggleSubtipo);
    toggleSubtipo();
    tipoBase.addEventListener('change', () => {
        const id = tipoBase.value;
        baseSel.innerHTML = '<option value=\"\">Selecione...</option>';
        if (!id) return;
        fetch(`{{ url('/api/prod/bases') }}/${id}`).then(r=>r.json()).then(rows=>{
            rows.forEach(r=>{
                const o = document.createElement('option');
                o.value = r.base_id; o.textContent = r.nome;
                baseSel.appendChild(o);
            });
        });
    });
});
</script>
@endpush
@endsection
