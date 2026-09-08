<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de bonos · Medichile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{background:#eef5f3;color:#173b37}.page-title{color:#075e54}.card{border-radius:18px}.status{display:inline-block;padding:.35rem .65rem;border-radius:999px;background:#e6eef1;color:#34515b;font-size:.78rem;font-weight:800}.status-activo,.status-pagado{background:#d9f5e8;color:#08714f}.status-usado,.status-cobrado{background:#e6e9ec;color:#49545c}.table td,.table th{vertical-align:middle}.code{font-weight:800;color:#075e54}.muted{color:#6b7d79;font-size:.85rem}
    </style>
</head>
<body>
@include('partials.demo_user_switcher')
<main class="container-fluid px-3 px-lg-5 py-4">
    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
        <div><div class="text-uppercase fw-bold text-success small">Medichile · Administración</div><h1 class="page-title h2 mb-1">Gestión de bonos</h1><p class="text-muted mb-0">Registro interno de bonos activos, utilizados, cobrados y auditables.</p></div>
        <a href="{{ url('escritorio-admin') }}" class="btn btn-outline-secondary">Volver a administración</a>
    </div>

    <section class="card border-0 shadow-sm p-3 p-lg-4 mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-lg-6"><label class="form-label fw-bold">Buscar</label><input class="form-control" name="buscar" value="{{ $buscar }}" placeholder="Código, paciente o profesional"></div>
            <div class="col-lg-3"><label class="form-label fw-bold">Estado</label><select class="form-select" name="estado"><option value="">Todos los estados</option>@foreach($estados as $opcion)<option value="{{ $opcion }}" @selected($estado === $opcion)>{{ ucfirst(str_replace('_',' ',$opcion)) }}</option>@endforeach</select></div>
            <div class="col-lg-3 d-flex gap-2"><button class="btn btn-success flex-fill">Filtrar</button><a href="{{ route('admin.bonos.gestion') }}" class="btn btn-outline-secondary">Limpiar</a></div>
        </form>
    </section>

    <section class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr><th>Código</th><th>Paciente</th><th>Profesional</th><th>Prestación</th><th>Valor</th><th>Estado</th><th>Proceso</th><th></th></tr></thead>
                <tbody>
                @forelse($vouchers as $voucher)
                    <tr>
                        <td><div class="code">{{ $voucher->codigo }}</div><div class="muted">ID {{ $voucher->id }} · {{ optional($voucher->created_at)->format('d-m-Y H:i') }}</div></td>
                        <td>{{ $voucher->beneficiario_nombre ?: $voucher->cliente_nombre ?: 'Sin nombre' }}</td>
                        <td>{{ $voucher->prestador_nombre ?: optional($voucher->profesional)->nombre ?: 'Pendiente' }}</td>
                        <td>{{ $voucher->tipo_servicio ?: optional($voucher->servicio)->nombre ?: 'Bono de atención' }}</td>
                        <td>${{ number_format($voucher->valor_total ?: $voucher->valor, 0, ',', '.') }}</td>
                        <td><span class="status status-{{ $voucher->estado }}">{{ ucfirst(str_replace('_',' ',$voucher->estado)) }}</span></td>
                        <td><div class="muted">Agenda: {{ optional($voucher->agenda)->estado ?: 'sin agenda' }}</div><div class="muted">Atención: {{ $voucher->atencion_cerrada_at ? 'cerrada' : ($voucher->atencion_id ? 'iniciada' : 'pendiente') }}</div><div class="muted">Cobro: {{ optional($voucher->cobros->first())->estado ?: 'pendiente' }}</div></td>
                        <td><a href="{{ route('vouchers.show', $voucher->id) }}" class="btn btn-sm btn-outline-success">Revisar</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">No hay bonos que coincidan con el filtro.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($vouchers->hasPages())<div class="p-3 border-top">{{ $vouchers->links() }}</div>@endif
    </section>
</main>
</body>
</html>
