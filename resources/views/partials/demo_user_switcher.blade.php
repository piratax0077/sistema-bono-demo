@if(config('demo.enabled') && config('demo.user_switch_enabled'))
@include('partials.demo_wide_layout')
<section class="demo-switcher" aria-label="Cambiar perfil de demostración">
    <a class="demo-switcher__home" href="{{ route('home') }}"><span class="demo-switcher__mark">M</span><span>Medichile</span></a>
    <details class="demo-switcher__current demo-switcher__profile">
        <summary>
            <span><small>Perfil activo</small>{{ auth()->user()?->name ?? 'Sin sesión' }}</span>
            <span class="demo-switcher__profile-caret" aria-hidden="true">▾</span>
        </summary>
        <div class="demo-switcher__profile-menu">
            <div class="demo-switcher__profile-info">
                <strong>{{ auth()->user()?->name ?? 'Sin sesión' }}</strong>
                <small>{{ auth()->user()?->email }}</small>
            </div>
            @auth
                @php
                    $inicioPorRol = [
                        'cliente' => route('home'),
                        'asistente' => route('asistente.escritorio'),
                        'profesional' => route('profesional.home'),
                        'vendedor' => route('vendedor.home'),
                        'admin' => route('admin.home'),
                        'auditor' => route('contraloria.home'),
                        'contralor' => route('contraloria.home'),
                    ];
                    $urlInicioPerfil = $inicioPorRol[auth()->user()?->rol] ?? route('home');
                @endphp
                <a class="demo-switcher__profile-link" href="{{ $urlInicioPerfil }}"><span aria-hidden="true">⌂</span> Página de inicio</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="demo-switcher__logout" type="submit"><span aria-hidden="true">↪</span> Cerrar sesión y volver al login</button>
                </form>
            @endauth
        </div>
    </details>
    <div @class(['demo-switcher__split', 'is-active' => request()->routeIs('paciente.*', 'totem.local')])>
        <form method="POST" action="{{ route('demo.switch-user', 'paciente') }}">@csrf
            <input type="hidden" name="destino" value="home">
            <button type="submit" class="demo-switcher__split-main">Paciente</button>
        </form>
        <details class="demo-switcher__dropdown demo-switcher__split-menu">
            <summary aria-label="Mostrar opciones de paciente" title="Opciones de paciente"><span aria-hidden="true">▾</span></summary>
            <div class="demo-switcher__dropdown-menu">
            <form method="POST" action="{{ route('demo.switch-user', 'paciente') }}">@csrf
                <input type="hidden" name="destino" value="paciente.totem">
                <button type="submit" @disabled(request()->routeIs('paciente.totem', 'totem.local'))>Tótem de autoatención</button>
            </form>
            <form method="POST" action="{{ route('demo.switch-user', 'paciente') }}">@csrf
                <input type="hidden" name="destino" value="paciente.escritorio">
                <button type="submit" @disabled(request()->routeIs('paciente.escritorio'))>Escritorio del paciente</button>
            </form>
            </div>
        </details>
    </div>
    @php($perfilAsistente = config('demo.users.asistente'))
    <form method="POST" action="{{ route('demo.switch-user', 'asistente') }}">@csrf
        <button type="submit" @disabled(auth()->user()?->email === $perfilAsistente['email'])>{{ $perfilAsistente['label'] }}</button>
    </form>
    @php($perfilProfesional = config('demo.users.profesional'))
    <details class="demo-switcher__dropdown">
        <summary @class(['is-active' => auth()->user()?->email === $perfilProfesional['email']])>Profesional <span aria-hidden="true">▾</span></summary>
        <div class="demo-switcher__dropdown-menu">
            <form method="POST" action="{{ route('demo.switch-user', 'profesional') }}">@csrf
                <input type="hidden" name="destino" value="profesional.escritorio">
                <button type="submit">Escritorio profesional</button>
            </form>
            <form method="POST" action="{{ route('demo.switch-user', 'profesional') }}">@csrf
                <input type="hidden" name="destino" value="profesional.cobros">
                <button type="submit">Gestión de cobros</button>
            </form>
            <form method="POST" action="{{ route('demo.switch-user', 'profesional') }}">@csrf
                <input type="hidden" name="destino" value="profesional.historial_pagos">
                <button type="submit">Historial de pagos</button>
            </form>
        </div>
    </details>
    @php($perfilContralor = config('demo.users.contralor'))
    <details class="demo-switcher__dropdown">
        <summary @class(['is-active' => auth()->user()?->email === $perfilContralor['email']])>Contraloría <span aria-hidden="true">▾</span></summary>
        <div class="demo-switcher__dropdown-menu">
            <form method="POST" action="{{ route('demo.switch-user', 'contralor') }}">@csrf
                <input type="hidden" name="destino" value="auditoria.index">
                <button type="submit" @disabled(request()->routeIs('auditoria.index'))>Vista de Contraloría</button>
            </form>
            <form method="POST" action="{{ route('demo.switch-user', 'contralor') }}">@csrf
                <input type="hidden" name="destino" value="auditoria.trazabilidad">
                <button type="submit" @disabled(request()->routeIs('auditoria.trazabilidad'))>Trazabilidad de bono</button>
            </form>
            <form method="POST" action="{{ route('demo.switch-user', 'contralor') }}">@csrf
                <input type="hidden" name="destino" value="auditoria.notificaciones">
                <button type="submit" @disabled(request()->routeIs('auditoria.notificaciones'))>Notificaciones</button>
            </form>
            <form method="POST" action="{{ route('demo.switch-user', 'contralor') }}">@csrf
                <input type="hidden" name="destino" value="auditoria.logins">
                <button type="submit" @disabled(request()->routeIs('auditoria.logins'))>Auditoría de accesos</button>
            </form>
        </div>
    </details>
    @foreach(collect(config('demo.users'))->except(['paciente', 'asistente', 'profesional', 'contralor', 'administrador']) as $key => $perfil)
        <form method="POST" action="{{ route('demo.switch-user', $key) }}">@csrf
            <button type="submit" @disabled(auth()->user()?->email === $perfil['email'])>{{ $perfil['label'] }}</button>
        </form>
    @endforeach
