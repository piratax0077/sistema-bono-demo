@extends('layouts.admin')

@section('content')

<div class="container mt-4">

    <h3>🔐 Auditoría de Login</h3>

    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Email</th>
                <th>Resultado</th>
                <th>IP</th>
                <th>Navegador</th>
            </tr>
        </thead>

        <tbody>
            @foreach($logins as $login)
                <tr>
                    <td>{{ $login->created_at }}</td>
                    <td>{{ $login->email }}</td>
                    <td>
                        @if($login->resultado == 'exitoso')
                            <span class="badge bg-success">Exitoso</span>
                        @elseif($login->resultado == 'logout')
                            <span class="badge bg-secondary">Logout</span>
                        @elseif($login->resultado == 'bloqueado_rate_limit')
                            <span class="badge bg-danger">Bloqueado</span>
                        @else
                            <span class="badge bg-warning">Fallido</span>
                        @endif
                    </td>
                    <td>{{ $login->ip }}</td>
                    <td style="max-width: 350px;">
                        <small>{{ $login->user_agent }}</small>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="/auditoria" class="btn btn-secondary">
        Volver
    </a>

</div>

@endsection
