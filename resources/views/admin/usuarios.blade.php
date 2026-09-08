<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>Usuarios</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body style="background:#f4f7fb;">

<div class="container py-5">

    <a href="/escritorio-admin"
       class="btn btn-secondary mb-4">
        Volver
    </a>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <form method="POST"
            action="{{ route('logout') }}">
            @csrf

            <button class="btn btn-danger">
                Cerrar sesión
            </button>
        </form>
        <h2 class="fw-bold">
            👥 Usuarios del Sistema
        </h2>

        <a href="/admin/usuarios/crear"
           class="btn btn-primary">
            Crear Usuario
        </a>

    </div>

    @if(session('ok'))
        <div class="alert alert-success">
            {{ session('ok') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm p-4">

        <table class="table table-hover align-middle">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Activo</th>
                    <th>Acción</th>
                </tr>
            </thead>

            <tbody>

                @forelse($usuarios as $usuario)

                    <tr>

                        <td>{{ $usuario->id }}</td>

                        <td>{{ $usuario->name }}</td>

                        <td>{{ $usuario->email }}</td>

                        <td>
                            <span class="badge bg-dark">
                                {{ $usuario->rol }}
                            </span>
                        </td>

                        <td>

                            @if($usuario->activo)
                                ✅
                            @else
                                ❌
                            @endif

                        </td>
                        <td>
                            <a href="{{ route('admin.usuarios.editar', $usuario->id) }}"
                            class="btn btn-sm btn-primary">
                                Editar
                            </a>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5">
                            No existen usuarios.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

</body>
</html>
