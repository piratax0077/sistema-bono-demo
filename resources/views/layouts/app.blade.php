<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        @if (file_exists(public_path('build/manifest.json')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        @endif
    </head>
    <body class="font-sans antialiased">
        @include('partials.demo_user_switcher')
        <div class="min-h-screen bg-gray-100">
            @unless(View::hasSection('ocultar-navegacion-app'))
                @include('layouts.navigation')

            <!-- Page Heading -->
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    @if (isset($header))
                        {{ $header }}
                    @endif
                </div>
            </header>
            @endunless

            <!-- Page Content -->
            <main>
               @if (isset($slot))
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </main>
        </div>
        <script>
            function sdiInicializarMenus() {
                if (window.Alpine) {
                    return;
                }

                document.querySelectorAll('[data-sdi-dropdown]').forEach(function (dropdown) {
                    if (dropdown.dataset.sdiDropdownReady === '1') {
                        return;
                    }
                    var trigger = dropdown.querySelector('[data-sdi-dropdown-trigger]');
                    var menu = dropdown.querySelector('[data-sdi-dropdown-menu]');
                    if (!trigger || !menu) {
                        return;
                    }
                    dropdown.dataset.sdiDropdownReady = '1';
                    trigger.addEventListener('click', function (event) {
                        event.stopPropagation();
                        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
                    });
                });

                document.addEventListener('click', function (event) {
                    document.querySelectorAll('[data-sdi-dropdown]').forEach(function (dropdown) {
                        if (!dropdown.contains(event.target)) {
                            var menu = dropdown.querySelector('[data-sdi-dropdown-menu]');
                            if (menu) {
                                menu.style.display = 'none';
                            }
                        }
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', sdiInicializarMenus);
            } else {
                sdiInicializarMenus();
            }
        </script>
@include('partials.demo_footer')
</body>
</html>
