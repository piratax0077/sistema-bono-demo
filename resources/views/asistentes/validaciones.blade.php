@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Validaciones pendientes de atención</h2>

    @if(session('ok'))
        <div class="alert alert-success">{{ session('ok') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered table-striped mt-4">
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
                    <td>{{ $atencion->cerrada_at }}</td>
                    <td>{{ $atencion->estado }}</td>
                    <td>
                        <form method="POST" action="{{ route('vouchers.validarAtencionWeb', $atencion->voucher_id) }}">
                            @csrf
                            <button class="btn btn-success btn-sm">
                                Validar Atención
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No hay atenciones pendientes de validación.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
