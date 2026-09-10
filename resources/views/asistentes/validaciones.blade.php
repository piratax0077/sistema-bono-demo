@extends('layouts.app')

@section('ocultar-navegacion-app', true)

@section('content')
<div class="container py-5" style="max-width:1320px;margin-inline:auto">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase text-primary fw-bold small">Control asistencial</div>
            <h2 class="fw-bold mb-1">Atenciones cerradas</h2>
            <p class="text-muted mb-0">Consultas finalizadas por el profesional y disponibles para revisión.</p>
        </div>
        <a href="{{ route('asistente.escritorio') }}" class="btn btn-outline-primary">Volver al inicio</a>
    </div>

    @if(session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <strong>Registros cerrados</strong>
        <span class="badge rounded-pill bg-primary">{{ $atenciones->count() }}</span>
    </div>
    <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>Voucher</th>
                <th>Cliente</th>
                <th>Profesional</th>
                <th>Diagnóstico</th>
                <th>Fecha cierre</th>
                <th>Estado</th>
                <th>Acción</th>
            </tr>
        </thead>

        <tbody>
            @forelse($atenciones as $atencion)
                <tr>
                    <td>{{ optional($atencion->voucher)->codigo }}</td>
                    <td>{{ optional($atencion->voucher)->cliente_nombre }}</td>
                    <td>{{ optional($atencion->voucher)->prestador_nombre ?: $atencion->profesional_id }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($atencion->diagnostico, 120) }}</td>
                    <td>{{ optional($atencion->cerrada_at)->format('d-m-Y H:i') ?: '-' }}</td>
                    <td><span class="badge bg-success">Atención cerrada</span></td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm" onclick="mostrarRevisionEnConstruccion()">Revisar</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">No hay atenciones cerradas pendientes de revisión.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function mostrarRevisionEnConstruccion() {
    if (window.Swal) {
        Swal.fire({
            icon: 'info',
            title: 'En construcción',
            text: 'La revisión detallada de la atención estará disponible próximamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#1848a1'
        });
        return;
    }
    alert('En construcción: la revisión detallada estará disponible próximamente.');
}
</script>
@endsection
