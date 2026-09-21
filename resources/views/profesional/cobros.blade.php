<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        .bank-account-modal .modal-dialog{max-width:980px;margin:1rem auto}.bank-account-modal .modal-content{max-height:calc(100vh - 2rem);overflow:hidden}.bank-account-modal form{display:flex;flex-direction:column;min-height:0;overflow:hidden}.bank-account-modal .modal-body{overflow-y:auto}.bank-account-modal .modal-footer{background:#fff;flex-shrink:0}.bank-account-table{border:1px solid #d9e4f3;border-radius:14px;overflow:hidden}.bank-account-table .table{margin:0}.bank-account-table th{background:#f3f7fd;color:#52647b;font-size:.73rem;letter-spacing:.06em;text-transform:uppercase}.bank-account-table td{font-size:.9rem}.bank-account-number{align-items:center;background:#eaf1ff;border-radius:9px;color:#1848a1;display:inline-flex;font-weight:800;height:30px;justify-content:center;width:30px}@media(max-width:767px){.bank-account-modal .modal-dialog{margin:.5rem}.bank-account-modal .modal-content{max-height:calc(100vh - 1rem)}.bank-account-modal .modal-body{padding:1rem!important}}
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

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            @if(optional($programacionCobro)->activo)
                @php
                    $frecuenciasCobro = [
                        'diario' => 'todos los días',
                        'semanal' => 'cada 1 semana',
                        'quincenal' => 'cada 2 semanas',
                        'mensual' => 'una vez al mes',
                    ];
                @endphp
                <div class="alert alert-success d-flex align-items-start gap-2 mb-4" role="status">
                    <span aria-hidden="true">✓</span>
                    <div>
                        <strong>Ya tienes un cobro automático programado.</strong>
                        Se ejecutará {{ $frecuenciasCobro[$programacionCobro->frecuencia] ?? $programacionCobro->frecuencia }}.
                        @if($programacionCobro->proxima_ejecucion_at)
                            La próxima ejecución será el {{ $programacionCobro->proxima_ejecucion_at->format('d-m-Y') }} a las {{ $programacionCobro->proxima_ejecucion_at->format('H:i') }}.
                        @endif
                    </div>
                </div>
            @endif
            <form method="POST" action="{{ route('profesional.cobros.programacion') }}" class="row g-3 align-items-end">
                @csrf @method('PUT')
                <div class="col-lg-5">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge {{ optional($programacionCobro)->activo ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                            {{ optional($programacionCobro)->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                        <div>
                            <div class="fw-bold fs-5">Programar cobro automático</div>
                            <div class="text-muted">Envía juntos todos los bonos con validación ✓ OK.</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-7 col-lg-4">
                    <label for="frecuenciaCobro" class="form-label fw-semibold">Frecuencia de cobro</label>
                    <select class="form-select" id="frecuenciaCobro" name="frecuencia">
                        <option value="diario" @selected(optional($programacionCobro)->frecuencia === 'diario')>Todos los días</option>
                        <option value="semanal" @selected(! $programacionCobro || $programacionCobro->frecuencia === 'semanal')>Cada 1 semana</option>
                        <option value="quincenal" @selected(optional($programacionCobro)->frecuencia === 'quincenal')>Cada 2 semanas</option>
                        <option value="mensual" @selected(optional($programacionCobro)->frecuencia === 'mensual')>Una vez al mes</option>
                    </select>
                </div>
                <div class="col-sm-5 col-lg-3">
                    <div class="d-grid gap-2">
                        <button type="submit" name="accion" value="activar" class="btn btn-primary">Guardar y activar</button>
                        @if(optional($programacionCobro)->activo)
                            <button type="submit" name="accion" value="desactivar" class="btn btn-outline-secondary">Desactivar cobro automático</button>
                        @endif
                    </div>
                </div>
                @if(optional($programacionCobro)->activo && $programacionCobro->proxima_ejecucion_at)
                    <div class="col-12">
                        <div class="alert alert-info mb-0 py-2">
                            <strong>Próxima ejecución:</strong> {{ $programacionCobro->proxima_ejecucion_at->format('d-m-Y H:i') }}
                            @if($programacionCobro->ultima_ejecucion_at)
                                · Última ejecución: {{ $programacionCobro->ultimo_resultado }}
                            @endif
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Historial y estados de pagos</h4>
        <form method="POST" action="{{ route('profesional.cobros.generarRendicion') }}">@csrf<button class="btn btn-primary" @if($pendientesRendicion->isEmpty()) disabled @endif>Enviar a rendición</button></form>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body border-bottom bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h5 class="mb-0">Filtros</h5>
                    <small class="text-muted">Busca por bono y combina estados o fechas.</small>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="limpiarFiltrosPagos">Limpiar filtros</button>
            </div>
            <div class="row g-3">
                <div class="col-md-6 col-xl-3">
                    <label for="filtroPagoBono" class="form-label small fw-semibold">Bono</label>
                    <input type="search" class="form-control" id="filtroPagoBono" placeholder="Código del bono">
                </div>
                <div class="col-md-6 col-xl-3">
                    <label for="filtroEstadoCobro" class="form-label small fw-semibold">Estado de cobro</label>
                    <select class="form-select" id="filtroEstadoCobro">
                        <option value="">Todos</option>
                        <option value="pendiente_auditoria">Pendiente de auditoría</option>
                        <option value="observado_auditoria">Observado por auditoría</option>
                        <option value="rechazado_auditoria">Rechazado por auditoría</option>
                        <option value="pendiente_rendicion">Visto bueno aprobado</option>
                        <option value="rendido">Rendido</option>
                        <option value="pagado">Pagado</option>
                    </select>
                </div>
                <div class="col-md-6 col-xl-3">
                    <label for="filtroEstadoRendicion" class="form-label small fw-semibold">Rendición</label>
                    <select class="form-select" id="filtroEstadoRendicion"><option value="">Todas</option><option value="pendiente">Pendiente de envío</option><option value="enviada">Con rendición</option></select>
                </div>
                <div class="col-md-6 col-xl-3">
                    <label for="filtroEstadoPago" class="form-label small fw-semibold">Pago</label>
                    <select class="form-select" id="filtroEstadoPago"><option value="">Todos</option><option value="pendiente">Pendiente de pago</option><option value="pagado">Pagado</option></select>
                </div>
                <div class="col-md-6 col-xl-3">
                    <label for="filtroPagoDesde" class="form-label small fw-semibold">Cobrado desde</label>
                    <input type="date" class="form-control" id="filtroPagoDesde">
                </div>
                <div class="col-md-6 col-xl-3">
                    <label for="filtroPagoHasta" class="form-label small fw-semibold">Cobrado hasta</label>
                    <input type="date" class="form-control" id="filtroPagoHasta">
                </div>
                <div class="col-md-12 col-xl-6 d-flex align-items-end">
                    <div class="alert alert-light border py-2 px-3 mb-0 w-100" id="resumenFiltrosPagos">Mostrando {{ $cobros->count() }} registros</div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablaHistorialPagos">
                <thead class="table-light"><tr><th>Bono</th><th>Monto</th><th>Cobro</th><th>Rendición</th><th>Pago</th><th>Comprobante</th></tr></thead>
                <tbody>
                @forelse($cobros as $cobro)
                    @php
                        $liquidacion = $cobro->rendicion
                            ? $cobro->rendicion->liquidaciones->first()
                            : null;
                        $pagado = $liquidacion && $liquidacion->estado === 'pagada';
                    @endphp
                    <tr class="fila-historial-pago"
                        data-bono="{{ mb_strtolower(optional($cobro->voucher)->codigo ?: '') }}"
                        data-cobro="{{ $cobro->estado }}"
                        data-rendicion="{{ $cobro->rendicion ? 'enviada' : 'pendiente' }}"
                        data-pago="{{ $pagado ? 'pagado' : 'pendiente' }}"
                        data-fecha="{{ $cobro->cobrado_en ? \Illuminate\Support\Carbon::parse($cobro->cobrado_en)->format('Y-m-d') : '' }}">
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
                    <tr id="sinResultadosFiltrosPagos" class="d-none"><td colspan="6" class="text-center text-muted py-4">No se encontraron pagos con los filtros seleccionados.</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <div class="text-uppercase text-primary fw-bold small">Liquidaciones</div>
                <h4 class="mb-1">Cuenta bancaria del profesional</h4>
                <p class="text-muted mb-0">Administra la cuenta donde recibirás los pagos y liquidaciones.</p>
            </div>
            <button type="button" class="btn btn-outline-primary fw-bold" onclick="abrirCuentaBancariaProfesional()">🏦 Mis datos bancarios</button>
        </div>
    </div>
</div>

@php
    $cuentaBancoProfesional = $cuentaBancariaMedsdi['cuenta'] ?? [];
    $perfilBancoProfesional = $cuentaBancariaMedsdi['profesional'] ?? [];
    $erroresBancoProfesional = $errors->getBag('cuentaBancariaProfesional');
@endphp
<div class="modal fade bank-account-modal" id="modalCuentaBancariaProfesional" tabindex="-1" aria-labelledby="modalCuentaBancariaProfesionalTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0" style="border-radius:22px">
            <div class="modal-header text-white" style="background:linear-gradient(120deg,#1848a1,#31bebe);border-radius:22px 22px 0 0">
                <div>
                    <div class="small fw-bold text-uppercase opacity-75">Liquidaciones Med-SDI</div>
                    <h2 class="h4 modal-title text-white mb-0" id="modalCuentaBancariaProfesionalTitulo">Datos de cuenta bancaria</h2>
                </div>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarCuentaBancariaProfesional()" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="{{ route('profesional.cuenta_bancaria.actualizar') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="cuenta_id" id="profesionalCuentaId" value="{{ old('cuenta_id', $cuentaBancoProfesional['id'] ?? '') }}">
                <div class="modal-body p-4">
                    @if(! ($cuentaBancariaMedsdi['ok'] ?? false))
                        <div class="alert alert-warning">{{ $cuentaBancariaMedsdi['mensaje'] ?? 'No fue posible consultar Med-SDI.' }}</div>
                    @elseif(collect($cuentaBancariaMedsdi['cuentas'] ?? [])->contains(fn ($cuenta) => blank($cuenta['banco'] ?? null) || blank($cuenta['numero_cuenta'] ?? null)))
                        <div class="alert alert-warning">Hay cuentas históricas cuyos datos protegidos no pudieron recuperarse. Revisa la clave de cifrado histórica de Med-SDI antes de editarlas.</div>
                    @elseif(!$cuentaBancoProfesional)
                        <div class="alert alert-info">Aún no tienes una cuenta bancaria registrada. Completa los datos para recibir futuras liquidaciones.</div>
                    @else
                        <div class="alert alert-success">Cuenta obtenida desde Med-SDI. Puedes actualizarla a continuación.</div>
                    @endif
                    @if($erroresBancoProfesional->any())
                        <div class="alert alert-danger">{{ $erroresBancoProfesional->first() }}</div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <div><strong>Cuentas registradas</strong><span class="badge bg-light text-dark border ms-2">{{ count($cuentaBancariaMedsdi['cuentas'] ?? []) }}</span></div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="profesionalNuevaCuenta">+ Nueva cuenta</button>
                    </div>
                    <div class="bank-account-table table-responsive mb-4">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>#</th><th>Banco y cuenta</th><th>Tipo</th><th>Estado</th><th class="text-end">Acción</th></tr></thead>
                            <tbody>
                            @forelse($cuentaBancariaMedsdi['cuentas'] ?? [] as $indice => $cuenta)
                                <tr>
                                    <td><span class="bank-account-number">{{ $cuenta['numero'] ?? $indice + 1 }}</span></td>
                                    <td><strong>{{ $cuenta['banco'] ?: 'Banco no disponible' }}</strong><div class="small text-muted">{{ filled($cuenta['numero_cuenta'] ?? null) ? 'Terminada en '.substr((string) $cuenta['numero_cuenta'], -4) : 'Número no disponible' }}</div></td>
                                    <td>{{ $cuenta['tipo_cuenta'] ?: 'No disponible' }}</td>
                                    <td>@if(!empty($cuenta['principal']))<span class="badge bg-success">Principal</span>@else<span class="badge bg-secondary">Secundaria</span>@endif</td>
                                    <td class="text-end"><div class="d-inline-flex flex-wrap justify-content-end gap-1"><button type="button" class="btn btn-sm btn-outline-primary profesional-editar-cuenta" data-cuenta-id="{{ $cuenta['id'] }}">Editar</button><button type="button" class="btn btn-sm btn-outline-info profesional-autorizar-cuenta" data-cuenta-id="{{ $cuenta['id'] }}">Autorizar App</button><button type="button" class="btn btn-sm btn-outline-danger profesional-eliminar-cuenta" data-cuenta-id="{{ $cuenta['id'] }}">Eliminar cuenta</button></div></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No hay cuentas bancarias registradas.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="row g-3" id="profesionalCuentaCampos">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Titular</label>
                            <input class="form-control" name="titular" value="{{ old('titular', $cuentaBancoProfesional['titular'] ?? $perfilBancoProfesional['nombre'] ?? '') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">RUT del titular</label>
                            <input class="form-control" value="{{ sdi_formatear_rut($cuentaBancoProfesional['rut'] ?? $perfilBancoProfesional['rut'] ?? '') }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Banco</label>
                            <select class="form-select" name="banco_id" required>
                                <option value="">Seleccione</option>
                                @foreach($cuentaBancariaMedsdi['bancos'] ?? [] as $banco)
                                    <option value="{{ $banco['id'] }}" @selected((string) old('banco_id', $cuentaBancoProfesional['banco_id'] ?? '') === (string) $banco['id'])>{{ $banco['nombre'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo de cuenta</label>
                            <select class="form-select" name="tipo_cuenta" required>
                                <option value="">Seleccione</option>
                                @foreach($cuentaBancariaMedsdi['tipos_cuenta'] ?? [] as $tipo)
                                    <option value="{{ $tipo['descripcion'] }}" @selected(old('tipo_cuenta', $cuentaBancoProfesional['tipo_cuenta'] ?? '') === $tipo['descripcion'])>{{ $tipo['descripcion'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Número de cuenta</label>
                            <input class="form-control" name="numero_cuenta" value="{{ old('numero_cuenta', $cuentaBancoProfesional['numero_cuenta'] ?? '') }}" autocomplete="off" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Correo para notificaciones</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email', $cuentaBancoProfesional['email'] ?? $perfilBancoProfesional['email'] ?? '') }}" required>
                        </div>
                    </div>
                    <p class="small text-muted mt-3 mb-0">Los datos se almacenan cifrados en Med-SDI. Al guardar una cuenta, quedará como principal y las demás pasarán a secundarias.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="cerrarCuentaBancariaProfesional()">Cancelar</button>
                    <button class="btn btn-primary" @disabled(! ($cuentaBancariaMedsdi['ok'] ?? false))>Guardar cambios</button>
                </div>
            </form>
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
function abrirCuentaBancariaProfesional() {
    const modal = document.getElementById('modalCuentaBancariaProfesional');
    if (!modal) return;
    modal.style.display = 'block';
    modal.classList.add('show');
    modal.removeAttribute('aria-hidden');
    modal.setAttribute('aria-modal', 'true');
    document.body.classList.add('modal-open');
    if (!document.getElementById('fondoCuentaBancariaProfesional')) {
        const fondo = document.createElement('div');
        fondo.id = 'fondoCuentaBancariaProfesional';
        fondo.className = 'modal-backdrop fade show';
        fondo.addEventListener('click', cerrarCuentaBancariaProfesional);
        document.body.appendChild(fondo);
    }
}

function cerrarCuentaBancariaProfesional() {
    const modal = document.getElementById('modalCuentaBancariaProfesional');
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');
    }
    document.body.classList.remove('modal-open');
    document.getElementById('fondoCuentaBancariaProfesional')?.remove();
}

const cuentasBancariasProfesional = @json($cuentaBancariaMedsdi['cuentas'] ?? []);
function cargarCuentaBancariaProfesional(cuenta) {
    const form = document.querySelector('#modalCuentaBancariaProfesional form');
    if (!form) return;
    form.querySelector('[name="cuenta_id"]').value = cuenta?.id || '';
    form.querySelector('[name="titular"]').value = cuenta?.titular || @json($perfilBancoProfesional['nombre'] ?? '');
    form.querySelector('[name="banco_id"]').value = cuenta?.banco_id || '';
    form.querySelector('[name="tipo_cuenta"]').value = cuenta?.tipo_cuenta || '';
    form.querySelector('[name="numero_cuenta"]').value = cuenta?.numero_cuenta || '';
    form.querySelector('[name="email"]').value = cuenta?.email || @json($perfilBancoProfesional['email'] ?? '');
}
document.querySelectorAll('.profesional-editar-cuenta').forEach(button => button.addEventListener('click', () => cargarCuentaBancariaProfesional(cuentasBancariasProfesional.find(cuenta => String(cuenta.id) === button.dataset.cuentaId))));
document.querySelectorAll('.profesional-autorizar-cuenta').forEach(button => button.addEventListener('click', async () => {
    const confirmado = typeof swal === 'function' ? await swal({title:'¿Autorizar esta cuenta en la App?',text:'Se enviará una confirmación de los datos bancarios a la App del profesional.',icon:'warning',buttons:['Cancelar','Autorizar App']}) : confirm('¿Autorizar esta cuenta en la App?');
    if (!confirmado) return;
    button.disabled = true;
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('#modalCuentaBancariaProfesional input[name="_token"]')?.value;
        if (!csrf) throw new Error('No se encontró el token de seguridad. Recarga la página e inténtalo nuevamente.');
        const response = await fetch('{{ route('profesional.cuenta_bancaria.notificar') }}', {method:'POST',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({cuenta_id:button.dataset.cuentaId})});
        const data = await response.json();
        if (!response.ok) throw new Error(data.mensaje || 'No fue posible solicitar la autorización.');
        if (typeof swal === 'function') await swal({title:'Autorización enviada',text:data.mensaje,icon:'success',button:'Aceptar'}); else alert(data.mensaje);
    } catch (error) {
        if (typeof swal === 'function') await swal({title:'No se pudo autorizar',text:error.message,icon:'error',button:'Aceptar'}); else alert(error.message);
    } finally { button.disabled = false; }
}));
document.querySelectorAll('.profesional-eliminar-cuenta').forEach(button => button.addEventListener('click', async () => {
    const confirmado = typeof swal === 'function' ? await swal({title:'¿Eliminar esta cuenta bancaria?',text:'La cuenta dejará de estar disponible y se enviará una confirmación a la App del profesional.',icon:'warning',buttons:['Cancelar','Eliminar y confirmar']}) : confirm('¿Eliminar esta cuenta y enviar confirmación a la App?');
    if (!confirmado) return;
    button.disabled = true;
    try {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('#modalCuentaBancariaProfesional input[name="_token"]')?.value;
        if (!csrf) throw new Error('No se encontró el token de seguridad. Recarga la página e inténtalo nuevamente.');
        const response = await fetch('{{ route('profesional.cuenta_bancaria.eliminar') }}', {method:'DELETE',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({cuenta_id:button.dataset.cuentaId})});
        const data = await response.json();
        if (!response.ok) throw new Error(data.mensaje || 'No fue posible eliminar la cuenta bancaria.');
        if (typeof swal === 'function') await swal({title:'Cuenta eliminada',text:data.mensaje,icon:'success',button:'Aceptar'}); else alert(data.mensaje);
        window.location.reload();
    } catch (error) {
        button.disabled = false;
        if (typeof swal === 'function') await swal({title:'No se pudo eliminar',text:error.message,icon:'error',button:'Aceptar'}); else alert(error.message);
    }
}));
document.getElementById('profesionalNuevaCuenta')?.addEventListener('click', () => {
    cargarCuentaBancariaProfesional(null);
});

@if(session('abrir_cuenta_bancaria_profesional') || $erroresBancoProfesional->any())
document.addEventListener('DOMContentLoaded', abrirCuentaBancariaProfesional);
@endif

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

(() => {
    const filtros = {
        bono: document.getElementById('filtroPagoBono'),
        cobro: document.getElementById('filtroEstadoCobro'),
        rendicion: document.getElementById('filtroEstadoRendicion'),
        pago: document.getElementById('filtroEstadoPago'),
        desde: document.getElementById('filtroPagoDesde'),
        hasta: document.getElementById('filtroPagoHasta'),
    };
    const filas = [...document.querySelectorAll('.fila-historial-pago')];
    const resumen = document.getElementById('resumenFiltrosPagos');
    const sinResultados = document.getElementById('sinResultadosFiltrosPagos');

    const aplicarFiltros = () => {
        const bono = filtros.bono.value.trim().toLocaleLowerCase('es');
        let visibles = 0;

        filas.forEach(fila => {
            const coincide = (!bono || fila.dataset.bono.includes(bono))
                && (!filtros.cobro.value || fila.dataset.cobro === filtros.cobro.value)
                && (!filtros.rendicion.value || fila.dataset.rendicion === filtros.rendicion.value)
                && (!filtros.pago.value || fila.dataset.pago === filtros.pago.value)
                && (!filtros.desde.value || fila.dataset.fecha >= filtros.desde.value)
                && (!filtros.hasta.value || fila.dataset.fecha <= filtros.hasta.value);
            fila.classList.toggle('d-none', !coincide);
            if (coincide) visibles++;
        });

        resumen.textContent = `Mostrando ${visibles} de ${filas.length} registros`;
        sinResultados?.classList.toggle('d-none', visibles !== 0 || filas.length === 0);
    };

    Object.values(filtros).forEach(control => {
        control?.addEventListener(control.tagName === 'INPUT' && control.type === 'search' ? 'input' : 'change', aplicarFiltros);
    });
    document.getElementById('limpiarFiltrosPagos')?.addEventListener('click', () => {
        Object.values(filtros).forEach(control => { control.value = ''; });
        aplicarFiltros();
        filtros.bono.focus();
    });
    aplicarFiltros();
})();
</script>
@include('partials.demo_footer')
</body>
</html>
