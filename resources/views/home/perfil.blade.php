@extends('layouts.app')

@section('ocultar-navegacion-app', true)

@php($mostrarTabsPerfil = config('demo.enabled') && request()->routeIs('profesional.home'))
@php($mostrarTabsAsistente = config('demo.enabled') && request()->routeIs('asistente.home'))

@section('content')
<style>
    .view-tabs{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 24px;padding:8px;border:1px solid #d8e3f0;border-radius:18px;background:rgba(255,255,255,.9);box-shadow:0 10px 28px rgba(24,72,161,.06)}
    .view-tabs button{flex:1 1 220px;min-height:52px;padding:10px 14px;border:0;border-radius:12px;background:transparent;color:#607184;font-family:'Manrope',sans-serif;font-size:15px;font-weight:800;line-height:1.3;cursor:pointer;transition:background .2s,color .2s}
    .view-tabs button:hover{background:#eef4ff;color:#1848a1}
    .view-tabs button[aria-selected="true"]{background:#e6efff;color:#1848a1;box-shadow:none}
    .view-tabs button:focus-visible{outline:3px solid #31bebe;outline-offset:2px}
    .view-panel[hidden]{display:none!important}
    @media(max-width:680px){.view-tabs button{min-height:48px;padding:9px 10px;font-size:13.5px}}
</style>
<main class="container py-5" @unless($mostrarTabsAsistente) style="max-width:1180px;margin-inline:auto" @endunless>
    @if($mostrarTabsAsistente)
        <div class="view-tabs" role="tablist" aria-label="Secciones de inicio asistente">
            <button type="button" role="tab" id="view-tab-recorrido" aria-controls="view-panel-recorrido" aria-selected="true" data-view-tab="recorrido">Recorrido</button>
            <button type="button" role="tab" id="view-tab-inicio" aria-controls="view-panel-inicio" aria-selected="false" tabindex="-1" data-view-tab="inicio">Asistente de recepción</button>
        </div>

        <div class="view-panel" id="view-panel-recorrido" role="tabpanel" aria-labelledby="view-tab-recorrido" data-view-panel="recorrido">
            @include('partials.demo_assistant_guide')
        </div>

        <div class="view-panel" id="view-panel-inicio" role="tabpanel" aria-labelledby="view-tab-inicio" data-view-panel="inicio" hidden>
    @endif

    <section class="p-4 p-md-5 mb-4 text-white shadow" style="border-radius:26px;background:linear-gradient(120deg,#1848a1,#31bebe)">
        <div class="text-uppercase fw-bold small opacity-75">Medichile · Página de inicio</div>
        <h1 class="display-6 fw-bold text-white mt-2 mb-2">Hola, {{ auth()->user()->name }}</h1>
        <p class="fs-5 mb-0">{{ $perfil }} · {{ $subtitulo }}</p>
    </section>

    @if($mostrarTabsPerfil)
        <div class="view-tabs" role="tablist" aria-label="Secciones de inicio profesional">
            <button type="button" role="tab" id="view-tab-recorrido" aria-controls="view-panel-recorrido" aria-selected="false" tabindex="-1" data-view-tab="recorrido">Recorrido profesional</button>
            <button type="button" role="tab" id="view-tab-inicio" aria-controls="view-panel-inicio" aria-selected="true" data-view-tab="inicio">Inicio</button>
        </div>

        <div class="view-panel" id="view-panel-recorrido" role="tabpanel" aria-labelledby="view-tab-recorrido" data-view-panel="recorrido" hidden>
            @include('partials.demo_flow_guide', ['demoProfessional' => true, 'demoInteractive' => true])
        </div>

        <div class="view-panel" id="view-panel-inicio" role="tabpanel" aria-labelledby="view-tab-inicio" data-view-panel="inicio">
    @endif

    <h2 class="h4 fw-bold mb-3">¿Qué desea hacer?</h2>
    <div class="row g-4">
        @foreach($accesos as $acceso)
            <div class="col-md-6 col-lg-4">
                <a href="{{ $acceso['url'] }}" class="text-decoration-none">
                    <article class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            @if(! empty($acceso['icono']))
                                <img src="{{ asset($acceso['icono']) }}" alt="" class="mb-3" style="width:50px;height:50px;object-fit:contain;display:block">
                            @else
                                <div class="fs-2 mb-3">{{ ['⌂', '✓', '→'][$loop->index % 3] }}</div>
                            @endif
                            <h3 class="h5 fw-bold text-dark">{{ $acceso['titulo'] }}</h3>
                            <p class="text-muted mb-0">{{ $acceso['detalle'] }}</p>
                        </div>
                    </article>
                </a>
            </div>
        @endforeach
    </div>

    @if($mostrarTabsPerfil || $mostrarTabsAsistente)
        </div>
    @endif
</main>
@if($mostrarTabsPerfil || $mostrarTabsAsistente)
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var pestanas = Array.prototype.slice.call(document.querySelectorAll('[data-view-tab]'));
        function seleccionar(pestana) {
            pestanas.forEach(function (boton) {
                var activa = boton === pestana;
                boton.setAttribute('aria-selected', String(activa));
                boton.tabIndex = activa ? 0 : -1;
            });
            document.querySelectorAll('[data-view-panel]').forEach(function (panel) {
                panel.hidden = panel.dataset.viewPanel !== pestana.dataset.viewTab;
            });
            window.dispatchEvent(new Event('scroll'));
        }
        pestanas.forEach(function (boton, indice) {
            boton.addEventListener('click', function () { seleccionar(boton); });
            boton.addEventListener('keydown', function (evento) {
                if (evento.key !== 'ArrowRight' && evento.key !== 'ArrowLeft') return;
                var siguiente = pestanas[(indice + (evento.key === 'ArrowRight' ? 1 : pestanas.length - 1)) % pestanas.length];
                siguiente.focus();
                seleccionar(siguiente);
            });
        });
    });
    </script>
@endif
@endsection
