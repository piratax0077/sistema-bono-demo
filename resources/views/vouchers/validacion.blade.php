<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validación de voucher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f7fb;
        }

        .validation-card {
            border: 0;
            border-radius: 24px;
            box-shadow: 0 20px 55px rgba(5, 48, 43, .10);
        }

        .status-pill {
            border-radius: 999px;
            display: inline-flex;
            font-size: .85rem;
            font-weight: 800;
            letter-spacing: .06em;
            padding: .45rem .85rem;
            text-transform: uppercase;
        }

        .section-title {
            color: #00796b;
            font-size: .8rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

@php
    $titular = $qrPayload['titular'] ?? [];
    $beneficiarios = $qrPayload['beneficiarios'] ?? [];
    $voucherData = $qrPayload['voucher'] ?? [];
    $firma = $qrPayload['integridad']['firma'] ?? null;
@endphp

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="javascript:history.back()" class="btn btn-secondary">
            Volver
        </a>

        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-danger">
                    Cerrar sesión
                </button>
            </form>
        @endauth
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card validation-card">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <div class="section-title">SDI Salud Digital Integrada</div>
                            <h1 class="fw-bold mb-2">
                                Validación de voucher
                            </h1>
                            <p class="text-muted mb-0">
                                Revisión visual para usuarios autorizados. La API técnica sigue disponible en JSON.
                            </p>
                        </div>

                        <span class="status-pill text-bg-{{ $nivel }}">
                            {{ $mensaje }}
                        </span>
                    </div>

                    <hr class="my-4">

                    @if(! $voucher)
                        <div class="alert alert-danger mb-0">
                            No se encontró un voucher asociado a este token.
                        </div>
                    @else
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="section-title mb-2">Voucher</div>
                                    <p class="mb-1"><strong>Código:</strong> {{ $voucher->codigo }}</p>
                                    <p class="mb-1"><strong>Estado:</strong> {{ $voucher->estado }}</p>
                                    <p class="mb-1"><strong>Servicio:</strong> {{ $voucherData['servicio'] ?? $voucher->tipo_servicio }}</p>
                                    <p class="mb-1"><strong>Valor:</strong> ${{ number_format($voucher->valor, 0, ',', '.') }}</p>
                                    <p class="mb-1"><strong>Copago:</strong> ${{ number_format($voucher->copago_usuario, 0, ',', '.') }}</p>
                                    <p class="mb-0"><strong>Vence:</strong> {{ $voucherData['vence'] ?? 'Sin dato' }}</p>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100">
                                    <div class="section-title mb-2">Titular</div>
                                    <p class="mb-1"><strong>Nombre:</strong> {{ $titular['nombre'] ?? 'Sin dato' }}</p>
                                    <p class="mb-1"><strong>RUT:</strong> {{ $titular['rut'] ?? 'Sin dato' }}</p>
                                    <p class="mb-1"><strong>Dirección:</strong> {{ $titular['direccion'] ?? 'Sin dato' }}</p>
                                    <p class="mb-0">
                                        <strong>Edad:</strong>
                                        {{ isset($titular['edad']) && $titular['edad'] !== null ? $titular['edad'].' años' : 'Sin dato' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded-4 p-3 mt-3">
                            <div class="section-title mb-2">Resultado de seguridad</div>
                            <ul class="mb-0">
                                @foreach($motivos as $motivo)
                                    <li>{{ $motivo }}</li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="border rounded-4 p-3 mt-3">
                            <div class="section-title mb-2">Beneficiarios / cargas</div>

                            @if(count($beneficiarios))
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>RUT</th>
                                                <th>Parentesco</th>
                                                <th>Dirección</th>
                                                <th>Edad</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($beneficiarios as $beneficiario)
                                                <tr>
                                                    <td>{{ $beneficiario['nombre'] ?? 'Sin dato' }}</td>
                                                    <td>{{ $beneficiario['rut'] ?? 'Sin dato' }}</td>
                                                    <td>{{ $beneficiario['parentesco'] ?? 'Sin dato' }}</td>
                                                    <td>{{ $beneficiario['direccion'] ?? 'Sin dato' }}</td>
                                                    <td>
                                                        {{ isset($beneficiario['edad']) && $beneficiario['edad'] !== null ? $beneficiario['edad'].' años' : 'Sin dato' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0">
                                    No hay beneficiarios/cargas registrados.
                                </p>
                            @endif
                        </div>

                        <div class="border rounded-4 p-3 mt-3">
                            <div class="section-title mb-2">Integridad</div>
                            <p class="mb-0 small text-muted">
                                Firma HMAC-SHA256: {{ $firma ?? 'Sin firma' }}
                            </p>
                        </div>

                        <div class="d-flex gap-2 flex-wrap mt-4">
                            <a href="{{ route('vouchers.qr', $voucher->qr_token) }}" class="btn btn-dark">
                                Ver QR
                            </a>
                            <a href="{{ url('/api/vouchers/'.$voucher->qr_token.'/validar') }}" class="btn btn-outline-secondary" target="_blank">
                                Ver JSON técnico
                            </a>
                            @auth
                                <a href="{{ route('vouchers.show', $voucher->id) }}" class="btn btn-outline-dark">
                                    Volver al voucher
                                </a>
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
