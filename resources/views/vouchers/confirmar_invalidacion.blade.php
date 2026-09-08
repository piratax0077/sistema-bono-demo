@extends('layouts.admin')

@section('content')

<div class="container mt-5">

    <div class="card shadow-sm border-danger">
        <div class="card-header bg-danger text-white">
            Confirmar invalidación de voucher
        </div>

        <div class="card-body">
            <p>
                Estás a punto de invalidar el voucher:
                <strong>{{ $voucher->codigo }}</strong>
            </p>

            <p>
                Si confirmas, este voucher quedará bloqueado y no podrá ser usado.
                Si existe copago, será registrado como saldo a favor del cliente.
            </p>

            <form method="POST"
                  action="{{ route('vouchers.invalidar.confirmar', $voucher->qr_token) }}">
                @csrf

                <button type="submit" class="btn btn-danger">
                    Sí, invalidar voucher
                </button>

                <a href="{{ route('vouchers.qr', $voucher->qr_token) }}"
                   class="btn btn-secondary">
                    Cancelar
                </a>
            </form>
        </div>
    </div>

</div>

@endsection
