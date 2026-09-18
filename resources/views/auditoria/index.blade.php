<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Auditoria SDI</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body { background:#edf4ff; color:#1f2d3d; }
        .card-soft { border:1px solid #d6e1f1; border-radius:22px; box-shadow:0 18px 45px rgba(24,72,161,.08); }
        .badge-soft { background:#e7f9f9; color:#237f87; }
        .badge-warn { background:#fff3cd; color:#875900; }
        .badge-danger-soft { background:#ffe2e2; color:#9f1f1f; }
        .small-muted { color:#7b8798; font-size:.86rem; }
        .audit-box { background:#f8fbff; border:1px solid #d8e3f1; border-radius:14px; padding:.75rem; }
        .audit-scroll { max-height:180px; overflow:auto; }
        .table td { vertical-align:top; }
        details summary { cursor:pointer; color:#1848a1; font-weight:700; }
    </style>
</head>

<body>
@include('partials.demo_user_switcher')

<div class="container py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="text-uppercase small fw-bold text-success">
                Operacion central
            </div>
            <h2 class="fw-bold mb-0">
                Auditoria SDI
            </h2>
        </div>

    </div>

    @if(session('ok'))
        <div class="alert alert-success">
            {{ session('ok') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card card-soft p-3 mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
            <div>
                <div class="text-uppercase small fw-bold text-success">Control posterior a la atención</div>
                <h4 class="fw-bold mb-1">Visto bueno de cobros enviados por QR</h4>
                <p class="small-muted mb-0">El sistema ejecuta seis controles. Solo un expediente completamente conforme puede pasar a rendición.</p>
            </div>
            <span class="badge badge-warn px-3 py-2">{{ $cobrosAuditoria->where('estado', 'pendiente_auditoria')->count() }} pendientes</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Expediente</th><th>Relación y atención</th><th>Valor</th><th>Controles del sistema</th><th>Decisión</th></tr></thead>
                <tbody>
                @forelse($cobrosAuditoria as $cobro)
                    @php
                        $voucher = $cobro->voucher;
                        $atencion = optional($voucher)->atencion;
                        $agenda = optional($voucher)->agenda;
                        $resuelto = in_array($cobro->estado, ['pendiente_rendicion', 'rechazado_auditoria', 'objetado_pago', 'pagado'], true);
                        $liquidacionPago = $cobro->rendicion
                            ? $cobro->rendicion->liquidaciones->first()
                            : null;
                        $decisionAuditoria = $cobro->estado === 'rechazado_auditoria'
                            ? ['Rechazado', 'bg-danger']
                            : ($cobro->estado === 'observado_auditoria'
                                ? ['Observado', 'bg-warning text-dark']
                                : ['Visto bueno otorgado', 'bg-success']);
                    @endphp
                    <tr>
                        <td style="min-width:190px">
                            <div class="fw-bold">{{ optional($voucher)->codigo ?: '-' }}</div>
                            <div class="small-muted">Cobro #{{ $cobro->id }} · {{ $cobro->cobrado_en }}</div>
                            <span class="badge {{ $cobro->controles_ok ? 'badge-soft' : 'badge-danger-soft' }} mt-2">{{ $cobro->controles_ok ? 'Controles conformes' : 'Con observaciones' }}</span>
                        </td>
                        <td style="min-width:280px">
                            <strong>{{ optional($voucher)->beneficiario_nombre ?: optional($voucher)->cliente_nombre }}</strong><br>
                            atendido por {{ optional($voucher)->prestador_nombre ?: 'Profesional asociado' }}<br>
                            <span class="small-muted">
                                {{ optional($voucher)->tipo_servicio ?: '-' }}<br>
                                Lugar: {{ optional($atencion)->direccion ?: optional($voucher)->prestador_direccion ?: 'Centro médico de prueba' }}<br>
                                Fecha: {{ optional($atencion)->cerrada_at ?: '-' }} · Hora Medichile #{{ optional($agenda)->medichile_hora_medica_id ?: '-' }}
                            </span>
                        </td>
                        <td style="min-width:130px"><strong class="fs-5">${{ number_format($cobro->monto_cobrado, 0, ',', '.') }}</strong></td>
                        <td style="min-width:300px">
                            @foreach($cobro->controles_calculados as $control)
                                <div class="d-flex align-items-start gap-2 mb-2">
                                    <span class="badge {{ $control['ok'] ? 'bg-success' : 'bg-danger' }}">{{ $control['ok'] ? 'OK' : 'FALLA' }}</span>
                                    <div><strong>{{ $control['nombre'] }}</strong><div class="small-muted">{{ $control['detalle'] }}</div></div>
                                </div>
                            @endforeach
                        </td>
                        <td style="min-width:290px">
                            @if(!$resuelto)
                                <form method="POST" action="{{ route('auditoria.cobros.resolver', $cobro->id) }}">
                                    @csrf
                                    <textarea name="observacion" class="form-control form-control-sm mb-2" rows="3" placeholder="Fundamento obligatorio al observar o rechazar"></textarea>
                                    <div class="d-grid gap-2">
                                        <button name="decision" value="aprobar" class="btn btn-success btn-sm" @disabled(!$cobro->controles_ok)>Dar visto bueno</button>
                                        <div class="d-flex gap-2">
                                            <button name="decision" value="observar" class="btn btn-warning btn-sm flex-fill">Observar</button>
                                            <button name="decision" value="rechazar" class="btn btn-outline-danger btn-sm flex-fill">Rechazar</button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <span class="badge {{ $decisionAuditoria[1] }} mb-2">
                                    {{ $decisionAuditoria[0] }}
                                </span>
                                <div class="small-muted">Contralor: {{ optional($cobro->auditor)->name ?: '-' }}<br>Fecha: {{ $cobro->auditado_at ?: '-' }}</div>
                                <div class="audit-box mt-2">{{ $cobro->observacion_auditor ?: '-' }}</div>
                                @if($cobro->hash_visto_bueno)<div class="small-muted mt-2 text-break">Huella: {{ $cobro->hash_visto_bueno }}</div>@endif

                                @if($cobro->estado === 'pendiente_rendicion' && $cobro->pago_estado === 'pendiente_autorizacion')
                                    <hr>
                                    <div class="fw-bold mb-2">Decisión de pago</div>
                                    <form method="POST" action="{{ route('auditoria.cobros.resolverPago', $cobro->id) }}">
                                        @csrf
                                        <textarea name="motivo_objecion" class="form-control form-control-sm mb-2" rows="2" placeholder="Motivo obligatorio si objeta"></textarea>
                                        <div class="d-grid gap-2">
                                            <button name="decision_pago" value="autorizar" class="btn btn-success btn-sm">Autorizar y depositar</button>
                                            <button name="decision_pago" value="objetar" class="btn btn-outline-danger btn-sm">Objetar pago</button>
                                        </div>
                                    </form>
                                @elseif($cobro->pago_estado === 'depositado')
                                    <hr>
                                    <span class="badge bg-success">Pago depositado</span>
                                    <div class="audit-box mt-2">
                                        <strong>{{ optional($liquidacionPago)->banco }}</strong><br>
                                        {{ optional($liquidacionPago)->tipo_cuenta }} · cuenta terminada en {{ substr((string) optional($liquidacionPago)->numero_cuenta, -4) }}<br>
                                        Monto: ${{ number_format(optional($liquidacionPago)->monto_profesional, 0, ',', '.') }}<br>
                                        Comprobante: {{ $cobro->deposito_comprobante }}
                                    </div>
                                @elseif($cobro->pago_estado === 'objetado')
                                    <hr>
                                    <span class="badge bg-danger">Pago objetado</span>
                                    <div class="audit-box mt-2">{{ $cobro->pago_objecion }}</div>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">No hay QR de cobro pendientes de auditoría.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Revisa la resolucion:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card card-soft p-4 h-100">
                <h5 class="fw-bold mb-2">
                    Expedientes de preconsulta
                </h5>
                <p class="small-muted mb-0">
                    Cada envio a auditoria incluye motivo, contradicciones, intentos fallidos y los porques para que el auditor resuelva sin buscar piezas sueltas.
                </p>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-soft p-4 h-100">
                <div class="small-muted">Pendientes</div>
                <div class="display-6 fw-bold">
                    {{ $preconsultaAuditorias->where('estado', 'pendiente')->count() }}
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-soft p-4 h-100">
                <div class="small-muted">En esta pagina</div>
                <div class="display-6 fw-bold">
                    {{ $preconsultaAuditorias->count() }}
                </div>
            </div>
        </div>
    </div>

    <div class="card card-soft p-3 mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
                <h5 class="fw-bold mb-1">
                    Alertas pendientes para auditoría
                </h5>
                <p class="small-muted mb-0">
                    Incluye fallas de tótem, contradicciones operacionales y eventos que requieren resolución humana.
                </p>
            </div>
            <div class="text-end">
                <div class="display-6 fw-bold">{{ $notificacionesPendientes }}</div>
                <div class="small-muted">notificaciones pendientes</div>
            </div>
        </div>

        @if($ultimasNotificaciones->count())
            <div class="table-responsive mt-3">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Título</th>
                            <th>Resumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ultimasNotificaciones as $notificacion)
                            <tr>
                                <td>{{ $notificacion->created_at }}</td>
                                <td>
                                    <span class="badge {{ $notificacion->leido ? 'badge-soft' : 'badge-danger-soft' }}">
                                        {{ $notificacion->leido ? 'Leída' : 'Pendiente' }}
                                    </span>
                                </td>
                                <td>{{ $notificacion->titulo }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($notificacion->mensaje, 140) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="card card-soft p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold mb-1">
                    Auditoria de preconsultas antes del bono
                </h4>
                <div class="small-muted">
                    No se muestran RUTs en claro; se usan IDs internos, fingerprints y contexto seguro.
                </div>
            </div>

        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Expediente</th>
                        <th>Motivo</th>
                        <th>Contradicciones</th>
                        <th>Intentos fallidos</th>
                        <th>Por que</th>
                        <th>Contexto</th>
                        <th>Resolucion</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($preconsultaAuditorias as $expediente)
                        @php
                            $preconsulta = $expediente->preconsulta;
                            $contradicciones = $expediente->contradicciones ?: [];
                            $intentos = $expediente->intentos_fallidos ?: [];
                            $porques = $expediente->porques ?: [];
                            $contexto = $expediente->contexto ?: [];
                            $estadoClase = $expediente->estado === 'pendiente' ? 'badge-warn' : 'badge-soft';
                        @endphp

                        <tr>
                            <td style="min-width:180px;">
                                <div class="fw-bold">
                                    #{{ $expediente->id }}
                                </div>
                                <div class="small-muted">
                                    Preconsulta: {{ $preconsulta ? $preconsulta->id : '-' }}
                                </div>
                                <span class="badge {{ $estadoClase }}">
                                    {{ ucfirst($expediente->estado) }}
                                </span>
                                <div class="small-muted mt-2">
                                    {{ $expediente->created_at }}
                                </div>

                                @if($preconsulta)
                                    <div class="small-muted mt-2">
                                        Resultado: <strong>{{ $preconsulta->resultado }}</strong><br>
                                        Hash: {{ $preconsulta->hash_validacion_modo }}<br>
                                        Usuario ID: {{ $preconsulta->usuario_id ?? '-' }}<br>
                                        Prestador: {{ $preconsulta->prestador_tipo ?? '-' }}
                                    </div>
                                @endif
                            </td>

                            <td style="min-width:240px;">
                                <div class="fw-bold">
                                    {{ $expediente->motivo ?: '-' }}
                                </div>

                                @if($preconsulta)
                                    <div class="small-muted mt-2">
                                        Servicio: {{ optional($preconsulta->servicio)->nombre ?? '-' }}<br>
                                        Profesional: {{ optional($preconsulta->profesional)->nombre ?? '-' }}<br>
                                        Laboratorio: {{ optional($preconsulta->laboratorio)->nombre ?? '-' }}
                                    </div>
                                @endif
                            </td>

                            <td style="min-width:290px;">
                                @forelse($contradicciones as $contradiccion)
                                    <div class="audit-box mb-2">
                                        <span class="badge badge-danger-soft">
                                            {{ data_get($contradiccion, 'codigo', 'sin_codigo') }}
                                        </span>
                                        <div class="mt-2">
                                            {{ data_get($contradiccion, 'detalle', '-') }}
                                        </div>
                                        @if(data_get($contradiccion, 'impacto'))
                                            <div class="small-muted mt-1">
                                                Impacto: {{ data_get($contradiccion, 'impacto') }}
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <span class="small-muted">Sin contradicciones registradas.</span>
                                @endforelse
                            </td>

                            <td style="min-width:260px;">
                                <div class="fw-bold mb-2">
                                    {{ $expediente->intentos_fallidos_count }} intento(s)
                                </div>

                                <details>
                                    <summary>Ver ultimos intentos</summary>
                                    <div class="audit-scroll mt-2">
                                        @forelse($intentos as $intento)
                                            <div class="audit-box mb-2">
                                                <div class="fw-bold">
                                                    #{{ data_get($intento, 'id', '-') }} -
                                                    {{ data_get($intento, 'resultado', '-') }}
                                                </div>
                                                <div class="small-muted">
                                                    {{ data_get($intento, 'fecha', '-') }}
                                                </div>
                                                <div>
                                                    {{ data_get($intento, 'motivo', '-') }}
                                                </div>
                                                <div class="small-muted">
                                                    Fingerprint: {{ data_get($intento, 'fingerprint', '-') }}
                                                </div>
                                            </div>
                                        @empty
                                            <div class="small-muted">
                                                No hay intentos previos guardados.
                                            </div>
                                        @endforelse
                                    </div>
                                </details>
                            </td>

                            <td style="min-width:280px;">
                                @forelse($porques as $porque)
                                    <div class="audit-box mb-2">
                                        <span class="badge badge-soft">
                                            {{ data_get($porque, 'codigo', 'porque') }}
                                        </span>
                                        <div class="mt-2">
                                            {{ data_get($porque, 'explicacion', '-') }}
                                        </div>
                                    </div>
                                @empty
                                    <span class="small-muted">Sin explicacion registrada.</span>
                                @endforelse
                            </td>

                            <td style="min-width:260px;">
                                <details>
                                    <summary>Ver contexto seguro</summary>
                                    <div class="audit-scroll mt-2">
                                        @forelse($contexto as $clave => $valor)
                                            <div class="d-flex justify-content-between border-bottom py-1 gap-3">
                                                <span class="small-muted">{{ $clave }}</span>
                                                <span class="text-end">
                                                    @if(is_bool($valor))
                                                        {{ $valor ? 'si' : 'no' }}
                                                    @elseif(is_array($valor))
                                                        {{ json_encode($valor) }}
                                                    @else
                                                        {{ $valor ?? '-' }}
                                                    @endif
                                                </span>
                                            </div>
                                        @empty
                                            <div class="small-muted">
                                                Sin contexto adicional.
                                            </div>
                                        @endforelse
                                    </div>
                                </details>
                            </td>

                            <td style="min-width:280px;">
                                @if($expediente->estado !== 'resuelto')
                                    <form method="POST"
                                          action="{{ route('auditoria.preconsultas.resolver', $expediente->id) }}">
                                        @csrf
                                        <textarea name="resolucion"
                                                  class="form-control mb-2"
                                                  rows="4"
                                                  required
                                                  placeholder="Indique decision, fundamento y accion a ejecutar."></textarea>
                                        <button class="btn btn-success btn-sm w-100">
                                            Resolver expediente
                                        </button>
                                    </form>
                                @else
                                    <span class="badge badge-soft mb-2">
                                        Resuelto
                                    </span>
                                    <div class="small-muted">
                                        Auditor: {{ optional($expediente->auditor)->name ?? '-' }}<br>
                                        Fecha: {{ $expediente->resuelto_at ?? '-' }}
                                    </div>
                                    <div class="audit-box mt-2">
                                        {{ $expediente->resolucion }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                No hay expedientes de preconsulta para auditoria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $preconsultaAuditorias->links() }}
        </div>
    </div>

    <div class="card card-soft p-3">
        <h4 class="fw-bold mb-3">
            Registro general de auditoria
        </h4>
        <p class="text-muted mb-3">Ordenado por tipo de usuario, usuario y fecha más reciente dentro de cada grupo.</p>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Voucher</th>
                        <th>Accion</th>
                        <th>Tipo</th>
                        <th>Usuario</th>
                        <th>Descripcion</th>
                        <th>IP</th>
                        <th>Fecha</th>
                    </tr>
                </thead>

                <tbody>
                    @php($grupoAuditoriaAnterior = null)
                    @forelse($auditorias as $a)
                        @php($grupoAuditoria = ($a->usuario_tipo ?: 'sistema').'|'.($a->usuario_id ?: 0))
                        @if($grupoAuditoria !== $grupoAuditoriaAnterior)
                            <tr class="table-light">
                                <td colspan="8" class="py-2">
                                    <span class="badge bg-dark me-2">{{ strtoupper($a->usuario_tipo ?: 'sistema') }}</span>
                                    <strong>{{ optional($a->usuario)->name ?: ($a->usuario_id ? 'Usuario #'.$a->usuario_id : 'Proceso automático') }}</strong>
                                    @if($a->usuario_id)<span class="text-muted ms-1">· ID {{ $a->usuario_id }}</span>@endif
                                </td>
                            </tr>
                            @php($grupoAuditoriaAnterior = $grupoAuditoria)
                        @endif
                        <tr>
                            <td>{{ $a->id }}</td>
                            <td>{{ $a->voucher->codigo ?? '-' }}</td>
                            <td>{{ $a->accion }}</td>
                            <td>{{ ucfirst($a->usuario_tipo ?: 'sistema') }}</td>
                            <td>{{ optional($a->usuario)->name ?: ($a->usuario_id ? '#'.$a->usuario_id : 'Automático') }}</td>
                            <td>{{ $a->descripcion }}</td>
                            <td>{{ $a->ip }}</td>
                            <td class="text-nowrap"><strong>{{ optional($a->created_at)->format('d-m-Y') }}</strong><br><small>{{ optional($a->created_at)->format('H:i:s') }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                No hay registros de auditoria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
