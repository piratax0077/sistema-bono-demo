@include('partials.scroll_fade')
@if(config('demo.enabled') && config('demo.user_switch_enabled'))
@include('partials.demo_wide_layout')
<section class="demo-switcher" aria-label="Cambiar perfil de demostración">
    @php($perfilAsistente = config('demo.users.asistente'))
    @php($perfilProfesional = config('demo.users.profesional'))
    @php($perfilContralor = config('demo.users.contralor'))
    @php($enInicioDemo = request()->routeIs('home'))
    <a class="demo-switcher__home" href="{{ route('home') }}" aria-label="Medichile, ir al inicio"><img src="{{ asset('images/logotipos/medichile-white-h.svg') }}" alt="Medichile" class="demo-switcher__logo"></a>
    <a class="demo-switcher__demo-home" href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Inicio del demo</a>
    <details class="demo-switcher__usuarios">
        <summary class="demo-switcher__trigger" title="Cambiar usuario de demostración">
            <span class="demo-switcher__avatar demo-switcher__avatar--paciente" data-perfil="paciente" aria-hidden="true"><img src="{{ asset('images/imagenes/paciente.svg') }}" alt=""></span>
            <span class="demo-switcher__avatar demo-switcher__avatar--asistente" data-perfil="asistente" aria-hidden="true"><img src="{{ asset('images/imagenes/usuario_asistente.svg') }}" alt=""></span>
            <span class="demo-switcher__avatar demo-switcher__avatar--profesional" data-perfil="profesional" aria-hidden="true"><img src="{{ asset('images/imagenes/usuario_profesional.svg') }}" alt=""></span>
            <span class="demo-switcher__avatar demo-switcher__avatar--contralor" data-perfil="contralor" aria-hidden="true"><img src="{{ asset('images/imagenes/contraloria.jpg') }}" alt=""></span>
            <span class="demo-switcher__trigger-texto">
                <small>Ver demo como</small>
                <strong data-perfil="ninguno">Elegir usuario</strong>
                <strong data-perfil="paciente">Paciente</strong>
                <strong data-perfil="asistente">{{ $perfilAsistente['label'] }}</strong>
                <strong data-perfil="profesional">Profesional</strong>
                <strong data-perfil="contralor">Contraloría</strong>
            </span>
            <span class="demo-switcher__caret" aria-hidden="true">▾</span>
        </summary>
        <div class="demo-switcher__panel">
            <div class="demo-switcher__panel-cabecera">
                <strong>Usuarios de demostración</strong>
                <span>Elige un perfil para recorrer su experiencia.</span>
            </div>
            <div class="demo-switcher__lista">
                <div @class(['demo-switcher__usuario', 'demo-switcher__usuario--paciente', 'is-active' => ! $enInicioDemo && request()->routeIs('paciente.*', 'totem.local')])>
                    <form method="POST" action="{{ route('demo.switch-user', 'paciente') }}">@csrf
                        <input type="hidden" name="destino" value="paciente.home">
                        <button type="submit" class="demo-switcher__usuario-main">
                            <span class="demo-switcher__avatar demo-switcher__avatar--paciente" aria-hidden="true"><img src="{{ asset('images/imagenes/paciente.svg') }}" alt=""></span>
                            <span class="demo-switcher__usuario-texto"><strong>Paciente</strong><small>Entrar con este usuario</small></span>
                        </button>
                    </form>
                </div>
                <div @class(['demo-switcher__usuario', 'demo-switcher__usuario--asistente', 'is-active' => ! $enInicioDemo && auth()->user()?->email === $perfilAsistente['email']])>
                    <form method="POST" action="{{ route('demo.switch-user', 'asistente') }}">@csrf
                        <input type="hidden" name="destino" value="asistente.home">
                        <button type="submit" class="demo-switcher__usuario-main">
                            <span class="demo-switcher__avatar demo-switcher__avatar--asistente" aria-hidden="true"><img src="{{ asset('images/imagenes/usuario_asistente.svg') }}" alt=""></span>
                            <span class="demo-switcher__usuario-texto"><strong>{{ $perfilAsistente['label'] }}</strong><small>Entrar con este usuario</small></span>
                        </button>
                    </form>
                </div>
                <div @class(['demo-switcher__usuario', 'demo-switcher__usuario--profesional', 'is-active' => ! $enInicioDemo && auth()->user()?->email === $perfilProfesional['email']])>
                    <form method="POST" action="{{ route('demo.switch-user', 'profesional') }}">@csrf
                        <input type="hidden" name="destino" value="profesional.home">
                        <button type="submit" class="demo-switcher__usuario-main">
                            <span class="demo-switcher__avatar demo-switcher__avatar--profesional" aria-hidden="true"><img src="{{ asset('images/imagenes/usuario_profesional.svg') }}" alt=""></span>
                            <span class="demo-switcher__usuario-texto"><strong>Profesional</strong><small>Entrar con este usuario</small></span>
                        </button>
                    </form>
                </div>
                <div @class(['demo-switcher__usuario', 'demo-switcher__usuario--contralor', 'is-active' => ! $enInicioDemo && auth()->user()?->email === $perfilContralor['email']])>
                    <form method="POST" action="{{ route('demo.switch-user', 'contralor') }}">@csrf
                        <input type="hidden" name="destino" value="auditoria.index">
                        <button type="submit" class="demo-switcher__usuario-main">
                            <span class="demo-switcher__avatar demo-switcher__avatar--contralor" aria-hidden="true"><img src="{{ asset('images/imagenes/contraloria.jpg') }}" alt=""></span>
                            <span class="demo-switcher__usuario-texto"><strong>Contraloría</strong><small>Entrar con este usuario</small></span>
                        </button>
                    </form>
                </div>
                @foreach(collect(config('demo.users'))->except(['paciente', 'asistente', 'profesional', 'contralor', 'administrador']) as $key => $perfil)
                    <div class="demo-switcher__usuario">
                        <form method="POST" action="{{ route('demo.switch-user', $key) }}">@csrf
                            <button type="submit" class="demo-switcher__usuario-main" @disabled(auth()->user()?->email === $perfil['email'])>
                                <span class="demo-switcher__avatar demo-switcher__avatar--inicial" aria-hidden="true">{{ $perfil['label'] }}</span>
                                <span class="demo-switcher__usuario-texto"><strong>{{ $perfil['label'] }}</strong><small>Entrar con este usuario</small></span>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </details>
