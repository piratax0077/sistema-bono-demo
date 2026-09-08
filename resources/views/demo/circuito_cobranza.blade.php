@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="mb-4">
        <span class="badge bg-primary">CIRCUITO MULTIBONO</span>
        <h2 class="mt-2 mb-1">Paciente → Centro Médico → Profesional → Administración → Contraloría</h2>
        <p class="text-muted">Dos bonos consolidados en una rendición y un depósito a la cuenta registrada del profesional.</p>
    </div>
    <div class="row g-3 mb-4">
        @foreach(['Compra paciente','Envío QR','Recepción asistente','Atención profesional','Envío a cobro','Rendición administrativa','Depósito profesional','Archivo contraloría'] as $i => $paso)
            <div class="col-md-3"><div class="card h-100 border-success"><div class="card-body"><small class="text-success fw-bold">{{ $i+1 }}</small><div class="fw-semibold">{{ $paso }}</div><span class="badge bg-success mt-2">Completado</span></div></div></div>
        @endforeach
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold">Bonos incluidos</div>
        <div class="table-responsive"><table class="table mb-0 align-middle"><thead><tr><th>Bono</th><th>Paciente</th><th>Prestación</th><th>Copago</th><th>Cobro profesional</th><th>Estado</th></tr></thead><tbody>
        @foreach($vouchers as $voucher)<tr><td><code>{{ $voucher->codigo }}</code></td><td>{{ $voucher->cliente_nombre }}</td><td>{{ $voucher->tipo_servicio }}</td><td>${{ number_format($voucher->copago_usuario,0,',','.') }}</td><td>${{ number_format(optional($voucher->cobros->first())->monto_cobrado,0,',','.') }}</td><td><span class="badge bg-success">Pagado</span></td></tr>@endforeach
        </tbody></table></div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-lg-6"><div class="card h-100 shadow-sm"><div class="card-header fw-bold">Rendición administrativa</div><div class="card-body"><p><strong>Centro:</strong> {{ $rendicion->sucursal }}</p><p><strong>Cantidad:</strong> {{ $rendicion->cantidad_vouchers }} bonos</p><p><strong>Total rendido:</strong> ${{ number_format($rendicion->total_cobrado,0,',','.') }}</p><span class="badge bg-success">{{ strtoupper($rendicion->estado) }}</span></div></div></div>
        @php($liquidacion = $rendicion->liquidaciones->first())
        <div class="col-lg-6"><div class="card h-100 shadow-sm"><div class="card-header fw-bold">Depósito al profesional</div><div class="card-body"><p><strong>Profesional:</strong> {{ $liquidacion->profesional_nombre }}</p><p><strong>Cuenta:</strong> {{ $liquidacion->banco }} · {{ $liquidacion->tipo_cuenta }} · {{ $liquidacion->numero_cuenta }}</p><p><strong>Monto:</strong> ${{ number_format($liquidacion->monto_profesional,0,',','.') }}</p><p><strong>Comprobante:</strong> {{ $liquidacion->comprobante_transferencia }}</p></div></div></div>
    </div>
    <div class="card shadow-sm"><div class="card-header fw-bold">Registro permanente para Contraloría</div><div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Fecha</th><th>Bono</th><th>Actor</th><th>Evento</th><th>Detalle</th></tr></thead><tbody>
    @foreach($auditorias as $audit)<tr><td>{{ $audit->created_at->format('d-m-Y H:i:s') }}</td><td>{{ optional($vouchers->firstWhere('id',$audit->voucher_id))->codigo }}</td><td>{{ $audit->usuario_tipo }}</td><td><code>{{ $audit->accion }}</code></td><td>{{ $audit->descripcion }}</td></tr>@endforeach
    </tbody></table></div></div>
</div>
@endsection
