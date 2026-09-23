<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QR Voucher</title>
    @include('partials.demo_wide_layout')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f7fb;
        }

        .qr-card {
            border: 0;
            border-radius: 24px;
            box-shadow: 0 20px 55px rgba(5, 48, 43, .10);
        }

        .qr-box {
            background: #fff;
            border: 6px solid #ffffff;
            border-radius: 22px;
            box-shadow: 0 18px 38px rgba(0, 111, 156, .18);
            padding: 18px;
            display: inline-block;
        }

        .medichile-qr-shell {
            background:
                radial-gradient(circle at top left, rgba(24, 191, 206, .22), transparent 34%),
                linear-gradient(135deg, #005b8f, #008f8b 56%, #10b981);
            border-radius: 30px;
            box-shadow: 0 24px 65px rgba(0, 91, 143, .20);
            color: #fff;
            display: inline-block;
            padding: 18px;
        }

        .medichile-brand {
            align-items: center;
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 12px;
        }

        .medichile-mark {
            align-items: center;
            background: rgba(255, 255, 255, .18);
            border: 1px solid rgba(255, 255, 255, .38);
            border-radius: 16px;
            display: inline-flex;
            font-size: .92rem;
            font-weight: 900;
            height: 42px;
            justify-content: center;
            letter-spacing: .06em;
            width: 42px;
        }

        .medichile-title {
            font-weight: 900;
            letter-spacing: .04em;
            line-height: 1;
            text-align: left;
            text-transform: uppercase;
        }

        .medichile-subtitle {
            font-size: .75rem;
            font-weight: 600;
            opacity: .82;
            text-align: left;
        }

        .medichile-qr-code {
            font-size: .82rem;
            font-weight: 800;
            letter-spacing: .08em;
            margin-top: 12px;
            opacity: .92;
        }

        .generated-qr-preview img {
            background: #fff;
            border-radius: 26px;
            box-shadow: 0 22px 55px rgba(0, 91, 143, .16);
            max-width: 520px;
            width: 100%;
        }

        .qr-share-preview {
            background: #f4fbfa;
            border: 1px solid #d6e8e4;
            border-radius: 22px;
            padding: 14px;
        }

        .qr-share-preview img {
            border-radius: 18px;
            display: block;
            margin: 0 auto;
            max-width: 260px;
            width: 100%;
        }

        .secure-payload {
            background: #f0faf7;
            border: 1px dashed #4aa79b;
            border-radius: 14px;
            padding: 14px;
            white-space: pre-wrap;
            font-size: .82rem;
            color: #073d37;
            text-align: left;
            max-height: 260px;
            overflow: auto;
        }

        .section-title {
            color: #00796b;
            font-size: .8rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .paperless-note {
            background: linear-gradient(135deg, #e7fff8, #f8fffd);
            border: 1px solid #a9e7dc;
            border-radius: 18px;
            color: #07443d;
            padding: 14px 16px;
            text-align: left;
        }

        .share-modal-backdrop {
            align-items: center;
            background: rgba(3, 22, 19, .66);
            display: flex;
            inset: 0;
            justify-content: center;
            padding: 22px;
            position: fixed;
            z-index: 1050;
        }

        .share-modal-backdrop[hidden] {
            display: none !important;
        }

        .share-modal {
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 30px 90px rgba(0, 0, 0, .28);
            max-height: 92vh;
            overflow: auto;
            width: min(980px, 100%);
        }

        .share-modal-header {
            align-items: flex-start;
            border-bottom: 1px solid #dcebe8;
            display: flex;
            gap: 18px;
            justify-content: space-between;
            padding: 26px 28px 18px;
        }

        .share-close {
            align-items: center;
            background: #e8f4f1;
            border: 0;
            border-radius: 16px;
            color: #063d37;
            display: inline-flex;
            font-size: 1.5rem;
            height: 44px;
            justify-content: center;
            line-height: 1;
            width: 44px;
        }

        .share-modal-body {
            padding: 24px 28px 28px;
        }

        .share-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }

        .share-option-card {
            border: 1px solid #d6e8e4;
            border-radius: 18px;
            cursor: pointer;
            display: block;
            padding: 16px;
            transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }

        .share-option-card:hover {
            border-color: #009688;
            box-shadow: 0 10px 28px rgba(0, 121, 107, .12);
            transform: translateY(-1px);
        }

        .share-option-card input {
            margin-right: 8px;
        }

        .share-channel-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .share-channel {
            align-items: center;
            border: 1px solid #d6e8e4;
            border-radius: 999px;
            cursor: pointer;
            display: inline-flex;
            gap: 8px;
            padding: 11px 15px;
        }

        .share-results {
            background: #f7fbfa;
            border: 1px solid #dcebe8;
            border-radius: 18px;
            padding: 14px;
        }

        .share-result-item {
            align-items: center;
            border-bottom: 1px solid #e2eeeb;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding: 12px 0;
        }

        .share-result-item:last-child {
            border-bottom: 0;
        }

        .share-muted {
            color: #5f7470;
            font-size: .88rem;
        }

        @media (max-width: 576px) {
            .share-modal-header,
            .share-modal-body {
                padding-left: 18px;
                padding-right: 18px;
            }

            .share-result-item {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

@php
    $titular = $qrPayload['titular'] ?? [];
    $profesional = $qrPayload['profesional'] ?? [];
    $beneficiarios = $qrPayload['beneficiarios'] ?? [];
    $beneficiarioSeleccionado = $qrPayload['beneficiario_seleccionado'] ?? null;
    $firma = $qrPayload['integridad']['firma'] ?? null;
    $qrImageUrl = $qrImage['url'] ?? null;
    $qrImagePath = $qrImage['path'] ?? null;
    $qrImageVersion = $qrImagePath && file_exists($qrImagePath) ? filemtime($qrImagePath) : time();
    $qrImageDownloadName = 'qr-medichile-'.$voucher->codigo.'.png';

    $normalizarTelefono = function ($telefono) {
        $digits = preg_replace('/\D+/', '', (string) $telefono);

        if ($digits !== '' && substr($digits, 0, 2) !== '56') {
            $digits = '56'.ltrim($digits, '0');
        }

        return $digits;
    };

    $shareRecipients = [];
    $recipientIds = [];
    $addRecipient = function ($id, $label, $role, $phone = null, $email = null) use (&$shareRecipients, &$recipientIds, $normalizarTelefono) {
        $phone = $normalizarTelefono($phone);
        $email = trim((string) $email);

        if (($phone === '' && $email === '') || isset($recipientIds[$id])) {
            return;
        }

        $channels = [];
        if ($phone !== '') {
            $channels[] = 'whatsapp';
        }
        if ($email !== '') {
            $channels[] = 'email';
        }

        $recipientIds[$id] = true;
        $shareRecipients[] = [
            'id' => $id,
            'label' => $label ?: 'Sin nombre',
            'role' => $role,
            'phone' => $phone !== '' ? $phone : null,
            'email' => $email !== '' ? $email : null,
            'channels' => $channels,
        ];
    };

    $addRecipient(
        'titular',
        $titular['nombre'] ?? $voucher->cliente_nombre ?? 'Titular',
        'Titular / paciente',
        $titular['telefono'] ?? $voucher->cliente_telefono ?? null,
        $titular['email'] ?? $voucher->cliente_email ?? null
    );

    $addRecipient(
        'profesional',
        $voucher->prestador_nombre ?? 'Profesional',
        'Profesional tratante',
        $voucher->prestador_telefono ?? null,
        $voucher->prestador_email ?? null
    );

    $addRecipient(
        'centro-medico',
        'Centro médico / recepción',
        'Recepción del centro',
        $centroWhatsapp ?? null,
        $voucher->centro_email ?? 'recepcion@centromedico.cl'
    );

    $shareSubject = 'QR Medichile '.$voucher->codigo;
    $shareMessage = implode("\n", array_filter([
        'Medichile · Bono digital seguro',
        '',
        'QR del bono: '.$voucher->codigo,
        '',
        'Se adjunta imagen QR. No es necesario imprimir.',
    ], fn ($line) => $line !== null));
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

    @include('partials.demo_flow_guide', ['demoStep' => $voucher->demoRecorridoStep()])

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card qr-card">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center">
                        <div class="section-title">Medichile · SDI Salud Digital Integrada</div>
                        <h2 class="fw-bold mb-2">
                            Voucher {{ $voucher->codigo }}
                        </h2>
                        <p class="text-muted">
                            El QR abre esta ficha segura y mantiene validación por token + HMAC.
                        </p>

                        <div class="generated-qr-preview my-3">
                            <img
                                src="{{ $qrImageUrl }}?v={{ $qrImageVersion }}"
                                alt="QR Medichile {{ $voucher->codigo }}"
                            >
                        </div>

                        <div class="d-flex gap-2 justify-content-center flex-wrap mb-3">
                            <a href="{{ $qrImageUrl }}?v={{ $qrImageVersion }}"
                               class="btn btn-outline-success btn-sm"
                               target="_blank"
                               rel="noopener">
                                Abrir imagen QR
                            </a>
                            <a href="{{ $qrImageUrl }}?v={{ $qrImageVersion }}"
                               class="btn btn-success btn-sm"
                               download="{{ $qrImageDownloadName }}">
                                Descargar imagen QR
                            </a>
                        </div>

                        <p class="small text-muted mb-0">
                            Imagen pública: {{ $qrImageUrl }}
                        </p>

                        <div class="paperless-note mt-3 mx-auto" style="max-width: 720px;">
                            <strong>Modo sin impresión:</strong>
                            comparte la imagen QR por WhatsApp o email. El paciente recibe una foto descargable, no un enlace largo.
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="section-title mb-2">Titular</div>
                                <p class="mb-1"><strong>Nombre:</strong> {{ $titular['nombre'] ?? 'Sin dato' }}</p>
                                <p class="mb-1"><strong>RUT:</strong> {{ !empty($titular['rut']) ? sdi_formatear_rut($titular['rut']) : 'Sin dato' }}</p>
                                <p class="mb-1"><strong>Dirección:</strong> {{ $titular['direccion'] ?? 'Sin dato' }}</p>
                                <p class="mb-1">
                                    <strong>Edad:</strong>
                                    {{ isset($titular['edad']) && $titular['edad'] !== null ? $titular['edad'].' años' : 'Sin dato' }}
                                </p>
                                <p class="mb-0"><strong>Teléfono:</strong> {{ $titular['telefono'] ?? 'Sin dato' }}</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 h-100">
                                <div class="section-title mb-2">Voucher</div>
                                <p class="mb-1"><strong>Servicio:</strong> {{ $qrPayload['voucher']['servicio'] ?? $voucher->tipo_servicio }}</p>
                                <p class="mb-1"><strong>Estado:</strong> {{ $voucher->estado }}</p>
                                <p class="mb-1"><strong>Valor:</strong> ${{ number_format($voucher->valor, 0, ',', '.') }}</p>
                                <p class="mb-1"><strong>Copago:</strong> ${{ number_format($voucher->copago_usuario, 0, ',', '.') }}</p>
                                <p class="mb-1"><strong>Bonificación / cobertura:</strong> ${{ number_format(max($voucher->valor - $voucher->copago_usuario, 0), 0, ',', '.') }}</p>
                                <p class="mb-0"><strong>Vence:</strong> {{ $qrPayload['voucher']['vence'] ?? 'Sin vencimiento' }}</p>
                            </div>
                        </div>
                    </div>

                    @if(!empty($profesional['nombre']))
                        <div class="border rounded-4 p-3 mt-3">
                            <div class="section-title mb-2">Profesional asignado</div>
                            <div class="row">
                                <div class="col-md-4"><strong>Nombre:</strong> {{ $profesional['nombre'] ?? 'Sin dato' }}</div>
                                <div class="col-md-3"><strong>RUT:</strong> {{ !empty($profesional['rut']) ? sdi_formatear_rut($profesional['rut']) : 'Sin dato' }}</div>
                                <div class="col-md-3"><strong>Especialidad:</strong> {{ $profesional['especialidad'] ?? 'Sin dato' }}</div>
                                <div class="col-md-2"><strong>Teléfono:</strong> {{ $profesional['telefono'] ?? 'Sin dato' }}</div>
                            </div>
                            @if(!empty($profesional['email']))
                                <p class="text-muted mb-0 mt-2">
                                    <strong>Email:</strong> {{ $profesional['email'] }}
                                </p>
                            @endif
                        </div>
                    @endif

                    @if(!empty($qrPayload['hora_medica']))
                        @php($horaMedica = $qrPayload['hora_medica'])
                        <div class="border rounded-4 p-3 mt-3">
                            <div class="section-title mb-2">Hora médica solicitada</div>
                            <div class="row">
                                <div class="col-md-4"><strong>Fecha y hora:</strong> {{ $horaMedica['fecha_hora'] ?? 'Sin dato' }}</div>
                                <div class="col-md-4"><strong>Estado Med-SDI:</strong> {{ $horaMedica['estado_texto'] ?? 'Sin sincronizar' }}</div>
                                <div class="col-md-4"><strong>Hora Med-SDI:</strong> {{ $horaMedica['id_medichile'] ? '#'.$horaMedica['id_medichile'] : 'Sin dato' }}</div>
                            </div>
                        </div>
                    @endif

                    @if($beneficiarioSeleccionado)
                        <div class="border rounded-4 p-3 mt-3">
                            <div class="section-title mb-2">Voucher para</div>
                            <div class="row">
                                <div class="col-md-3"><strong>Nombre:</strong> {{ $beneficiarioSeleccionado['nombre'] ?? 'Sin dato' }}</div>
                                <div class="col-md-3"><strong>RUT:</strong> {{ !empty($beneficiarioSeleccionado['rut']) ? sdi_formatear_rut($beneficiarioSeleccionado['rut']) : 'Sin dato' }}</div>
                                <div class="col-md-3"><strong>Tipo:</strong> {{ $beneficiarioSeleccionado['parentesco'] ?? 'Titular' }}</div>
                                <div class="col-md-3">
                                    <strong>Edad:</strong>
                                    {{ isset($beneficiarioSeleccionado['edad']) && $beneficiarioSeleccionado['edad'] !== null ? $beneficiarioSeleccionado['edad'].' años' : 'Sin dato' }}
                                </div>
                            </div>
                            <p class="text-muted mb-0 mt-2">
                                <strong>Dirección:</strong> {{ $beneficiarioSeleccionado['direccion'] ?? ($titular['direccion'] ?? 'Sin dato') }}
                            </p>
                        </div>
                    @endif

                    <div class="border rounded-4 p-3 mt-3">
                        <div class="section-title mb-2">Cargas del titular</div>

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
                                                <td>{{ !empty($beneficiario['rut']) ? sdi_formatear_rut($beneficiario['rut']) : 'Sin dato' }}</td>
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
                                No hay beneficiarios/cargas registrados para este titular.
                            </p>
                        @endif
                    </div>

                    <div class="mt-4">
                        <div class="section-title mb-2">Payload firmado del QR</div>
                        <div class="secure-payload">{{ $qrText }}</div>
                        <p class="small text-muted mt-2 mb-0">
                            Firma HMAC-SHA256: {{ $firma }}
                        </p>
                    </div>

                    <div class="d-flex gap-2 flex-wrap mt-4">
                        <a href="{{ route('vouchers.validarPantalla', $voucher->qr_token) }}" class="btn btn-dark">
                            Validar en pantalla
                        </a>
                        <a href="{{ route('vouchers.qr.lectorDemo', $voucher->qr_token) }}" class="btn btn-success">
                            Leer QR simulado y ver datos del bono
                        </a>
                        <a href="{{ url('/api/vouchers/'.$voucher->qr_token.'/validar') }}" class="btn btn-outline-secondary" target="_blank">
                            JSON técnico API
                        </a>
                        @auth
                            <a href="{{ auth()->user()->rol === 'cliente' ? route('cliente.dashboard') : route('vouchers.show', $voucher->id) }}" class="btn btn-outline-dark">
                                Volver al voucher
                            </a>
                        @endauth
                        <button type="button" class="btn btn-success" id="openShareVoucherModal">
                            Enviar QR digital
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="share-modal-backdrop" id="shareVoucherModal" hidden>
    <div class="share-modal" role="dialog" aria-modal="true" aria-labelledby="shareVoucherTitle">
        <div class="share-modal-header">
            <div>
                <div class="section-title mb-2">Envío digital sin papel</div>
                <h3 class="fw-bold mb-2" id="shareVoucherTitle">Compartir QR Medichile {{ $voucher->codigo }}</h3>
                <p class="text-muted mb-0">
                    Selecciona uno o más destinatarios y uno o más métodos de envío. El sistema prepara la imagen QR
                    para compartirla sin imprimir papel.
                </p>
            </div>
            <button type="button" class="share-close" id="closeShareVoucherModal" aria-label="Cerrar">
                ×
            </button>
        </div>

        <div class="share-modal-body">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="section-title mb-2">1. Destinatarios</div>

                    @if(count($shareRecipients))
                        <div class="share-grid">
                            @foreach($shareRecipients as $recipient)
                                <label class="share-option-card">
                                    <div class="d-flex align-items-start">
                                        <input
                                            type="checkbox"
                                            class="share-recipient"
                                            value="{{ $recipient['id'] }}"
                                            @checked($loop->first)
                                        >
                                        <div>
                                            <strong>{{ $recipient['label'] }}</strong>
                                            <div class="share-muted">{{ $recipient['role'] }}</div>
                                            @if($recipient['phone'])
                                                <div class="small mt-2">WhatsApp: +{{ $recipient['phone'] }}</div>
                                            @endif
                                            @if($recipient['email'])
                                                <div class="small">Email: {{ $recipient['email'] }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            No hay contactos guardados. Puedes descargar la imagen QR y enviarla por tu canal autorizado.
                        </div>
                    @endif
                </div>

                <div class="col-lg-5">
                    <div class="section-title mb-2">2. Imagen QR</div>
                    <div class="qr-share-preview mb-3">
                        <img
                            src="{{ $qrImageUrl }}?v={{ $qrImageVersion }}"
                            alt="Imagen QR Medichile {{ $voucher->codigo }}"
                        >
                        <div class="d-flex gap-2 justify-content-center flex-wrap mt-3">
                            <a href="{{ $qrImageUrl }}?v={{ $qrImageVersion }}"
                               class="btn btn-sm btn-success"
                               download="{{ $qrImageDownloadName }}">
                                Descargar imagen
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-dark" id="copyVoucherImage">
                                Copiar imagen
                            </button>
                        </div>
                    </div>

                    <div class="section-title mb-2">3. Métodos</div>
                    <div class="share-channel-list mb-3">
                        <label class="share-channel">
                            <input type="checkbox" class="share-channel-input" value="whatsapp" checked>
                            WhatsApp
                        </label>
                        <label class="share-channel">
                            <input type="checkbox" class="share-channel-input" value="email">
                            Email
                        </label>
                        <label class="share-channel">
                            <input type="checkbox" class="share-channel-input" value="copy">
                            Copiar imagen
                        </label>
                    </div>

                    <label class="form-label fw-semibold" for="shareVoucherMessage">Mensaje QR editable</label>
                    <textarea class="form-control" id="shareVoucherMessage" rows="9">{{ $shareMessage }}</textarea>
                    <p class="share-muted mt-2 mb-0">
                        WhatsApp Web no adjunta imágenes desde un enlace automático; descarga o copia la imagen QR y adjúntala en la conversación.
                    </p>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap mt-4">
                <button type="button" class="btn btn-success" id="prepareShareVoucher">
                    Preparar envíos
                </button>
                <button type="button" class="btn btn-outline-dark" id="copyVoucherLink">
                    Copiar imagen QR
                </button>
                <button type="button" class="btn btn-outline-secondary" id="cancelShareVoucher">
                    Cerrar
                </button>
            </div>

            <div class="share-results mt-4" id="shareVoucherResults" hidden>
                <div class="section-title mb-2">Envíos QR preparados</div>
                <div id="shareVoucherResultsList"></div>
            </div>
        </div>
    </div>
</div>

<script>
    (() => {
        const modal = document.getElementById('shareVoucherModal');
        const openButton = document.getElementById('openShareVoucherModal');
        const closeButton = document.getElementById('closeShareVoucherModal');
        const cancelButton = document.getElementById('cancelShareVoucher');
        const prepareButton = document.getElementById('prepareShareVoucher');
        const copyLinkButton = document.getElementById('copyVoucherLink');
        const copyImageButton = document.getElementById('copyVoucherImage');
        const messageInput = document.getElementById('shareVoucherMessage');
        const results = document.getElementById('shareVoucherResults');
        const resultsList = document.getElementById('shareVoucherResultsList');

        const recipients = @json($shareRecipients);
        const subject = @json($shareSubject);
        const qrImageUrl = @json($qrImageUrl.'?v='.$qrImageVersion);
        const whatsappDemoUrl = @json(route('vouchers.qr.whatsappDemo', $voucher->qr_token));

        const channelLabels = {
            whatsapp: 'WhatsApp',
            email: 'Email',
            copy: 'Copiar imagen',
        };

        const openModal = () => {
            modal.hidden = false;
            prepareButton.focus();
        };

        const closeModal = () => {
            modal.hidden = true;
        };

        const selectedRecipients = () => {
            const checked = [...document.querySelectorAll('.share-recipient:checked')].map((item) => item.value);
            return recipients.filter((recipient) => checked.includes(recipient.id));
        };

        const selectedChannels = () => {
            return [...document.querySelectorAll('.share-channel-input:checked')].map((item) => item.value);
        };

        const copyTextToClipboard = async (text) => {
            try {
                await navigator.clipboard.writeText(text);
                alert('Copiado al portapapeles.');
            } catch (error) {
                window.prompt('Copia este texto:', text);
            }
        };

        const copyQrImageToClipboard = async () => {
            try {
                if (!navigator.clipboard || !window.ClipboardItem) {
                    throw new Error('Clipboard image no disponible');
                }

                const response = await fetch(qrImageUrl, { cache: 'no-store' });
                const blob = await response.blob();

                await navigator.clipboard.write([
                    new ClipboardItem({ 'image/png': blob })
                ]);

                alert('Imagen QR copiada. Ahora puedes pegarla en WhatsApp o email.');
            } catch (error) {
                window.open(qrImageUrl, '_blank', 'noopener');
                alert('Tu navegador no permitió copiar la imagen. Abrí la imagen QR para que puedas descargarla o copiarla manualmente.');
            }
        };

        const createActionItem = ({ title, detail, url, buttonText, copyText, copyImage, disabled }) => {
            const item = document.createElement('div');
            item.className = 'share-result-item';

            const text = document.createElement('div');
            const titleNode = document.createElement('strong');
            titleNode.textContent = title;
            const detailNode = document.createElement('div');
            detailNode.className = 'share-muted';
            detailNode.textContent = detail;

            text.appendChild(titleNode);
            text.appendChild(detailNode);
            item.appendChild(text);

            if (disabled) {
                const badge = document.createElement('span');
                badge.className = 'badge text-bg-warning';
                badge.textContent = 'Dato faltante';
                item.appendChild(badge);
                return item;
            }

            if (url) {
                const action = document.createElement('a');
                action.className = 'btn btn-sm btn-success';
                action.href = url;
                action.target = '_blank';
                action.rel = 'noopener';
                action.textContent = buttonText;
                item.appendChild(action);
                return item;
            }

            const copyButton = document.createElement('button');
            copyButton.type = 'button';
            copyButton.className = 'btn btn-sm btn-outline-dark';
            copyButton.textContent = buttonText;
            copyButton.addEventListener('click', () => {
                if (copyImage) {
                    copyQrImageToClipboard();
                    return;
                }

                copyTextToClipboard(copyText);
            });
            item.appendChild(copyButton);

            return item;
        };

        const prepareShares = () => {
            const channels = selectedChannels();
            const people = selectedRecipients();
            const message = messageInput.value.trim() || 'Medichile · se adjunta imagen QR.';

            resultsList.innerHTML = '';

            if (!channels.length) {
                alert('Selecciona al menos un método de envío.');
                return;
            }

            if (!people.length && !channels.includes('copy')) {
                alert('Selecciona al menos un destinatario o usa la opción Copiar imagen.');
                return;
            }

            if (channels.includes('whatsapp') || channels.includes('email') || channels.includes('copy')) {
                resultsList.appendChild(createActionItem({
                    title: 'Imagen QR Medichile',
                    detail: 'Copia o descarga esta imagen y adjúntala en WhatsApp/email. No se envía link largo al paciente.',
                    copyImage: true,
                    buttonText: 'Copiar imagen QR',
                }));
            }

            people.forEach((recipient) => {
                channels.forEach((channel) => {
                    if (channel === 'copy') {
                        return;
                    }

                    if (channel === 'whatsapp') {
                        if (!recipient.phone) {
                            resultsList.appendChild(createActionItem({
                                title: `${recipient.label} · ${channelLabels[channel]}`,
                                detail: 'Este destinatario no tiene teléfono registrado.',
                                disabled: true,
                            }));
                            return;
                        }

                        resultsList.appendChild(createActionItem({
                            title: `${recipient.label} · WhatsApp`,
                            detail: `Enviar a +${recipient.phone}`,
                            url: `${whatsappDemoUrl}?destino=${encodeURIComponent(recipient.id)}`,
                            buttonText: 'Abrir y enviar simulado',
                        }));
                    }

                    if (channel === 'email') {
                        if (!recipient.email) {
                            resultsList.appendChild(createActionItem({
                                title: `${recipient.label} · ${channelLabels[channel]}`,
                                detail: 'Este destinatario no tiene email registrado.',
                                disabled: true,
                            }));
                            return;
                        }

                        resultsList.appendChild(createActionItem({
                            title: `${recipient.label} · Email`,
                            detail: `Enviar a ${recipient.email}`,
                            url: `mailto:${recipient.email}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(message)}`,
                            buttonText: 'Abrir email',
                        }));
                    }
                });
            });

            if (channels.includes('copy')) {
                resultsList.appendChild(createActionItem({
                    title: 'Abrir imagen QR',
                    detail: 'Abre la imagen guardada en public/qr-vouchers para descargarla o compartirla.',
                    url: qrImageUrl,
                    buttonText: 'Abrir imagen',
                }));
            }

            results.hidden = false;
        };

        openButton?.addEventListener('click', openModal);
        closeButton?.addEventListener('click', closeModal);
        cancelButton?.addEventListener('click', closeModal);
        prepareButton?.addEventListener('click', prepareShares);
        copyLinkButton?.addEventListener('click', copyQrImageToClipboard);
        copyImageButton?.addEventListener('click', copyQrImageToClipboard);

        modal?.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });

        // Permite abrir el modal directo desde otras pantallas (ej. botón
        // "Compartir" del historial de bonos) con ?compartir=1 en la URL.
        if (new URLSearchParams(window.location.search).get('compartir') === '1') {
            openModal();
        }
    })();
</script>

</body>
</html>
