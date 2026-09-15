<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trazabilidad de bono | Medichile Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#edf4ff; color:#1f2d3d; }
        .card-soft { background:#fff; border:1px solid #d6e1f1; border-radius:22px; box-shadow:0 18px 45px rgba(24,72,161,.08); }
        .small-muted { color:#7b8798; font-size:.86rem; }
        .trace-hero { background:linear-gradient(135deg,#173f91 0%,#2368b7 55%,#28b9bb 100%); color:#fff; overflow:hidden; }
        .trace-hero h2 { color:#fff; }
        .trace-search { background:rgba(255,255,255,.98); border-radius:16px; padding:.45rem; }
        .trace-search .form-control { border:0; box-shadow:none; min-height:50px; }
        .trace-search .btn { border-radius:12px; min-width:190px; }
        .trace-example { color:#dcecff; font-size:.83rem; }
        .trace-summary { background:#f4f8ff; border:1px solid #d8e5f7; border-radius:16px; }
        .trace-status { background:#ddf8ef; color:#087659; border-radius:999px; font-weight:800; padding:.45rem .8rem; }
        .trace-event { display:grid; grid-template-columns:120px 34px minmax(0,1fr); gap:0 1rem; position:relative; }
        .trace-event:not(:last-child)::before { background:#cbdcf2; content:""; left:136px; position:absolute; top:29px; bottom:-12px; width:2px; }
        .trace-time { color:#6f7f95; font-size:.82rem; padding-top:.25rem; text-align:right; }
        .trace-dot { align-items:center; background:#fff; border:3px solid #1eaeaa; border-radius:50%; color:#13847f; display:flex; font-size:.75rem; font-weight:900; height:28px; justify-content:center; position:relative; width:28px; z-index:1; }
        .trace-content { background:#fff; border:1px solid #dce6f4; border-radius:14px; margin-bottom:12px; padding:1rem; }
        .trace-actor { border-radius:999px; display:inline-block; font-size:.7rem; font-weight:800; letter-spacing:.03em; padding:.28rem .55rem; text-transform:uppercase; }
        .actor-paciente { background:#e7efff; color:#194fae; }
        .actor-asistente { background:#e3f7fa; color:#147c8c; }
        .actor-profesional { background:#dff7ef; color:#087b5e; }
        .actor-sistema { background:#eef0f4; color:#4c5665; }
        .actor-contraloria { background:#f0e8fb; color:#6f45a6; }
        .trace-evidence { color:#66768c; font-size:.82rem; }
        @media (max-width:767.98px) {
            .trace-search { display:block!important; }
            .trace-search .btn { min-width:100%; }
            .trace-event { grid-template-columns:28px minmax(0,1fr); gap:0 .75rem; }
            .trace-event:not(:last-child)::before { left:13px; }
            .trace-time { grid-column:2; padding:0 0 .35rem; text-align:left; }
            .trace-dot { grid-column:1; grid-row:1 / span 2; }
            .trace-content { grid-column:2; }
        }
    </style>
</head>
<body>
@include('partials.demo_user_switcher')

<main class="container-fluid py-4 px-3 px-md-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <div class="text-uppercase small fw-bold text-success">Contraloría · visión transversal</div>
            <h1 class="fw-bold mb-1">Trazabilidad de bono</h1>
            <p class="text-muted mb-0">Reconstrucción demostrativa del recorrido completo de una atención.</p>
        </div>
    </div>

    <section class="card card-soft trace-hero p-4 p-lg-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-xl-5">
                <div class="text-uppercase small fw-bold text-info mb-2">Trazabilidad integral</div>
                <h2 class="fw-bold mb-3">Buscar traza de bono</h2>
                <p class="mb-0 text-white-50">Consulta por bono, expediente o paciente y visualiza cómo todos los módulos contribuyeron a una misma historia.</p>
            </div>
            <div class="col-xl-7">
                <form id="traceSearchForm" class="trace-search d-flex gap-2" novalidate>
                    <input id="traceSearchInput" class="form-control form-control-lg" value="BONO-EXT-260914-P9MBKSR" placeholder="Número de bono, expediente o paciente" aria-label="Número de bono, expediente o paciente">
                    <button class="btn btn-primary fw-bold" type="submit">Reconstruir historia</button>
                </form>
                <div class="trace-example mt-2 px-2">Ejemplo: BONO-EXT-260914-P9MBKSR · EXP-2026-11407 · Francisco Rojo</div>
            </div>
        </div>
    </section>

    <section id="traceResult" class="card card-soft p-3 p-lg-4" aria-live="polite">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
            <div><div class="text-uppercase small fw-bold text-success">Expediente reconstruido</div><h3 class="fw-bold mb-1" id="traceTitle">BONO-EXT-260914-P9MBKSR</h3><div class="small-muted">Expediente EXP-2026-11407 · Consulta médica electiva</div></div>
            <div class="d-flex flex-wrap align-items-center gap-2"><span class="trace-status">✓ Trazabilidad íntegra</span><span class="badge bg-light text-dark border px-3 py-2">7 eventos conectados</span></div>
        </div>

        <div class="trace-summary p-3 mb-4"><div class="row g-3">
            <div class="col-sm-6 col-xl-3"><div class="small-muted">Paciente</div><strong id="tracePatient">Francisco Rojo Gallardo</strong></div>
            <div class="col-sm-6 col-xl-3"><div class="small-muted">Profesional</div><strong>Jaime Kriman Astorga</strong></div>
            <div class="col-sm-6 col-xl-3"><div class="small-muted">Atención</div><strong>28-09-2026 · 08:00</strong></div>
            <div class="col-sm-6 col-xl-3"><div class="small-muted">Estado final</div><strong class="text-success">Pago autorizado</strong></div>
        </div></div>

        <div class="trace-timeline">
            @php
                $eventos = [
                    ['14 sep · 20:38', 'Paciente', 'paciente', 'Compra y emisión del bono', 'Se confirma la transacción y el bono queda asociado al beneficiario y a la prestación.', 'Transacción aprobada · monto $40.000 · comprobante emitido'],
                    ['14 sep · 20:39', 'Sistema', 'sistema', 'Reserva vinculada y QR seguro generado', 'La agenda, el profesional y el bono quedan relacionados bajo un identificador verificable.', 'Hora Med-SDI #11407 · firma validada · vigencia controlada'],
                    ['28 sep · 07:51', 'Paciente', 'paciente', 'Llegada confirmada desde el tótem', 'El paciente se identifica y confirma su presencia sin exponer información clínica innecesaria.', 'Identidad validada · reserva vigente · canal tótem'],
                    ['28 sep · 07:53', 'Asistente', 'asistente', 'Recepción y sala de espera', 'Recepción reconoce la reserva y notifica al profesional que el paciente está disponible.', 'Usuario receptor identificado · cambio de estado registrado'],
                    ['28 sep · 08:34', 'Profesional', 'profesional', 'Atención realizada y cerrada', 'El profesional confirma la prestación efectivamente realizada y cierra la atención.', 'Profesional autenticado · prestación coincidente · cierre firmado'],
                    ['28 sep · 08:36', 'Sistema', 'sistema', 'Cobro enviado con controles automáticos', 'Se contrastan bono, paciente, agenda, atención, monto y vigencia.', '6 de 6 controles conformes · sin contradicciones'],
                    ['28 sep · 09:02', 'Contraloría', 'contraloria', 'Visto bueno y autorización de pago', 'Contraloría revisa la cadena de evidencia y autoriza el pago al profesional.', 'Decisión identificable · cuenta previamente autorizada en app'],
                ];
            @endphp
            @foreach($eventos as $indice => $evento)
                <article class="trace-event">
                    <div class="trace-time">{{ $evento[0] }}</div><div class="trace-dot">{{ $indice + 1 }}</div>
                    <div class="trace-content"><span class="trace-actor actor-{{ $evento[2] }}">{{ $evento[1] }}</span><strong class="d-block mt-2">{{ $evento[3] }}</strong><p class="mb-1">{{ $evento[4] }}</p><div class="trace-evidence">Evidencia: {{ $evento[5] }}</div></div>
                </article>
            @endforeach
        </div>

        <div class="alert alert-success mb-0 mt-2"><strong>Historia consistente:</strong> los siete eventos conservan la relación entre el mismo paciente, bono, hora, profesional y expediente de pago.</div>
    </section>
</main>

<script>
    (() => {
        const form = document.getElementById('traceSearchForm');
        const input = document.getElementById('traceSearchInput');
        const result = document.getElementById('traceResult');
        const title = document.getElementById('traceTitle');
        const patient = document.getElementById('tracePatient');
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            const query = input.value.trim();
            if (!query) { input.classList.add('is-invalid'); input.focus(); return; }
            input.classList.remove('is-invalid');
            const normalized = query.toUpperCase();
            const patientSearch = !normalized.startsWith('BONO') && !normalized.startsWith('EXP');
            title.textContent = normalized.startsWith('BONO') ? normalized : 'BONO-EXT-260914-P9MBKSR';
            patient.textContent = patientSearch ? query : 'Francisco Rojo Gallardo';
            result.scrollIntoView({ behavior:'smooth', block:'start' });
        });
    })();
</script>
</body>
</html>
