<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SDI - Beneficiario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .patient-hero{position:relative;overflow:hidden;padding:34px;border-radius:28px;background:linear-gradient(125deg,#153d8d,#1e69ad 55%,#31bebe);color:#fff;box-shadow:0 24px 58px rgba(24,72,161,.22)}.patient-hero:after{content:"";position:absolute;width:280px;height:280px;border-radius:50%;right:-80px;top:-120px;background:#ffffff18}.patient-hero .eyebrow,.patient-hero h1{color:#fff}.patient-hero p{color:#ddf5ff;max-width:720px}.patient-profile{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}.patient-chip{padding:8px 12px;border:1px solid #ffffff44;border-radius:999px;background:#ffffff16;font-size:.85rem;font-weight:700}.journey-actions{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin:20px 0 28px}.journey-action{display:flex;flex-direction:column;min-height:210px;padding:18px;border:1px solid #d2dff1;border-radius:20px;background:#fff;text-decoration:none;color:#263b57;box-shadow:0 12px 30px rgba(24,72,161,.08);transition:.18s}.journey-action:hover{transform:translateY(-3px);border-color:#31bebe;color:#263b57}.journey-action button{border:0;background:transparent;text-align:left;padding:0;color:inherit}.journey-number{display:grid;place-items:center;width:32px;height:32px;border-radius:10px;background:#1848a1;color:#fff;font-weight:900}.journey-icon{font-size:28px;margin:14px 0 8px}.journey-action strong{font-size:1rem}.journey-action small{color:#6d7d91;line-height:1.4;margin-top:7px}.journey-go{margin-top:auto;padding-top:13px;color:#1848a1;font-weight:900;font-size:.82rem}.journey-action form{margin-top:auto}.journey-subactions{display:flex;gap:6px;flex-wrap:wrap;margin-top:auto;padding-top:10px}.journey-subactions a,.journey-subactions button{padding:7px 9px;border-radius:9px;background:#edf4ff;color:#1848a1;font-size:.72rem;font-weight:850;text-decoration:none}.authorization-choice{display:grid;grid-template-columns:1fr 1fr;gap:12px}.authorization-choice button{padding:16px;border:0;border-radius:14px;font-weight:900}.auth-approve{background:#31bebe;color:#fff}.auth-reject{background:#fff0f1;color:#a8333e}@media(max-width:1050px){.journey-actions{grid-template-columns:repeat(2,1fr)}.journey-action:last-child{grid-column:1/-1}}@media(max-width:620px){.patient-hero{padding:24px}.journey-actions{grid-template-columns:1fr}.journey-action:last-child{grid-column:auto}}
    </style>
</head>
<body>
@include('partials.demo_user_switcher')
<main class="shell">
    @php($qrRespaldo = $agendaOnlineResultado ?: $vouchers->first())
    <section class="patient-hero mb-4">
        <div class="eyebrow">Medichile · Portal del paciente</div>
        <h1 class="display-6 fw-bold mt-2 mb-2">Hola, {{ $user->name }}</h1>
        <p class="mb-0">Reserva tu hora, confirma el copago y llega al centro médico. La hora, el médico, el pago y el QR permanecen vinculados durante todo el recorrido.</p>
        <div class="patient-profile"><span class="patient-chip">Sesión demo-bono</span><span class="patient-chip">RUT {{ sdi_formatear_rut($user->rut) }}</span><span class="patient-chip">WhatsApp {{ $user->telefono ?: 'no registrado' }}</span><span class="patient-chip">Identidad autenticada sin solicitar clave</span></div>
    </section>

    <section aria-label="Acciones principales del paciente">
        <div class="journey-actions">
            <a class="journey-action" href="https://med-sdi.cl/Paciente/Reservar_Hora" target="_blank" rel="noopener">
                <span class="journey-number">1</span><span class="journey-icon">🌐</span><strong>Reservar en Med‑SDI</strong><small>Abre el portal real de reserva de horas del paciente.</small><span class="journey-go">Abrir portal externo →</span>
            </a>
            <div class="journey-action" role="button" tabindex="0" id="abrirAgendaOnlinePrincipal">
                <span class="journey-number">2</span><span class="journey-icon">📅</span><strong>Buscar y confirmar hora</strong><small>Busca la agenda, selecciona médico y confirma la reserva dentro del demo.</small><span class="journey-go">Comenzar reserva →</span>
            </div>
            <a class="journey-action" href="https://med-sdi.cl/Profesional/mi_agenda?lugares_atencion=69" target="_blank" rel="noopener">
                <span class="journey-number">3</span><span class="journey-icon">🩺</span><strong>Agenda profesional</strong><small>Consulta la vista real del profesional para el lugar de atención 69.</small><span class="journey-go">Abrir agenda externa →</span>
            </a>
            <div class="journey-action" role="button" tabindex="0" id="abrirAutorizacionDemo">
                <span class="journey-number">4</span><span class="journey-icon">📲</span><strong>Autorizar por App o WhatsApp</strong><small>Simula la aprobación o rechazo del copago antes de emitir el bono.</small><span class="journey-go">Simular autorización →</span>
            </div>
            <div class="journey-action">
                <span class="journey-number">5</span><span class="journey-icon">▦</span><strong>QR de respaldo y llegada</strong><small>El QR queda disponible en la agenda profesional; úsalo solo como respaldo en tótem o secretaría.</small>
                <div class="journey-subactions">
                    @if($qrRespaldo?->qr_token)<a href="{{ route('vouchers.qr', $qrRespaldo->qr_token) }}">Ver QR</a>@endif
                    <a href="{{ route('totem.local', ['tab' => 'autoatencion']) }}">Vista tótem</a>
                    <form method="POST" action="{{ route('demo.switch-user', 'asistente') }}">@csrf<button type="submit">Vista secretaría</button></form>
                </div>
            </div>
        </div>
    </section>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="eyebrow">Portal beneficiario</div>
            <h1 class="h3 mb-1">Hola, {{ $user->name }}</h1>
            <p class="text-muted mb-0">
                Entra por el login del sistema, valida tu telefono y compra el bono con verificacion de base externa.
            </p>
        </div>

        <div class="d-flex gap-2">
            <button type="button" id="abrirAgendaOnline" class="btn btn-success">
                Pedir hora online
            </button>
        </div>
    </div>

    @if(session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('otp_demo'))
        <div class="alert alert-warning">
            OTP de prueba del bono: <strong>{{ session('otp_demo') }}</strong>
            <span class="text-muted">Solo aparece en ambiente local.</span>
        </div>
    @endif

    @if(session('action_url'))
        <div class="alert alert-info qr-route">
            Entrega preparada:
            <a href="{{ session('action_url') }}" target="_blank" rel="noopener">abrir WhatsApp para enviar el QR</a>.
        </div>
    @endif

    @if(session('authorization_pending_token'))
        <div class="alert alert-warning">
            <h2 class="h6 mb-2">Compra pendiente de autorizacion en la app del beneficiario</h2>
            <p class="mb-2">
                Apruebe la compra en la app autorizadora del paciente. Luego presione continuar para generar el bono y preparar el QR.
            </p>
            <p class="small mb-2 qr-route">
                Token: <code>{{ session('authorization_pending_token') }}</code>
                @if(session('authorization_pending_expires'))
                    <br>Vence: {{ session('authorization_pending_expires') }}
                @endif
            </p>
            <form method="POST" action="{{ route('cliente.bonos.comprar.confirmar') }}" class="d-inline">
                @csrf
                <input type="hidden" name="cliente_authorization_token" value="{{ session('authorization_pending_token') }}">
                <button class="btn btn-success">
                    Ya autorice en mi app, generar bono
                </button>
            </form>
            <small class="d-block mt-2 text-muted">
                Demo local app: POST /api/cliente-autorizacion/responder con imei APP-DEMO-10211568, token y respuesta aprobar.
            </small>
        </div>
    @endif

    @if(session('agenda_authorization_pending_token'))
        <div class="alert alert-warning">
            <h2 class="h6 mb-2">Hora web pendiente de autorizacion en la app</h2>
            <p class="mb-2">
                Apruebe la solicitud de hora en la app autorizadora del paciente. Luego presione confirmar para registrar la hora solicitada.
            </p>
            <p class="small mb-2 qr-route">
                Token: <code>{{ session('agenda_authorization_pending_token') }}</code>
                @if(session('agenda_authorization_pending_expires'))
                    <br>Vence: {{ session('agenda_authorization_pending_expires') }}
                @endif
            </p>
            <form method="POST" action="{{ route('cliente.agenda.confirmar') }}" class="d-inline">
                @csrf
                <input type="hidden" name="cliente_authorization_token" value="{{ session('agenda_authorization_pending_token') }}">
                <button class="btn btn-success">
                    Ya autorice en mi app, registrar hora
                </button>
            </form>
            <small class="d-block mt-2 text-muted">
                Demo local app: POST /api/cliente-autorizacion/responder con imei APP-DEMO-10211568, token y respuesta aprobar.
            </small>
        </div>
    @endif

    <section class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card metric p-4">
                <span class="text-muted">Bonos asociados</span>
                <strong>{{ $vouchers->total() }}</strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card metric p-4">
                <span class="text-muted">Saldo disponible</span>
                <strong>${{ number_format($saldos->where('estado', 'disponible')->sum('monto'), 0, ',', '.') }}</strong>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card metric p-4">
                <span class="text-muted">Telefono validado</span>
                <strong class="fs-4">{{ $user->telefono ?: 'Sin telefono' }}</strong>
            </div>
        </div>
    </section>

    <section class="card p-4 mb-4 {{ config('demo.enabled') ? 'd-none' : '' }}">
        <div class="row g-4 align-items-start">
            <div class="col-lg-4">
                <div class="eyebrow">Compra segura</div>
                <h2 class="h5 mb-3">Validar y comprar bono</h2>
                <div class="secure-note">
                    <strong>Medida de seguridad:</strong>
                    antes de generar el bono se valida la relacion en base externa y ademas la app autorizadora del beneficiario
                    debe aprobar la compra. Si hay contradicciones, se bloquea y queda para auditoria.
                </div>
            </div>

            <div class="col-lg-8">
                <form method="POST" action="{{ route('cliente.bonos.comprar') }}" class="row g-3">
                    @csrf

                    <div class="col-md-6">
                        <label class="form-label">RUT beneficiario</label>
                        <input type="text" name="rut" id="rutBeneficiarioCompra" class="form-control" data-rut-input required
                               data-rut-autorizado="{{ $user->rut }}"
                               value="{{ old('rut', $user->rut) }}"
                               placeholder="10211568-6">
                        @error('rut') <small class="text-danger">{{ $message }}</small> @enderror
                        <div id="rutPrevisionError" class="alert alert-danger py-2 px-3 mt-2 mb-0 d-none" role="alert">
                            Usuario no encontrado. Comuníquese con su sistema de previsión.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Telefono autorizado</label>
                        <input type="text" class="form-control" value="{{ $user->telefono }}" readonly>
                        <small class="text-muted">El login de beneficiario usa OTP telefonico, no Google Authenticator.</small>
                    </div>

                    <div class="col-12">
                        <div class="alert alert-success mb-0" id="datosPersonaAutocompletados">
                            <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                                <strong>Datos autocompletados por RUT</strong>
                                <span class="badge text-bg-success">{{ $perfilPersona['origen'] }}</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-4"><small class="text-muted d-block">Paciente</small><strong>{{ $perfilPersona['nombre'] ?: 'Sin información' }}</strong></div>
                                <div class="col-md-2"><small class="text-muted d-block">Grupo de ingreso</small><strong>{{ $perfilPersona['grupo_ingreso'] ?: 'Sin información' }}</strong></div>
                                <div class="col-md-2"><small class="text-muted d-block">Edad</small><strong>{{ $perfilPersona['edad'] ? $perfilPersona['edad'].' años' : 'Sin información' }}</strong></div>
                                <div class="col-md-4"><small class="text-muted d-block">Dirección</small><strong>{{ $perfilPersona['direccion'] ?: 'Sin información' }}</strong></div>
                            </div>
                            <small class="d-block mt-2">Estos antecedentes se incorporarán al bono al avanzar el proceso.</small>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">1. Buscar profesional por RUT o nombre</label>
                        <input type="search" id="busquedaProfesionalCompra" class="form-control mb-2"
                               placeholder="Ej.: 11.111.111-1 o nombre del profesional" autocomplete="off">
                        <select name="profesional_id" id="profesionalCompra" class="form-select" required>
                            <option value="">Seleccione profesional</option>
                        </select>
                        <small class="text-muted">Solo aparecen profesionales con convenio vigente para este beneficiario.</small>
                        @error('profesional_id') <small class="text-danger d-block">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">2. Especialidad y prestación convenida</label>
                        <select name="servicio_id" id="especialidadCompra" class="form-select" required disabled>
                            <option value="">Primero seleccione un profesional</option>
                        </select>
                        @error('servicio_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-12">
                        <div id="resumenConvenioCompra" class="alert alert-info mb-0 d-none">
                            <div class="row g-2 text-center">
                                <div class="col-6 col-md-3"><small class="d-block text-muted">Nivel convenio</small><strong id="nivelConvenio">—</strong></div>
                                <div class="col-6 col-md-3"><small class="d-block text-muted">Valor prestación</small><strong id="valorConvenio">—</strong></div>
                                <div class="col-6 col-md-3"><small class="d-block text-muted">Cobertura</small><strong id="coberturaConvenio">—</strong></div>
                                <div class="col-6 col-md-3"><small class="d-block text-muted">Copago paciente</small><strong id="copagoConvenio" class="text-success">—</strong></div>
                            </div>
                        </div>
                        @if(empty($conveniosCompra))
                            <div class="alert alert-warning mb-0">No hay convenios vigentes disponibles para el RUT del beneficiario.</div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Envío obligatorio</label>
                        <input type="text" class="form-control mb-2" value="WhatsApp propio: {{ $user->telefono }}" readonly>
                        <label class="form-label small">Enviar además a</label>
                        <select name="destino_qr" class="form-select" required>
                            <option value="provider_whatsapp" @selected(old('destino_qr', 'provider_whatsapp') === 'provider_whatsapp')>WhatsApp del profesional</option>
                            <option value="medical_center_whatsapp" @selected(old('destino_qr') === 'medical_center_whatsapp')>WhatsApp del centro médico</option>
                        </select>
                        <small class="text-muted">Siempre se envía a WhatsApp propio y además al profesional o institución.</small>
                        @error('destino_qr') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Centro médico / secretaría seleccionada</label>
                        <input type="email" name="centro_medico_email" class="form-control"
                               value="{{ old('centro_medico_email') }}"
                               placeholder="recepcion@centromedico.cl">
                        <small class="text-muted">Relaciona el bono con paciente, profesional y lugar de atención. Si queda vacío se usa el email del profesional o la secretaría SDI.</small>
                        @error('centro_medico_email') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">WhatsApp centro médico</label>
                        <input type="text" name="centro_medico_telefono" class="form-control"
                               value="{{ old('centro_medico_telefono') }}"
                               placeholder="+56912345678">
                        <small class="text-muted">Si se informa, el centro recibe un enlace WhatsApp y el bono queda igualmente en recepción.</small>
                        @error('centro_medico_telefono') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Numero oficina / box</label>
                        <input type="text" name="office_number" class="form-control"
                               value="{{ old('office_number') }}"
                               placeholder="Box 3, oficina 204, etc.">
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <button class="btn btn-success w-100">
                            Validar usuario y comprar bono
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="card p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
            <div>
                <div class="eyebrow">Agenda online</div>
                <h2 class="h5 mb-1">Horas reservadas</h2>
                <p class="text-muted mb-0">
                    Las reservas confirmadas muestran el bono, profesional, hora y estado sincronizado con Medichile.
                </p>
            </div>
        </div>

        @if($agendas->count())
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Bono</th>
                            <th>Profesional</th>
                            <th>Solicitada</th>
                            <th>Confirmada</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agendas as $agenda)
                            <tr>
                                <td>{{ optional($agenda->voucher)->codigo ?: '-' }}</td>
                                <td>{{ optional($agenda->profesional)->nombre ?: optional($agenda->voucher)->prestador_nombre ?: '-' }}</td>
                                <td>{{ $agenda->fecha_hora_solicitada ? \Illuminate\Support\Carbon::parse($agenda->fecha_hora_solicitada)->format('d-m-Y H:i') : '-' }}</td>
                                <td>{{ $agenda->fecha_hora_confirmada ? \Illuminate\Support\Carbon::parse($agenda->fecha_hora_confirmada)->format('d-m-Y H:i') : 'Pendiente' }}</td>
                                <td><span class="badge text-bg-info">{{ $agenda->estado }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">Aun no hay horas solicitadas por agenda web.</p>
        @endif
    </section>

    <section class="card p-0 mb-4">
        <div class="p-4 border-bottom">
            <div class="eyebrow">Bonos comprados</div>
            <h2 class="h5 mb-0">Historial del beneficiario</h2>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Codigo</th>
                        <th>Servicio</th>
                        <th>Prestador</th>
                        <th>Paciente</th>
                        <th>Valor</th>
                        <th>Estado</th>
                        <th>Vence</th>
                        <th class="text-end">Accion</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                        <tr>
                            <td>
                                <strong>{{ $voucher->codigo }}</strong><br>
                                <small class="text-muted">ID {{ $voucher->id }}</small>
                            </td>
                            <td>{{ $voucher->tipo_servicio }}</td>
                            <td>{{ $voucher->prestador_nombre ?: optional($voucher->profesional)->nombre ?: '-' }}</td>
                            <td>{{ $voucher->mascota_nombre ?: $voucher->cliente_nombre }}</td>
                            <td>${{ number_format($voucher->valor, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $voucher->estado === 'activo' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $voucher->estado }}
                                </span>
                            </td>
                            <td>{{ $voucher->fecha_vencimiento ? \Illuminate\Support\Carbon::parse($voucher->fecha_vencimiento)->format('d-m-Y') : '-' }}</td>
                            <td class="text-end">
                                @if($voucher->qr_token)
                                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                                        <a href="{{ route('vouchers.qr', $voucher->qr_token) }}" class="btn btn-sm btn-outline-success">
                                            Ver QR
                                        </a>
                                        <a href="{{ route('vouchers.qr.lectorDemo', $voucher->qr_token) }}" class="btn btn-sm btn-success">
                                            Lector simulado
                                        </a>
                                    </div>
                                @else
                                    <span class="text-muted">Sin QR</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted p-5">
                                Todavia no hay bonos asociados a este beneficiario.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3">
            {{ $vouchers->links() }}
        </div>
    </section>

    <section class="card p-4">
        <div class="eyebrow">Devoluciones</div>
        <h2 class="h5 mb-3">Saldos del cliente</h2>

        @if($saldos->count())
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Monto</th>
                            <th>Estado</th>
                            <th>Origen</th>
                            <th>Descripcion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($saldos as $saldo)
                            <tr>
                                <td>{{ $saldo->created_at }}</td>
                                <td>${{ number_format($saldo->monto, 0, ',', '.') }}</td>
                                <td>{{ $saldo->estado }}</td>
                                <td>{{ $saldo->origen }}</td>
                                <td>{{ $saldo->descripcion }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted mb-0">No existen saldos por devolucion para este beneficiario.</p>
        @endif
    </section>
</main>

<div class="modal fade" id="modalAgendaOnline" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"><div class="modal-content border-0" style="border-radius:22px;overflow:hidden">
        <div class="modal-header text-white" style="background:#087f6f"><div><div class="eyebrow text-white-50">Agenda profesional de prueba</div><h2 class="h5 modal-title">Seleccione un horario disponible</h2></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body p-4">
            <div class="alert alert-info">La hora se reserva definitivamente al completar el pago simulado y generar el QR.</div>
            <div class="row g-3">
                @forelse($horariosOnline as $horario)
                    <div class="col-md-6 col-xl-4"><div class="border rounded-4 p-3 h-100">
                        <div class="eyebrow">{{ $horario->fecha_hora->translatedFormat('l d \d\e F') }}</div>
                        <div class="fs-4 fw-bold text-success">{{ $horario->fecha_hora->format('H:i') }}</div>
                        <strong>{{ $horario->profesional->nombre }}</strong><br><span class="text-muted">{{ $horario->servicio->nombre }}</span>
                        <hr><small class="d-block">{{ $horario->centro_nombre }}</small><small class="text-muted">{{ $horario->lugar_atencion }} · {{ $horario->centro_direccion }}</small>
                        <button type="button" class="btn btn-outline-success w-100 mt-3 seleccionar-horario-online" data-horario-id="{{ $horario->id }}">Seleccionar hora</button>
                    </div></div>
                @empty
                    <div class="col-12"><div class="alert alert-warning mb-0">No quedan horarios disponibles. Cargue nuevos horarios de demostración.</div></div>
                @endforelse
            </div>
        </div>
    </div></div>
</div>

<div class="modal fade" id="modalAgendaOnlinePaciente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content border-0" style="border-radius:22px;overflow:hidden">
        <div class="modal-header text-white" style="background:#087f6f"><div><div class="eyebrow text-white-50">Paso 2 de 3</div><h2 class="h5 modal-title">Completar datos del paciente</h2></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body p-4">
            <div id="resumenHorarioPaciente" class="alert alert-info"></div>
            <label class="form-label">RUT del paciente</label>
            <input type="text" id="rutAgendaOnline" class="form-control form-control-lg" value="{{ $user->rut }}" data-rut-input data-rut-autorizado="{{ $user->rut }}">
            <div id="errorRutAgendaOnline" class="alert alert-danger mt-2 d-none"></div>
            <div id="personaAgendaOnline" class="alert alert-success mt-3">
                <strong>Datos recuperados desde Personas MySQL</strong>
                <div class="row g-2 mt-1"><div class="col-md-4"><small class="d-block text-muted">Paciente</small>{{ $perfilPersona['nombre'] }}</div><div class="col-md-2"><small class="d-block text-muted">Clase</small>{{ $perfilPersona['grupo_ingreso'] }}</div><div class="col-md-2"><small class="d-block text-muted">Edad</small>{{ $perfilPersona['edad'] }} años</div><div class="col-md-4"><small class="d-block text-muted">Dirección</small>{{ $perfilPersona['direccion'] }}</div></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" id="confirmarDatosAgendaOnline" class="btn btn-success">Reservar hora</button></div>
    </div></div>
</div>

<div class="modal fade" id="modalAgendaOnlineCompra" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content border-0" style="border-radius:22px;overflow:hidden">
        <div class="modal-header text-white" style="background:#087f6f"><div><div class="eyebrow text-white-50">Paso 3 de 3</div><h2 class="h5 modal-title">Comprar bono y pagar copago</h2></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="{{ route('cliente.agenda-online.comprar') }}" id="formCompraAgendaOnline">@csrf
            <input type="hidden" name="horario_id" id="horarioAgendaOnlineId"><input type="hidden" name="rut" id="rutAgendaOnlineCompra">
            <div class="modal-body p-4">
                <div id="resumenCompraAgendaOnline" class="border rounded-4 p-3 mb-3"></div>
                <div class="row g-3"><div class="col-md-6"><label class="form-label">Método de pago</label><select name="metodo_pago" class="form-select" required><option value="tarjeta_demo_online">Tarjeta de prueba · pago simulado</option></select></div><div class="col-md-6"><label class="form-label">Resultado</label><input class="form-control" value="Aprobación automática del demo" readonly></div></div>
                <div class="secure-note mt-3">Al confirmar se genera el bono y su QR, se vinculan paciente, profesional, hora y centro médico, y queda disponible en recepción para la asistente.</div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Volver</button><button class="btn btn-success">Pagar copago y generar QR</button></div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="modalAutorizacionDemo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0" style="border-radius:22px;overflow:hidden">
        <div class="modal-header text-white" style="background:linear-gradient(110deg,#1848a1,#31bebe)"><div><div class="eyebrow text-white-50">Verificación de identidad y copago</div><h2 class="h5 modal-title">Autorizar desde App o WhatsApp</h2></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body p-4">
            <div class="secure-note mb-3"><strong>Solicitud segura:</strong> confirma paciente, médico, prestación, hora y monto antes de mover el copago.</div>
            <p class="mb-2"><strong>Paciente:</strong> {{ $user->name }} · {{ sdi_formatear_rut($user->rut) }}</p>
            <p class="mb-2"><strong>Canal:</strong> WhatsApp registrado o App Medichile</p>
            <p class="text-muted">En producción se usa un token de un solo uso con vencimiento. En esta demo puede aprobar o rechazar manualmente.</p>
            <div class="authorization-choice"><button type="button" class="auth-reject" id="rechazarAutorizacionDemo">No autorizar</button><button type="button" class="auth-approve" id="aprobarAutorizacionDemo">Aceptar y autorizar</button></div>
        </div>
    </div></div>
</div>

@if($agendaOnlineResultado)
<div class="modal fade" id="modalAgendaOnlineResultado" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0" style="border-radius:22px"><div class="modal-body text-center p-5"><div class="medichile-alert-icon" style="border-color:#48b88a;color:#198754">✓</div><h2 class="h4">Hora y bono confirmados</h2><p>{{ $agendaOnlineResultado->codigo }}</p><p class="text-muted">{{ optional($agendaOnlineResultado->agenda?->fecha_hora_confirmada)->format('d-m-Y H:i') }} · {{ $agendaOnlineResultado->prestador_nombre }}</p><div class="d-flex justify-content-center gap-2 flex-wrap"><a href="{{ route('vouchers.qr', $agendaOnlineResultado->qr_token) }}" class="btn btn-outline-success">Ver QR generado</a><a href="{{ route('vouchers.qr.lectorDemo', $agendaOnlineResultado->qr_token) }}" class="btn btn-success">Leer QR simulado</a><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button></div></div></div></div></div>
@endif

<div id="medichileRutAlert" class="medichile-alert-overlay d-none" role="dialog" aria-modal="true" aria-labelledby="medichileRutAlertTitle">
    <div class="medichile-alert-card">
        <div class="medichile-alert-icon" aria-hidden="true">!</div>
        <h2 id="medichileRutAlertTitle">Usuario no encontrado</h2>
        <p id="medichileRutAlertMessage">Comuníquese con su sistema de previsión.</p>
        <button type="button" id="cerrarMedichileRutAlert">Aceptar</button>
    </div>
</div>
<style>
.medichile-alert-overlay{position:fixed;inset:0;z-index:12000;background:rgba(0,0,0,.42);display:grid;place-items:center;padding:20px}.medichile-alert-card{width:min(430px,100%);background:#fff;border-radius:16px;padding:30px;text-align:center;box-shadow:0 18px 60px rgba(0,0,0,.28);animation:medichileAlertIn .18s ease-out}.medichile-alert-icon{width:72px;height:72px;border:4px solid #ef6b6b;border-radius:50%;display:grid;place-items:center;margin:0 auto 18px;color:#e24b4b;font-size:46px;font-weight:300;line-height:1}.medichile-alert-card h2{font-size:24px;margin:0 0 10px;color:#263238}.medichile-alert-card p{font-size:17px;color:#65727a;margin:0 0 24px}.medichile-alert-card button{min-width:120px;border:0;border-radius:8px;background:#198754;color:#fff;font-weight:700;padding:10px 20px}.medichile-alert-card button:hover{background:#146c43}@keyframes medichileAlertIn{from{opacity:0;transform:scale(.94)}to{opacity:1;transform:scale(1)}}
.modal.demo-modal-visible{display:block;background:rgba(5,33,30,.48);overflow-y:auto}.modal.demo-modal-visible .modal-dialog{animation:medichileAlertIn .18s ease-out}body.demo-modal-open{overflow:hidden}
</style>

<div class="modal fade" id="modalAgendaWeb" tabindex="-1" aria-labelledby="modalAgendaWebLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:22px; border:0;">
            <div class="modal-header" style="background:#087f6f; color:#fff;">
                <div>
                    <div class="eyebrow text-white-50">Agenda en linea</div>
                    <h5 class="modal-title" id="modalAgendaWebLabel">Pedir hora web</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="{{ route('cliente.agenda.solicitar') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info">
                        Esta solicitud valida el bono, la relacion beneficiario-profesional-servicio y solicita aprobacion en la app autorizadora antes de registrar la hora.
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bono activo</label>
                            <select name="voucher_id" class="form-select" required>
                                <option value="">Seleccione bono</option>
                                @foreach($vouchersAgenda as $voucherAgenda)
                                    <option value="{{ $voucherAgenda->id }}" @selected((string) old('voucher_id') === (string) $voucherAgenda->id)>
                                        {{ $voucherAgenda->codigo }} - {{ $voucherAgenda->tipo_servicio ?: 'Servicio' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('voucher_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Profesional</label>
                            <select name="profesional_id" class="form-select" required>
                                <option value="">Seleccione profesional</option>
                                @foreach($profesionales as $profesional)
                                    <option value="{{ $profesional->id }}" @selected((string) old('profesional_id') === (string) $profesional->id)>
                                        {{ $profesional->nombre }}{{ $profesional->especialidad ? ' - '.$profesional->especialidad : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('profesional_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fecha y hora solicitada</label>
                            <input type="datetime-local" name="fecha_hora_solicitada" class="form-control" required value="{{ old('fecha_hora_solicitada') }}">
                            @error('fecha_hora_solicitada') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estado inicial</label>
                            <input type="text" class="form-control" value="Hora solicitada - pendiente confirmacion" readonly>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observacion</label>
                            <textarea name="observacion" class="form-control" rows="3" placeholder="Motivo, preferencia de horario, sintomas u observaciones">{{ old('observacion') }}</textarea>
                            @error('observacion') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-success">Solicitar hora con autorizacion app</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('js/plugins/sweetalert.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const convenios = @json($conveniosCompra);
    const oldProfesional = @json((string) old('profesional_id'));
    const oldServicio = @json((string) old('servicio_id'));
    const buscador = document.getElementById('busquedaProfesionalCompra');
    const profesional = document.getElementById('profesionalCompra');
    const especialidad = document.getElementById('especialidadCompra');
    const resumen = document.getElementById('resumenConvenioCompra');
    const rutInput = document.getElementById('rutBeneficiarioCompra');
    const rutError = document.getElementById('rutPrevisionError');
    const datosPersona = document.getElementById('datosPersonaAutocompletados');
    const normalizarRut = value => String(value || '').toUpperCase().replace(/[^0-9K]/g, '');
    const rutAutorizado = normalizarRut(rutInput.dataset.rutAutorizado);
    function rutValido(rut) {
        const partes = normalizarRut(rut).match(/^(\d{7,8})([0-9K])$/);
        if (!partes) return false;
        let suma = 0;
        let multiplicador = 2;
        for (let i = partes[1].length - 1; i >= 0; i--) {
            suma += Number(partes[1][i]) * multiplicador;
            multiplicador = multiplicador === 7 ? 2 : multiplicador + 1;
        }
        const resultado = 11 - (suma % 11);
        const dv = resultado === 11 ? '0' : (resultado === 10 ? 'K' : String(resultado));
        return dv === partes[2];
    }

    let alertaRutAbierta = false;
    const alertaMedichile = document.getElementById('medichileRutAlert');
    const cerrarAlertaMedichile = document.getElementById('cerrarMedichileRutAlert');
    function mostrarAlertaMedichileLocal(titulo, mensaje) {
        document.getElementById('medichileRutAlertTitle').textContent = titulo;
        document.getElementById('medichileRutAlertMessage').textContent = mensaje;
        alertaMedichile.classList.remove('d-none');
        cerrarAlertaMedichile.focus();
    }
    function cerrarAlertaMedichileLocal() {
        alertaMedichile.classList.add('d-none');
        alertaRutAbierta = false;
        rutInput.focus();
    }
    cerrarAlertaMedichile.addEventListener('click', cerrarAlertaMedichileLocal);

    function mostrarAlertaMedichileUsuario(titulo, mensaje) {
        if (alertaRutAbierta) return;
        alertaRutAbierta = true;
        if (typeof swal === 'function') {
            swal({
                title: titulo,
                text: mensaje,
                icon: 'error',
                button: 'Aceptar',
                dangerMode: true,
            }).then(() => {
                alertaRutAbierta = false;
                rutInput.focus();
            });
        } else {
            mostrarAlertaMedichileLocal(titulo, mensaje);
        }
    }

    function validarRutBeneficiario(mostrarAlerta = false) {
        const rutIngresado = normalizarRut(rutInput.value);
        const esValido = rutValido(rutIngresado);
        const esAutorizado = esValido && rutIngresado === rutAutorizado;
        let titulo = '';
        let mensaje = '';
        if (!esValido) {
            titulo = 'RUT no válido';
            mensaje = 'Revise el número y el dígito verificador.';
        } else if (!esAutorizado) {
            titulo = 'Usuario no encontrado';
            mensaje = 'Comuníquese con su sistema de previsión.';
        }
        const tieneError = !esAutorizado;
        rutError.textContent = tieneError ? `${titulo}. ${mensaje}` : '';
        rutError.classList.toggle('d-none', !tieneError);
        rutInput.classList.toggle('is-invalid', tieneError);
        datosPersona.classList.toggle('d-none', !esAutorizado);
        rutInput.setCustomValidity(tieneError ? `${titulo}. ${mensaje}` : '');
        if (tieneError && mostrarAlerta) mostrarAlertaMedichileUsuario(titulo, mensaje);
        return esAutorizado;
    }
    const money = value => new Intl.NumberFormat('es-CL', {style: 'currency', currency: 'CLP', maximumFractionDigits: 0}).format(value || 0);
    const nivelLabel = value => String(value || 'nivel_1').replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());

    function profesionalesUnicos(filtro = '') {
        const texto = filtro.trim().toLocaleLowerCase('es');
        const mapa = new Map();
        convenios.forEach(item => {
            const searchable = `${item.profesional_nombre} ${item.profesional_rut}`.toLocaleLowerCase('es');
            if (!texto || searchable.includes(texto)) mapa.set(String(item.profesional_id), item);
        });
        profesional.innerHTML = '<option value="">Seleccione profesional</option>';
        mapa.forEach(item => {
            const option = new Option(`${item.profesional_nombre} · RUT ${item.profesional_rut}`, item.profesional_id);
            profesional.add(option);
        });
        if (oldProfesional && [...profesional.options].some(o => o.value === oldProfesional)) profesional.value = oldProfesional;
        cargarEspecialidades();
    }

    function cargarEspecialidades() {
        const opciones = convenios.filter(item => String(item.profesional_id) === profesional.value);
        especialidad.innerHTML = opciones.length
            ? '<option value="">Seleccione especialidad / prestación</option>'
            : '<option value="">Primero seleccione un profesional</option>';
        opciones.forEach(item => {
            const option = new Option(`${item.especialidad} · ${item.servicio_nombre} · ${nivelLabel(item.nivel)}`, item.servicio_id);
            option.dataset.convenio = JSON.stringify(item);
            especialidad.add(option);
        });
        especialidad.disabled = opciones.length === 0;
        if (oldServicio && opciones.some(i => String(i.servicio_id) === oldServicio)) especialidad.value = oldServicio;
        mostrarPrecio();
    }

    function mostrarPrecio() {
        const option = especialidad.selectedOptions[0];
        const item = option && option.dataset.convenio ? JSON.parse(option.dataset.convenio) : null;
        resumen.classList.toggle('d-none', !item);
        if (!item) return;
        document.getElementById('nivelConvenio').textContent = nivelLabel(item.nivel);
        document.getElementById('valorConvenio').textContent = money(item.valor);
        document.getElementById('coberturaConvenio').textContent = money(item.cobertura);
        document.getElementById('copagoConvenio').textContent = money(item.copago);
    }

    buscador.addEventListener('input', () => profesionalesUnicos(buscador.value));
    rutInput.addEventListener('input', () => validarRutBeneficiario(false));
    rutInput.addEventListener('blur', () => validarRutBeneficiario(true));
    rutInput.closest('form').addEventListener('submit', event => {
        if (!validarRutBeneficiario(true)) event.preventDefault();
    });
    validarRutBeneficiario(false);
    profesional.addEventListener('change', cargarEspecialidades);
    especialidad.addEventListener('change', mostrarPrecio);
    profesionalesUnicos();

    const horariosOnline = @json($horariosOnlineJson);
    let horarioOnlineSeleccionado = null;
    const modalHorario = document.getElementById('modalAgendaOnline');
    const modalPaciente = document.getElementById('modalAgendaOnlinePaciente');
    const modalCompra = document.getElementById('modalAgendaOnlineCompra');
    const modalAutorizacion = document.getElementById('modalAutorizacionDemo');
    const formCompraAgenda = document.getElementById('formCompraAgendaOnline');
    let compraAutorizada = false;
    let compraEsperandoAutorizacion = false;
    const rutAgenda = document.getElementById('rutAgendaOnline');
    const personaAgenda = document.getElementById('personaAgendaOnline');
    const errorRutAgenda = document.getElementById('errorRutAgendaOnline');
    const confirmarDatosAgenda = document.getElementById('confirmarDatosAgendaOnline');

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
    document.getElementById('abrirAgendaOnline').addEventListener('click', () => mostrarModalDemo(modalHorario));
    document.getElementById('abrirAgendaOnlinePrincipal')?.addEventListener('click', () => mostrarModalDemo(modalHorario));
    document.getElementById('abrirAgendaOnlinePrincipal')?.addEventListener('keydown', event => { if (event.key === 'Enter' || event.key === ' ') mostrarModalDemo(modalHorario); });
    document.getElementById('abrirAutorizacionDemo')?.addEventListener('click', () => { compraEsperandoAutorizacion = false; mostrarModalDemo(modalAutorizacion); });
    document.getElementById('abrirAutorizacionDemo')?.addEventListener('keydown', event => { if (event.key === 'Enter' || event.key === ' ') mostrarModalDemo(modalAutorizacion); });
    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', () => ocultarModalDemo(button.closest('.modal')));
    });

    document.querySelectorAll('.seleccionar-horario-online').forEach(button => {
        button.addEventListener('click', () => {
            horarioOnlineSeleccionado = horariosOnline.find(item => String(item.id) === button.dataset.horarioId);
            if (!horarioOnlineSeleccionado) return;
            document.getElementById('resumenHorarioPaciente').innerHTML = `<strong>${horarioOnlineSeleccionado.fecha}</strong><br>${horarioOnlineSeleccionado.profesional} · ${horarioOnlineSeleccionado.servicio}<br>${horarioOnlineSeleccionado.centro} · ${horarioOnlineSeleccionado.lugar}`;
            ocultarModalDemo(modalHorario);
            setTimeout(() => mostrarModalDemo(modalPaciente), 120);
        });
    });

    function validarRutAgendaOnline(mostrarAlerta = false) {
        const normalizado = normalizarRut(rutAgenda.value);
        const valido = rutValido(normalizado);
        const autorizado = valido && normalizado === normalizarRut(rutAgenda.dataset.rutAutorizado);
        const titulo = valido ? 'Usuario no encontrado' : 'RUT no válido';
        const mensaje = valido ? 'Comuníquese con su sistema de previsión.' : 'Revise el número y el dígito verificador.';
        personaAgenda.classList.toggle('d-none', !autorizado);
        errorRutAgenda.classList.toggle('d-none', autorizado);
        errorRutAgenda.textContent = autorizado ? '' : `${titulo}. ${mensaje}`;
        confirmarDatosAgenda.disabled = !autorizado;
        if (!autorizado && mostrarAlerta) mostrarAlertaMedichileUsuario(titulo, mensaje);
        return autorizado;
    }
    rutAgenda.addEventListener('input', () => validarRutAgendaOnline(false));
    rutAgenda.addEventListener('blur', () => validarRutAgendaOnline(true));
    validarRutAgendaOnline(false);

    confirmarDatosAgenda.addEventListener('click', () => {
        if (!horarioOnlineSeleccionado || !validarRutAgendaOnline(true)) return;
        document.getElementById('horarioAgendaOnlineId').value = horarioOnlineSeleccionado.id;
        document.getElementById('rutAgendaOnlineCompra').value = rutAgenda.value;
        document.getElementById('resumenCompraAgendaOnline').innerHTML = `<div class="row g-2"><div class="col-md-7"><strong>${horarioOnlineSeleccionado.servicio}</strong><br>${horarioOnlineSeleccionado.profesional}<br><span class="text-muted">${horarioOnlineSeleccionado.fecha} · ${horarioOnlineSeleccionado.centro} · ${horarioOnlineSeleccionado.lugar}</span></div><div class="col-md-5 text-md-end"><small class="text-muted d-block">Valor prestación</small><strong>${money(horarioOnlineSeleccionado.valor)}</strong><small class="text-muted d-block mt-2">Copago a pagar</small><strong class="text-success fs-5">${money(horarioOnlineSeleccionado.copago)}</strong></div></div>`;
        ocultarModalDemo(modalPaciente);
        setTimeout(() => mostrarModalDemo(modalCompra), 120);
    });

    formCompraAgenda?.addEventListener('submit', event => {
        if (compraAutorizada) return;
        event.preventDefault();
        compraEsperandoAutorizacion = true;
        ocultarModalDemo(modalCompra);
        setTimeout(() => mostrarModalDemo(modalAutorizacion), 120);
    });
    document.getElementById('rechazarAutorizacionDemo')?.addEventListener('click', () => {
        compraEsperandoAutorizacion = false;
        ocultarModalDemo(modalAutorizacion);
        if (typeof swal === 'function') swal({title:'Autorización rechazada',text:'No se realizó el cargo ni se reservó la hora. Puede volver a revisar los datos.',icon:'error',button:'Entendido',dangerMode:true});
    });
    document.getElementById('aprobarAutorizacionDemo')?.addEventListener('click', () => {
        ocultarModalDemo(modalAutorizacion);
        const continuar = () => {
            if (!compraEsperandoAutorizacion) return;
            compraAutorizada = true;
            formCompraAgenda.requestSubmit();
        };
        if (typeof swal === 'function') {
            swal({title:'Autorización aprobada',text:'Identidad verificada. Copago autorizado y depositado en la cuenta registrada del usuario. Se generará el bono.',icon:'success',button:'Continuar'}).then(continuar);
        } else {
            alert('Autorización aprobada. Copago depositado en la cuenta registrada del usuario.');
            continuar();
        }
    });

    @if($agendaOnlineResultado)
    setTimeout(() => mostrarModalDemo(document.getElementById('modalAgendaOnlineResultado')), 120);
    @endif

    const errorServidor = @json(session('error'));
    if (errorServidor && typeof swal === 'function') {
        swal({title: 'Validación de usuario', text: errorServidor, icon: 'error', button: 'Aceptar', dangerMode: true});
    }
});
</script>
</body>
</html>
