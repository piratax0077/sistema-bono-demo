@extends('layouts.app')

@section('ocultar-navegacion-app', true)

@section('content')
<div class="container py-5" style="max-width:1320px;margin-inline:auto">
    <div class="mb-4">
        <div class="text-uppercase text-primary fw-bold small">Centro médico</div>
        <h2 class="fw-bold mb-1">Escritorio Asistente SDI</h2>
        <p class="text-muted mb-0">Recepción, sala de espera y validación de atenciones.</p>
    </div>

    <div class="alert {{ $sesionMedsdi['ok'] ? 'alert-success' : 'alert-warning' }} d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4" role="status">
        <div>
            <div class="fw-bold">
                {{ $sesionMedsdi['ok'] ? 'Conectada con Med-SDI' : 'No fue posible iniciar sesión en Med-SDI' }}
            </div>
            <div>{{ $sesionMedsdi['mensaje'] }}</div>
        </div>
        @if ($sesionMedsdi['ok'])
            <div class="text-md-end">
                <div class="fw-bold">{{ $sesionMedsdi['user']['name'] ?: $sesionMedsdi['user']['email'] }}</div>
                <div class="small">
                    {{ $sesionMedsdi['user']['email'] }}
                    · {{ collect($sesionMedsdi['roles'])->pluck('name')->filter()->implode(', ') }}
                </div>
            </div>
        @endif
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-xl-3">
            <button type="button" class="btn p-0 border-0 bg-transparent text-start w-100 h-100" onclick="abrirModalAsistente('modalRecepcionAsistente')">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="fs-2">📲</div>
                            <span class="badge bg-primary rounded-pill">{{ $pendientesRecepcion }}</span>
                        </div>
                        <h4 class="mt-3 text-dark">Recepción de bonos</h4>
                        <p class="text-muted mb-0">Leer QR, reconocer al paciente y enviarlo a espera.</p>
                    </div>
                </div>
            </button>
        </div>

        <div class="col-md-6 col-xl-3">
            <button type="button" class="btn p-0 border-0 bg-transparent text-start w-100 h-100" onclick="abrirModalAsistente('modalSalaEsperaAsistente')">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="fs-2">🕐</div>
                            <span class="badge bg-success rounded-pill">{{ $pacientesEnEspera }}</span>
                        </div>
                        <h4 class="mt-3 text-dark">Sala de espera</h4>
                        <p class="text-muted mb-0">Pacientes sincronizados con estado Espera en Medichile.</p>
                    </div>
                </div>
            </button>
        </div>

        <div class="col-md-6 col-xl-3">
            <button type="button" class="btn p-0 border-0 bg-transparent text-start w-100 h-100" onclick="abrirModalAsistente('modalValidacionesAsistente')">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="fs-2">✅</div>
                            <span class="badge bg-warning text-dark rounded-pill">{{ $validacionesPendientes }}</span>
                        </div>
                        <h4 class="mt-3 text-dark">Validar atenciones</h4>
                        <p class="text-muted mb-0">Revisar cierres profesionales y habilitar bonos para cobro.</p>
                    </div>
                </div>
            </button>
        </div>

        <div class="col-md-6 col-xl-3">
            <button type="button" class="btn p-0 border-0 bg-transparent text-start w-100 h-100" onclick="abrirModalAsistente('modalVentaBonoAsistente')">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="fs-2">🎫</div>
                        <h4 class="mt-3 text-dark">Venta de bonos</h4>
                        <p class="text-muted mb-0">Buscar al paciente por RUT, elegir prestación y reservar su hora en Med-SDI.</p>
                    </div>
                </div>
            </button>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('personas-rapidas.prueba') }}" class="btn btn-outline-primary">Buscar o registrar paciente</a>
    </div>
</div>

