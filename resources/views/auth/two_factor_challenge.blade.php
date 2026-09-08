<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SDI · Código de seguridad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{min-height:100vh;background:#f3f8f7;display:flex;align-items:center;justify-content:center;color:#15302f}
        .card-sdi{width:min(520px,92vw);border:0;border-radius:28px;box-shadow:0 22px 70px rgba(10,45,43,.12)}
        .shield{width:48px;height:48px;border-radius:16px;background:#00796b;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900;font-size:24px}
        .code-input{font-size:28px;letter-spacing:6px;text-align:center;font-weight:900}
        .brand{letter-spacing:.12em;color:#00806f;font-weight:900;text-transform:uppercase;font-size:.82rem}
    </style>
</head>
<body>
<div class="card card-sdi p-4 p-lg-5">
    <div class="brand mb-2">SDI · Seguridad central</div>
    <h1 class="fw-bold d-flex align-items-center gap-3">
        <span class="shield">2</span>
        Codigo de seguridad
    </h1>
    <p class="text-muted mt-3">
        Ingresa el codigo de 6 digitos de Google Authenticator para abrir administracion o auditoria.
    </p>

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
            Verificar y entrar
        </button>
    </form>
</div>
</body>
</html>
