<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SDI · Activar autenticador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{min-height:100vh;background:linear-gradient(135deg,#063f3a,#0f766e 45%,#eef8f6 45%);color:#163231}
        .wrap{max-width:920px;margin:42px auto;padding:0 18px}
        .card-sdi{border:0;border-radius:28px;box-shadow:0 22px 70px rgba(10,45,43,.18);overflow:hidden}
        .qr-panel{background:#f4fbfa;border-radius:20px;border:1px solid #d2ebe7;text-align:center;padding:22px}
        .qr-panel img{width:280px;height:280px;max-width:100%}
        .secret-code{font-family:Consolas,monospace;background:#052f2b;color:#d8fff8;border-radius:14px;padding:12px 14px;display:block;overflow-wrap:anywhere}
        .code-input{font-size:26px;letter-spacing:6px;text-align:center;font-weight:900}
        .brand{letter-spacing:.12em;color:#00806f;font-weight:900;text-transform:uppercase;font-size:.82rem}
        @media(max-width:820px){.grid{grid-template-columns:1fr!important}}
    </style>
</head>
<body>
<div class="wrap">
    <div class="card card-sdi p-4 p-lg-5">
        <div class="brand mb-2">SDI · Seguridad central</div>
        <h1 class="fw-bold mb-2">Activar Google Authenticator</h1>
        <p class="text-muted mb-4">
            Este paso protege los roles de administrador y auditor. Luego lo podremos conectar con tu app propia de autenticacion.
        </p>

        <div class="grid" style="display:grid;grid-template-columns:330px minmax(0,1fr);gap:28px;align-items:start">
            <div class="qr-panel">
                <img src="{{ route('two-factor.qr') }}" alt="QR autenticador">
                <p class="text-muted mb-0 mt-2">Escanea este QR con Google Authenticator.</p>
            </div>

            <div>
                <h4 class="fw-bold">Paso final</h4>
                <p class="text-muted">
                    Despues de escanear, escribe el codigo de 6 digitos que muestra la app.
                </p>

                <label class="form-label fw-bold">Clave manual</label>
                <span class="secret-code mb-3">{{ $secret }}</span>

                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('two-factor.confirm') }}">
                    @csrf
                    <label class="form-label fw-bold">Codigo de la app</label>
                    <input class="form-control code-input"
                           name="code"
                           inputmode="numeric"
                           autocomplete="one-time-code"
                           maxlength="12"
                           required
                           autofocus>
                    <button class="btn btn-success btn-lg w-100 mt-3">
                        Activar y entrar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
