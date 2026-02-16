@extends('_layout.site.site_default')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4 fw-bold" style="color: var(--primary-color);">Criar Conta</h2>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('auth.register.action') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="type_user" class="form-label">Tipo de Conta</label>
                            <select class="form-select" id="type_user" name="type_user" required>
                                <option value="2" selected>Usuário (Rolezeiro)</option>
                                <option value="1">Estabelecimento / Barista</option>
                            </select>
                            <div class="form-text">Escolha como você quer usar a plataforma.</div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar Senha</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary text-white fw-bold" style="background: var(--primary-color); border: none;">Cadastrar</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <p class="mb-0">Já tem uma conta? <a href="{{ route('login') }}" class="text-decoration-none" style="color: var(--secondary-color);">Faça login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
