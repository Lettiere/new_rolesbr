@extends('_layout.site.site_default')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 fw-bold mb-0">Ingressos</h1>
        <form action="{{ route('site.tickets.index') }}" method="GET" class="d-flex gap-2">
            <input type="text" class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Buscar ingressos">
            <button class="btn btn-sm btn-outline-secondary">Buscar</button>
        </form>
    </div>
    <div id="ticketsListWrapper">
        @include('site.partials.tickets_list', ['tickets' => $tickets])
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('form[action="{{ route('site.tickets.index') }}"]');
    if (!form) return;
    var input = form.querySelector('input[name="q"]');
    var wrapper = document.getElementById('ticketsListWrapper');
    if (!input || !wrapper) return;
    var timer = null;
    input.addEventListener('input', function () {
        if (timer) clearTimeout(timer);
        timer = setTimeout(function () {
            var url = new URL(form.action, window.location.origin);
            if (input.value) {
                url.searchParams.set('q', input.value);
            } else {
                url.searchParams.delete('q');
            }
            url.searchParams.set('ajax', '1');
            fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(function (r) {
                return r.text();
            }).then(function (html) {
                wrapper.innerHTML = html;
            }).catch(function () {});
        }, 300);
    });
});
</script>
@endpush
@endsection
