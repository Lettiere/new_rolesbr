@extends('_layout.site.site_default')

@push('styles')
<style>
    @media (max-width: 768px) {
        .ticket-checkout-page {
            margin: 0;
            padding: 0;
        }
        .ticket-checkout-page > .container {
            max-width: 100%;
            padding-left: 0;
            padding-right: 0;
        }
        .ticket-checkout-page .card {
            border-radius: 0;
        }
    }
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.6);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1050;
    }
    .modal-overlay.show {
        display: flex;
    }
    .modal-panel {
        background: #fff;
        width: 92%;
        max-width: 520px;
        border-radius: 12px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        overflow: hidden;
    }
    .modal-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .modal-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }
    .modal-body {
        padding: 1rem 1.25rem;
    }
    .modal-footer {
        padding: 0.75rem 1.25rem;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 0.5rem;
        justify-content: flex-end;
    }
</style>
@endpush

@section('content')
<div class="ticket-checkout-page">
<div class="container py-4">
    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h1 class="h4 mb-3">Finalizar compra</h1>
                    <div class="mb-3 p-3 rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $ticket->nome }}</div>
                                <div class="small text-muted">{{ $ticket->tipo }}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">R$ {{ number_format($ticket->preco, 2, ',', '.') }}</div>
                            </div>
                        </div>
                        @if($event)
                            <div class="small text-muted mt-2">
                                Evento: {{ $event->nome }}
                                @if($event->data_inicio)
                                    • {{ \Carbon\Carbon::parse($event->data_inicio)->format('d/m H:i') }}
                                @endif
                            </div>
                        @endif
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('cpf_requires_register'))
                        <div class="alert alert-warning d-flex justify-content-between align-items-center">
                            <div>
                                CPF não cadastrado. É necessário cadastrar para prosseguir.
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" id="btn-open-register-modal">
                                Cadastrar
                            </button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('site.ticket.checkout.submit', $ticket->lote_id) }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Documento (CPF)</label>
                                <input type="text" class="form-control" name="cpf" value="{{ old('cpf') }}" placeholder="000.000.000-00" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">WhatsApp do titular</label>
                                <input type="tel" class="form-control" name="whatsapp_titular" value="{{ old('whatsapp_titular') }}" placeholder="(xx) xxxxx-xxxx" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Quantidade</label>
                                <input type="number" min="1" max="5" value="{{ old('quantidade', 1) }}" class="form-control" name="quantidade">
                            </div>
                            <div class="col-12" id="convidadosWrapper" style="display:none;">
                                <div class="card mt-2">
                                    <div class="card-header bg-white">
                                        <strong>Dados dos convidados</strong>
                                        <div class="small text-muted">Preencha 1 bloco para cada ingresso adicional</div>
                                    </div>
                                    <div class="card-body" id="convidadosContainer">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="aceite" name="aceite" required>
                                    <label class="form-check-label" for="aceite">
                                        Aceito os termos e condições da compra
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('site.ticket.show', $ticket->lote_id) }}" class="btn btn-outline-secondary">Voltar</a>
                            <button class="btn btn-primary" type="submit">Continuar pagamento</button>
                        </div>
                    </form>
                    <p class="small text-muted mt-3 mb-0">Integração de pagamento será conectada posteriormente.</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-5">
            @if($event)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0">Resumo</h2>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        @php
                            $cover = $event->imagem_capa ? asset(ltrim($event->imagem_capa, '/')) : 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=300&h=180&fit=crop';
                        @endphp
                        <img src="{{ $cover }}" alt="{{ $event->nome }}" class="rounded me-3" style="width: 120px; height: 80px; object-fit: cover;">
                        <div>
                            <div class="fw-semibold">{{ $event->nome }}</div>
                            @if($event->data_inicio)
                                <div class="small text-muted">{{ \Carbon\Carbon::parse($event->data_inicio)->format('d/m H:i') }}</div>
                            @endif
                            @if($event->establishment)
                                <div class="small text-muted">{{ $event->establishment->nome }}</div>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Ingresso</span>
                        <span>R$ {{ number_format($ticket->preco, 2, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>Taxas</span>
                        <span>—</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-semibold">
                        <span>Total</span>
                        <span>R$ {{ number_format($ticket->preco, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
</div>

<div class="modal-overlay" id="registerModal" aria-modal="true" role="dialog">
    <div class="modal-panel">
        <div class="modal-header">
            <h3 class="modal-title">Criar cadastro</h3>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-close-register">Fechar</button>
        </div>
        <div class="modal-body">
            <form id="registerInlineForm">
                <div class="mb-3">
                    <label class="form-label">CPF</label>
                    <input type="text" class="form-control" name="cpf" id="registerCpf" value="{{ old('cpf') }}" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nome completo</label>
                    <input type="text" class="form-control" name="nome" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">E-mail</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Telefone (WhatsApp)</label>
                    <input type="tel" class="form-control" name="telefone" placeholder="(xx) xxxxx-xxxx">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" id="btn-cancel-register">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit-register">Cadastrar</button>
                </div>
            </form>
            <div class="alert alert-danger mt-3 d-none" id="registerErrors"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    const qtdInput = document.querySelector('input[name="quantidade"]');
    const convidadosWrapper = document.getElementById('convidadosWrapper');
    const convidadosContainer = document.getElementById('convidadosContainer');

    function createConvidadoBlock(index) {
        const div = document.createElement('div');
        div.className = 'row g-3 border rounded p-3 mb-3';
        div.innerHTML = `
            <div class="col-12">
                <div class="fw-semibold mb-2">Convidado ${index+1}</div>
            </div>
            <div class="col-12">
                <label class="form-label">Nome completo</label>
                <input type="text" class="form-control" name="convidados[${index}][nome]" required>
            </div>
            <div class="col-12">
                <label class="form-label">CPF</label>
                <input type="text" class="form-control" name="convidados[${index}][cpf]" placeholder="000.000.000-00">
            </div>
            <div class="col-12">
                <label class="form-label">Data de nascimento</label>
                <input type="date" class="form-control" name="convidados[${index}][data_nascimento]">
            </div>
            <div class="col-12">
                <label class="form-label">E-mail</label>
                <input type="email" class="form-control" name="convidados[${index}][email]">
            </div>
        `;
        return div;
    }

    function renderConvidados() {
        if (!qtdInput || !convidadosWrapper || !convidadosContainer) return;
        const qtd = parseInt(qtdInput.value || '1', 10);
        const extras = Math.max(0, Math.min(5, qtd) - 1);
        convidadosContainer.innerHTML = '';
        if (extras > 0) {
            convidadosWrapper.style.display = '';
            for (let i = 0; i < extras; i++) {
                convidadosContainer.appendChild(createConvidadoBlock(i));
            }
        } else {
            convidadosWrapper.style.display = 'none';
        }
    }

    if (qtdInput) {
        qtdInput.addEventListener('input', renderConvidados);
        renderConvidados();
    }

    const openBtn = document.getElementById('btn-open-register-modal');
    const closeBtn = document.getElementById('btn-close-register');
    const cancelBtn = document.getElementById('btn-cancel-register');
    const modal = document.getElementById('registerModal');
    const form = document.getElementById('registerInlineForm');
    const errorBox = document.getElementById('registerErrors');
    const cpfInput = document.querySelector('input[name="cpf"]');
    const cpfModal = document.getElementById('registerCpf');

    function openModal() {
        if (cpfInput && cpfModal) cpfModal.value = cpfInput.value;
        modal.classList.add('show');
    }
    function closeModal() {
        modal.classList.remove('show');
        errorBox.classList.add('d-none');
        errorBox.innerHTML = '';
        form.reset();
        if (cpfInput && cpfModal) cpfModal.value = cpfInput.value;
    }

    if (openBtn) openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (modal) {
        modal.addEventListener('click', function(e){
            if (e.target === modal) closeModal();
        });
    }

    if (form) {
        form.addEventListener('submit', async function(e){
            e.preventDefault();
            errorBox.classList.add('d-none');
            errorBox.innerHTML = '';
            const formData = new FormData(form);
            try {
                const res = await fetch("{{ route('site.ticket.register.inline') }}", {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    },
                    body: formData
                });
                if (!res.ok) {
                    const data = await res.json();
                    const errs = data.errors || {'erro': ['Erro ao cadastrar']};
                    const html = Object.values(errs).map(arr => arr.join('<br>')).join('<br>');
                    errorBox.innerHTML = html;
                    errorBox.classList.remove('d-none');
                    return;
                }
                const data = await res.json();
                if (data && data.ok) {
                    location.reload();
                } else {
                    errorBox.textContent = 'Não foi possível concluir o cadastro.';
                    errorBox.classList.remove('d-none');
                }
            } catch (err) {
                errorBox.textContent = 'Falha de rede. Tente novamente.';
                errorBox.classList.remove('d-none');
            }
        });
    }
})();
</script>
@endpush
