<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Emisor de Bonos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body style="background:#f4f7fb;">

<div class="container py-5">

    <a href="/admin/vendedores"
       class="btn btn-secondary mb-4">
        Volver
    </a>

    <div class="card p-5 border-0 shadow-sm">

        <h2 class="fw-bold mb-4">
            🎟️ Crear Emisor de Bonos
        </h2>

        <form method="POST"
              action="{{ route('admin.vendedores.store') }}">

            @csrf

            <div class="mb-3">
                <label>Nombre</label>

                <input type="text"
                       name="nombre"
                       class="form-control"
                       required>
            </div>

            <div class="mb-3">
                <label>RUT</label>

                <input type="text"
                       name="rut"
                       class="form-control">
            </div>

            <div class="mb-3">
                <label>Email</label>

                <input type="email"
                       name="email"
                       class="form-control">
            </div>

            <div class="mb-3">
                <label>Teléfono</label>

                <input type="text"
                       name="telefono"
                       class="form-control">
            </div>
            <div class="mb-3">
                <label>Contraseña de acceso</label>

                <input type="password"
                    name="password"
                    class="form-control"
                    placeholder="Si queda vacío: password">
            </div>

            <div class="form-check mb-4">

                <input type="checkbox"
                       name="activo"
                       value="1"
                       checked
                       class="form-check-input"
                       id="activo">

                <label class="form-check-label"
                       for="activo">
                    Emisor activo
                </label>

            </div>

            <button class="btn btn-primary w-100">
                Guardar Emisor
            </button>

        </form>

    </div>

</div>

</body>
</html>
