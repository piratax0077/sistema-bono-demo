@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="mb-4">
        <div class="text-uppercase text-primary fw-bold small">Centro médico</div>
        <h2 class="fw-bold mb-1">Escritorio Asistente SDI</h2>
        <p class="text-muted mb-0">Recepción, sala de espera y validación de atenciones.</p>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <a href="{{ route('asistente.recepcionBonos') }}" class="text-decoration-none">
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
            </a>
        </div>

        <div class="col-md-4">
            <a href="{{ route('asistente.recepcionBonos') }}" class="text-decoration-none">
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
            </a>
        </div>

        <div class="col-md-4">
            <a href="{{ route('asistente.validaciones') }}" class="text-decoration-none">
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
            </a>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('personas-rapidas.prueba') }}" class="btn btn-outline-primary">Buscar o registrar paciente</a>
    </div>
</div>
@endsection
