<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SDI · Salud Digital Integrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('partials.scroll_fade')
</head>

<style>
    html, body {
        height: 100%;
        overflow: hidden;
    }

    .bg-aurora {
        position: fixed;
        inset: 0;
        z-index: 0;
        background: linear-gradient(120deg, rgba(12,53,117,.88), rgba(18,69,148,.88));
        background-size: 200% 200%;
        animation: auroraMove 18s ease-in-out infinite alternate;
    }

    .bg-blob {
        position: fixed;
        border-radius: 50%;
        filter: blur(70px);
        z-index: 0;
        opacity: .25;
        pointer-events: none;
    }
    .bg-blob.one {
        width: 420px; height: 420px;
        background: #1c7a7a;
        top: -120px; left: -100px;
        animation: floatBlob 14s ease-in-out infinite alternate;
    }
    .bg-blob.two {
        width: 380px; height: 380px;
        background: #0a3a8c;
        bottom: -150px; right: -90px;
        animation: floatBlob 16s ease-in-out infinite alternate-reverse;
    }

    @keyframes auroraMove {
        0%   { background-position: 0% 50%; }
        100% { background-position: 100% 50%; }
    }
    @keyframes floatBlob {
        0%   { transform: translate(0,0) scale(1); }
        100% { transform: translate(30px,25px) scale(1.08); }
    }

    .content-wrap {
        position: relative;
        z-index: 1;
    }

    .fade-up {
        opacity: 0;
        animation: fadeUp .8s ease forwards;
    }
    .fade-up.delay-1 { animation-delay: .15s; }
    .fade-up.delay-2 { animation-delay: .3s; }
    .fade-up.delay-3 { animation-delay: .45s; }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.25);
        padding: 6px 14px;
        border-radius: 999px;
        font-size: .8rem;
        backdrop-filter: blur(4px);
    }
    .status-dot {
        width: 8px; height: 8px;
        background: #31bebe;
        border-radius: 50%;
        animation: pulseDot 2s ease-out infinite;
    }
    @keyframes pulseDot {
        0%   { box-shadow: 0 0 0 0 rgba(49,190,190,.6); }
        70%  { box-shadow: 0 0 0 8px rgba(49,190,190,0); }
        100% { box-shadow: 0 0 0 0 rgba(49,190,190,0); }
    }

    .feature-chip {
        transition: transform .25s ease, background-color .25s ease;
        perspective: 400px;
    }
    .feature-chip:hover {
        transform: translateY(-3px);
        background-color: rgba(255,255,255,.16) !important;
    }

    .icon-badge {
        width: 52px;
        height: 52px;
        min-width: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform .3s ease;
    }
    .feature-chip:hover .icon-badge {
        transform: translateY(-2px);
    }
    .icon-badge img {
        filter: brightness(0) invert(1) drop-shadow(0 2px 3px rgba(0,0,0,.35));
    }

    .login-card {
        position: relative;
        overflow: hidden;
        background: rgba(255,255,255,.97);
        backdrop-filter: blur(10px);
    }

    .btn-entrar {
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .btn-entrar:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(49,190,190,.35);
    }

    .form-control:focus {
        border-color: #31bebe;
        box-shadow: 0 0 0 .2rem rgba(49,190,190,.25);
    }

    @media (prefers-reduced-motion: reduce) {
        .bg-aurora, .bg-blob, .status-dot, .fade-up {
            animation: none !important;
            opacity: 1;
        }
    }
</style>

<body class="d-flex align-items-center" style="height:100vh;background:#0f172a url('{{ asset('images/imagenes/background_1.jpg') }}') center center / cover no-repeat fixed;">

<div class="bg-aurora"></div>
<div class="bg-blob one"></div>
<div class="bg-blob two"></div>

<div class="container content-wrap py-3 py-md-5" style="max-height:100vh;overflow:hidden;">

    <div class="row align-items-center">

        <div class="col-md-7 text-white d-none d-md-block">

    

            <h1 class="display-4 fw-bold fade-up delay-1">
                SDI
            </h1>

            <p class="lead mt-4 fade-up delay-2">
                Salud Digital Integrada: bonos, pagos, QR,
                rendiciones y liquidaciones.
            </p>

            <div class="row mt-5 g-3 fade-up delay-3">

                <div class="col-md-6">
                    <div class="p-4 rounded bg-white bg-opacity-10 d-flex align-items-center gap-3 feature-chip">
                        <span class="icon-badge">
                            <img src="{{ asset('images/iconos/cupon-login.png') }}" alt="" width="40" height="40">
                        </span>
                        Emisión de vouchers
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-4 rounded bg-white bg-opacity-10 d-flex align-items-center gap-3 feature-chip">
                        <span class="icon-badge">
                            <img src="{{ asset('images/iconos/qr-login.png') }}" alt="" width="40" height="40">
                        </span>
                        QR sin papel por WhatsApp
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-4 rounded bg-white bg-opacity-10 d-flex align-items-center gap-3 feature-chip">
                        <span class="icon-badge">
                            <img src="{{ asset('images/iconos/autorizacion-login.png') }}" alt="" width="40" height="40">
                        </span>
                        Red profesional autorizada
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-4 rounded bg-white bg-opacity-10 d-flex align-items-center gap-3 feature-chip">
                        <span class="icon-badge">
                            <img src="{{ asset('images/iconos/liquidacion-login.png') }}" alt="" width="40" height="40">
                        </span>
                        Rendición y liquidación
                    </div>
                </div>

            </div>

        </div>

        <div class="col-12 text-white text-center d-md-none mb-3 fade-up">
            <h1 class="fw-bold">SDI</h1>
            <p class="small mb-0">Salud Digital Integrada</p>
        </div>

        <div class="col-md-5">

            <div class="card login-card shadow-lg border-0 rounded-4 fade-up delay-1">

                <div class="card-body p-4 p-md-5">

                    <h3 class="fw-bold mb-2">
                        Iniciar sesión
                    </h3>

                    <p class="text-muted mb-4">
                        Acceso vendedor, profesional, auditor o administrador.
                    </p>

                    <form method="POST" action="{{ route('login') }}">

                        @csrf

                        <div class="mb-3">
                            <label class="fw-semibold">Email</label>
                            <input type="email"
                                   name="email"
                                   class="form-control form-control-lg"
                                   required
                                   autofocus>
                        </div>

                        <div class="mb-3">
                            <label class="fw-semibold">Contraseña</label>
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

                            <label class="form-check-label fw-semibold" for="remember">
                                Recordarme
                            </label>
                        </div>

                        <button class="btn btn-entrar btn-lg w-100 fw-semibold text-white border-0"
                                style="background:linear-gradient(135deg,#31bebe,#2aa8a8);">
                            Entrar
                        </button>

                    </form>

                    <hr>

                    <small class="text-muted">
                        SDI · Plataforma segura y verificada
                    </small>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>
