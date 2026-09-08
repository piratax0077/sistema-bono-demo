<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Vouchers Mascota</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body style="background:#f4f7fb;">

<div class="container py-5">

    <a href="{{ route('cliente.mascotas') }}"
       class="btn btn-secondary mb-4">
        Volver
    </a>

    <h2 class="fw-bold mb-4">
        🎟️ Vouchers de {{ $mascota->nombre }}
    </h2>

    <div class="card p-4 border-0 shadow-sm">

        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Servicio</th>
                    <th>Valor</th>
                    <th>Copago</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                </tr>
            </thead>

            <tbody>
                @forelse($vouchers as $voucher)
                    <tr>
                        <td>{{ $voucher->codigo }}</td>
                        <td>{{ $voucher->tipo_servicio }}</td>
                        <td>${{ number_format($voucher->valor, 0, ',', '.') }}</td>
                        <td>${{ number_format($voucher->copago_usuario, 0, ',', '.') }}</td>
                        <td>{{ $voucher->estado }}</td>
                        <td>{{ $voucher->created_at }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            Esta mascota no tiene vouchers registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

</div>

</body>
</html>
