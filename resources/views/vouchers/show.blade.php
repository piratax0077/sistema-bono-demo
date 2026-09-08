<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Voucher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:#f4f7fb;">

@php
    $profesionalNombre = $voucher->prestador_nombre
        ?: optional($voucher->profesionalCatalogo)->nombre_mostrable
        ?: optional($voucher->profesional)->nombre;
    $profesionalRut = $voucher->prestador_rut
        ?: optional($voucher->profesionalCatalogo)->rut_mostrable
        ?: optional($voucher->profesional)->rut;
    $profesionalEspecialidad = $voucher->prestador_especialidad
        ?: optional($voucher->profesionalCatalogo)->especialidad_mostrable
        ?: optional($voucher->profesional)->especialidad;
    $profesionalTelefono = $voucher->prestador_telefono
        ?: optional($voucher->profesionalCatalogo)->telefono_mostrable
        ?: optional($voucher->profesional)->telefono;
    $profesionalEmail = $voucher->prestador_email
        ?: optional($voucher->profesionalCatalogo)->email_mostrable
        ?: optional($voucher->profesional)->email;

    $beneficiarioNombre = $voucher->beneficiario_nombre ?: $voucher->cliente_nombre;
    $beneficiarioRut = $voucher->beneficiario_rut_visible ?: $voucher->cliente_rut_visible;
    $beneficiarioTipo = $voucher->beneficiario_parentesco ?: ($voucher->beneficiario_tipo === 'carga' ? 'Carga' : 'Titular');
    $beneficiarioDireccion = $voucher->beneficiario_direccion_visible;
    $beneficiarioEdad = $voucher->beneficiario_edad_visible;
@endphp

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

        <h2>🎟️ Voucher {{ $voucher->codigo }}</h2>

        <hr>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="border rounded-4 p-3 h-100">
                    <h5 class="fw-bold mb-3">Titular que compra / autoriza</h5>
                    <p class="mb-2"><strong>Cliente:</strong> {{ $voucher->cliente_nombre }}</p>
                    <p class="mb-2"><strong>RUT:</strong> {{ $voucher->cliente_rut_visible }}</p>
                    <p class="mb-2"><strong>Teléfono:</strong> {{ $voucher->cliente_telefono ?: 'Sin dato' }}</p>
                    <p class="mb-0"><strong>Email:</strong> {{ $voucher->cliente_email ?: 'Sin dato' }}</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="border rounded-4 p-3 h-100">
                    <h5 class="fw-bold mb-3">Paciente / beneficiario</h5>
                    <p class="mb-2"><strong>Nombre:</strong> {{ $beneficiarioNombre ?: 'Sin dato' }}</p>
                    <p class="mb-2"><strong>RUT:</strong> {{ $beneficiarioRut ?: 'Sin dato' }}</p>
                    <p class="mb-2"><strong>Tipo:</strong> {{ $beneficiarioTipo ?: 'Sin dato' }}</p>
                    <p class="mb-2"><strong>Edad:</strong> {{ $beneficiarioEdad !== null ? $beneficiarioEdad.' años' : 'Sin dato' }}</p>
                    <p class="mb-0"><strong>Dirección:</strong> {{ $beneficiarioDireccion ?: 'Sin dato' }}</p>
                </div>
            </div>
        </div>

        <div class="border rounded-4 p-3 mt-3">
            <h5 class="fw-bold mb-3">Profesional asignado</h5>
            @if($profesionalNombre)
                <div class="row">
                    <div class="col-md-4 mb-2"><strong>Nombre:</strong> {{ $profesionalNombre }}</div>
                    <div class="col-md-2 mb-2"><strong>RUT:</strong> {{ $profesionalRut ?: 'Sin dato' }}</div>
                    <div class="col-md-3 mb-2"><strong>Especialidad:</strong> {{ $profesionalEspecialidad ?: 'Sin dato' }}</div>
                    <div class="col-md-3 mb-2"><strong>Teléfono:</strong> {{ $profesionalTelefono ?: 'Sin dato' }}</div>
                </div>
                <p class="mb-0"><strong>Email:</strong> {{ $profesionalEmail ?: 'Sin dato' }}</p>
            @else
                <div class="alert alert-warning mb-0">
                    Este voucher aún no tiene profesional asociado.
                </div>
            @endif
        </div>

        <div class="border rounded-4 p-3 mt-3">
            <h5 class="fw-bold mb-3">Datos del bono</h5>
            <p><strong>Servicio:</strong> {{ $voucher->tipo_servicio }}</p>
            <p class="mb-0"><strong>Estado:</strong> {{ $voucher->estado }}</p>
        </div>

        <hr>

        <p><strong>Valor:</strong> ${{ number_format($voucher->valor, 0, ',', '.') }}</p>
        <p><strong>Copago Usuario:</strong> ${{ number_format($voucher->copago_usuario, 0, ',', '.') }}</p>
        <p><strong>Comisión SDI:</strong> ${{ number_format($voucher->comision_veterchile, 0, ',', '.') }}</p>
        <p><strong>Saldo Profesional:</strong> ${{ number_format($voucher->saldo_veterinario, 0, ',', '.') }}</p>

        <hr>

        <p><strong>QR Token:</strong></p>
        <code>{{ $voucher->qr_token }}</code>

        <div class="mt-4 d-flex gap-2 flex-wrap">

            @if($voucher->estado == 'pendiente_pago')
                <a href="{{ route('vouchers.pagar', $voucher->id) }}"
                class="btn btn-success">
                    Registrar Pago
                </a>
            @endif

            @if($voucher->estado == 'activo')
                <form method="POST"
                    action="{{ route('vouchers.cobrar', $voucher->id) }}">
                    @csrf

                    <button type="submit" class="btn btn-primary">
                        Cobrar Voucher
                    </button>
                </form>
            @endif

            <a href="{{ route('vouchers.qr', $voucher->qr_token) }}"
            class="btn btn-dark">
                Ver QR
            </a>

            @if(session('whatsapp_otp_url'))
                <a href="{{ session('whatsapp_otp_url') }}"
                target="_blank"
                class="btn btn-success">
                    Enviar OTP por WhatsApp
                </a>
            @endif
            @if($voucher->estado != 'cobrado' && !$voucher->qr_usado)
                <form method="POST"
                    action="{{ route('vouchers.reenviarOtp', $voucher->qr_token) }}">
                    @csrf

                    <button type="submit" class="btn btn-warning">
                        Reenviar OTP
                    </button>
                </form>
            @endif
            @if($voucher->estado == 'activo' || $voucher->estado == 'usado' || $voucher->estado == 'cobrado')
                <a href="https://wa.me/?text={{ urlencode('Hola, este es tu voucher SDI. Revisa tu QR seguro aquí: '.route('vouchers.qr', $voucher->qr_token)) }}"
                target="_blank"
                class="btn btn-success">
                    Enviar Voucher WhatsApp
                </a>
            @endif
            @if($voucher->saldo_cliente_aplicado > 0)
                <div class="alert alert-info mt-3">
                    Se aplicó saldo cliente por:
                    <strong>${{ number_format($voucher->saldo_cliente_aplicado, 0, ',', '.') }}</strong>
                </div>
            @endif
            <a href="{{ route('vouchers.index') }}"
            class="btn btn-secondary">
                Volver
            </a>

        </div>


    </div>

</div>

</body>
</html>
