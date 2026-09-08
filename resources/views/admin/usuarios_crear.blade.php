<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f7fb;
        }

        .sdi-card {
            border: 0;
            border-radius: 22px;
            box-shadow: 0 18px 45px rgba(9, 50, 47, .08);
        }

        .section-title {
            color: #006b5f;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
            font-size: .82rem;
        }

        .soft-box {
            background: #f8fffd;
            border: 1px solid #d7ebe7;
            border-radius: 18px;
            padding: 18px;
        }

        .nested-row {
            background: #fff;
            border: 1px solid #e4ecea;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 14px;
        }

        .hint {
            color: #667;
            font-size: .9rem;
        }
    </style>
</head>

@php
    $dependientesOld = old('dependientes', [
        ['nombre' => '', 'rut' => '', 'parentesco' => '', 'fecha_nacimiento' => '', 'direccion' => ''],
    ]);
@endphp

<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="/admin/usuarios" class="btn btn-secondary">
            Volver
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-outline-danger">
                Cerrar sesión
            </button>
        </form>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card sdi-card p-4 p-md-5">
                <div class="mb-4">
                    <div class="section-title">Administración central</div>
                    <h2 class="fw-bold mb-2">Crear usuario</h2>
                    <p class="hint mb-0">
                        Si el usuario es paciente/beneficiario, aquí mismo puedes dejar creadas sus cargas para que luego aparezcan al emitir el bono.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Revisa estos datos:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.usuarios.store') }}">
                    @csrf

                    <div class="soft-box mb-4">
                        <div class="section-title mb-3">Datos del usuario</div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">RUT</label>
                                <input type="text" name="rut" class="form-control" value="{{ old('rut') }}" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Rol</label>
                                <select name="rol" id="rolSelect" class="form-select" required>
                                    <option value="vendedor" {{ old('rol') === 'vendedor' ? 'selected' : '' }}>Vendedor</option>
                                    <option value="profesional" {{ old('rol') === 'profesional' ? 'selected' : '' }}>Profesional</option>
                                    <option value="asistente" {{ old('rol') === 'asistente' ? 'selected' : '' }}>Asistente</option>
                                    <option value="auditor" {{ old('rol') === 'auditor' ? 'selected' : '' }}>Auditor</option>
                                    <option value="admin" {{ old('rol') === 'admin' ? 'selected' : '' }}>Administrador</option>
                                    <option value="cliente" {{ old('rol', 'cliente') === 'cliente' ? 'selected' : '' }}>Cliente / Beneficiario</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vendedor asociado (opcional)</label>
                                <select name="vendedor_id" class="form-select">
                                    <option value="">Sin vendedor</option>
                                    @foreach($vendedores as $vendedor)
                                        <option value="{{ $vendedor->id }}" {{ (string) old('vendedor_id') === (string) $vendedor->id ? 'selected' : '' }}>
                                            {{ $vendedor->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Profesional asociado (opcional)</label>
                                <select name="profesional_id" class="form-select">
                                    <option value="">Sin profesional</option>
                                    @foreach($profesionales as $profesional)
                                        <option value="{{ $profesional->id }}" {{ (string) old('profesional_id') === (string) $profesional->id ? 'selected' : '' }}>
                                            {{ $profesional->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" name="activo" value="1" checked class="form-check-input" id="activo">
                            <label class="form-check-label" for="activo">
                                Usuario activo
                            </label>
                        </div>
                    </div>

                    <div id="clienteExtras">
                        <div class="soft-box mb-4">
                            <div class="section-title mb-3">Datos adicionales del cliente</div>
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Dirección</label>
                                    <input type="text" name="direccion" class="form-control" value="{{ old('direccion') }}">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Fecha de nacimiento</label>
                                    <input type="date" name="fecha_nacimiento" class="form-control" value="{{ old('fecha_nacimiento') }}">
                                </div>
                            </div>
                            <p class="hint mb-0">
                                Estos datos se guardan cifrados en la base de preconsulta para validar relación antes de emitir un bono.
                            </p>
                        </div>

                        <div class="soft-box mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <div class="section-title">Beneficiarios / Cargas</div>
                                    <p class="hint mb-0">Ejemplo: hijo, cónyuge, familiar autorizado. Si no aplica, deja vacío.</p>
                                </div>

                                <button type="button" class="btn btn-outline-success btn-sm" id="addDependiente">
                                    Agregar carga
                                </button>
                            </div>

                            <div id="dependientesContainer">
                                @foreach($dependientesOld as $i => $dependiente)
                                    <div class="nested-row">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Nombre beneficiario/carga</label>
                                                <input type="text" name="dependientes[{{ $i }}][nombre]" class="form-control" value="{{ $dependiente['nombre'] ?? '' }}">
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">RUT</label>
                                                <input type="text" name="dependientes[{{ $i }}][rut]" class="form-control" value="{{ $dependiente['rut'] ?? '' }}">
                                            </div>

                                            <div class="col-md-2 mb-3">
                                                <label class="form-label">Parentesco</label>
                                                <input type="text" name="dependientes[{{ $i }}][parentesco]" class="form-control" value="{{ $dependiente['parentesco'] ?? '' }}" placeholder="Hijo, cónyuge...">
                                            </div>

                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Nacimiento</label>
                                                <input type="date" name="dependientes[{{ $i }}][fecha_nacimiento]" class="form-control" value="{{ $dependiente['fecha_nacimiento'] ?? '' }}">
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label">Dirección</label>
                                                <input type="text" name="dependientes[{{ $i }}][direccion]" class="form-control" value="{{ $dependiente['direccion'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-primary btn-lg w-100">
                        Crear usuario
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const rolSelect = document.getElementById('rolSelect');
    const clienteExtras = document.getElementById('clienteExtras');
    const dependientesContainer = document.getElementById('dependientesContainer');

    let depIndex = {{ count($dependientesOld) }};

    function toggleClienteExtras() {
        clienteExtras.style.display = rolSelect.value === 'cliente' ? 'block' : 'none';
    }

    document.getElementById('addDependiente').addEventListener('click', function () {
        const i = depIndex++;
        dependientesContainer.insertAdjacentHTML('beforeend', `
            <div class="nested-row">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre beneficiario/carga</label>
                        <input type="text" name="dependientes[${i}][nombre]" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">RUT</label>
                        <input type="text" name="dependientes[${i}][rut]" class="form-control">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">Parentesco</label>
                        <input type="text" name="dependientes[${i}][parentesco]" class="form-control" placeholder="Hijo, cónyuge...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Nacimiento</label>
                        <input type="date" name="dependientes[${i}][fecha_nacimiento]" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="dependientes[${i}][direccion]" class="form-control">
                    </div>
                </div>
            </div>
        `);
    });

    rolSelect.addEventListener('change', toggleClienteExtras);
    toggleClienteExtras();
</script>

</body>
</html>
