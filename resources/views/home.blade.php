<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Medichile · Demo del ecosistema de atención</title>
    <style>
        :root{--blue:#1848a1;--blue-dark:#112f68;--cyan:#31bebe;--green:#087b68;--ink:#1f2d3d;--muted:#607184;--line:#d8e3f0;--soft:#f3f8fc;--white:#fff}
        *{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:radial-gradient(circle at 5% 4%,rgba(49,190,190,.12),transparent 28rem),linear-gradient(180deg,#f8fbff,#edf4ff);color:var(--ink);font-family:Inter,"Segoe UI",system-ui,sans-serif}.wrap{max-width:1540px;margin:auto;padding:34px 24px 64px}.card{background:rgba(255,255,255,.97);border:1px solid rgba(216,227,240,.9);border-radius:24px;box-shadow:0 18px 46px rgba(24,72,161,.08)}.eyebrow{color:var(--green);font-size:12px;font-weight:900;letter-spacing:.17em;text-transform:uppercase}.hero{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(300px,.65fr);gap:34px;padding:42px}.hero h1{max-width:850px;margin:10px 0 16px;font-size:clamp(34px,5vw,62px);line-height:1.02;letter-spacing:-.045em}.hero-lead{max-width:810px;margin:0;color:var(--muted);font-size:19px;line-height:1.65}.hero-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:26px}.button{display:inline-flex;align-items:center;justify-content:center;gap:8px;border:0;border-radius:12px;padding:12px 17px;background:var(--blue);color:#fff;font-family:inherit;font-size:14px;font-weight:800;text-decoration:none;cursor:pointer}.button.secondary{background:#eaf2ff;color:var(--blue)}.trust-box{align-self:stretch;padding:25px;border-radius:19px;background:linear-gradient(145deg,var(--blue-dark),var(--blue) 58%,#258fa4);color:#fff}.trust-box h2{margin:8px 0 13px;font-size:23px}.trust-box p{margin:0 0 18px;color:#dceaff;line-height:1.55}.trust-list{display:grid;gap:11px}.trust-item{display:flex;gap:10px;align-items:flex-start;padding:11px;border:1px solid rgba(255,255,255,.17);border-radius:12px;background:rgba(255,255,255,.08)}.trust-check{display:grid;place-items:center;flex:0 0 25px;height:25px;border-radius:50%;background:var(--cyan);font-weight:950}.section{margin-top:26px}.section-heading{max-width:820px;margin:0 auto 24px;text-align:center}.section-heading h2{margin:8px 0 10px;font-size:clamp(27px,4vw,40px);letter-spacing:-.035em}.section-heading p{margin:0;color:var(--muted);font-size:17px;line-height:1.6}.journey{display:grid;grid-template-columns:repeat(5,1fr);gap:15px}.role{position:relative;display:flex;min-height:455px;flex-direction:column;padding:22px;overflow:hidden}.role:before{content:"";position:absolute;inset:0 0 auto;height:5px;background:var(--accent)}.role-number{display:grid;place-items:center;width:46px;height:46px;border-radius:15px;background:#edf4ff;color:var(--accent);font-size:20px;font-weight:950}.role h3{margin:18px 0 8px;font-size:23px}.role-subtitle{min-height:68px;margin:0 0 18px;color:var(--muted);line-height:1.45}.role ul{display:grid;gap:12px;margin:0 0 24px;padding:0;list-style:none}.role li{position:relative;padding-left:23px;color:#435568;line-height:1.45}.role li:before{content:"✓";position:absolute;left:0;color:var(--accent);font-weight:950}.role .role-action{margin-top:auto}.role form{margin:0}.role .button{width:100%;background:var(--accent)}.role-patient{--accent:#1848a1}.role-totem{--accent:#e07935}.role-assistant{--accent:#218a9d}.role-professional{--accent:#087b68}.role-audit{--accent:#7355a6}.security{display:grid;grid-template-columns:.8fr 1.2fr;gap:24px;padding:34px}.security-intro{padding:10px}.security-intro h2{margin:9px 0 13px;font-size:35px;letter-spacing:-.035em}.security-intro p{color:var(--muted);font-size:16px;line-height:1.65}.security-flow{display:grid;gap:10px}.security-step{display:grid;grid-template-columns:44px 1fr;gap:14px;padding:15px;border:1px solid var(--line);border-radius:15px;background:var(--soft)}.security-step span{display:grid;place-items:center;width:40px;height:40px;border-radius:12px;background:#fff;color:var(--blue);font-weight:950;box-shadow:0 5px 16px rgba(24,72,161,.09)}.security-step h3{margin:1px 0 4px;font-size:16px}.security-step p{margin:0;color:var(--muted);font-size:14px;line-height:1.5}.proof-title{display:flex;align-items:end;justify-content:space-between;gap:20px;margin:30px 0 12px}.proof-title h2{margin:5px 0 0;font-size:30px}.proof-title p{max-width:650px;margin:0;color:var(--muted);line-height:1.5}.stats{display:grid;grid-template-columns:repeat(6,1fr);gap:10px}.stat{padding:15px;border-radius:15px;background:#fff;border:1px solid var(--line)}.stat strong{display:block;color:var(--green);font-size:25px}.stat span{color:var(--muted);font-size:13px;text-transform:capitalize}.evidence{margin-top:12px;overflow:hidden}.evidence summary{padding:19px 22px;cursor:pointer;color:var(--blue);font-weight:900;list-style:none}.evidence summary::-webkit-details-marker{display:none}.evidence summary:after{content:"＋";float:right}.evidence[open] summary:after{content:"−"}.tablebox{overflow:auto;border-top:1px solid var(--line)}table{width:100%;border-collapse:collapse;min-width:960px}th,td{padding:13px;text-align:left;vertical-align:top;border-bottom:1px solid #e5edf5}th{color:#607184;font-size:11px;letter-spacing:.05em;text-transform:uppercase}.badge{display:inline-block;padding:4px 8px;border-radius:999px;background:#e4f4ee;color:#087555;font-size:12px;font-weight:800}.table-action{display:inline-block;margin:2px;padding:6px 9px;border-radius:8px;background:var(--green);color:#fff;text-decoration:none;font-size:12px;font-weight:800}.empty{padding:30px;text-align:center;color:var(--muted)}.note{margin-top:18px;text-align:center;color:var(--muted);font-size:13px}@media(max-width:1250px){.journey{grid-template-columns:repeat(3,1fr)}}@media(max-width:1050px){.hero,.security{grid-template-columns:1fr}.journey{grid-template-columns:repeat(2,1fr)}.stats{grid-template-columns:repeat(3,1fr)}}@media(max-width:680px){.wrap{padding:20px 13px 45px}.hero{padding:26px 20px}.hero h1{font-size:36px}.journey,.stats{grid-template-columns:1fr}.role{min-height:auto}.role-subtitle{min-height:auto}.security{padding:22px}.proof-title{display:block}.proof-title p{margin-top:10px}}
    </style>
</head>
<body>
@include('partials.demo_user_switcher')
<main class="wrap">
    <section class="hero card">
        <div>
            <div class="eyebrow">Ecosistema Medichile · Demostración integral</div>
            <h1>Una atención conectada, segura y trazable de principio a fin</h1>
            <p class="hero-lead">Esta demostración permite recorrer una misma operación desde sus canales y actores principales: el paciente se autogestiona desde la app o el tótem, el asistente acompaña, el profesional atiende y Contraloría verifica antes de liberar el pago.</p>
            <div class="hero-actions">
                <a class="button" href="#recorrido">Explorar el recorrido</a>
                <a class="button secondary" href="#seguridad">Conocer las capas de seguridad</a>
            </div>
        </div>
        <aside class="trust-box">
            <div class="eyebrow" style="color:#9cf2ea">Confianza incorporada al proceso</div>
            <h2 style="color: #fff;">Cada acción deja evidencia</h2>
            <p>La seguridad no aparece sólo al final: acompaña la identidad, la autorización, la prestación y el pago.</p>
            <div class="trust-list">
                <div class="trust-item"><span class="trust-check">✓</span><span>Paciente y dependientes vinculados a un titular responsable.</span></div>
                <div class="trust-item"><span class="trust-check">✓</span><span>Notificaciones y autorizaciones enviadas a la app del paciente.</span></div>
                <div class="trust-item"><span class="trust-check">✓</span><span>QR, estados y decisiones conservados en una trazabilidad auditable.</span></div>
            </div>
        </aside>
    </section>

    <section class="section" id="recorrido">
        <header class="section-heading">
            <div class="eyebrow">Una plataforma · Cinco experiencias conectadas</div>
            <h2>Explore cómo se coordina la atención</h2>
            <p>Cada perfil muestra sólo las tareas que le corresponden, mientras todos participan sobre la misma información y cadena de evidencia.</p>
        </header>
        <div class="journey">
            <article class="role role-patient card">
                <div class="role-number">01</div><h3>Paciente</h3>
                <p class="role-subtitle">Autogestión simple, con control directo sobre sus decisiones.</p>
                <ul><li>Reserva una hora desde su escritorio o desde el tótem.</li><li>Compra y consulta bonos vinculados al beneficiario correcto.</li><li>Recibe en la app solicitudes para aprobar o rechazar operaciones.</li><li>Confirma su llegada sin exponer información innecesaria.</li></ul>
                <div class="role-action"><form method="POST" action="{{ route('demo.switch-user', 'paciente') }}">@csrf<input type="hidden" name="destino" value="paciente.escritorio"><button class="button" type="submit">Ingresar como paciente →</button></form></div>
            </article>
            <article class="role role-totem card">
                <div class="role-number">02</div><h3>Tótem de autoservicio</h3>
                <p class="role-subtitle">Extiende la atención digital al punto físico, sin depender de una sesión personal precargada.</p>
                <ul><li>Permite que cada persona ingrese y valide su propio RUT.</li><li>Reconoce en backend si es titular o dependiente.</li><li>Permite reservar una hora y seleccionar al responsable vigente.</li><li>Registra la llegada y conecta al paciente con la sala de espera.</li></ul>
                <div class="role-action"><form method="POST" action="{{ route('demo.switch-user', 'paciente') }}">@csrf<input type="hidden" name="destino" value="paciente.totem"><button class="button" type="submit">Probar el tótem →</button></form></div>
            </article>
            <article class="role role-assistant card">
                <div class="role-number">03</div><h3>Asistente</h3>
                <p class="role-subtitle">Acompaña el proceso sin reemplazar la voluntad del paciente.</p>
                <ul><li>Identifica al paciente por RUT y reconoce si es dependiente.</li><li>Reserva horas y vende bonos mediante el flujo asistido.</li><li>Envía solicitudes de autorización al titular responsable.</li><li>Recibe al paciente y lo incorpora a la sala de espera.</li></ul>
                <div class="role-action"><form method="POST" action="{{ route('demo.switch-user', 'asistente') }}">@csrf<button class="button" type="submit">Ingresar como asistente →</button></form></div>
            </article>
            <article class="role role-professional card">
                <div class="role-number">04</div><h3>Profesional</h3>
                <p class="role-subtitle">Atiende con contexto clínico y respaldo operacional.</p>
                <ul><li>Visualiza únicamente pacientes que completaron la recepción.</li><li>Relaciona identidad, hora, prestación y bono.</li><li>Registra y cierra la atención realizada.</li><li>Envía el cobro con antecedentes verificables.</li></ul>
                <div class="role-action"><form method="POST" action="{{ route('demo.switch-user', 'profesional') }}">@csrf<input type="hidden" name="destino" value="profesional.escritorio"><button class="button" type="submit">Ingresar como profesional →</button></form></div>
            </article>
            <article class="role role-audit card">
                <div class="role-number">05</div><h3>Contraloría</h3>
                <p class="role-subtitle">Convierte la trazabilidad en control previo al pago.</p>
                <ul><li>Contrasta bono, agenda, atención y solicitud de cobro.</li><li>Revisa autorizaciones y eventos registrados durante el proceso.</li><li>Aprueba, observa o rechaza con una decisión identificable.</li><li>Habilita el depósito sólo después de superar los controles.</li></ul>
                <div class="role-action"><form method="POST" action="{{ route('demo.switch-user', 'contralor') }}">@csrf<button class="button" type="submit">Ingresar a Contraloría →</button></form></div>
            </article>
        </div>
    </section>

    <section class="section security card" id="seguridad">
        <div class="security-intro">
            <div class="eyebrow">Seguridad por diseño</div><h2>La autorización correcta, en el momento correcto</h2>
            <p>El objetivo no es agregar fricción, sino asegurar que una acción sensible pueda atribuirse, verificarse y auditarse. En la demo, la app autorizadora representa el canal privado mediante el cual el paciente o titular conserva el control.</p>
            <p><strong>Ejemplo:</strong> si una asistente inicia una compra para un dependiente, el sistema identifica al titular vigente y envía la solicitud a su app. La operación continúa únicamente con una respuesta registrada.</p>
        </div>
        <div class="security-flow">
            <div class="security-step"><span>1</span><div><h3>Identidad y relación</h3><p>El tótem no precarga usuarios: cada RUT se valida y el backend determina si corresponde a un titular o a un dependiente con responsable vigente.</p></div></div>
            <div class="security-step"><span>2</span><div><h3>Solicitud contextual</h3><p>La app recibe los datos relevantes de la operación: beneficiario, prestación, profesional y monto cuando corresponde.</p></div></div>
            <div class="security-step"><span>3</span><div><h3>Consentimiento explícito</h3><p>La aprobación o el rechazo quedan asociados a la solicitud, al momento y al canal desde el cual se respondió.</p></div></div>
            <div class="security-step"><span>4</span><div><h3>Trazabilidad y control</h3><p>Recepción, atención, auditoría y depósito avanzan mediante estados controlados y quedan disponibles para revisión.</p></div></div>
        </div>
    </section>

    <section class="section" id="evidencia">
        <div class="proof-title"><div><div class="eyebrow">Demo en vivo</div><h2>Indicadores del recorrido</h2></div><p>Estos indicadores cambian a medida que se opera desde los distintos perfiles. El detalle de pagos y respaldos QR se consulta desde el Historial de pagos del profesional.</p></div>
        <div class="stats">@foreach($resumen as $nombre => $valor)<div class="stat"><strong>{{ $valor }}</strong><span>{{ str_replace('_',' ',ucfirst($nombre)) }}</span></div>@endforeach</div>
        <p class="note">Entorno demostrativo: las integraciones y respuestas de autorización están simuladas para presentar el circuito completo de forma segura.</p>
    </section>
</main>
</body>
</html>