</section>
@if(session('perfil_cambiado'))
<div class="demo-toast" id="demoToastPerfil" role="status" aria-live="polite">
    <span class="demo-toast__icono" aria-hidden="true">✓</span>
    <span class="demo-toast__texto">{{ session('perfil_cambiado') }}</span>
    <button type="button" class="demo-toast__cerrar" aria-label="Cerrar notificación">×</button>
    <span class="demo-toast__progreso" aria-hidden="true"></span>
</div>
<script>
(function () {
    var toast = document.getElementById('demoToastPerfil');
    if (!toast) return;
    var duracion = 25000;
    toast.style.setProperty('--demo-toast-duracion', duracion + 'ms');
    var cerrar = function () {
        toast.classList.add('is-saliendo');
        setTimeout(function () { toast.remove(); }, 300);
    };
    requestAnimationFrame(function () { toast.classList.add('is-visible'); });
    var temporizador = setTimeout(cerrar, duracion);
    toast.querySelector('.demo-toast__cerrar').addEventListener('click', function () {
        clearTimeout(temporizador);
        cerrar();
    });
})();
</script>
@endif
<style>
.demo-toast{position:fixed;top:90px;right:1rem;z-index:10050;display:flex;align-items:center;gap:.7rem;width:min(380px,calc(100vw - 2rem));padding:.9rem 1rem;overflow:hidden;border-radius:15px;background:#d4ede6;color:#1f3553;box-shadow:0 14px 36px rgba(16,39,74,.18);font:600 14px/1.35 'Manrope',sans-serif;opacity:0;transform:translateY(-10px);transition:opacity .3s ease,transform .3s ease}.demo-toast.is-visible{opacity:1;transform:translateY(0)}.demo-toast.is-saliendo{opacity:0;transform:translateY(-10px)}.demo-toast__icono{display:flex;align-items:center;justify-content:center;flex:0 0 auto;width:26px;height:26px;border-radius:50%;background:#31bebe;color:#fff;font-size:13px;font-weight:800}.demo-toast__texto{flex:1;min-width:0}.demo-toast__cerrar{flex:0 0 auto;border:0;background:transparent;color:#53657b;font-size:20px;line-height:1;padding:0 .2rem;cursor:pointer}.demo-toast__cerrar:hover{color:#1848a1}.demo-toast__progreso{position:absolute;left:0;bottom:0;height:3px;width:100%;background:#31bebe;transform-origin:left;animation:demoToastProgreso var(--demo-toast-duracion,25s) linear forwards}
@keyframes demoToastProgreso{from{transform:scaleX(1)}to{transform:scaleX(0)}}
@media(max-width:760px){.demo-toast{top:auto;bottom:1rem;right:1rem;left:1rem;width:auto}}
.demo-switcher__demo-home{display:inline-block;border:1px solid #ffffff66;border-radius:999px;padding:.5rem .8rem;color:#fff;text-decoration:none;background:#ffffff1c}.demo-switcher__demo-home:hover,.demo-switcher__demo-home[aria-current="page"]{background:#fff;color:#1848a1}
:root{--sdi-ink:#1f2d3d;--sdi-muted:#7b8798;--sdi-green:#31bebe;--sdi-green-dark:#238f99;--sdi-blue:#1848a1;--sdi-bg:#edf4ff;--sdi-line:#c9d8ef;--sdi-card:#fff;--sdi-shadow:0 18px 48px rgba(24,72,161,.09)}
html{background:var(--sdi-bg)}body{background:radial-gradient(circle at 5% 0%,rgba(49,190,190,.11),transparent 28rem),linear-gradient(180deg,#f8fbff 0,var(--sdi-bg) 100%)!important;color:var(--sdi-ink)!important;font-family:'Manrope',sans-serif!important;min-height:100vh}h1,h2,h3,h4,h5{color:#1f2d3d;letter-spacing:-.02em}.card{border:1px solid rgba(201,216,239,.82)!important;border-radius:20px!important;box-shadow:var(--sdi-shadow)!important;background:rgba(255,255,255,.97)!important}.card-header{border-color:var(--sdi-line)!important;border-radius:20px 20px 0 0!important}.btn{border-radius:11px!important;font-weight:750!important;padding:.55rem .9rem;transition:transform .16s ease,box-shadow .16s ease,filter .16s ease}.btn:hover{transform:translateY(-1px);box-shadow:0 8px 20px rgba(24,72,161,.15)}.btn-success,.bg-success{background-color:var(--sdi-green)!important;border-color:var(--sdi-green)!important}.btn-primary{background-color:var(--sdi-blue)!important;border-color:var(--sdi-blue)!important}.btn-danger{background-color:#c94350!important;border-color:#c94350!important}.text-success{color:#238f99!important}.form-control,.form-select{border-radius:12px!important;border-color:#c9d8ef!important;padding:.68rem .82rem!important;box-shadow:none!important}.form-control:focus,.form-select:focus{border-color:#31bebe!important;box-shadow:0 0 0 .22rem rgba(49,190,190,.16)!important}.alert{border:0!important;border-radius:15px!important;box-shadow:0 8px 24px rgba(24,72,161,.06)}.table{--bs-table-bg:transparent}.table thead th{padding:.9rem .8rem;border-bottom:1px solid #c9d8ef;color:#53657b;font-size:.75rem;letter-spacing:.055em;text-transform:uppercase;white-space:nowrap}.table tbody td{padding:.88rem .8rem;border-color:#e1e9f3}.table-hover tbody tr:hover{--bs-table-hover-bg:#f0f8fb}.badge{font-weight:750;letter-spacing:.015em}.text-muted{color:var(--sdi-muted)!important}
.demo-switcher{position:sticky;top:0;z-index:9999;display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;padding:.7rem 1rem;background:linear-gradient(110deg,#133570,#1848a1 58%,#31bebe);color:#fff;box-shadow:0 8px 30px rgba(24,72,161,.24);font:700 12.5px/1.2 'Manrope',sans-serif}.demo-switcher form{margin:0}.demo-switcher button{border:1px solid rgba(255,255,255,.3);border-radius:999px;padding:.5rem .72rem;background:rgba(255,255,255,.11);color:#fff;text-decoration:none;cursor:pointer;backdrop-filter:blur(8px);transition:.18s ease}.demo-switcher button:hover{background:rgba(255,255,255,.22);transform:translateY(-1px)}.demo-switcher button:disabled{background:#fff;color:#1848a1;border-color:#fff;opacity:1;box-shadow:0 5px 14px rgba(0,0,0,.16)}.demo-switcher__home{display:flex;align-items:center;gap:.5rem;margin-right:.25rem;color:#fff;text-decoration:none;font-size:13px}.demo-switcher__logo{display:block;height:clamp(44px,4.4vw,56px);width:auto;max-width:48vw;margin:-8px 0;object-fit:contain}.demo-switcher__current{position:relative;margin-left:auto;font-weight:800;white-space:nowrap}.demo-switcher__current>summary{display:flex;align-items:center;gap:.55rem;list-style:none;padding:.28rem .42rem;border-radius:11px;cursor:pointer;transition:.18s ease}.demo-switcher__current>summary::-webkit-details-marker{display:none}.demo-switcher__current>summary:hover{background:rgba(255,255,255,.12)}.demo-switcher__current>summary>span:first-child{display:flex;flex-direction:column;align-items:flex-end}.demo-switcher__current small{color:#c9f4f4;text-transform:uppercase;font-size:9px;letter-spacing:.12em;font-weight:700}.demo-switcher__home{order:-2}.demo-switcher__current{order:-1}@media(max-width:1050px){.demo-switcher__current{margin-left:0}}@media(max-width:760px){.demo-switcher{padding:.62rem}.demo-switcher__current{width:100%}.demo-switcher__current>summary{justify-content:flex-start}.demo-switcher__current>summary>span:first-child{align-items:flex-start}.demo-switcher button{font-size:11.5px;padding:.44rem .6rem}}
.demo-switcher__profile-caret{font-size:11px;transition:transform .18s}.demo-switcher__profile[open] .demo-switcher__profile-caret{transform:rotate(180deg)}.demo-switcher__profile-menu{position:absolute;top:calc(100% + .55rem);right:0;z-index:10001;min-width:230px;padding:.55rem;border:1px solid #d7e2f2;border-radius:15px;background:#fff;color:#294d78;box-shadow:0 18px 44px rgba(16,39,74,.25);font-family:'Manrope',sans-serif}.demo-switcher__profile-info{display:flex;flex-direction:column;gap:.25rem;padding:.65rem .7rem .8rem;border-bottom:1px solid #e3eaf4}.demo-switcher__profile-info strong{color:#1f3553;font-weight:800}.demo-switcher__profile-info small{max-width:205px;overflow:hidden;color:#7b8798;text-overflow:ellipsis;text-transform:none;letter-spacing:0;font-weight:500}.demo-switcher__profile-link{display:flex;align-items:center;gap:.5rem;margin-top:.4rem;padding:.68rem .72rem;border-radius:10px;color:#294d78;text-decoration:none;font-weight:700}.demo-switcher__profile-link:hover{background:#edf4ff;color:#1848a1}.demo-switcher__profile-menu form{padding-top:.2rem}.demo-switcher__profile-menu .demo-switcher__logout{display:flex;width:100%;align-items:center;gap:.5rem;border:0;border-radius:10px;padding:.68rem .72rem;background:transparent;color:#b73542;text-align:left;backdrop-filter:none;font-weight:700;font-family:inherit}.demo-switcher__profile-menu .demo-switcher__logout:hover{background:#fff0f1;color:#9d2834;box-shadow:none;transform:none}@media(max-width:760px){.demo-switcher__profile-menu{left:0;right:auto}}
.demo-switcher__usuarios{position:relative;margin-left:auto}.demo-switcher__usuarios::after{content:"";position:absolute;top:100%;left:0;right:0;height:.8rem}
.demo-switcher__trigger{display:flex;align-items:center;gap:.6rem;list-style:none;padding:.3rem .85rem .3rem .3rem;border:1px solid rgba(255,255,255,.3);border-radius:20px;background:rgba(255,255,255,.11);color:#fff;cursor:pointer;backdrop-filter:blur(8px);transition:.18s ease}.demo-switcher__trigger::-webkit-details-marker{display:none}.demo-switcher__trigger:hover,.demo-switcher__usuarios[open]>.demo-switcher__trigger{background:rgba(255,255,255,.22)}.demo-switcher__trigger-texto{display:flex;flex-direction:column;gap:.15rem;line-height:1.1}.demo-switcher__trigger-texto small{color:#c9f4f4;font-size:9.5px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}.demo-switcher__trigger-texto strong{font-size:13.5px;font-weight:800}.demo-switcher__caret{margin-left:.15rem;font-size:11px;transition:transform .18s}.demo-switcher__usuarios[open] .demo-switcher__caret{transform:rotate(180deg)}
.demo-switcher__trigger [data-perfil]{display:none}.demo-switcher:not(:has(.demo-switcher__usuario.is-active)) .demo-switcher__trigger{padding-left:.85rem}.demo-switcher:not(:has(.demo-switcher__usuario.is-active)) .demo-switcher__trigger strong[data-perfil="ninguno"],.demo-switcher:has(.demo-switcher__usuario--paciente.is-active) .demo-switcher__trigger [data-perfil="paciente"],.demo-switcher:has(.demo-switcher__usuario--asistente.is-active) .demo-switcher__trigger [data-perfil="asistente"],.demo-switcher:has(.demo-switcher__usuario--profesional.is-active) .demo-switcher__trigger [data-perfil="profesional"],.demo-switcher:has(.demo-switcher__usuario--contralor.is-active) .demo-switcher__trigger [data-perfil="contralor"]{display:flex}
.demo-switcher__avatar{display:flex;align-items:center;justify-content:center;flex:0 0 auto;width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#8a97ab,#4d5d75);color:#fff;font-size:11px;font-weight:800;letter-spacing:.04em;box-shadow:inset 0 0 0 2px rgba(255,255,255,.35)}.demo-switcher__avatar--paciente{background:linear-gradient(135deg,#3fd0c9,#238f99)}.demo-switcher__avatar--asistente{background:linear-gradient(135deg,#6f9cf5,#3567c9)}.demo-switcher__avatar--profesional{background:linear-gradient(135deg,#2b5fc4,#133570)}.demo-switcher__avatar--contralor{background:linear-gradient(135deg,#8a97ab,#4d5d75)}.demo-switcher__avatar{overflow:hidden}.demo-switcher__avatar img{display:block;width:100%;height:100%;object-fit:cover}.demo-switcher__avatar--inicial{display:block;font-size:0;line-height:40px;text-align:center}.demo-switcher__avatar--inicial::first-letter{font-size:13px}
.demo-switcher__panel{position:absolute;top:calc(100% + .65rem);right:0;z-index:10000;width:min(660px,calc(100vw - 1.5rem));max-height:calc(100vh - 110px);overflow:auto;padding:1rem;border:1px solid #d7e2f2;border-radius:18px;background:#fff;color:#294d78;box-shadow:0 22px 50px rgba(16,39,74,.26);font-family:'Manrope',sans-serif;cursor:default}.demo-switcher__usuarios[open]>.demo-switcher__panel{animation:demoDropdownHoverIn .16s ease-out}.demo-switcher__panel-cabecera{display:flex;flex-direction:column;gap:.2rem;padding:.15rem .25rem .85rem;margin-bottom:.85rem;border-bottom:1px solid #e3eaf4}.demo-switcher__panel-cabecera strong{color:#1f3553;font-size:15px;font-weight:800}.demo-switcher__panel-cabecera span{color:#7b8798;font-size:12.5px;font-weight:600}
.demo-switcher__lista{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem}.demo-switcher__usuario{position:relative;display:flex;flex-direction:column;gap:.7rem;padding:.75rem;border:1px solid #e3eaf4;border-radius:14px;background:#fff;transition:border-color .18s ease,box-shadow .18s ease}.demo-switcher__usuario:hover{border-color:#c9d8ef;box-shadow:0 10px 24px rgba(24,72,161,.08)}.demo-switcher__usuario.is-active{border-color:#31bebe;background:#f3fbfb;box-shadow:0 0 0 3px rgba(49,190,190,.13)}.demo-switcher__usuario.is-active::after{content:"Activo";position:absolute;top:.8rem;right:.8rem;padding:.24rem .55rem;border-radius:999px;background:#31bebe;color:#fff;font-size:9.5px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.demo-switcher .demo-switcher__usuario-main{display:flex;align-items:center;gap:.7rem;width:100%;padding:.1rem;border:0;border-radius:10px;background:transparent;color:#1f3553;text-align:left;font-family:inherit;backdrop-filter:none;box-shadow:none;transform:none}.demo-switcher button.demo-switcher__usuario-main:hover,.demo-switcher button.demo-switcher__usuario-main:disabled{background:transparent;color:#1f3553;box-shadow:none;transform:none}.demo-switcher button.demo-switcher__usuario-main:hover strong{color:#1848a1}.demo-switcher__usuario.is-active .demo-switcher__usuario-main{padding-right:4.5rem}.demo-switcher__usuario .demo-switcher__avatar{width:40px;height:40px;border-radius:50%;font-size:12px;box-shadow:none}.demo-switcher__usuario-texto{display:flex;flex-direction:column;gap:.18rem;min-width:0}.demo-switcher__usuario-texto strong{color:#1f3553;font-size:14px;font-weight:800;transition:color .18s ease}.demo-switcher__usuario-texto small{color:#7b8798;font-size:11.5px;font-weight:600}
@keyframes demoDropdownHoverIn{from{opacity:0;transform:translateY(-5px)}to{opacity:1;transform:translateY(0)}}
@media(hover:hover) and (min-width:761px){.demo-switcher__usuarios:hover::details-content{content-visibility:visible}.demo-switcher__usuarios:hover>.demo-switcher__panel{display:block;animation:demoDropdownHoverIn .16s ease-out}.demo-switcher__usuarios:hover>.demo-switcher__trigger{background:rgba(255,255,255,.22)}.demo-switcher__usuarios:hover .demo-switcher__caret{transform:rotate(180deg)}}
@media(max-width:760px){.demo-switcher__usuarios{position:static;width:100%;margin-left:0}.demo-switcher__trigger{width:100%}.demo-switcher__caret{margin-left:auto}.demo-switcher__panel{top:calc(100% - .2rem);left:.6rem;right:.6rem;width:auto}.demo-switcher__lista{grid-template-columns:minmax(0,1fr)}}
</style>
@include('partials.rut_input_script')
@if(request()->routeIs('contraloria.*', 'auditoria.*'))
    <div class="container pt-3">@include('partials.demo_audit_guide')</div>
@endif
@endif
