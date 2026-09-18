<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Escritorio Profesional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>.page-shell{max-width:1320px}.page-kicker{color:#1848a1;font-size:.75rem;font-weight:850;letter-spacing:.16em;text-transform:uppercase}.clinical-card .card-header{background:linear-gradient(90deg,#f6f9ff,#f1fbfb)!important}.empty-state{padding:3rem 1rem!important}.header-actions,.patient-actions{display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;justify-content:flex-end}.agenda-dialog{width:min(720px,calc(100% - 28px));border:0;border-radius:24px;padding:0;box-shadow:0 30px 90px rgba(16,39,74,.3)}.agenda-dialog::backdrop{background:#07172bb3}.agenda-dialog-head{display:flex;justify-content:space-between;gap:18px;padding:22px 24px;background:linear-gradient(110deg,#1848a1,#31bebe);color:#fff}.agenda-dialog-head h2{color:#fff}.agenda-dialog-body{padding:24px}.agenda-dialog-grid{display:grid;grid-template-columns:1fr 230px;gap:22px}.agenda-qr svg{width:210px;height:210px;background:#fff;padding:10px;border:1px solid #d7e2f2;border-radius:18px}.security-check{padding:13px;border-radius:13px;background:#edf4ff;color:#294d78;font-size:.86rem}@media(max-width:650px){.agenda-dialog-grid{grid-template-columns:1fr}.agenda-qr{text-align:center}}</style>
</head>
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

<body>
@include('partials.demo_user_switcher')

<div class="container page-shell py-5">
    <section class="patient-hero mb-4">
        <div class="eyebrow">Medichile · Portal del profesional</div>
        <h1 class="display-6 fw-bold mt-2 mb-2">Hola, Jaime Kriman Astorga</h1>
        <p class="mb-0">Reserva tu hora, confirma el copago y llega al centro médico. La hora, el médico, el pago y el QR permanecen vinculados durante todo el recorrido.</p>
        <div class="patient-profile"><span class="patient-chip">Profesional Med-SDI #</span><span class="patient-chip">RUT</span><span class="patient-chip">WhatsApp</span><span class="patient-chip"></span></div>
    </section>
    <header class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div><div class="page-kicker">Medichile · Agenda clínica</div><h1 class="h2 fw-bold mb-1">Escritorio profesional</h1><p class="text-muted mb-0">Pacientes recepcionados, atención clínica y gestión de cobros.</p></div>
    </header>

    @include('partials.demo_flow_guide', ['demoStep' => $pacientesEnEspera->isNotEmpty() ? 4 : 5])

    <div class="card clinical-card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <div class="text-uppercase text-success fw-bold small">Agenda clínica</div>
                <h4 class="mb-0">Pacientes en espera</h4>
                <small class="text-muted">Recepcionados y confirmados en la agenda real de Medichile.</small>
            </div>
            <span class="badge rounded-pill bg-success fs-6">{{ $pacientesEnEspera->count() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Paciente</th>
                        <th>RUT</th>
                        <th>Prestación</th>
                        <th>Llegada</th>
                        <th>Hora Medichile</th>
                        <th>Estado</th>
                        <th class="d-flex justify-content-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pacientesEnEspera as $voucher)
                        <tr>
                            <td>
                                <strong>{{ $voucher->cliente_nombre }}</strong>
                                <small class="d-block text-muted">Bono {{ $voucher->codigo }}</small>
                            </td>
                            <td>{{ sdi_formatear_rut($voucher->beneficiario_rut_visible ?: $voucher->cliente_rut_visible) }}</td>
                            <td>{{ $voucher->tipo_servicio }}</td>
                            <td>{{ optional($voucher->agenda->fecha_hora_confirmada)->format('d-m-Y H:i') ?: '-' }}</td>
                            <td><span class="badge bg-light text-dark border">#{{ $voucher->agenda->medichile_hora_medica_id }}</span></td>
                            <td><span class="badge bg-warning text-dark">Esperando atención</span></td>
                            <td class="text-end"><div class="patient-actions">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('agenda-qr-{{ $voucher->id }}').showModal()">Ficha y QR</button>
                                @if($voucher->prestador_nombre)
                                    <form method="POST" action="{{ route('profesional.medsdi.iniciar_atencion', $voucher->id) }}">
                                        @csrf
                                        <button class="btn btn-primary btn-sm">Atender</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('profesional.vouchers.aceptar', $voucher->id) }}">
                                        @csrf
                                        <button class="btn btn-primary btn-sm">Abrir atención</button>
                                    </form>
                                @endif
                            </div></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted empty-state">No hay pacientes esperando atención.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach($pacientesEnEspera as $voucher)
        <dialog class="agenda-dialog" id="agenda-qr-{{ $voucher->id }}">
            <div class="agenda-dialog-head"><div><small>AGENDA PROFESIONAL · PACIENTE PRESENTE</small><h2 class="h4 mb-0">{{ $voucher->cliente_nombre }}</h2></div><button class="btn btn-light" type="button" onclick="this.closest('dialog').close()">Cerrar</button></div>
            <div class="agenda-dialog-body">
                <div class="agenda-dialog-grid">
                    <div>
                        <p><strong>RUT:</strong> {{ sdi_formatear_rut($voucher->beneficiario_rut_visible ?: $voucher->cliente_rut_visible) }}</p>
                        <p><strong>Prestación:</strong> {{ $voucher->tipo_servicio }}</p>
                        <p><strong>Hora:</strong> {{ optional($voucher->agenda->fecha_hora_confirmada)->format('d-m-Y H:i') }}</p>
                        <p><strong>Bono:</strong> {{ $voucher->codigo }}</p>
                        <div class="security-check">🔒 Identidad, relación médico–paciente, hora y bono verificados. El QR es respaldo para tótem o secretaría; no reemplaza el control de sesión y estado.</div>
                    </div>
                    <div class="agenda-qr text-center">
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(210)->margin(1)->generate(route('vouchers.usar', $voucher->qr_token)) !!}
                        <small class="d-block text-muted mt-2">QR de respaldo</small>
                    </div>
                </div>
            </div>
        </dialog>
    @endforeach

    <div class="card clinical-card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-header bg-white py-3">
            <div class="text-uppercase text-primary fw-bold small">Med-SDI · Agenda real</div>
            <h4 class="mb-0">En atención ahora (Jaime Kriman Astorga)</h4>
            <small class="text-muted">Bonos Med-SDI cuya consulta ya está siendo realizada.</small>
        </div>
        @if(! $bonosMedsdi['disponible'])
            <div class="p-3">
                <div class="alert alert-warning mb-0">No fue posible consultar Med-SDI: {{ $bonosMedsdi['mensaje'] }}</div>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Paciente</th>
                            <th>Prestación</th>
                            <th>Hora Med-SDI</th>
                            <th>Estado</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bonosMedsdi['registros'] as $bono)
                            <tr>
                                <td>{{ $bono['voucher']?->cliente_nombre ?? '—' }}</td>
                                <td>{{ $bono['voucher']?->tipo_servicio ?? '—' }}</td>
                                <td><span class="badge bg-light text-dark border">#{{ $bono['id_hora_medica'] }}</span></td>
                                <td><span class="badge bg-info text-dark">{{ $bono['estado_medsdi'] }}</span></td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('profesional.medsdi.finalizar_hora', $bono['voucher']->id) }}">
                                        @csrf
                                        <button class="btn btn-success btn-sm">Finalizar consulta</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted empty-state">No hay consultas en curso en este momento.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{--
    Historial general de vouchers asignados oculto para mantener limpio el
    escritorio profesional. Los bonos se consultan desde Gestión de cobros.
    <div class="card p-4 border-0 shadow-sm">

        <h5 class="fw-bold mb-3">
            Vouchers asignados
        </h5>

        <table class="table table-hover align-middle">

            <thead>
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>RUT Dueño</th>
                    <th>Servicio</th>
                    <th>Monto a cobrar</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>

            <tbody>

                @forelse($vouchers as $voucher)

                    <tr>
                        <td>{{ $voucher->codigo }}</td>
                        <td>{{ $voucher->cliente_nombre }}</td>
                        <td>{{ $voucher->cliente_rut_visible }}</td>
                        <td>{{ $voucher->tipo_servicio }}</td>
                        <td>${{ number_format($voucher->saldo_veterinario, 0, ',', '.') }}</td>
                        <td>{{ $voucher->estado }}</td>
                        <td>
                            <a href="{{ route('vouchers.show', $voucher->id) }}"
                            class="btn btn-sm btn-primary mb-1">
                                Ver
                            </a>

                            @if($voucher->estado === 'asignado' && optional($voucher->agenda)->estado === 'paciente_en_espera')
                                <form method="POST" action="{{ route('profesional.vouchers.aceptar', $voucher->id) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-primary">Abrir atención</button>
                                </form>
                            @elseif($voucher->estado === 'en_atencion')
                                <a href="{{ route('profesional.vouchers.atencion', $voucher->id) }}" class="btn btn-sm btn-warning">Continuar atención</a>
                            @elseif($voucher->estado === 'atencion_cerrada')
                                <span class="badge bg-warning text-dark">Pendiente validación</span>
                            @elseif($voucher->estado === 'validado_atencion')
                                <a href="{{ route('profesional.cobros') }}" class="btn btn-sm btn-success">Ir a cobrar</a>
                            @elseif($voucher->estado == 'usado')

                                <span class="badge bg-success">
                                    Atención cerrada
                                </span>

                            @else

                                <span class="text-muted">
                                    Sin acción
                                </span>

                            @endif
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="7">
                            No hay vouchers asignados a este profesional.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>
    --}}

</div>

</body>
</html>
