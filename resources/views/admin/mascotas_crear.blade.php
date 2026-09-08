<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>Crear Mascota</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body style="background:#f4f7fb;">

<div class="container py-5">

    <a href="/admin/mascotas"
       class="btn btn-secondary mb-4">
        Volver
    </a>

    <div class="card p-5 border-0 shadow-sm">

        <h2 class="fw-bold mb-4">
            🐾 Crear Mascota
        </h2>

        <form method="POST"
              action="{{ route('admin.mascotas.store') }}">

            @csrf

            <div class="row">
                <div class="col-12 mb-3">
                    <label>Cliente / beneficiario asociado</label>

                    <select name="cliente_id"
                            class="form-select">
                        <option value="">Sin cliente asociado</option>

                        @isset($clientes)
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}">
                                    {{ $cliente->nombre }} - {{ $cliente->telefono ?: 'sin teléfono' }}
                                </option>
                            @endforeach
                        @endisset
                    </select>

                    <small class="text-muted">
                        Si la mascota se crea desde “Crear usuario”, esta asociación se hace automáticamente.
                    </small>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Nombre Mascota</label>

                    <input type="text"
                           name="nombre"
                           class="form-control"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Especie</label>

                    <select name="especie"
                            class="form-select">

                        <option>Canino</option>
                        <option>Felino</option>
                        <option>Ave</option>
                        <option>Roedor</option>
                        <option>Otro</option>

                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Raza</label>

                    <input type="text"
                           name="raza"
                           class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Sexo</label>

                    <select name="sexo"
                            class="form-select">

                        <option>Macho</option>
                        <option>Hembra</option>

                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Fecha Nacimiento</label>

                    <input type="date"
                           name="fecha_nacimiento"
                           class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Edad</label>

                    <input type="number"
                           min="0"
                           name="edad"
                           class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Color</label>

                    <input type="text"
                           name="color"
                           class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Microchip</label>

                    <input type="text"
                           name="microchip"
                           class="form-control">
                </div>

            </div>

            <hr>

            <h5 class="mb-3">
                👤 Dueño
            </h5>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label>RUT Dueño</label>

                    <input type="text"
                           name="dueno_rut"
                           class="form-control"
                           required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Nombre Dueño</label>

                    <input type="text"
                           name="dueno_nombre"
                           class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Teléfono</label>

                    <input type="text"
                           name="dueno_telefono"
                           class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Email</label>

                    <input type="email"
                           name="dueno_email"
                           class="form-control">
                </div>

            </div>

            <div class="form-check mb-4">

                <input type="checkbox"
                       class="form-check-input"
                       name="activo"
                       value="1"
                       checked
                       id="activo">

                <label class="form-check-label"
                       for="activo">
                    Mascota Activa
                </label>

            </div>

            <button class="btn btn-primary w-100">
                Guardar Mascota
            </button>

        </form>

    </div>

</div>

</body>
</html>
