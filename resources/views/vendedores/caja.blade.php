<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Caja Vendedor</title>
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

    <a href="/escritorio-vendedor"
       class="btn btn-secondary mb-4">
        Volver
    </a>

    <h2 class="fw-bold mb-4">
        💰 Mi Caja Vendedor
    </h2>

    <div class="row g-4 mb-4">

        <div class="col-md-4">
            <div class="card p-4 border-0 shadow-sm">
                <h6>Vouchers emitidos</h6>
                <h2>0</h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4 border-0 shadow-sm">
                <h6>Dinero recaudado</h6>
                <h2>$0</h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4 border-0 shadow-sm">
                <h6>Caja conforme</h6>
                <h2>⏳</h2>
                <small class="text-muted">
                    Pendiente de recepción
                </small>
            </div>
        </div>

    </div>

    <div class="card p-4 border-0 shadow-sm">

        <h5 class="fw-bold mb-3">
            Vouchers del vendedor
        </h5>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Cliente</th>
                    <th>Servicio</th>
                    <th>Copago</th>
                    <th>Estado</th>
                    <th>Caja</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td colspan="6">
                        Pendiente conectar datos reales.
                    </td>
                </tr>
            </tbody>
        </table>

    </div>

</div>

</body>
</html>
