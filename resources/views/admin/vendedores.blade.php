<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Emisores de bonos</title>

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
        <a href="{{ route('admin.vendedores.crear') }}"
   class="btn btn-primary mb-4">
    Crear Emisor
</a>
    </div>

    <a href="/escritorio-admin"
       class="btn btn-secondary mb-4">
        Volver
    </a>

    <h2 class="fw-bold mb-4">
        🎟️ Emisores de Bonos Autorizados
    </h2>

    <div class="card shadow-sm border-0 p-4">

        <table class="table table-hover align-middle">

            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>RUT</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Activo</th>
                </tr>
            </thead>

            <tbody>

                @forelse($vendedores as $vendedor)

                    <tr>
                        <td>{{ $vendedor->nombre }}</td>
                        <td>{{ $vendedor->rut }}</td>
                        <td>{{ $vendedor->email }}</td>
                        <td>{{ $vendedor->telefono }}</td>

                        <td>
                            @if($vendedor->activo)
                                ✅
                            @else
                                ❌
                            @endif
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="5">
                            No existen emisores registrados.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

</body>
</html>
