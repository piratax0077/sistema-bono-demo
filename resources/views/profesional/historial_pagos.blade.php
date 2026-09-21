<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Historial de pagos · Profesional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .history-shell{max-width:1400px}.page-kicker{color:#1848a1;font-size:.75rem;font-weight:850;letter-spacing:.16em;text-transform:uppercase}.summary-card{border:1px solid #d8e3f0!important;border-radius:18px!important}.summary-card strong{display:block;color:#087b68;font-size:1.65rem}.history-card{overflow:hidden;border-radius:22px!important}.history-filters{display:grid;grid-template-columns:minmax(240px,1.5fr) repeat(2,minmax(170px,.6fr));gap:.75rem}.payment-state{display:inline-block;padding:.35rem .6rem;border-radius:999px;background:#edf4ff;color:#1848a1;font-size:.75rem;font-weight:850;text-transform:capitalize}.payment-state.is-paid{background:#dff5ed;color:#087555}.history-actions{display:flex;justify-content:flex-end;gap:.35rem;flex-wrap:wrap}@media(max-width:760px){.history-filters{grid-template-columns:1fr}}
    </style>
</head>
<body>
@include('partials.demo_user_switcher')
<main class="container history-shell py-5">
    <header class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div><div class="page-kicker">Liquidaciones y trazabilidad</div><h1 class="h2 fw-bold mb-1">Historial de pagos</h1><p class="text-muted mb-0">Consulta bonos, cobros, auditorías y depósitos asociados a tus atenciones.</p></div>
        <!-- <a href="{{ route('profesional.cobros') }}" class="btn btn-primary">Ir a Gestión de cobros</a> -->
    </header>

    <section class="row g-3 mb-4">
        @foreach($resumen as $nombre => $valor)
            <div class="col-6 col-lg-3"><div class="card summary-card h-100"><div class="card-body"><strong>{{ $valor }}</strong><span class="text-muted text-capitalize">{{ str_replace('_', ' ', $nombre) }}</span></div></div></div>
        @endforeach
    </section>

    <section class="card history-card border-0 shadow-sm">
        <div class="card-header bg-white p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3"><div><div class="page-kicker">Registro consolidado</div><h2 class="h4 mb-0">Operaciones del profesional</h2></div><span class="badge bg-light text-dark border" id="historialCantidad">{{ $vouchers->count() }} registros</span></div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Bono</th><th>Paciente</th><th>Prestación</th><th>Fecha</th><th>Pago del bono</th><th>Cobro y auditoría</th><th>Depósito</th><th class="text-end">Respaldo</th></tr></thead>
                <tbody id="historialPagosBody">
                @forelse($vouchers as $voucher)
                    @php
                        $pago = $voucher->pagos->sortByDesc('id')->first();
                        $cobro = $voucher->cobros->sortByDesc('id')->first();
                        $estadoFiltro = $cobro?->pago_estado === 'depositado' ? 'depositado' : (in_array($cobro?->estado, ['pendiente_auditoria', 'observado_auditoria']) ? 'auditoria' : ($pago ? 'pagado' : 'pendiente'));
                    @endphp
                    <tr class="historial-pago-row" data-search="{{ strtolower($voucher->codigo.' '.($voucher->beneficiario_nombre ?: $voucher->cliente_nombre).' '.$voucher->tipo_servicio) }}" data-estado="{{ $estadoFiltro }}">
                        <td><strong>{{ $voucher->codigo }}</strong><small class="d-block text-muted">{{ $voucher->estado }}</small></td>
                        <td>{{ $voucher->beneficiario_nombre ?: $voucher->cliente_nombre }}</td>
                        <td>{{ $voucher->tipo_servicio ?: 'Atención médica' }}<small class="d-block text-muted">{{ $voucher->prestador_nombre }}</small></td>
                        <td>{{ optional($voucher->created_at)->format('d-m-Y H:i') }}</td>
                        <td><span class="payment-state {{ $pago ? 'is-paid' : '' }}">{{ $pago?->estado_pago ?? 'pendiente' }}</span>@if($pago)<small class="d-block mt-1">${{ number_format($pago->monto ?? $voucher->copago_usuario,0,',','.') }}</small>@endif</td>
                        <td>{{ $cobro?->estado ? str_replace('_',' ',$cobro->estado) : 'No enviado' }}@if($cobro?->auditado_at)<small class="d-block text-muted">{{ $cobro->auditado_at->format('d-m-Y H:i') }}</small>@endif</td>
                        <td><span class="payment-state {{ $cobro?->pago_estado === 'depositado' ? 'is-paid' : '' }}">{{ $cobro?->pago_estado ?? 'pendiente' }}</span>@if($cobro?->deposito_comprobante)<small class="d-block text-muted mt-1">{{ $cobro->deposito_comprobante }}</small>@endif</td>
                        <td><div class="history-actions">@if($voucher->qr_token && $voucher->estado === 'activo')<a href="{{ route('vouchers.qr', $voucher->qr_token) }}" class="btn btn-sm btn-outline-success">Ver QR</a><a href="{{ route('vouchers.qr.lectorDemo', $voucher->qr_token) }}" class="btn btn-sm btn-outline-primary">Lector</a>@else<span class="text-muted small">Sin QR vigente</span>@endif</div></td>
                    </tr>
                @empty<tr><td colspan="8" class="text-center text-muted py-5">Todavía no existen operaciones asociadas.</td></tr>@endforelse
                <tr id="historialSinResultados" class="d-none"><td colspan="8" class="text-center text-muted py-5">No se encontraron operaciones con estos filtros.</td></tr>
                </tbody>
            </table>
        </div>
    </section>
</main>
@include('partials.demo_footer')
</body>
</html>
