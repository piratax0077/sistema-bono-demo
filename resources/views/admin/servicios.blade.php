<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prestaciones Médicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:#f4f7fb;">

<div class="container py-5">

    <a href="/escritorio-admin" class="btn btn-secondary mb-4">
        Volver
    </a>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">🩺 Prestaciones Médicas</h2>

        <a href="{{ route('admin.servicios.crear') }}"
           class="btn btn-primary">
            Crear Prestación
        </a>
    </div>

    <div class="card p-4 border-0 shadow-sm">

        <table class="table table-hover align-middle">

            <thead>
                <tr>
                    <th>Servicio</th>
                    <th>Valor Base</th>
                    <th>Copago</th>
                    <th>Comisión SDI</th>
                    <th>Activo</th>
                </tr>
            </thead>

            <tbody>

                @forelse($servicios as $servicio)

                    <tr>
                        <td>
                            <strong>{{ $servicio->nombre }}</strong><br>
                            <small class="text-muted">
                                {{ $servicio->descripcion }}
                            </small>
                        </td>

                        <td>${{ number_format($servicio->valor_base, 0, ',', '.') }}</td>

                        <td>${{ number_format($servicio->copago_base, 0, ',', '.') }}</td>

                        <td>${{ number_format($servicio->comision_veterchile, 0, ',', '.') }}</td>

                        <td>
                            @if($servicio->activo)
                                ✅
                            @else
                                ❌
                            @endif
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="5">
                            No hay prestaciones registradas.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

</body>
</html>
