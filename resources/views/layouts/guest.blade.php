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

    <script src="https://cdn.ckeditor.com/ckeditor5/27.1.0/classic/ckeditor.js"></script>

    @livewireStyles

    <style>
        body {
            /* font-size: 16px; */
        }

        input[type="number"] {
            text-align: end;
        }

        body::-webkit-scrollbar {
            -webkit-appearance: none;
        }

        body::-webkit-scrollbar:vertical {
            width: 10px;
        }

        body::-webkit-scrollbar-button:increment,
        .contenedor::-webkit-scrollbar-button {
            display: none;
        }

        body::-webkit-scrollbar:horizontal {
            height: 10px;
        }

        body::-webkit-scrollbar-thumb {
            background-color: #797979;
            border-radius: 20px;
            border: 2px solid #f1f2f3;
        }

        body::-webkit-scrollbar-track {
            border-radius: 10px;
        }

        div._jw-tpk-hour ol,
        div._jw-tpk-minute ol {
            margin-bottom: 0 !important;
            padding-left: 0 !important;
        }

        .modal.fade.show {
            z-index: 1051 !important;
        }

        .mr-1 {
            margin-right: 0.25rem !important;
        }

        .dropdown-toggle.notifications::after {
            display: none !important;
        }

        .notifications-dropdown .dropdown-menu {
            top: 42px;
            right: 0;
            left: unset !important;
            width: 460px;
            box-shadow: 0 5px 7px -1px #c1c1c1;
            padding: 0;
            max-height: 90vh;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .dropdown-menu:not(.dropdown-menu-button-group):before {
            content: "";
            position: absolute;
            top: -20px;
            right: 12px;
            border: 10px solid #fff;
            border-color: transparent transparent #fff transparent;
        }

        .notifications-dropdown .head {
            padding: 5px 15px;
            border-radius: 3px 3px 0px 0px;
        }

        .notifications-dropdown .footer {
            padding: 5px 15px;
            border-radius: 0px 0px 3px 3px;
        }

        .notifications-dropdown .notification-box {
            padding: 10px 0px;
        }

        .bg-gray {
            background-color: #eee;
        }

        .loading,
        .loading>img {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            margin: auto;
            z-index: 999999;
        }

        .loading {
            background: rgb(0, 0, 0, 0.5);
        }

        @media (max-width: 640px) {
            .notifications-dropdown .dropdown-menu {
                top: 50px;
                left: -16px;
                width: 290px;
            }

            .notifications-dropdown .nav {
                display: block;
            }

            .notifications-dropdown .nav .nav-item,
            .nav .nav-item a {
                padding-left: 0px;
            }

            .notifications-dropdown .message {
                font-size: 13px;
            }
        }
    </style>
    @stack('styles')

    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>

    <!-- Alpine Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
