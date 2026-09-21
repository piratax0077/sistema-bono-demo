<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tótem Medichile · Sistema de Bonos Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite('resources/js/flatpickr.js')
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
    <style>.method-separator{display:flex;align-items:center;gap:12px;margin:22px 0;color:#71849b;font-weight:800}.method-separator:before,.method-separator:after{content:"";height:1px;flex:1;background:#d5e1f1}.qr-input{text-transform:none!important}.automatic-match{margin:18px 0;padding:16px 18px;border-radius:14px;background:#e7f9f9;color:#176d75;font-weight:800}.alternate-identification{margin-top:15px}.alternate-identification summary{cursor:pointer;color:#1848a1;font-weight:900;padding:10px 0}.next-screen form{width:100%;margin:0}.next-screen button{display:block;width:100%;border:0;padding:17px 22px;border-radius:15px;background:#155ba4;color:#fff;text-align:center;font-family:inherit;font-size:18px;font-weight:900;cursor:pointer;box-shadow:0 9px 22px rgba(21,91,164,.2)}.totem-header-row{width:100%;margin:0}.totem-header-row>.col-12{min-width:0}.totem-header-row .demo-switcher{width:100%;max-width:100%;margin:0}</style>
</head>
<body style="display:block;padding:0">
<div class="row g-0 totem-header-row">
    <div class="col-12 p-0">
        @include('partials.demo_user_switcher')
    </div>
</div>
<main class="shell" style="margin:24px auto">
    <header class="head">
        <div class="brand"><small>Medichile · SDI Salud Digital Integrada</small><h1 style="color: #fff;">Tótem de atención</h1></div>
        <div class="status">● {{ $totem ? 'ACTIVO' : 'SIN EQUIPO ACTIVO' }}</div>
    </header>
    <section class="body">
        @php($soloCompra = request()->routeIs('paciente.totem'))
        @php($tab = $soloCompra ? request('tab', 'reserva') : request('tab', 'comprar'))
        <div class="intro">
            <h2>{{ $soloCompra ? 'Paciente Tótem' : 'Autoatención Medichile' }}</h2>
            <p>{{ $soloCompra ? 'Reserva tu hora o registra tu aviso de llegada.' : 'Compra tu bono o avisa tu llegada sin pasar por recepción.' }}</p>
        </div>
        @if($soloCompra)
            <nav class="tabs" aria-label="Opciones del paciente tótem">
                <a class="tab {{ $tab === 'reserva' ? 'active' : '' }}" href="{{ route('paciente.totem', ['tab' => 'reserva']) }}">Reserva de hora</a>
                <a class="tab {{ $tab === 'autoatencion' ? 'active' : '' }}" href="{{ route('paciente.totem', ['tab' => 'autoatencion']) }}">Aviso de llegada</a>
            </nav>
        @else
            <nav class="tabs" aria-label="Opciones del tótem">
                <a class="tab {{ $tab === 'comprar' ? 'active' : '' }}" href="{{ route('totem.local', ['tab' => 'comprar']) }}">🎫 Comprar bono</a>
                <a class="tab {{ $tab === 'autoatencion' ? 'active' : '' }}" href="{{ route('totem.local', ['tab' => 'autoatencion']) }}">✓ Avisar que llegué</a>
            </nav>
        @endif

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
                    <summary>{{ $identificacionAutomatica ? 'Buscar otra hora por RUT' : 'Identificar reserva por RUT' }}</summary>
                <form id="buscarReservaRutForm" method="POST" action="{{ route('totem.local.buscar-hora') }}" autocomplete="off" data-totem-identification-form>
                    @csrf
                    @if($soloCompra)<input type="hidden" name="origen" value="paciente-totem">@endif
                    <div class="field">
                        <input id="buscarReservaRut" name="rut" value="" placeholder="Ej.: 6.187.674-K" autocomplete="off" data-rut-input required>
                        <button type="submit" @disabled(!$totem)>Buscar mi hora</button>
                    </div>
                    @error('rut')<div class="error-text">{{ $message }}</div>@enderror
                </form>
                <div class="hint">Seguridad: sólo se mostrarán reservas vigentes después de validar el RUT del paciente.</div>
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
                                @if(optional($bono->agenda)->estado !== 'paciente_en_espera')
                                    <form method="POST" action="{{ route('totem.local.confirmar-llegada', $bono) }}">
                                        @csrf
                                        @if($soloCompra)<input type="hidden" name="origen" value="paciente-totem">@endif
                                        <button type="submit">Confirmar mi llegada</button>
                                    </form>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @elseif($soloCompra)
            <section class="panel">
                @if($agendaOnlineResultado)
                    <div class="qr-result"><h3>✓ Hora reservada y bono generado</h3><p><strong>{{ $agendaOnlineResultado->codigo }}</strong> · {{ $agendaOnlineResultado->prestador_nombre }} · {{ optional($agendaOnlineResultado->agenda?->fecha_hora_confirmada)->format('d-m-Y H:i') }}</p><a href="{{ route('vouchers.qr', $agendaOnlineResultado->qr_token) }}">Ver QR</a><a href="{{ route('paciente.totem', ['tab' => 'autoatencion']) }}">Ir a autoatención</a></div>
                @endif
                <h3 style="margin:0 0 6px">Reservar hora real en Med-SDI</h3>
                <p style="color:#60736f;margin:0 0 14px">Busca especialidad, profesional y hora directamente contra la agenda real de Med-SDI (igual que en el escritorio de paciente).</p>
                @if(auth()->user()?->rol === 'cliente')
                    <button class="primary" type="button" id="abrirReservaMedsdiTotem" @disabled(!$totem)>Reservar hora Med-SDI</button>
                @else
                    <form method="POST" action="{{ route('demo.switch-user', 'paciente') }}">@csrf<input type="hidden" name="destino" value="paciente.totem"><button class="primary" type="submit" @disabled(!$totem)>Identificar paciente para reservar en Med-SDI</button></form>
                @endif
            </section>
        @else
            <section class="panel"><h2>Comprar bono</h2><p>Abre el flujo real de prueba para seleccionar hora, pagar y generar el QR.</p><form method="POST" action="{{ route('demo.switch-user', 'paciente') }}">@csrf<input type="hidden" name="destino" value="paciente.totem"><button class="primary" type="submit" @disabled(!$totem)>Comenzar compra de bono</button></form></section>
        @endif
        <div class="meta">
            <span><strong>Equipo:</strong> {{ $totem?->codigo ?? 'No provisionado' }}</span>
            <span><strong>Ubicación:</strong> {{ $totem?->ubicacion ?? 'Sin ubicación' }}</span>
            <span><strong>Último ping:</strong> {{ $totem?->ultimo_ping?->format('d-m-Y H:i:s') ?? 'Sin conexión' }}</span>
        </div>
        @include('partials.demo_flow_guide', ['demoStep' => $tab === 'autoatencion' ? ($bonos->contains(fn ($bono) => optional($bono->agenda)->estado === 'paciente_en_espera') ? 4 : 3) : ($agendaOnlineResultado ? 2 : 1)])
        @if(auth()->user()?->rol === 'admin')
            <nav class="admin-only"><a href="{{ url('admin/totems/dashboard') }}">Administrar tótem</a></nav>
        @endif
    </section>
</main>

@if($soloCompra)
<style>
.modal.demo-modal-visible{display:block;background:rgba(5,33,30,.48);overflow-y:auto}
body.demo-modal-open{overflow:hidden}
</style>

<div class="modal fade" id="modalReservaMedsdiTotem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"><div class="modal-content border-0" style="border-radius:22px;overflow:hidden">
        <div class="modal-header text-white" style="background:#1848a1">
            <div><div class="eyebrow text-white-50">Conectado a Med-SDI</div><h2 class="h5 modal-title">Buscar profesional y reservar hora</h2></div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
            <div id="medsdiEstadoServicio" class="alert alert-warning d-none"></div>

            <div class="border rounded-4 p-3 mb-3 bg-light">
                <div class="eyebrow mb-2">1 · Beneficiario</div>
                <label class="form-label fw-bold" for="medsdiRutBuscar">RUT del paciente</label>
                <div class="input-group">
                    <input type="text" id="medsdiRutBuscar" class="form-control" autocomplete="off" placeholder="Ej.: 17.174.188-2" data-rut-input>
                    <button type="button" id="medsdiValidarPaciente" class="btn btn-primary">Validar paciente</button>
                </div>
                <div id="medsdiPacienteResultado" class="small mt-2 d-none"></div>
                <div id="medsdiResponsableWrap" class="mt-3 d-none">
                    <label for="medsdiResponsable" class="form-label small fw-bold">Titular responsable</label>
                    <select id="medsdiResponsable" class="form-select"></select>
                </div>
            </div>

            <div class="border rounded-4 p-3 mb-3">
                <div id="medsdiPrestacionForm">
                    <div class="eyebrow mb-2">2 · Prestación FONASA</div>
                    <label class="form-label small" for="medsdiPrestacionBuscar">Busca por nombre o código</label>
                    <div class="input-group">
                        <input type="search" id="medsdiPrestacionBuscar" class="form-control" autocomplete="off" placeholder="Ej.: consulta médica o 0101001" disabled>
                        <button type="button" id="medsdiPrestacionBuscarBtn" class="btn btn-info text-white" disabled>Buscar</button>
                    </div>
                    <div id="medsdiPrestacionResultados" class="list-group mt-2 d-none" style="max-height:260px;overflow-y:auto"></div>
                </div>
                <div id="medsdiPrestacionSeleccionada" class="alert alert-info mt-3 mb-0 d-none d-flex justify-content-between align-items-center gap-2">
                    <span id="medsdiPrestacionSeleccionadaTexto"></span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="medsdiCambiarPrestacion">Cambiar</button>
                </div>
            </div>

            <div id="medsdiPasoBusqueda" class="d-none border rounded-4 p-3">
                <div class="eyebrow mb-2">3 · Ubicación y profesional</div>
                <div id="medsdiUbicacionBuscadorWrap">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small">Región</label>
                            <select id="medsdiRegion" class="form-select">
                                <option value="">Centro INSI (predeterminado)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Ciudad</label>
                            <select id="medsdiCiudad" class="form-select" disabled>
                                <option value="">Primero seleccione una región</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-4"><label class="form-label small">Especialidad</label><select id="medsdiEspecialidad" class="form-select"><option value="">Todas</option></select></div>
                        <div class="col-md-4"><label class="form-label small">Tipo</label><select id="medsdiTipoEspecialidad" class="form-select" disabled><option value="">Todos</option></select></div>
                        <div class="col-md-4"><label class="form-label small">Subtipo</label><select id="medsdiSubTipoEspecialidad" class="form-select" disabled><option value="">Todos</option></select></div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" id="medsdiNombreProfesional" class="form-control" placeholder="Buscar por nombre del profesional (opcional)">
                        <button type="button" id="medsdiBuscarBtn" class="btn btn-primary">Buscar profesionales</button>
                    </div>
                    <div id="medsdiResultados" class="row g-2"></div>
                </div>
            </div>

            <div id="medsdiPasoCotizacion" class="d-none mt-4 pt-3 border-top">
                <div class="eyebrow mb-2">4 · Cotización</div>
                <div id="medsdiCotizacion" class="alert alert-info mb-0">Calculando convenio FONASA...</div>
            </div>

            <div id="medsdiPasoHorario" class="d-none mt-4 pt-3 border-top">
                <div class="eyebrow mb-2">5 · Fecha y hora</div>
                <div id="medsdiProfesionalElegido" class="alert alert-info d-flex justify-content-between align-items-center gap-2">
                    <span id="medsdiProfesionalElegidoTexto"></span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="medsdiCambiarProfesional">Cambiar</button>
                </div>
                <div id="medsdiDiasAtencion" class="small fw-semibold text-primary mb-2">Consultando días de atención...</div>
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-md-4"><label class="form-label small">Fecha</label><input type="text" id="medsdiFecha" class="form-control" placeholder="Seleccione un día disponible" disabled></div>
                    <div class="col-md-3"><button type="button" id="medsdiVerHorasBtn" class="btn btn-outline-primary w-100">Ver horas disponibles</button></div>
                </div>
                <div id="medsdiHoras" class="d-flex flex-wrap gap-2"></div>
            </div>

            <form id="medsdiFormConfirmar" method="POST" action="{{ route('totem.reserva.agendar') }}" class="d-none mt-4 pt-3 border-top">
                @csrf
                <input type="hidden" name="id_profesional" id="medsdiInputProfesionalId">
                <input type="hidden" name="id_especialidad" id="medsdiInputEspecialidadId">
                <input type="hidden" name="nombre_profesional" id="medsdiInputProfesionalNombre">
                <input type="hidden" name="especialidad" id="medsdiInputEspecialidad">
                <input type="hidden" name="id_lugar" id="medsdiInputLugarId">
                <input type="hidden" name="lugar_nombre" id="medsdiInputLugarNombre">
                <input type="hidden" name="direccion" id="medsdiInputDireccion">
                <input type="hidden" name="fecha_hora" id="medsdiInputFechaHora">
                <input type="hidden" name="id_prestacion" id="medsdiInputPrestacionId">
                <input type="hidden" name="origen_prestacion" id="medsdiInputPrestacionOrigen">
                <input type="hidden" name="prestacion_codigo" id="medsdiInputPrestacionCodigo">
                <input type="hidden" name="prestacion_nombre" id="medsdiInputPrestacionNombre">
                <div class="eyebrow mb-2">6 · Confirmación</div>
                <div id="medsdiResumenReserva" class="alert alert-success d-flex justify-content-between align-items-center gap-2">
                    <span id="medsdiResumenReservaTexto"></span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="medsdiCambiarHora">Cambiar hora</button>
                </div>
                <div class="mb-3">
                    <label class="form-label">Paciente validado</label>
                    <input type="text" name="rut" id="medsdiRutPaciente" class="form-control" value="" readonly>
                    <input type="hidden" name="titular_rut" id="medsdiRutTitular">
                    <div class="form-text" id="medsdiPacienteConfirmacion"></div>
                </div>
                <button type="submit" class="btn btn-success w-100">Confirmar reserva y generar bono</button>
            </form>
        </div>
    </div></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function mostrarModalDemo(modal) {
        if (!modal) return;
        document.querySelectorAll('.modal.demo-modal-visible').forEach(item => ocultarModalDemo(item));
        modal.classList.add('show', 'demo-modal-visible');
        modal.style.display = 'block';
        modal.removeAttribute('aria-hidden');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('role', 'dialog');
        document.body.classList.add('demo-modal-open');
    }
    function ocultarModalDemo(modal) {
        if (!modal) return;
        modal.classList.remove('show', 'demo-modal-visible');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');
        modal.removeAttribute('role');
        if (!document.querySelector('.modal.demo-modal-visible')) document.body.classList.remove('demo-modal-open');
    }
    document.getElementById('abrirReservaMedsdiTotem')?.addEventListener('click', () => { mostrarModalDemo(document.getElementById('modalReservaMedsdiTotem')); inicializarReservaMedsdi(); });
    document.querySelectorAll('#modalReservaMedsdiTotem [data-bs-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', () => ocultarModalDemo(button.closest('.modal')));
    });

    // --- Reserva de hora vía API real Med-SDI (igual que escritorio de paciente) ---
    let medsdiInicializado = false;
    let medsdiSeleccion = null;
    let medsdiPrestacion = null;
    let medsdiCotizacionActual = null;
    let medsdiPrestacionTimer = null;
    let medsdiCalendario = null;
    let medsdiPaciente = null;
    const medsdiEstado = document.getElementById('medsdiEstadoServicio');
    const medsdiRutBuscar = document.getElementById('medsdiRutBuscar');
    const medsdiPacienteResultado = document.getElementById('medsdiPacienteResultado');
    const medsdiResponsableWrap = document.getElementById('medsdiResponsableWrap');
    const medsdiResponsable = document.getElementById('medsdiResponsable');
    const medsdiRegion = document.getElementById('medsdiRegion');
    const medsdiCiudad = document.getElementById('medsdiCiudad');
    const medsdiEspecialidad = document.getElementById('medsdiEspecialidad');
    const medsdiTipoEspecialidad = document.getElementById('medsdiTipoEspecialidad');
    const medsdiSubTipoEspecialidad = document.getElementById('medsdiSubTipoEspecialidad');
    const medsdiResultados = document.getElementById('medsdiResultados');
    const medsdiPasoHorario = document.getElementById('medsdiPasoHorario');
    const medsdiPasoBusqueda = document.getElementById('medsdiPasoBusqueda');
    const medsdiPasoCotizacion = document.getElementById('medsdiPasoCotizacion');
    const medsdiCotizacion = document.getElementById('medsdiCotizacion');
    const medsdiPrestacionBuscar = document.getElementById('medsdiPrestacionBuscar');
    const medsdiPrestacionResultados = document.getElementById('medsdiPrestacionResultados');
    const medsdiPrestacionSeleccionada = document.getElementById('medsdiPrestacionSeleccionada');
    const medsdiHoras = document.getElementById('medsdiHoras');
    const medsdiFormConfirmar = document.getElementById('medsdiFormConfirmar');
    const medsdiFecha = document.getElementById('medsdiFecha');
    const medsdiDiasAtencion = document.getElementById('medsdiDiasAtencion');

    function medsdiMostrarAviso(mensaje) {
        medsdiEstado.textContent = mensaje;
        medsdiEstado.classList.remove('d-none');
    }

    function medsdiBloquearFlujoPaciente() {
        medsdiPaciente = null;
        medsdiPrestacion = null;
        medsdiPrestacionBuscar.value = '';
        medsdiPrestacionBuscar.disabled = true;
        document.getElementById('medsdiPrestacionBuscarBtn').disabled = true;
        document.getElementById('medsdiPrestacionResultados').classList.add('d-none');
        document.getElementById('medsdiPrestacionSeleccionada').classList.add('d-none');
        document.getElementById('medsdiPasoBusqueda').classList.add('d-none');
        medsdiLimpiarSeleccion();
    }

    document.getElementById('medsdiValidarPaciente')?.addEventListener('click', async () => {
        const rut = medsdiRutBuscar.value.trim();
        medsdiBloquearFlujoPaciente();
        medsdiResponsableWrap.classList.add('d-none');
        medsdiPacienteResultado.className = 'small mt-2 text-muted';
        medsdiPacienteResultado.textContent = 'Validando paciente en Med-SDI...';
        if (!rut) {
            medsdiPacienteResultado.className = 'small mt-2 text-danger';
            medsdiPacienteResultado.textContent = 'Ingrese el RUT del paciente.';
            return;
        }

        const data = await medsdiFetchJson(`{{ route('totem.reserva.paciente') }}?rut=${encodeURIComponent(rut)}`);
        if (!data.ok || !data.paciente) {
            medsdiPacienteResultado.className = 'small mt-2 text-danger';
            medsdiPacienteResultado.textContent = data.mensaje || data.message || 'Paciente no encontrado.';
            return;
        }

        const paciente = data.paciente;
        const esDependiente = paciente.tipo === 'dependiente' || Boolean(paciente.es_dependiente);
        const responsables = Array.isArray(paciente.responsables) ? paciente.responsables : [];
        const titularInicial = esDependiente
            ? (responsables.length === 1 ? responsables[0] : null)
            : (paciente.titular || null);
        if (esDependiente && responsables.length === 0) {
            medsdiPacienteResultado.className = 'small mt-2 text-danger';
            medsdiPacienteResultado.textContent = 'El paciente figura como dependiente, pero no tiene un titular responsable vigente.';
            return;
        }

        medsdiPaciente = {...paciente, titular: titularInicial};
        medsdiRutBuscar.value = paciente.rut || rut;
        medsdiRutBuscar.dispatchEvent(new Event('input', {bubbles: true}));
        document.getElementById('medsdiRutPaciente').value = paciente.rut || rut;
        document.getElementById('medsdiRutTitular').value = titularInicial?.rut || (esDependiente ? '' : (paciente.rut || rut));
        const detalle = esDependiente
            ? `Dependiente (${paciente.parentesco || 'Carga'}) · Responsable: ${titularInicial?.nombre_completo || 'identificado'}`
            : 'Titular';
        medsdiPacienteResultado.className = 'small mt-2 text-success fw-semibold';
        medsdiPacienteResultado.textContent = `✓ ${paciente.nombre_completo} · RUT ${paciente.rut} · ${detalle}`;
        document.getElementById('medsdiPacienteConfirmacion').textContent = `${paciente.nombre_completo} · ${detalle}`;

        if (esDependiente && responsables.length > 1) {
            medsdiResponsable.innerHTML = '<option value="">Seleccione al titular responsable</option>';
            responsables.forEach(responsable => medsdiResponsable.add(new Option(
                `${responsable.nombre_completo || 'Titular'} · ${responsable.rut}`,
                responsable.rut
            )));
            medsdiResponsableWrap.classList.remove('d-none');
        }
        const pacienteListo = !esDependiente || Boolean(titularInicial);
        medsdiPrestacionBuscar.disabled = !pacienteListo;
        document.getElementById('medsdiPrestacionBuscarBtn').disabled = !pacienteListo;
        if (pacienteListo) medsdiPrestacionBuscar.focus();
    });

    medsdiResponsable?.addEventListener('change', () => {
        const responsable = (medsdiPaciente?.responsables || []).find(item => item.rut === medsdiResponsable.value);
        if (!responsable) return;
        medsdiPaciente.titular = responsable;
        document.getElementById('medsdiRutTitular').value = responsable.rut;
        document.getElementById('medsdiPacienteConfirmacion').textContent = `${medsdiPaciente.nombre_completo} · Dependiente · Responsable: ${responsable.nombre_completo}`;
        medsdiPrestacionBuscar.disabled = false;
        document.getElementById('medsdiPrestacionBuscarBtn').disabled = false;
        medsdiPrestacionBuscar.focus();
    });

    medsdiRutBuscar?.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            document.getElementById('medsdiValidarPaciente').click();
        }
    });

    function medsdiLimpiarSeleccion() {
        medsdiSeleccion = null;
        medsdiCotizacionActual = null;
        document.getElementById('medsdiUbicacionBuscadorWrap')?.classList.remove('d-none');
        medsdiPasoCotizacion.classList.add('d-none');
        medsdiPasoHorario.classList.add('d-none');
        medsdiHoras.innerHTML = '';
        medsdiFormConfirmar.classList.add('d-none');
        medsdiCalendario?.destroy();
        medsdiCalendario = null;
        medsdiFecha.value = '';
        medsdiFecha.disabled = true;
        medsdiDiasAtencion.textContent = '';
    }

    const medsdiNombresDias = ['', 'LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO', 'DOMINGO'];

    async function medsdiCargarDiasLaborales() {
        medsdiDiasAtencion.textContent = 'Consultando días de atención...';
        const params = new URLSearchParams({
            id_profesional: medsdiSeleccion.idProfesional,
            id_lugar: medsdiSeleccion.idLugar,
        });
        const resp = await medsdiFetchJson(`{{ route('totem.reserva.dias_laborales') }}?${params.toString()}`);
        const dias = String(resp.registros?.horario_agenda_laboral || '')
            .split(',').map(Number).filter(dia => dia >= 1 && dia <= 7);

        if (!resp.ok || !dias.length) {
            medsdiDiasAtencion.textContent = 'El profesional no tiene días de atención informados.';
            medsdiFecha.disabled = true;
            return;
        }

        medsdiDiasAtencion.textContent = `Atiende los días: ${dias.map(dia => medsdiNombresDias[dia]).join(' · ')}`;
        medsdiFecha.disabled = false;
        medsdiCalendario = flatpickr(medsdiFecha, {
            disableMobile: true,
            minDate: 'today',
            maxDate: new Date().fp_incr(60),
            dateFormat: 'Y-m-d',
            disable: [date => !dias.includes(date.getDay() === 0 ? 7 : date.getDay())],
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                },
                months: {
                    shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                },
            },
            onChange: () => document.getElementById('medsdiVerHorasBtn').click(),
        });
    }

    async function medsdiFetchJson(url) {
        const respuesta = await fetch(url, { headers: { 'Accept': 'application/json' } });
        return respuesta.json();
    }

    async function medsdiPostJson(url, data) {
        const respuesta = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(data),
        });
        const payload = await respuesta.json();
        if (!respuesta.ok && !payload.mensaje) payload.mensaje = 'La solicitud no pudo ser procesada.';
        return payload;
    }

    async function medsdiBuscarPrestaciones() {
        const buscar = medsdiPrestacionBuscar.value.trim();
        if (buscar.length < 2) {
            medsdiPrestacionResultados.classList.add('d-none');
            return;
        }
        medsdiPrestacionResultados.innerHTML = '<div class="list-group-item text-muted">Buscando prestaciones...</div>';
        medsdiPrestacionResultados.classList.remove('d-none');
        const params = new URLSearchParams({buscar});
        const resp = await medsdiFetchJson(`{{ route('totem.reserva.prestaciones') }}?${params.toString()}`);
        medsdiPrestacionResultados.innerHTML = '';
        if (!resp.ok || !(resp.registros || []).length) {
            const vacio = document.createElement('div');
            vacio.className = 'list-group-item text-muted';
            vacio.textContent = resp.mensaje || 'No se encontraron prestaciones.';
            medsdiPrestacionResultados.appendChild(vacio);
            return;
        }
        resp.registros.forEach(prestacion => {
            const boton = document.createElement('button');
            boton.type = 'button';
            boton.className = 'list-group-item list-group-item-action text-start';
            boton.dataset.prestacion = JSON.stringify(prestacion);
            boton.innerHTML = `<strong>${prestacion.codigo}</strong> · ${prestacion.nombre}`;
            medsdiPrestacionResultados.appendChild(boton);
        });
    }

    document.getElementById('medsdiPrestacionBuscarBtn')?.addEventListener('click', medsdiBuscarPrestaciones);
    medsdiPrestacionBuscar?.addEventListener('input', () => {
        clearTimeout(medsdiPrestacionTimer);
        medsdiPrestacionTimer = setTimeout(medsdiBuscarPrestaciones, 300);
    });
    medsdiPrestacionResultados?.addEventListener('click', event => {
        const boton = event.target.closest('button[data-prestacion]');
        if (!boton) return;
        medsdiPrestacion = JSON.parse(boton.dataset.prestacion);
        medsdiPrestacionResultados.classList.add('d-none');
        document.getElementById('medsdiPrestacionSeleccionadaTexto').textContent = `${medsdiPrestacion.codigo} · ${medsdiPrestacion.nombre}`;
        medsdiPrestacionSeleccionada.classList.remove('d-none');
        document.getElementById('medsdiPrestacionForm').classList.add('d-none');
        medsdiPasoBusqueda.classList.remove('d-none');
        medsdiLimpiarSeleccion();
        medsdiResultados.innerHTML = '';
        medsdiPasoBusqueda.scrollIntoView({behavior: 'smooth', block: 'nearest'});
    });

    document.getElementById('medsdiCambiarPrestacion')?.addEventListener('click', () => {
        document.getElementById('medsdiPrestacionForm').classList.remove('d-none');
        medsdiPrestacionSeleccionada.classList.add('d-none');
        medsdiPasoBusqueda.classList.add('d-none');
        medsdiPrestacion = null;
        medsdiResultados.innerHTML = '';
        medsdiLimpiarSeleccion();
    });

    function medsdiLlenarSelect(select, registros, campoValor, campoTexto, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        registros.forEach(registro => {
            select.add(new Option(registro[campoTexto], registro[campoValor]));
        });
        select.disabled = registros.length === 0;
    }

    async function inicializarReservaMedsdi() {
        if (medsdiInicializado) return;
        medsdiInicializado = true;

        const [regiones, especialidades] = await Promise.all([
            medsdiFetchJson('{{ route('totem.reserva.regiones') }}'),
            medsdiFetchJson('{{ route('totem.reserva.especialidades') }}'),
        ]);
        if (regiones.ok) {
            medsdiLlenarSelect(medsdiRegion, regiones.registros || [], 'id', 'nombre', 'Centro INSI (predeterminado)');
            medsdiRegion.disabled = false;
        } else {
            medsdiMostrarAviso(regiones.mensaje || 'No fue posible cargar las regiones de Med-SDI.');
        }
        if (!especialidades.disponible) {
            medsdiMostrarAviso(especialidades.mensaje || 'Servicio Med-SDI no disponible en este momento.');
        }
        medsdiLlenarSelect(medsdiEspecialidad, especialidades.registros || [], 'id', 'nombre', 'Todas');
        medsdiEspecialidad.disabled = false;
    }

    medsdiRegion?.addEventListener('change', async () => {
        medsdiLimpiarSeleccion();
        medsdiResultados.innerHTML = '';
        medsdiLlenarSelect(medsdiCiudad, [], 'id', 'nombre', medsdiRegion.value ? 'Todas las ciudades' : 'Primero seleccione una región');
        if (!medsdiRegion.value) return;

        const resp = await medsdiFetchJson(`{{ route('totem.reserva.ciudades') }}?id_region=${medsdiRegion.value}`);
        medsdiLlenarSelect(medsdiCiudad, resp.registros || [], 'id', 'nombre', 'Todas las ciudades');
        medsdiCiudad.disabled = !resp.ok;
        if (!resp.ok) medsdiMostrarAviso(resp.mensaje || 'No fue posible cargar las ciudades de la región.');
    });

    medsdiCiudad?.addEventListener('change', () => {
        medsdiLimpiarSeleccion();
        medsdiResultados.innerHTML = '';
    });

    medsdiEspecialidad?.addEventListener('change', async () => {
        medsdiLimpiarSeleccion();
        medsdiResultados.innerHTML = '';
        medsdiLlenarSelect(medsdiTipoEspecialidad, [], 'id', 'nombre', 'Todos');
        medsdiLlenarSelect(medsdiSubTipoEspecialidad, [], 'id', 'nombre', 'Todos');
        if (!medsdiEspecialidad.value) return;
        const resp = await medsdiFetchJson(`{{ route('totem.reserva.tipo_especialidades') }}?id_especialidad=${medsdiEspecialidad.value}`);
        medsdiLlenarSelect(medsdiTipoEspecialidad, resp.registros || [], 'id', 'nombre', 'Todos');
    });

    medsdiTipoEspecialidad?.addEventListener('change', async () => {
        medsdiLimpiarSeleccion();
        medsdiResultados.innerHTML = '';
        medsdiLlenarSelect(medsdiSubTipoEspecialidad, [], 'id', 'nombre', 'Todos');
        if (!medsdiTipoEspecialidad.value) return;
        const resp = await medsdiFetchJson(`{{ route('totem.reserva.sub_tipo_especialidades') }}?id_tipo_especialidad=${medsdiTipoEspecialidad.value}`);
        medsdiLlenarSelect(medsdiSubTipoEspecialidad, resp.registros || [], 'id', 'nombre', 'Todos');
    });

    document.getElementById('medsdiBuscarBtn')?.addEventListener('click', async () => {
        medsdiLimpiarSeleccion();
        medsdiEstado.classList.add('d-none');
        medsdiResultados.innerHTML = '<div class="col-12 text-muted">Buscando en Med-SDI...</div>';
        const params = new URLSearchParams();
        if (medsdiRegion.value) params.set('id_region', medsdiRegion.value);
        if (medsdiCiudad.value) params.set('id_ciudad', medsdiCiudad.value);
        if (medsdiEspecialidad.value) params.set('id_especialidad', medsdiEspecialidad.value);
        if (medsdiTipoEspecialidad.value) params.set('id_tipo_especialidad', medsdiTipoEspecialidad.value);
        if (medsdiSubTipoEspecialidad.value) params.set('id_sub_tipo_especialidad', medsdiSubTipoEspecialidad.value);
        if (document.getElementById('medsdiNombreProfesional').value.trim()) params.set('nombre_profesional', document.getElementById('medsdiNombreProfesional').value.trim());

        const resp = await medsdiFetchJson(`{{ route('totem.reserva.profesionales') }}?${params.toString()}`);
        if (!resp.disponible) { medsdiResultados.innerHTML = ''; medsdiMostrarAviso(resp.mensaje); return; }
        if (!resp.ok || !(resp.registros || []).length) { medsdiResultados.innerHTML = `<div class="col-12 alert alert-warning mb-0">${resp.mensaje || 'No se encontraron profesionales para los filtros indicados.'}</div>`; return; }

        medsdiResultados.innerHTML = '';
        resp.registros.forEach(prof => {
            const nombreCompleto = `${prof.nombre} ${prof.apellido_uno || ''} ${prof.apellido_dos || ''}`.trim();
            const especialidadProfesional = prof.nombre_especialidad || medsdiEspecialidad.options[medsdiEspecialidad.selectedIndex]?.text || '';
            const lugares = (prof.lugares_atencion || []).map(lugar => `
                <button type="button" class="btn btn-outline-success btn-sm me-1 mb-1 medsdi-elegir-lugar"
                    data-id-profesional="${prof.id}" data-nombre-profesional="${nombreCompleto}"
                    data-especialidad="${especialidadProfesional}" data-id-lugar="${lugar.id}"
                    data-lugar-nombre="${lugar.nombre}">${lugar.nombre}</button>`).join('');
            const div = document.createElement('div');
            div.className = 'col-md-6';
            div.innerHTML = `<div class="card border-0 shadow-sm h-100"><div class="card-body p-3">
                <strong>${nombreCompleto}</strong><br>
                <span class="text-muted small">${especialidadProfesional}${prof.nombre_sub_tipo_especialidad ? ' · '+prof.nombre_sub_tipo_especialidad : ''}</span>
                <div class="mt-2">${lugares || '<span class="text-muted small">Sin lugares de atención disponibles.</span>'}</div>
            </div></div>`;
            medsdiResultados.appendChild(div);
        });
    });

    medsdiResultados?.addEventListener('click', async event => {
        const btn = event.target.closest('.medsdi-elegir-lugar');
        if (!btn) return;
        medsdiSeleccion = {
            idProfesional: btn.dataset.idProfesional, nombreProfesional: btn.dataset.nombreProfesional,
            idEspecialidad: medsdiEspecialidad.value, especialidad: btn.dataset.especialidad,
            idLugar: btn.dataset.idLugar, lugarNombre: btn.dataset.lugarNombre,
        };
        document.getElementById('medsdiUbicacionBuscadorWrap').classList.add('d-none');
        document.getElementById('medsdiProfesionalElegidoTexto').textContent = `${medsdiSeleccion.nombreProfesional} · ${medsdiSeleccion.especialidad} · ${medsdiSeleccion.lugarNombre}`;
        medsdiHoras.innerHTML = '';
        medsdiFormConfirmar.classList.add('d-none');
        medsdiPasoCotizacion.classList.remove('d-none');
        medsdiPasoHorario.classList.add('d-none');
        medsdiCotizacion.className = 'alert alert-info mb-0';
        medsdiCotizacion.textContent = 'Calculando convenio FONASA...';
        const respCotizacion = await medsdiPostJson('{{ route('totem.reserva.cotizar') }}', {
            id_profesional: medsdiSeleccion.idProfesional,
            id_lugar_atencion: medsdiSeleccion.idLugar,
            id_prestacion: medsdiPrestacion.id,
            origen_prestacion: medsdiPrestacion.origen,
        });
        if (!respCotizacion.ok) {
            medsdiCotizacion.className = 'alert alert-warning mb-0';
            medsdiCotizacion.textContent = respCotizacion.mensaje || 'No fue posible cotizar esta prestación.';
            return;
        }
        medsdiCotizacionActual = respCotizacion.cotizacion;
        const moneda = valor => new Intl.NumberFormat('es-CL', {style:'currency', currency:'CLP', maximumFractionDigits:0}).format(valor || 0);
        medsdiCotizacion.className = 'alert alert-success mb-0';
        medsdiCotizacion.textContent = `Valor ${moneda(medsdiCotizacionActual.valor)} · Bonificación ${moneda(medsdiCotizacionActual.bonificacion)} · Copago ${moneda(medsdiCotizacionActual.copago)} · Simulación FONASA`;
        medsdiPasoHorario.classList.remove('d-none');
        medsdiCargarDiasLaborales().catch(() => {
            medsdiDiasAtencion.textContent = 'No fue posible consultar los días de atención.';
            medsdiFecha.disabled = true;
        });
        medsdiPasoCotizacion.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    document.getElementById('medsdiVerHorasBtn')?.addEventListener('click', async () => {
        if (!medsdiSeleccion || !medsdiFecha.value) return;
        medsdiHoras.innerHTML = '<span class="text-muted">Consultando horas disponibles...</span>';
        const params = new URLSearchParams({
            id_profesional: medsdiSeleccion.idProfesional, id_lugar: medsdiSeleccion.idLugar,
            fecha: medsdiFecha.value,
        });
        const resp = await medsdiFetchJson(`{{ route('totem.reserva.horas_disponibles') }}?${params.toString()}`);
        if (!resp.disponible) { medsdiHoras.innerHTML = ''; medsdiMostrarAviso(resp.mensaje); return; }
        if (!resp.ok || !(resp.registros || []).length) { medsdiHoras.innerHTML = '<span class="text-muted">Sin horas disponibles para esta fecha.</span>'; return; }

        medsdiHoras.innerHTML = '';
        resp.registros.forEach(bloque => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-primary btn-sm medsdi-elegir-hora';
            btn.dataset.fechaHora = bloque.fecha_hora;
            btn.textContent = bloque.hora;
            medsdiHoras.appendChild(btn);
        });
    });

    medsdiHoras?.addEventListener('click', event => {
        const btn = event.target.closest('.medsdi-elegir-hora');
        if (!btn || !medsdiSeleccion) return;
        document.getElementById('medsdiInputProfesionalId').value = medsdiSeleccion.idProfesional;
        document.getElementById('medsdiInputEspecialidadId').value = medsdiSeleccion.idEspecialidad;
        document.getElementById('medsdiInputProfesionalNombre').value = medsdiSeleccion.nombreProfesional;
        document.getElementById('medsdiInputEspecialidad').value = medsdiSeleccion.especialidad;
        document.getElementById('medsdiInputLugarId').value = medsdiSeleccion.idLugar;
        document.getElementById('medsdiInputLugarNombre').value = medsdiSeleccion.lugarNombre;
        document.getElementById('medsdiInputFechaHora').value = btn.dataset.fechaHora;
        document.getElementById('medsdiInputPrestacionId').value = medsdiPrestacion.id;
        document.getElementById('medsdiInputPrestacionOrigen').value = medsdiPrestacion.origen;
        document.getElementById('medsdiInputPrestacionCodigo').value = medsdiPrestacion.codigo;
        document.getElementById('medsdiInputPrestacionNombre').value = medsdiPrestacion.nombre;
        document.getElementById('medsdiResumenReservaTexto').textContent = `${medsdiPrestacion.codigo} · ${medsdiPrestacion.nombre} · ${medsdiSeleccion.nombreProfesional} · ${medsdiSeleccion.lugarNombre} · ${btn.dataset.fechaHora} · Copago ${new Intl.NumberFormat('es-CL', {style:'currency', currency:'CLP', maximumFractionDigits:0}).format(medsdiCotizacionActual?.copago || 0)}`;
        medsdiPasoCotizacion.classList.add('d-none');
        medsdiPasoHorario.classList.add('d-none');
        medsdiFormConfirmar.classList.remove('d-none');
        medsdiFormConfirmar.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    document.getElementById('medsdiCambiarProfesional')?.addEventListener('click', () => {
        medsdiLimpiarSeleccion();
    });

    document.getElementById('medsdiCambiarHora')?.addEventListener('click', () => {
        medsdiFormConfirmar.classList.add('d-none');
        medsdiPasoCotizacion.classList.remove('d-none');
        medsdiPasoHorario.classList.remove('d-none');
        medsdiPasoHorario.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    medsdiFormConfirmar?.addEventListener('submit', async event => {
        event.preventDefault();
        if (!medsdiPaciente) return medsdiMostrarAviso('Vuelva a validar el RUT del paciente.');
        const boton = medsdiFormConfirmar.querySelector('button[type="submit"]');
        boton.disabled = true;
        const payload = Object.fromEntries(new FormData(medsdiFormConfirmar).entries());
        const data = await medsdiPostJson(medsdiFormConfirmar.action, payload);
        if (!data.ok) {
            boton.disabled = false;
            medsdiMostrarAviso(data.mensaje || 'No fue posible reservar la hora.');
            medsdiEstado.scrollIntoView({behavior: 'smooth', block: 'nearest'});
            return;
        }
        window.location.assign(data.redirect);
    });
});
</script>
@endif

@if($tab === 'autoatencion')
<script src="{{ asset('js/plugins/sweetalert.min.js') }}"></script>
<script>
window.addEventListener('pageshow', function () {
    document.querySelectorAll('[data-totem-identification-form]').forEach(function (form) {
        form.reset();
        form.querySelectorAll('input:not([type="hidden"])').forEach(function (input) {
            input.value = '';
        });
    });
});
</script>
@endif
@include('partials.rut_input_script')
@include('partials.demo_footer')
</body>
</html>
