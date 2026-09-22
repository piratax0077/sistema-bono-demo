<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SDI - Beneficiario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite('resources/js/flatpickr.js')
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
</head>
<body>
@include('partials.demo_user_switcher')
<main class="shell">
    @php
        $qrRespaldo = $agendaOnlineResultado ?? $vouchers->first();
        $nombrePacienteReal = $pacienteMedsdi
            ? trim(implode(' ', array_filter([$pacienteMedsdi['nombres'] ?? null, $pacienteMedsdi['apellido_uno'] ?? null, $pacienteMedsdi['apellido_dos'] ?? null])))
            : $user->name;
        $rutPacienteReal = $pacienteMedsdi['rut'] ?? $user->rut;
        $telefonoPacienteReal = $pacienteMedsdi['telefono_uno'] ?? $user->telefono;
    @endphp
    <section class="patient-hero mb-4">
        <div class="eyebrow">Salud Digital integrada · Flujo demo paciente</div>
        <h1 class="display-6 fw-bold mt-2 mb-2 text-white">Hola, {{ $nombrePacienteReal }}</h1>
        <p class="mb-0">El paciente,reserva su hora,elije profesional en un centro médico. El sistema se conecta mediante api con FONASA (consulta si ambos están habilitados primer control); se produce el co-pago; el paciente debe autorizar mediante App o WhatsApp, si acepta: se genera un QR DINAMICO y que permanecen vinculados hasta el cobro por el profesional.</p>
        <p class="mb-0">SI EL PACIENTE RECHAZA SE ANULA LA OPERACIÓN Y EL DINERO DEL COPAGO SE DEPOSITA EN LA CUENTA DEL BENEFICIARIO SI NO TIENE SE DEJA COMO SALDO A FAVOR</p>
        <div class="patient-profile"><span class="patient-chip">Paciente Med-SDI #{{ $pacienteMedsdi['id'] ?? 'no disponible' }}</span><span class="patient-chip">RUT {{ sdi_formatear_rut($rutPacienteReal) }}</span><span class="patient-chip">WhatsApp {{ $telefonoPacienteReal ?: 'no registrado' }}</span>@if($pacienteMedsdi)<span class="patient-chip">{{ $pacienteMedsdi['email'] ?? 'Correo no registrado' }}</span>@endif</div>
    </section>

    <section aria-label="Acciones principales del paciente">
        <div class="journey-actions">
            <div class="journey-action" role="button" tabindex="0" id="abrirReservaMedsdi">
                <span class="journey-number">1</span><span class="journey-icon">📅</span><strong>Reservar hora (SDI)</strong><small>Busca profesionales reales del centro configurado vía la API de SDI y confirma la reserva.</small><span class="journey-go">Buscar profesional →</span>
            </div>
            <div class="journey-action" role="button" tabindex="0" id="abrirAutorizacionDemo">
                <span class="journey-number">2</span><span class="journey-icon">📲</span><strong>Autorizar por App o WhatsApp</strong><small>Simula la aprobación o rechazo del copago antes de emitir el bono.</small><span class="journey-go">Simular autorización →</span>
            </div>
            <div class="journey-action">
                <span class="journey-number">3</span><span class="journey-icon">▦</span><strong>QR de respaldo y Aviso de llegada</strong><small>El QR queda disponible en la agenda del profesional;  en tótem o secretaría.El que le llega por email es solo respaldo</small>
                <div class="journey-subactions">
                    @if($qrRespaldo && $qrRespaldo->qr_token && $qrRespaldo->estado === 'activo')
                        <a href="{{ route('vouchers.qr', $qrRespaldo->qr_token) }}">Ver QR</a>
                    @endif
                    <a href="{{ route('totem.local', ['tab' => 'autoatencion']) }}">Vista tótem</a>
                    <form method="POST" action="{{ route('demo.switch-user', 'asistente') }}">@csrf<button type="submit">Vista secretaría</button></form>
                </div>
            </div>
        </div>
    </section>

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="eyebrow">Portal beneficiario</div>
            <h1 class="h3 mb-1">Hola, {{ $nombrePacienteReal }}</h1>
            <p class="text-muted mb-0">
                Perfil autenticado en SDI · RUT {{ sdi_formatear_rut($rutPacienteReal) }} · {{ $telefonoPacienteReal ?: 'Teléfono no registrado' }}.
            </p>
        </div>
    </div>

    @if(! $perfilRemotoMedsdi['ok'])
        <div class="alert alert-warning">No fue posible cargar el perfil real desde SDI: {{ $perfilRemotoMedsdi['mensaje'] }}</div>
    @endif

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
                    Ya autorice en mi app, generar bono (Qr.)
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
                <strong class="fs-4">{{ $telefonoPacienteReal ?: 'Sin teléfono' }}</strong>
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
                        <input type="text" class="form-control" value="{{ $telefonoPacienteReal }}" readonly>
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
                        <input type="text" class="form-control mb-2" value="WhatsApp propio: {{ $telefonoPacienteReal }}" readonly>
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
                        <tr id="voucher-row-{{ $voucher->id }}">
                            <td>
                                <strong>{{ $voucher->codigo }}</strong><br>
                                <small class="text-muted">Bono #{{ $voucher->id }}</small>
                                @if(optional($voucher->agenda)->medichile_hora_medica_id)
                                    <br><small class="text-primary fw-semibold">Hora Med-SDI #{{ $voucher->agenda->medichile_hora_medica_id }}</small>
                                @endif
                            </td>
                            <td>{{ $voucher->tipo_servicio }}</td>
                            <td>{{ $voucher->prestador_nombre ?: optional($voucher->profesional)->nombre ?: '-' }}</td>
                            <td>{{ $voucher->mascota_nombre ?: $voucher->cliente_nombre }}</td>
                            <td>${{ number_format($voucher->valor, 0, ',', '.') }}</td>
                            <td>
                                <div id="voucher-estado-{{ $voucher->id }}">
                                    @include('partials.voucher_estado_celda', ['voucher' => $voucher])
                                </div>
                            </td>
                            <td>{{ $voucher->fecha_vencimiento ? \Illuminate\Support\Carbon::parse($voucher->fecha_vencimiento)->format('d-m-Y') : '-' }}</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end align-items-center flex-wrap gap-2">
                                    @if(optional($voucher->agenda)->medichile_hora_medica_id)
                                        <form method="POST" action="{{ route('cliente.medsdi.sincronizar_hora', $voucher) }}" class="m-0" data-medsdi-sync data-voucher-id="{{ $voucher->id }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-primary" type="submit" title="Actualizar estado Med-SDI">
                                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                                <span class="btn-label" aria-hidden="true">🔄</span>
                                                <span class="visually-hidden">Actualizar estado Med-SDI</span>
                                            </button>
                                        </form>
                                    @endif
                                    @if($voucher->estado === 'pendiente_confirmacion' && optional($voucher->agenda)->medichile_hora_medica_id)
                                        <form method="POST" action="{{ route('cliente.medsdi.confirmar_hora', $voucher) }}" class="m-0" data-confirm-action="¿Confirma que desea registrar esta hora médica para el paciente?" data-confirm-title="Confirmar hora" data-confirm-icon="info">
                                            @csrf
                                            <button class="btn btn-sm btn-primary" type="submit" title="Confirmar hora">
                                                <span aria-hidden="true">✅</span>
                                                <span class="visually-hidden">Confirmar hora</span>
                                            </button>
                                        </form>
                                    @elseif($voucher->estado === 'pendiente_pago')
                                        <button type="button" class="btn btn-sm btn-warning abrir-pago-bono" title="Pagar bono"
                                            data-action-url="{{ route('cliente.medsdi.simular_pago', $voucher) }}"
                                            data-codigo="{{ $voucher->codigo }}"
                                            data-profesional="{{ $voucher->prestador_nombre ?: optional($voucher->profesional)->nombre ?: '-' }}"
                                            data-valor="{{ $voucher->valor }}"
                                            data-copago="{{ $voucher->copago_usuario }}"
                                            data-bonificacion="{{ max($voucher->valor - $voucher->copago_usuario, 0) }}">
                                            <span aria-hidden="true">💳</span>
                                            <span class="visually-hidden">Pagar bono</span>
                                        </button>
                                    @elseif($voucher->estado === 'activo' && $voucher->qr_token)
                                        <a href="{{ route('vouchers.qr', $voucher->qr_token) }}" class="btn btn-sm btn-outline-success qr-resumen-modal-trigger" title="Ver QR"
                                           data-qr-datos-url="{{ route('vouchers.compartirDatos', $voucher->qr_token) }}"
                                           data-bono-codigo="{{ $voucher->codigo }}"
                                           data-bono-estado="{{ $voucher->estado }}"
                                           data-servicio="{{ $voucher->tipo_servicio ?: 'Atención médica' }}"
                                           data-profesional="{{ $voucher->prestador_nombre ?: 'Sin profesional asignado' }}"
                                           data-vencimiento="{{ optional($voucher->fecha_vencimiento)->format('d-m-Y') ?: 'Sin vencimiento' }}">
                                            <span aria-hidden="true">🔳</span>
                                            <span class="visually-hidden">Ver QR</span>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-primary" title="Compartir"
                                            data-share-voucher-url="{{ route('vouchers.compartirDatos', $voucher->qr_token) }}">
                                            <span aria-hidden="true">📤</span>
                                            <span class="visually-hidden">Compartir</span>
                                        </button>
                                        <a href="{{ route('vouchers.qr.lectorDemo', $voucher->qr_token) }}" class="btn btn-sm btn-success lector-simulado-modal-trigger" title="Lector simulado"
                                           data-bono-codigo="{{ $voucher->codigo }}"
                                           data-bono-estado="{{ $voucher->estado }}"
                                           data-paciente="{{ $voucher->beneficiario_nombre ?: $voucher->cliente_nombre }}"
                                           data-servicio="{{ $voucher->tipo_servicio ?: 'Atención médica' }}"
                                           data-profesional="{{ $voucher->prestador_nombre ?: 'Sin profesional asignado' }}"
                                           data-fecha-hora="{{ optional($voucher->agenda?->fecha_hora_confirmada ?: $voucher->agenda?->fecha_hora_solicitada)->format('d-m-Y H:i') ?: 'Pendiente' }}"
                                           data-hora-medsdi="{{ $voucher->agenda?->medichile_hora_medica_id ? '#'.$voucher->agenda->medichile_hora_medica_id : 'Sin sincronizar' }}"
                                           data-valor="${{ number_format($voucher->valor_total ?: $voucher->valor, 0, ',', '.') }}">
                                            <span aria-hidden="true">📷</span>
                                            <span class="visually-hidden">Lector simulado</span>
                                        </a>
                                    @elseif(!$voucher->qr_token)
                                        <span class="text-muted">Sin QR</span>
                                    @endif
                                </div>
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
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div><div class="eyebrow">Devoluciones</div><h2 class="h5 mb-0">Saldos del cliente</h2></div>
            <button type="button" class="btn btn-outline-primary" onclick="abrirCuentaBancaria()">🏦 Mis datos bancarios</button>
        </div>

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

