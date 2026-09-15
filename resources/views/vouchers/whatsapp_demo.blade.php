<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WhatsApp simulado · {{ $voucher->codigo }}</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#d7dedb;color:#17211f;font-family:Inter,Segoe UI,Arial,sans-serif}.page{min-height:100vh;padding:28px 16px}.toolbar{width:min(1180px,100%);margin:0 auto 16px;display:flex;justify-content:space-between;align-items:center;gap:12px}.toolbar a{color:#075e54;text-decoration:none;font-weight:800}.demo-badge{background:#fff3cd;color:#725600;border:1px solid #f1d678;border-radius:999px;padding:8px 14px;font-size:13px;font-weight:800}.layout{width:min(1180px,100%);margin:auto;display:grid;grid-template-columns:290px minmax(0,1fr);background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 22px 65px rgba(16,42,36,.2);min-height:720px}.contacts{border-right:1px solid #dce6e2;background:#f7faf9}.contacts-title{padding:22px;border-bottom:1px solid #dce6e2}.contacts-title strong{display:block;font-size:20px}.contacts-title small{color:#667672}.contact{display:flex;gap:12px;align-items:center;padding:16px 18px;color:#17211f;text-decoration:none;border-bottom:1px solid #e6eeeb}.contact.active{background:#e8f4f0;border-left:5px solid #00a884;padding-left:13px}.avatar{width:46px;height:46px;border-radius:50%;background:#d8eee7;display:grid;place-items:center;color:#087f6f;font-weight:900;font-size:18px;flex:0 0 auto}.contact strong,.contact small{display:block}.contact small{color:#6b7a76;margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:185px}.chat{display:flex;flex-direction:column;background:#efeae2;position:relative}.chat:before{content:"";position:absolute;inset:0;opacity:.2;background-image:radial-gradient(#7f978f 1px,transparent 1px);background-size:18px 18px}.chat-header{position:relative;z-index:1;background:#f0f2f5;padding:13px 18px;display:flex;align-items:center;gap:12px;border-bottom:1px solid #d8dfdc}.chat-header .meta{flex:1}.chat-header strong,.chat-header small{display:block}.chat-header small{color:#667672;margin-top:3px}.chat-body{position:relative;z-index:1;padding:28px clamp(16px,5vw,70px);overflow:auto;flex:1}.encryption{text-align:center;margin-bottom:24px}.encryption span{display:inline-block;background:#fff5c8;color:#6f5a14;border-radius:10px;padding:8px 12px;font-size:12px;box-shadow:0 1px 2px rgba(0,0,0,.08)}.bubble{margin-left:auto;width:min(430px,92%);background:#d9fdd3;border-radius:13px 2px 13px 13px;padding:7px;box-shadow:0 1px 2px rgba(0,0,0,.13)}.bubble-intro{padding:8px 8px 10px;font-size:15px}.voucher-card{background:#fff;border-radius:10px;overflow:hidden}.voucher-image{background:#fff;padding:12px;text-align:center}.voucher-image img{display:block;width:min(100%,340px);height:auto;margin:auto;border-radius:8px}.voucher-caption{padding:15px 17px;border-top:1px solid #edf1ef}.brand{color:#087f6f;text-transform:uppercase;font-weight:900;letter-spacing:.12em;font-size:11px}.voucher-caption h1{font-size:20px;margin:7px 0 12px}.data{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:12px 0}.data small{display:block;color:#71807c;font-size:11px;text-transform:uppercase;font-weight:700;margin-bottom:2px}.secure-link{display:block;text-align:center;margin-top:14px;padding:11px;border-top:1px solid #e8eeeb;color:#087f6f;text-decoration:none;font-weight:800}.bubble-footer{text-align:right;padding:5px 5px 1px;color:#667672;font-size:11px}.checks{color:#53bdeb;font-size:14px;font-weight:900}.system-note,.send-panel,.flash{width:min(430px,92%);margin:14px 0 0 auto;background:#fff;border-radius:10px;padding:12px 14px;color:#53625e;font-size:13px;box-shadow:0 1px 2px rgba(0,0,0,.1)}.flash.ok{background:#d9fdd3;color:#165c49}.flash.error{background:#ffe0e0;color:#8b2424}.send-panel button{width:100%;border:0;border-radius:9px;padding:12px;background:#00a884;color:#fff;font-weight:900;font-size:15px;cursor:pointer}.send-panel button:disabled{background:#aab7b3;cursor:not-allowed}.missing{margin-top:8px;color:#b3261e;font-weight:800}.composer{position:relative;z-index:1;background:#f0f2f5;padding:12px 16px;display:flex;gap:10px;align-items:center}.composer .input{background:#fff;border-radius:999px;padding:13px 18px;flex:1;color:#80908b}.send{width:44px;height:44px;border:0;border-radius:50%;background:#00a884;color:#fff;font-size:20px}.back-mobile{display:none}@media(max-width:760px){.page{padding:0}.toolbar{padding:12px;margin:0;background:#fff}.layout{border-radius:0;display:block;min-height:calc(100vh - 62px)}.contacts{display:none}.chat{min-height:calc(100vh - 62px)}.chat-body{padding:20px 12px}.bubble{width:96%}.back-mobile{display:inline;color:#075e54;text-decoration:none;font-size:24px}.data{grid-template-columns:1fr}.demo-badge{font-size:11px;padding:6px 9px}}
    </style>
