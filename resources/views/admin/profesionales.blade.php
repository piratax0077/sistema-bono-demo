<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Profesionales</title>

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
        <a href="{{ route('admin.profesionales.crear') }}"
            class="btn btn-primary mb-4">
                Crear Profesional
        </a>

    </div>

    <a href="/escritorio-admin"
       class="btn btn-secondary mb-4">
        Volver
    </a>

    <h2 class="fw-bold mb-4">
        🏥 Profesionales Autorizados
    </h2>

    <div class="card shadow-sm border-0 p-4">

        <table class="table table-hover align-middle">

            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>RUT</th>
                    <th>Especialidad</th>
                    <th>Email</th>
                    <th>Banco</th>
                    <th>Cuenta</th>
                    <th>Activo</th>
                </tr>
            </thead>

            <tbody>

                @forelse($profesionales as $profesional)

                    <tr>
                        <td>{{ $profesional->nombre }}</td>
                        <td>{{ $profesional->rut }}</td>
                        <td>{{ $profesional->especialidad }}</td>
                        <td>{{ $profesional->email }}</td>
                        <td>{{ $profesional->banco }}</td>
                        <td>{{ $profesional->numero_cuenta }}</td>
                        <td>
                            @if($profesional->activo)
                                ✅
                            @else
                                ❌
                            @endif
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="7">
                            No existen profesionales registrados.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

</body>
</html>
