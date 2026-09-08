<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usar Voucher</title>

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

    </div>

    <div class="row justify-content-center">

        <div class="col-md-8">

            <div class="card shadow border-0 p-5">

                <h2 class="fw-bold mb-3">
                    🎟️ Usar Voucher SDI
                </h2>

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <p>
                    <strong>Cliente:</strong>
                    {{ $voucher->cliente_nombre }}
                </p>

                <p>
                    <strong>RUT Dueño:</strong>
                    {{ $voucher->cliente_rut_visible }}
                </p>

                <p>
                    <strong>Servicio:</strong>
                    {{ $voucher->tipo_servicio }}
                </p>

                <p>
                    <strong>Estado:</strong>
                    {{ $voucher->estado }}
                </p>

                <hr>

                @if($voucher->estado != 'activo')

                    <div class="alert alert-warning">
                        Este voucher aún no está disponible para asignar o ya fue asignado/usado.
                    </div>

                @else

                    <form method="POST"
                          action="{{ route('vouchers.asignarProfesional', $voucher->qr_token) }}">

                        @csrf
                        @if($voucher->estado == 'pendiente_cliente')

                        <div class="card border-success mb-4">

                            <div class="card-body">

                                <h4>
                                    ¿Acepta este voucher?
                                </h4>

                                <form method="POST"
                                    action="{{ route('vouchers.aceptar',$voucher->qr_token) }}">
                                    @csrf

                                    <button class="btn btn-success">
                                        Aceptar Voucher
                                    </button>
                                </form>

                                <hr>

                                <form method="POST"
                                    action="{{ route('vouchers.rechazar',$voucher->qr_token) }}">
                                    @csrf

                                    <textarea
                                        name="motivo_rechazo"
                                        class="form-control mb-3"
                                        placeholder="Motivo rechazo"></textarea>

                                    <button class="btn btn-danger">
                                        Rechazar Voucher
                                    </button>
                                </form>

                            </div>

                        </div>

                        @endif
                        <div class="mb-3">
                            <label class="form-label">
                                Seleccione profesional o centro autorizado
                            </label>

                            <select name="profesional_id"
                                    class="form-select"
                                    required>

                                <option value="">
                                    Seleccione
                                </option>

                                @foreach($profesionales as $profesional)

                                    <option value="{{ $profesional->id }}">
                                        {{ $profesional->nombre }}
                                        — {{ $profesional->especialidad }}
                                    </option>

                                @endforeach

                            </select>
                        </div>

                        <button class="btn btn-primary w-100">
                            Asignar Voucher al Profesional
                        </button>

                    </form>

                @endif

            </div>

        </div>

    </div>

</div>

</body>
</html>
