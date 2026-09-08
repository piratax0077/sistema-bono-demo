@extends('layouts.admin')

@section('content')

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Mascotas</h3>
            <p class="text-muted mb-0">
                Mascotas vinculadas a clientes/beneficiarios para venta de bonos y atención.
            </p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.mascotas.crear') }}" class="btn btn-primary">
                Crear mascota
            </a>
            <a href="/escritorio-admin" class="btn btn-secondary">
                Volver
            </a>
        </div>
    </div>

    @if(session('ok'))
        <div class="alert alert-success">
            {{ session('ok') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <p class="text-muted">
                Total registros: {{ $mascotas->count() }}
            </p>

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Mascota</th>
                            <th>Especie</th>
                            <th>Raza</th>
                            <th>Dueño</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($mascotas as $mascota)
                            <tr>
                                <td>{{ $mascota->nombre }}</td>
                                <td>{{ $mascota->especie }}</td>
                                <td>{{ $mascota->raza }}</td>
                                <td>{{ $mascota->dueno_nombre }}</td>
                                <td>{{ $mascota->dueno_telefono }}</td>
                                <td>{{ $mascota->dueno_email }}</td>
                                <td>
                                    <span class="badge {{ $mascota->activo ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $mascota->activo ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    Aún no hay mascotas registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
