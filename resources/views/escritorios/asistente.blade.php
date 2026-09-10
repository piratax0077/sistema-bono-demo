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
        <div class="col-md-4">
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

        <div class="col-md-4">
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

        <div class="col-md-4">
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
});
</script>
@if(session('abrir_recepcion_modal') || $errors->any())
<script>document.addEventListener('DOMContentLoaded', function () { abrirModalAsistente('modalRecepcionAsistente'); });</script>
@endif
@endsection