<div id="modalLectorSimulado" class="lector-modal" hidden>
    <div class="lector-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="modalLectorSimuladoTitulo">
        <header class="lector-modal__header">
            <div><div class="small text-uppercase opacity-75 fw-bold">Validación segura del bono</div><h2 id="modalLectorSimuladoTitulo" class="h5 text-white mb-0">Lector QR simulado</h2><small id="modalLectorSimuladoCodigo" class="opacity-75"></small></div>
            <button type="button" class="btn-close btn-close-white" id="cerrarModalLectorSimulado" aria-label="Cerrar"></button>
        </header>
        <div class="lector-modal__body">
            <div class="lector-validation"><span class="lector-validation__icon">✓</span><div><strong>Lectura correcta y firma válida</strong><small>La integridad del bono fue verificada mediante su firma digital.</small></div></div>
            <p class="lector-privacy">Vista resumida para demostración. No expone RUT, dirección, cuenta bancaria ni datos clínicos.</p>
            <div class="lector-summary-grid">
                <section class="lector-summary-card"><div class="lector-summary-card__title dark">Identificación del bono</div><dl><dt>Código</dt><dd id="lectorDatoCodigo"></dd><dt>Estado</dt><dd><span class="lector-status" id="lectorDatoEstado"></span></dd><dt>Prestación</dt><dd id="lectorDatoServicio"></dd></dl></section>
                <section class="lector-summary-card"><div class="lector-summary-card__title blue">Paciente / beneficiario</div><dl><dt>Nombre</dt><dd id="lectorDatoPaciente"></dd><dt>Identidad</dt><dd><span class="lector-verified">✓ Validada</span></dd><dt>Autorización</dt><dd>Vinculada al bono</dd></dl></section>
                <section class="lector-summary-card"><div class="lector-summary-card__title green">Profesional asignado</div><dl><dt>Nombre</dt><dd id="lectorDatoProfesional"></dd><dt>Relación</dt><dd>Asociado a la atención</dd></dl></section>
                <section class="lector-summary-card"><div class="lector-summary-card__title cyan">Hora médica</div><dl><dt>Fecha y hora</dt><dd id="lectorDatoFecha"></dd><dt>Hora Med-SDI</dt><dd id="lectorDatoHoraMedsdi"></dd><dt>Valor</dt><dd><strong id="lectorDatoValor"></strong></dd></dl></section>
            </div>
            <div class="lector-trace"><span>1 · Bono emitido</span><span>2 · Identidad validada</span><span>3 · Hora vinculada</span><span>4 · Lectura verificada</span></div>
        </div>
    </div>
