<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Pago Voucher</title>
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

    <div class="card shadow border-0 p-5">

        <h2>💳 Registrar Pago</h2>

        <p><strong>Voucher:</strong> {{ $voucher->codigo }}</p>
        <p><strong>Cliente:</strong> {{ $voucher->cliente_nombre }}</p>
        <p><strong>Copago a pagar:</strong> ${{ number_format($voucher->copago_usuario, 0, ',', '.') }}</p>

        <hr>

        <form method="POST"
              action="{{ route('vouchers.procesarPago', $voucher->id) }}">

            @csrf

            <div class="mb-3">
                <label>Método de pago</label>

                <select name="metodo_pago"
                        class="form-select"
                        required>
                    <option value="">Seleccione</option>
                    <option value="efectivo">Efectivo</option>
                    <option value="tarjeta_pos">Tarjeta / POS</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="webpay">Webpay</option>
                    <option value="mercadopago">MercadoPago</option>
                </select>
            </div>

            <div class="mb-3">
                <label>Comprobante / referencia</label>

                <input type="text"
                       name="comprobante"
                       class="form-control"
                       placeholder="N° operación, boleta, transbank, etc.">
            </div>

            <button class="btn btn-success w-100">
                Confirmar Pago y Activar Voucher
            </button>

            <a href="{{ route('vouchers.show', $voucher->id) }}"
               class="btn btn-secondary w-100 mt-2">
                Volver
            </a>

        </form>

    </div>

</div>

</body>
</html>
