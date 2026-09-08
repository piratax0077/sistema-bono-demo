<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Atención profesional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f4f7fb;">
<div class="container py-5" style="max-width: 920px;">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div class="text-uppercase text-success fw-bold small">Consulta en curso</div>
            <h2 class="fw-bold mb-1">Atención de {{ $voucher->cliente_nombre }}</h2>
            <p class="text-muted mb-0">Bono {{ $voucher->codigo }} · Hora Medichile #{{ optional($voucher->agenda)->medichile_hora_medica_id }}</p>
        </div>
        <a href="{{ url('/escritorio-profesional') }}" class="btn btn-outline-secondary">Volver</a>
    </div>

    @if(session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    @include('partials.demo_flow_guide', ['demoStep' => 5])

    <div class="row g-4 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Paciente</small><div class="fw-bold">{{ $voucher->cliente_nombre }}</div><div>{{ sdi_formatear_rut($voucher->beneficiario_rut_visible ?: $voucher->cliente_rut_visible) }}</div></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Prestación</small><div class="fw-bold">{{ $voucher->tipo_servicio }}</div><div>{{ $voucher->prestador_especialidad }}</div></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Estado Medichile</small><div class="fw-bold text-primary">Realizando</div><div>Hora #{{ optional($voucher->agenda)->medichile_hora_medica_id }}</div></div></div></div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h4 class="mb-3">Registro clínico simulado</h4>
            <form method="POST" action="{{ route('profesional.vouchers.finalizar', $voucher->id) }}">
                @csrf
                <div class="mb-3">
                    <label for="diagnostico" class="form-label fw-bold">Diagnóstico</label>
                    <textarea id="diagnostico" name="diagnostico" rows="5" class="form-control" required>{{ old('diagnostico', optional($voucher->atencion)->diagnostico ?: 'Diagnóstico de prueba: paciente estable, sin signos de alarma. Se indican cuidados generales y control según evolución.') }}</textarea>
                    <div class="form-text">Este dato queda en la atención clínica y en la auditoría del cierre.</div>
                </div>
                <div class="mb-4">
                    <label for="observacion" class="form-label">Indicaciones y observaciones</label>
                    <textarea id="observacion" name="observacion" rows="3" class="form-control">{{ old('observacion', optional($voucher->atencion)->observacion ?: 'Control médico según evolución. Consultar si aparecen nuevos síntomas.') }}</textarea>
                </div>
                <div class="alert alert-warning">
                    Al cerrar, la hora Medichile cambiará a <strong>Realizada</strong>. El sistema validará automáticamente la identidad profesional, el QR y la trazabilidad; luego habilitará el bono para cobro.
                </div>
                <button class="btn btn-success btn-lg">Guardar diagnóstico y cerrar atención</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