</div>
<style>
.lector-modal{position:fixed;inset:0;z-index:10050;display:grid;place-items:center;padding:18px;background:rgba(10,31,61,.66);backdrop-filter:blur(4px)}.lector-modal[hidden]{display:none}.lector-modal__dialog{display:flex;width:min(1100px,96vw);max-height:94vh;flex-direction:column;overflow:hidden;border-radius:22px;background:#f4f7fb;box-shadow:0 28px 90px rgba(5,22,52,.38)}.lector-modal__header{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:15px 20px;background:linear-gradient(110deg,#1848a1,#31bebe);color:#fff}.lector-modal__body{padding:22px;overflow-y:auto}.lector-validation{display:flex;gap:13px;align-items:center;padding:16px 18px;border-radius:15px;background:#d9f1e8;color:#07543e}.lector-validation__icon{display:grid;place-items:center;flex:0 0 38px;height:38px;border-radius:50%;background:#098764;color:#fff;font-size:21px;font-weight:900}.lector-validation strong,.lector-validation small{display:block}.lector-validation small{margin-top:3px;color:#357060}.lector-privacy{margin:12px 0 18px;color:#69798b;font-size:13px}.lector-summary-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:15px}.lector-summary-card{overflow:hidden;border:1px solid #d5e1ef;border-radius:17px;background:#fff}.lector-summary-card__title{padding:12px 16px;color:#fff;font-weight:900}.lector-summary-card__title.dark{background:#20262d}.lector-summary-card__title.blue{background:#176cf2}.lector-summary-card__title.green{background:#31bebe}.lector-summary-card__title.cyan{background:#16bede}.lector-summary-card dl{display:grid;grid-template-columns:minmax(115px,.75fr) 1.25fr;gap:8px 12px;margin:0;padding:17px}.lector-summary-card dt{font-weight:800}.lector-summary-card dd{min-width:0;margin:0;overflow-wrap:anywhere}.lector-status,.lector-verified{display:inline-block;border-radius:7px;padding:3px 8px;background:#24bdc9;color:#072f39;font-size:12px;font-weight:900;text-transform:uppercase}.lector-verified{background:#dff5ed;color:#087555}.lector-trace{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:16px}.lector-trace span{padding:11px;border-radius:10px;background:#e8f0fc;color:#1848a1;text-align:center;font-size:12px;font-weight:800}@media(max-width:700px){.lector-modal{padding:0}.lector-modal__dialog{width:100vw;max-height:none;height:100vh;border-radius:0}.lector-modal__header{padding:13px 15px}.lector-modal__body{padding:15px}.lector-summary-grid{grid-template-columns:1fr}.lector-trace{grid-template-columns:repeat(2,1fr)}}
</style>

<div id="modalQrPaciente" class="qr-paciente-modal" hidden>
    <div class="qr-paciente-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="modalQrPacienteTitulo">
        <header class="qr-paciente-modal__header"><div><div class="small text-uppercase opacity-75 fw-bold">Bono digital Medichile</div><h2 id="modalQrPacienteTitulo" class="h5 text-white mb-0">Mi código QR</h2></div><button type="button" class="btn-close btn-close-white" id="cerrarModalQrPaciente" aria-label="Cerrar"></button></header>
        <div class="qr-paciente-modal__body">
            <div class="qr-paciente-modal__visual"><div id="qrPacienteCargando" class="qr-paciente-modal__loading">Generando QR seguro…</div><img id="qrPacienteImagen" alt="Código QR del bono" hidden></div>
            <div class="qr-paciente-modal__info">
                <span class="qr-paciente-modal__badge">✓ Bono digital vigente</span>
                <h3 id="qrPacienteCodigo"></h3>
                <p class="text-muted">Presenta este código en recepción para identificar tu bono de manera rápida y segura.</p>
                <dl><dt>Prestación</dt><dd id="qrPacienteServicio"></dd><dt>Profesional</dt><dd id="qrPacienteProfesional"></dd><dt>Estado</dt><dd id="qrPacienteEstado"></dd><dt>Vigencia</dt><dd id="qrPacienteVencimiento"></dd></dl>
                <div class="qr-paciente-modal__security">🔒 El código contiene una referencia segura. Tus datos bancarios y clínicos no están visibles.</div>
            </div>
        </div>
    </div>
</div>
<style>
.qr-paciente-modal{position:fixed;inset:0;z-index:10051;display:grid;place-items:center;padding:18px;background:rgba(10,31,61,.66);backdrop-filter:blur(4px)}.qr-paciente-modal[hidden]{display:none}.qr-paciente-modal__dialog{width:min(850px,95vw);overflow:hidden;border-radius:22px;background:#fff;box-shadow:0 28px 90px rgba(5,22,52,.38)}.qr-paciente-modal__header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;background:linear-gradient(110deg,#1848a1,#31bebe);color:#fff}.qr-paciente-modal__body{display:grid;grid-template-columns:minmax(270px,.8fr) 1.2fr;gap:28px;align-items:center;padding:28px}.qr-paciente-modal__visual{display:grid;min-height:290px;place-items:center;padding:16px;border:1px solid #d8e3f0;border-radius:18px;background:#f7fbff}.qr-paciente-modal__visual img{width:min(280px,100%);height:auto}.qr-paciente-modal__loading{color:#607184;font-weight:800;text-align:center}.qr-paciente-modal__badge{display:inline-block;padding:6px 10px;border-radius:999px;background:#dff5ed;color:#087555;font-size:12px;font-weight:900}.qr-paciente-modal__info h3{margin:12px 0 8px;color:#1848a1;font-size:22px;overflow-wrap:anywhere}.qr-paciente-modal__info dl{display:grid;grid-template-columns:105px 1fr;gap:9px 12px;margin:20px 0}.qr-paciente-modal__info dt{font-weight:850}.qr-paciente-modal__info dd{margin:0}.qr-paciente-modal__security{padding:12px;border-radius:12px;background:#edf4ff;color:#31557d;font-size:13px}@media(max-width:700px){.qr-paciente-modal{padding:0}.qr-paciente-modal__dialog{width:100vw;height:100vh;border-radius:0;overflow-y:auto}.qr-paciente-modal__body{grid-template-columns:1fr;padding:18px}.qr-paciente-modal__visual{min-height:250px}}
</style>

@php
    $cuentaBanco = $cuentaBancariaMedsdi['cuenta'] ?? [];
    $pacienteCuenta = $cuentaBancariaMedsdi['paciente'] ?? [];
@endphp
<div class="modal fade bank-account-modal" id="modalCuentaBancaria" tabindex="-1" aria-labelledby="modalCuentaBancariaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0" style="border-radius:22px">
            <div class="modal-header text-white" style="background:linear-gradient(120deg,#1848a1,#31bebe);border-radius:22px 22px 0 0">
                <div><div class="small fw-bold text-uppercase opacity-75">Devoluciones Med-SDI</div><h2 class="h4 modal-title text-white mb-0" id="modalCuentaBancariaTitulo">Datos de cuenta bancaria</h2></div>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarCuentaBancaria()" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="{{ route('paciente.cuenta_bancaria.actualizar') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="cuenta_id" id="pacienteCuentaId" value="{{ old('cuenta_id', $cuentaBanco['id'] ?? '') }}">
                <div class="modal-body p-4">
                    @if(! $cuentaBancariaMedsdi['ok'])
                        <div class="alert alert-warning">{{ $cuentaBancariaMedsdi['mensaje'] }}</div>
                    @elseif(collect($cuentaBancariaMedsdi['cuentas'] ?? [])->contains(fn ($cuenta) => blank($cuenta['banco'] ?? null) || blank($cuenta['numero_cuenta'] ?? null)))
                        <div class="alert alert-warning">Hay cuentas históricas cuyos datos protegidos no pudieron recuperarse. Revisa la clave de cifrado histórica de Med-SDI antes de editarlas.</div>
                    @elseif(!$cuentaBanco)
                        <div class="alert alert-info">Aún no tienes una cuenta bancaria registrada. Completa los datos para futuras devoluciones.</div>
                    @else
                        <div class="alert alert-success">Cuenta obtenida desde Med-SDI. Puedes actualizarla a continuación.</div>
                    @endif
                    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <div><strong>Cuentas registradas</strong><span class="badge bg-light text-dark border ms-2">{{ count($cuentaBancariaMedsdi['cuentas'] ?? []) }}</span></div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="pacienteNuevaCuenta">+ Nueva cuenta</button>
                    </div>
                    <div class="bank-account-table table-responsive mb-4">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>#</th><th>Banco y cuenta</th><th>Tipo</th><th>Estado</th><th class="text-end">Acción</th></tr></thead>
                            <tbody>
                            @forelse($cuentaBancariaMedsdi['cuentas'] ?? [] as $indice => $cuenta)
                                <tr>
                                    <td><span class="bank-account-number">{{ $cuenta['numero'] ?? $indice + 1 }}</span></td>
                                    <td><strong>{{ $cuenta['banco'] ?: 'Banco no disponible' }}</strong><div class="small text-muted">{{ filled($cuenta['numero_cuenta'] ?? null) ? 'Terminada en '.substr((string) $cuenta['numero_cuenta'], -4) : 'Número no disponible' }}</div></td>
                                    <td>{{ $cuenta['tipo_cuenta'] ?: 'No disponible' }}</td>
                                    <td>@if(!empty($cuenta['principal']))<span class="badge bg-success">Principal</span>@else<span class="badge bg-secondary">Secundaria</span>@endif</td>
                                    <td class="text-end"><div class="d-inline-flex flex-wrap justify-content-end gap-1"><button type="button" class="btn btn-sm btn-outline-primary paciente-editar-cuenta" data-cuenta-id="{{ $cuenta['id'] }}">Editar</button><button type="button" class="btn btn-sm btn-outline-info paciente-autorizar-cuenta" data-cuenta-id="{{ $cuenta['id'] }}">Autorizar App</button><button type="button" class="btn btn-sm btn-outline-danger paciente-eliminar-cuenta" data-cuenta-id="{{ $cuenta['id'] }}">Eliminar cuenta</button></div></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No hay cuentas bancarias registradas.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-8"><label class="form-label fw-bold">Titular</label><input class="form-control" name="titular" value="{{ old('titular', $cuentaBanco['titular'] ?? $pacienteCuenta['nombre'] ?? $nombrePacienteReal) }}" required></div>
                        <div class="col-md-4"><label class="form-label fw-bold">RUT del titular</label><input class="form-control" value="{{ sdi_formatear_rut($cuentaBanco['rut'] ?? $pacienteCuenta['rut'] ?? $rutPacienteReal) }}" readonly></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Banco</label><select class="form-select" name="banco_id" required><option value="">Seleccione</option>@foreach($cuentaBancariaMedsdi['bancos'] ?? [] as $banco)<option value="{{ $banco['id'] }}" @selected((string) old('banco_id', $cuentaBanco['banco_id'] ?? '') === (string) $banco['id'])>{{ $banco['nombre'] }}</option>@endforeach</select></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Tipo de cuenta</label><select class="form-select" name="tipo_cuenta" required><option value="">Seleccione</option>@foreach($cuentaBancariaMedsdi['tipos_cuenta'] ?? [] as $tipo)<option value="{{ $tipo['descripcion'] }}" @selected(old('tipo_cuenta', $cuentaBanco['tipo_cuenta'] ?? '') === $tipo['descripcion'])>{{ $tipo['descripcion'] }}</option>@endforeach</select></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Número de cuenta</label><input class="form-control" name="numero_cuenta" value="{{ old('numero_cuenta', $cuentaBanco['numero_cuenta'] ?? '') }}" autocomplete="off" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Correo para notificaciones</label><input type="email" class="form-control" name="email" value="{{ old('email', $cuentaBanco['email'] ?? $pacienteCuenta['email'] ?? $pacienteMedsdi['email'] ?? '') }}" required></div>
                    </div>
                    <p class="small text-muted mt-3 mb-0">Estos datos se almacenan cifrados en Med-SDI. Al guardar una cuenta, quedará como principal y las demás pasarán a secundarias.</p>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" onclick="cerrarCuentaBancaria()">Cancelar</button><button class="btn btn-primary" @disabled(! $cuentaBancariaMedsdi['ok'])>Guardar cambios</button></div>
            </form>
        </div>
    </div>
</div>

<script>
const modalQrPaciente = document.getElementById('modalQrPaciente');
const qrPacienteImagen = document.getElementById('qrPacienteImagen');
async function abrirQrPaciente(datos) {
    if (!modalQrPaciente || !qrPacienteImagen) return;
    const asignar = (id, valor) => { const elemento = document.getElementById(id); if (elemento) elemento.textContent = valor || 'Sin dato'; };
    asignar('qrPacienteCodigo', datos.bonoCodigo);
    asignar('qrPacienteServicio', datos.servicio);
    asignar('qrPacienteProfesional', datos.profesional);
    asignar('qrPacienteEstado', String(datos.bonoEstado || '').replaceAll('_', ' '));
    asignar('qrPacienteVencimiento', datos.vencimiento);
    qrPacienteImagen.hidden = true;
    qrPacienteImagen.removeAttribute('src');
    const cargando = document.getElementById('qrPacienteCargando');
    cargando.hidden = false;
    cargando.textContent = 'Generando QR seguro…';
    modalQrPaciente.hidden = false;
    document.body.classList.add('modal-open');
    document.getElementById('cerrarModalQrPaciente')?.focus();
    try {
        const response = await fetch(datos.qrDatosUrl, {headers:{'Accept':'application/json'}});
        const payload = await response.json();
        if (!response.ok || !payload.qr_image_url) throw new Error(payload.message || 'No fue posible generar el QR.');
        qrPacienteImagen.src = payload.qr_image_url;
        qrPacienteImagen.hidden = false;
        cargando.hidden = true;
    } catch (error) {
        cargando.textContent = error.message || 'No fue posible generar el QR.';
    }
}
function cerrarQrPaciente() {
    if (!modalQrPaciente) return;
    modalQrPaciente.hidden = true;
    qrPacienteImagen?.removeAttribute('src');
    document.body.classList.remove('modal-open');
}
document.querySelectorAll('.qr-resumen-modal-trigger').forEach(link => link.addEventListener('click', event => {
    event.preventDefault();
    abrirQrPaciente(link.dataset);
}));
document.getElementById('cerrarModalQrPaciente')?.addEventListener('click', cerrarQrPaciente);
modalQrPaciente?.addEventListener('click', event => { if (event.target === modalQrPaciente) cerrarQrPaciente(); });

const modalLectorSimulado = document.getElementById('modalLectorSimulado');
function abrirLectorSimulado(datos) {
    if (!modalLectorSimulado) return;
    const asignar = (id, valor) => { const elemento = document.getElementById(id); if (elemento) elemento.textContent = valor || 'Sin dato'; };
    asignar('modalLectorSimuladoCodigo', datos.bonoCodigo);
    asignar('lectorDatoCodigo', datos.bonoCodigo);
    asignar('lectorDatoEstado', String(datos.bonoEstado || '').replaceAll('_', ' '));
    asignar('lectorDatoServicio', datos.servicio);
    asignar('lectorDatoPaciente', datos.paciente);
    asignar('lectorDatoProfesional', datos.profesional);
    asignar('lectorDatoFecha', datos.fechaHora);
    asignar('lectorDatoHoraMedsdi', datos.horaMedsdi);
    asignar('lectorDatoValor', datos.valor);
    modalLectorSimulado.hidden = false;
    document.body.classList.add('modal-open');
    document.getElementById('cerrarModalLectorSimulado')?.focus();
}
function cerrarLectorSimulado() {
    if (!modalLectorSimulado) return;
    modalLectorSimulado.hidden = true;
    document.body.classList.remove('modal-open');
}
document.querySelectorAll('.lector-simulado-modal-trigger').forEach(link => link.addEventListener('click', event => {
    event.preventDefault();
    abrirLectorSimulado(link.dataset);
}));
document.getElementById('cerrarModalLectorSimulado')?.addEventListener('click', cerrarLectorSimulado);
modalLectorSimulado?.addEventListener('click', event => { if (event.target === modalLectorSimulado) cerrarLectorSimulado(); });
document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    if (!modalQrPaciente?.hidden) cerrarQrPaciente();
    else if (!modalLectorSimulado?.hidden) cerrarLectorSimulado();
});

function abrirCuentaBancaria() {
    const modal = document.getElementById('modalCuentaBancaria');
    if (!modal) return;
    modal.style.display = 'block';
    modal.classList.add('show');
    modal.removeAttribute('aria-hidden');
    modal.setAttribute('aria-modal', 'true');
    document.body.classList.add('modal-open');
    if (!document.getElementById('fondoCuentaBancaria')) {
        const fondo = document.createElement('div');
        fondo.id = 'fondoCuentaBancaria';
        fondo.className = 'modal-backdrop fade show';
        fondo.addEventListener('click', cerrarCuentaBancaria);
        document.body.appendChild(fondo);
    }
}
function cerrarCuentaBancaria() {
    const modal = document.getElementById('modalCuentaBancaria');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');
    }
    document.body.classList.remove('modal-open');
    document.getElementById('fondoCuentaBancaria')?.remove();
}
const cuentasBancariasPaciente = @json($cuentaBancariaMedsdi['cuentas'] ?? []);
function cargarCuentaBancariaPaciente(cuenta) {
    const form = document.querySelector('#modalCuentaBancaria form');
    if (!form) return;
    form.querySelector('[name="cuenta_id"]').value = cuenta?.id || '';
    form.querySelector('[name="titular"]').value = cuenta?.titular || @json($pacienteCuenta['nombre'] ?? $nombrePacienteReal);
    form.querySelector('[name="banco_id"]').value = cuenta?.banco_id || '';
    form.querySelector('[name="tipo_cuenta"]').value = cuenta?.tipo_cuenta || '';
    form.querySelector('[name="numero_cuenta"]').value = cuenta?.numero_cuenta || '';
    form.querySelector('[name="email"]').value = cuenta?.email || @json($pacienteCuenta['email'] ?? $pacienteMedsdi['email'] ?? '');
}
document.querySelectorAll('.paciente-editar-cuenta').forEach(button => button.addEventListener('click', () => cargarCuentaBancariaPaciente(cuentasBancariasPaciente.find(cuenta => String(cuenta.id) === button.dataset.cuentaId))));
document.querySelectorAll('.paciente-autorizar-cuenta').forEach(button => button.addEventListener('click', async () => {
    const confirmado = typeof swal === 'function' ? await swal({title:'¿Autorizar esta cuenta en la App?',text:'Se enviará una confirmación de los datos bancarios a la App del paciente.',icon:'warning',buttons:['Cancelar','Autorizar App']}) : confirm('¿Autorizar esta cuenta en la App?');
    if (!confirmado) return;
    button.disabled = true;
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('#modalCuentaBancaria input[name="_token"]')?.value;
        if (!csrf) throw new Error('No se encontró el token de seguridad. Recarga la página e inténtalo nuevamente.');
        const response = await fetch('{{ route('paciente.cuenta_bancaria.notificar') }}', {method:'POST',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({cuenta_id:button.dataset.cuentaId})});
        const data = await response.json();
        if (!response.ok) throw new Error(data.mensaje || 'No fue posible solicitar la autorización.');
        if (typeof swal === 'function') await swal({title:'Autorización enviada',text:data.mensaje,icon:'success',button:'Aceptar'}); else alert(data.mensaje);
    } catch (error) {
        if (typeof swal === 'function') await swal({title:'No se pudo autorizar',text:error.message,icon:'error',button:'Aceptar'}); else alert(error.message);
    } finally { button.disabled = false; }
}));
document.querySelectorAll('.paciente-eliminar-cuenta').forEach(button => button.addEventListener('click', async () => {
    const confirmado = typeof swal === 'function' ? await swal({title:'¿Eliminar esta cuenta bancaria?',text:'La cuenta dejará de estar disponible y se enviará una confirmación a la App del paciente.',icon:'warning',buttons:['Cancelar','Eliminar y confirmar']}) : confirm('¿Eliminar esta cuenta y enviar confirmación a la App?');
    if (!confirmado) return;
    button.disabled = true;
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('#modalCuentaBancaria input[name="_token"]')?.value;
        if (!csrf) throw new Error('No se encontró el token de seguridad. Recarga la página e inténtalo nuevamente.');
        const response = await fetch('{{ route('paciente.cuenta_bancaria.eliminar') }}', {method:'DELETE',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({cuenta_id:button.dataset.cuentaId})});
        const data = await response.json();
        if (!response.ok) throw new Error(data.mensaje || 'No fue posible eliminar la cuenta bancaria.');
        if (typeof swal === 'function') await swal({title:'Cuenta eliminada',text:data.mensaje,icon:'success',button:'Aceptar'}); else alert(data.mensaje);
        window.location.reload();
    } catch (error) {
        button.disabled = false;
        if (typeof swal === 'function') await swal({title:'No se pudo eliminar',text:error.message,icon:'error',button:'Aceptar'}); else alert(error.message);
    }
}));
document.getElementById('pacienteNuevaCuenta')?.addEventListener('click', () => {
    cargarCuentaBancariaPaciente(null);
});
</script>

