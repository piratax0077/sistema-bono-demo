<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tótem Medichile · Sistema de Bonos Demo</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;font-family:Inter,Segoe UI,Arial,sans-serif;background:linear-gradient(145deg,#e4f5f1,#f7faf9);color:#123532;display:grid;place-items:center;padding:24px}.shell{width:min(980px,100%);background:#fff;border-radius:30px;box-shadow:0 24px 70px rgba(6,52,47,.15);overflow:hidden}.head{padding:28px 34px;background:#073f38;color:#fff;display:flex;justify-content:space-between;align-items:center;gap:20px}.brand small{display:block;color:#9de1d5;text-transform:uppercase;letter-spacing:.15em;font-weight:800}.brand h1{margin:5px 0 0;font-size:clamp(26px,4vw,42px)}.status{background:#d8f7e9;color:#096447;border-radius:999px;padding:10px 16px;font-weight:900}.body{padding:36px}.intro{text-align:center;margin-bottom:30px}.intro h2{font-size:clamp(25px,4vw,38px);margin:0 0 10px}.intro p{color:#5d716d;font-size:18px}.options{display:grid;grid-template-columns:1fr 1fr;gap:22px}.option{border:1px solid #cfe6df;border-radius:22px;padding:26px;background:#f8fcfb}.option .icon{font-size:40px}.option h3{font-size:24px;margin:12px 0 8px}.option p{color:#60736f;min-height:48px}.option button{width:100%;border:0;border-radius:14px;padding:15px;background:#07866f;color:#fff;font-size:17px;font-weight:900;cursor:pointer}.option.secondary button{background:#155ba4}.meta{margin-top:28px;padding:17px 20px;border-radius:16px;background:#edf7f4;display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap}.meta strong{color:#075e54}.foot{display:flex;justify-content:center;gap:12px;margin-top:24px}.foot a{color:#075e54;font-weight:800;text-decoration:none;padding:10px}@media(max-width:700px){.head{align-items:flex-start;flex-direction:column}.body{padding:24px 18px}.options{grid-template-columns:1fr}.option p{min-height:auto}}
    </style>
    <style>
        .tabs{display:grid;grid-template-columns:1fr 1fr;gap:10px;background:#eaf4f1;padding:8px;border-radius:18px;margin-bottom:28px}.tab{display:block;text-align:center;padding:15px;border-radius:13px;color:#315651;text-decoration:none;font-weight:900;font-size:17px}.tab.active{background:#fff;color:#075e54;box-shadow:0 5px 18px rgba(6,52,47,.1)}.panel{border:1px solid #cfe6df;border-radius:22px;padding:28px;background:#fbfefd}.panel h2{margin:0 0 8px;font-size:28px}.panel>p{color:#60736f}.primary{display:inline-block;width:100%;border:0;border-radius:14px;padding:15px;background:#07866f;color:#fff;font-size:17px;font-weight:900;cursor:pointer;text-align:center}.next-screen{display:flex;justify-content:center;margin-top:22px}.next-screen a{display:block;width:100%;padding:17px 22px;border-radius:15px;background:#155ba4;color:#fff;text-align:center;text-decoration:none;font-size:18px;font-weight:900;box-shadow:0 9px 22px rgba(21,91,164,.2)}.next-screen small{display:block;margin-top:4px;color:#dcecff;font-size:13px;font-weight:600}.flow-diagram{margin-top:28px;padding:22px;border:1px solid #cfe6df;border-radius:20px;background:#f4faf8}.flow-diagram h3{margin:0 0 6px;text-align:center;color:#075e54}.flow-diagram>p{margin:0 0 18px;text-align:center;color:#60736f}.flow-line{display:grid;grid-template-columns:1fr auto 1fr auto 1fr auto 1fr auto 1fr;align-items:center;gap:8px}.flow-step{min-height:112px;padding:14px 9px;border-radius:15px;background:#fff;border:1px solid #c8dfd9;text-align:center;box-shadow:0 5px 14px rgba(6,52,47,.06)}.flow-step strong{display:block;margin:6px 0 4px;color:#074b43}.flow-step small{color:#60736f;line-height:1.3}.flow-icon{font-size:28px}.flow-arrow{font-size:25px;color:#07866f;font-weight:900}.admin-only{display:flex;justify-content:center;margin-top:18px}.admin-only a{padding:10px 14px;border-radius:10px;background:#e7eef7;color:#264d78;text-decoration:none;font-weight:800}.field{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px;margin-top:22px}.field input{width:100%;border:2px solid #bddbd3;border-radius:14px;padding:15px;font-size:19px;text-transform:uppercase}.field button{border:0;border-radius:14px;padding:0 24px;background:#155ba4;color:#fff;font-weight:900;font-size:17px;cursor:pointer}.flash{padding:15px 18px;border-radius:14px;margin-bottom:18px;font-weight:800}.flash.ok{background:#d8f7e9;color:#096447}.flash.error{background:#ffe0e0;color:#922323}.voucher-list{display:grid;gap:14px;margin-top:20px}.voucher{border:1px solid #c9ded8;border-radius:16px;padding:18px;display:grid;grid-template-columns:1fr auto;gap:18px;align-items:center}.voucher h3{margin:0 0 8px}.voucher p{margin:4px 0;color:#5d716d}.voucher form button{border:0;border-radius:12px;padding:13px 18px;background:#07866f;color:#fff;font-weight:900;cursor:pointer}.hint{margin-top:16px;padding:13px 15px;background:#eef4ff;color:#254e7b;border-radius:12px}.error-text{color:#a32020;font-weight:800;margin-top:8px}@media(max-width:820px){.flow-line{grid-template-columns:1fr}.flow-arrow{transform:rotate(90deg);text-align:center}}@media(max-width:700px){.field,.voucher{grid-template-columns:1fr}.field button{padding:15px}.tabs{grid-template-columns:1fr}.voucher form button{width:100%}}
    </style>
    <style>
        .med-search{margin-top:18px;background:#eef7f5;border:1px solid #c9e2dc;border-radius:18px;padding:18px}.med-search-grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px}.med-search label{display:block;color:#4e6963;font-weight:800;font-size:12px;margin:0 0 6px}.med-search input,.med-search select{width:100%;border:1px solid #b8d6ce;border-radius:12px;padding:12px;background:#fff;font-size:15px}.schedule-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:18px}.schedule-card{border:1px solid #c8dfd9;border-radius:17px;padding:17px;background:#fff}.schedule-date{color:#087f6f;font-size:12px;text-transform:uppercase;font-weight:900;letter-spacing:.08em}.schedule-time{font-size:27px;color:#087f6f;font-weight:900;margin:3px 0}.schedule-card h3{margin:5px 0}.schedule-card p{margin:4px 0;color:#5e716d}.schedule-price{display:flex;justify-content:space-between;background:#edf7f4;border-radius:11px;padding:10px;margin:12px 0}.schedule-price small{display:block;color:#60736f}.schedule-card button{width:100%;border:0;border-radius:11px;padding:12px;background:#07866f;color:#fff;font-weight:900;cursor:pointer}.schedule-empty{display:none;margin-top:16px;padding:15px;background:#fff1d6;color:#7d5700;border-radius:12px}.qr-result{border:2px solid #40b98d;border-radius:17px;padding:18px;background:#effbf6;margin-bottom:18px}.qr-result h3{margin:0 0 8px;color:#087f6f}.qr-result a{display:inline-block;margin:6px 6px 0 0;padding:9px 13px;border-radius:10px;background:#07866f;color:#fff;text-decoration:none;font-weight:800}@media(max-width:760px){.med-search-grid,.schedule-grid{grid-template-columns:1fr}}
    </style>
    <style>
        body{background:linear-gradient(145deg,#edf4ff,#f8fbff);color:#1f2d3d}.shell{border:1px solid #d7e2f2;box-shadow:0 24px 70px rgba(24,72,161,.14)}.head{background:linear-gradient(120deg,#1848a1,#31bebe)}.brand small{color:#d9f7f7}.status,.flash.ok{background:#e7f9f9;color:#237f87}.panel,.option,.flow-diagram{border-color:#c9d8ef;background:#fbfdff}.primary,.option button,.voucher form button,.schedule-card button,.qr-result a{background:#31bebe}.next-screen a,.field button,.option.secondary button{background:#1848a1}.meta,.schedule-price{background:#edf4ff}.meta strong,.tab.active,.flow-diagram h3,.schedule-date,.schedule-time,.qr-result h3{color:#1848a1}.flow-arrow{color:#31bebe}.tabs,.med-search{background:#edf4ff;border-color:#c9d8ef}.tab{color:#52657e}.flow-step,.schedule-card,.voucher{border-color:#d5e1f1;box-shadow:0 8px 22px rgba(24,72,161,.07)}.flow-step strong{color:#1f2d3d}.med-search input,.med-search select,.field input{border-color:#c9d8ef}.qr-result{border-color:#31bebe;background:#f0fbfb}.hint{background:#edf4ff;color:#1848a1}
    </style>
    <style>.method-separator{display:flex;align-items:center;gap:12px;margin:22px 0;color:#71849b;font-weight:800}.method-separator:before,.method-separator:after{content:"";height:1px;flex:1;background:#d5e1f1}.qr-input{text-transform:none!important}.automatic-match{margin:18px 0;padding:16px 18px;border-radius:14px;background:#e7f9f9;color:#176d75;font-weight:800}.alternate-identification{margin-top:15px}.alternate-identification summary{cursor:pointer;color:#1848a1;font-weight:900;padding:10px 0}.next-screen form{width:100%;margin:0}.next-screen button{display:block;width:100%;border:0;padding:17px 22px;border-radius:15px;background:#155ba4;color:#fff;text-align:center;font-family:inherit;font-size:18px;font-weight:900;cursor:pointer;box-shadow:0 9px 22px rgba(21,91,164,.2)}</style>
</head>
<body style="display:block;padding:0">
@include('partials.demo_user_switcher')
<main class="shell" style="margin:24px auto">
    <header class="head">
        <div class="brand"><small>Medichile · SDI Salud Digital Integrada</small><h1>Tótem de atención</h1></div>
        <div class="status">● {{ $totem ? 'ACTIVO' : 'SIN EQUIPO ACTIVO' }}</div>
    </header>
    <section class="body">
        @php($soloCompra = request()->routeIs('paciente.totem'))
        @php($tab = $soloCompra ? 'comprar' : request('tab', 'comprar'))
        <div class="intro">
            <h2>{{ $soloCompra ? 'Paciente Tótem · Comprar bono' : 'Autoatención Medichile' }}</h2>
            <p>{{ $soloCompra ? 'Vista exclusiva para seleccionar hora, pagar y recibir el QR del bono.' : 'Compra tu bono o avisa tu llegada sin pasar por recepción.' }}</p>
        </div>
        @unless($soloCompra)
            <nav class="tabs" aria-label="Opciones del tótem">
                <a class="tab {{ $tab === 'comprar' ? 'active' : '' }}" href="{{ route('totem.local', ['tab' => 'comprar']) }}">🎫 Comprar bono</a>
                <a class="tab {{ $tab === 'autoatencion' ? 'active' : '' }}" href="{{ route('totem.local', ['tab' => 'autoatencion']) }}">✓ Avisar que llegué</a>
            </nav>
        @endunless

        @if(session('checkin_ok'))<div class="flash ok">✓ {{ session('checkin_ok') }}</div>@endif
        @if(session('ok'))<div class="flash ok">{{ session('ok') }}</div>@endif
        @if(session('error'))<div class="flash error">{{ session('error') }}</div>@endif

        @if($tab === 'autoatencion')
            <section class="panel">
                <h2>Avisar que llegué</h2>
                <p>El tótem reconoce automáticamente las horas vinculadas al paciente y a su médico.</p>
                @if($identificacionAutomatica)
                    <div class="automatic-match">✓ Paciente reconocido. Encontramos la relación médico–paciente y la hora reservada.</div>
                @endif
                <details class="alternate-identification" @if(!$identificacionAutomatica) open @endif>
                    <summary>{{ $identificacionAutomatica ? 'Usar otro QR o buscar otra hora por RUT' : 'Identificar reserva por QR o RUT' }}</summary>
                <form method="POST" action="{{ route('totem.local.anexar-qr') }}">
                    @csrf
                    <div class="field">
                        <input class="qr-input" name="qr" value="{{ old('qr') }}" placeholder="Escanee el QR o pegue el enlace del bono" autocomplete="off" autofocus required>
                        <button type="submit" @disabled(!$totem)>Anexar QR</button>
                    </div>
                    @error('qr')<div class="error-text">{{ $message }}</div>@enderror
                </form>
                <div class="method-separator">o buscar por RUT</div>
                <form method="POST" action="{{ route('totem.local.buscar-hora') }}">
                    @csrf
                    <div class="field">
                        <input name="rut" value="{{ old('rut') }}" placeholder="Ej.: 6.187.674-K" autocomplete="off" data-rut-input required>
                        <button type="submit" @disabled(!$totem)>Buscar mi hora</button>
                    </div>
                    @error('rut')<div class="error-text">{{ $message }}</div>@enderror
                </form>
                <div class="hint">Seguridad: sólo se muestran bonos vigentes después de validar el QR seguro o el RUT del paciente.</div>
                </details>

                @if($bonos->isNotEmpty())
                    <div class="voucher-list">
                        @foreach($bonos as $bono)
                            <article class="voucher">
                                <div>
                                    <h3>{{ $bono->tipo_servicio }} · {{ $bono->codigo }}</h3>
                                    <p><strong>Paciente:</strong> {{ $bono->beneficiario_nombre ?: $bono->cliente_nombre }}</p>
                                    <p><strong>Profesional:</strong> {{ $bono->prestador_nombre ?: optional($bono->profesional)->nombre }}</p>
                                    <p><strong>Hora:</strong> {{ optional($bono->agenda?->fecha_hora_confirmada ?: $bono->agenda?->fecha_hora_solicitada)?->format('d-m-Y H:i') ?? 'Hora asociada en Medichile' }}</p>
                                    <p><strong>Estado:</strong> {{ optional($bono->agenda)->estado === 'paciente_en_espera' ? 'Paciente en espera' : 'Hora confirmada' }}</p>
                                </div>
                                <form method="POST" action="{{ route('totem.local.confirmar-llegada', $bono) }}">
                                    @csrf
                                    <button type="submit">{{ optional($bono->agenda)->estado === 'paciente_en_espera' ? 'Llegada confirmada' : 'Confirmar mi llegada' }}</button>
                                </form>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @elseif($soloCompra)
            <section class="panel">
                <h2>Comprar bono</h2>
                <p>Busca como en Medichile: profesional por RUT o nombre, especialidad, fecha y centro de atención.</p>
                @if($agendaOnlineResultado)
                    <div class="qr-result"><h3>✓ Hora reservada y bono generado</h3><p><strong>{{ $agendaOnlineResultado->codigo }}</strong> · {{ $agendaOnlineResultado->prestador_nombre }} · {{ optional($agendaOnlineResultado->agenda?->fecha_hora_confirmada)->format('d-m-Y H:i') }}</p><a href="{{ route('vouchers.qr', $agendaOnlineResultado->qr_token) }}">Ver QR</a><a href="{{ route('totem.local', ['tab' => 'autoatencion']) }}">Anexar QR y avisar llegada</a></div>
                @endif
                <button class="primary" type="button" id="pedirHoraTotem" @disabled(!$totem)>Pedir hora</button>
                <div class="med-search" id="buscadorTotem" hidden>
                    <div class="med-search-grid">
                        <div><label>Profesional por RUT o nombre</label><input type="search" id="buscarProfesionalTotem" placeholder="Ej.: 11.111.111-1 o Profesional Revisión"></div>
                        <div><label>Especialidad</label><select id="especialidadTotem"><option value="">Todas</option>@foreach($horariosOnline->pluck('profesional.especialidad')->filter()->unique() as $especialidad)<option value="{{ mb_strtolower($especialidad) }}">{{ $especialidad }}</option>@endforeach</select></div>
                        <div><label>Fecha</label><input type="date" id="fechaTotem" autocomplete="off" min="{{ now()->toDateString() }}" value="{{ optional($horariosOnline->first())->fecha_hora?->toDateString() }}" max="{{ optional($horariosOnline->last())->fecha_hora?->toDateString() }}"></div>
                    </div>
                    <div class="schedule-grid" id="horariosTotem">
                        @foreach($horariosOnline as $horario)
                            <article class="schedule-card" data-profesional="{{ mb_strtolower($horario->profesional->nombre.' '.$horario->profesional->rut) }}" data-especialidad="{{ mb_strtolower($horario->profesional->especialidad) }}" data-fecha="{{ $horario->fecha_hora->format('Y-m-d') }}">
                                <div class="schedule-date">{{ $horario->fecha_hora->format('d-m-Y') }} · {{ $horario->centro_nombre }}</div><div class="schedule-time">{{ $horario->fecha_hora->format('H:i') }}</div>
                                <h3>{{ $horario->profesional->nombre }}</h3><p>RUT {{ sdi_formatear_rut($horario->profesional->rut) }} · {{ $horario->profesional->especialidad }}</p><p>{{ $horario->servicio->nombre }}<br>{{ $horario->lugar_atencion }} · {{ $horario->centro_direccion }}</p>
                                <div class="schedule-price"><div><small>Valor</small><strong>${{ number_format($horario->servicio->valor_base,0,',','.') }}</strong></div><div><small>Copago</small><strong>${{ number_format($horario->servicio->copago_base,0,',','.') }}</strong></div></div>
                                @if(auth()->user()?->rol === 'cliente')
                                    <form method="POST" action="{{ route('cliente.agenda-online.comprar') }}">@csrf<input type="hidden" name="horario_id" value="{{ $horario->id }}"><input type="hidden" name="rut" value="{{ auth()->user()->rut }}"><input type="hidden" name="metodo_pago" value="tarjeta_demo_online"><input type="hidden" name="return_to" value="paciente.totem"><button type="submit">Seleccionar hora y comprar bono</button></form>
                                @else
                                    <form method="POST" action="{{ route('demo.switch-user', 'paciente') }}">@csrf<input type="hidden" name="destino" value="paciente.totem"><button type="submit">Identificar paciente para comprar</button></form>
                                @endif
                            </article>
                        @endforeach
                    </div>
                    <div class="schedule-empty" id="sinHorariosTotem">No hay horarios que coincidan con la búsqueda.</div>
                </div>
            </section>
        @else
            <section class="panel"><h2>Comprar bono</h2><p>Abre el flujo real de prueba para seleccionar hora, pagar y generar el QR.</p><form method="POST" action="{{ route('demo.switch-user', 'paciente') }}">@csrf<input type="hidden" name="destino" value="paciente.totem"><button class="primary" type="submit" @disabled(!$totem)>Comenzar compra de bono</button></form></section>
        @endif
        <div class="meta">
            <span><strong>Equipo:</strong> {{ $totem?->codigo ?? 'No provisionado' }}</span>
            <span><strong>Ubicación:</strong> {{ $totem?->ubicacion ?? 'Sin ubicación' }}</span>
            <span><strong>Último ping:</strong> {{ $totem?->ultimo_ping?->format('d-m-Y H:i:s') ?? 'Sin conexión' }}</span>
        </div>
        @if($soloCompra)
            <div class="next-screen">
                @if($agendaOnlineResultado)
                    <form method="POST" action="{{ route('totem.local.anexar-qr') }}">
                        @csrf
                        <input type="hidden" name="qr" value="{{ route('vouchers.usar', $agendaOnlineResultado->qr_token) }}">
                        <button type="submit">
                            Ir a Pantalla 2 · Autoatención
                            <small>Anexar el QR, reconocer la hora y avisar que el paciente llegó</small>
                        </button>
                    </form>
                @else
                    <a href="{{ route('totem.local', ['tab' => 'autoatencion']) }}">
                        Ir a Pantalla 2 · Autoatención
                        <small>Escanear QR o validar RUT para avisar que el paciente llegó</small>
                    </a>
                @endif
            </div>
        @endif
        @include('partials.demo_flow_guide', ['demoStep' => $tab === 'autoatencion' ? ($bonos->contains(fn ($bono) => optional($bono->agenda)->estado === 'paciente_en_espera') ? 4 : 3) : ($agendaOnlineResultado ? 2 : 1)])
        @if(auth()->user()?->rol === 'admin')
            <nav class="admin-only"><a href="{{ url('admin/totems/dashboard') }}">Administrar tótem</a></nav>
        @endif
    </section>
</main>
@if($soloCompra)
<script>
document.addEventListener('DOMContentLoaded',function(){
    const abrir=document.getElementById('pedirHoraTotem'),panel=document.getElementById('buscadorTotem'),texto=document.getElementById('buscarProfesionalTotem'),especialidad=document.getElementById('especialidadTotem'),fecha=document.getElementById('fechaTotem'),vacio=document.getElementById('sinHorariosTotem');
    const tarjetas=[...document.querySelectorAll('.schedule-card')];
    const normalizar=v=>(v||'').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/[.\-\s]/g,'');
    function filtrar(){let visibles=0;tarjetas.forEach(t=>{const okTexto=!texto.value||normalizar(t.dataset.profesional).includes(normalizar(texto.value));const okEsp=!especialidad.value||t.dataset.especialidad===especialidad.value;const okFecha=!fecha.value||t.dataset.fecha===fecha.value;const mostrar=okTexto&&okEsp&&okFecha;t.hidden=!mostrar;if(mostrar)visibles++;});vacio.style.display=visibles?'none':'block';}
    abrir?.addEventListener('click',()=>{panel.hidden=false;abrir.hidden=true;filtrar();texto.focus();});texto?.addEventListener('input',filtrar);especialidad?.addEventListener('change',filtrar);fecha?.addEventListener('change',filtrar);
    if(panel && !panel.hidden) filtrar();
});
</script>
@endif
</body>
</html>
