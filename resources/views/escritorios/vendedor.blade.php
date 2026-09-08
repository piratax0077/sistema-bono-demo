<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Escritorio Vendedor Vouchers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

    </div>

<a href="/"
   class="btn btn-secondary mb-4">
    Volver
</a>

<h2 class="fw-bold mb-4">
    🎟️ Escritorio Vendedor SDI
</h2>

    <div class="row g-4">

        <div class="col-md-4">
            <div class="card p-4 shadow-sm border-0">
                <h5>Emitir Voucher</h5>
                <p class="text-muted">Crear voucher uno a uno: dueño, mascota, profesional y servicio.</p>

                <a href="/vouchers/crear"
                   class="btn btn-primary">
                    Emitir Voucher
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4 shadow-sm border-0">
                <h5>Vouchers Emitidos</h5>
                <p class="text-muted">Revisar vouchers generados, pagados, activos y usados.</p>

                <a href="/vouchers"
                   class="btn btn-dark">
                    Ver Vouchers
                </a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4 shadow-sm border-0">
                <h5>Resumen Caja</h5>
                <p class="text-muted">
                    Dinero recaudado, vouchers emitidos y caja conforme.
                </p>

                <a href="/vendedores/caja"
                class="btn btn-success">
                    Ver Mi Caja
                </a>
            </div>
        </div>

    </div>

</div>

</body>
</html>
