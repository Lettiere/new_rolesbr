@extends('_layout.dashboard.barista.layout_barista')

@section('title', 'Produtos')

@section('content')
<div class="d-flex align-items-center justify-content-between pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h3 m-0">Produtos</h1>
        @if(method_exists($products, 'total'))
            <div class="text-muted small mt-1">{{ $products->total() }} produto(s) encontrado(s)</div>
        @endif
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary d-inline d-md-none" data-bs-toggle="modal" data-bs-target="#filterModalProdutos">
            <i class="fas fa-filter me-1"></i> Filtros
        </button>
        <div class="dropdown d-none d-md-block">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Cadastros de Produtos
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ url('dashboard/barista/produtos/familias') }}">Famílias</a></li>
                <li><a class="dropdown-item" href="{{ url('dashboard/barista/produtos/tipos') }}">Tipos</a></li>
                <li><a class="dropdown-item" href="{{ url('dashboard/barista/produtos/bases') }}">Bases</a></li>
            </ul>
        </div>
        <a href="{{ route('dashboard.barista.products.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Novo Produto</a>
    </div>
</div>
 
<div class="card mb-3 d-none d-md-block">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h5 m-0">Filtros</h2>
            <a href="{{ route('dashboard.barista.products.index') }}" class="btn btn-sm btn-outline-secondary">Limpar filtros</a>
        </div>
        <form class="row g-2" method="GET" id="filtersForm">
            <div class="col-md-3">
                <label class="form-label">Estabelecimento</label>
                <select name="bares_id" class="form-select" onchange="document.getElementById('filtersForm').submit()">
                    <option value="">Todos</option>
                    @foreach($establishments as $e)
                        <option value="{{ $e->bares_id }}" @selected($selectedBares==$e->bares_id)>{{ $e->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Família</label>
                <select name="familia_id" class="form-select" onchange="document.getElementById('filtersForm').submit()">
                    <option value="">Todas</option>
                    @foreach($families as $f)
                        <option value="{{ $f->familia_id }}" @selected($selectedFamily==$f->familia_id)>{{ $f->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo</label>
                <select name="tipo_id" id="tipo_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($types as $t)
                        <option value="{{ $t->tipo_id }}" @selected($selectedType==$t->tipo_id)>{{ $t->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Base</label>
                <select name="base_id" id="base_id" class="form-select" {{ empty($selectedType) ? 'disabled' : '' }}>
                    <option value="">Todas</option>
                    @foreach($bases as $b)
                        <option value="{{ $b->base_id }}" @selected($selectedBase==$b->base_id)>{{ $b->nome }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>
<!-- Modal de Filtros (Mobile) -->
<div class="modal fade d-md-none" id="filterModalProdutos" tabindex="-1" aria-labelledby="filterModalProdutosLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filterModalProdutosLabel"><i class="fas fa-filter me-2"></i>Filtros</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <form method="GET">
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Estabelecimento</label>
                <select name="bares_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($establishments as $e)
                        <option value="{{ $e->bares_id }}" @selected($selectedBares==$e->bares_id)>{{ $e->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Família</label>
                <select name="familia_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($families as $f)
                        <option value="{{ $f->familia_id }}" @selected($selectedFamily==$f->familia_id)>{{ $f->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Tipo</label>
                <select name="tipo_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($types as $t)
                        <option value="{{ $t->tipo_id }}" @selected($selectedType==$t->tipo_id)>{{ $t->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Base</label>
                <select name="base_id" class="form-select" {{ empty($selectedType) ? 'disabled' : '' }}>
                    <option value="">Todas</option>
                    @foreach($bases as $b)
                        <option value="{{ $b->base_id }}" @selected($selectedBase==$b->base_id)>{{ $b->nome }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <a href="{{ route('dashboard.barista.products.index') }}" class="btn btn-outline-secondary">Limpar</a>
          <button type="submit" class="btn btn-primary">Aplicar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle m-0">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th>Estabelecimento</th>
                    <th>Tipo</th>
                    <th>Família</th>
                    <th>Base</th>
                    <th class="text-end">Preço</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                <tr>
                    <td>{{ $p->nome }}</td>
                    <td>{{ $p->establishment->nome ?? '-' }}</td>
                    <td>{{ $p->type->nome ?? '-' }}</td>
                    <td>{{ $p->family->nome ?? '-' }}</td>
                    <td>{{ $p->base->nome ?? '-' }}</td>
                    <td class="text-end">{{ $p->preco !== null ? 'R$ '.number_format($p->preco,2,',','.') : '-' }}</td>
                    <td><span class="badge {{ $p->status==='ativo'?'bg-success':'bg-secondary' }}">{{ $p->status }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('dashboard.barista.products.show', $p->prod_id) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                        <a href="{{ route('dashboard.barista.products.edit', $p->prod_id) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                        <form action="{{ route('dashboard.barista.products.destroy', $p->prod_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Inativar este produto?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Inativar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">Nenhum produto encontrado</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($products, 'links'))
    <div class="card-footer">
        {{ $products->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipoSelect = document.getElementById('tipo_id');
    const baseSel = document.getElementById('base_id');
    const form = document.getElementById('filtersForm');
    tipoSelect.addEventListener('change', () => {
        const id = tipoSelect.value;
        baseSel.innerHTML = '<option value=\"\">Todas</option>';
        baseSel.disabled = true;
        if (!id) { form.submit(); return; }
        fetch(`{{ url('/api/prod/bases') }}/${id}`).then(r=>r.json()).then(rows=>{
            rows.forEach(r=>{
                const o = document.createElement('option');
                o.value = r.base_id; o.textContent = r.nome;
                baseSel.appendChild(o);
            });
            baseSel.disabled = false;
            form.submit();
        });
    });
    baseSel && baseSel.addEventListener('change', () => form.submit());
});
</script>
@endpush
@endsection
