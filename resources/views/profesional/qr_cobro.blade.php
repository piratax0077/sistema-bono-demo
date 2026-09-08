<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QR de cobro {{ $voucher->codigo }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #eef5f3; color: #17352f; }
        .qr-card { border: 0; border-radius: 22px; box-shadow: 0 18px 45px rgba(23,53,47,.10); }
        .qr-frame { background: #fff; border: 1px solid #d8e7e3; border-radius: 20px; padding: 18px; display: inline-block; }
        .data-label { color: #6b7f7a; font-size: .77rem; letter-spacing: .06em; text-transform: uppercase; font-weight: 700; }
        .data-value { font-weight: 650; margin-bottom: 1rem; }
        .security-box { background: #e8f8f2; border: 1px solid #b8e3d3; border-radius: 14px; }
    </style>
</head>
<body>
<main class="container py-4 py-lg-5" style="max-width:1050px">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <div class="text-uppercase text-success fw-bold small">Expediente digital de cobro</div>
            <h2 class="fw-bold mb-1">QR para enviar a cobro</h2>
            <p class="text-muted mb-0">Bono {{ $voucher->codigo }} · enlace firmado válido durante 24 horas.</p>
        </div>
        <a href="{{ route('profesional.cobros') }}" class="btn btn-outline-secondary">Volver a cobros</a>
    </div>

    <div class="card qr-card overflow-hidden">
        <div class="card-body p-4 p-lg-5">
            <div class="row g-5 align-items-center">
                <div class="col-lg-5 text-center">
                    <div class="qr-frame">
                        <img src="{{ $qrDataUri }}" alt="QR seguro de cobro {{ $voucher->codigo }}" width="330" height="330" class="img-fluid">
                    </div>
                    <div class="mt-3"><span class="badge bg-success px-3 py-2">Habilitado para cobro</span></div>
                </div>
                <div class="col-lg-7">
                    <h4 class="fw-bold mb-4">Datos asociados al QR</h4>
                    <div class="row">
                        <div class="col-sm-6"><div class="data-label">Paciente</div><div class="data-value">{{ $datosCobro['paciente'] }}</div></div>
                        <div class="col-sm-6"><div class="data-label">Profesional</div><div class="data-value">{{ $datosCobro['profesional'] }}</div></div>
                        <div class="col-12"><div class="data-label">Relación</div><div class="data-value">{{ $datosCobro['relacion'] }}</div></div>
                        <div class="col-sm-6"><div class="data-label">Lugar de atención</div><div class="data-value">{{ $datosCobro['lugar_atencion'] }}</div></div>
                        <div class="col-sm-6"><div class="data-label">Fecha de atención</div><div class="data-value">{{ $datosCobro['fecha_atencion'] }}</div></div>
                        <div class="col-sm-6"><div class="data-label">Tipo de atención</div><div class="data-value">{{ $datosCobro['tipo_atencion'] }}</div></div>
                        <div class="col-sm-6"><div class="data-label">Valor a cobrar</div><div class="data-value fs-4 text-success">${{ number_format($datosCobro['valor_a_cobrar'], 0, ',', '.') }}</div></div>
                    </div>

                    <div class="security-box p-3 mb-4">
                        <strong>Validación segura.</strong> El enlace comprueba la firma y la vigencia antes de mostrar el expediente. No contiene comisión, cuenta bancaria ni liquidaciones internas.
                    </div>

                    <div class="d-grid gap-2 d-sm-flex">
                        <form method="POST" action="{{ route('vouchers.cobrar', $voucher->id) }}" class="flex-grow-1">
                            @csrf
                            <button class="btn btn-success w-100 fw-bold">Enviar QR a cobro</button>
                        </form>
                        <a href="https://wa.me/?text={{ rawurlencode($mensajeWhatsapp) }}" target="_blank" rel="noopener" class="btn btn-outline-success">Compartir por WhatsApp</a>
                        <a href="{{ $qrDataUri }}" download="qr-cobro-{{ $voucher->codigo }}.svg" class="btn btn-outline-secondary">Descargar QR</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
