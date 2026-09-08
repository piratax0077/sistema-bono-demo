<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Mascotas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body style="background:#f4f7fb;">

<div class="container py-5">

    <div class="d-flex justify-content-between mb-4">

        <h2>
            🐾 Mis Mascotas
        </h2>

        <form method="POST"
              action="{{ route('logout') }}">
            @csrf

            <button class="btn btn-danger">
                Cerrar sesión
            </button>
        </form>

    </div>

    <div class="row">

        @forelse($mascotas as $mascota)

            <div class="col-md-4">

                <div class="card shadow-sm border-0 mb-4">

                    <div class="card-body">

                        <h4>
                            {{ $mascota->nombre }}
                        </h4>

                        <p>
                            {{ $mascota->especie }}
                            /
                            {{ $mascota->raza }}
                        </p>

                        <p>
                            🎂 {{ $mascota->fecha_nacimiento }}
                        </p>
                        <a href="#"
                           class="btn btn-primary btn-sm">
                            Ver Ficha
                        </a>
                        <a href="{{ route('cliente.mascotas.vouchers', $mascota->id) }}" class="btn btn-success btn-sm">
                            Ver Vouchers
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info">
                No existen mascotas asociadas a su cuenta.
            </div>
        @endforelse
    </div>
</div>

</body>
</html>