<div class="share-modal-backdrop" id="shareVoucherModal" hidden>
    <div class="share-modal" role="dialog" aria-modal="true" aria-labelledby="shareVoucherTitle">
        <div class="share-modal-header">
            <div>
                <div class="section-title mb-2">Envío digital sin papel</div>
                <h3 class="fw-bold mb-2" id="shareVoucherTitle">Compartir QR Medichile</h3>
                <p class="text-muted mb-0">
                    Selecciona uno o más destinatarios y uno o más métodos de envío. El sistema prepara la imagen QR
                    para compartirla sin imprimir papel.
                </p>
            </div>
            <button type="button" class="share-close" id="closeShareVoucherModal" aria-label="Cerrar">×</button>
        </div>

        <div class="share-modal-body">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="section-title mb-2">1. Destinatarios</div>
                    <div class="share-grid" id="shareVoucherRecipients"></div>
                </div>

                <div class="col-lg-5">
                    <div class="section-title mb-2">2. Imagen QR</div>
                    <div class="qr-share-preview mb-3">
                        <img id="shareVoucherImage" src="" alt="Imagen QR">
                        <div class="d-flex gap-2 justify-content-center flex-wrap mt-3">
                            <a href="#" id="shareVoucherDownload" class="btn btn-sm btn-success" download>Descargar imagen</a>
                            <button type="button" class="btn btn-sm btn-outline-dark" id="copyVoucherImage">Copiar imagen</button>
                        </div>
                    </div>

                    <div class="section-title mb-2">3. Métodos</div>
                    <div class="share-channel-list mb-3">
                        <label class="share-channel">
                            <input type="checkbox" class="share-channel-input" value="whatsapp" checked>
                            WhatsApp
                        </label>
                        <label class="share-channel">
                            <input type="checkbox" class="share-channel-input" value="email">
                            Email
                        </label>
                        <label class="share-channel">
                            <input type="checkbox" class="share-channel-input" value="copy">
                            Copiar imagen
                        </label>
                    </div>

                    <div class="alert alert-info py-2 px-3 small" role="note">
                        Si no tiene instalada la aplicación Med-SDI, verifique que el número de WhatsApp registrado sea correcto y autentíquese por ese medio.
                    </div>

                    <label class="form-label fw-semibold" for="shareVoucherMessage">Mensaje QR editable</label>
                    <textarea class="form-control" id="shareVoucherMessage" rows="9"></textarea>
                    <p class="share-muted mt-2 mb-0">
                        WhatsApp Web no adjunta imágenes desde un enlace automático; descarga o copia la imagen QR y adjúntala en la conversación.
                    </p>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap mt-4">
                <button type="button" class="btn btn-success" id="prepareShareVoucher">Preparar envíos</button>
                <button type="button" class="btn btn-outline-dark" id="copyVoucherLink">Copiar imagen QR</button>
                <button type="button" class="btn btn-outline-secondary" id="cancelShareVoucher">Cerrar</button>
            </div>

            <div class="share-results mt-4" id="shareVoucherResults" hidden>
                <div class="section-title mb-2">Envíos QR preparados</div>
                <div id="shareVoucherResultsList"></div>
            </div>
        </div>
    </div>
