<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Escritorio Profesional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>.page-shell{max-width:1320px}.page-kicker{color:#1848a1;font-size:.75rem;font-weight:850;letter-spacing:.16em;text-transform:uppercase}.clinical-card .card-header{background:linear-gradient(90deg,#f6f9ff,#f1fbfb)!important}.empty-state{padding:3rem 1rem!important}.header-actions,.patient-actions{display:flex;gap:.5rem;flex-wrap:wrap;align-items:center;justify-content:flex-end}.agenda-dialog{width:min(720px,calc(100% - 28px));border:0;border-radius:24px;padding:0;box-shadow:0 30px 90px rgba(16,39,74,.3)}.agenda-dialog::backdrop{background:#07172bb3}.agenda-dialog-head{display:flex;justify-content:space-between;gap:18px;padding:22px 24px;background:linear-gradient(110deg,#1848a1,#31bebe);color:#fff}.agenda-dialog-head h2{color:#fff}.agenda-dialog-body{padding:24px}.agenda-dialog-grid{display:grid;grid-template-columns:1fr 230px;gap:22px}.agenda-qr svg{width:210px;height:210px;background:#fff;padding:10px;border:1px solid #d7e2f2;border-radius:18px}.security-check{padding:13px;border-radius:13px;background:#edf4ff;color:#294d78;font-size:.86rem}@media(max-width:650px){.agenda-dialog-grid{grid-template-columns:1fr}.agenda-qr{text-align:center}}</style>
</head>

<body>
@include('partials.demo_user_switcher')

<div class="container page-shell py-5">
    <header class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div><div class="page-kicker">Medichile · Agenda clínica</div><h1 class="h2 fw-bold mb-1">Escritorio profesional</h1><p class="text-muted mb-0">Pacientes recepcionados, atención clínica y gestión de cobros.</p></div>
    </header>

    @include('partials.demo_flow_guide', ['demoProfessional' => true, 'demoInteractive' => true])

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
                        <th class="text-end">Acción</th>
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
