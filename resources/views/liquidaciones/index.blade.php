<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Liquidaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:#f4f7fb;">

    <div class="d-flex justify-content-end mb-4">

        <form method="POST"
            action="{{ route('logout') }}">
            @csrf

            <button class="btn btn-danger">
                Cerrar sesión
            </button>
        </form>

    </div>

    <a href="/escritorio-admin"
       class="btn btn-secondary mb-4">
        Volver
    </a>

    <h2 class="fw-bold mb-4">
        💸 Liquidaciones SDI
    </h2>

    <div class="card p-4 border-0 shadow-sm">

        <table class="table table-hover align-middle">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Profesional</th>
                    <th>Banco</th>
                    <th>Cuenta</th>
                    <th>Monto Profesional</th>
                    <th>Comisión SDI</th>
                    <th>Estado</th>
                    <th>Fecha Pago</th>
                    <th>Acción</th>
                </tr>
            </thead>

            <tbody>

                @forelse($liquidaciones as $liq)

                    <tr>
                        <td>{{ $liq->id }}</td>
                        <td>{{ $liq->profesional_nombre }}</td>
                        <td>{{ $liq->banco }}</td>
                        <td>{{ $liq->numero_cuenta }}</td>
                        <td>${{ number_format($liq->monto_profesional, 0, ',', '.') }}</td>
                        <td>${{ number_format($liq->comision_veterchile, 0, ',', '.') }}</td>
                        <td>{{ $liq->estado }}</td>
                        <td>{{ $liq->pagado_en ?? '-' }}</td>
                        <td>

                            @if($liq->estado != 'pagado')

                                <form method="POST"
                                    action="{{ route('liquidaciones.pagar', $liq->id) }}">

                                    @csrf

                                    <input type="hidden"
                                        name="medio_pago"
                                        value="transferencia">

                                    <input type="hidden"
                                        name="comprobante_transferencia"
                                        value="TRANSFERENCIA-MANUAL">

                                    <button class="btn btn-sm btn-success">
                                        Marcar Pagada
                                    </button>

                                </form>

                            @else

                                ✅ Pagada

                            @endif

                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="8">
                            No hay liquidaciones generadas.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

</body>
</html>
