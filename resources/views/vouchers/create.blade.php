<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Emitir Voucher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:#f4f7fb;">

@php
    $servicios = \App\Models\VoucherServicio::where('activo', 1)
        ->orderBy('nombre')
        ->get();

    $profesionales = \App\Models\Profesional::activos()
        ->orderBy('apellido_uno')
        ->orderBy('apellido_dos')
        ->orderBy('nombre')
        ->get();

    $decrypt = function ($value) {
        if (! filled($value)) {
            return '';
        }

        try {
            return \Illuminate\Support\Facades\Crypt::decryptString((string) $value);
        } catch (\Throwable $exception) {
            return (string) $value;
        }
    };

    $edad = function ($fecha) {
        if (! filled($fecha)) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($fecha)->age;
        } catch (\Throwable $exception) {
            return null;
        }
    };

    $titulares = \App\Models\VoucherBaseUsuario::with(['dependientes' => function ($query) {
            $query->where('estado', 'activo')->orderBy('nombre');
        }])
        ->where('estado', 'activo')
        ->orderBy('nombre')
        ->get();

    $beneficiarioOptions = [];

    foreach ($titulares as $titular) {
        $titularRut = $decrypt($titular->rut_encrypted);
        $titularDireccion = $decrypt($titular->direccion_encrypted);
        $titularFechaNacimiento = $decrypt($titular->fecha_nacimiento_encrypted);
        $titularOtros = is_array($titular->otros) ? $titular->otros : [];

        $beneficiarioOptions[] = [
            'key' => 'titular:'.$titular->id,
            'tipo' => 'titular',
            'nombre' => $titular->nombre,
            'rut' => $titularRut,
            'parentesco' => 'Titular',
            'direccion' => $titularDireccion,
            'edad' => $edad($titularFechaNacimiento),
            'titular_nombre' => $titular->nombre,
            'titular_rut' => $titularRut,
            'telefono' => $titularOtros['telefono'] ?? '',
            'email' => $titularOtros['email'] ?? '',
            'cargas_count' => $titular->dependientes->count(),
        ];

        foreach ($titular->dependientes as $dependiente) {
            $dependienteFechaNacimiento = $decrypt($dependiente->fecha_nacimiento_encrypted);

            $beneficiarioOptions[] = [
                'key' => 'carga:'.$dependiente->id,
                'tipo' => 'carga',
                'nombre' => $dependiente->nombre,
                'rut' => $decrypt($dependiente->rut_encrypted),
                'parentesco' => $dependiente->parentesco ?: 'Carga',
                'direccion' => $decrypt($dependiente->direccion_encrypted) ?: $titularDireccion,
                'edad' => $edad($dependienteFechaNacimiento),
                'titular_nombre' => $titular->nombre,
                'titular_rut' => $titularRut,
                'telefono' => $titularOtros['telefono'] ?? '',
                'email' => $titularOtros['email'] ?? '',
                'cargas_count' => $titular->dependientes->count(),
            ];
        }
    }
@endphp

