@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 980px">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <span class="badge bg-dark mb-2">LECTOR QR SIMULADO</span>
            <h2 class="mb-1">Datos leídos del bono</h2>
            <p class="text-muted mb-0">Esta vista representa la información que recupera un lector autorizado.</p>
        </div>
        <a href="{{ route('vouchers.qr', $voucher->qr_token) }}" class="btn btn-outline-secondary">Volver al QR</a>
    </div>

    <div class="alert {{ $firmaValida ? 'alert-success' : 'alert-danger' }}">
        <strong>{{ $firmaValida ? 'Lectura correcta y firma válida.' : 'Advertencia: la firma no coincide.' }}</strong>
        El lector verificó la integridad del contenido con HMAC-SHA256.
    </div>

    @php
        $pacienteQr = $qrPayload['beneficiario_seleccionado'] ?? $qrPayload['titular'];
        $edadQr = $pacienteQr['edad'] ?? $qrPayload['titular']['edad'] ?? null;
        $direccionQr = $pacienteQr['direccion'] ?? $qrPayload['titular']['direccion'] ?? null;
        $grupoQr = $pacienteQr['grupo_ingreso'] ?? $qrPayload['titular']['grupo_ingreso'] ?? null;
        $formatearRut = static function ($rut) {
            $limpio = strtoupper(preg_replace('/[^0-9K]/i', '', (string) $rut));
            if (strlen($limpio) < 2) return $rut ?: 'Sin dato';
            return number_format((int) substr($limpio, 0, -1), 0, ',', '.').'-'.substr($limpio, -1);
        };
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-dark text-white fw-bold">Identificación del bono</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Código</dt><dd class="col-7"><code>{{ $qrPayload['voucher']['codigo'] }}</code></dd>
                        <dt class="col-5">Estado</dt><dd class="col-7"><span class="badge bg-success">{{ strtoupper($qrPayload['voucher']['estado']) }}</span></dd>
                        <dt class="col-5">Servicio</dt><dd class="col-7">{{ $qrPayload['voucher']['servicio'] }}</dd>
                        <dt class="col-5">Fecha de emisión</dt><dd class="col-7">{{ $qrPayload['emitido_en'] ? \Illuminate\Support\Carbon::parse($qrPayload['emitido_en'])->format('d-m-Y H:i:s') : 'Sin dato' }}</dd>
                        <dt class="col-5">Vencimiento</dt><dd class="col-7">{{ $qrPayload['voucher']['vence'] ? \Illuminate\Support\Carbon::parse($qrPayload['voucher']['vence'])->format('d-m-Y') : 'Sin dato' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-primary text-white fw-bold">Paciente / beneficiario</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Nombre</dt><dd class="col-7">{{ $pacienteQr['nombre'] ?? 'Sin dato' }}</dd>
                        <dt class="col-5">RUT</dt><dd class="col-7">{{ $formatearRut($pacienteQr['rut'] ?? null) }}</dd>
                        <dt class="col-5">Grupo de ingreso</dt><dd class="col-7"><span class="badge bg-info text-dark">GRUPO {{ $grupoQr ?: 'SIN DATO' }}</span></dd>
                        <dt class="col-5">Dirección</dt><dd class="col-7">{{ $direccionQr ?: 'Sin dato' }}</dd>
                        <dt class="col-5">Edad</dt><dd class="col-7">{{ $edadQr !== null ? $edadQr.' años' : 'Sin dato' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-success text-white fw-bold">Profesional asignado</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Nombre</dt><dd class="col-7">{{ $qrPayload['profesional']['nombre'] ?: 'Sin asignar' }}</dd>
                        <dt class="col-5">RUT profesional</dt><dd class="col-7">{{ $formatearRut($qrPayload['profesional']['rut'] ?? null) }}</dd>
                        <dt class="col-5">Especialidad</dt><dd class="col-7">{{ $qrPayload['profesional']['especialidad'] ?: 'Sin dato' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        @if(!empty($qrPayload['hora_medica']))
        <div class="col-lg-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-info text-white fw-bold">Hora médica solicitada</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Fecha y hora</dt><dd class="col-7">{{ $qrPayload['hora_medica']['fecha_hora'] ?? 'Sin dato' }}</dd>
                        <dt class="col-5">Estado Med-SDI</dt><dd class="col-7">{{ $qrPayload['hora_medica']['estado_texto'] ?? 'Sin sincronizar' }}</dd>
                        <dt class="col-5">Hora Med-SDI</dt><dd class="col-7">{{ $qrPayload['hora_medica']['id_medichile'] ? '#'.$qrPayload['hora_medica']['id_medichile'] : 'Sin dato' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        @endif
        <div class="col-lg-6">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-light fw-bold">Valores para el paciente</div>
                <div class="card-body">
                    <div class="row text-center g-2">
                        <div class="col-4"><small class="text-muted d-block">Valor</small><strong>${{ number_format($qrPayload['voucher']['valor'], 0, ',', '.') }}</strong></div>
                        <div class="col-4"><small class="text-muted d-block">Copago</small><strong>${{ number_format($qrPayload['voucher']['copago'], 0, ',', '.') }}</strong></div>
                        <div class="col-4"><small class="text-muted d-block">Cobertura</small><strong>${{ number_format(max($qrPayload['voucher']['valor'] - $qrPayload['voucher']['copago'], 0), 0, ',', '.') }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--
    Sección técnica reservada para revisión de código y depuración interna.
    No debe mostrarse al usuario en el lector QR simulado.
    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold">Contenido técnico exacto</div>
                <div class="card-body">
                    <p class="small text-muted">No contiene comisión, cuenta bancaria ni liquidaciones internas.</p>
                    <pre class="bg-dark text-light rounded p-3 mb-0" style="max-height: 480px; overflow:auto; white-space:pre-wrap">{{ json_encode($lectura, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </div>
    </div>
    --}}

    <div class="card shadow-sm mt-4">
        <div class="card-header fw-bold">Datos incorporados a medida que avanza el proceso</div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Etapa</th><th>Estado</th><th>Fecha</th><th>Dato registrado</th></tr></thead>
                <tbody>
                @foreach($etapas as $etapa)
                    <tr>
                        <td class="fw-semibold">{{ $etapa['etapa'] }}</td>
                        <td><span class="badge {{ $etapa['estado'] === 'completada' ? 'bg-success' : ($etapa['estado'] === 'en_proceso' ? 'bg-primary' : 'bg-secondary') }}">{{ str_replace('_', ' ', strtoupper($etapa['estado'])) }}</span></td>
                        <td>{{ $etapa['fecha'] ? \Illuminate\Support\Carbon::parse($etapa['fecha'])->format('d-m-Y H:i:s') : '—' }}</td>
                        <td>{{ $etapa['detalle'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
