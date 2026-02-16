@extends('_layout.site.site_default')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4 fw-bold" style="color: var(--primary-color);">Login</h2>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('auth.login.action') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Lembrar de mim</label>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary text-white fw-bold" style="background: var(--primary-color); border: none;">Entrar</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <p class="mb-0">Não tem uma conta? <a href="{{ route('register') }}" class="text-decoration-none" style="color: var(--secondary-color);">Cadastre-se</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
