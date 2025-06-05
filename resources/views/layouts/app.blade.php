<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

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

        body::-webkit-scrollbar-button:increment, .contenedor::-webkit-scrollbar-button {
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
        .loading > img {
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

            .notifications-dropdown .nav .nav-item, .nav .nav-item a {
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
<body x-data="{
    display: 'd-sm-inline',
    sidebar_with: 'sidebar-width',
    appbar_user_menu: 'justify-content-end',
    class_logo: '',
    movile_menu_hidden: 'left-hidden',
    show_title: true,
    menu_absolute: '',
    submenu_absolute: 'w-100',
    is_mobile_screen: false,
    toggleClicked(){
        this.sidebar_with = !this.sidebar_with ? 'sidebar-width' : '';
        this.display = !this.display ? 'd-sm-inline' : '';
        this.show_title = !this.show_title;
        this.class_logo = !this.class_logo ? 'small' : '';
        this.movile_menu_hidden = !this.movile_menu_hidden ? 'left-hidden' : '';
        this.menu_absolute = !this.menu_absolute ? 'menu-absolute' : '';
        this.submenu_absolute = this.submenu_absolute == 'w-100' ? 'submenu-absolute' : 'w-100';
    },
    setModeScreen(){
        this.is_mobile_screen = window.screen.width < 992;
        if(this.is_mobile_screen){
            this.appbar_user_menu = 'justify-content-between mt-2'
        }
        $.get('https://6do9tah.localto.net/api/clientes_service', function (data) {
            $.post('/consumir_clientes_service', {data}, function (data) {
                console.log(data);
            });
        });
        $.get('https://6do9tah.localto.net/api/operadores_service', function (data) {
            $.post('/consumir_operadores_service', {data}, function (data) {
                console.log(data);
            });
        })
    }
}" x-init="setModeScreen()">
<div id="app">
    <livewire:layouts.toast/>
    @if(auth()->user())
        @livewire('layouts.nav')
    @endif
    <div class="container-fluid">
        <div class="row flex-nowrap">
            @if(auth()->user())
                @livewire('layouts.sidebar')
            @endif
            <main class="py-4 col">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</div>
<script>
    Livewire.on('flashMessage', () => {
        $('.alert').css('display', 'block !important').delay(3000).slideUp(600);
    });
    Livewire.on('uncheckCheckbox', (param) => {
        $('#' + param)[0].checked = false;
    });
    Livewire.on('checkCheckbox', (param) => {
        $('#' + param)[0].checked = true;
    });
</script>
@stack('scripts')
@livewire('livewire-ui-modal')
</body>
</html>
