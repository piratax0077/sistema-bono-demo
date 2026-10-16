@php
    $demoStep = (int) ($demoStep ?? 1);
    $demoProfessional = (bool) ($demoProfessional ?? false);
    $demoAssistant = (bool) ($demoAssistant ?? false);
    $demoAudit = (bool) ($demoAudit ?? false);
    $demoInteractive = (bool) ($demoInteractive ?? (auth()->user()?->rol === 'cliente'));
    $demoGuideInformative = (bool) ($demoGuideInformative ?? false) && ! $demoInteractive;
    $demoSteps = [
        1 => ['icon' => '📅', 'title' => 'Reservar', 'text' => 'Elegir médico, centro, fecha y hora.', 'security' => 'Identidad y RUT validados; convenio y relación paciente–médico–prestación verificados. Agenda bloqueada al confirmar para evitar doble reserva. El RUT, dirección y datos sernsibles del bono se guarda encriptado.'],
        2 => ['icon' => '🎫', 'title' => 'Bono y QR', 'text' => 'Pagar el copago y emitir el bono digital.', 'security' => 'Autorización del paciente por App o WhatsApp, simulada en la demo. Token único con vencimiento y firma HMAC para detectar alteraciones. Se comprueba el estado del bono para evitar reutilizarlo.'],
        3 => ['icon' => '✓', 'title' => 'Aviso de llegada al centro de salud', 'text' => 'Reconocer automáticamente al paciente y su hora.', 'security' => 'Se relacionan paciente, bono, médico y hora. El bono queda disponible para recepción; el QR es respaldo en tótem, secretaría o agenda online. Validación del estado y límites de intentos.'],
        4 => ['icon' => '🪑', 'title' => 'Sala de espera', 'text' => 'Informar al médico que el paciente está presente.', 'security' => 'Llegada vinculada a la reserva y registrada con trazabilidad. Los cambios se procesan de forma coordinada para evitar duplicados. El profesional consulta la atención asociada a su agenda.'],
        5 => ['icon' => '🩺', 'title' => 'Atención', 'text' => 'El médico llama, atiende y registra el cierre.', 'security' => 'Acceso por rol e identidad profesional. Se registra la atención y su cierre; el circuito continúa con auditoría, autorización del cobro y registro del depósito para seguir cada movimiento.'],
    ];
    if ($demoProfessional) {
        $demoSteps = [
            1 => ['icon' => '📅', 'title' => 'Revisar la agenda', 'text' => 'Consulta los pacientes agendados que confirmaron su llegada y están en sala de espera, con el bono asociado a su atención.', 'security' => 'El sistema valida la identidad del paciente y relaciona la reserva con el profesional, la prestación y el lugar de atención. La comunicación por API mantiene el bono disponible en la agenda.'],
            2 => ['icon' => '🩺', 'title' => 'Atender y cerrar la consulta', 'text' => 'Llama al paciente, realiza la atención y registra el cierre de la consulta. El cierre deja el bono disponible para continuar al proceso de cobro.', 'security' => 'La sesión identifica al profesional. El registro vincula paciente, profesional, hora, prestación y bono; conserva evidencia de la atención y su cierre para la revisión de contraloría.'],
            3 => ['icon' => '💳', 'title' => 'Gestionar el cobro del bono', 'text' => 'El bono con QR dinámico pasa al escritorio de cobros. El profesional revisa los bonos, la frecuencia de cobro y la cuenta registrada para el depósito.', 'security' => 'La autenticación y la revisión de paciente atendido, profesional, lugar y cierre respaldan el cobro. Contraloría revisa los bonos enviados; las incidencias pasan a auditoría. El registro digital evita extravíos y permite seguir la autorización y el depósito.'],
        ];
    }
    if ($demoAssistant) {
        $demoSteps = $assistantSteps;
    }
    $demoTotal = count($demoSteps);
    $demoCompleted = ($demoInteractive || $demoGuideInformative) ? 0 : max(0, min($demoTotal, $demoStep - 1));
    $demoViewed = $demoInteractive ? 1 : max(1, min($demoTotal, $demoStep));
    $demoInitialProgress = (int) round($demoCompleted / max(1, $demoTotal) * 100);
