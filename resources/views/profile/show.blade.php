@extends('_layout.site.site_default')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body d-flex flex-column flex-md-row align-items-center gap-3">
                    @php
                        $rawAvatar = $profile->foto_perfil ?: ($profile->user->foto ?? null);
                        if ($rawAvatar) {
                            if (preg_match('#^https?://#', $rawAvatar)) {
                                $avatar = $rawAvatar;
                            } else {
                                $avatar = asset(ltrim($rawAvatar, '/'));
                            }
                        } else {
                            $avatar = asset('assets/img/user-default.jpg');
                        }
                    @endphp
                    <div class="flex-shrink-0">
                        <img src="{{ $avatar }}"
                             alt="{{ $profile->user->name ?? 'Usuário' }}"
                             class="rounded-circle border border-3 border-warning"
                             style="width: 96px; height: 96px; object-fit: cover;">
                    </div>
                    <div class="flex-grow-1">
                        <h1 class="h4 mb-1">
                            {{ $profile->user->name ?? 'Usuário' }}
                        </h1>
                        <div class="text-muted small mb-2">
                            {{ '@' . ($profile->user->username ?? 'usuario') }}
                        </div>
                        <div class="text-muted small">
                            {{ $profile->user->email ?? '' }}
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 pt-0">
                    <div class="d-flex flex-column flex-sm-row gap-2 justify-content-sm-end">
                        <a href="{{ route('profile.edit', $profile->perfil_id) }}"
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-user-edit me-1"></i>
                            Editar perfil
                        </a>
                        @if((int)($user->type_user ?? 0) === 3)
                            <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-list me-1"></i>
                                Lista de perfis
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0">Informações do perfil</h2>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @php
                            $dataNascimentoBr = $profile->data_nascimento
                                ? \Carbon\Carbon::parse($profile->data_nascimento)->format('d/m/Y')
                                : null;
                        @endphp
                        <div class="col-md-6">
                            <div class="small text-muted">CPF</div>
                            <div>{{ $profile->cpf ?: '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">RG</div>
                            <div>{{ $profile->rg ?: '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Telefone</div>
                            <div>{{ $profile->telefone ?: '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Data de nascimento</div>
                            <div>{{ $dataNascimentoBr ?: '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Gênero</div>
                            <div>{{ $profile->genero_nome ?: '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Estado</div>
                            <div>{{ $profile->estado_nome ?: '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Cidade</div>
                            <div>{{ $profile->cidade_nome ?: '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Bairro</div>
                            <div>{{ $profile->bairro_nome_rel ?: ($profile->bairro_nome ?: '—') }}</div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted">Bairro (texto)</div>
                            <div>{{ $profile->bairro_nome ?: '—' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted">Bio</div>
                            <div>{{ $profile->bio ?: '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
