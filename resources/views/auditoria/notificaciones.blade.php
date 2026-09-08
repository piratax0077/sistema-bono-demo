@extends('layouts.admin')

@section('content')

<div class="container mt-4">

    <h3>🚨 Notificaciones Auditor</h3>

    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>Estado</th>
                <th>Título</th>
                <th>Mensaje</th>
                <th>Voucher</th>
                <th>Alerta</th>
                <th>Fecha</th>
                <th>Acción</th>
            </tr>
        </thead>

        <tbody>
            @foreach($notificaciones as $notificacion)
                <tr>
                    <td>
                        @if($notificacion->leido)
                            <span class="badge bg-secondary">Leída</span>
                        @else
                            <span class="badge bg-danger">Pendiente</span>
                        @endif
                    </td>

                    <td>{{ $notificacion->titulo }}</td>

                    <td>{!! nl2br(e($notificacion->mensaje)) !!}</td>

                    <td>
                        @if($notificacion->voucher)
                            <a href="{{ route('vouchers.show', $notificacion->voucher->id) }}"
                               class="btn btn-sm btn-outline-primary">
                                Ver Voucher
                            </a>
                        @else
                            -
                        @endif
                    </td>

                    <td>
                        @if($notificacion->alerta)
                            {{ $notificacion->alerta->tipo_alerta }}
                        @else
                            -
                        @endif
                    </td>

                    <td>{{ $notificacion->created_at }}</td>

                    <td>
                        @if(!$notificacion->leido)
                            <form method="POST"
                                  action="{{ route('auditoria.notificaciones.leer', $notificacion->id) }}">
                                @csrf

                                <button class="btn btn-sm btn-success">
                                    Marcar leída
                                </button>
                            </form>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="/auditoria" class="btn btn-secondary">
        Volver a auditoria
    </a>

</div>

@endsection
