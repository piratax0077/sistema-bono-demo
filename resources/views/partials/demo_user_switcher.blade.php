@if(config('demo.enabled') && config('demo.user_switch_enabled'))
<section class="demo-switcher" aria-label="Cambiar perfil de demostración">
    <a class="demo-switcher__home" href="{{ route('demo.portal') }}"><span class="demo-switcher__mark">M</span><span>Medichile Demo</span></a>
    <span class="demo-switcher__current"><small>Perfil activo</small>{{ auth()->user()?->name ?? 'Sin sesión' }}</span>
    @foreach([
        'paciente.totem' => 'Paciente Tótem',
        'paciente.escritorio' => 'Paciente Escritorio',
        'paciente.agenda' => 'Paciente Agenda',
    ] as $destino => $etiqueta)
        <form method="POST" action="{{ route('demo.switch-user', 'paciente') }}">@csrf
            <input type="hidden" name="destino" value="{{ $destino }}">
            <button type="submit" @disabled(request()->routeIs($destino) || ($destino === 'paciente.totem' && request()->routeIs('totem.local')))>{{ $etiqueta }}</button>
        </form>
    @endforeach
    @foreach(collect(config('demo.users'))->except('paciente') as $key => $perfil)
        <form method="POST" action="{{ route('demo.switch-user', $key) }}">@csrf
            <button type="submit" @disabled(auth()->user()?->email === $perfil['email'])>{{ $perfil['label'] }}</button>
        </form>
    @endforeach
</section>
<style>
:root{--sdi-ink:#1f2d3d;--sdi-muted:#7b8798;--sdi-green:#31bebe;--sdi-green-dark:#238f99;--sdi-blue:#1848a1;--sdi-bg:#edf4ff;--sdi-line:#c9d8ef;--sdi-card:#fff;--sdi-shadow:0 18px 48px rgba(24,72,161,.09)}
html{background:var(--sdi-bg)}body{background:radial-gradient(circle at 5% 0%,rgba(49,190,190,.11),transparent 28rem),linear-gradient(180deg,#f8fbff 0,var(--sdi-bg) 100%)!important;color:var(--sdi-ink)!important;font-family:Inter,"Segoe UI",system-ui,-apple-system,sans-serif!important;min-height:100vh}h1,h2,h3,h4,h5{color:#1f2d3d;letter-spacing:-.02em}.card{border:1px solid rgba(201,216,239,.82)!important;border-radius:20px!important;box-shadow:var(--sdi-shadow)!important;background:rgba(255,255,255,.97)!important}.card-header{border-color:var(--sdi-line)!important;border-radius:20px 20px 0 0!important}.btn{border-radius:11px!important;font-weight:750!important;padding:.55rem .9rem;transition:transform .16s ease,box-shadow .16s ease,filter .16s ease}.btn:hover{transform:translateY(-1px);box-shadow:0 8px 20px rgba(24,72,161,.15)}.btn-success,.bg-success{background-color:var(--sdi-green)!important;border-color:var(--sdi-green)!important}.btn-primary{background-color:var(--sdi-blue)!important;border-color:var(--sdi-blue)!important}.btn-danger{background-color:#c94350!important;border-color:#c94350!important}.text-success{color:#238f99!important}.form-control,.form-select{border-radius:12px!important;border-color:#c9d8ef!important;padding:.68rem .82rem!important;box-shadow:none!important}.form-control:focus,.form-select:focus{border-color:#31bebe!important;box-shadow:0 0 0 .22rem rgba(49,190,190,.16)!important}.alert{border:0!important;border-radius:15px!important;box-shadow:0 8px 24px rgba(24,72,161,.06)}.table{--bs-table-bg:transparent}.table thead th{padding:.9rem .8rem;border-bottom:1px solid #c9d8ef;color:#53657b;font-size:.75rem;letter-spacing:.055em;text-transform:uppercase;white-space:nowrap}.table tbody td{padding:.88rem .8rem;border-color:#e1e9f3}.table-hover tbody tr:hover{--bs-table-hover-bg:#f0f8fb}.badge{font-weight:750;letter-spacing:.015em}.text-muted{color:var(--sdi-muted)!important}
.demo-switcher{position:sticky;top:0;z-index:9999;display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;padding:.7rem 1rem;background:linear-gradient(110deg,#133570,#1848a1 58%,#31bebe);color:#fff;box-shadow:0 8px 30px rgba(24,72,161,.24);font:700 12.5px/1.2 Inter,"Segoe UI",system-ui}.demo-switcher form{margin:0}.demo-switcher button{border:1px solid rgba(255,255,255,.3);border-radius:999px;padding:.5rem .72rem;background:rgba(255,255,255,.11);color:#fff;text-decoration:none;cursor:pointer;backdrop-filter:blur(8px);transition:.18s ease}.demo-switcher button:hover{background:rgba(255,255,255,.22);transform:translateY(-1px)}.demo-switcher button:disabled{background:#fff;color:#1848a1;border-color:#fff;opacity:1;box-shadow:0 5px 14px rgba(0,0,0,.16)}.demo-switcher__home{display:flex;align-items:center;gap:.5rem;margin-right:.25rem;color:#fff;text-decoration:none;font-size:13px}.demo-switcher__mark{display:grid;place-items:center;width:27px;height:27px;border-radius:9px;background:#fff;color:#1848a1;font-weight:950;box-shadow:0 5px 15px rgba(0,0,0,.16)}.demo-switcher__current{margin-left:auto;display:flex;flex-direction:column;align-items:flex-end;font-weight:800;white-space:nowrap}.demo-switcher__current small{color:#c9f4f4;text-transform:uppercase;font-size:9px;letter-spacing:.12em}.demo-switcher__home{order:-2}.demo-switcher__current{order:-1}@media(max-width:1050px){.demo-switcher__current{margin-left:0}}@media(max-width:760px){.demo-switcher{padding:.62rem}.demo-switcher__current{width:100%;align-items:flex-start}.demo-switcher button{font-size:11.5px;padding:.44rem .6rem}}
</style>
@include('partials.rut_input_script')
@endif
