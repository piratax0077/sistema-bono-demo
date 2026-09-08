<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Profesional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:#f4f7fb;">

<div class="container py-5">

    <a href="/admin/profesionales" class="btn btn-secondary mb-4">
        Volver
    </a>

    <div class="card p-5 border-0 shadow-sm">

        <h2 class="fw-bold mb-4">🏥 Crear Profesional</h2>

        <form method="POST" action="{{ route('admin.profesionales.store') }}">
            @csrf

            <input name="nombre" class="form-control mb-3" placeholder="Nombre / Clínica" required>
            <input name="rut" class="form-control mb-3" placeholder="RUT">
            <input name="especialidad" class="form-control mb-3" placeholder="Especialidad">
            <input name="telefono" class="form-control mb-3" placeholder="Teléfono">
            <input name="email" type="email" class="form-control mb-3" placeholder="Email">
            <div class="mb-3">
                <label>Contraseña de acceso</label>

                <input type="password"
                    name="password"
                    class="form-control"
                    placeholder="Si queda vacío: password">
            </div>
            <hr>

            <input name="banco" class="form-control mb-3" placeholder="Banco">
            <input name="tipo_cuenta" class="form-control mb-3" placeholder="Tipo de cuenta">
            <input name="numero_cuenta" class="form-control mb-3" placeholder="Número de cuenta">
            <input name="titular_cuenta" class="form-control mb-3" placeholder="Titular cuenta">
            <input name="rut_cuenta" class="form-control mb-3" placeholder="RUT cuenta">

            <button class="btn btn-primary w-100">
                Guardar Profesional
            </button>
        </form>

    </div>

</div>

</body>
</html>