</section>
<style>
:root{--sdi-ink:#1f2d3d;--sdi-muted:#7b8798;--sdi-green:#31bebe;--sdi-green-dark:#238f99;--sdi-blue:#1848a1;--sdi-bg:#edf4ff;--sdi-line:#c9d8ef;--sdi-card:#fff;--sdi-shadow:0 18px 48px rgba(24,72,161,.09)}
html{background:var(--sdi-bg)}body{background:radial-gradient(circle at 5% 0%,rgba(49,190,190,.11),transparent 28rem),linear-gradient(180deg,#f8fbff 0,var(--sdi-bg) 100%)!important;color:var(--sdi-ink)!important;font-family:Inter,"Segoe UI",system-ui,-apple-system,sans-serif!important;min-height:100vh}h1,h2,h3,h4,h5{color:#1f2d3d;letter-spacing:-.02em}.card{border:1px solid rgba(201,216,239,.82)!important;border-radius:20px!important;box-shadow:var(--sdi-shadow)!important;background:rgba(255,255,255,.97)!important}.card-header{border-color:var(--sdi-line)!important;border-radius:20px 20px 0 0!important}.btn{border-radius:11px!important;font-weight:750!important;padding:.55rem .9rem;transition:transform .16s ease,box-shadow .16s ease,filter .16s ease}.btn:hover{transform:translateY(-1px);box-shadow:0 8px 20px rgba(24,72,161,.15)}.btn-success,.bg-success{background-color:var(--sdi-green)!important;border-color:var(--sdi-green)!important}.btn-primary{background-color:var(--sdi-blue)!important;border-color:var(--sdi-blue)!important}.btn-danger{background-color:#c94350!important;border-color:#c94350!important}.text-success{color:#238f99!important}.form-control,.form-select{border-radius:12px!important;border-color:#c9d8ef!important;padding:.68rem .82rem!important;box-shadow:none!important}.form-control:focus,.form-select:focus{border-color:#31bebe!important;box-shadow:0 0 0 .22rem rgba(49,190,190,.16)!important}.alert{border:0!important;border-radius:15px!important;box-shadow:0 8px 24px rgba(24,72,161,.06)}.table{--bs-table-bg:transparent}.table thead th{padding:.9rem .8rem;border-bottom:1px solid #c9d8ef;color:#53657b;font-size:.75rem;letter-spacing:.055em;text-transform:uppercase;white-space:nowrap}.table tbody td{padding:.88rem .8rem;border-color:#e1e9f3}.table-hover tbody tr:hover{--bs-table-hover-bg:#f0f8fb}.badge{font-weight:750;letter-spacing:.015em}.text-muted{color:var(--sdi-muted)!important}
.demo-switcher{position:sticky;top:0;z-index:9999;display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;padding:.7rem 1rem;background:linear-gradient(110deg,#133570,#1848a1 58%,#31bebe);color:#fff;box-shadow:0 8px 30px rgba(24,72,161,.24);font:700 12.5px/1.2 Inter,"Segoe UI",system-ui}.demo-switcher form{margin:0}.demo-switcher button{border:1px solid rgba(255,255,255,.3);border-radius:999px;padding:.5rem .72rem;background:rgba(255,255,255,.11);color:#fff;text-decoration:none;cursor:pointer;backdrop-filter:blur(8px);transition:.18s ease}.demo-switcher button:hover{background:rgba(255,255,255,.22);transform:translateY(-1px)}.demo-switcher button:disabled{background:#fff;color:#1848a1;border-color:#fff;opacity:1;box-shadow:0 5px 14px rgba(0,0,0,.16)}.demo-switcher__home{display:flex;align-items:center;gap:.5rem;margin-right:.25rem;color:#fff;text-decoration:none;font-size:13px}.demo-switcher__mark{display:grid;place-items:center;width:27px;height:27px;border-radius:9px;background:#fff;color:#1848a1;font-weight:950;box-shadow:0 5px 15px rgba(0,0,0,.16)}.demo-switcher__current{position:relative;margin-left:auto;font-weight:800;white-space:nowrap}.demo-switcher__current>summary{display:flex;align-items:center;gap:.55rem;list-style:none;padding:.28rem .42rem;border-radius:11px;cursor:pointer;transition:.18s ease}.demo-switcher__current>summary::-webkit-details-marker{display:none}.demo-switcher__current>summary:hover{background:rgba(255,255,255,.12)}.demo-switcher__current>summary>span:first-child{display:flex;flex-direction:column;align-items:flex-end}.demo-switcher__current small{color:#c9f4f4;text-transform:uppercase;font-size:9px;letter-spacing:.12em}.demo-switcher__home{order:-2}.demo-switcher__current{order:-1}@media(max-width:1050px){.demo-switcher__current{margin-left:0}}@media(max-width:760px){.demo-switcher{padding:.62rem}.demo-switcher__current{width:100%}.demo-switcher__current>summary{justify-content:flex-start}.demo-switcher__current>summary>span:first-child{align-items:flex-start}.demo-switcher button{font-size:11.5px;padding:.44rem .6rem}}
.demo-switcher__profile-caret{font-size:11px;transition:transform .18s}.demo-switcher__profile[open] .demo-switcher__profile-caret{transform:rotate(180deg)}.demo-switcher__profile-menu{position:absolute;top:calc(100% + .55rem);right:0;z-index:10001;min-width:230px;padding:.55rem;border:1px solid #d7e2f2;border-radius:15px;background:#fff;color:#294d78;box-shadow:0 18px 44px rgba(16,39,74,.25)}.demo-switcher__profile-info{display:flex;flex-direction:column;gap:.25rem;padding:.65rem .7rem .8rem;border-bottom:1px solid #e3eaf4}.demo-switcher__profile-info strong{color:#1f3553}.demo-switcher__profile-info small{max-width:205px;overflow:hidden;color:#7b8798;text-overflow:ellipsis;text-transform:none;letter-spacing:0}.demo-switcher__profile-link{display:flex;align-items:center;gap:.5rem;margin-top:.4rem;padding:.68rem .72rem;border-radius:10px;color:#294d78;text-decoration:none}.demo-switcher__profile-link:hover{background:#edf4ff;color:#1848a1}.demo-switcher__profile-menu form{padding-top:.2rem}.demo-switcher__profile-menu .demo-switcher__logout{display:flex;width:100%;align-items:center;gap:.5rem;border:0;border-radius:10px;padding:.68rem .72rem;background:transparent;color:#b73542;text-align:left;backdrop-filter:none}.demo-switcher__profile-menu .demo-switcher__logout:hover{background:#fff0f1;color:#9d2834;box-shadow:none;transform:none}@media(max-width:760px){.demo-switcher__profile-menu{left:0;right:auto}}
.demo-switcher__dropdown{position:relative}.demo-switcher__dropdown summary{list-style:none;border:1px solid rgba(255,255,255,.3);border-radius:999px;padding:.5rem .72rem;background:rgba(255,255,255,.11);color:#fff;cursor:pointer;backdrop-filter:blur(8px);transition:.18s ease}.demo-switcher__dropdown summary::-webkit-details-marker{display:none}.demo-switcher__dropdown summary:hover{background:rgba(255,255,255,.22);transform:translateY(-1px)}.demo-switcher__dropdown summary.is-active{background:#fff;color:#1848a1;border-color:#fff;box-shadow:0 5px 14px rgba(0,0,0,.16)}.demo-switcher__dropdown-menu{position:absolute;top:calc(100% + .55rem);right:0;z-index:10000;display:grid;gap:.3rem;min-width:190px;padding:.5rem;border:1px solid #d7e2f2;border-radius:14px;background:#fff;box-shadow:0 16px 40px rgba(16,39,74,.22)}.demo-switcher__dropdown-menu button{width:100%;border:0;border-radius:9px;padding:.65rem .75rem;background:transparent;color:#294d78;text-align:left;white-space:nowrap;backdrop-filter:none}.demo-switcher__dropdown-menu button:hover{background:#edf4ff;color:#1848a1;transform:none;box-shadow:none}
.demo-switcher__split{display:flex;align-items:stretch}.demo-switcher__split>form>.demo-switcher__split-main{height:100%;border-radius:999px 0 0 999px;border-right:0;padding-right:.55rem}.demo-switcher__split-menu>summary{display:grid;height:100%;place-items:center;border-radius:0 999px 999px 0;padding:.5rem .58rem}.demo-switcher__split.is-active .demo-switcher__split-main,.demo-switcher__split.is-active .demo-switcher__split-menu>summary{background:#fff;color:#1848a1;border-color:#fff}.demo-switcher__split-menu .demo-switcher__dropdown-menu{right:0}
</style>
@include('partials.rut_input_script')
@if(request()->routeIs('contraloria.*', 'auditoria.*'))
    <div class="container pt-3">@include('partials.demo_audit_guide')</div>
@endif
@endif
