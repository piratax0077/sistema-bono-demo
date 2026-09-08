<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Rendiciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:#f4f7fb;">
@if(session('ok'))
    <div class="alert alert-success">
        {{ session('ok') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif
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

    <h2 class="mb-4">📑 Rendiciones SDI</h2>

    @if(session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card p-4 mb-4">
        <div class="d-flex justify-content-between">
            <div>
                <h4>Cobros pendientes</h4>
                <p class="text-muted">Total: {{ $cobrosPendientes->count() }}</p>
            </div>

            <form method="POST" action="{{ route('rendiciones.generar') }}">
                @csrf
                <button class="btn btn-primary">
                    Generar Rendición
                </button>
            </form>
        </div>
    </div>

    <div class="card p-4">
        <h4>Rendiciones generadas</h4>

        <table class="table table-hover mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Profesional</th>
                    <th>Sucursal</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Liquidación</th>
                    <th>Profesional</th>
                    <th>Sucursal</th>
                </tr>
            </thead>

            <tbody>
                @foreach($rendiciones as $rendicion)
                    <tr>
                        <td>{{ $rendicion->id }}</td>
                        <td>{{ $rendicion->veterinario_nombre }}</td>
                        <td>{{ $rendicion->sucursal }}</td>
                        <td>{{ $rendicion->cantidad_vouchers }}</td>
                        <td>${{ number_format($rendicion->total_cobrado, 0, ',', '.') }}</td>
                        <td>{{ $rendicion->estado }}</td>
                        <td>{{ $rendicion->rendida_en }}</td>
                        <td>
                            @if($rendicion->estado == 'pendiente')
                                <form method="POST"
                                    action="{{ route('rendiciones.generarLiquidacion', $rendicion->id) }}">
                                    @csrf

                                    <button class="btn btn-sm btn-primary">
                                        Generar Liquidación
                                    </button>
                                </form>
                            @else
                                {{ $rendicion->estado }}
                            @endif
                        </td>
                        <td>{{ $rendicion->veterinario_nombre }}</td>
                        <td>{{ $rendicion->sucursal }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
