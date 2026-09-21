@php
    $estadoTexto = ['pendiente_confirmacion' => 'Esperando confirmación', 'pendiente_pago' => 'Esperando pago', 'activo' => 'Pagado y activo', 'asignado' => 'Hora asignada'][$voucher->estado] ?? str_replace('_', ' ', $voucher->estado);
    $estadoClase = ['activo' => 'is-success', 'pendiente_pago' => 'is-warning', 'pendiente_confirmacion' => 'is-info', 'asignado' => 'is-info'][$voucher->estado] ?? 'is-neutral';
@endphp
<div class="voucher-status">
    <span class="voucher-status__pill {{ $estadoClase }}"><span class="voucher-status__dot"></span>{{ ucfirst($estadoTexto) }}</span>
    @if(optional($voucher->agenda)->medichile_estado_id)
        @php
            $medsdiEstado = [1 => ['Reservada', 'is-info'], 2 => ['Confirmada', 'is-success'], 3 => ['Rechazada', 'is-danger'], 4 => ['Paciente en espera', 'is-warning'], 5 => ['Atención en curso', 'is-info'], 6 => ['Atención realizada', 'is-success'], 7 => ['Inasistencia', 'is-danger']][$voucher->agenda->medichile_estado_id] ?? ['Estado '.$voucher->agenda->medichile_estado_id, 'is-neutral'];
        @endphp
        <div class="voucher-status__remote"><span class="voucher-status__remote-dot {{ $medsdiEstado[1] }}"></span><span>Med-SDI</span><strong>{{ $medsdiEstado[0] }}</strong></div>
        @if($voucher->agenda->medichile_sincronizado_at)
            <div class="voucher-status__sync">Actualizado {{ $voucher->agenda->medichile_sincronizado_at->diffForHumans() }}</div>
        @endif
        @if($voucher->agenda->medichile_sync_error)
            <div class="voucher-status__error">{{ $voucher->agenda->medichile_sync_error }}</div>
        @endif
    @endif
</div>
