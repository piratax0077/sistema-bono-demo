<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Notificaciones de auditoría · Medichile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{background:#edf4ff;color:#1f2d3d}.notifications-shell{max-width:1500px}.page-kicker{color:#087b68;font-size:.76rem;font-weight:850;letter-spacing:.13em;text-transform:uppercase}.card-soft{overflow:hidden;border:1px solid #d6e1f1!important;border-radius:22px!important;box-shadow:0 18px 45px rgba(24,72,161,.08)}.notification-message{min-width:260px;max-width:440px;white-space:normal}.notification-title{min-width:170px}.empty-notifications{padding:4rem 1rem!important}.status-unread{background:#ffe2e2;color:#9f1f1f}.status-read{background:#e9eef5;color:#607184}.status-pill{display:inline-block;padding:.38rem .65rem;border-radius:999px;font-size:.75rem;font-weight:850}.table td{vertical-align:middle}.header-actions{display:flex;gap:.6rem;flex-wrap:wrap}
    </style>
</head>
<body>
@include('partials.demo_user_switcher')
<main class="container notifications-shell py-4 px-4">
    <header class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><div class="page-kicker">Operación central</div><h1 class="h2 fw-bold mb-1">Notificaciones de auditoría</h1><p class="text-muted mb-0">Alertas y eventos que requieren revisión del equipo de Contraloría.</p></div>
    </header>

    @if(session('ok'))<div class="alert alert-success">{{ session('ok') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <section class="card card-soft border-0">
        <div class="card-header bg-white p-4 d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div><div class="page-kicker">Bandeja de control</div><h2 class="h4 fw-bold mb-1">Alertas recibidas</h2><p class="text-muted mb-0">Cada notificación conserva su relación con el voucher y la alerta que la originó.</p></div>
            <span class="badge bg-danger px-3 py-2">{{ $notificaciones->where('leido', false)->count() }} pendientes</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Estado</th><th>Título</th><th>Mensaje</th><th>Voucher</th><th>Alerta</th><th>Fecha</th><th class="text-end">Acción</th></tr></thead>
                <tbody>
                @forelse($notificaciones as $notificacion)
                    <tr>
                        <td><span class="status-pill {{ $notificacion->leido ? 'status-read' : 'status-unread' }}">{{ $notificacion->leido ? 'Leída' : 'Pendiente' }}</span></td>
                        <td class="notification-title fw-semibold">{{ $notificacion->titulo }}</td>
                        <td class="notification-message text-muted">{!! nl2br(e($notificacion->mensaje)) !!}</td>
                        <td>@if($notificacion->voucher)<a href="{{ route('vouchers.show', $notificacion->voucher->id) }}" class="btn btn-sm btn-outline-primary">Ver voucher</a>@else<span class="text-muted">—</span>@endif</td>
                        <td>{{ $notificacion->alerta?->tipo_alerta ?: '—' }}</td>
                        <td class="text-nowrap">{{ optional($notificacion->created_at)->format('d-m-Y H:i') }}</td>
                        <td class="text-end">@if(!$notificacion->leido)<form method="POST" action="{{ route('auditoria.notificaciones.leer', $notificacion->id) }}">@csrf<button class="btn btn-sm btn-success text-nowrap">Marcar como leída</button></form>@else<span class="text-muted">Revisada</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-notifications text-center text-muted"><div class="fs-1 mb-2">✓</div><strong>No hay notificaciones pendientes</strong><div>Las nuevas alertas aparecerán aquí para su revisión.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</main>
@include('partials.demo_footer')
</body>
</html>
