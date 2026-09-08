@extends('layouts.admin')

@section('content')

<div class="container mt-5">

    <div class="alert alert-success">
        <h4>Voucher invalidado correctamente</h4>

        <p>
            El voucher <strong>{{ $voucher->codigo }}</strong> fue bloqueado.
        </p>

        <p>
            El copago quedó registrado como saldo a favor del cliente.
        </p>
    </div>

</div>

@endsection