@endphp
<style>
    .demo-guide{margin:24px 0;padding:22px 24px;border:1px solid #cbdaf0;border-radius:22px;background:#fff;box-shadow:0 12px 32px rgba(24,72,161,.07);font-family:'Manrope',sans-serif;color:#18304f}
    .demo-guide *{font-family:inherit;box-sizing:border-box}
    .demo-guide-head{display:flex;justify-content:space-between;gap:20px;align-items:center;margin-bottom:12px}
    .demo-guide-head h2{margin:0;color:#18304f;font-size:21px;font-weight:800;letter-spacing:-.01em}
    .demo-guide-head p{margin:3px 0 0;color:#65758b;font-size:14.5px;line-height:1.45}
    .demo-guide-meter{display:grid;gap:7px;justify-items:end;min-width:170px}
    .demo-guide-progress{font-size:13px;font-weight:800;color:#1848a1;background:#edf4ff;border-radius:999px;padding:7px 13px;white-space:nowrap;transition:transform .25s ease}
    .demo-guide-progress.progress-bump{transform:scale(1.05)}
    .demo-guide-bar{width:100%;height:5px;border-radius:999px;background:#e7edf6;overflow:hidden}
    .demo-guide-bar span{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,#2dbabb,#16886c);transition:width .45s ease}
    .demo-guide-body{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(320px,1fr);gap:24px;align-items:start}
    .demo-guide-carousel{container-type:inline-size;min-width:0;border-radius:18px;outline:none}
    .demo-guide-carousel:focus-visible{outline:3px solid #1848a1;outline-offset:4px}
    .demo-guide-route{display:flex;margin:0 0 16px;padding:0;list-style:none}
    .demo-guide-stop{position:relative;flex:1 1 0;min-width:0}
    .demo-guide-stop:not(:first-child):before{content:'';position:absolute;top:17px;right:50%;width:100%;height:3px;border-radius:3px;background:#dbe5f2;transition:background .35s}
    .demo-guide-stop.current:before{background:#2dbabb}
    .demo-guide-stop.done:before{background:#59c68e}
    .demo-guide-stop-btn{position:relative;z-index:1;display:flex;flex-direction:column;align-items:center;gap:7px;width:100%;padding:0 4px;border:0;background:none;color:#65758b;font-size:12.5px;font-weight:700;line-height:1.3;text-align:center;cursor:pointer}
    .demo-guide-stop-btn:focus-visible{outline:none}
    .demo-guide-stop-btn:focus-visible .demo-guide-stop-dot{outline:3px solid #1848a1;outline-offset:3px}
    .demo-guide-stop-dot{display:grid;place-items:center;width:36px;height:36px;border:2px solid #d7e2f2;border-radius:50%;background:#fff;color:#65758b;font-size:14px;font-weight:800;transition:background .25s,border-color .25s,color .25s,box-shadow .25s}
    .demo-guide-stop:not(.current):not(.done) .demo-guide-stop-btn:hover .demo-guide-stop-dot{border-color:#1848a1;color:#1848a1}
    .demo-guide-stop.current .demo-guide-stop-dot{border-color:#2dbabb;background:#2dbabb;color:#fff}
    .demo-guide-stop.done .demo-guide-stop-dot{border-color:#16886c;background:#16886c;color:#fff}
    .demo-guide-stop.done .demo-guide-stop-dot span{display:none}
    .demo-guide-stop.done .demo-guide-stop-dot:after{content:'✓';font-weight:900}
    .demo-guide-stop.is-viewed .demo-guide-stop-dot{box-shadow:0 0 0 5px rgba(45,186,187,.2)}
    .demo-guide-stop.done.is-viewed .demo-guide-stop-dot{box-shadow:0 0 0 5px rgba(22,136,108,.18)}
    .demo-guide-stop.is-viewed .demo-guide-stop-label{color:#18304f;font-weight:800}
    .demo-guide-stop-label{display:block;max-width:150px}
    .demo-guide-viewport{overflow:hidden;border-radius:18px;touch-action:pan-y}
    .demo-guide-track{display:flex;gap:16px;align-items:stretch}
    .demo-guide.is-ready .demo-guide-track{transition:transform .55s cubic-bezier(.22,1,.36,1)}
    .demo-guide-slide{flex:0 0 100%;min-width:0;display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.05fr);gap:18px;align-items:start;padding:20px;border:1px solid #dfe8f4;border-radius:18px;background:#f8fbff;transition:border-color .3s,background .3s}
    .demo-guide-slide.current{border-color:#a6e2e2;background:linear-gradient(135deg,#ebfafa 0%,#f8fbff 75%)}
    .demo-guide-slide.done{border-color:#bfe9d3;background:linear-gradient(135deg,#eefaf4 0%,#f8fbff 75%)}
    .demo-guide-slide-main{display:grid;grid-template-columns:52px minmax(0,1fr);gap:14px;align-items:start}
    .demo-guide-icon{display:grid;place-items:center;width:52px;height:52px;border:1px solid #e3ebf6;border-radius:15px;background:#fff;box-shadow:0 6px 14px rgba(24,72,161,.08);font-size:26px;line-height:1}
    .demo-guide-status{display:inline-flex;margin-bottom:7px;border-radius:999px;padding:3px 9px;background:#e7edf6;color:#52657e;font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
    .current .demo-guide-status{background:#13898a;color:#fff}
    .done .demo-guide-status{background:#c8f0dd;color:#096b51}
    .demo-guide-slide h3{margin:0;color:#18304f;font-size:20px;font-weight:800;line-height:1.3;letter-spacing:-.01em}
    .demo-guide-text{margin:6px 0 0;color:#40546d;font-size:15px;line-height:1.5}
    .demo-guide-security{padding:14px 16px;border:1px solid #e1eaf6;border-radius:14px;background:#fff;color:#4a6382;font-size:13.5px;line-height:1.55}
    .demo-guide-security p{margin:0}
    .demo-guide-security-label{display:flex;gap:6px;align-items:center;margin-bottom:5px;color:#1848a1;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
    .demo-guide-security-label svg{width:14px;height:14px}
    @container (max-width:620px){.demo-guide-slide{grid-template-columns:1fr;gap:14px}}
    .demo-guide-nav{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:14px}
    .demo-guide-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;padding:10px 18px;border-radius:12px;font-size:14px;font-weight:700;line-height:1;cursor:pointer;transition:background .2s,border-color .2s,color .2s,opacity .2s,box-shadow .2s}
    .demo-guide-btn svg{width:18px;height:18px;flex:0 0 auto}
    .demo-guide-prev{border:1px solid #cbdaf0;background:#fff;color:#1848a1}
    .demo-guide-prev:hover:not(:disabled){border-color:#1848a1;background:#f3f7ff}
    .demo-guide-next{border:1px solid #1848a1;background:#1848a1;color:#fff;box-shadow:0 8px 18px rgba(24,72,161,.2)}
    .demo-guide-next:hover:not(:disabled){border-color:#123b86;background:#123b86}
    .demo-guide-btn:disabled{opacity:.4;cursor:default;box-shadow:none}
    .demo-guide-btn:focus-visible{outline:3px solid #2dbabb;outline-offset:2px}
    .demo-guide-aside{display:grid;gap:8px}
    .demo-guide-aside-title{display:flex;gap:7px;align-items:center;margin:0;color:#1848a1;font-size:11.5px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
    .demo-guide-aside-title svg{width:15px;height:15px}
    .demo-security-note{display:grid;grid-template-columns:34px minmax(0,1fr);gap:11px;align-items:start;margin:0;padding:11px 13px;border:1px solid #d9e6fb;border-radius:13px;background:#eef4ff;color:#294d78;font-size:13px;line-height:1.5}
    .demo-security-note strong{color:#1848a1;font-weight:800}
    .demo-security-badge{display:grid;place-items:center;width:34px;height:34px;border-radius:10px;background:#fff;box-shadow:0 2px 6px rgba(24,72,161,.08);font-size:18px;line-height:1}
    .demo-security-icon{display:block;width:24px;height:24px}
    @keyframes demoDoneCheck{0%{transform:scale(.6);opacity:0}100%{transform:scale(1);opacity:1}}
    .demo-guide-stop.done-enter .demo-guide-stop-dot:after{display:inline-block;animation:demoDoneCheck .4s ease-out}
    @media(min-width:1181px) and (max-height:820px){.demo-guide{padding:18px 22px}.demo-guide-head{margin-bottom:10px}.demo-guide-route{margin-bottom:12px}.demo-guide-slide{padding:16px}.demo-guide-nav{margin-top:12px}.demo-guide-aside{gap:7px}.demo-security-note{gap:10px;padding:9px 12px;font-size:12.5px;line-height:1.45}.demo-security-badge{width:30px;height:30px;font-size:16px}.demo-security-note{grid-template-columns:30px minmax(0,1fr)}.demo-security-icon{width:21px;height:21px}}
    @media(max-width:1180px){.demo-guide-body{grid-template-columns:1fr;gap:20px}.demo-guide-aside{grid-template-columns:repeat(2,minmax(0,1fr))}.demo-guide-aside-title{grid-column:1/-1}}
    @media(max-width:760px){.demo-guide{padding:18px}.demo-guide-head{flex-direction:column;align-items:flex-start;gap:12px}.demo-guide-meter{justify-items:start;width:100%;min-width:0}.demo-guide-aside{grid-template-columns:1fr}}
    @media(max-width:600px){.demo-guide-stop-label{display:none}.demo-guide-stop-dot{width:32px;height:32px;font-size:13px}.demo-guide-stop:not(:first-child):before{top:15px}.demo-guide-slide{padding:16px}.demo-guide-slide-main{grid-template-columns:44px minmax(0,1fr)}.demo-guide-icon{width:44px;height:44px;border-radius:13px;font-size:22px}.demo-guide-slide h3{font-size:18px}.demo-guide-text{font-size:14px}}
    @media(max-width:420px){.demo-guide{padding:15px;border-radius:18px}.demo-guide-head h2{font-size:19px}.demo-guide-btn{padding:10px 13px;font-size:13.5px}.demo-security-note{padding:11px}}
    @media(prefers-reduced-motion:reduce){.demo-guide *,.demo-guide *:before,.demo-guide *:after{animation:none!important;transition:none!important}}
</style>
<section class="demo-guide {{ $demoProfessional ? 'professional-guide' : '' }} {{ $demoAssistant ? 'assistant-guide' : '' }}" aria-label="Recorrido de atención Medichile" data-interactive="{{ $demoInteractive ? 'true' : 'false' }}" data-informative="{{ $demoGuideInformative ? 'true' : 'false' }}" data-completed="{{ $demoCompleted }}" data-viewed="{{ $demoViewed }}" data-patient-id="{{ auth()->id() }}" data-storage-scope="{{ $demoAssistant ? 'assistant-'.request()->route()->getName() : ($demoProfessional ? 'professional' : '') }}">
    <div class="demo-guide-head">
        <div><h2>{{ $demoAssistant ? $assistantTitle : ($demoProfessional ? 'Recorrido del profesional: de la agenda al cobro' : 'Tu recorrido de atención') }}</h2><p>{{ $demoAudit ? 'Revisión de evidencias, decisiones independientes y seguimiento de cada operación.' : ($demoAssistant ? 'Secretaría: identidad, autorización, lugar de atención y trazabilidad en cada paso.' : ($demoProfessional ? 'Tres etapas vinculadas: paciente en espera, atención cerrada y bono enviado a revisión para su cobro.' : 'Cada etapa conserva la relación entre paciente, médico, hora y bono.')) }}</p></div>
        @unless($demoGuideInformative)
        <div class="demo-guide-meter">
            <span class="demo-guide-progress" aria-live="polite">Etapa {{ $demoInteractive ? 1 : $demoStep }} de {{ $demoTotal }}</span>
            <span class="demo-guide-bar" aria-hidden="true"><span style="width:{{ $demoInitialProgress }}%"></span></span>
        </div>
        @endunless
    </div>
    <div class="demo-guide-body">
        <div class="demo-guide-carousel" role="region" aria-roledescription="carrusel" aria-label="Etapas del recorrido" tabindex="0">
            <ol class="demo-guide-route">
                @foreach($demoSteps as $number => $step)
                    <li class="demo-guide-stop {{ ! $demoGuideInformative ? ($number <= $demoCompleted ? 'done' : ($number === $demoCompleted + 1 ? 'current' : '')) : '' }} {{ $number === $demoViewed ? 'is-viewed' : '' }}">
                        <button type="button" class="demo-guide-stop-btn" aria-label="Etapa {{ $number }}: {{ $step['title'] }}" aria-current="{{ $number === $demoViewed ? 'step' : 'false' }}">
                            <span class="demo-guide-stop-dot" aria-hidden="true"><span>{{ $number }}</span></span>
                            <span class="demo-guide-stop-label" aria-hidden="true">{{ $step['title'] }}</span>
                        </button>
                    </li>
                @endforeach
            </ol>
            <div class="demo-guide-viewport">
                <div class="demo-guide-track" style="transform:translateX(calc({{ 1 - $demoViewed }} * (100% + 16px)))">
                    @foreach($demoSteps as $number => $step)
                        <article class="demo-guide-slide {{ ! $demoGuideInformative ? ($number <= $demoCompleted ? 'done' : ($number === $demoCompleted + 1 ? 'current' : '')) : '' }}" role="group" aria-roledescription="etapa" aria-label="{{ $number }} de {{ $demoTotal }}" aria-hidden="{{ $number === $demoViewed ? 'false' : 'true' }}" @if($number !== $demoViewed) inert @endif>
                            <div class="demo-guide-slide-main">
                                <span class="demo-guide-icon" aria-hidden="true">{{ $step['icon'] }}</span>
                                <div>
                                    @unless($demoGuideInformative)
                                    <span class="demo-guide-status">{{ $demoInteractive ? ($number === 1 ? 'Etapa 1' : 'Siguiente') : ($number < $demoStep ? 'Completado' : ($number === $demoStep ? 'Etapa '.$number : 'Siguiente')) }}</span>
                                    @endunless
                                    <h3>{{ $number }}. {{ $step['title'] }}</h3>
                                    <p class="demo-guide-text">{{ $step['text'] }}</p>
                                </div>
                            </div>
                            <div class="demo-guide-security"><span class="demo-guide-security-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v5c0 4.5-3 8.2-7 10-4-1.8-7-5.5-7-10V6l7-3z"/><path d="M9 12l2 2 4-4"/></svg>Seguridad:</span><p>{{ $step['security'] }}</p></div>
                        </article>
                    @endforeach
                </div>
            </div>
            <div class="demo-guide-nav">
                <button type="button" class="demo-guide-btn demo-guide-prev" @if($demoViewed === 1) disabled @endif><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>Anterior</button>
                <button type="button" class="demo-guide-btn demo-guide-next" @if(! $demoInteractive && $demoViewed === $demoTotal) disabled @endif><span class="demo-guide-next-text">{{ $demoInteractive ? 'Completar etapa 1' : 'Siguiente' }}</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg></button>
            </div>
        </div>
        <aside class="demo-guide-aside" aria-label="Seguridad y reglas del recorrido">
            <p class="demo-guide-aside-title"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v5c0 4.5-3 8.2-7 10-4-1.8-7-5.5-7-10V6l7-3z"/><path d="M9 12l2 2 4-4"/></svg>Seguridad y reglas del recorrido</p>
            <div class="demo-security-note"><span class="demo-security-badge"><img src="{{ asset('images/iconos/candado-datos.svg') }}" alt="" class="demo-security-icon"></span><div><strong>Datos encriptados:</strong> el RUT del bono y los campos protegidos de dirección y fecha de nacimiento se almacenan cifrados. Las verificaciones usan huellas digitales de los datos; la firma HMAC permite detectar alteraciones del bono.</div></div>
            @if($demoAudit)
            <div class="demo-security-note"><span class="demo-security-badge" aria-hidden="true">🛡️</span><div><strong>Revisión independiente:</strong> contraloría contrasta identidad, autorización, bono, prestación, lugar y cierre de la atención. Una alerta o una ubicación por sí sola no demuestra fraude: las inconsistencias requieren revisión y una decisión fundada.</div></div>
            <div class="demo-security-note"><span class="demo-security-badge" aria-hidden="true">🧾</span><div><strong>Decisiones trazables:</strong> cada resolución debe conservar responsable, fecha, motivo y evidencias. La revisión del bono y la autorización del pago son decisiones diferenciadas. Estas tarjetas son explicativas: recorrerlas no aprueba cobros, no resuelve alertas ni marca notificaciones como leídas.</div></div>
            @elseif($demoAssistant)
            <div class="demo-security-note"><span class="demo-security-badge" aria-hidden="true">📍</span><div><strong>Verificación y envío de geolocalización:</strong> el circuito presentado solicita permiso para obtener la ubicación, revisa su precisión y correspondencia con el centro de salud y la vincula a paciente, profesional, bono, fecha y hora. La evidencia se envía al registro de la atención para su revisión. Si no hay permiso, señal suficiente o coincidencia con el centro, queda pendiente de verificación. La ubicación es un dato de apoyo, no acredita por sí sola que la consulta se realizó. Estas tarjetas explican el control; no capturan ni envían ubicaciones.</div></div>
            <div class="demo-security-note"><span class="demo-security-badge" aria-hidden="true">🛡️</span><div><strong>Responsabilidades separadas:</strong> la secretaría verifica y registra; el paciente autoriza desde su teléfono; el profesional atiende y cierra la consulta. Contraloría revisa las evidencias y las incidencias pasan a auditoría antes de autorizar el cobro. Cada acción conserva su responsable.</div></div>
            @elseif($demoProfessional)
            <div class="demo-security-note"><span class="demo-security-badge" aria-hidden="true">🛡️</span><div><strong>Control antifraude del cobro:</strong> el profesional registra la atención efectivamente realizada y cierra la consulta. Los bonos enviados a cobro quedan vinculados a esa evidencia para su revisión por contraloría; las incidencias pasan a auditoría. La autorización del pago corresponde a un rol independiente.</div></div>
            <div class="demo-security-note"><span class="demo-security-badge" aria-hidden="true">💳</span><div><strong>Cobro y depósito trazables:</strong> el escritorio reúne los bonos digitales y la configuración de frecuencia de cobro y cuenta de depósito. La demostración explica el envío, la revisión, la autorización y el depósito mediante operaciones simuladas.</div></div>
            @else
            <div class="demo-security-note"><span class="demo-security-badge"><img src="{{ asset('images/iconos/telefono-autorizacion.svg') }}" alt="" class="demo-security-icon"></span><div><strong>Primer control antifraude: decide el paciente.</strong> Puede aprobar o rechazar la autorización por App o WhatsApp. En el rechazo, el circuito demo definido informa: «Usuario rechazó el bono. Dinero transferido a la cuenta registrada por el paciente», y lo refleja como dinero disponible en Saldos. La aprobación, los mensajes y la transferencia se simulan en la demostración.</div></div>
            @endif
            <div class="demo-security-note"><span class="demo-security-badge"><img src="{{ asset('images/iconos/escudo-reglas.svg') }}" alt="" class="demo-security-icon"></span><div><strong>Reglas definidas para esta demostración:</strong> profesional jkriman@gmail.com y agenda particular del lugar 69; pacientes de prueba y secretarías configurados por el profesional; una hora por paciente en el mismo día. Estas reglas mantienen el recorrido de prueba asociado a la agenda indicada.</div></div>
            <div class="demo-security-note"><span class="demo-security-badge"><img src="{{ asset('images/iconos/candado-proteccion.svg') }}" alt="" class="demo-security-icon"></span><div><strong>Protección transversal:</strong> sesión autenticada, verificaciones de autorización, protección CSRF contra solicitudes ajenas a la sesión, límites de intentos y permisos por rol: paciente, asistente, profesional, auditoría y administración. La auditoría registra fecha, usuario e IP para seguir reserva, consentimiento, bono, llegada, atención y cobro. El ingreso automático y los botones de autorización corresponden al modo demo.</div></div>
        </aside>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.demo-guide:not([data-carousel-ready])').forEach(guide => {
        guide.dataset.carouselReady = 'true';
        const slides = [...guide.querySelectorAll('.demo-guide-slide')];
        const stops = [...guide.querySelectorAll('.demo-guide-stop')];
        const track = guide.querySelector('.demo-guide-track');
        const viewport = guide.querySelector('.demo-guide-viewport');
        const carousel = guide.querySelector('.demo-guide-carousel');
        const prev = guide.querySelector('.demo-guide-prev');
        const next = guide.querySelector('.demo-guide-next');
        const nextText = guide.querySelector('.demo-guide-next-text');
        const progress = guide.querySelector('.demo-guide-progress');
        const bar = guide.querySelector('.demo-guide-bar span');
        const interactive = guide.dataset.interactive === 'true';
        const informative = guide.dataset.informative === 'true';
        const total = slides.length;
        const stepLabel = guide.dataset.stepLabel || 'Etapa';
        const key = 'medichile-demo-guide-patient-' + guide.dataset.patientId + (guide.dataset.storageScope ? '-' + guide.dataset.storageScope : '');
        let completed = Math.min(total, Math.max(0, Number(guide.dataset.completed) || 0));
        if (interactive) {
            try { completed = Math.min(total, Math.max(0, Math.floor(Number(sessionStorage.getItem(key)) || 0))); } catch (_) {}
        }
        let viewed = interactive ? Math.min(completed + 1, total) : Math.min(total, Math.max(1, Number(guide.dataset.viewed) || 1));
        const replay = (el, className) => {
            el.classList.remove(className);
            void el.offsetWidth;
            el.classList.add(className);
            el.addEventListener('animationend', () => el.classList.remove(className), { once: true });
        };
        const bump = () => {
            if (!progress || !interactive) return;
            progress.classList.remove('progress-bump');
            void progress.offsetWidth;
            progress.classList.add('progress-bump');
            setTimeout(() => progress.classList.remove('progress-bump'), 300);
        };
        const save = () => { try { sessionStorage.setItem(key, String(completed)); } catch (_) {} };
        const canAdvance = () => interactive ? completed < total : viewed < total;
        const render = () => {
            slides.forEach((slide, index) => {
                const number = index + 1;
                const done = !informative && number <= completed;
                const current = !informative && number === completed + 1;
                const active = number === viewed;
                [slide, stops[index]].forEach(el => {
                    el.classList.toggle('done', done);
                    el.classList.toggle('current', current);
                });
                slide.setAttribute('aria-hidden', active ? 'false' : 'true');
                slide.inert = !active;
                stops[index].classList.toggle('is-viewed', active);
                stops[index].querySelector('button').setAttribute('aria-current', active ? 'step' : 'false');
                const status = slide.querySelector('.demo-guide-status');
                if (status) status.textContent = done ? '✓ Completado' : (current ? `${stepLabel} ${number}` : 'Siguiente');
            });
            track.style.transform = `translateX(calc(${1 - viewed} * (100% + 16px)))`;
            prev.disabled = viewed === 1;
            if (interactive) {
                nextText.textContent = completed === total ? 'Repetir recorrido' : `Completar ${stepLabel.toLowerCase()} ${viewed}`;
                if (progress) progress.textContent = completed === total ? `${total} de ${total} · recorrido explicado` : `${stepLabel} ${completed + 1} de ${total}`;
                if (bar) bar.style.width = `${Math.round(completed / total * 100)}%`;
            } else {
                next.disabled = viewed === total;
            }
        };
        const open = number => {
            viewed = number;
            if (interactive) {
                completed = number - 1;
                save();
            }
            render();
            bump();
        };
        const goNext = () => {
            if (interactive) {
                if (completed === total) {
                    completed = 0;
                    viewed = 1;
                } else {
                    completed = viewed;
                    viewed = Math.min(completed + 1, total);
                    replay(stops[completed - 1], 'done-enter');
                }
                save();
            } else if (viewed < total) {
                viewed += 1;
            }
            render();
            bump();
        };
        const goPrev = () => { if (viewed > 1) open(viewed - 1); };
        prev.addEventListener('click', goPrev);
        next.addEventListener('click', goNext);
        stops.forEach((stop, index) => stop.querySelector('button').addEventListener('click', () => open(index + 1)));
        carousel.addEventListener('keydown', event => {
            if (event.key === 'ArrowLeft') { event.preventDefault(); goPrev(); }
            if (event.key === 'ArrowRight') { event.preventDefault(); if (canAdvance()) goNext(); }
        });
        let startX = null;
        viewport.addEventListener('touchstart', event => { startX = event.touches[0].clientX; }, { passive: true });
        viewport.addEventListener('touchend', event => {
            if (startX === null) return;
            const distance = event.changedTouches[0].clientX - startX;
            startX = null;
            if (Math.abs(distance) < 50) return;
            if (distance < 0 && canAdvance()) goNext();
            if (distance > 0) goPrev();
        }, { passive: true });
        render();
        void track.offsetWidth;
        guide.classList.add('is-ready');
    });
});
</script>
