<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SDI - Validacion telefonica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { min-height:100vh; background:linear-gradient(135deg,#0f172a,#087f6f); }
        .otp-card { max-width:520px; border:0; border-radius:24px; box-shadow:0 24px 60px rgba(0,0,0,.24); }
        .brand { color:#087f6f; font-size:.78rem; letter-spacing:.16em; text-transform:uppercase; font-weight:800; }
    </style>
</head>
<body>
<main class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
    <section class="card otp-card w-100">
        <div class="card-body p-5">
            <div class="brand mb-2">SDI - Beneficiario</div>
            <h1 class="h3 fw-bold mb-2">Validacion del telefono</h1>
            <p class="text-muted">
                Ingresa el codigo de 6 digitos enviado al telefono registrado
                <strong>{{ $telefono ?? 'del beneficiario' }}</strong>.
            </p>

            @if(session('ok'))
                <div class="alert alert-success">{{ session('ok') }}</div>
            @endif

            @if(session('otp_demo'))
                <div class="alert alert-warning">
                    Modo local: codigo telefonico demo <strong>{{ session('otp_demo') }}</strong>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.otp.validar') }}" class="mb-3">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Codigo telefonico</label>
                    <input type="text"
                           name="otp"
                           class="form-control form-control-lg text-center"
                           inputmode="numeric"
                           autocomplete="one-time-code"
                           maxlength="6"
                           required
                           autofocus>
                    <small class="text-muted">
                        El codigo vence en pocos minutos. En produccion se envia por WhatsApp/SMS.
                    </small>
                </div>

                <button class="btn btn-success btn-lg w-100">
                    Validar y entrar
                </button>
            </form>

            <form method="POST" action="{{ route('login.otp.reenviar') }}" class="mb-3">
                @csrf
                <button class="btn btn-outline-secondary w-100">
                    Reenviar codigo al telefono
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-link text-danger w-100">
                    Cancelar inicio de sesion
                </button>
            </form>
        </div>
    </section>
</main>
</body>
</html>
