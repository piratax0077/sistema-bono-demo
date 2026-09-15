<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Auditoría de accesos | Medichile Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #edf4ff;
            color: #17263a;
        }

        .page-shell {
            padding: 34px 28px 56px;
        }

        .card-soft {
            background: #fff;
            border: 1px solid #ceddf5;
            border-radius: 22px;
            box-shadow: 0 12px 30px rgba(31, 78, 142, .06);
        }

        .eyebrow {
            color: #178b9d;
            font-size: .9rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .summary-value {
            color: #17263a;
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        .security-note {
            background: #e8f2ff;
            border: 1px solid #c8dcfb;
            border-radius: 16px;
            color: #194c94;
        }

        .table thead th {
            background: #f6f9fe;
            color: #4b607d;
            font-size: .78rem;
            letter-spacing: .04em;
            padding: 16px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .table tbody td {
            border-color: #e1e9f5;
            padding: 16px;
            vertical-align: middle;
        }

        .user-agent {
            color: #62738b;
            font-size: .84rem;
            line-height: 1.4;
            max-width: 460px;
            overflow-wrap: anywhere;
        }

        .result-badge {
            border-radius: 999px;
            display: inline-flex;
            font-size: .75rem;
            font-weight: 800;
            padding: .42rem .72rem;
        }

        @media (max-width: 767.98px) {
            .page-shell {
                padding: 22px 12px 40px;
            }
        }
    </style>
</head>
<body>
    @include('partials.demo_user_switcher')

    @php
        $totalAccesos = $logins->count();
        $accesosExitosos = $logins->where('resultado', 'exitoso')->count();
        $accesosBloqueados = $logins->where('resultado', 'bloqueado_rate_limit')->count();
        $accesosFallidos = $logins->where('resultado', 'fallido')->count();
    @endphp

    <main class="page-shell container-fluid">
        <section class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 mb-4">
            <div>
                <div class="eyebrow mb-1">Seguridad y control de acceso</div>
                <h1 class="display-6 fw-bold mb-2">Auditoría de inicios de sesión</h1>
                <p class="text-secondary fs-5 mb-0">
                    Revisa intentos de acceso, cierres de sesión y bloqueos preventivos registrados por el sistema.
                </p>
            </div>

        </section>

        <section class="security-note p-3 p-md-4 mb-4">
            <div class="d-flex gap-3 align-items-start">
                <span class="fs-4" aria-hidden="true">🔐</span>
                <div>
                    <strong>Registro trazable de autenticación.</strong>
                    Cada evento conserva fecha, identidad declarada, resultado, dirección IP y contexto del navegador para apoyar la detección de accesos anómalos.
                </div>
            </div>
        </section>

        <section class="row g-3 mb-4">
            <div class="col-6 col-xl-3">
                <div class="card-soft h-100 p-3 p-md-4">
                    <div class="text-secondary mb-2">Eventos revisados</div>
                    <div class="summary-value">{{ $totalAccesos }}</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card-soft h-100 p-3 p-md-4">
                    <div class="text-secondary mb-2">Accesos exitosos</div>
                    <div class="summary-value text-success">{{ $accesosExitosos }}</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card-soft h-100 p-3 p-md-4">
                    <div class="text-secondary mb-2">Intentos fallidos</div>
                    <div class="summary-value text-warning">{{ $accesosFallidos }}</div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="card-soft h-100 p-3 p-md-4">
                    <div class="text-secondary mb-2">Bloqueos preventivos</div>
                    <div class="summary-value text-danger">{{ $accesosBloqueados }}</div>
                </div>
            </div>
        </section>

        <section class="card-soft overflow-hidden">
            <div class="p-4 border-bottom">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-8">
                        <label for="loginSearch" class="form-label fw-semibold">Buscar un evento</label>
                        <input
                            id="loginSearch"
                            type="search"
                            class="form-control form-control-lg"
                            placeholder="Correo, dirección IP o navegador"
                        >
                    </div>
                    <div class="col-lg-4">
                        <label for="resultFilter" class="form-label fw-semibold">Resultado</label>
                        <select id="resultFilter" class="form-select form-select-lg">
                            <option value="">Todos los resultados</option>
                            <option value="exitoso">Exitoso</option>
                            <option value="fallido">Fallido</option>
                            <option value="bloqueado_rate_limit">Bloqueado</option>
                            <option value="logout">Cierre de sesión</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table mb-0" id="loginTable">
                    <thead>
                        <tr>
                            <th>Fecha y hora</th>
                            <th>Identidad</th>
                            <th>Resultado</th>
                            <th>Dirección IP</th>
                            <th>Contexto del navegador</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logins as $login)
                            @php
                                $resultado = $login->resultado ?? 'fallido';
                                $resultadoVisual = match ($resultado) {
                                    'exitoso' => ['Acceso exitoso', 'bg-success-subtle text-success'],
                                    'logout' => ['Cierre de sesión', 'bg-secondary-subtle text-secondary'],
                                    'bloqueado_rate_limit' => ['Bloqueado', 'bg-danger-subtle text-danger'],
                                    default => ['Intento fallido', 'bg-warning-subtle text-warning-emphasis'],
                                };
                                $textoBusqueda = strtolower(implode(' ', [
                                    $login->email ?? '',
                                    $login->ip ?? '',
                                    $login->user_agent ?? '',
                                ]));
                            @endphp
                            <tr class="login-row" data-search="{{ $textoBusqueda }}" data-result="{{ $resultado }}">
                                <td class="text-nowrap fw-semibold">
                                    {{ optional($login->created_at)->format('d-m-Y H:i:s') ?? 'Sin fecha' }}
                                </td>
                                <td>{{ $login->email ?: 'No informada' }}</td>
                                <td>
                                    <span class="result-badge {{ $resultadoVisual[1] }}">
                                        {{ $resultadoVisual[0] }}
                                    </span>
                                </td>
                                <td><code>{{ $login->ip ?: 'No registrada' }}</code></td>
                                <td><div class="user-agent">{{ $login->user_agent ?: 'Sin información de navegador' }}</div></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-5">
                                    No hay eventos de autenticación registrados.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="noFilteredResults" class="d-none">
                            <td colspan="5" class="text-center text-secondary py-5">
                                No hay eventos que coincidan con los filtros seleccionados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        (() => {
            const searchInput = document.getElementById('loginSearch');
            const resultFilter = document.getElementById('resultFilter');
            const rows = Array.from(document.querySelectorAll('.login-row'));
            const emptyRow = document.getElementById('noFilteredResults');

            const applyFilters = () => {
                const query = searchInput.value.trim().toLowerCase();
                const result = resultFilter.value;
                let visible = 0;

                rows.forEach((row) => {
                    const matchesQuery = !query || row.dataset.search.includes(query);
                    const matchesResult = !result || row.dataset.result === result;
                    const show = matchesQuery && matchesResult;

                    row.classList.toggle('d-none', !show);
                    if (show) visible += 1;
                });

                emptyRow.classList.toggle('d-none', visible > 0 || rows.length === 0);
            };

            searchInput.addEventListener('input', applyFilters);
            resultFilter.addEventListener('change', applyFilters);
        })();
    </script>
</body>
</html>
