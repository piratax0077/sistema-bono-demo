<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>Atenciones</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body style="background:#f4f7fb;">

<div class="container py-5">

    <h2 class="mb-4">
        🩺 Atenciones Profesionales
    </h2>
    <div class="d-flex justify-content-between mb-4">

<a href="{{ url('/redirigir-rol') }}"
   class="btn btn-secondary">
    Volver
</a>

    <form method="POST"
          action="{{ route('logout') }}">
        @csrf

        <button class="btn btn-danger">
            Cerrar sesión
        </button>
    </form>

</div>
    <div class="card p-4 shadow-sm border-0">

        <table class="table">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Servicio</th>
                    <th>Estado</th>
                    <th>atencion</th>
                </tr>

            </thead>

            <tbody>

            @foreach($vouchers as $voucher)

                <tr>

                    <td>{{ $voucher->id }}</td>

                    <td>{{ $voucher->cliente_nombre }}</td>

                    <td>{{ $voucher->tipo_servicio }}</td>

                    <td>{{ $voucher->estado }}</td>

                    <td>

                        <form method="POST"
                              action="/profesional/vouchers/{{ $voucher->id }}/finalizar">

                            @csrf

                            <button class="btn btn-success">
                                Cerrar Atención
                            </button>

                        </form>

                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

    </div>

</div>

</body>
</html>
