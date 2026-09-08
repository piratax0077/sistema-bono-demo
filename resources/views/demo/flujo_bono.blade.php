@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <span class="badge bg-primary mb-2">DEMO END-TO-END</span>
            <h2 class="mb-1">Flujo completo del bono {{ $voucher->codigo }}</h2>
            <p class="text-muted mb-0">Compra, envío, recepción, auditoría, cobro, rendición y pago.</p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('vouchers.qr', $voucher->qr_token) }}">Ver QR</a>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['1', 'Compra', 'Bono pagado por paciente', 'success'],
            ['2', 'Envío QR', 'Enviado y recibido', 'success'],
            ['3', 'Atención', 'Cerrada por profesional', 'success'],
            ['4', 'Auditoría', 'Validada por asistente/contralor', 'success'],
            ['5', 'Cobro', 'Incluido en rendición', 'success'],
            ['6', 'Pago', 'Liquidación pagada', 'success'],
        ] as $paso)
        <div class="col-md-4 col-xl-2">
            <div class="card h-100 border-{{ $paso[3] }}">
                <div class="card-body">
                    <div class="fw-bold text-{{ $paso[3] }}">Paso {{ $paso[0] }}</div>
                    <div class="fw-semibold">{{ $paso[1] }}</div>
                    <small class="text-muted">{{ $paso[2] }}</small>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">Resumen financiero</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-7">Paciente</dt><dd class="col-5">{{ $voucher->cliente_nombre }}</dd>
                        <dt class="col-7">Profesional</dt><dd class="col-5">{{ optional($voucher->profesional)->nombre }}</dd>
                        <dt class="col-7">Valor bono</dt><dd class="col-5">${{ number_format($voucher->valor, 0, ',', '.') }}</dd>
                        <dt class="col-7">Copago paciente</dt><dd class="col-5">${{ number_format($voucher->copago_usuario, 0, ',', '.') }}</dd>
                        <dt class="col-7">Pago profesional</dt><dd class="col-5">${{ number_format(optional(optional($voucher->cobros->first())->rendicion?->liquidaciones->first())->monto_profesional ?? 0, 0, ',', '.') }}</dd>
                        <dt class="col-7">Estado final</dt><dd class="col-5"><span class="badge bg-success">PAGADO</span></dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">Trazabilidad auditable</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead><tr><th>Fecha</th><th>Actor</th><th>Evento</th><th>Detalle</th></tr></thead>
                            <tbody>
                            @foreach($auditorias as $audit)
                                <tr>
                                    <td class="text-nowrap">{{ $audit->created_at->format('d-m-Y H:i:s') }}</td>
                                    <td>{{ ucfirst($audit->usuario_tipo) }}</td>
                                    <td><code>{{ $audit->accion }}</code></td>
                                    <td>{{ $audit->descripcion }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
