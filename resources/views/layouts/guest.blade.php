<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <style>
        ::root {
            --color-primary: #065F46;
            --color-primary-subtle: #c5f7e9;
        }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    {{-- Estilos movidos a resources/css/layout-guest.css (via entry app.css) --}}
    @stack('styles')

    <!-- Alpine Plugins (el core lo inyecta Livewire 3 via @livewireScripts) -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="font-sans text-gray-900 antialiased">
    <div id="app" class="container-fluid">
        <div>
            <a href="/">
                <x-application-logo class="logo" />
            </a>
        </div>

        <div class="row flex-nowrap">
            <main class="py-4 col">
                {{ $slot }}
            </main>
        </div>
    </div>
    @stack('scripts')
</body>

</html>