</div>

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

<div class="modal fade" id="modalReservaMedsdi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"><div class="modal-content border-0" style="border-radius:22px;overflow:hidden">
        <div class="modal-header text-white" style="background:#1848a1">
            <div><div class="eyebrow text-white-50">Conectado a Med-SDI</div><h2 class="h5 modal-title">Buscar profesional y reservar hora</h2></div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
            <div id="medsdiEstadoServicio" class="alert alert-warning d-none"></div>

            <div class="border rounded-4 p-3 mb-3 bg-light">
                <div class="eyebrow mb-2">1 · Beneficiario</div>
                @if($pacienteMedsdi)
                    <strong>{{ trim(data_get($pacienteMedsdi, 'nombres').' '.data_get($pacienteMedsdi, 'apellido_uno').' '.data_get($pacienteMedsdi, 'apellido_dos')) }}</strong>
                    <div class="small text-muted">RUT {{ sdi_formatear_rut(data_get($pacienteMedsdi, 'rut')) }} · Validación simulada FONASA</div>
                    <div class="small text-success fw-semibold mt-2">✓ Beneficiario autenticado en Med-SDI</div>
                @else
                    <div class="text-danger">{{ data_get($perfilRemotoMedsdi, 'mensaje', 'No fue posible obtener el beneficiario.') }}</div>
                @endif
            </div>

            <div class="border rounded-4 p-3 mb-3">
                <div id="medsdiPrestacionForm">
                    <div class="eyebrow mb-2">2 · Prestación FONASA</div>
                    <label class="form-label small" for="medsdiPrestacionBuscar">Busca por nombre o código</label>
                    <div class="input-group">
                        <input type="search" id="medsdiPrestacionBuscar" class="form-control" autocomplete="off" placeholder="Ej.: consulta médica o 0101001" @disabled(!$pacienteMedsdi)>
                        <button type="button" id="medsdiPrestacionBuscarBtn" class="btn btn-info text-white" @disabled(!$pacienteMedsdi)>Buscar</button>
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

            <form id="medsdiFormConfirmar" method="POST" action="{{ route('cliente.medsdi.agendar') }}" class="d-none mt-4 pt-3 border-top">
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
                    <label class="form-label">RUT del paciente autenticado en Med-SDI</label>
                    <input type="text" name="rut" id="medsdiRutPaciente" class="form-control" value="{{ data_get($pacienteMedsdi, 'rut', '') }}" readonly>
                    @if($pacienteMedsdi)
                        <div class="form-text">{{ trim(data_get($pacienteMedsdi, 'nombres').' '.data_get($pacienteMedsdi, 'apellido_uno').' '.data_get($pacienteMedsdi, 'apellido_dos')) }}</div>
                    @else
                        <div class="text-danger small mt-1">{{ data_get($perfilRemotoMedsdi, 'mensaje', 'No fue posible obtener el paciente autenticado.') }}</div>
                    @endif
                </div>
                <button type="submit" class="btn btn-success w-100" @disabled(!$pacienteMedsdi)>Confirmar reserva y generar bono</button>
            </form>
        </div>
    </div></div>
</div>

<div class="modal fade" id="modalAutorizacionDemo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0" style="border-radius:22px;overflow:hidden">
        <div class="modal-header text-white" style="background:linear-gradient(110deg,#1848a1,#31bebe)"><div><div class="eyebrow text-white-50">Verificación de identidad y copago</div><h2 class="h5 modal-title">Autorizar desde App o WhatsApp</h2></div><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body p-4">
            <div class="secure-note mb-3"><strong>Solicitud segura:</strong> confirma paciente, médico, prestación, hora y monto antes de mover el copago.</div>
            <p class="mb-2"><strong>Paciente:</strong> {{ $nombrePacienteReal }} · {{ sdi_formatear_rut($rutPacienteReal) }}</p>
            <p class="mb-2"><strong>Canal:</strong> WhatsApp registrado o App Medichile</p>
            <p class="text-muted">En producción se usa un token de un solo uso con vencimiento. En esta demo puede aprobar o rechazar manualmente.</p>
            <div class="border rounded-4 p-3 mb-4 bg-light">
                <label for="bonoNotificacionAndroid" class="form-label fw-bold">Notificar un bono recién adquirido</label>
                <select id="bonoNotificacionAndroid" class="form-select mb-2" @disabled($bonosRecientesNotificables->isEmpty())>
                    <option value="">Seleccione un bono reciente</option>
                    @foreach($bonosRecientesNotificables as $bonoReciente)
                        <option value="{{ $bonoReciente->id }}">
                            {{ $bonoReciente->codigo }} · {{ $bonoReciente->tipo_servicio ?: 'Bono médico' }} · {{ $bonoReciente->created_at->format('d-m-Y H:i') }}
                        </option>
                    @endforeach
                </select>
                <div class="d-grid">
                    <button type="button" id="enviarBonoNotificacionAndroid" class="btn btn-outline-primary" @disabled($bonosRecientesNotificables->isEmpty())>
                        Enviar notificación a la App Android
                    </button>
                </div>
                <small class="text-muted d-block mt-2">
                    @if($bonosRecientesNotificables->isEmpty())
                        No hay bonos adquiridos durante los últimos 30 días.
                    @else
                        El aviso se guardará en Med-SDI y se enviará a los dispositivos activos del paciente.
                    @endif
                </small>
            </div>
            <div class="authorization-choice"><button type="button" class="auth-reject" id="rechazarAutorizacionDemo">No autorizar</button><button type="button" class="auth-approve" id="aprobarAutorizacionDemo">Aceptar y autorizar</button></div>
        </div>
    </div></div>
</div>

@if($agendaOnlineResultado)
<div class="modal fade" id="modalAgendaOnlineResultado" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0" style="border-radius:22px"><div class="modal-body text-center p-5"><div class="medichile-alert-icon" style="border-color:#48b88a;color:#198754">✓</div><h2 class="h4">Hora reservada</h2><p>{{ $agendaOnlineResultado->codigo }}</p><p class="text-muted">Hora Med-SDI #{{ optional($agendaOnlineResultado->agenda)->medichile_hora_medica_id }} · pendiente de confirmación</p><div class="d-flex justify-content-center gap-2 flex-wrap"><button type="button" class="btn btn-primary" data-bs-dismiss="modal">Ir al historial</button></div></div></div></div></div>
@endif

<div class="modal fade" id="modalRecorridoMedsdi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0" style="border-radius:22px;overflow:hidden">
            <div class="modal-header text-white" style="background:#1848a1">
                <div>
                    <div class="eyebrow text-white-50" id="modalRecorridoMedsdiCodigo"></div>
                    <h2 class="h5 modal-title" id="modalRecorridoMedsdiTitulo"></h2>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="modalRecorridoMedsdiBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

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

