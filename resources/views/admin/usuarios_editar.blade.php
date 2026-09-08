<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:#f4f7fb;">

<div class="container py-5">

    <a href="/admin/usuarios" class="btn btn-secondary mb-4">
        Volver
    </a>

    <div class="card p-5 border-0 shadow-sm">

        <h2 class="fw-bold mb-4">Editar Usuario</h2>
        <form method="POST"
                action="/admin/usuarios/{{ $usuario->id }}/actualizar"

                @csrf

            {{--  <div class="mb-3">
                <label>Nombre</label>
                <input type="text"
                    name="name"  class="form-control"  value="{{ $usuario->name }}" required>



            </div>  --}}
            <div class="mb-3">
                <label>Nombre</label>

<input type="text"
       name="name"
       class="form-control"
       value="{{ old('name', $usuario->name) }}"
       required>


            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email"
                    name="email"  class="form-control"  value="{{ $usuario->email }}" required>



            </div>

            <div class="mb-3">
                <label>RUT</label>
                <input type="text"
                    name="rut"
                    class="form-control"
                    value="{{ $usuario->rut }}">
            </div>

            <div class="mb-3">
                <label>Teléfono</label>
                <input type="text"
                    name="telefono"
                    class="form-control"
                    value="{{ $usuario->telefono }}">
            </div>

            <div class="mb-3">
                <label>Nueva contraseña</label>
                <input type="password"
                    name="password"
                    class="form-control"
                    placeholder="Dejar vacío para no cambiar">
            </div>

            <div class="mb-3">
                <label>Rol</label>

                <select name="rol" class="form-select">

                    <option value="admin" {{ $usuario->rol == 'admin' ? 'selected' : '' }}>Admin</option>

                    <option value="vendedor" {{ $usuario->rol == 'vendedor' ? 'selected' : '' }}>Vendedor</option>

                    <option value="profesional" {{ $usuario->rol == 'profesional' ? 'selected' : '' }}>Profesional</option>

                    <option value="auditor" {{ $usuario->rol == 'auditor' ? 'selected' : '' }}>Auditor</option>

                    <option value="cliente" {{ $usuario->rol == 'cliente' ? 'selected' : '' }}>Paciente / Beneficiario</option>

                </select>
            </div>

            <div class="mb-3">
                <label>Vendedor asociado</label>

                <select name="vendedor_id" class="form-select">
                    <option value="">Sin vendedor</option>

                    @foreach($vendedores as $vendedor)
                        <option value="{{ $vendedor->id }}"
                            {{ $usuario->vendedor_id == $vendedor->id ? 'selected' : '' }}>
                            {{ $vendedor->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Profesional asociado</label>

                <select name="profesional_id" class="form-select">
                    <option value="">Sin profesional</option>

                    @foreach($profesionales as $profesional)
                        <option value="{{ $profesional->id }}"
                            {{ $usuario->profesional_id == $profesional->id ? 'selected' : '' }}>
                            {{ $profesional->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-check mb-4">
                <input type="checkbox"
                    name="activo"
                    value="1"
                    class="form-check-input"
                    id="activo"
                    {{ $usuario->activo ? 'checked' : '' }}>

                <label class="form-check-label" for="activo">
                    Usuario activo
                </label>
            </div>

            {{--  <button type="submit"
                    class="btn btn-primary w-100">
                Guardar Cambios
            </button>  --}}
            <p style="color:red">
             FORM TEST
            </p>
            <input type="submit"
            value="GUARDAR CAMBIOS"
            class="btn btn-danger w-100">

        </form>

    </div>

</div>

</body>
</html>
