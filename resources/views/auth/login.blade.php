<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SDI · Salud Digital Integrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="min-height:100vh;background:linear-gradient(135deg,#0f172a,#0d6efd);">

<div class="container py-5">

    <div class="row align-items-center min-vh-100">

        <div class="col-md-7 text-white">

            <h1 class="display-4 fw-bold">
                SDI
            </h1>

            <p class="lead mt-4">
                Salud Digital Integrada: bonos, pagos, QR,
                rendiciones y liquidaciones.
            </p>

            <div class="row mt-5 g-3">

                <div class="col-md-6">
                    <div class="p-4 rounded bg-white bg-opacity-10">
                        🎟️ Emisión de vouchers
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-4 rounded bg-white bg-opacity-10">
                        📲 QR sin papel por WhatsApp
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-4 rounded bg-white bg-opacity-10">
                        🏥 Red profesional autorizada
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-4 rounded bg-white bg-opacity-10">
                        💸 Rendición y liquidación
                    </div>
                </div>

            </div>

        </div>

        <div class="col-md-5">

            <div class="card shadow-lg border-0 rounded-4">

                <div class="card-body p-5">

                    <h3 class="fw-bold mb-2">
                        Iniciar sesión
                    </h3>

                    <p class="text-muted mb-4">
                        Acceso vendedor, profesional, auditor o administrador.
                    </p>

                    <form method="POST" action="{{ route('login') }}">

                        @csrf

                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email"
                                   name="email"
                                   class="form-control form-control-lg"
                                   required
                                   autofocus>
                        </div>

                        <div class="mb-3">
                            <label>Contraseña</label>
                            <input type="password"
                                   name="password"
                                   class="form-control form-control-lg"
                                   required>
                        </div>

                        <div class="form-check mb-4">
                            <input type="checkbox"
                                   name="remember"
                                   class="form-check-input"
                                   id="remember">

                            <label class="form-check-label" for="remember">
                                Recordarme
                            </label>
                        </div>

                        <button class="btn btn-primary btn-lg w-100">
                            Entrar
                        </button>

                    </form>

                    <hr>

                    <small class="text-muted">
                        SDI · Salud Digital Integrada · Plataforma segura de bonos y vouchers.
                    </small>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>
