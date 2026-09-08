<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SDI · Salud Digital Integrada</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="/escritorio-admin">
            SDI Administración
        </a>

        <div class="d-flex">
            <a href="/escritorio-admin" class="btn btn-outline-light btn-sm me-2">
                Dashboard
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-danger btn-sm">
                    Salir
                </button>
            </form>
        </div>
    </div>
</nav>

<main>
    @yield('content')
</main>

</body>
</html>
