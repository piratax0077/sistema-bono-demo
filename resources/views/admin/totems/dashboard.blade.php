<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SDI · Administración de tótems</title>
    @include('partials.demo_wide_layout')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#eef6f4; color:#123532; }
        .shell { max-width:1400px; margin:0 auto; padding:28px; }
        .topbar { display:flex; gap:16px; justify-content:space-between; align-items:center; margin-bottom:22px; }
        .eyebrow { color:#087f6f; font-size:.78rem; letter-spacing:.16em; text-transform:uppercase; font-weight:800; margin-bottom:4px; }
        .card { border:1px solid rgba(8,127,111,.14); border-radius:18px; box-shadow:0 14px 34px rgba(6,52,47,.08); }
        .metric { background:#fff; padding:20px; }
        .metric span { color:#607875; font-size:.9rem; }
        .metric strong { display:block; font-size:2rem; color:#004b43; }
        .status-dot { width:10px; height:10px; display:inline-block; border-radius:999px; margin-right:6px; }
        .dot-ok { background:#15b88a; }
        .dot-alerta { background:#ffb020; }
        .dot-off { background:#dc3545; }
        .secret-box { background:#082f2b; color:#fff; border-radius:16px; padding:18px; }
        .secret-box code { color:#ffe08a; font-size:1.05rem; }
        .form-control, .form-select { border-radius:12px; }
        .btn { border-radius:12px; font-weight:700; }
        .table > :not(caption) > * > * { vertical-align:middle; }
        .alert-text { max-width:360px; white-space:normal; }
    </style>
</head>
<body>
<main class="shell">
    <div class="topbar">
        <div>
            <p class="eyebrow">Operación central</p>
            <h1 class="h3 mb-0">Tótems de atención</h1>
            <p class="text-muted mb-0">Sólo el administrador activa, bloquea o regenera claves de instalación.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ url('escritorio-admin') }}" class="btn btn-outline-secondary">Volver al escritorio</a>
            <a href="{{ route('totem.local') }}" class="btn btn-success">Abrir tótem de esta carpeta</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-danger">Salir</button>
            </form>
        </div>
    </div>

    @if(session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    @if(session('clave_instalacion'))
        <div class="secret-box mb-4">
            <p class="eyebrow text-warning mb-2">Clave de instalación visible una sola vez</p>
            <div class="row g-3 align-items-center">
                <div class="col-md-3"><strong>Tótem:</strong> {{ session('clave_instalacion.codigo') }}</div>
                <div class="col-md-6"><strong>Clave:</strong> <code>{{ session('clave_instalacion.clave') }}</code></div>
                <div class="col-md-3 text-md-end">Guárdela antes de salir de esta pantalla.</div>
            </div>
        </div>
    @endif

    <section class="row g-3 mb-4">
        <div class="col-md-2"><div class="card metric"><span>Total</span><strong>{{ $resumen['total'] }}</strong></div></div>
        <div class="col-md-2"><div class="card metric"><span>Activos</span><strong>{{ $resumen['activos'] }}</strong></div></div>
        <div class="col-md-2"><div class="card metric"><span>Alertas</span><strong>{{ $resumen['alertas'] }}</strong></div></div>
        <div class="col-md-3"><div class="card metric"><span>Ventas hoy</span><strong>{{ $resumen['ventas_hoy'] }}</strong></div></div>
        <div class="col-md-3"><div class="card metric"><span>Monto hoy</span><strong>${{ number_format($resumen['monto_hoy'], 0, ',', '.') }}</strong></div></div>
    </section>

    <section class="card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <p class="eyebrow">Crear o actualizar</p>
                <h2 class="h5">Provisionar tótem desde administrador</h2>
            </div>
            <span class="badge text-bg-info">No existe activación desde emisor</span>
        </div>
        <form method="POST" action="{{ route('admin.totems.store') }}" class="row g-3">
            @csrf
            <div class="col-md-2">
                <label class="form-label">Código</label>
                <input name="codigo" class="form-control" required maxlength="100" placeholder="TOTEM001">
            </div>
            <div class="col-md-3">
                <label class="form-label">Nombre</label>
                <input name="nombre" class="form-control" required maxlength="150" placeholder="Tótem recepción central">
            </div>
            <div class="col-md-3">
                <label class="form-label">Ubicación</label>
                <input name="ubicacion" class="form-control" maxlength="255" placeholder="Sucursal / box / hall">
            </div>
            <div class="col-md-2">
                <label class="form-label">IP autorizada</label>
                <input name="ip_autorizada" class="form-control" placeholder="127.0.0.1">
            </div>
            <div class="col-md-2">
                <label class="form-label">Versión</label>
                <input name="version" class="form-control" maxlength="50" placeholder="1.0.0">
            </div>
            <div class="col-md-3">
                <label class="form-label">Serial equipo</label>
                <input name="serial" class="form-control" maxlength="150">
            </div>
            <div class="col-md-4">
                <label class="form-label">Clave de instalación</label>
                <input name="clave_instalacion" class="form-control" minlength="12" placeholder="Déjela vacía para generar una nueva al crear">
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="activo" class="form-select">
                    <option value="1">Activo</option>
                    <option value="0">Bloqueado</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-success w-100">Guardar tótem</button>
            </div>
            @if($errors->any())
                <div class="col-12">
                    <div class="alert alert-danger mb-0">
                        {{ $errors->first() }}
                    </div>
                </div>
            @endif
        </form>
    </section>

    <section class="card p-0 mb-4">
        <div class="p-4 border-bottom">
            <p class="eyebrow mb-1">Equipos registrados</p>
            <h2 class="h5 mb-0">Estado, geolocalización y seguridad</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th>Último ping</th>
                        <th>GPS</th>
                        <th>Sesiones</th>
                        <th>Ventas</th>
                        <th>Alerta</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($totems as $totem)
                    @php
                        $online = $totem->ultimo_ping && $totem->ultimo_ping->gt(now()->subMinutes(2));
                        $dot = $totem->estado_operacional === 'alerta' ? 'dot-alerta' : ($online ? 'dot-ok' : 'dot-off');
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $totem->codigo }}</strong><br>
                            <small class="text-muted">{{ $totem->nombre }}</small>
                        </td>
                        <td>{{ $totem->ubicacion ?: 'Sin ubicación' }}<br><small class="text-muted">{{ $totem->ip_autorizada ?: 'IP libre' }}</small></td>
                        <td>
                            <span class="status-dot {{ $dot }}"></span>
                            {{ $totem->activo ? 'Activo' : 'Bloqueado' }}
                            <br><small class="text-muted">{{ $totem->estado_operacional ?: 'ok' }}</small>
                        </td>
                        <td>{{ $totem->ultimo_ping ? $totem->ultimo_ping->diffForHumans() : 'Nunca' }}</td>
                        <td>
                            @if($totem->geolocalizacion_lat && $totem->geolocalizacion_lng)
                                {{ $totem->geolocalizacion_lat }}, {{ $totem->geolocalizacion_lng }}
                            @else
                                <span class="text-muted">Sin GPS</span>
                            @endif
                        </td>
                        <td>{{ $totem->sesiones_abiertas_count }}</td>
                        <td>{{ $totem->ventas_count }}</td>
                        <td class="alert-text">
                            @if($totem->estado_operacional === 'alerta')
                                <span class="badge text-bg-warning">Revisar</span>
                                <div>{{ $totem->ultima_alerta_mensaje }}</div>
                                <small class="text-muted">{{ optional($totem->ultima_alerta_at)->format('d-m-Y H:i') }}</small>
                            @else
                                <span class="badge text-bg-success">OK</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <form method="POST" action="{{ route('admin.totems.toggle', $totem) }}">
                                    @csrf
                                    <button class="btn btn-sm {{ $totem->activo ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                        {{ $totem->activo ? 'Bloquear' : 'Activar' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.totems.regenerateSecret', $totem) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-dark">Regenerar clave</button>
                                </form>
                                @if($totem->estado_operacional === 'alerta')
                                    <form method="POST" action="{{ route('admin.totems.clearAlert', $totem) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-warning">Resolver alerta</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted p-4">Aún no existen tótems registrados.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">
            {{ $totems->links() }}
        </div>
    </section>

    <section class="row g-4">
        <div class="col-lg-6">
            <div class="card p-4 h-100">
                <p class="eyebrow">Últimas ventas</p>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>ID</th><th>Tótem</th><th>Cliente</th><th>Total</th><th>Estado</th></tr></thead>
                        <tbody>
                            @forelse($ultimasVentas as $venta)
                                <tr>
                                    <td>{{ $venta->id }}</td>
                                    <td>{{ optional($venta->totem)->codigo }}</td>
                                    <td>{{ $venta->cliente_nombre ?: 'Invitado' }}</td>
                                    <td>${{ number_format($venta->total, 0, ',', '.') }}</td>
                                    <td>{{ $venta->estado }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted">Sin ventas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card p-4 h-100">
                <p class="eyebrow">Bitácora operacional</p>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Fecha</th><th>Tótem</th><th>Evento</th><th>Detalle</th></tr></thead>
                        <tbody>
                            @forelse($ultimosLogs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d-m H:i') }}</td>
                                    <td>{{ optional($log->totem)->codigo }}</td>
                                    <td>{{ $log->evento }}</td>
                                    <td>{{ $log->detalle }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted">Sin eventos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>
</body>
</html>
