@extends('layouts.app')

@section('ocultar-navegacion-app', true)

@section('content')
<main class="container py-5" style="max-width:1180px;margin-inline:auto">
    <section class="p-4 p-md-5 mb-4 text-white shadow" style="border-radius:26px;background:linear-gradient(120deg,#1848a1,#31bebe)">
        <div class="text-uppercase fw-bold small opacity-75">Medichile · Página de inicio</div>
        <h1 class="display-6 fw-bold text-white mt-2 mb-2">Hola, {{ auth()->user()->name }}</h1>
        <p class="fs-5 mb-0">{{ $perfil }} · {{ $subtitulo }}</p>
    </section>

    <h2 class="h4 fw-bold mb-3">¿Qué desea hacer?</h2>
    <div class="row g-4">
        @foreach($accesos as $acceso)
            <div class="col-md-6 col-lg-4">
                <a href="{{ $acceso['url'] }}" class="text-decoration-none">
                    <article class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="fs-2 mb-3">{{ ['⌂', '✓', '→'][$loop->index % 3] }}</div>
                            <h3 class="h5 fw-bold text-dark">{{ $acceso['titulo'] }}</h3>
                            <p class="text-muted mb-0">{{ $acceso['detalle'] }}</p>
                        </div>
                    </article>
                </a>
            </div>
        @endforeach
    </div>
</main>
@endsection
