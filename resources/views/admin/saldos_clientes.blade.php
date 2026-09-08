@extends('layouts.admin')

@section('content')

<div class="container mt-4">

    <h3>💰 Saldos a Favor de Clientes</h3>

    <div class="alert alert-info">
        Copagos registrados como saldo a favor por bonos invalidados.
    </div>

    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Monto</th>
                <th>Origen</th>
                <th>Estado</th>
                <th>Bono</th>
                <th>Fecha</th>
                <th>Usado en bono</th>
                <th>Consumido en</th>
            </tr>
        </thead>

        <tbody>
            @foreach($saldos as $saldo)
                <tr>
                    <td>{{ $saldo->id }}</td>
                    <td>{{ $saldo->cliente_nombre }}</td>
                    <td>${{ number_format($saldo->monto, 0, ',', '.') }}</td>
                    <td>{{ $saldo->origen }}</td>
                    <td>
                        <span class="badge bg-success">
                           @if($saldo->estado == 'disponible')
                                <span class="badge bg-success">Disponible</span>
                            @elseif($saldo->estado == 'consumido')
                                <span class="badge bg-secondary">Consumido</span>
                            @else
                                <span class="badge bg-warning">{{ $saldo->estado }}</span>
                            @endif
                        </span>
                    </td>
                    <td>
                        @if($saldo->voucher)
                            <a href="{{ route('vouchers.show', $saldo->voucher->id) }}"
                            class="btn btn-sm btn-outline-primary">
                                Ver Bono {{ $saldo->voucher->codigo }}
                            </a>
                        @else
                            {{ $saldo->voucher_id }}
                        @endif
                    </td>
                    <td>{{ $saldo->created_at }}</td>
                    <td>
                        @if($saldo->voucher_consumido_id)
                            <a href="{{ route('vouchers.show', $saldo->voucher_consumido_id) }}"
                            class="btn btn-sm btn-outline-success">
                                Ver Bono
                            </a>
                        @else
                            -
                        @endif
                    </td>

                    <td>
                        {{ $saldo->consumido_en ?? '-' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="/escritorio-admin" class="btn btn-secondary">
        Volver
    </a>

</div>

@endsection
