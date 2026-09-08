<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paciente desde Agenda · Medichile</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#eef6f4;color:#123532;font-family:Inter,Segoe UI,Arial,sans-serif}.shell{max-width:1240px;margin:auto;padding:34px 18px}.hero{background:linear-gradient(135deg,#064a41,#07866f);color:#fff;border-radius:26px;padding:30px;margin-bottom:24px}.hero small{text-transform:uppercase;letter-spacing:.16em;color:#a7e9dc;font-weight:900}.hero h1{font-size:clamp(28px,4vw,44px);margin:7px 0}.hero p{margin:0;color:#d9f4ee;font-size:17px}.profile{display:grid;grid-template-columns:2fr 1fr 1fr 2fr;gap:12px;margin-top:22px}.profile div{background:#ffffff17;border:1px solid #ffffff2b;border-radius:14px;padding:12px}.profile span{display:block;font-size:11px;text-transform:uppercase;color:#bde7df;margin-bottom:4px}.flash{padding:15px 18px;border-radius:14px;margin:14px 0;font-weight:800}.ok{background:#d8f7e9;color:#096447}.error{background:#ffe0e0;color:#922323}.section-title{display:flex;justify-content:space-between;align-items:end;gap:14px;margin:28px 0 15px}.section-title h2{margin:0}.section-title p{margin:0;color:#60736f}.slots{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}.slot{background:#fff;border:1px solid #d5e7e2;border-radius:20px;padding:20px;box-shadow:0 12px 30px rgba(6,52,47,.07)}.date{color:#087f6f;text-transform:uppercase;letter-spacing:.08em;font-weight:900;font-size:12px}.time{font-size:30px;color:#087f6f;font-weight:900;margin:4px 0}.slot h3{margin:8px 0 3px}.slot p{margin:5px 0;color:#60736f}.amounts{display:flex;justify-content:space-between;gap:10px;background:#edf7f4;border-radius:12px;padding:11px;margin:14px 0}.amounts span{display:block;font-size:11px;color:#60736f}.slot button{width:100%;border:0;border-radius:12px;padding:13px;background:#07866f;color:#fff;font-weight:900;cursor:pointer}.history{background:#fff;border-radius:20px;overflow:auto;box-shadow:0 12px 30px rgba(6,52,47,.07)}table{width:100%;border-collapse:collapse;min-width:760px}th,td{text-align:left;padding:14px;border-bottom:1px solid #e5efec}th{background:#f3f8f6;color:#42625c}.result{background:#fff;border:2px solid #45b990;border-radius:22px;padding:24px;margin-bottom:22px}.result h2{margin-top:0;color:#087f6f}.actions{display:flex;gap:10px;flex-wrap:wrap}.actions a{padding:11px 16px;border-radius:11px;background:#07866f;color:#fff;text-decoration:none;font-weight:900}.actions a.alt{background:#fff;color:#07866f;border:1px solid #07866f}@media(max-width:900px){.slots{grid-template-columns:1fr 1fr}.profile{grid-template-columns:1fr 1fr}}@media(max-width:620px){.slots,.profile{grid-template-columns:1fr}}
    </style>
