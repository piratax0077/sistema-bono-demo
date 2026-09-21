@extends('layouts.app')

@section('ocultar-navegacion-app', true)

@vite('resources/js/flatpickr.js')

@section('content')
   <style>
        body { background:#edf4ff; color:#1f2d3d; }
        .shell { max-width:1180px; margin:0 auto; padding:32px 18px; }
        .card { border:1px solid #d9e4f3; border-radius:22px; box-shadow:0 18px 42px rgba(24,72,161,.09); }
        .eyebrow { color:#1848a1; font-size:.78rem; letter-spacing:.16em; text-transform:uppercase; font-weight:800; }
        .metric strong { display:block; font-size:2rem; color:#1848a1; }
        .btn { border-radius:12px; font-weight:700; }
        .form-control, .form-select { border-radius:12px; border-color:#c9d8ef; }
        .table td { vertical-align:middle; }
        .secure-note { background:#effafa; border:1px solid #bce5e5; border-radius:16px; padding:14px; }
        .qr-route { word-break:break-word; }
        .share-modal-backdrop { align-items:center; background:rgba(3,22,19,.66); display:flex; inset:0; justify-content:center; padding:16px 22px; position:fixed; z-index:11000; }
        .share-modal-backdrop[hidden] { display:none !important; }
        .share-modal { background:#fff; border-radius:28px; box-shadow:0 30px 90px rgba(0,0,0,.28); max-height:calc(100vh - 32px); overflow:auto; width:min(980px,100%); }
        .share-modal-header { align-items:flex-start; border-bottom:1px solid #dcebe8; display:flex; gap:18px; justify-content:space-between; padding:26px 28px 18px; }
        .share-close { align-items:center; background:#e8f4f1; border:0; border-radius:16px; color:#063d37; display:inline-flex; font-size:1.5rem; height:44px; justify-content:center; line-height:1; width:44px; }
        .share-modal-body { padding:24px 28px 28px; }
        .share-grid { display:grid; gap:14px; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); }
        .share-option-card { border:1px solid #d6e8e4; border-radius:18px; cursor:pointer; display:block; padding:16px; transition:border-color .18s ease,box-shadow .18s ease,transform .18s ease; }
        .share-option-card:hover { border-color:#009688; box-shadow:0 10px 28px rgba(0,121,107,.12); transform:translateY(-1px); }
        .share-option-card input { margin-right:8px; }
        .qr-share-preview { background:#f4fbfa; border:1px solid #d6e8e4; border-radius:22px; padding:14px; }
        .qr-share-preview img { border-radius:18px; display:block; margin:0 auto; max-width:260px; width:100%; }
        .share-channel-list { display:flex; flex-wrap:wrap; gap:10px; }
        .share-channel { align-items:center; border:1px solid #d6e8e4; border-radius:999px; cursor:pointer; display:inline-flex; gap:8px; padding:11px 15px; }
        .share-results { background:#f7fbfa; border:1px solid #dcebe8; border-radius:18px; padding:14px; }
        .share-result-item { align-items:center; border-bottom:1px solid #e2eeeb; display:flex; gap:12px; justify-content:space-between; padding:12px 0; }
        .share-result-item:last-child { border-bottom:0; }
        .share-muted { color:#5f7470; font-size:.88rem; }
        .section-title { color:#00796b; font-size:.8rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
        .bank-account-modal .modal-dialog{max-width:980px;margin:1rem auto}.bank-account-modal .modal-content{max-height:calc(100vh - 2rem);overflow:hidden}.bank-account-modal form{display:flex;flex-direction:column;min-height:0;overflow:hidden}.bank-account-modal .modal-body{overflow-y:auto}.bank-account-modal .modal-footer{background:#fff;flex-shrink:0}.bank-account-table{border:1px solid #d9e4f3;border-radius:14px;overflow:hidden}.bank-account-table .table{margin:0}.bank-account-table th{background:#f3f7fd;color:#52647b;font-size:.73rem;letter-spacing:.06em;text-transform:uppercase}.bank-account-table td{font-size:.9rem}.bank-account-number{align-items:center;background:#eaf1ff;border-radius:9px;color:#1848a1;display:inline-flex;font-weight:800;height:30px;justify-content:center;width:30px}@media(max-width:767px){.bank-account-modal .modal-dialog{margin:.5rem}.bank-account-modal .modal-content{max-height:calc(100vh - 1rem)}.bank-account-modal .modal-body{padding:1rem!important}}
        .patient-hero{position:relative;overflow:hidden;padding:34px;border-radius:28px;background:linear-gradient(125deg,#153d8d,#1e69ad 55%,#31bebe);color:#fff;box-shadow:0 24px 58px rgba(24,72,161,.22)}.patient-hero:after{content:"";position:absolute;width:280px;height:280px;border-radius:50%;right:-80px;top:-120px;background:#ffffff18}.patient-hero .eyebrow,.patient-hero h1{color:#fff}.patient-hero p{color:#ddf5ff;max-width:720px}.patient-profile{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}.patient-chip{padding:8px 12px;border:1px solid #ffffff44;border-radius:999px;background:#ffffff16;font-size:.85rem;font-weight:700}.journey-actions{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin:20px 0 28px}.journey-action{display:flex;flex-direction:column;min-height:210px;padding:18px;border:1px solid #d2dff1;border-radius:20px;background:#fff;text-decoration:none;color:#263b57;box-shadow:0 12px 30px rgba(24,72,161,.08);transition:.18s}.journey-action:hover{transform:translateY(-3px);border-color:#31bebe;color:#263b57}.journey-action button{border:0;background:transparent;text-align:left;padding:0;color:inherit}.journey-number{display:grid;place-items:center;width:32px;height:32px;border-radius:10px;background:#1848a1;color:#fff;font-weight:900}.journey-icon{font-size:28px;margin:14px 0 8px}.journey-action strong{font-size:1rem}.journey-action small{color:#6d7d91;line-height:1.4;margin-top:7px}.journey-go{margin-top:auto;padding-top:13px;color:#1848a1;font-weight:900;font-size:.82rem}.journey-action form{margin-top:auto}.journey-subactions{display:flex;gap:6px;flex-wrap:wrap;margin-top:auto;padding-top:10px}.journey-subactions a,.journey-subactions button{padding:7px 9px;border-radius:9px;background:#edf4ff;color:#1848a1;font-size:.72rem;font-weight:850;text-decoration:none}.authorization-choice{display:grid;grid-template-columns:1fr 1fr;gap:12px}.authorization-choice button{padding:16px;border:0;border-radius:14px;font-weight:900}.auth-approve{background:#31bebe;color:#fff}.auth-reject{background:#fff0f1;color:#a8333e}@media(max-width:1050px){.journey-actions{grid-template-columns:repeat(2,1fr)}.journey-action:last-child{grid-column:1/-1}}@media(max-width:620px){.patient-hero{padding:24px}.journey-actions{grid-template-columns:1fr}.journey-action:last-child{grid-column:auto}}
    </style>

<div class="container py-5" style="max-width:1320px;margin-inline:auto">

    @include('partials.role_welcome_hero', [
        'class' => 'mb-4',
        'eyebrow' => 'Medichile · Recepción del centro médico',
        'title' => 'Hola, '.($sesionMedsdi['user']['name'] ?? auth()->user()->name),
        'description' => 'Recepciona pacientes, reserva horas y coordina su ingreso a sala de espera manteniendo identidad, bono y agenda conectados.',
        'chips' => [
            'Perfil asistente',
            $sesionMedsdi['user']['email'] ?? auth()->user()->email,
            $sesionMedsdi['ok'] ? 'Conectada con Med-SDI' : 'Conexión Med-SDI pendiente',
            $pendientesRecepcion.' recepciones pendientes',
        ],
    ])

    <div class="alert {{ $sesionMedsdi['ok'] ? 'alert-success' : 'alert-warning' }} d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4" role="status">
        <div>
            <div class="fw-bold">
                {{ $sesionMedsdi['ok'] ? 'Conectada con Med-SDI' : 'No fue posible iniciar sesión en Med-SDI' }}
            </div>
            <div>{{ $sesionMedsdi['mensaje'] }}</div>
        </div>
        @if ($sesionMedsdi['ok'])
            <div class="text-md-end">
                <div class="fw-bold">{{ $sesionMedsdi['user']['name'] ?: $sesionMedsdi['user']['email'] }}</div>
                <div class="small">
                    {{ $sesionMedsdi['user']['email'] }}
                    · {{ collect($sesionMedsdi['roles'])->pluck('name')->filter()->implode(', ') }}
                </div>
            </div>
        @endif
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-xl-4">
            <button type="button" class="btn p-0 border-0 bg-transparent text-start w-100 h-100" onclick="abrirRecepcionNueva()">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="fs-2">📲</div>
                            <span class="badge bg-primary rounded-pill">{{ $pendientesRecepcion }}</span>
                        </div>
                        <h4 class="mt-3 text-dark">Recepción de pacientes</h4>
                        <p class="text-muted mb-0">Leer QR, reconocer al paciente y enviarlo a espera.</p>
                    </div>
                </div>
            </button>
        </div>

        <div class="col-md-6 col-xl-4">
            <button type="button" class="btn p-0 border-0 bg-transparent text-start w-100 h-100" onclick="abrirModalAsistente('modalVentaBonoAsistente')">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="fs-2">🎫</div>
                        <h4 class="mt-3 text-dark">Venta de bonos</h4>
                        <p class="text-muted mb-0">Buscar al paciente por RUT, elegir prestación y reservar su hora en Med-SDI.</p>
                    </div>
                </div>
            </button>
        </div>

        <div class="col-md-6 col-xl-4">
            <button type="button" class="btn p-0 border-0 bg-transparent text-start w-100 h-100" onclick="abrirModalAsistente('modalSalaEsperaAsistente')">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="fs-2">🕐</div>
                            <span class="badge bg-success rounded-pill">{{ $pacientesEnEspera }}</span>
                        </div>
                        <h4 class="mt-3 text-dark">Sala de espera</h4>
                        <p class="text-muted mb-0">Pacientes sincronizados con estado Espera en Medichile.</p>
                    </div>
                </div>
            </button>
        </div>

        <!-- <div class="col-md-6 col-xl-3">
            <button type="button" class="btn p-0 border-0 bg-transparent text-start w-100 h-100" onclick="abrirModalAsistente('modalValidacionesAsistente')">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="fs-2">✅</div>
                            <span class="badge bg-warning text-dark rounded-pill">{{ $validacionesPendientes }}</span>
                        </div>
                        <h4 class="mt-3 text-dark">Validar atenciones</h4>
                        <p class="text-muted mb-0">Revisar cierres profesionales y habilitar bonos para cobro.</p>
                    </div>
                </div>
            </button>
        </div>

        

        <div class="col-md-6 col-xl-3">
            <button type="button" class="btn p-0 border-0 bg-transparent text-start w-100 h-100" onclick="abrirModalAsistente('modalAutorizacionesPaciente')">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="fs-2">🔔</div>
                            <span class="badge bg-danger rounded-pill">{{ $autorizacionesPendientes }}</span>
                        </div>
                        <h4 class="mt-3 text-dark">Notificaciones al paciente</h4>
                        <p class="text-muted mb-0">Revisar solicitudes de compra y simular la autorización del bono.</p>
                    </div>
                </div>
            </button>
        </div> -->
    </div>

</div>

<div class="modal fade" id="modalAutorizacionesPaciente" tabindex="-1" aria-labelledby="modalAutorizacionesPacienteTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background:linear-gradient(120deg,#1848a1,#31bebe)">
                <div>
                    <div class="small fw-bold text-uppercase">App autorizadora</div>
                    <h4 class="modal-title fw-bold" id="modalAutorizacionesPacienteTitulo">Notificaciones de compra de bonos</h4>
                    <small>Solicitudes enviadas al paciente para aprobar o rechazar el copago.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalAsistente('modalAutorizacionesPaciente')" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                @if(session('abrir_autorizaciones_modal') && session('ok'))<div class="alert alert-success">{{ session('ok') }}</div>@endif
                @if(session('abrir_autorizaciones_modal') && session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
                <div class="alert alert-info">
                    En producción responde el paciente desde su dispositivo autorizado. Estos botones simulan esa respuesta para probar el flujo completo.
                </div>
                <div class="table-responsive border rounded-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Paciente</th><th>Prestación</th><th>Profesional</th><th>Copago</th><th>Solicitud</th><th>Estado</th><th class="text-end">Acción</th></tr>
                        </thead>
                        <tbody>
                        @forelse($autorizacionesPaciente as $autorizacion)
                            @php($datos = $autorizacion->metadata ?: [])
                            <tr>
                                <td><strong>{{ $datos['beneficiario'] ?? 'Paciente #'.$autorizacion->cliente_id }}</strong><small class="d-block text-muted">{{ isset($datos['rut']) ? sdi_formatear_rut($datos['rut']) : 'RUT no informado' }}</small></td>
                                <td>{{ $datos['servicio_nombre'] ?? 'Compra de bono' }}</td>
                                <td>{{ $datos['profesional_nombre'] ?? 'Por definir' }}</td>
                                <td>${{ number_format((float) ($datos['copago'] ?? 0), 0, ',', '.') }}</td>
                                <td><span class="d-block">{{ $autorizacion->created_at?->format('d-m-Y H:i') }}</span><small class="text-muted">Vence {{ $autorizacion->expira_at?->format('d-m-Y H:i') ?: 'sin fecha' }}</small></td>
                                <td>
                                    @switch($autorizacion->estado)
                                        @case('aprobada') <span class="badge bg-success">Autorizada</span> @break
                                        @case('rechazada') <span class="badge bg-danger">Rechazada</span> @break
                                        @case('expirada') <span class="badge bg-secondary">Expirada</span> @break
                                        @default <span class="badge bg-warning text-dark">Pendiente</span>
                                    @endswitch
                                </td>
                                <td class="text-end">
                                    @if($autorizacion->estado === 'pendiente' && config('demo.enabled'))
                                        <div class="d-inline-flex gap-2">
                                            <form method="POST" action="{{ route('asistente.autorizaciones.responder', $autorizacion) }}" class="form-responder-autorizacion" data-respuesta="rechazar">@csrf<input type="hidden" name="respuesta" value="rechazar"><button class="btn btn-outline-danger btn-sm">Rechazar</button></form>
                                            <form method="POST" action="{{ route('asistente.autorizaciones.responder', $autorizacion) }}" class="form-responder-autorizacion" data-respuesta="aprobar">@csrf<input type="hidden" name="respuesta" value="aprobar"><button class="btn btn-success btn-sm">Autorizar</button></form>
                                        </div>
                                    @else
                                        <span class="text-muted small">Respondida</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-5">No existen notificaciones de compra de bonos.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalValidacionesAsistente" tabindex="-1" aria-labelledby="modalValidacionesTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background:linear-gradient(120deg,#1848a1,#31bebe)">
                <div><div class="small fw-bold text-uppercase">Control asistencial</div><h4 class="modal-title fw-bold" id="modalValidacionesTitulo">Atenciones cerradas</h4><small>Consultas finalizadas por el profesional y disponibles para revisión.</small></div>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalAsistente('modalValidacionesAsistente')" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Bono</th><th>Paciente</th><th>Profesional</th><th>Diagnóstico</th><th>Cierre</th><th>Estado</th><th>Acción</th></tr></thead>
                        <tbody>
                        @forelse($atencionesCerradas as $voucherCerrado)
                            <tr>
                                <td><strong>{{ $voucherCerrado->codigo }}</strong></td>
                                <td>{{ $voucherCerrado->beneficiario_nombre ?: $voucherCerrado->cliente_nombre }}</td>
                                <td>{{ $voucherCerrado->prestador_nombre ?: optional($voucherCerrado->profesional)->nombre ?: '-' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit(optional($voucherCerrado->atencion)->diagnostico ?: 'Sin diagnóstico informado', 85) }}</td>
                                <td>{{ optional(optional($voucherCerrado->atencion)->cerrada_at ?: $voucherCerrado->atencion_cerrada_at)->format('d-m-Y H:i') ?: '-' }}</td>
                                <td>
                                    @if($voucherCerrado->estado === 'validado_atencion')
                                        <span class="badge bg-success">Habilitada para cobro</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Atención cerrada</span>
                                    @endif
                                </td>
                                <td><button type="button" class="btn btn-primary btn-sm" onclick="mostrarRevisionEnConstruccion()">Revisar</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-5">No hay atenciones cerradas pendientes de revisión.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSalaEsperaAsistente" tabindex="-1" aria-labelledby="modalSalaEsperaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background:linear-gradient(120deg,#1848a1,#31bebe)">
                <div><div class="small fw-bold text-uppercase">Agenda clínica</div><h4 class="modal-title fw-bold" id="modalSalaEsperaTitulo">Pacientes en espera</h4><small>Recepcionados y confirmados en la agenda real de Med-SDI.</small></div>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalAsistente('modalSalaEsperaAsistente')" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Paciente</th><th>RUT</th><th>Prestación</th><th>Llegada</th><th>Hora Med-SDI</th><th>Estado</th></tr></thead>
                        <tbody>
                        @forelse($pacientesEnEsperaDetalle as $bono)
                            <tr>
                                <td><strong>{{ $bono->beneficiario_nombre ?: $bono->cliente_nombre }}</strong><small class="d-block text-muted">Bono {{ $bono->codigo }}</small></td>
                                <td>{{ sdi_formatear_rut($bono->beneficiario_rut_visible ?: $bono->cliente_rut_visible) }}</td>
                                <td>{{ $bono->tipo_servicio }}</td>
                                <td>{{ optional($bono->agenda->fecha_hora_confirmada)->format('d-m-Y H:i') ?: '-' }}</td>
                                <td><span class="badge bg-light text-dark border">#{{ $bono->agenda->medichile_hora_medica_id }}</span></td>
                                <td><span class="badge bg-warning text-dark">Esperando atención</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">No hay pacientes esperando atención.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRecepcionAsistente" tabindex="-1" aria-labelledby="modalRecepcionAsistenteTitulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background:linear-gradient(120deg,#1848a1,#31bebe)">
                <div>
                    <h4 class="modal-title fw-bold" id="modalRecepcionAsistenteTitulo">Recepción de bonos</h4>
                    <small>Identifique la reserva y envíe al paciente a sala de espera.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalAsistente('modalRecepcionAsistente')" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                @if(session('ok'))<div class="alert alert-success">{{ session('ok') }}</div>@endif
                @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

                <ul class="nav nav-pills nav-fill gap-2 mb-4" role="tablist">
                    <li class="nav-item"><button class="nav-link {{ old('metodo') !== 'rut' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#recepcionCodigo" type="button">Código o QR</button></li>
                    <li class="nav-item"><button class="nav-link {{ old('metodo') === 'rut' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#recepcionRut" type="button">RUT del paciente</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade {{ old('metodo') !== 'rut' ? 'show active' : '' }}" id="recepcionCodigo">
                        <form method="POST" action="{{ route('asistente.recepcion.buscar') }}">
                            @csrf
                            <input type="hidden" name="metodo" value="codigo">
                            <label class="form-label fw-bold">Código del bono o contenido del QR</label>
                            <div class="input-group input-group-lg">
                                <input class="form-control" name="codigo" value="{{ old('codigo') }}" placeholder="Ej.: BONO-EXT-260910-ATI..." required>
                                <button class="btn btn-primary">Buscar reserva</button>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade {{ old('metodo') === 'rut' ? 'show active' : '' }}" id="recepcionRut">
                        <form method="POST" action="{{ route('asistente.recepcion.buscar') }}">
                            @csrf
                            <input type="hidden" name="metodo" value="rut">
                            <label class="form-label fw-bold">RUT del paciente</label>
                            <div class="input-group input-group-lg">
                                <input class="form-control" name="rut" value="{{ old('rut') }}" placeholder="Ej.: 6.187.674-K" autocomplete="off" data-rut-input required>
                                <button class="btn btn-primary">Buscar hora</button>
                            </div>
                        </form>
                        <div class="alert alert-light border mt-3 mb-0">Sólo se mostrarán horas vigentes pendientes de llegada.</div>
                    </div>
                </div>

                @if($pacienteRecepcion)
                    <div id="recepcionPacienteResultado" class="alert {{ ($pacienteRecepcion['tipo'] ?? null) === 'dependiente' ? 'alert-info' : 'alert-success' }} mt-4 mb-0">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                            <div>
                                <strong>{{ $pacienteRecepcion['nombre'] ?: 'Paciente reconocido' }}</strong>
                                <span class="badge {{ ($pacienteRecepcion['tipo'] ?? null) === 'dependiente' ? 'bg-info text-dark' : (($pacienteRecepcion['tipo'] ?? null) === 'titular' ? 'bg-success' : 'bg-secondary') }} ms-1">
                                    {{ ($pacienteRecepcion['tipo'] ?? null) === 'dependiente' ? 'Dependiente' : (($pacienteRecepcion['tipo'] ?? null) === 'titular' ? 'Titular' : 'Paciente') }}
                                </span>
                                <div class="small mt-1">RUT {{ sdi_formatear_rut($pacienteRecepcion['rut']) }}@if($pacienteRecepcion['parentesco']) · {{ $pacienteRecepcion['parentesco'] }}@endif</div>
                            </div>
                            @if(($pacienteRecepcion['tipo'] ?? null) === 'dependiente')
                                <div class="text-md-end"><small class="d-block text-muted">Titular responsable y receptor de la App</small><strong>{{ $pacienteRecepcion['titular_nombre'] ?: 'No identificado' }}</strong>@if($pacienteRecepcion['titular_rut'])<div class="small">RUT {{ sdi_formatear_rut($pacienteRecepcion['titular_rut']) }}</div>@endif</div>
                            @endif
                        </div>
                    </div>
                @endif

                @if($bonosRecepcion->isNotEmpty())
                    <div id="recepcionHorasResultado"><hr class="my-4">
                    <h5 class="fw-bold mb-3">Horas encontradas</h5>
                    @foreach($bonosRecepcion as $bono)
                        @php($recepcion = $recepcionesPorVoucher->get($bono->id))
                        <div class="card border mb-3"><div class="card-body d-flex flex-column flex-md-row justify-content-between gap-3">
                            <div>
                                <h5 class="mb-2">{{ $bono->tipo_servicio }} · {{ $bono->codigo }}</h5>
                                <div><strong>Paciente:</strong> {{ $bono->beneficiario_nombre ?: $bono->cliente_nombre }}</div>
                                @if(($bono->beneficiario_tipo ?? 'titular') !== 'titular')
                                    <div><strong>Tipo:</strong> <span class="badge bg-info text-dark">Dependiente · {{ $bono->beneficiario_parentesco ?: 'Carga' }}</span></div>
                                    <div><strong>Titular responsable:</strong> {{ $bono->cliente_nombre }}</div>
                                @endif
                                <div><strong>Profesional:</strong> {{ $bono->prestador_nombre ?: optional($bono->profesional)->nombre }}</div>
                                <div><strong>Hora:</strong> {{ optional($bono->agenda?->fecha_hora_confirmada ?: $bono->agenda?->fecha_hora_solicitada)?->format('d-m-Y H:i') }}</div>
                                <div class="mt-2"><strong>Estado:</strong> @include('partials.voucher_estado_celda', ['voucher' => $bono])</div>
                            </div>
                            <div class="d-flex flex-column gap-2 align-self-md-center">
                                @if(config('demo.enabled'))
                                    <form method="POST" action="{{ route('asistente.recepcion.solicitar_autorizacion', $bono) }}" class="asistente-solicitar-autorizacion">
                                        @csrf
                                        <button class="btn btn-outline-info text-nowrap">📲 Notificar a la App</button>
                                    </form>
                                @endif
                                @if(optional($bono->agenda)->medichile_hora_medica_id)
                                    <form method="POST" action="{{ route('asistente.recepcion.sincronizar_hora', $bono) }}">
                                        @csrf
                                        <button class="btn btn-outline-primary text-nowrap" title="Consultar estado actual en Med-SDI">↻ Actualizar estado</button>
                                    </form>
                                @endif
                                @if($bono->estado === 'pendiente_confirmacion' && optional($bono->agenda)->estado === 'hora_reservada' && optional($bono->agenda)->medichile_hora_medica_id)
                                    <form method="POST" action="{{ route('asistente.recepcion.confirmar_hora', $bono) }}" class="asistente-confirmar-hora">
                                        @csrf
                                        <button class="btn btn-primary text-nowrap">✓ Confirmar hora</button>
                                    </form>
                                @endif
                                @if($bono->estado === 'pendiente_pago' && optional($bono->agenda)->estado === 'hora_confirmada')
                                    <button type="button" class="btn btn-warning text-nowrap asistente-abrir-pago"
                                        data-action="{{ route('asistente.recepcion.pagar', $bono) }}"
                                        data-codigo="{{ $bono->codigo }}" data-paciente="{{ $bono->beneficiario_nombre ?: $bono->cliente_nombre }}"
                                        data-profesional="{{ $bono->prestador_nombre ?: optional($bono->profesional)->nombre }}"
                                        data-valor="{{ $bono->valor }}" data-copago="{{ $bono->copago_usuario }}" data-bonificacion="{{ max($bono->valor - $bono->copago_usuario, 0) }}">💳 Pagar bono</button>
                                @endif
                                @if($recepcion)
                                    <form method="POST" action="{{ route('asistente.recepcion.espera', $recepcion) }}">
                                        @csrf
                                        <button class="btn btn-success text-nowrap">Confirmar llegada</button>
                                    </form>
                                @else
                                    <span class="badge text-bg-warning">Sin recepción asociada</span>
                                @endif
                            </div>
                        </div></div>
                    @endforeach</div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($autorizacionAppPaciente)
    @php($appDatos = $autorizacionAppPaciente->metadata ?: [])
    <div class="modal fade" id="modalAppPacienteSimulada" tabindex="-1" aria-labelledby="modalAppPacienteSimuladaTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius:22px;overflow:hidden">
                <div class="modal-header text-white" style="background:linear-gradient(120deg,#1848a1,#31bebe)">
                    <div><div class="small text-uppercase opacity-75 fw-bold">App móvil simulada</div><h4 class="modal-title" id="modalAppPacienteSimuladaTitulo">Autorizar bono médico</h4></div>
                    <button type="button" class="btn-close btn-close-white" onclick="cerrarModalAsistente('modalAppPacienteSimulada')"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info"><strong>Solicitud segura:</strong> revise los datos antes de aceptar o rechazar.</div>
                    <dl class="row mb-0">
                        <dt class="col-4">Beneficiario</dt><dd class="col-8">{{ $appDatos['beneficiario'] ?? 'Paciente' }}@if(($appDatos['beneficiario_tipo'] ?? 'titular') !== 'titular')<span class="badge bg-info text-dark ms-1">Dependiente</span>@endif</dd>
                        <dt class="col-4">RUT</dt><dd class="col-8">{{ sdi_formatear_rut($appDatos['beneficiario_rut'] ?? '') }}</dd>
                        @if(($appDatos['beneficiario_tipo'] ?? 'titular') !== 'titular')<dt class="col-4">Titular</dt><dd class="col-8">{{ $appDatos['titular'] ?? 'No informado' }} · {{ sdi_formatear_rut($appDatos['titular_rut'] ?? '') }}</dd>@endif
                        <dt class="col-4">Bono</dt><dd class="col-8">{{ $appDatos['bono'] ?? '-' }}</dd>
                        <dt class="col-4">Prestación</dt><dd class="col-8">{{ $appDatos['servicio_nombre'] ?? '-' }}</dd>
                        <dt class="col-4">Profesional</dt><dd class="col-8">{{ $appDatos['profesional_nombre'] ?? '-' }}</dd>
                        <dt class="col-4">Copago</dt><dd class="col-8 fw-bold">${{ number_format((float) ($appDatos['copago'] ?? 0), 0, ',', '.') }}</dd>
                    </dl>
                    <small class="text-muted">Esta solicitud vence {{ $autorizacionAppPaciente->expira_at?->format('d-m-Y H:i:s') }}.</small>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 d-grid gap-2" style="grid-template-columns:1fr 1fr">
                    <form method="POST" action="{{ route('asistente.recepcion.responder_autorizacion', $autorizacionAppPaciente) }}" class="form-app-paciente" data-respuesta="rechazar">@csrf<input type="hidden" name="respuesta" value="rechazar"><button class="btn btn-outline-danger w-100">No autorizar</button></form>
                    <form method="POST" action="{{ route('asistente.recepcion.responder_autorizacion', $autorizacionAppPaciente) }}" class="form-app-paciente" data-respuesta="aprobar">@csrf<input type="hidden" name="respuesta" value="aprobar"><button class="btn btn-success w-100">Aceptar y autorizar</button></form>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="modal fade" id="modalPagoBonoAsistente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:20px;overflow:hidden">
            <div class="modal-header text-white" style="background:linear-gradient(120deg,#087f6f,#31bebe)">
                <div><div class="small text-uppercase opacity-75 fw-bold">Recepción de pago</div><h4 class="modal-title">Pagar copago del bono</h4></div>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalAsistente('modalPagoBonoAsistente')"></button>
            </div>
            <form method="POST" id="formPagoBonoAsistente">@csrf
                <div class="modal-body p-4">
                    <div class="alert alert-warning">Valide paciente, profesional, hora y monto antes de registrar el pago.</div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Paciente</label><input id="pagoAsistentePaciente" class="form-control" readonly></div>
                        <div class="col-md-6"><label class="form-label">Profesional</label><input id="pagoAsistenteProfesional" class="form-control" readonly></div>
                        <div class="col-md-6"><label class="form-label">Bono</label><input id="pagoAsistenteCodigo" class="form-control" readonly></div>
                        <div class="col-md-6"><label class="form-label">Método de pago</label><select name="metodo_pago" class="form-select" required><option value="tarjeta_credito">Tarjeta de crédito</option><option value="tarjeta_debito">Tarjeta de débito</option><option value="transferencia">Transferencia bancaria</option><option value="efectivo">Efectivo</option></select></div>
                        <div class="col-md-4"><label class="form-label">Valor prestación</label><input id="pagoAsistenteValor" class="form-control" readonly></div>
                        <div class="col-md-4"><label class="form-label">Bonificación</label><input id="pagoAsistenteBonificacion" class="form-control" readonly></div>
                        <div class="col-md-4"><label class="form-label">Copago</label><input id="pagoAsistenteCopago" class="form-control fw-bold" readonly></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" onclick="cerrarModalAsistente('modalPagoBonoAsistente')">Cancelar</button><button class="btn btn-success">Confirmar pago</button></div>
            </form>
        </div>
    </div>
</div>

<style>
    #ventaAsistenteBeneficiarios {
        gap: .55rem;
    }
    #ventaAsistenteBeneficiarios .responsable-option {
        border: 1px solid #d8e0ea;
        border-radius: .8rem;
        background: #fff;
        color: #243142;
        padding: .85rem 1rem;
        transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
    }
    #ventaAsistenteBeneficiarios .responsable-option:hover {
        border-color: #9aabba;
        background: #f8fafc;
    }
    #ventaAsistenteBeneficiarios .responsable-option.active {
        border-color: #55748f;
        background: #eef3f7;
        color: #172536;
        box-shadow: 0 0 0 2px rgba(85, 116, 143, .1);
    }
    #ventaAsistenteBeneficiarios .responsable-rut {
        color: #708092;
    }
    #ventaAsistenteBeneficiarios .responsable-badge {
        border: 1px solid #cbd5df;
        border-radius: 999px;
        background: #f5f7f9;
        color: #536273;
        font-size: .76rem;
        font-weight: 600;
        padding: .35rem .6rem;
        white-space: nowrap;
    }
    #ventaAsistenteBeneficiarios .responsable-option.active .responsable-badge {
        border-color: #afbfcd;
        background: #fff;
        color: #3d566b;
    }
</style>

<div class="modal fade" id="modalVentaBonoAsistente" tabindex="-1" aria-labelledby="modalVentaBonoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background:linear-gradient(120deg,#1848a1,#31bebe)">
                <div><div class="small fw-bold text-uppercase">Reserva asistida</div><h4 class="modal-title fw-bold" id="modalVentaBonoTitulo">Vender bono y reservar hora</h4><small>La hora y el bono quedarán asociados al paciente identificado por su RUT.</small></div>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalAsistente('modalVentaBonoAsistente')" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div id="ventaAsistenteAviso" class="alert d-none" role="alert"></div>
                <section class="border rounded-3 p-3 mb-3">
                    <div class="small text-primary fw-bold mb-2">1 · PACIENTE</div>
                    <label class="form-label fw-bold" for="ventaAsistenteRut">RUT del paciente</label>
                    <div class="input-group input-group-lg">
                        <input id="ventaAsistenteRut" class="form-control" placeholder="Ej.: 17.174.188-2" autocomplete="off" data-rut-input>
                        <button id="ventaAsistenteValidar" type="button" class="btn btn-primary">Validar paciente</button>
                    </div>
                    <div id="ventaAsistentePaciente" class="alert alert-success mt-3 mb-0 d-none"></div>
                    <div id="ventaAsistenteBeneficiariosTitulo" class="small fw-bold mt-3 mb-2 d-none">Seleccione el titular responsable que autorizará desde la App</div>
                    <div id="ventaAsistenteBeneficiarios" class="list-group mt-2 d-none" aria-label="Responsables disponibles"></div>
                </section>

                <section id="ventaAsistentePasoPrestacion" class="border rounded-3 p-3 mb-3 d-none">
                    <div class="small text-primary fw-bold mb-2">2 · PRESTACIÓN FONASA</div>
                    <label class="form-label" for="ventaAsistentePrestacionBuscar">Busca por nombre o código</label>
                    <input type="search" id="ventaAsistentePrestacionBuscar" class="form-control" autocomplete="off" placeholder="Ej.: consulta médica o 0101001" aria-controls="ventaAsistentePrestaciones">
                    <div id="ventaAsistentePrestaciones" class="list-group mt-2 d-none" style="max-height:260px;overflow-y:auto"></div>
                    <div id="ventaAsistentePrestacionElegida" class="alert alert-info mt-3 mb-0 d-none"></div>
                </section>

                <section id="ventaAsistentePasoProfesional" class="border rounded-3 p-3 mb-3 d-none">
                    <div class="small text-primary fw-bold mb-2">3 · PROFESIONAL Y LUGAR</div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6"><label class="form-label small">Región</label><select id="ventaAsistenteRegion" class="form-select"><option value="">Todas las regiones</option></select></div>
                        <div class="col-md-6"><label class="form-label small">Ciudad</label><select id="ventaAsistenteCiudad" class="form-select" disabled><option value="">Primero seleccione una región</option></select></div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-4"><label class="form-label small">Profesión</label><select id="ventaAsistenteEspecialidad" class="form-select"><option value="">Todas</option></select></div>
                        <div class="col-md-4"><label class="form-label small">Especialidad</label><select id="ventaAsistenteTipoEspecialidad" class="form-select" disabled><option value="">Todas</option></select></div>
                        <div class="col-md-4"><label class="form-label small">Subespecialidad</label><select id="ventaAsistenteSubTipoEspecialidad" class="form-select" disabled><option value="">Todas</option></select></div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-9"><label class="form-label small">Nombre del profesional (opcional)</label><input id="ventaAsistenteProfesionalBuscar" class="form-control" autocomplete="off" placeholder="Ej.: Jaime Kriman"></div>
                        <div class="col-md-3 d-grid"><label class="form-label small">&nbsp;</label><button id="ventaAsistenteProfesionalBtn" type="button" class="btn btn-primary">Buscar profesionales</button></div>
                    </div>
                    <div id="ventaAsistenteProfesionales" class="row g-2 mt-1"></div>
                    <div id="ventaAsistenteProfesionalElegido" class="alert alert-info mt-3 mb-0 d-none"></div>
                </section>

                <section id="ventaAsistentePasoHora" class="border rounded-3 p-3 d-none">
                    <div class="small text-primary fw-bold mb-2">4 · FECHA Y HORA</div>
                    <div id="ventaAsistenteCotizacion" class="alert alert-success"></div>
                    <div id="ventaAsistenteDiasAtencion" class="small fw-semibold text-primary mb-2">Consultando días de atención...</div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-8"><label class="form-label">Fecha</label><input id="ventaAsistenteFecha" type="text" class="form-control" placeholder="Seleccione un día disponible" disabled></div>
                        <div class="col-md-4 d-grid"><button id="ventaAsistenteHorasBtn" type="button" class="btn btn-outline-primary">Ver horas disponibles</button></div>
                    </div>
                    <div id="ventaAsistenteHoras" class="d-flex flex-wrap gap-2 mt-3"></div>
                </section>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/plugins/sweetalert.min.js') }}"></script>
@include('partials.rut_input_script')
<script>
function mostrarRevisionEnConstruccion() {
    if (typeof swal === 'function') {
        swal({icon:'info', title:'En construcción', text:'La revisión detallada de la atención estará disponible próximamente.', button:'Entendido'});
        return;
    }
    alert('En construcción: la revisión detallada estará disponible próximamente.');
}
function abrirModalAsistente(id) {
    var modal = document.getElementById(id);
    if (!modal) return;
    modal.style.display = 'block';
    modal.classList.add('show');
    modal.removeAttribute('aria-hidden');
    modal.setAttribute('aria-modal', 'true');
    document.body.classList.add('modal-open');
    var fondo = document.createElement('div');
    fondo.className = 'modal-backdrop fade show';
    fondo.dataset.modalAsistenteBackdrop = id;
    fondo.onclick = function () { cerrarModalAsistente(id); };
    document.body.appendChild(fondo);
}
function abrirRecepcionNueva() {
    document.querySelectorAll('#modalRecepcionAsistente input[name="rut"], #modalRecepcionAsistente input[name="codigo"]').forEach(function (input) { input.value = ''; });
    document.getElementById('recepcionPacienteResultado')?.classList.add('d-none');
    document.getElementById('recepcionHorasResultado')?.classList.add('d-none');
    abrirModalAsistente('modalRecepcionAsistente');
}
function cerrarModalAsistente(id) {
    var modal = document.getElementById(id);
    if (!modal) return;
    modal.style.display = 'none';
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
    modal.removeAttribute('aria-modal');
    document.body.classList.remove('modal-open');
    document.querySelectorAll('[data-modal-asistente-backdrop="' + id + '"]').forEach(function (fondo) { fondo.remove(); });
}
document.addEventListener('DOMContentLoaded', function () {
    const monedaAsistente = valor => new Intl.NumberFormat('es-CL',{style:'currency',currency:'CLP',maximumFractionDigits:0}).format(Number(valor) || 0);
    document.querySelectorAll('.asistente-solicitar-autorizacion').forEach(form => form.addEventListener('submit', async event => {
        event.preventDefault();
        const confirmado = typeof swal === 'function'
            ? await swal({title:'¿Notificar al paciente?',text:'La solicitud se enviará al dispositivo del titular responsable y se abrirá la App móvil simulada.',icon:'warning',buttons:['Cancelar','Enviar notificación']})
            : confirm('¿Enviar la solicitud de autorización a la App del paciente?');
        if (confirmado) HTMLFormElement.prototype.submit.call(form);
    }));
    document.querySelectorAll('.form-app-paciente').forEach(form => form.addEventListener('submit', async event => {
        event.preventDefault();
        const aprobar = form.dataset.respuesta === 'aprobar';
        const confirmado = typeof swal === 'function'
            ? await swal({
                title: aprobar ? '¿Aceptar y autorizar?' : '¿Rechazar el bono?',
                text: aprobar ? 'La App registrará la autorización del titular responsable.' : 'La App informará que el paciente no autorizó el bono.',
                icon: aprobar ? 'warning' : 'error',
                buttons: ['Cancelar', aprobar ? 'Autorizar' : 'Rechazar'],
                dangerMode: !aprobar,
            })
            : confirm(aprobar ? '¿Autorizar el bono?' : '¿Rechazar el bono?');
        if (confirmado) HTMLFormElement.prototype.submit.call(form);
    }));
    document.querySelectorAll('.form-responder-autorizacion').forEach(form => form.addEventListener('submit', async event => {
        event.preventDefault();
        const aprobar = form.dataset.respuesta === 'aprobar';
        const confirmado = typeof swal === 'function'
            ? await swal({
                title: aprobar ? '¿Autorizar compra?' : '¿Rechazar compra?',
                text: aprobar
                    ? 'Se registrará la aprobación del paciente y la compra podrá continuar usando su token vigente.'
                    : 'La solicitud quedará rechazada y no podrá utilizarse para generar el bono.',
                icon: aprobar ? 'warning' : 'error',
                buttons: ['Cancelar', aprobar ? 'Autorizar' : 'Rechazar'],
                dangerMode: !aprobar,
            })
            : confirm(aprobar ? '¿Autorizar esta compra?' : '¿Rechazar esta compra?');
        if (confirmado) HTMLFormElement.prototype.submit.call(form);
    }));
    document.querySelectorAll('.asistente-abrir-pago').forEach(button => button.addEventListener('click', () => {
        const form = document.getElementById('formPagoBonoAsistente');
        form.action = button.dataset.action;
        document.getElementById('pagoAsistentePaciente').value = button.dataset.paciente;
        document.getElementById('pagoAsistenteProfesional').value = button.dataset.profesional;
        document.getElementById('pagoAsistenteCodigo').value = button.dataset.codigo;
        document.getElementById('pagoAsistenteValor').value = monedaAsistente(button.dataset.valor);
        document.getElementById('pagoAsistenteBonificacion').value = monedaAsistente(button.dataset.bonificacion);
        document.getElementById('pagoAsistenteCopago').value = monedaAsistente(button.dataset.copago);
        abrirModalAsistente('modalPagoBonoAsistente');
    }));
    document.getElementById('formPagoBonoAsistente')?.addEventListener('submit', async event => {
        event.preventDefault();
        const form = event.currentTarget;
        const confirmado = typeof swal === 'function'
            ? await swal({title:'¿Confirmar pago?',text:'Se registrará el copago y el bono quedará activo con su QR disponible.',icon:'warning',buttons:['Cancelar','Confirmar pago']})
            : confirm('¿Confirmar el pago del bono?');
        if (confirmado) HTMLFormElement.prototype.submit.call(form);
    });
    document.querySelectorAll('.asistente-confirmar-hora').forEach(form => form.addEventListener('submit', async event => {
        event.preventDefault();
        const confirmado = typeof swal === 'function'
            ? await swal({title:'¿Confirmar hora médica?',text:'La hora será confirmada en Med-SDI y el bono quedará pendiente de pago.',icon:'warning',buttons:['Cancelar','Confirmar hora']})
            : confirm('¿Confirmar esta hora médica?');
        if (confirmado) form.submit();
    }));
    document.querySelectorAll('#modalRecepcionAsistente [data-bs-toggle="pill"]').forEach(function (boton) {
        boton.addEventListener('click', function (evento) {
            evento.preventDefault();
            document.querySelectorAll('#modalRecepcionAsistente [data-bs-toggle="pill"]').forEach(function (item) { item.classList.remove('active'); });
            document.querySelectorAll('#modalRecepcionAsistente .tab-pane').forEach(function (panel) { panel.classList.remove('show', 'active'); });
            boton.classList.add('active');
            var panel = document.querySelector(boton.dataset.bsTarget);
            if (panel) panel.classList.add('show', 'active');
        });
    });

    const venta = { paciente: null, prestacion: null, seleccion: null, cotizacion: null };
    let ventaCalendario = null;
    let ventaPrestacionTimer = null;
    let ventaPrestacionConsulta = 0;
    const ventaProfesion = document.getElementById('ventaAsistenteEspecialidad');
    const ventaEspecialidad = document.getElementById('ventaAsistenteTipoEspecialidad');
    const ventaSubespecialidad = document.getElementById('ventaAsistenteSubTipoEspecialidad');
    const ventaRegion = document.getElementById('ventaAsistenteRegion');
    const ventaCiudad = document.getElementById('ventaAsistenteCiudad');
    const llenarSelectVenta = (select, registros, placeholder = 'Todas') => {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        registros.forEach(registro => select.add(new Option(registro.nombre, registro.id)));
        select.disabled = registros.length === 0;
    };
    const ventaAviso = (texto, tipo = 'danger') => {
        const el = document.getElementById('ventaAsistenteAviso');
        el.className = `alert alert-${tipo}`;
        el.textContent = texto;
    };
    const ventaJson = async (url, opciones = {}) => {
        const respuesta = await fetch(url, {headers: {'Accept':'application/json', ...(opciones.body ? {'Content-Type':'application/json', 'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content} : {})}, ...opciones});
        const data = await respuesta.json();
        if (!respuesta.ok && !data.mensaje) data.mensaje = Object.values(data.errors || {}).flat()[0] || 'No fue posible procesar la solicitud.';
        return data;
    };
    const ventaNombresDias = ['', 'LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO', 'DOMINGO'];
    const reiniciarHorarioVenta = () => {
        ventaCalendario?.destroy();
        ventaCalendario = null;
        const fecha = document.getElementById('ventaAsistenteFecha');
        fecha.value = '';
        fecha.disabled = true;
        document.getElementById('ventaAsistenteHoras').innerHTML = '';
        document.getElementById('ventaAsistentePasoHora').classList.add('d-none');
    };
    const cargarDiasLaboralesVenta = async () => {
        const fecha = document.getElementById('ventaAsistenteFecha');
        const estado = document.getElementById('ventaAsistenteDiasAtencion');
        estado.textContent = 'Consultando días de atención...';
        const params = new URLSearchParams({
            id_profesional: venta.seleccion.idProfesional,
            id_lugar: venta.seleccion.idLugar,
        });
        const data = await ventaJson(`{{ route('asistente.venta_bonos.dias_laborales') }}?${params}`);
        const dias = String(data.registros?.horario_agenda_laboral || '')
            .split(',').map(Number).filter(dia => dia >= 1 && dia <= 7);
        if (!data.ok || !dias.length) {
            estado.textContent = 'El profesional no tiene días de atención informados para este lugar.';
            fecha.disabled = true;
            return;
        }
        estado.textContent = `Atiende los días: ${dias.map(dia => ventaNombresDias[dia]).join(' · ')}`;
        fecha.disabled = false;
        ventaCalendario = flatpickr(fecha, {
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
            onChange: () => document.getElementById('ventaAsistenteHorasBtn').click(),
        });
    };

    document.getElementById('ventaAsistenteValidar')?.addEventListener('click', async () => {
        const rut = document.getElementById('ventaAsistenteRut').value.trim();
        if (!rut) return ventaAviso('Ingrese el RUT del paciente.');
        ventaAviso('Validando paciente en Med-SDI...', 'info');
        const data = await ventaJson(`{{ route('asistente.venta_bonos.paciente') }}?rut=${encodeURIComponent(rut)}`);
        if (!data.ok || !data.paciente) return ventaAviso(data.mensaje || 'Paciente no encontrado.');
        const paciente = data.paciente;
        const esDependiente = paciente.tipo === 'dependiente' || Boolean(paciente.es_dependiente);
        const responsables = Array.isArray(paciente.responsables) ? paciente.responsables : [];
        const contenedor = document.getElementById('ventaAsistenteBeneficiarios');
        contenedor.innerHTML = '';
        const mostrarPaciente = responsable => {
            venta.paciente = {...paciente, titular: responsable || null};
            const estado = esDependiente
                ? `Dependiente (${paciente.parentesco || 'Carga'}) · Responsable: ${responsable?.nombre_completo || 'por seleccionar'}`
                : 'Titular';
            document.getElementById('ventaAsistentePaciente').textContent = `✓ ${paciente.nombre_completo} · RUT ${paciente.rut} · ${estado}`;
            document.getElementById('ventaAsistentePaciente').classList.remove('d-none');
            contenedor.querySelectorAll('button').forEach(boton => boton.classList.toggle('active', boton.dataset.rut === String(responsable?.rut || '')));
            document.getElementById('ventaAsistentePasoPrestacion').classList.toggle('d-none', esDependiente && !responsable);
            if (!esDependiente || responsable) document.getElementById('ventaAsistentePrestacionBuscar').focus();
        };
        responsables.forEach(responsable => {
            const boton = document.createElement('button');
            boton.type = 'button';
            boton.className = 'responsable-option d-flex justify-content-between align-items-center gap-3 text-start';
            boton.dataset.rut = responsable.rut;
            const texto = document.createElement('span');
            texto.innerHTML = `<strong></strong><small class="d-block text-muted"></small>`;
            texto.querySelector('strong').textContent = responsable.nombre_completo;
            texto.querySelector('small').classList.add('responsable-rut');
            texto.querySelector('small').textContent = `RUT ${responsable.rut}`;
            const badge = document.createElement('span');
            badge.className = 'responsable-badge';
            badge.textContent = responsable.parentesco ? `Responsable · ${responsable.parentesco}` : 'Responsable';
            boton.append(texto, badge);
            boton.onclick = () => mostrarPaciente(responsable);
            contenedor.appendChild(boton);
        });
        document.getElementById('ventaAsistenteBeneficiariosTitulo').classList.toggle('d-none', !esDependiente);
        contenedor.classList.toggle('d-none', !esDependiente);
        if (esDependiente && responsables.length === 0) {
            mostrarPaciente(null);
            return ventaAviso('El paciente figura como dependiente, pero Med-SDI no devolvió un responsable vigente.');
        }
        mostrarPaciente(esDependiente && responsables.length === 1 ? responsables[0] : (esDependiente ? null : null));
        document.getElementById('ventaAsistenteAviso').classList.add('d-none');
    });

    const buscarPrestaciones = async () => {
        const buscar = document.getElementById('ventaAsistentePrestacionBuscar').value.trim();
        const contenedor = document.getElementById('ventaAsistentePrestaciones');
        if (buscar.length < 2) {
            contenedor.innerHTML = '';
            contenedor.classList.add('d-none');
            return;
        }
        const consultaActual = ++ventaPrestacionConsulta;
        contenedor.innerHTML = '<div class="list-group-item text-muted">Buscando prestaciones...</div>';
        contenedor.classList.remove('d-none');
        try {
            const data = await ventaJson(`{{ route('asistente.venta_bonos.prestaciones') }}?buscar=${encodeURIComponent(buscar)}`);
            if (consultaActual !== ventaPrestacionConsulta) return;
            contenedor.innerHTML = '';
            (data.registros || []).forEach(item => {
                const boton = document.createElement('button');
                boton.type = 'button';
                boton.className = 'list-group-item list-group-item-action text-start';
                const codigo = document.createElement('strong'); codigo.textContent = item.codigo || 'Sin código';
                const nombre = document.createElement('span'); nombre.textContent = ` · ${item.nombre}`;
                boton.append(codigo, nombre);
                boton.onclick = () => seleccionarPrestacion(item);
                contenedor.appendChild(boton);
            });
            if (!contenedor.children.length) contenedor.innerHTML = `<div class="list-group-item text-muted">${data.mensaje || 'No se encontraron prestaciones.'}</div>`;
        } catch (error) {
            if (consultaActual !== ventaPrestacionConsulta) return;
            contenedor.innerHTML = '<div class="list-group-item text-danger">No fue posible consultar las prestaciones.</div>';
        }
    };
    document.getElementById('ventaAsistentePrestacionBuscar')?.addEventListener('input', () => {
        clearTimeout(ventaPrestacionTimer);
        ventaPrestacionTimer = setTimeout(buscarPrestaciones, 300);
    });
    const seleccionarPrestacion = async item => {
        venta.prestacion = item;
        const resultados = document.getElementById('ventaAsistentePrestaciones'); resultados.innerHTML = ''; resultados.classList.add('d-none');
        const elegida = document.getElementById('ventaAsistentePrestacionElegida'); elegida.textContent = `${item.codigo} · ${item.nombre}`; elegida.classList.remove('d-none');
        document.getElementById('ventaAsistentePasoProfesional').classList.remove('d-none');
        const data = await ventaJson('{{ route('asistente.venta_bonos.especialidades') }}');
        llenarSelectVenta(ventaProfesion, data.registros || [], 'Todas las profesiones');
        ventaProfesion.disabled = !data.ok;
        const regiones = await ventaJson('{{ route('asistente.venta_bonos.regiones') }}');
        llenarSelectVenta(ventaRegion, regiones.registros || [], 'Todas las regiones');
        ventaRegion.disabled = !regiones.ok;
    };

    ventaRegion?.addEventListener('change', async () => {
        venta.seleccion = null;
        reiniciarHorarioVenta();
        document.getElementById('ventaAsistenteProfesionales').innerHTML = '';
        llenarSelectVenta(ventaCiudad, [], ventaRegion.value ? 'Todas las ciudades' : 'Primero seleccione una región');
        if (!ventaRegion.value) return;
        const data = await ventaJson(`{{ route('asistente.venta_bonos.ciudades') }}?id_region=${ventaRegion.value}`);
        llenarSelectVenta(ventaCiudad, data.registros || [], 'Todas las ciudades');
    });

    ventaCiudad?.addEventListener('change', () => {
        venta.seleccion = null;
        reiniciarHorarioVenta();
        document.getElementById('ventaAsistenteProfesionales').innerHTML = '';
    });

    ventaProfesion?.addEventListener('change', async () => {
        venta.seleccion = null;
        reiniciarHorarioVenta();
        document.getElementById('ventaAsistenteProfesionales').innerHTML = '';
        llenarSelectVenta(ventaEspecialidad, [], ventaProfesion.value ? 'Todas las especialidades' : 'Primero seleccione una profesión');
        llenarSelectVenta(ventaSubespecialidad, [], 'Primero seleccione una especialidad');
        if (!ventaProfesion.value) return;
        const data = await ventaJson(`{{ route('asistente.venta_bonos.tipo_especialidades') }}?id_especialidad=${ventaProfesion.value}`);
        llenarSelectVenta(ventaEspecialidad, data.registros || [], 'Todas las especialidades');
    });

    ventaEspecialidad?.addEventListener('change', async () => {
        venta.seleccion = null;
        reiniciarHorarioVenta();
        document.getElementById('ventaAsistenteProfesionales').innerHTML = '';
        llenarSelectVenta(ventaSubespecialidad, [], ventaEspecialidad.value ? 'Todas las subespecialidades' : 'Primero seleccione una especialidad');
        if (!ventaEspecialidad.value) return;
        const data = await ventaJson(`{{ route('asistente.venta_bonos.sub_tipo_especialidades') }}?id_tipo_especialidad=${ventaEspecialidad.value}`);
        llenarSelectVenta(ventaSubespecialidad, data.registros || [], 'Todas las subespecialidades');
    });

    ventaSubespecialidad?.addEventListener('change', () => {
        venta.seleccion = null;
        reiniciarHorarioVenta();
        document.getElementById('ventaAsistenteProfesionales').innerHTML = '';
    });

    document.getElementById('ventaAsistenteProfesionalBtn')?.addEventListener('click', async () => {
        const params = new URLSearchParams({incluir_todos_lugares: '1'});
        const nombre = document.getElementById('ventaAsistenteProfesionalBuscar').value.trim();
        if (ventaRegion.value) params.set('id_region', ventaRegion.value);
        if (ventaCiudad.value) params.set('id_ciudad', ventaCiudad.value);
        if (ventaProfesion.value) params.set('id_especialidad', ventaProfesion.value);
        if (ventaEspecialidad.value) params.set('id_tipo_especialidad', ventaEspecialidad.value);
        if (ventaSubespecialidad.value) params.set('id_sub_tipo_especialidad', ventaSubespecialidad.value);
        if (nombre) params.set('nombre_profesional', nombre);
        const contenedor = document.getElementById('ventaAsistenteProfesionales'); contenedor.innerHTML = '<div class="text-muted">Buscando profesionales...</div>';
        const data = await ventaJson(`{{ route('asistente.venta_bonos.profesionales') }}?${params}`); contenedor.innerHTML = '';
        (data.registros || []).forEach(prof => {
            const nombreCompleto = `${prof.nombre} ${prof.apellido_uno || ''} ${prof.apellido_dos || ''}`.trim();
            const esp = prof.nombre_sub_tipo_especialidad || prof.nombre_tipo_especialidad || prof.nombre_especialidad || ventaSubespecialidad.options[ventaSubespecialidad.selectedIndex]?.text || ventaEspecialidad.options[ventaEspecialidad.selectedIndex]?.text || ventaProfesion.options[ventaProfesion.selectedIndex]?.text || '';
            const col = document.createElement('div'); col.className = 'col-md-6';
            col.innerHTML = `<div class="card h-100"><div class="card-body"><strong>${nombreCompleto}</strong><div class="small text-muted mb-2">${esp}</div><div class="small fw-semibold mb-2">Seleccione un lugar de atención:</div><div class="venta-lugares"></div></div></div>`;
            const lugares = prof.lugares_atencion || [];
            lugares.forEach(lugar => { const b=document.createElement('button'); b.type='button'; b.className='btn btn-outline-success btn-sm me-1 mb-1'; b.textContent=lugar.nombre; b.onclick=()=>elegirProfesional({idProfesional:prof.id,nombreProfesional:nombreCompleto,idEspecialidad:ventaProfesion.value,especialidad:esp,idLugar:lugar.id,lugarNombre:lugar.nombre}); col.querySelector('.venta-lugares').appendChild(b); });
            if (!lugares.length) col.querySelector('.venta-lugares').innerHTML = '<span class="small text-muted">Sin lugares de atención disponibles.</span>';
            contenedor.appendChild(col);
        });
        if (!contenedor.children.length) contenedor.innerHTML = `<div class="alert alert-warning">${data.mensaje || 'No se encontraron profesionales.'}</div>`;
    });
    const elegirProfesional = async seleccion => {
        reiniciarHorarioVenta();
        venta.seleccion = seleccion;
        const elegido = document.getElementById('ventaAsistenteProfesionalElegido'); elegido.textContent = `${seleccion.nombreProfesional} · ${seleccion.especialidad} · ${seleccion.lugarNombre}`; elegido.classList.remove('d-none');
        const data = await ventaJson('{{ route('asistente.venta_bonos.cotizar') }}', {method:'POST', body:JSON.stringify({id_profesional:seleccion.idProfesional,id_lugar_atencion:seleccion.idLugar,id_prestacion:venta.prestacion.id,origen_prestacion:venta.prestacion.origen})});
        if (!data.ok) return ventaAviso(data.mensaje || 'No fue posible cotizar esta prestación.');
        venta.cotizacion = data.cotizacion; const moneda = v => new Intl.NumberFormat('es-CL',{style:'currency',currency:'CLP',maximumFractionDigits:0}).format(v || 0);
        document.getElementById('ventaAsistenteCotizacion').textContent = `Valor ${moneda(data.cotizacion.valor)} · Bonificación ${moneda(data.cotizacion.bonificacion)} · Copago ${moneda(data.cotizacion.copago)}`;
        document.getElementById('ventaAsistentePasoHora').classList.remove('d-none');
        cargarDiasLaboralesVenta().catch(() => {
            document.getElementById('ventaAsistenteDiasAtencion').textContent = 'No fue posible consultar los días de atención.';
            document.getElementById('ventaAsistenteFecha').disabled = true;
        });
    };

    document.getElementById('ventaAsistenteHorasBtn')?.addEventListener('click', async () => {
        const fecha = document.getElementById('ventaAsistenteFecha').value; if (!venta.seleccion || !fecha) return ventaAviso('Seleccione una fecha.');
        const p = new URLSearchParams({id_profesional:venta.seleccion.idProfesional,id_lugar:venta.seleccion.idLugar,fecha});
        const data = await ventaJson(`{{ route('asistente.venta_bonos.horas_disponibles') }}?${p}`); const contenedor=document.getElementById('ventaAsistenteHoras'); contenedor.innerHTML='';
        (data.registros || []).forEach(h => { const b=document.createElement('button'); b.type='button'; b.className='btn btn-outline-primary'; b.textContent=h.hora; b.onclick=()=>confirmarVenta(h.fecha_hora); contenedor.appendChild(b); });
        if (!contenedor.children.length) contenedor.innerHTML='<span class="text-muted">No hay horas disponibles para esta fecha.</span>';
    });
    const confirmarVenta = async fechaHora => {
        const fechaLegible = new Intl.DateTimeFormat('es-CL', {dateStyle:'long', timeStyle:'short'}).format(new Date(fechaHora));
        const confirmado = typeof swal === 'function'
            ? await swal({
                icon: 'warning',
                title: '¿Confirmar hora médica?',
                text: `${venta.paciente.nombre_completo} · ${venta.seleccion.nombreProfesional} · ${venta.seleccion.lugarNombre} · ${fechaLegible}`,
                buttons: ['Cancelar', 'Confirmar hora'],
                dangerMode: false,
                closeOnClickOutside: false,
            })
            : confirm('¿Confirmar reserva para este paciente?');
        if (!confirmado) return;
        const s=venta.seleccion,p=venta.prestacion;
        const data=await ventaJson('{{ route('asistente.venta_bonos.agendar') }}',{method:'POST',body:JSON.stringify({rut:venta.paciente.rut,titular_rut:venta.paciente.titular?.rut||null,id_profesional:s.idProfesional,nombre_profesional:s.nombreProfesional,id_especialidad:s.idEspecialidad||null,especialidad:s.especialidad,id_lugar:s.idLugar,lugar_nombre:s.lugarNombre,direccion:'',fecha_hora:fechaHora,id_prestacion:p.id,origen_prestacion:p.origen,prestacion_codigo:p.codigo,prestacion_nombre:p.nombre})});
        if (!data.ok) {
            if (typeof swal === 'function') await swal({icon:'error',title:'No fue posible reservar',text:data.mensaje || 'No fue posible reservar la hora.',button:'Aceptar'});
            else ventaAviso(data.mensaje || 'No fue posible reservar la hora.');
            return;
        }
        if (typeof swal === 'function') await swal({icon:'success',title:'Reserva realizada',text:`${data.mensaje} Bono ${data.voucher}.`,button:'Aceptar'}); else alert(data.mensaje);
        window.location.reload();
    };
});
</script>
@if(session('abrir_recepcion_modal') || $errors->any())
<script>document.addEventListener('DOMContentLoaded', function () { abrirModalAsistente('modalRecepcionAsistente'); });</script>
@endif
@if(session('abrir_autorizaciones_modal'))
<script>document.addEventListener('DOMContentLoaded', function () { abrirModalAsistente('modalAutorizacionesPaciente'); });</script>
@endif
@if(session('abrir_app_paciente_modal') && $autorizacionAppPaciente)
<script>document.addEventListener('DOMContentLoaded', function () { cerrarModalAsistente('modalRecepcionAsistente'); abrirModalAsistente('modalAppPacienteSimulada'); });</script>
@endif
@endsection