<div class="modal fade" id="modalValidacionesAsistente" tabindex="-1" aria-labelledby="modalValidacionesTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background:linear-gradient(120deg,#1848a1,#31bebe)">
                <div><div class="small fw-bold text-uppercase">Control asistencial</div><h4 class="modal-title fw-bold" id="modalValidacionesTitulo">Atenciones cerradas</h4><small>Consultas finalizadas por el profesional y disponibles para revisión.</small></div>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalAsistente('modalValidacionesAsistente')" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Bono</th><th>Paciente</th><th>Profesional</th><th>Diagnóstico</th><th>Cierre</th><th>Estado</th><th>Acción</th></tr></thead>
                        <tbody>
                        @forelse($atencionesCerradas as $voucherCerrado)
                            <tr>
                                <td><strong>{{ $voucherCerrado->codigo }}</strong></td>
                                <td>{{ $voucherCerrado->beneficiario_nombre ?: $voucherCerrado->cliente_nombre }}</td>
                                <td>{{ $voucherCerrado->prestador_nombre ?: optional($voucherCerrado->profesional)->nombre ?: '-' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit(optional($voucherCerrado->atencion)->diagnostico ?: 'Sin diagnóstico informado', 85) }}</td>
                                <td>{{ optional(optional($voucherCerrado->atencion)->cerrada_at ?: $voucherCerrado->atencion_cerrada_at)->format('d-m-Y H:i') ?: '-' }}</td>
                                <td>
                                    @if($voucherCerrado->estado === 'validado_atencion')
                                        <span class="badge bg-success">Habilitada para cobro</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Atención cerrada</span>
                                    @endif
                                </td>
                                <td><button type="button" class="btn btn-primary btn-sm" onclick="mostrarRevisionEnConstruccion()">Revisar</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-5">No hay atenciones cerradas pendientes de revisión.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSalaEsperaAsistente" tabindex="-1" aria-labelledby="modalSalaEsperaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background:linear-gradient(120deg,#1848a1,#31bebe)">
                <div><div class="small fw-bold text-uppercase">Agenda clínica</div><h4 class="modal-title fw-bold" id="modalSalaEsperaTitulo">Pacientes en espera</h4><small>Recepcionados y confirmados en la agenda real de Med-SDI.</small></div>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalAsistente('modalSalaEsperaAsistente')" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Paciente</th><th>RUT</th><th>Prestación</th><th>Llegada</th><th>Hora Med-SDI</th><th>Estado</th></tr></thead>
                        <tbody>
                        @forelse($pacientesEnEsperaDetalle as $bono)
                            <tr>
                                <td><strong>{{ $bono->beneficiario_nombre ?: $bono->cliente_nombre }}</strong><small class="d-block text-muted">Bono {{ $bono->codigo }}</small></td>
                                <td>{{ sdi_formatear_rut($bono->beneficiario_rut_visible ?: $bono->cliente_rut_visible) }}</td>
                                <td>{{ $bono->tipo_servicio }}</td>
                                <td>{{ optional($bono->agenda->fecha_hora_confirmada)->format('d-m-Y H:i') ?: '-' }}</td>
                                <td><span class="badge bg-light text-dark border">#{{ $bono->agenda->medichile_hora_medica_id }}</span></td>
                                <td><span class="badge bg-warning text-dark">Esperando atención</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">No hay pacientes esperando atención.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRecepcionAsistente" tabindex="-1" aria-labelledby="modalRecepcionAsistenteTitulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background:linear-gradient(120deg,#1848a1,#31bebe)">
                <div>
                    <h4 class="modal-title fw-bold" id="modalRecepcionAsistenteTitulo">Recepción de bonos</h4>
                    <small>Identifique la reserva y envíe al paciente a sala de espera.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalAsistente('modalRecepcionAsistente')" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                @if(session('ok'))<div class="alert alert-success">{{ session('ok') }}</div>@endif
                @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

                <ul class="nav nav-pills nav-fill gap-2 mb-4" role="tablist">
                    <li class="nav-item"><button class="nav-link {{ old('metodo') !== 'rut' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#recepcionCodigo" type="button">Código o QR</button></li>
                    <li class="nav-item"><button class="nav-link {{ old('metodo') === 'rut' ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#recepcionRut" type="button">RUT del paciente</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade {{ old('metodo') !== 'rut' ? 'show active' : '' }}" id="recepcionCodigo">
                        <form method="POST" action="{{ route('asistente.recepcion.buscar') }}">
                            @csrf
                            <input type="hidden" name="metodo" value="codigo">
                            <label class="form-label fw-bold">Código del bono o contenido del QR</label>
                            <div class="input-group input-group-lg">
                                <input class="form-control" name="codigo" value="{{ old('codigo') }}" placeholder="Ej.: BONO-EXT-260910-ATI..." required>
                                <button class="btn btn-primary">Buscar reserva</button>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade {{ old('metodo') === 'rut' ? 'show active' : '' }}" id="recepcionRut">
                        <form method="POST" action="{{ route('asistente.recepcion.buscar') }}">
                            @csrf
                            <input type="hidden" name="metodo" value="rut">
                            <label class="form-label fw-bold">RUT del paciente</label>
                            <div class="input-group input-group-lg">
                                <input class="form-control" name="rut" value="{{ old('rut') }}" placeholder="Ej.: 6.187.674-K" required>
                                <button class="btn btn-primary">Buscar hora</button>
                            </div>
                        </form>
                        <div class="alert alert-light border mt-3 mb-0">Sólo se mostrarán horas vigentes pendientes de llegada.</div>
                    </div>
                </div>

                @if($bonosRecepcion->isNotEmpty())
                    <hr class="my-4">
                    <h5 class="fw-bold mb-3">Horas encontradas</h5>
                    @foreach($bonosRecepcion as $bono)
                        @php($recepcion = $recepcionesPorVoucher->get($bono->id))
                        <div class="card border mb-3"><div class="card-body d-flex flex-column flex-md-row justify-content-between gap-3">
                            <div>
                                <h5 class="mb-2">{{ $bono->tipo_servicio }} · {{ $bono->codigo }}</h5>
                                <div><strong>Paciente:</strong> {{ $bono->beneficiario_nombre ?: $bono->cliente_nombre }}</div>
                                <div><strong>Profesional:</strong> {{ $bono->prestador_nombre ?: optional($bono->profesional)->nombre }}</div>
                                <div><strong>Hora:</strong> {{ optional($bono->agenda?->fecha_hora_confirmada ?: $bono->agenda?->fecha_hora_solicitada)?->format('d-m-Y H:i') }}</div>
                            </div>
                            @if($recepcion)
                                <form method="POST" action="{{ route('asistente.recepcion.espera', $recepcion) }}" class="align-self-md-center">
                                    @csrf
                                    <button class="btn btn-success text-nowrap">Confirmar llegada</button>
                                </form>
                            @else
                                <span class="badge text-bg-warning align-self-md-center">Sin recepción asociada</span>
                            @endif
                        </div></div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVentaBonoAsistente" tabindex="-1" aria-labelledby="modalVentaBonoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white" style="background:linear-gradient(120deg,#1848a1,#31bebe)">
                <div><div class="small fw-bold text-uppercase">Reserva asistida</div><h4 class="modal-title fw-bold" id="modalVentaBonoTitulo">Vender bono y reservar hora</h4><small>La hora y el bono quedarán asociados al paciente identificado por su RUT.</small></div>
                <button type="button" class="btn-close btn-close-white" onclick="cerrarModalAsistente('modalVentaBonoAsistente')" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div id="ventaAsistenteAviso" class="alert d-none" role="alert"></div>
                <section class="border rounded-3 p-3 mb-3">
                    <div class="small text-primary fw-bold mb-2">1 · PACIENTE</div>
                    <label class="form-label fw-bold" for="ventaAsistenteRut">RUT del paciente</label>
                    <div class="input-group input-group-lg">
                        <input id="ventaAsistenteRut" class="form-control" placeholder="Ej.: 17.174.188-2">
                        <button id="ventaAsistenteValidar" type="button" class="btn btn-primary">Validar paciente</button>
                    </div>
                    <div id="ventaAsistentePaciente" class="alert alert-success mt-3 mb-0 d-none"></div>
                </section>

                <section id="ventaAsistentePasoPrestacion" class="border rounded-3 p-3 mb-3 d-none">
                    <div class="small text-primary fw-bold mb-2">2 · PRESTACIÓN FONASA</div>
                    <div class="input-group">
                        <input id="ventaAsistentePrestacionBuscar" class="form-control" placeholder="Consulta médica o código 0101001">
                        <button id="ventaAsistentePrestacionBtn" type="button" class="btn btn-info text-white">Buscar</button>
                    </div>
                    <div id="ventaAsistentePrestaciones" class="list-group mt-2" style="max-height:220px;overflow:auto"></div>
                    <div id="ventaAsistentePrestacionElegida" class="alert alert-info mt-3 mb-0 d-none"></div>
                </section>

                <section id="ventaAsistentePasoProfesional" class="border rounded-3 p-3 mb-3 d-none">
                    <div class="small text-primary fw-bold mb-2">3 · PROFESIONAL Y LUGAR</div>
                    <div class="row g-2">
                        <div class="col-md-5"><select id="ventaAsistenteEspecialidad" class="form-select"><option value="">Todas las especialidades</option></select></div>
                        <div class="col-md-5"><input id="ventaAsistenteProfesionalBuscar" class="form-control" placeholder="Nombre del profesional"></div>
                        <div class="col-md-2 d-grid"><button id="ventaAsistenteProfesionalBtn" type="button" class="btn btn-primary">Buscar</button></div>
                    </div>
                    <div id="ventaAsistenteProfesionales" class="row g-2 mt-1"></div>
                    <div id="ventaAsistenteProfesionalElegido" class="alert alert-info mt-3 mb-0 d-none"></div>
                </section>

                <section id="ventaAsistentePasoHora" class="border rounded-3 p-3 d-none">
                    <div class="small text-primary fw-bold mb-2">4 · FECHA Y HORA</div>
                    <div id="ventaAsistenteCotizacion" class="alert alert-success"></div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-8"><label class="form-label">Fecha</label><input id="ventaAsistenteFecha" type="date" min="{{ now()->format('Y-m-d') }}" class="form-control"></div>
                        <div class="col-md-4 d-grid"><button id="ventaAsistenteHorasBtn" type="button" class="btn btn-outline-primary">Ver horas disponibles</button></div>
                    </div>
                    <div id="ventaAsistenteHoras" class="d-flex flex-wrap gap-2 mt-3"></div>
                </section>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function mostrarRevisionEnConstruccion() {
    if (window.Swal) {
        Swal.fire({icon:'info', title:'En construcción', text:'La revisión detallada de la atención estará disponible próximamente.', confirmButtonText:'Entendido', confirmButtonColor:'#1848a1'});
        return;
    }
    alert('En construcción: la revisión detallada estará disponible próximamente.');
}
function abrirModalAsistente(id) {
    var modal = document.getElementById(id);
    if (!modal) return;
    modal.style.display = 'block';
    modal.classList.add('show');
    modal.removeAttribute('aria-hidden');
    modal.setAttribute('aria-modal', 'true');
    document.body.classList.add('modal-open');
    var fondo = document.createElement('div');
    fondo.className = 'modal-backdrop fade show';
    fondo.dataset.modalAsistenteBackdrop = id;
    fondo.onclick = function () { cerrarModalAsistente(id); };
    document.body.appendChild(fondo);
}
function cerrarModalAsistente(id) {
    var modal = document.getElementById(id);
    if (!modal) return;
    modal.style.display = 'none';
    modal.classList.remove('show');
    modal.setAttribute('aria-hidden', 'true');
    modal.removeAttribute('aria-modal');
    document.body.classList.remove('modal-open');
    document.querySelectorAll('[data-modal-asistente-backdrop="' + id + '"]').forEach(function (fondo) { fondo.remove(); });
}
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#modalRecepcionAsistente [data-bs-toggle="pill"]').forEach(function (boton) {
        boton.addEventListener('click', function (evento) {
            evento.preventDefault();
            document.querySelectorAll('#modalRecepcionAsistente [data-bs-toggle="pill"]').forEach(function (item) { item.classList.remove('active'); });
            document.querySelectorAll('#modalRecepcionAsistente .tab-pane').forEach(function (panel) { panel.classList.remove('show', 'active'); });
            boton.classList.add('active');
            var panel = document.querySelector(boton.dataset.bsTarget);
            if (panel) panel.classList.add('show', 'active');
        });
    });

    const venta = { paciente: null, prestacion: null, seleccion: null, cotizacion: null };
    const ventaAviso = (texto, tipo = 'danger') => {
        const el = document.getElementById('ventaAsistenteAviso');
        el.className = `alert alert-${tipo}`;
        el.textContent = texto;
    };
    const ventaJson = async (url, opciones = {}) => {
        const respuesta = await fetch(url, {headers: {'Accept':'application/json', ...(opciones.body ? {'Content-Type':'application/json', 'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content} : {})}, ...opciones});
        const data = await respuesta.json();
        if (!respuesta.ok && !data.mensaje) data.mensaje = Object.values(data.errors || {}).flat()[0] || 'No fue posible procesar la solicitud.';
        return data;
    };

    document.getElementById('ventaAsistenteValidar')?.addEventListener('click', async () => {
        const rut = document.getElementById('ventaAsistenteRut').value.trim();
        if (!rut) return ventaAviso('Ingrese el RUT del paciente.');
        ventaAviso('Validando paciente en Med-SDI...', 'info');
        const data = await ventaJson(`{{ route('asistente.venta_bonos.paciente') }}?rut=${encodeURIComponent(rut)}`);
        if (!data.ok || !data.paciente) return ventaAviso(data.mensaje || 'Paciente no encontrado.');
        venta.paciente = data.paciente;
        document.getElementById('ventaAsistentePaciente').textContent = `✓ ${data.paciente.nombre_completo} · RUT ${data.paciente.rut}`;
        document.getElementById('ventaAsistentePaciente').classList.remove('d-none');
        document.getElementById('ventaAsistentePasoPrestacion').classList.remove('d-none');
        document.getElementById('ventaAsistenteAviso').classList.add('d-none');
    });

    const buscarPrestaciones = async () => {
        const buscar = document.getElementById('ventaAsistentePrestacionBuscar').value.trim();
        if (buscar.length < 2) return ventaAviso('Escriba al menos 2 caracteres para buscar una prestación.');
        const contenedor = document.getElementById('ventaAsistentePrestaciones');
        contenedor.innerHTML = '<div class="list-group-item text-muted">Buscando...</div>';
        const data = await ventaJson(`{{ route('asistente.venta_bonos.prestaciones') }}?buscar=${encodeURIComponent(buscar)}`);
        contenedor.innerHTML = '';
        (data.registros || []).forEach(item => {
            const boton = document.createElement('button'); boton.type = 'button'; boton.className = 'list-group-item list-group-item-action';
            boton.textContent = `${item.codigo} · ${item.nombre}`; boton.onclick = () => seleccionarPrestacion(item); contenedor.appendChild(boton);
        });
        if (!contenedor.children.length) contenedor.innerHTML = `<div class="list-group-item text-muted">${data.mensaje || 'Sin resultados.'}</div>`;
    };
    document.getElementById('ventaAsistentePrestacionBtn')?.addEventListener('click', buscarPrestaciones);
    const seleccionarPrestacion = async item => {
        venta.prestacion = item;
        document.getElementById('ventaAsistentePrestaciones').innerHTML = '';
        const elegida = document.getElementById('ventaAsistentePrestacionElegida'); elegida.textContent = `${item.codigo} · ${item.nombre}`; elegida.classList.remove('d-none');
        document.getElementById('ventaAsistentePasoProfesional').classList.remove('d-none');
        const data = await ventaJson('{{ route('asistente.venta_bonos.especialidades') }}');
        const select = document.getElementById('ventaAsistenteEspecialidad'); select.innerHTML = '<option value="">Todas las especialidades</option>';
        (data.registros || []).forEach(e => select.add(new Option(e.nombre, e.id)));
    };

    document.getElementById('ventaAsistenteProfesionalBtn')?.addEventListener('click', async () => {
        const params = new URLSearchParams();
        const especialidad = document.getElementById('ventaAsistenteEspecialidad');
        const nombre = document.getElementById('ventaAsistenteProfesionalBuscar').value.trim();
        if (especialidad.value) params.set('id_especialidad', especialidad.value); if (nombre) params.set('nombre_profesional', nombre);
        const contenedor = document.getElementById('ventaAsistenteProfesionales'); contenedor.innerHTML = '<div class="text-muted">Buscando profesionales...</div>';
        const data = await ventaJson(`{{ route('asistente.venta_bonos.profesionales') }}?${params}`); contenedor.innerHTML = '';
        (data.registros || []).forEach(prof => {
            const nombreCompleto = `${prof.nombre} ${prof.apellido_uno || ''} ${prof.apellido_dos || ''}`.trim();
            const esp = prof.nombre_especialidad || especialidad.options[especialidad.selectedIndex]?.text || '';
            const col = document.createElement('div'); col.className = 'col-md-6';
            col.innerHTML = `<div class="card h-100"><div class="card-body"><strong>${nombreCompleto}</strong><div class="small text-muted mb-2">${esp}</div><div class="venta-lugares"></div></div></div>`;
            (prof.lugares_atencion || []).forEach(lugar => { const b=document.createElement('button'); b.type='button'; b.className='btn btn-outline-success btn-sm me-1'; b.textContent=lugar.nombre; b.onclick=()=>elegirProfesional({idProfesional:prof.id,nombreProfesional:nombreCompleto,idEspecialidad:especialidad.value,especialidad:esp,idLugar:lugar.id,lugarNombre:lugar.nombre}); col.querySelector('.venta-lugares').appendChild(b); });
            contenedor.appendChild(col);
        });
        if (!contenedor.children.length) contenedor.innerHTML = `<div class="alert alert-warning">${data.mensaje || 'No se encontraron profesionales.'}</div>`;
    });
    const elegirProfesional = async seleccion => {
        venta.seleccion = seleccion;
        const elegido = document.getElementById('ventaAsistenteProfesionalElegido'); elegido.textContent = `${seleccion.nombreProfesional} · ${seleccion.especialidad} · ${seleccion.lugarNombre}`; elegido.classList.remove('d-none');
        const data = await ventaJson('{{ route('asistente.venta_bonos.cotizar') }}', {method:'POST', body:JSON.stringify({id_profesional:seleccion.idProfesional,id_lugar_atencion:seleccion.idLugar,id_prestacion:venta.prestacion.id,origen_prestacion:venta.prestacion.origen})});
        if (!data.ok) return ventaAviso(data.mensaje || 'No fue posible cotizar esta prestación.');
        venta.cotizacion = data.cotizacion; const moneda = v => new Intl.NumberFormat('es-CL',{style:'currency',currency:'CLP',maximumFractionDigits:0}).format(v || 0);
        document.getElementById('ventaAsistenteCotizacion').textContent = `Valor ${moneda(data.cotizacion.valor)} · Bonificación ${moneda(data.cotizacion.bonificacion)} · Copago ${moneda(data.cotizacion.copago)}`;
        document.getElementById('ventaAsistentePasoHora').classList.remove('d-none');
    };

    document.getElementById('ventaAsistenteHorasBtn')?.addEventListener('click', async () => {
        const fecha = document.getElementById('ventaAsistenteFecha').value; if (!venta.seleccion || !fecha) return ventaAviso('Seleccione una fecha.');
        const p = new URLSearchParams({id_profesional:venta.seleccion.idProfesional,id_lugar:venta.seleccion.idLugar,fecha});
        const data = await ventaJson(`{{ route('asistente.venta_bonos.horas_disponibles') }}?${p}`); const contenedor=document.getElementById('ventaAsistenteHoras'); contenedor.innerHTML='';
        (data.registros || []).forEach(h => { const b=document.createElement('button'); b.type='button'; b.className='btn btn-outline-primary'; b.textContent=h.hora; b.onclick=()=>confirmarVenta(h.fecha_hora); contenedor.appendChild(b); });
        if (!contenedor.children.length) contenedor.innerHTML='<span class="text-muted">No hay horas disponibles para esta fecha.</span>';
    });
    const confirmarVenta = async fechaHora => {
        const confirmacion = window.Swal ? await Swal.fire({icon:'question',title:'¿Confirmar reserva?',text:`La hora quedará asociada a ${venta.paciente.nombre_completo}.`,showCancelButton:true,confirmButtonText:'Sí, reservar',cancelButtonText:'Cancelar',confirmButtonColor:'#1848a1'}) : {isConfirmed:confirm('¿Confirmar reserva para este paciente?')};
        if (!confirmacion.isConfirmed) return;
        const s=venta.seleccion,p=venta.prestacion;
        const data=await ventaJson('{{ route('asistente.venta_bonos.agendar') }}',{method:'POST',body:JSON.stringify({rut:venta.paciente.rut,id_profesional:s.idProfesional,nombre_profesional:s.nombreProfesional,id_especialidad:s.idEspecialidad||null,especialidad:s.especialidad,id_lugar:s.idLugar,lugar_nombre:s.lugarNombre,direccion:'',fecha_hora:fechaHora,id_prestacion:p.id,origen_prestacion:p.origen,prestacion_codigo:p.codigo,prestacion_nombre:p.nombre})});
        if (!data.ok) return ventaAviso(data.mensaje || 'No fue posible reservar la hora.');
        if (window.Swal) await Swal.fire({icon:'success',title:'Reserva realizada',text:`${data.mensaje} Bono ${data.voucher}.`,confirmButtonColor:'#1848a1'}); else alert(data.mensaje);
        window.location.reload();
    };
});
</script>
@if(session('abrir_recepcion_modal') || $errors->any())
<script>document.addEventListener('DOMContentLoaded', function () { abrirModalAsistente('modalRecepcionAsistente'); });</script>
@endif
@endsection
