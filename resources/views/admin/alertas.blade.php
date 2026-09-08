<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alertas Auditoría</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:#f4f7fb;">

<div class="container py-5">

    <a href="{{ url('escritorio-admin') }}"
       class="btn btn-secondary mb-4">
        Volver
    </a>

    <h2 class="fw-bold mb-4">
        🚨 Alertas Auditoría SDI
    </h2>

    <div class="row g-4 mb-4">

        <div class="col-md-4">
            <div class="card p-4 border-0 shadow-sm">
                <h6>🟢 Verdes</h6>
                <h2>{{ $verdes }}</h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4 border-0 shadow-sm">
                <h6>🟡 Amarillas</h6>
                <h2>{{ $amarillas }}</h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4 border-0 shadow-sm">
                <h6>🔴 Rojas</h6>
                <h2>{{ $rojas }}</h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-4 border-0 shadow-sm">

                <h6>💰 Monto Bloqueado</h6>

                <h2>
                    ${{ number_format($montoBloqueado,0,',','.') }}
                </h2>

            </div>
        </div>

    </div>

    <div class="card p-4 border-0 shadow-sm">

        <h5 class="fw-bold mb-3">
            Detalle de alertas
        </h5>
        <div class="row mb-4">
            @foreach($alertasPorNivel as $item)
                <div class="col-md-4">
                    <div class="card p-3 shadow-sm border-0">
                        <small>Nivel</small>
                        <h5>{{ strtoupper($item->nivel) }}</h5>
                        <h3>{{ $item->total }}</h3>
                    </div>
                </div>
            @endforeach
        </div>
        <table class="table table-hover align-middle">

            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Bono</th>
                    <th>Tipo</th>
                    <th>Nivel</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>

            <tbody>

                @forelse($alertas as $alerta)

                    <tr>
                        <td>{{ $alerta->created_at }}</td>
                        <td>{{ $alerta->voucher->codigo ?? '-' }}</td>
                        <td>{{ $alerta->tipo_alerta }}</td>
                        <td>
                            @if($alerta->nivel == 'rojo')
                                <span class="badge bg-danger">Rojo</span>
                            @elseif($alerta->nivel == 'amarillo')
                                <span class="badge bg-warning">Amarillo</span>
                            @else
                                <span class="badge bg-success">Verde</span>
                            @endif
                        </td>
                        <td>{{ $alerta->descripcion }}</td>
                        <td>
                            @if($alerta->resuelta)
                                ✅ Resuelta
                            @else
                                ⏳ Pendiente
                            @endif
                        </td>
                        <td>
                            @if($alerta->voucher)
                                <a href="{{ route('vouchers.show', $alerta->voucher->id) }}"
                                   class="btn btn-sm btn-outline-primary mb-1">Ver Bono</a>
                            @endif
                            @if(!$alerta->resuelta)
                                <form method="POST"
                                    action="{{ route('admin.alertas.resolver', $alerta->id) }}">
                                    @csrf

                                    <button class="btn btn-sm btn-success">
                                        Resolver
                                    </button>
                                </form>
                            @else
                                ✅
                            @endif
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="6">
                            No hay alertas registradas.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>
        <div class="card p-4 border-0 shadow-sm mt-4">

            <h5>
                🚨 Top Profesionales Observados
            </h5>

            <table class="table">

                <thead>
                    <tr>
                        <th>Profesional</th>
                        <th>Alertas</th>
                    </tr>
                </thead>

                <tbody>

                @foreach($topProfesionales as $item)

                    <tr>

                        <td>
                            {{ $item->profesional_id }}
                        </td>

                        <td>
                            {{ $item->total }}
                        </td>

                    </tr>

                @endforeach

                </tbody>

            </table>

        </div>

    </div>
    <div class="col-md-3">
        <div class="card p-4 border-0 shadow-sm">
            <h6>💰 Monto Bloqueado</h6>
            <h2>
                ${{ number_format($montoBloqueado,0,',','.') }}
            </h2>
        </div>
    </div>

</div>

</body>
</html>
