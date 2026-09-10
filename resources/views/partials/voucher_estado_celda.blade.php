@php
    $estadoTexto = [
        'pendiente_confirmacion' => 'Esperando confirmación',
        'pendiente_pago' => 'Esperando pago',
        'activo' => 'Pagado / activo',
    ][$voucher->estado] ?? str_replace('_', ' ', $voucher->estado);
@endphp
<span class="badge {{ $voucher->estado === 'activo' ? 'text-bg-success' : ($voucher->estado === 'pendiente_pago' ? 'text-bg-warning' : 'text-bg-info') }}">
    {{ $estadoTexto }}
</span>
@if(optional($voucher->agenda)->medichile_estado_id)
    @php
        $medsdiEstadoTexto = [
            1 => 'Reservada',
            2 => 'Confirmada',
            3 => 'Rechazada',
            4 => 'En espera',
            5 => 'Realizando',
            6 => 'Realizada',
            7 => 'Inasistida',
        ][$voucher->agenda->medichile_estado_id] ?? 'Estado '.$voucher->agenda->medichile_estado_id;
    @endphp
    <div class="small text-muted mt-1">Med-SDI: {{ $medsdiEstadoTexto }}</div>
    @if($voucher->agenda->medichile_sincronizado_at)
        <div class="small text-muted">Sincronizado {{ $voucher->agenda->medichile_sincronizado_at->diffForHumans() }}</div>
    @endif
    @if($voucher->agenda->medichile_sync_error)
        <div class="small text-danger">{{ $voucher->agenda->medichile_sync_error }}</div>
    @endif
@endif
