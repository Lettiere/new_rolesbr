@extends('_layout.site.site_default')
@section('content')
@section('meta_title', 'Eventos | RolesBr')
@section('meta_description', 'Veja a agenda de eventos, festas e rolês cadastrados no RolesBr.')
@section('meta_og_type', 'website')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 fw-bold mb-0">Eventos</h1>
        <form action="{{ route('site.events.index') }}" method="GET" class="d-flex gap-2">
            <input type="text" class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Buscar eventos">
            <button class="btn btn-sm btn-outline-secondary">Buscar</button>
        </form>
    </div>

    <div class="mb-3">
        @include('site.partials.stories_strip')
    </div>

    <div id="eventsListWrapper">
        @include('site.partials.events_list', ['events' => $events])
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('form[action="{{ route('site.events.index') }}"]');
    if (!form) return;
    var input = form.querySelector('input[name="q"]');
    var wrapper = document.getElementById('eventsListWrapper');
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
