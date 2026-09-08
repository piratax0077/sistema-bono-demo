@extends('layouts.admin')

@section('content')

<div class="container mt-4">

    <h3>🔐 IPs Autorizadas</h3>

    <div class="alert alert-info">
        Solo Admin y Auditor podrán ingresar desde IPs permitidas.
    </div>

    <form method="POST" action="{{ route('admin.ips.autorizadas.store') }}" class="card p-3 mb-4">
        @csrf

        <div class="row">
            <div class="col-md-2">
                <label>Rol</label>
                <select name="rol" class="form-control">
                    <option value="">Por usuario</option>
                    <option value="admin">Admin</option>
                    <option value="auditor">Auditor</option>
                </select>
            </div>

            <div class="col-md-3">
                <label>Usuario</label>
                <select name="user_id" class="form-control">
                    <option value="">Todos según rol</option>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->id }}">
                            {{ $usuario->name }} - {{ $usuario->rol }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label>IP</label>
                <input type="text"
                       name="ip"
                       class="form-control"
                       placeholder="Ej: 186.10.20.30"
                       required>
            </div>

            <div class="col-md-3">
                <label>Descripción</label>
                <input type="text"
                       name="descripcion"
                       class="form-control"
                       placeholder="Oficina, casa, VPN...">
            </div>

            <div class="col-md-1 d-flex align-items-end">
                <button class="btn btn-primary w-100">
                    +
                </button>
            </div>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>IP</th>
                <th>Rol</th>
                <th>User ID</th>
                <th>Descripción</th>
                <th>Activo</th>
                <th>Acción</th>
            </tr>
        </thead>

        <tbody>
            @foreach($ips as $ip)
                <tr>
                    <td>{{ $ip->ip }}</td>
                    <td>{{ $ip->rol ?? '-' }}</td>
                    <td>{{ $ip->user_id ?? '-' }}</td>
                    <td>{{ $ip->descripcion }}</td>
                    <td>
                        @if($ip->activo)
                            <span class="badge bg-success">Activo</span>
                        @else
                            <span class="badge bg-secondary">Inactivo</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST"
                              action="{{ route('admin.ips.autorizadas.toggle', $ip->id) }}">
                            @csrf

                            <button class="btn btn-sm btn-warning">
                                Activar / Desactivar
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ url('escritorio-admin') }}" class="btn btn-secondary">
        Volver
    </a>

</div>

@endsection
