@extends('_layout.site.site_default')
@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3 px-2">
        <h1 class="h5 fw-bold mb-0">Stories</h1>
        <span class="badge bg-dark text-warning border border-warning border-opacity-50">24h</span>
    </div>
    <div class="px-2">
        @include('site.partials.stories_strip')
    </div>
</div>
@endsection

