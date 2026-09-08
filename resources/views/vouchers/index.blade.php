<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Vouchers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

    <div class="d-flex justify-content-between mb-4">
        <h2>🎟️ Vouchers SDI</h2>

        <a href="{{ route('vouchers.create') }}"
           class="btn btn-primary">
            Emitir Voucher
        </a>
    </div>

    <div class="card p-4">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Servicio</th>
                    <th>Valor</th>
                    <th>Copago</th>
                    <th>Estado</th>
                    <th>Ver</th>
                    <th>Vendedor</th>
                </tr>
            </thead>

            <tbody>
                @foreach($vouchers as $voucher)
                    <tr>
                        <td>{{ $voucher->codigo }}</td>
                        <td>{{ $voucher->cliente_nombre }}</td>
                        <td>{{ $voucher->tipo_servicio }}</td>
                        <td>${{ number_format($voucher->valor, 0, ',', '.') }}</td>
                        <td>${{ number_format($voucher->copago_usuario, 0, ',', '.') }}</td>
                        <td>{{ $voucher->estado }}</td>
                        <td>
                            <a href="{{ route('vouchers.show', $voucher->id) }}"
                               class="btn btn-sm btn-dark">
                                Ver
                            </a>
                        </td>
                        <td>
                            {{ optional($voucher->vendedor)->nombre }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