<div class="container py-5">
    <div class="d-flex justify-content-between mb-4">
        <a href="/escritorio-vendedor" class="btn btn-secondary">
            Volver
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-danger">
                Cerrar sesión
            </button>
        </form>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-body p-5">
                    <h2 class="mb-4">
                        🎟️ Emitir Voucher
                    </h2>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>No se pudo emitir el voucher:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('vouchers.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Voucher para</label>
                            <select name="beneficiario_key"
                                    id="beneficiario_key"
                                    class="form-select"
                                    required>
                                <option value="">Seleccione titular o carga</option>

                                @foreach($beneficiarioOptions as $option)
                                    <option value="{{ $option['key'] }}"
                                            data-titular="{{ $option['titular_nombre'] }}"
                                            data-titular-rut="{{ $option['titular_rut'] }}"
                                            data-telefono="{{ $option['telefono'] }}"
                                            data-email="{{ $option['email'] }}"
                                            data-nombre="{{ $option['nombre'] }}"
                                            data-rut="{{ $option['rut'] }}"
                                            data-parentesco="{{ $option['parentesco'] }}"
                                            data-direccion="{{ $option['direccion'] }}"
                                            data-edad="{{ $option['edad'] }}"
                                            data-cargas-count="{{ $option['cargas_count'] }}"
                                            {{ old('beneficiario_key') === $option['key'] ? 'selected' : '' }}>
                                        {{ $option['tipo'] === 'carga' ? 'Carga' : 'Titular' }}:
                                        {{ $option['nombre'] }} — RUT {{ $option['rut'] ?: 'sin RUT' }}
                                        @if($option['tipo'] === 'carga')
                                            / Titular: {{ $option['titular_nombre'] }}
                                        @elseif($option['cargas_count'] > 0)
                                            / tiene {{ $option['cargas_count'] }} carga(s)
                                        @else
                                            / sin cargas registradas
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                En ISAPRE el voucher debe quedar asociado al titular o a una carga vigente de la base externa.
                            </small>
                            <div id="beneficiarioResumen" class="alert alert-info mt-3 mb-0" style="display:none;"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Titular que compra / autoriza</label>
                            <input type="text"
                                   name="cliente_nombre"
                                   class="form-control"
                                   value="{{ old('cliente_nombre') }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">RUT titular</label>
                            <input type="text"
                                   name="cliente_rut"
                                   class="form-control"
                                   value="{{ old('cliente_rut') }}"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teléfono Cliente / WhatsApp</label>
                            <input type="text"
                                   name="cliente_telefono"
                                   class="form-control"
                                   value="{{ old('cliente_telefono') }}"
                                   placeholder="+569..."
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Cliente (opcional)</label>
                            <input type="email"
                                   name="cliente_email"
                                   class="form-control"
                                   value="{{ old('cliente_email') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Profesional que atenderá</label>
                            <select name="profesional_id"
                                    id="profesional_id"
                                    class="form-select"
                                    required>
                                <option value="">Seleccione profesional</option>

                                @foreach($profesionales as $profesional)
                                    <option value="{{ $profesional->id }}"
                                            data-rut="{{ $profesional->rut_mostrable }}"
                                            data-especialidad="{{ $profesional->especialidad_mostrable }}"
                                            data-email="{{ $profesional->email_mostrable }}"
                                            data-telefono="{{ $profesional->telefono_mostrable }}"
                                            {{ (string) old('profesional_id') === (string) $profesional->id ? 'selected' : '' }}>
                                        {{ $profesional->nombre_mostrable }}
                                        — RUT {{ $profesional->rut_mostrable ?: 'sin RUT' }}
                                        @if($profesional->especialidad_mostrable)
                                            / {{ $profesional->especialidad_mostrable }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                Este dato deja trazabilidad paciente-profesional en el voucher y en el QR.
                            </small>
                            <div id="profesionalResumen" class="alert alert-success mt-3 mb-0" style="display:none;"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Servicio</label>
                            <select name="servicio_id"
                                    id="servicio_id"
                                    class="form-select"
                                    required>
                                <option value="">Seleccione servicio</option>

                                @foreach($servicios as $servicio)
                                    <option value="{{ $servicio->id }}"
                                            data-valor="{{ $servicio->valor_base }}"
                                            data-copago="{{ $servicio->copago_base }}"
                                            data-comision="{{ $servicio->comision_veterchile }}"
                                            {{ (string) old('servicio_id') === (string) $servicio->id ? 'selected' : '' }}>
                                        {{ $servicio->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Valor Total</label>
                            <input id="valor"
                                   name="valor"
                                   class="form-control"
                                   value="{{ old('valor') }}"
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Copago Usuario</label>
                            <input id="copago_usuario"
                                   name="copago_usuario"
                                   class="form-control"
                                   value="{{ old('copago_usuario') }}"
                                   readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Comisión SDI</label>
                            <input id="comision_veterchile"
                                   name="comision_veterchile"
                                   class="form-control"
                                   value="{{ old('comision_veterchile') }}"
                                   readonly>
                        </div>

                        <button class="btn btn-primary btn-lg w-100">
                            Emitir Voucher
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const servicioSelect = document.getElementById('servicio_id');
    const beneficiarioSelect = document.getElementById('beneficiario_key');
    const beneficiarioResumen = document.getElementById('beneficiarioResumen');
    const profesionalSelect = document.getElementById('profesional_id');
    const profesionalResumen = document.getElementById('profesionalResumen');

    servicioSelect.addEventListener('change', function () {
        const opcion = this.options[this.selectedIndex];

        document.getElementById('valor').value = opcion.dataset.valor || 0;
        document.getElementById('copago_usuario').value = opcion.dataset.copago || 0;
        document.getElementById('comision_veterchile').value = opcion.dataset.comision || 0;
    });

    beneficiarioSelect.addEventListener('change', function () {
        const opcion = this.options[this.selectedIndex];

        document.querySelector('[name="cliente_nombre"]').value = opcion.dataset.titular || '';
        document.querySelector('[name="cliente_rut"]').value = opcion.dataset.titularRut || '';
        document.querySelector('[name="cliente_telefono"]').value = opcion.dataset.telefono || '';
        document.querySelector('[name="cliente_email"]').value = opcion.dataset.email || '';

        if (!this.value) {
            beneficiarioResumen.style.display = 'none';
            beneficiarioResumen.innerHTML = '';
            return;
        }

        const cargasCount = Number(opcion.dataset.cargasCount || 0);
        const cargasTexto = cargasCount > 0
            ? `El titular tiene ${cargasCount} carga(s) vigente(s).`
            : 'El titular no tiene cargas vigentes registradas.';

        beneficiarioResumen.style.display = 'block';
        beneficiarioResumen.innerHTML = `
            <strong>Voucher para:</strong> ${opcion.dataset.nombre || 'Sin nombre'}
            <br><strong>RUT beneficiario:</strong> ${opcion.dataset.rut || 'Sin RUT'}
            <br><strong>Tipo:</strong> ${opcion.dataset.parentesco || 'Titular'}
            <br><strong>Titular que autoriza:</strong> ${opcion.dataset.titular || 'Sin titular'}
            <br><span class="text-muted">${cargasTexto}</span>
        `;
    });

    profesionalSelect.addEventListener('change', function () {
        const opcion = this.options[this.selectedIndex];

        if (!this.value) {
            profesionalResumen.style.display = 'none';
            profesionalResumen.innerHTML = '';
            return;
        }

        profesionalResumen.style.display = 'block';
        profesionalResumen.innerHTML = `
            <strong>Profesional:</strong> ${opcion.textContent.trim()}
            <br><strong>RUT:</strong> ${opcion.dataset.rut || 'Sin RUT'}
            <br><strong>Especialidad:</strong> ${opcion.dataset.especialidad || 'Sin especialidad'}
            <br><strong>Contacto:</strong> ${opcion.dataset.telefono || 'Sin teléfono'} ${opcion.dataset.email ? ' · ' + opcion.dataset.email : ''}
        `;
    });

    if (servicioSelect.value) {
        servicioSelect.dispatchEvent(new Event('change'));
    }

    if (beneficiarioSelect.value) {
        beneficiarioSelect.dispatchEvent(new Event('change'));
    }

    if (profesionalSelect.value) {
        profesionalSelect.dispatchEvent(new Event('change'));
    }
</script>

</body>
</html>