</head>
<body>
@include('partials.demo_user_switcher')
<main class="shell">
    <header class="hero">
        <small>Paciente desde Agenda</small>
        <h1>Reservar hora y comprar bono</h1>
        <p>Selecciona un horario. El pago simulado confirma la reserva, genera el bono y deja el QR disponible para recepción.</p>
        <div class="profile">
            <div><span>Paciente</span><strong>{{ $perfilPersona['nombre'] }}</strong></div>
            <div><span>RUT</span><strong>{{ sdi_formatear_rut($perfilPersona['rut']) }}</strong></div>
            <div><span>Clase / edad</span><strong>{{ $perfilPersona['grupo_ingreso'] ?: '-' }} · {{ $perfilPersona['edad'] ?: '-' }} años</strong></div>
            <div><span>Dirección</span><strong>{{ $perfilPersona['direccion'] ?: 'Sin información' }}</strong></div>
        </div>
    </header>

    @include('partials.demo_flow_guide', ['demoStep' => $agendaOnlineResultado ? 2 : 1])

    @if(session('ok'))<div class="flash ok">✓ {{ session('ok') }}</div>@endif
    @if(session('error'))<div class="flash error">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="flash error">{{ $errors->first() }}</div>@endif

    @if($agendaOnlineResultado)
        <section class="result">
            <h2>✓ Hora y bono confirmados</h2>
            <p><strong>{{ $agendaOnlineResultado->codigo }}</strong> · {{ $agendaOnlineResultado->prestador_nombre }} · {{ optional($agendaOnlineResultado->agenda?->fecha_hora_confirmada)->format('d-m-Y H:i') }}</p>
            <div class="actions"><a href="{{ route('vouchers.qr', $agendaOnlineResultado->qr_token) }}">Ver QR generado</a><a class="alt" href="{{ route('totem.local', ['tab' => 'autoatencion']) }}">Ir al tótem y avisar llegada</a></div>
        </section>
    @endif

    <div class="section-title"><div><h2>Horarios disponibles</h2><p>Profesional, especialidad, lugar, valor y copago vienen vinculados desde la agenda.</p></div></div>
    <section class="slots">
        @forelse($horariosOnline as $horario)
            <article class="slot">
                <div class="date">{{ $horario->fecha_hora->translatedFormat('l d \d\e F') }}</div>
                <div class="time">{{ $horario->fecha_hora->format('H:i') }}</div>
                <h3>{{ $horario->profesional->nombre }}</h3>
                <p>{{ $horario->profesional->especialidad }} · {{ $horario->servicio->nombre }}</p>
                <p><strong>{{ $horario->centro_nombre }}</strong><br>{{ $horario->lugar_atencion }} · {{ $horario->centro_direccion }}</p>
                <div class="amounts"><div><span>Valor</span><strong>${{ number_format($horario->servicio->valor_base,0,',','.') }}</strong></div><div><span>Copago</span><strong>${{ number_format($horario->servicio->copago_base,0,',','.') }}</strong></div></div>
                <form method="POST" action="{{ route('cliente.agenda-online.comprar') }}">
                    @csrf
                    <input type="hidden" name="horario_id" value="{{ $horario->id }}">
                    <input type="hidden" name="rut" value="{{ $user->rut }}">
                    <input type="hidden" name="metodo_pago" value="tarjeta_demo_online">
                    <input type="hidden" name="return_to" value="paciente.agenda">
                    <button type="submit">Reservar, pagar y generar QR</button>
                </form>
            </article>
        @empty
            <div class="flash error">No quedan horarios disponibles en la agenda de prueba.</div>
        @endforelse
    </section>

    <div class="section-title"><div><h2>Mis horas</h2><p>Historial asociado al paciente y sus bonos.</p></div></div>
    <section class="history"><table><thead><tr><th>Bono</th><th>Profesional</th><th>Fecha y hora</th><th>Estado</th><th>QR</th></tr></thead><tbody>
        @forelse($agendas as $agenda)
            <tr><td>{{ optional($agenda->voucher)->codigo ?: '-' }}</td><td>{{ optional($agenda->profesional)->nombre ?: optional($agenda->voucher)->prestador_nombre ?: '-' }}</td><td>{{ optional($agenda->fecha_hora_confirmada ?: $agenda->fecha_hora_solicitada)->format('d-m-Y H:i') }}</td><td>{{ $agenda->estado }}</td><td>@if(optional($agenda->voucher)->qr_token)<a href="{{ route('vouchers.qr', $agenda->voucher->qr_token) }}">Ver QR</a>@else - @endif</td></tr>
        @empty
            <tr><td colspan="5">Aún no existen horas asociadas.</td></tr>
        @endforelse
    </tbody></table></section>
</main>
</body>
</html>