<div class="modal fade" id="modalPagoBono" tabindex="-1" aria-labelledby="modalPagoBonoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:22px; border:0; overflow:hidden;">
            <div class="modal-header text-white" style="background:#087f6f;">
                <div>
                    <div class="eyebrow text-white-50">Recepción de pago</div>
                    <h5 class="modal-title" id="modalPagoBonoLabel">Pagar copago del bono</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" id="formPagoBono">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-danger py-2 px-3">
                        Recuerde validar los datos del paciente, profesional y convenio con los datos del bono físico.
                    </div>
                    <div id="pagoAutorizacionEstado" class="alert alert-primary d-none" role="status" aria-live="polite">
                        <div class="d-flex align-items-center gap-3">
                            <span id="pagoAutorizacionSpinner" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                            <div>
                                <strong id="pagoAutorizacionTitulo">Esperando respuesta desde la app</strong>
                                <div id="pagoAutorizacionMensaje" class="small mt-1">Enviamos una solicitud al dispositivo autorizado del paciente.</div>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">RUT del paciente</label>
                            <input type="text" class="form-control" value="{{ sdi_formatear_rut($rutPacienteReal) }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombre del paciente</label>
                            <input type="text" class="form-control" value="{{ $nombrePacienteReal }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombre profesional</label>
                            <input type="text" class="form-control" id="pagoBonoProfesional" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Convenio</label>
                            <input type="text" class="form-control" value="Fonasa" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">N° de bono o programa</label>
                            <input type="text" class="form-control" id="pagoBonoCodigo" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Método de pago</label>
                            <select name="metodo_pago" id="pagoBonoMetodo" class="form-select" required>
                                <option value="tarjeta_credito">Tarjeta de crédito</option>
                                <option value="tarjeta_debito">Tarjeta de débito</option>
                                <option value="transferencia">Transferencia bancaria</option>
                                <option value="efectivo">Efectivo</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Valor prestación</label>
                            <input type="text" class="form-control" id="pagoBonoValor" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Valor bonificación</label>
                            <input type="text" class="form-control" id="pagoBonoBonificacion" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Valor a pagar</label>
                            <input type="text" class="form-control fw-bold text-success" id="pagoBonoCopago" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="pagoBonoSubmit" class="btn btn-success"><i class="feather icon-check"></i> Solicitar autorización de pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="{{ asset('js/plugins/sweetalert.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('enviarBonoNotificacionAndroid')?.addEventListener('click', async function () {
        const selector = document.getElementById('bonoNotificacionAndroid');
        if (!selector?.value) {
            if (typeof swal === 'function') swal({title:'Seleccione un bono', text:'Debe seleccionar el bono que desea notificar.', icon:'warning', button:'Aceptar'});
            return;
        }

        this.disabled = true;
        const textoOriginal = this.textContent;
        this.textContent = 'Enviando notificación...';
        try {
            const respuesta = await fetch('{{ route('cliente.bonos.notificar_android') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({voucher_id: selector.value}),
            });
            const data = await respuesta.json();
            if (!respuesta.ok || !data.ok) throw new Error(data.mensaje || 'No fue posible enviar la notificación.');
            if (typeof swal === 'function') {
                swal({title:'Notificación procesada', text:data.mensaje, icon:data.dispositivos_notificados > 0 ? 'success' : 'info', button:'Aceptar'});
            }
        } catch (error) {
            if (typeof swal === 'function') swal({title:'No se pudo notificar', text:error.message, icon:'error', button:'Aceptar'});
        } finally {
            this.disabled = false;
            this.textContent = textoOriginal;
        }
    });

    (() => {
        const modal = document.getElementById('shareVoucherModal');
        if (!modal) return;

        const titleEl = document.getElementById('shareVoucherTitle');
        const closeButton = document.getElementById('closeShareVoucherModal');
        const cancelButton = document.getElementById('cancelShareVoucher');
        const prepareButton = document.getElementById('prepareShareVoucher');
        const copyLinkButton = document.getElementById('copyVoucherLink');
        const copyImageButton = document.getElementById('copyVoucherImage');
        const messageInput = document.getElementById('shareVoucherMessage');
        const results = document.getElementById('shareVoucherResults');
        const resultsList = document.getElementById('shareVoucherResultsList');
        const recipientsContainer = document.getElementById('shareVoucherRecipients');
        const imageEl = document.getElementById('shareVoucherImage');
        const downloadLink = document.getElementById('shareVoucherDownload');

        let currentData = null;
        const channelLabels = { whatsapp: 'WhatsApp', email: 'Email', copy: 'Copiar imagen' };

        const closeModal = () => { modal.hidden = true; };

        const renderRecipients = (recipients) => {
            recipientsContainer.innerHTML = '';
            if (!recipients.length) {
                recipientsContainer.innerHTML = '<div class="alert alert-warning mb-0">No hay contactos guardados. Puedes descargar la imagen QR y enviarla por tu canal autorizado.</div>';
                return;
            }
            recipients.forEach((recipient, index) => {
                const label = document.createElement('label');
                label.className = 'share-option-card';
                const phoneLine = recipient.phone ? `<div class="small mt-2">WhatsApp: +${recipient.phone}</div>` : '';
                const emailLine = recipient.email ? `<div class="small">Email: ${recipient.email}</div>` : '';
                label.innerHTML = `<div class="d-flex align-items-start">
                        <input type="checkbox" class="share-recipient" value="${recipient.id}" ${index === 0 ? 'checked' : ''}>
                        <div>
                            <strong>${recipient.label}</strong>
                            <div class="share-muted">${recipient.role}</div>
                            ${phoneLine}${emailLine}
                        </div>
                    </div>`;
                recipientsContainer.appendChild(label);
            });
        };

        const selectedRecipients = () => {
            const checked = [...document.querySelectorAll('.share-recipient:checked')].map((item) => item.value);
            return (currentData?.recipients || []).filter((recipient) => checked.includes(recipient.id));
        };

        const selectedChannels = () => [...document.querySelectorAll('.share-channel-input:checked')].map((item) => item.value);

        const copyTextToClipboard = async (text) => {
            try {
                await navigator.clipboard.writeText(text);
                alert('Copiado al portapapeles.');
            } catch (error) {
                window.prompt('Copia este texto:', text);
            }
        };

        const copyQrImageToClipboard = async () => {
            try {
                if (!navigator.clipboard || !window.ClipboardItem) throw new Error('Clipboard image no disponible');
                const response = await fetch(currentData.qr_image_url, { cache: 'no-store' });
                const blob = await response.blob();
                await navigator.clipboard.write([new ClipboardItem({ 'image/png': blob })]);
                alert('Imagen QR copiada. Ahora puedes pegarla en WhatsApp o email.');
            } catch (error) {
                window.open(currentData.qr_image_url, '_blank', 'noopener');
                alert('Tu navegador no permitió copiar la imagen. Abrí la imagen QR para que puedas descargarla o copiarla manualmente.');
            }
        };

        const createActionItem = ({ title, detail, url, buttonText, copyImage, disabled, onClick }) => {
            const item = document.createElement('div');
            item.className = 'share-result-item';
            const text = document.createElement('div');
            text.innerHTML = `<strong>${title}</strong><div class="share-muted">${detail}</div>`;
            item.appendChild(text);

            if (disabled) {
                const badge = document.createElement('span');
                badge.className = 'badge text-bg-warning';
                badge.textContent = 'Dato faltante';
                item.appendChild(badge);
                return item;
            }

            if (onClick) {
                const action = document.createElement('button');
                action.type = 'button';
                action.className = 'btn btn-sm btn-success';
                action.textContent = buttonText;
                action.addEventListener('click', () => onClick(action));
                item.appendChild(action);
                return item;
            }

            if (url) {
                const action = document.createElement('a');
                action.className = 'btn btn-sm btn-success';
                action.href = url;
                action.target = '_blank';
                action.rel = 'noopener';
                action.textContent = buttonText;
                item.appendChild(action);
                return item;
            }

            const copyButton = document.createElement('button');
            copyButton.type = 'button';
            copyButton.className = 'btn btn-sm btn-outline-dark';
            copyButton.textContent = buttonText;
            copyButton.addEventListener('click', () => (copyImage ? copyQrImageToClipboard() : copyTextToClipboard(detail)));
            item.appendChild(copyButton);
            return item;
        };

        const sendEmail = async (recipient, message, button) => {
            const original = button.textContent;
            button.disabled = true;
            button.textContent = 'Enviando...';
            try {
                const response = await fetch(currentData.email_send_url, {
                    method: 'POST',
                    headers: {'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
                    body: JSON.stringify({email:recipient.email, subject:currentData.subject, message}),
                });
                const data = await response.json();
                if (!response.ok || !data.ok) throw new Error(data.mensaje || 'No fue posible enviar el email.');
                if (typeof swal === 'function') await swal({title:'Email enviado',text:data.mensaje,icon:'success',button:'Aceptar'}); else alert(data.mensaje);
            } catch (error) {
                if (typeof swal === 'function') await swal({title:'No se pudo enviar',text:error.message,icon:'error',button:'Aceptar'}); else alert(error.message);
            } finally {
                button.disabled = false;
                button.textContent = original;
            }
        };

        const prepareShares = () => {
            const channels = selectedChannels();
            const people = selectedRecipients();
            const message = messageInput.value.trim() || 'Medichile · se adjunta imagen QR.';
            resultsList.innerHTML = '';

            if (!channels.length) {
                alert('Selecciona al menos un método de envío.');
                return;
            }
            if (!people.length && !channels.includes('copy')) {
                alert('Selecciona al menos un destinatario o usa la opción Copiar imagen.');
                return;
            }

            if (channels.includes('whatsapp') || channels.includes('email') || channels.includes('copy')) {
                resultsList.appendChild(createActionItem({
                    title: 'Imagen QR Medichile',
                    detail: 'Copia o descarga esta imagen y adjúntala en WhatsApp/email. No se envía link largo al paciente.',
                    copyImage: true,
                    buttonText: 'Copiar imagen QR',
                }));
            }

            people.forEach((recipient) => {
                channels.forEach((channel) => {
                    if (channel === 'copy') return;

                    if (channel === 'whatsapp') {
                        if (!recipient.phone) {
                            resultsList.appendChild(createActionItem({ title: `${recipient.label} · ${channelLabels[channel]}`, detail: 'Este destinatario no tiene teléfono registrado.', disabled: true }));
                            return;
                        }
                        resultsList.appendChild(createActionItem({
                            title: `${recipient.label} · WhatsApp`,
                            detail: `Enviar a +${recipient.phone}`,
                            url: `${currentData.whatsapp_demo_url}?destino=${encodeURIComponent(recipient.id)}`,
                            buttonText: 'Abrir y enviar simulado',
                        }));
                    }

                    if (channel === 'email') {
                        if (!recipient.email) {
                            resultsList.appendChild(createActionItem({ title: `${recipient.label} · ${channelLabels[channel]}`, detail: 'Este destinatario no tiene email registrado.', disabled: true }));
                            return;
                        }
                        resultsList.appendChild(createActionItem({
                            title: `${recipient.label} · Email`,
                            detail: `Enviar a ${recipient.email}`,
                            onClick: button => sendEmail(recipient, message, button),
                            buttonText: 'Enviar email',
                        }));
                    }
                });
            });

            if (channels.includes('copy')) {
                resultsList.appendChild(createActionItem({
                    title: 'Abrir imagen QR',
                    detail: 'Abre la imagen guardada para descargarla o compartirla.',
                    url: currentData.qr_image_url,
                    buttonText: 'Abrir imagen',
                }));
            }

            results.hidden = false;
        };

        const abrirCompartir = async (url) => {
            try {
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('No se pudo cargar la información de envío.');
                currentData = await response.json();

                titleEl.textContent = 'Compartir QR Medichile ' + currentData.codigo;
                renderRecipients(currentData.recipients);
                imageEl.src = currentData.qr_image_url;
                imageEl.alt = 'Imagen QR Medichile ' + currentData.codigo;
                downloadLink.href = currentData.qr_image_url;
                downloadLink.setAttribute('download', currentData.qr_image_download_name);
                messageInput.value = currentData.message;
                results.hidden = true;
                resultsList.innerHTML = '';

                modal.hidden = false;
                prepareButton.focus();
            } catch (error) {
                if (typeof swal === 'function') {
                    swal({ title: 'No se pudo abrir Compartir', text: error.message, icon: 'error', button: 'Aceptar' });
                } else {
                    alert(error.message);
                }
            }
        };

        document.querySelectorAll('[data-share-voucher-url]').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                abrirCompartir(button.dataset.shareVoucherUrl);
            });
        });

        closeButton?.addEventListener('click', closeModal);
        cancelButton?.addEventListener('click', closeModal);
        prepareButton?.addEventListener('click', prepareShares);
        copyLinkButton?.addEventListener('click', copyQrImageToClipboard);
        copyImageButton?.addEventListener('click', copyQrImageToClipboard);
        modal.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });
        document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !modal.hidden) closeModal(); });
    })();


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

    document.querySelectorAll('form[data-confirm-action]').forEach(form => {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const title = form.dataset.confirmTitle || 'Confirmar acción';
            const text = form.dataset.confirmAction || '¿Estás seguro de continuar?';
            const icon = form.dataset.confirmIcon || 'warning';

            if (typeof swal === 'function') {
                swal({
                    title: title,
                    text: text,
                    icon: icon,
                    buttons: ['Cancelar', 'Continuar'],
                    dangerMode: false,
                }).then((confirmado) => {
                    if (confirmado) form.submit();
                });
                return;
            }

            form.submit();
        });
    });

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
    let formularioPendienteAutorizacion = null;
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
    document.getElementById('abrirAgendaOnline')?.addEventListener('click', () => mostrarModalDemo(modalHorario));
    document.getElementById('abrirReservaMedsdi')?.addEventListener('click', () => { mostrarModalDemo(document.getElementById('modalReservaMedsdi')); inicializarReservaMedsdi(); });
    document.getElementById('abrirReservaMedsdi')?.addEventListener('keydown', event => { if (event.key === 'Enter' || event.key === ' ') { mostrarModalDemo(document.getElementById('modalReservaMedsdi')); inicializarReservaMedsdi(); } });
    document.getElementById('abrirAutorizacionDemo')?.addEventListener('click', () => { compraEsperandoAutorizacion = false; mostrarModalDemo(modalAutorizacion); });
    document.getElementById('abrirAutorizacionDemo')?.addEventListener('keydown', event => { if (event.key === 'Enter' || event.key === ' ') mostrarModalDemo(modalAutorizacion); });
    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', () => ocultarModalDemo(button.closest('.modal')));
    });

    const formPagoBono = document.getElementById('formPagoBono');
    const pagoEstado = document.getElementById('pagoAutorizacionEstado');
    const pagoEstadoTitulo = document.getElementById('pagoAutorizacionTitulo');
    const pagoEstadoMensaje = document.getElementById('pagoAutorizacionMensaje');
    const pagoEstadoSpinner = document.getElementById('pagoAutorizacionSpinner');
    const pagoSubmit = document.getElementById('pagoBonoSubmit');
    let pagoPollingTimer = null;
    let pagoConsultando = false;

    function detenerConsultaPago() {
        if (pagoPollingTimer) window.clearTimeout(pagoPollingTimer);
        pagoPollingTimer = null;
        pagoConsultando = false;
    }

    function mostrarEstadoPago(tipo, titulo, mensaje) {
        pagoEstado.classList.remove('d-none', 'alert-primary', 'alert-success', 'alert-danger');
        pagoEstado.classList.add(tipo === 'success' ? 'alert-success' : (tipo === 'danger' ? 'alert-danger' : 'alert-primary'));
        pagoEstadoTitulo.textContent = titulo;
        pagoEstadoMensaje.textContent = mensaje;
        pagoEstadoSpinner.classList.toggle('d-none', tipo !== 'pending');
    }

    function reiniciarModalPago() {
        detenerConsultaPago();
        pagoEstado.classList.add('d-none');
        pagoSubmit.disabled = false;
        pagoSubmit.innerHTML = '<i class="feather icon-check"></i> Solicitar autorización de pago';
    }

    document.querySelectorAll('.abrir-pago-bono').forEach(button => {
        button.addEventListener('click', () => {
            reiniciarModalPago();
            formPagoBono.action = button.dataset.actionUrl;
            document.getElementById('pagoBonoCodigo').value = button.dataset.codigo;
            document.getElementById('pagoBonoProfesional').value = button.dataset.profesional;
            document.getElementById('pagoBonoValor').value = money(button.dataset.valor);
            document.getElementById('pagoBonoBonificacion').value = money(button.dataset.bonificacion);
            document.getElementById('pagoBonoCopago').value = money(button.dataset.copago);
            mostrarModalDemo(document.getElementById('modalPagoBono'));
        });
    });

    async function consultarAutorizacionPago() {
        if (pagoConsultando || !formPagoBono.action) return;
        pagoConsultando = true;

        try {
            const response = await fetch(formPagoBono.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: new FormData(formPagoBono),
            });
            const data = await response.json();

            if (response.status === 429) {
                mostrarEstadoPago('pending', 'Esperando respuesta desde la app', 'Estamos moderando las consultas de seguridad. Volveremos a revisar la autorización automáticamente.');
                pagoSubmit.disabled = true;
                pagoSubmit.textContent = 'Esperando autorización…';
                const retryAfter = Number(response.headers.get('Retry-After')) || 6;
                pagoPollingTimer = window.setTimeout(consultarAutorizacionPago, Math.max(retryAfter, 4) * 1000);
                return;
            }

            if (response.status === 202 || data.pendiente_autorizacion) {
                mostrarEstadoPago('pending', 'Esperando respuesta desde la app', data.mensaje || 'El paciente debe autorizar o rechazar el pago desde su dispositivo registrado.');
                pagoSubmit.disabled = true;
                pagoSubmit.textContent = 'Esperando autorización…';
                pagoPollingTimer = window.setTimeout(consultarAutorizacionPago, 3000);
                return;
            }

            if (!response.ok || !data.ok) {
                detenerConsultaPago();
                const rechazado = Boolean(data.autorizacion_rechazada);
                mostrarEstadoPago('danger', rechazado ? 'Pago rechazado desde la app' : 'No se pudo autorizar el pago', data.mensaje || 'Revise la solicitud e intente nuevamente.');
                pagoSubmit.disabled = false;
                pagoSubmit.textContent = 'Solicitar una nueva autorización';
                return;
            }

            detenerConsultaPago();
            mostrarEstadoPago('success', 'Pago autorizado', data.mensaje || 'La app autorizó el pago y el bono quedó activo.');
            pagoSubmit.disabled = true;
            pagoSubmit.textContent = 'Pago autorizado';
            window.setTimeout(() => window.location.reload(), 1400);
        } catch (error) {
            detenerConsultaPago();
            mostrarEstadoPago('danger', 'No pudimos consultar la autorización', 'Compruebe la conexión e intente nuevamente. La solicitud no se aprobará automáticamente.');
            pagoSubmit.disabled = false;
            pagoSubmit.textContent = 'Reintentar';
        } finally {
            pagoConsultando = false;
        }
    }

    formPagoBono?.addEventListener('submit', event => {
        event.preventDefault();
        consultarAutorizacionPago();
    });

    document.querySelectorAll('#modalPagoBono [data-bs-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', detenerConsultaPago);
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
        formularioPendienteAutorizacion = formCompraAgenda;
        ocultarModalDemo(modalCompra);
        setTimeout(() => mostrarModalDemo(modalAutorizacion), 120);
    });
    document.getElementById('rechazarAutorizacionDemo')?.addEventListener('click', () => {
        compraEsperandoAutorizacion = false;
        formularioPendienteAutorizacion = null;
        ocultarModalDemo(modalAutorizacion);
        if (typeof swal === 'function') swal({title:'Autorización rechazada',text:'No se realizó el cargo ni se reservó la hora. Puede volver a revisar los datos.',icon:'error',button:'Entendido',dangerMode:true});
    });
    document.getElementById('aprobarAutorizacionDemo')?.addEventListener('click', () => {
        ocultarModalDemo(modalAutorizacion);
        const continuar = () => {
            if (!compraEsperandoAutorizacion) return;
            compraAutorizada = true;
            formularioPendienteAutorizacion?.requestSubmit();
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

    document.querySelectorAll('form[data-medsdi-sync]').forEach(form => {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const boton = form.querySelector('button[type="submit"]');
            const spinner = boton?.querySelector('.spinner-border');
            boton && (boton.disabled = true);
            spinner?.classList.remove('d-none');

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            })
                .then(response => response.json().then(data => ({ ok: response.ok, data })))
                .then(({ ok, data }) => {
                    const celda = document.getElementById('voucher-estado-' + form.dataset.voucherId);
                    if (celda && data.estado_html) celda.innerHTML = data.estado_html;

                    if (!ok || !data.ok) {
                        if (typeof swal === 'function') {
                            swal({ title: 'No se pudo sincronizar', text: data.mensaje || 'Intente nuevamente.', icon: 'error', button: 'Aceptar', dangerMode: true });
                        }
                        return;
                    }

                    document.getElementById('modalRecorridoMedsdiCodigo').textContent = 'Bono ' + data.codigo;
                    document.getElementById('modalRecorridoMedsdiTitulo').textContent = 'Estado Med-SDI: ' + data.estado_texto;
                    document.getElementById('modalRecorridoMedsdiBody').innerHTML = data.recorrido_html || '';
                    mostrarModalDemo(document.getElementById('modalRecorridoMedsdi'));
                })
                .catch(() => {
                    if (typeof swal === 'function') {
                        swal({ title: 'Error de conexión', text: 'No fue posible comunicarse con el servidor.', icon: 'error', button: 'Aceptar', dangerMode: true });
                    }
                })
                .finally(() => {
                    boton && (boton.disabled = false);
                    spinner?.classList.add('d-none');
                });
        });
    });

    // --- Reserva de hora vía API real Med-SDI ---
    let medsdiInicializado = false;
    let medsdiSeleccion = null;
    let medsdiPrestacion = null;
    let medsdiCotizacionActual = null;
    let medsdiPrestacionTimer = null;
    let medsdiCalendario = null;
    const medsdiEstado = document.getElementById('medsdiEstadoServicio');
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

    medsdiFormConfirmar?.addEventListener('submit', async event => {
        event.preventDefault();
        const boton = medsdiFormConfirmar.querySelector('button[type="submit"]');
        if (!boton) return;
        boton.disabled = true;
        const textoOriginal = boton.textContent;
        boton.textContent = 'Confirmando disponibilidad en Med-SDI...';

        try {
            const response = await fetch(medsdiFormConfirmar.action, {
                method: 'POST',
                headers: {'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest'},
                body: new FormData(medsdiFormConfirmar),
            });
            const data = await response.json();
            if (!response.ok || !data.ok) throw new Error(data.mensaje || 'Med-SDI no pudo reservar la hora seleccionada.');

            compraEsperandoAutorizacion = true;
            formularioPendienteAutorizacion = null;
            const selectorBono = document.getElementById('bonoNotificacionAndroid');
            if (selectorBono && data.voucher) {
                const option = new Option(`${data.voucher.codigo} · ${data.voucher.servicio || 'Bono médico'} · recién adquirido`, data.voucher.id, true, true);
                selectorBono.add(option, 1);
                selectorBono.disabled = false;
                document.getElementById('enviarBonoNotificacionAndroid').disabled = false;
            }

            ocultarModalDemo(document.getElementById('modalReservaMedsdi'));
            const continuar = () => setTimeout(() => mostrarModalDemo(modalAutorizacion), 120);
            if (typeof swal === 'function') {
                swal({
                    title:'Hora reservada correctamente',
                    text:`Med-SDI confirmó la hora #${data.voucher?.hora_medsdi_id || ''}. Ahora puede solicitar la autorización o enviar el aviso del bono.`,
                    icon:'success',
                    button:'Continuar',
                }).then(continuar);
            } else {
                alert(data.mensaje);
                continuar();
            }
        } catch (error) {
            if (typeof swal === 'function') {
                swal({title:'No se pudo tomar la hora', text:error.message, icon:'error', button:'Revisar disponibilidad', dangerMode:true});
            } else alert(error.message);
        } finally {
            boton.disabled = false;
            boton.textContent = textoOriginal;
        }
    });

    function medsdiMostrarAviso(mensaje) {
        medsdiEstado.textContent = mensaje;
        medsdiEstado.classList.remove('d-none');
    }

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
        const resp = await medsdiFetchJson(`{{ route('cliente.medsdi.dias_laborales') }}?${params.toString()}`);
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
        const resp = await medsdiFetchJson(`{{ route('cliente.medsdi.prestaciones') }}?${params.toString()}`);
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
            const codigo = document.createElement('strong');
            codigo.textContent = prestacion.codigo || 'Sin código';
            const nombre = document.createElement('span');
            nombre.textContent = ` · ${prestacion.nombre}`;
            boton.append(codigo, nombre);
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
            medsdiFetchJson('{{ route('cliente.medsdi.regiones') }}'),
            medsdiFetchJson('{{ route('cliente.medsdi.especialidades') }}'),
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

        const resp = await medsdiFetchJson(`{{ route('cliente.medsdi.ciudades') }}?id_region=${medsdiRegion.value}`);
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
        const resp = await medsdiFetchJson(`{{ route('cliente.medsdi.tipo_especialidades') }}?id_especialidad=${medsdiEspecialidad.value}`);
        medsdiLlenarSelect(medsdiTipoEspecialidad, resp.registros || [], 'id', 'nombre', 'Todos');
    });

    medsdiTipoEspecialidad?.addEventListener('change', async () => {
        medsdiLimpiarSeleccion();
        medsdiResultados.innerHTML = '';
        medsdiLlenarSelect(medsdiSubTipoEspecialidad, [], 'id', 'nombre', 'Todos');
        if (!medsdiTipoEspecialidad.value) return;
        const resp = await medsdiFetchJson(`{{ route('cliente.medsdi.sub_tipo_especialidades') }}?id_tipo_especialidad=${medsdiTipoEspecialidad.value}`);
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

        const resp = await medsdiFetchJson(`{{ route('cliente.medsdi.profesionales') }}?${params.toString()}`);
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
        const respCotizacion = await medsdiPostJson('{{ route('cliente.medsdi.cotizar') }}', {
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
        const resp = await medsdiFetchJson(`{{ route('cliente.medsdi.horas_disponibles') }}?${params.toString()}`);
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

    const errorServidor = @json(session('error'));
    if (errorServidor && typeof swal === 'function') {
        swal({title: 'Validación de usuario', text: errorServidor, icon: 'error', button: 'Aceptar', dangerMode: true});
    }
    @if(session('abrir_cuenta_bancaria') || $errors->any())
        abrirCuentaBancaria();
    @endif
});
</script>
</body>
</html>
