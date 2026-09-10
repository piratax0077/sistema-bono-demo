<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cobros Profesional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .cobro-modal-backdrop { align-items:center; background:rgba(9,35,30,.6); display:flex; inset:0; justify-content:center; padding:22px; position:fixed; z-index:1050; }
        .cobro-modal-backdrop[hidden] { display:none !important; }
        .cobro-modal { background:#eef5f3; border-radius:22px; box-shadow:0 30px 90px rgba(0,0,0,.28); max-height:92vh; max-width:1050px; overflow:auto; width:100%; }
        .cobro-modal .qr-frame { background:#fff; border:1px solid #d8e7e3; border-radius:20px; display:inline-block; padding:18px; }
        .cobro-modal .data-label { color:#6b7f7a; font-size:.77rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }
        .cobro-modal .data-value { font-weight:650; margin-bottom:1rem; }
        .cobro-modal .security-box { background:#e8f8f2; border:1px solid #b8e3d3; border-radius:14px; }
        .qr-action{display:inline-grid!important;place-items:center;width:46px;height:46px;padding:8px!important}.qr-action svg{width:25px;height:25px}
    </style>
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
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">OK para cobro</small><h2>{{ $bonosClinicos->where('estado', 'validado_atencion')->count() }}</h2></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Pendientes de auditoría</small><h2>{{ $pendientesAuditoria->count() }}</h2></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Con visto bueno</small><h2>{{ $pendientesRendicion->count() }}</h2></div></div></div>
        <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><small class="text-muted">Total habilitado para rendir</small><h2>${{ number_format($pendientesRendicion->sum('monto_cobrado'), 0, ',', '.') }}</h2></div></div></div>
    </div>

    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-header bg-white py-3"><h4 class="mb-0">Atenciones cerradas</h4><small class="text-muted">El cierre clínico validado automáticamente habilita el botón Cobrar.</small></div>
        <form method="POST" action="{{ route('profesional.cobros.seleccionados') }}" id="formCobrosSeleccionados">@csrf
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th><input type="checkbox" class="form-check-input" id="seleccionarTodosCobros" aria-label="Seleccionar todos"></th><th>Bono</th><th>Paciente</th><th>Hora Med-SDI</th><th>Diagnóstico</th><th>Validación</th><th class="text-end">QR</th></tr></thead>
                <tbody>
                @forelse($bonosClinicos as $voucher)
                    <tr>
                        <td>@if($voucher->estado === 'validado_atencion')<input type="checkbox" class="form-check-input cobro-seleccion" name="voucher_ids[]" value="{{ $voucher->id }}" aria-label="Seleccionar bono {{ $voucher->codigo }}">@endif</td>
                        <td><strong>{{ $voucher->codigo }}</strong></td>
                        <td>{{ $voucher->cliente_nombre }}</td>
                        <td>
                            @if(optional($voucher->agenda)->medichile_hora_medica_id)
                                <span class="badge bg-light text-dark border">#{{ $voucher->agenda->medichile_hora_medica_id }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td style="max-width:340px">{{ \Illuminate\Support\Str::limit(optional($voucher->atencion)->diagnostico, 90) }}</td>
                        <td>
                            @if($voucher->estado === 'validado_atencion')
                                <span class="badge bg-success">✓ OK</span>
                            @else
                                <span class="badge bg-warning text-dark">Revisión excepcional</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($voucher->estado === 'validado_atencion')
                                <button type="button" class="btn btn-outline-primary qr-action" data-cobro-qr-url="{{ route('profesional.cobros.qr.datos', $voucher->id) }}" title="Ver QR de cobro" aria-label="Ver QR de cobro del bono {{ $voucher->codigo }}">
                                    <svg viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M3 3h8v8H3V3Zm2 2v4h4V5H5Zm8-2h8v8h-8V3Zm2 2v4h4V5h-4ZM3 13h8v8H3v-8Zm2 2v4h4v-4H5Zm8-2h3v3h-3v-3Zm5 0h3v5h-2v-2h-1v-3Zm-5 5h2v3h-2v-3Zm4 0h2v2h2v1h-4v-3Z"/></svg>
                                </button>
                            @else
                                <span class="text-muted small">Requiere revisión</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No hay atenciones cerradas pendientes de cobro.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-3 py-3"><span class="text-muted" id="resumenSeleccionCobros">0 bonos seleccionados</span><button type="submit" class="btn btn-primary" id="enviarSeleccionadosCobros" disabled>Enviar seleccionados a cobros</button></div>
        </form>
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

<div class="cobro-modal-backdrop" id="cobroQrModal" hidden>
    <div class="cobro-modal" role="dialog" aria-modal="true" aria-labelledby="cobroQrTitle">
        <div class="p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <div class="text-uppercase text-success fw-bold small">Expediente digital de cobro</div>
                    <h2 class="fw-bold mb-1" id="cobroQrTitle">QR para enviar a cobro</h2>
                    <p class="text-muted mb-0" id="cobroQrSubtitulo">Enlace firmado válido durante 24 horas.</p>
                </div>
                <button type="button" class="btn btn-outline-secondary" id="closeCobroQrModal" aria-label="Cerrar">Cerrar</button>
            </div>

            <div class="row g-5 align-items-center">
                <div class="col-lg-5 text-center">
                    <div class="qr-frame">
                        <img id="cobroQrImagen" src="" alt="QR seguro de cobro" width="330" height="330" class="img-fluid">
                    </div>
                    <div class="mt-3"><span class="badge bg-success px-3 py-2">Habilitado para cobro</span></div>
                </div>
                <div class="col-lg-7">
                    <h4 class="fw-bold mb-4">Datos asociados al QR</h4>
                    <div class="row">
                        <div class="col-sm-6"><div class="data-label">Paciente</div><div class="data-value" id="cobroQrPaciente"></div></div>
                        <div class="col-sm-6"><div class="data-label">Profesional</div><div class="data-value" id="cobroQrProfesional"></div></div>
                        <div class="col-12"><div class="data-label">Relación</div><div class="data-value" id="cobroQrRelacion"></div></div>
                        <div class="col-sm-6"><div class="data-label">Lugar de atención</div><div class="data-value" id="cobroQrLugar"></div></div>
                        <div class="col-sm-6"><div class="data-label">Fecha de atención</div><div class="data-value" id="cobroQrFecha"></div></div>
                        <div class="col-sm-6"><div class="data-label">Tipo de atención</div><div class="data-value" id="cobroQrTipo"></div></div>
                        <div class="col-sm-6"><div class="data-label">Valor a cobrar</div><div class="data-value fs-4 text-success" id="cobroQrValor"></div></div>
                    </div>

                    <div class="security-box p-3 mb-4">
                        <strong>Validación segura.</strong> El enlace comprueba la firma y la vigencia antes de mostrar el expediente. No contiene comisión, cuenta bancaria ni liquidaciones internas.
                    </div>

                    <div class="d-grid gap-2 d-sm-flex">
                        <form method="POST" id="cobroQrForm" action="" class="flex-grow-1">
                            @csrf
                            <button class="btn btn-success w-100 fw-bold">Enviar QR a cobro</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/plugins/sweetalert.min.js') }}"></script>
<script>
(() => {
    const form = document.getElementById('formCobrosSeleccionados');
    const todos = document.getElementById('seleccionarTodosCobros');
    const checks = [...document.querySelectorAll('.cobro-seleccion')];
    const boton = document.getElementById('enviarSeleccionadosCobros');
    const resumen = document.getElementById('resumenSeleccionCobros');
    const actualizar = () => {
        const cantidad = checks.filter(check => check.checked).length;
        resumen.textContent = cantidad + (cantidad === 1 ? ' bono seleccionado' : ' bonos seleccionados');
        boton.disabled = cantidad === 0;
        todos.checked = checks.length > 0 && cantidad === checks.length;
        todos.indeterminate = cantidad > 0 && cantidad < checks.length;
    };
    todos?.addEventListener('change', () => { checks.forEach(check => check.checked = todos.checked); actualizar(); });
    checks.forEach(check => check.addEventListener('change', actualizar));
    form?.addEventListener('submit', event => {
        event.preventDefault();
        const cantidad = checks.filter(check => check.checked).length;
        if (!cantidad) return;
        if (typeof swal !== 'function') {
            if (confirm('¿Enviar ' + cantidad + ' bonos seleccionados a cobros?')) form.submit();
            return;
        }
        swal({title:'¿Enviar a cobros?', text:'Se enviarán ' + cantidad + (cantidad === 1 ? ' bono seleccionado.' : ' bonos seleccionados.'), icon:'warning', buttons:['Cancelar','Sí, enviar']})
            .then(confirmado => { if (confirmado) form.submit(); });
    });
    actualizar();
})();

(() => {
    const modal = document.getElementById('cobroQrModal');
    const closeButton = document.getElementById('closeCobroQrModal');

    const abrir = async (url) => {
        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('No se pudo generar el QR de cobro.');
            const data = await response.json();

            document.getElementById('cobroQrSubtitulo').textContent = 'Bono ' + data.codigo_bono + ' · enlace firmado válido durante 24 horas.';
            document.getElementById('cobroQrImagen').src = data.qr_data_uri;
            document.getElementById('cobroQrImagen').alt = 'QR seguro de cobro ' + data.codigo_bono;
            document.getElementById('cobroQrPaciente').textContent = data.paciente;
            document.getElementById('cobroQrProfesional').textContent = data.profesional;
            document.getElementById('cobroQrRelacion').textContent = data.relacion;
            document.getElementById('cobroQrLugar').textContent = data.lugar_atencion;
            document.getElementById('cobroQrFecha').textContent = data.fecha_atencion;
            document.getElementById('cobroQrTipo').textContent = data.tipo_atencion;
            document.getElementById('cobroQrValor').textContent = '$' + new Intl.NumberFormat('es-CL').format(data.valor_a_cobrar);
            document.getElementById('cobroQrForm').action = data.cobrar_url;

            modal.hidden = false;
        } catch (error) {
            alert(error.message);
        }
    };

    document.querySelectorAll('[data-cobro-qr-url]').forEach((button) => {
        button.addEventListener('click', () => abrir(button.dataset.cobroQrUrl));
    });

    closeButton?.addEventListener('click', () => { modal.hidden = true; });
    modal.addEventListener('click', (event) => { if (event.target === modal) modal.hidden = true; });
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !modal.hidden) modal.hidden = true; });
})();
</script>
</body>
</html>
