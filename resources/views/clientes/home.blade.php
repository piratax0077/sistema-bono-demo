<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio paciente · Salud Digital Integrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root{--blue:#1848a1;--cyan:#31bebe;--ink:#17283f;--muted:#65758a;--line:#d5e1f1;--soft:#edf4ff}
        *{box-sizing:border-box}body{margin:0;background:linear-gradient(145deg,#edf4ff,#f9fcff);color:var(--ink);font-family:Inter,Segoe UI,Arial,sans-serif}.home-shell{width:min(1160px,calc(100% - 32px));margin:34px auto 60px}.hero{position:relative;overflow:hidden;padding:38px;border-radius:28px;background:linear-gradient(120deg,#1848a1,#236bb0 56%,#31bebe);color:#fff;box-shadow:0 24px 60px rgba(24,72,161,.2)}.hero:after{content:"";position:absolute;width:330px;height:330px;border-radius:50%;right:-100px;top:-180px;background:#ffffff18}.eyebrow{font-size:.76rem;font-weight:900;letter-spacing:.15em;text-transform:uppercase}.hero h1{font-size:clamp(2rem,5vw,3.4rem);margin:8px 0}.hero p{max-width:720px;color:#e3f5ff;font-size:1.08rem}.chips{display:flex;gap:9px;flex-wrap:wrap;margin-top:20px}.chip{padding:8px 13px;border:1px solid #ffffff55;border-radius:999px;background:#ffffff17;font-weight:700;font-size:.86rem}.section-title{margin:34px 0 15px}.section-title h2{margin:0;font-size:1.5rem}.section-title p{margin:5px 0 0;color:var(--muted)}.actions{display:grid;grid-template-columns:repeat(4,1fr);gap:15px}.action{display:flex;flex-direction:column;min-height:210px;padding:22px;border:1px solid var(--line);border-radius:22px;background:#fff;color:var(--ink);text-decoration:none;box-shadow:0 12px 34px rgba(24,72,161,.08);transition:.18s}.action:hover{transform:translateY(-3px);border-color:var(--cyan);color:var(--ink)}.action-icon{font-size:2rem}.action strong{font-size:1.08rem;margin-top:15px}.action span:not(.action-icon){color:var(--muted);margin-top:7px;line-height:1.45}.action b{margin-top:auto;padding-top:18px;color:var(--blue)}.content-grid{display:grid;grid-template-columns:1.25fr .75fr;gap:18px;margin-top:18px}.card-home{border:1px solid var(--line);border-radius:24px;background:#fff;padding:25px;box-shadow:0 12px 34px rgba(24,72,161,.07)}.appointment{display:grid;grid-template-columns:auto 1fr;gap:18px;align-items:center}.date-box{min-width:92px;padding:15px;border-radius:18px;background:var(--soft);text-align:center;color:var(--blue)}.date-box strong{display:block;font-size:1.8rem}.date-box span{font-weight:800;text-transform:uppercase}.appointment h3{margin:0 0 7px;font-size:1.2rem}.appointment p{margin:3px 0;color:var(--muted)}.metrics{display:grid;grid-template-columns:1fr 1fr;gap:12px}.metric{padding:15px;border-radius:16px;background:var(--soft)}.metric strong{display:block;color:var(--blue);font-size:1.65rem}.metric span{color:var(--muted);font-size:.83rem}.empty{padding:20px;border-radius:16px;background:var(--soft);color:var(--muted)}@media(max-width:900px){.actions{grid-template-columns:1fr 1fr}.content-grid{grid-template-columns:1fr}}@media(max-width:560px){.home-shell{width:min(100% - 20px,1160px);margin-top:18px}.hero{padding:27px}.actions{grid-template-columns:1fr}.appointment{grid-template-columns:1fr}.date-box{width:100%}}
    </style>
</head>
<body>
@include('partials.demo_user_switcher')
@include('partials.patient_channel_nav')

<main class="home-shell">
    @php
        $nombrePacienteReal = $pacienteMedsdi
            ? trim(implode(' ', array_filter([$pacienteMedsdi['nombres'] ?? null, $pacienteMedsdi['apellido_uno'] ?? null, $pacienteMedsdi['apellido_dos'] ?? null])))
            : $user->name;
        $rutPacienteReal = $pacienteMedsdi['rut'] ?? $user->rut;
        $telefonoPacienteReal = $pacienteMedsdi['telefono_uno'] ?? $user->telefono;
    @endphp
    @if(session('ok'))<div class="alert alert-success">{{ session('ok') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <section class="hero">
        <div class="eyebrow">Medichile · Portal del paciente</div>
        <h1 class="text-white">Hola, {{ $nombrePacienteReal }}</h1>
        <p>Desde aquí puedes administrar tus horas, bonos y llegada al centro médico en un solo lugar.</p>
        <div class="chips">
            <span class="chip">RUT {{ sdi_formatear_rut($rutPacienteReal) }}</span>
            <span class="chip">Perfil Med-SDI #{{ $pacienteMedsdi['id'] ?? 'no disponible' }}</span>
            <span class="chip">{{ $telefonoPacienteReal ?: 'Teléfono no registrado' }}</span>
            @if(!empty($pacienteMedsdi['email']))<span class="chip">{{ $pacienteMedsdi['email'] }}</span>@endif
        </div>
    </section>

    @if(! $perfilRemotoMedsdi['ok'])
        <div class="alert alert-warning mt-3">No fue posible cargar el perfil desde Med-SDI: {{ $perfilRemotoMedsdi['mensaje'] }}</div>
    @endif

    @include('partials.demo_security_cards')
    @include('partials.demo_flow_guide', ['demoInteractive' => true])

    <div class="section-title"><h2>¿Qué necesitas hacer?</h2><p>Accesos rápidos a las funciones principales.</p></div>
    <section class="actions" aria-label="Acciones principales">
        <a class="action" href="{{ route('cliente.dashboard') }}"><span class="action-icon">📅</span><strong>Reservar una hora</strong><span>Busca profesionales, prestaciones y horarios disponibles en Med-SDI.</span><b>Comenzar →</b></a>
        <a class="action" href="{{ route('paciente.escritorio') }}"><span class="action-icon">🎟️</span><strong>Mis bonos y pagos</strong><span>Revisa tus bonos, confirma horas y realiza el pago correspondiente.</span><b>Ver escritorio →</b></a>
        <a class="action" href="{{ route('paciente.agenda') }}"><span class="action-icon">🗓️</span><strong>Mi agenda</strong><span>Consulta tus próximas atenciones y el estado de cada reserva.</span><b>Ver agenda →</b></a>
        <a class="action" href="{{ route('paciente.totem', ['tab' => 'autoatencion']) }}"><span class="action-icon">✓</span><strong>Confirmar llegada</strong><span>Identifica tu hora por RUT y avisa que ya llegaste al centro.</span><b>Ir a autoatención →</b></a>
    </section>

    <section class="content-grid">
        <article class="card-home">
            <div class="eyebrow text-primary mb-3">Próxima atención</div>
            @if($proximaAgenda)
                @php($fechaProxima = $proximaAgenda->fecha_hora_confirmada ?: $proximaAgenda->fecha_hora_solicitada)
                <div class="appointment">
                    <div class="date-box"><strong>{{ $fechaProxima->format('d') }}</strong><span>{{ $fechaProxima->translatedFormat('M') }}</span><div>{{ $fechaProxima->format('H:i') }}</div></div>
                    <div><h3>{{ $proximaAgenda->voucher?->tipo_servicio ?: 'Atención médica' }}</h3><p><strong>Profesional:</strong> {{ $proximaAgenda->voucher?->prestador_nombre ?: $proximaAgenda->profesional?->nombre ?: 'Por confirmar' }}</p><p><strong>Estado:</strong> {{ str_replace('_', ' ', ucfirst($proximaAgenda->estado)) }}</p></div>
                </div>
            @else
                <div class="empty">No tienes próximas horas registradas.</div>
            @endif
        </article>
        <article class="card-home">
            <div class="eyebrow text-primary mb-3">Resumen</div>
            <div class="metrics">
                <div class="metric"><strong>{{ $resumen['bonos_vigentes'] }}</strong><span>Bonos vigentes</span></div>
                <div class="metric"><strong>{{ $resumen['pagados'] }}</strong><span>Pagados online</span></div>
                <div class="metric"><strong>{{ $resumen['pendientes_pago'] }}</strong><span>Pendientes de pago</span></div>
                <div class="metric"><strong>{{ $resumen['en_espera'] }}</strong><span>En sala de espera</span></div>
            </div>
        </article>
    </section>
</main>
</body>
</html>
