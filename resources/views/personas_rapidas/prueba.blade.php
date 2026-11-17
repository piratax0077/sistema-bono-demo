@extends('layouts.app')

@section('ocultar-navegacion-app', true)

@section('content')
<style>
    .pr-shell{max-width:1180px;margin:0 auto;padding:32px 18px 48px}
    .pr-hero-wrap{position:relative}
    .pr-hero-wrap .role-welcome-hero h1{font-size:clamp(1.6rem,2.6vw,2.1rem)}
    .pr-hero-actions{position:absolute;top:24px;right:24px;z-index:2}
    .pr-hero-actions .btn{background:rgba(255,255,255,.14)!important;border:1px solid rgba(255,255,255,.4)!important;color:#fff!important}
    .pr-hero-actions .btn:hover{background:#fff!important;color:#1848a1!important}
    .pr-panel{border:1px solid #d9e4f3!important;border-radius:22px!important;box-shadow:0 18px 42px rgba(24,72,161,.09)!important;background:#fff!important}
    .pr-panel .card-footer{background:#f7faff!important;border-top:1px solid #e1e9f3;border-radius:0 0 22px 22px!important}
    .pr-eyebrow{color:#1848a1;font-size:.78rem;letter-spacing:.16em;text-transform:uppercase;font-weight:800}
    .pr-panel h2{font-size:1.25rem;font-weight:800;margin:.35rem 0 1.1rem}
    .pr-panel .form-label{color:#3a4c63;font-weight:700;font-size:.9rem}
    .pr-result{background:#edf4ff;border:1px solid #d2dff1;border-radius:16px}
    .pr-result small{color:#53657b;font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;font-weight:800}
    .pr-result #resumenPersona{color:#1848a1;font-size:1.05rem;font-weight:800}
    .pr-secure{background:#effafa;border:1px solid #bce5e5;border-radius:14px;padding:.65rem .85rem;color:#2d6f75;font-size:.85rem}
    .pr-done-icon{width:72px;height:72px;font-size:38px;background:linear-gradient(135deg,#1848a1,#31bebe)}
    @media(max-width:767px){.pr-shell{padding:20px 16px 36px}.pr-hero-actions{position:static;padding:0 0 12px;display:flex;justify-content:flex-end}.pr-hero-actions .btn{background:#fff!important;border-color:#c9d8ef!important;color:#1848a1!important}.pr-panel .card-footer{flex-direction:column;align-items:stretch!important;gap:12px}.pr-panel .card-footer .btn{width:100%}}
</style>
@php($volverEscritorio = auth()->user()?->rol === 'asistente' ? route('asistente.escritorio') : url('/escritorio-admin'))
<div class="pr-shell">
    <div class="pr-hero-wrap mb-4">
        <div class="pr-hero-actions">
            <a href="{{ $volverEscritorio }}" class="btn">← Volver al escritorio</a>
        </div>
        @include('partials.role_welcome_hero', [
            'eyebrow' => '',
            'title' => 'Recepción por RUT',
            'description' => 'Consulta, edita o agrega personas por RUT. Los datos se validan al instante y la dirección se guarda cifrada.',
        ])
    </div>

    <div id="personaWorkspace" class="row g-4">
        <div class="col-lg-5">
            <div class="card pr-panel h-100">
                <div class="card-body p-4">
                    <h2>Buscar paciente</h2>
                    <label for="rut" class="form-label">RUT</label>
                    <input
                        id="rut"
                        name="rut"
                        type="text"
                        class="form-control form-control-lg"
                        data-rut-input
                        autocomplete="off"
                        inputmode="text"
                        placeholder="Ej: 12.345.678-9">

                    <div id="estadoBusqueda" class="small text-muted mt-2">
                        Escriba un RUT para preparar la consulta.
                    </div>

                    <div class="pr-result mt-4 p-3">
                        <small>Resultado</small>
                        <div id="resumenPersona">Sin búsqueda activa</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <form id="personaForm" class="card pr-panel">
                <div class="card-body p-4">
                    <input type="hidden" id="personaId">
                    <h2>Datos de la persona</h2>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="nombre1" class="form-label">Nombres</label>
                            <input id="nombre1" name="nombre1" type="text" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label for="appaterno" class="form-label">Apellido paterno</label>
                            <input id="appaterno" name="appaterno" type="text" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label for="apmaterno" class="form-label">Apellido materno</label>
                            <input id="apmaterno" name="apmaterno" type="text" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" name="email" type="email" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input id="telefono" name="telefono" type="text" class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input id="direccion" name="direccion" type="text" class="form-control">
                            <div class="pr-secure mt-2">🔒 La dirección se guarda cifrada en la base rápida.</div>
                        </div>
                    </div>
                </div>

                <div class="card-footer p-4 d-flex justify-content-between align-items-center">
                    <span id="modoFormulario" class="small text-muted">Esperando RUT</span>
                    <button id="guardarBtn" type="submit" class="btn btn-primary px-4" disabled>Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <section id="recepcionCompletada" class="card pr-panel text-center d-none" role="status" aria-live="polite">
        <div class="card-body px-4 py-5">
            <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle text-white pr-done-icon">✓</div>
            <div class="pr-eyebrow mb-2">Recepción completada</div>
            <h2 class="h3 mb-2">Paciente recibido correctamente</h2>
            <p class="text-muted mb-4">Los datos fueron validados y ocultados. Aquí termina la labor de recepción de la secretaria.</p>
            <a href="{{ $volverEscritorio }}" class="btn btn-primary btn-lg">Volver al escritorio de la asistente</a>
        </div>
    </section>
</div>

<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const rutInput = document.getElementById('rut');
const estado = document.getElementById('estadoBusqueda');
const resumen = document.getElementById('resumenPersona');
const form = document.getElementById('personaForm');
const workspace = document.getElementById('personaWorkspace');
const recepcionCompletada = document.getElementById('recepcionCompletada');
const guardarBtn = document.getElementById('guardarBtn');
const modo = document.getElementById('modoFormulario');
const fields = ['personaId', 'nombre1', 'appaterno', 'apmaterno', 'email', 'telefono', 'direccion'];
let timer = null;
let ultimoRut = '';

function normalizarRut(value) {
    return (value || '').toUpperCase().replace(/[^0-9K]/g, '');
}

function formatearRut(value) {
    const limpio = normalizarRut(value);

    if (limpio.length <= 1) {
        return limpio;
    }

    return `${limpio.slice(0, -1)}-${limpio.slice(-1)}`;
}

function setStatus(message, type = 'muted') {
    estado.className = `small text-${type} mt-2`;
    estado.textContent = message;
}

function limpiarFormulario() {
    fields.forEach((id) => document.getElementById(id).value = '');
}

function cargarPersona(persona) {
    document.getElementById('personaId').value = persona.id || '';
    document.getElementById('nombre1').value = persona.nombre1 || '';
    document.getElementById('appaterno').value = persona.appaterno || '';
    document.getElementById('apmaterno').value = persona.apmaterno || '';
    document.getElementById('email').value = persona.email || '';
    document.getElementById('telefono').value = persona.telefono || '';
    document.getElementById('direccion').value = persona.direccion || '';
    resumen.textContent = persona.nombre_completo || 'Persona sin nombre completo';
}

async function leerJsonSeguro(response) {
    const contentType = response.headers.get('content-type') || '';

    if (contentType.includes('application/json')) {
        return response.json();
    }

    return {
        ok: false,
        message: 'El servidor respondio con un error inesperado. Recargue y vuelva a intentar.',
    };
}

async function buscarRut() {
    const rut = normalizarRut(rutInput.value);
    ultimoRut = rut;

    if (rut.length < 2) {
        limpiarFormulario();
        guardarBtn.disabled = true;
        modo.textContent = 'Esperando RUT';
        resumen.textContent = 'Sin búsqueda activa';
        setStatus('Escriba un RUT para preparar la consulta.');
        return;
    }

    setStatus('Preparando consulta...');

    let data;

    try {
        const response = await fetch(`{{ route('personas-rapidas.buscar') }}?rut=${encodeURIComponent(rut)}`, {
            headers: {'Accept': 'application/json'}
        });
        data = await leerJsonSeguro(response);
    } catch (error) {
        limpiarFormulario();
        guardarBtn.disabled = true;
        modo.textContent = 'Error de conexion';
        resumen.textContent = 'No disponible';
        setStatus('No se pudo conectar con la base rapida.', 'danger');
        return;
    }

    if (ultimoRut !== rut) {
        return;
    }

    if (!data.ok) {
        limpiarFormulario();
        guardarBtn.disabled = true;
        modo.textContent = 'RUT no valido';
        resumen.textContent = 'No disponible';
        setStatus(data.message || 'No se puede consultar este RUT.', 'danger');
        return;
    }

    guardarBtn.disabled = false;

    if (data.found) {
        cargarPersona(data.persona);
        modo.textContent = 'Editando persona existente';
        setStatus('Encontrado. Puede revisar y editar los datos.', 'success');
        return;
    }

    limpiarFormulario();
    resumen.textContent = `RUT ${data.rut_normalizado} no encontrado`;
    modo.textContent = 'Nuevo registro';
    setStatus(data.message, 'warning');
}

rutInput.addEventListener('input', () => {
    rutInput.value = formatearRut(rutInput.value);
    clearTimeout(timer);
    timer = setTimeout(buscarRut, 250);
});

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    guardarBtn.disabled = true;
    setStatus('Guardando...');

    const payload = {
        rut: rutInput.value,
        nombre1: document.getElementById('nombre1').value,
        appaterno: document.getElementById('appaterno').value,
        apmaterno: document.getElementById('apmaterno').value,
        email: document.getElementById('email').value,
        telefono: document.getElementById('telefono').value,
        direccion: document.getElementById('direccion').value,
    };

    let response;
    let data;

    try {
        response = await fetch(`{{ route('personas-rapidas.guardar') }}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify(payload),
        });
        data = await leerJsonSeguro(response);
    } catch (error) {
        guardarBtn.disabled = false;
        setStatus('No se pudo conectar con la base rapida.', 'danger');
        return;
    }

    guardarBtn.disabled = false;

    if (!response.ok || !data.ok) {
        setStatus(data.message || 'No se pudo guardar.', 'danger');
        return;
    }

    limpiarFormulario();
    rutInput.value = '';
    workspace.classList.add('d-none');
    recepcionCompletada.classList.remove('d-none');
    recepcionCompletada.focus?.();
});
</script>
@endsection