</head>
<body>
@include('partials.demo_user_switcher')
@php
    $titular = $qrPayload['titular'] ?? [];
    $profesional = $qrPayload['profesional'] ?? [];
    $voucherData = $qrPayload['voucher'] ?? [];
    $imageVersion = file_exists($qrImage['path']) ? filemtime($qrImage['path']) : time();
    $iniciales = collect(preg_split('/\s+/', trim($contacto['nombre'])))->filter()->take(2)->map(fn($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))->implode('');
    $telefonoDisponible = preg_replace('/\D+/', '', (string) $contacto['detalle']) !== '';
@endphp
<div class="page">
    <div class="toolbar">
        <div>
            <a href="{{ route('vouchers.qr', $voucher->qr_token) }}">← Volver al bono</a>
            @if(Route::has('demo.portal'))
                <a href="{{ route('demo.portal') }}" style="margin-left:18px">Volver al escritorio demo</a>
            @endif
        </div>
        <span class="demo-badge">{{ $whatsappCloudEnabled ? 'PRUEBA REAL · WHATSAPP CLOUD API' : 'SIMULACIÓN · NO ENVÍA MENSAJES REALES' }}</span>
    </div>
    <div class="layout">
        <aside class="contacts">
            <div class="contacts-title"><strong>WhatsApp simulado</strong><small>Seleccione el destinatario</small></div>
            @foreach($contactos as $id => $item)
                <a class="contact {{ $destino === $id ? 'active' : '' }}" href="{{ route('vouchers.qr.whatsappDemo', ['token' => $voucher->qr_token, 'destino' => $id]) }}">
                    <span class="avatar">{{ collect(preg_split('/\s+/', trim($item['nombre'])))->filter()->take(2)->map(fn($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))->implode('') }}</span>
                    <span><strong>{{ $item['nombre'] }}</strong><small>{{ $item['detalle'] }}</small></span>
                </a>
            @endforeach
        </aside>
        <main class="chat">
            <header class="chat-header">
                <a class="back-mobile" href="{{ route('vouchers.qr', $voucher->qr_token) }}">‹</a>
                <span class="avatar">{{ $iniciales }}</span>
                <div class="meta"><strong>{{ $contacto['nombre'] }}</strong><small>{{ $contacto['detalle'] }} · en línea</small></div>
                <span aria-label="Seguridad">🔒</span>
            </header>
            <section class="chat-body">
                <div class="encryption"><span>🔒 {{ $whatsappCloudEnabled ? 'Modo de prueba real: solo puede enviarse al destinatario autorizado.' : 'Vista protegida de demostración. El mensaje no será enviado.' }}</span></div>
                @if(session('whatsapp_demo_ok'))<div class="flash ok">✓ {{ session('whatsapp_demo_ok') }}</div>@endif
                @if(session('whatsapp_demo_error'))<div class="flash error">{{ session('whatsapp_demo_error') }}</div>@endif
                <article class="bubble">
                    <div class="bubble-intro">Hola. Medichile comparte tu bono digital seguro. Preséntalo en recepción; no necesitas imprimirlo.</div>
                    <div class="voucher-card">
                        <div class="voucher-image"><img src="{{ $qrImage['url'] }}?v={{ $imageVersion }}" alt="QR del bono {{ $voucher->codigo }}"></div>
                        <div class="voucher-caption">
                            <div class="brand">Medichile · SDI Salud Digital Integrada</div>
                            <h1>Bono {{ $voucher->codigo }}</h1>
                            <div class="data">
                                <div><small>Paciente</small><strong>{{ $titular['nombre'] ?? $voucher->cliente_nombre }}</strong></div>
                                <div><small>Profesional</small><strong>{{ $profesional['nombre'] ?? $voucher->prestador_nombre }}</strong></div>
                                <div><small>Prestación</small><strong>{{ $voucherData['servicio'] ?? $voucher->tipo_servicio }}</strong></div>
                                <div><small>Copago</small><strong>${{ number_format($voucherData['copago'] ?? $voucher->copago_usuario, 0, ',', '.') }}</strong></div>
                                <div><small>Emitido</small><strong>{{ optional($voucher->created_at)->format('d-m-Y H:i') }}</strong></div>
                                <div><small>Estado</small><strong>{{ strtoupper($voucher->estado) }}</strong></div>
                            </div>
                            <a class="secure-link" href="{{ route('vouchers.qr', $voucher->qr_token) }}">Ver ficha segura del bono</a>
                        </div>
                    </div>
                    <div class="bubble-footer">{{ now()->format('H:i') }} <span class="checks">✓✓</span></div>
                </article>
                <div class="system-note"><strong>{{ $whatsappCloudEnabled ? 'WhatsApp Cloud API:' : 'Entrega simulada:' }}</strong> {{ $whatsappCloudEnabled ? 'se cargará y enviará la imagen QR al número de prueba autorizado.' : 'la imagen QR está lista para adjuntarse a WhatsApp. Esta pantalla no contacta a ninguna persona.' }}</div>
                <form class="send-panel" method="POST" action="{{ route('vouchers.qr.whatsappDemo.enviar', $voucher->qr_token) }}">
                    @csrf
                    <input type="hidden" name="destino" value="{{ $destino }}">
                    <button type="submit" @disabled(!$telefonoDisponible)>{{ $whatsappCloudEnabled ? 'Enviar por WhatsApp Cloud API' : 'Enviar bono por WhatsApp (simulado)' }}</button>
                    @if(!$telefonoDisponible)<div class="missing">Dato faltante: teléfono WhatsApp de {{ $contacto['nombre'] }}.</div>@endif
                </form>
            </section>
            <footer class="composer"><span>😊</span><div class="input">Escribe un mensaje</div><button class="send" type="button" aria-label="Enviar deshabilitado">➤</button></footer>
        </main>
    </div>
</div>
</body>
</html>
