<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Servicio</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body style="background:#f4f7fb;">

<div class="container py-5">

    <a href="/admin/servicios"
       class="btn btn-secondary mb-4">
        Volver
    </a>

    <div class="card p-5 border-0 shadow-sm">

        <h2 class="fw-bold mb-4">
            🩺 Crear Prestación Médica
        </h2>

        <form method="POST"
              action="{{ route('admin.servicios.store') }}">

            @csrf

            <div class="mb-3">
                <label>Nombre Servicio</label>

                <input type="text"
                       name="nombre"
                       class="form-control"
                       required>
            </div>

            <div class="mb-3">
                <label>Descripción</label>

                <textarea name="descripcion"
                          class="form-control"
                          rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label>Valor Base</label>

                <input type="number"
                       name="valor_base"
                       class="form-control"
                       value="0">
            </div>

            <div class="mb-3">
                <label>Copago Usuario</label>

                <input type="number"
                       name="copago_base"
                       class="form-control"
                       value="0">
            </div>

            <div class="mb-3">
                <label>Comisión SDI</label>

                <input type="number"
                       name="comision_veterchile"
                       class="form-control"
                       value="0">
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
                    Servicio Activo
                </label>

            </div>

            <button class="btn btn-primary w-100">
                Guardar Servicio
            </button>

        </form>

    </div>

</div>

</body>
</html>
