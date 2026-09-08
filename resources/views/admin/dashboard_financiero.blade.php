<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>Dashboard Financiero</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body style="background:#f4f7fb;">

    <div class="container py-5">
        <div class="d-flex justify-content-end mb-4">

        <form method="POST"
            action="{{ route('logout') }}">
            @csrf

            <button class="btn btn-danger">
                Cerrar sesión
            </button>
        </form>

    </div>

    <a href="{{ url('escritorio-admin') }}"
       class="btn btn-secondary mb-4">
        Volver
    </a>

    <h2 class="fw-bold mb-4">
        📊 Dashboard Financiero SDI
    </h2>

    <div class="row g-4 mb-4">

        <div class="col-md-3">
            <div class="card p-4 border-0 shadow-sm">

                <small class="text-muted">
                    Total Emitido
                </small>

                <h3>
                    ${{ number_format($totalEmitido, 0, ',', '.') }}
                </h3>

            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-4 border-0 shadow-sm">

                <small class="text-muted">
                    Copagos Recaudados
                </small>

                <h3>
                    ${{ number_format($totalCopagos, 0, ',', '.') }}
                </h3>

            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-4 border-0 shadow-sm">

                <small class="text-muted">
                    Comisión SDI
                </small>

                <h3>
                    ${{ number_format($totalComision, 0, ',', '.') }}
                </h3>

            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-4 border-0 shadow-sm">

                <small class="text-muted">
                    Pendiente Rendición
                </small>

                <h3>
                    ${{ number_format($pendienteRendicion, 0, ',', '.') }}
                </h3>

            </div>
        </div>

    </div>

    <div class="card p-4 border-0 shadow-sm mb-4">

        <h5 class="fw-bold">
            💳 Total Rendido Profesionales
        </h5>

        <h2>
            ${{ number_format($totalRendido, 0, ',', '.') }}
        </h2>

    </div>

    <div class="card p-4 border-0 shadow-sm">

        <h5 class="fw-bold mb-3">
            Últimos bonos
        </h5>

        <table class="table table-hover align-middle">

            <thead>
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Servicio</th>
                    <th>Valor</th>
                    <th>Estado</th>
                </tr>
            </thead>

            <tbody>

                @forelse($vouchers as $voucher)

                    <tr>
                        <td>{{ $voucher->codigo }}</td>
                        <td>{{ $voucher->cliente_nombre }}</td>
                        <td>{{ $voucher->tipo_servicio }}</td>
                        <td>
                            ${{ number_format($voucher->valor, 0, ',', '.') }}
                        </td>
                        <td>{{ $voucher->estado }}</td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="5">
                            No existen bonos.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>
    <div class="col-md-3">
        <div class="card p-3 shadow-sm border-0">
            <small>Saldos disponibles</small>
            <h4>
                ${{ number_format($totalSaldosDisponibles, 0, ',', '.') }}
            </h4>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3 shadow-sm border-0">
            <small>Saldos consumidos</small>
            <h4>
                ${{ number_format($totalSaldosConsumidos, 0, ',', '.') }}
            </h4>
        </div>
    </div>
    <div class="col-md-4">
    <div class="card p-4 shadow-sm border-0">
        <h5>🔐 IPs Autorizadas</h5>

        <p>
            Control de acceso por IP para Admin y Auditor.
        </p>

        <a href="{{ route('admin.ips.autorizadas') }}"
           class="btn btn-dark">
            Gestionar IPs
        </a>
    </div>
</div>
</div>

</body>
</html>
