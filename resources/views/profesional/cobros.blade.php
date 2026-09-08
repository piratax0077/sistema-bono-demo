<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cobros Profesional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f4f7fb;">
@include('partials.demo_user_switcher')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div class="text-uppercase text-success fw-bold small">Ciclo financiero</div>
            <h2 class="fw-bold mb-1">Escritorio de bonos y cobros</h2>
            <p class="text-muted mb-0">Atenciones cerradas, cobros, rendiciones y pagos recibidos.</p>
        </div>
        <a href="{{ url('/escritorio-profesional') }}" class="btn btn-outline-secondary">Volver</a>
    </div>

    @if(session('ok'))<div class="alert alert-success">{{ session('ok') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Habilitados para cobro</small><h2>{{ $bonosClinicos->where('estado', 'validado_atencion')->count() }}</h2></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Pendientes de auditoría</small><h2>{{ $pendientesAuditoria->count() }}</h2></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Con visto bueno</small><h2>{{ $pendientesRendicion->count() }}</h2></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Total habilitado para rendir</small><h2>${{ number_format($pendientesRendicion->sum('monto_cobrado'), 0, ',', '.') }}</h2></div></div></div>
    </div>

    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-header bg-white py-3"><h4 class="mb-0">Atenciones cerradas</h4><small class="text-muted">El cierre clínico validado automáticamente habilita el botón Cobrar.</small></div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>Bono</th><th>Paciente</th><th>Diagnóstico</th><th>Validación</th><th>Monto</th><th class="text-end">Acción</th></tr></thead>
                <tbody>
                @forelse($bonosClinicos as $voucher)
                    <tr>
                        <td><strong>{{ $voucher->codigo }}</strong></td>
                        <td>{{ $voucher->cliente_nombre }}</td>
                        <td style="max-width:340px">{{ \Illuminate\Support\Str::limit(optional($voucher->atencion)->diagnostico, 90) }}</td>
                        <td>
                            @if($voucher->estado === 'validado_atencion')
                                <span class="badge bg-success">Habilitado para cobro</span>
                            @else
                                <span class="badge bg-warning text-dark">Revisión excepcional</span>
                            @endif
                        </td>
                        <td>${{ number_format($voucher->saldo_veterinario, 0, ',', '.') }}</td>
                        <td class="text-end">
                            @if($voucher->estado === 'validado_atencion')
                                <a href="{{ route('profesional.cobros.qr.generar', $voucher->id) }}" class="btn btn-success btn-sm">Generar QR de cobro</a>
                            @else
                                <span class="text-muted small">Requiere revisión</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay atenciones cerradas pendientes de cobro.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Cobros y estado de pago</h4>
        <form method="POST" action="{{ route('profesional.cobros.generarRendicion') }}">@csrf<button class="btn btn-primary" @if($pendientesRendicion->isEmpty()) disabled @endif>Enviar a rendición</button></form>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Bono</th><th>Monto</th><th>Cobro</th><th>Rendición</th><th>Pago</th><th>Comprobante</th></tr></thead>
                <tbody>
                @forelse($cobros as $cobro)
                    @php
                        $liquidacion = $cobro->rendicion
                            ? $cobro->rendicion->liquidaciones->first()
                            : null;
                        $pagado = $liquidacion && $liquidacion->estado === 'pagada';
                    @endphp
                    <tr>
                        <td><strong>{{ optional($cobro->voucher)->codigo ?: '-' }}</strong></td>
                        <td>${{ number_format($cobro->monto_cobrado, 0, ',', '.') }}</td>
                        <td>
                            @php
                                $estadoCobro = [
                                    'pendiente_auditoria' => ['Pendiente de auditoría', 'bg-warning text-dark'],
                                    'observado_auditoria' => ['Observado por auditoría', 'bg-danger'],
                                    'rechazado_auditoria' => ['Rechazado por auditoría', 'bg-danger'],
                                    'pendiente_rendicion' => ['Visto bueno aprobado', 'bg-success'],
                                    'rendido' => ['Rendido', 'bg-info text-dark'],
                                    'pagado' => ['Pagado', 'bg-success'],
                                ][$cobro->estado] ?? [$cobro->estado, 'bg-secondary'];
                            @endphp
                            <span class="badge {{ $estadoCobro[1] }}">{{ $estadoCobro[0] }}</span>
                            <small class="d-block text-muted">{{ $cobro->cobrado_en }}</small>
                        </td>
                        <td>{{ $cobro->rendicion ? '#'.$cobro->rendicion->id.' · '.$cobro->rendicion->estado : 'Pendiente de envío' }}</td>
                        <td><span class="badge {{ $pagado ? 'bg-success' : 'bg-warning text-dark' }}">{{ $pagado ? 'Pagado' : 'Pendiente de pago' }}</span></td>
                        <td>{{ $pagado ? ($liquidacion->comprobante_transferencia ?: 'Registrado') : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Aún no existen cobros solicitados.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
