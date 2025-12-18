<nav class="navbar navbar-expand-lg navbar-light py-2">
    <div class="container-fluid">
        <a href="{{ url('/') }}" class="">
            <!-- LOGO -->
            {{-- {{$class_logo}} --}}
            <div class="topbar-left hidden-xs " :class="class_logo">
                <div class="text-center">
                    <a href="{{ url('/') }}" class="logo">
                        <h1 class="fs-3" x-show="show_title">Optima Go Pos</h1>
                        <h1 class="fs-2" x-show="!show_title">OGP</h1>
                        <!-- <img x-show="show_title" src="{{ asset('images/bretail.png') }}" alt="">
                        <img x-show="!show_title" src="{{ asset('images/bretail-sm.png') }}" alt=""> -->
                    </a>
                </div>
            </div>
            <div class="topbar-left hidden-lg">
                <div class="text-center">
                    <a href="{{ url('/') }}" class="logo">
                        {{-- <img x-show="show_title" src="images/bretail.png" alt=""> --}}
                        <h1 class="fs-2">KB</h1>
                        <!-- <img src="{{ asset('images/bretail-sm.png') }}" alt=""> -->
                    </a>
                </div>
            </div>
            {{-- <div class="topbar-left" --}}
            {{-- style="background-image: linear-gradient(to right, #ffad3b , #ffeed6 ,  #ffeed6, #ffad3b );"> --}}
            {{-- <img src="{{ asset('images/pm_logo_2.png') }}?v={{ config('app.version') }}" --}}
            {{-- alt="{{ config('app.name') }}" height="40"> --}}
            {{-- <strong>{{config('app.name')}}</strong> --}}
            {{-- </div> --}}
        </a>
        <!-- Mobile Menu Toggle Button -->
        {{-- <button class="navbar-toggler d-block m-sm-2" @click="toggleClicked()" --}}
        {{-- type="button" data-bs-toggle="" style="z-index: 9999991" --}}
        {{-- data-bs-target="#sidebar-menu" aria-controls="sidebar-menu"> --}}
        {{-- --}}{{-- <span class="navbar-toggler-icon"></span> --}}
        {{-- <span class="fa fa-bars text-white"></span> --}}
        {{-- @if ($this->profile_exist) --}}
        {{-- <span class="text-white">&nbsp;{{$this->profile_role}}</span> --}}
        {{-- @endif --}}
        {{-- </button> --}}

        <button @click="toggleClicked()" class="navbar-toggler d-block" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false"
            aria-label="Toggle navigation" style="margin-left: 7px">
            <div style="transform: rotate(90deg)">
                <span class="bi bi-bar-chart text-white"></span>
            </div>
        </button>

        <div id="nav" class="navbar-collapse">
            <div class="d-flex gap-3 w-100" :class="appbar_user_menu">
                @guest
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="nav-link">{{ __('Login') }}</a>
                    @endif

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="nav-link">{{ __('Register') }}</a>
                    @endif
                @else
                    {{-- <a href="{{ url('/home') }}" class="nav-link">{{ __('Home') }}</a> --}}

                    {{-- @if (Route::has('users')) --}}
                    {{-- <a href="{{ route('users') }}" class="nav-link">{{ __('Users') }}</a> --}}
                    {{-- @endif --}}
                    {{-- <div class="nav-item mr-2"> --}}
                    {{-- <a href="#" class="nav-link fs-5"> --}}
                    {{-- <i class="bi bi-gear"></i> --}}
                    {{-- </a> --}}
                    {{-- </div> --}}
                    <div class="nav-item mr-2">
                        <livewire:layouts.tipo-cambio />
                    </div>
                    <div class="nav-item dropdown notifications-dropdown mr-2">
                        <a href="#" class="nav-link dropdown-toggle notifications me-2" data-bs-toggle="dropdown">
                            <i
                                class="pt-1 bi @if (count($notifications) > 0) bi-bell text-warning @else bi-bell-slash @endif fs-5"></i>
                            @if (count($notifications) > 0)
                                <span class="badge bg-danger rounded-circle position-absolute"
                                    style="top: 16px; font-size: 9px">{{ count($notifications) }}</span>
                            @endif
                        </a>
                        @if (count($notifications) > 0)
                            <ul class="dropdown-menu">
                                <li class="head text-light bg-site-primary">
                                    <div class="row">
                                        <div class="col-lg-12 col-sm-12 col-12">
                                            <span>Notificaciones {{ count($notifications) }}</span>
                                            <a href="javascript:void(0)" wire:click="$emit('markNotificationsAllAsRead')"
                                                class="float-end text-light">Marcar como leidas</a>
                                        </div>
                                    </div>
                                </li>
                                @foreach ($notifications as $key => $notification)
                                    <li class="notification-box @if ($key % 2 > 0) bg-gray @endif">
                                        <div class="row m-0">
                                            <div class="col-lg-2 col-sm-2 col-2 text-center no-padding">
                                                <img src="{{ isset($notification->data['img']) && $notification->data['img'] != ''
                                                    ? asset($notification->data['img'])
                                                    : asset('img/no_avatar.png') }}"
                                                    class="rounded-circle img-thumbnail"
                                                    style="height: 80px; width: 80px; object-fit: cover">
                                            </div>
                                            <div class="col-lg-10 col-sm-10 col-10 no-padding-right">
                                                <strong
                                                    class="text-site-primary">{{ $notification->data['title'] }}</strong>
                                                <div>
                                                    {{ $notification->data['message'] }}
                                                </div>
                                                @if ($notification->data['link'])
                                                    <a href="javascript:void(0)" class="float-start ml-3"
                                                        style="text-decoration: none"
                                                        wire:click="goToLink('{{ $notification->id }}')"><small
                                                            class="text-danger">Ver</small></a>
                                                @endif
                                                <a href="javascript:void(0)" class="float-end mr-3"
                                                    style="text-decoration: none"
                                                    wire:click="$emit('markNotificationAsRead', '{{ $notification->id }}')"><small
                                                        class="text-danger">Marcar como leída</small></a>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                                {{-- <li class="footer bg-red text-center"> --}}
                                {{-- <a href="#" class="text-light">View All</a> --}}
                                {{-- </li> --}}
                            </ul>
                        @endif
                    </div>

                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle fs-5" data-bs-toggle="dropdown">
                            <img src="{{ user()->avatar_uri ? asset(user()->avatar_uri) : '/img/avatars/no_avatar.png' }}"
                                class="user-image" style="object-fit: cover" alt="User Image"><span
                                class="hidden-xs">{{ user()->nombre_completo }}</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <x-dropdown-item label="Modificar Perfil" click="$emit('openModal', 'auth.update-profile')" />

                            <x-dropdown-item label="Cambiar Contraseña"
                                click="$emit('openModal', 'auth.change-password')" />

                            <x-dropdown-item :label="__('Logout')" click="logout" />
                        </div>
                    </div>
                @endguest
            </div>
        </div>
    </div>
    {{-- MOVILE MENU --}}
    {{-- {{$movile_menu_hidden}} --}}
    <div id="header-menu-mobile" class="header-menu-mobile hidden-lg bg-custom-light col-auto px-2 sidebar-menu pt-2"
        :class="movile_menu_hidden">
        <div class="d-flex flex-column align-items-center align-items-sm-start pt-2 text-white min-vh-100">
            <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100"
                id="menu_principal">
                {{-- //TODO implementar menu lateral --}}
                <li class="w-100 li-item {{ active_route('usuarios*') }}">
                    <a href="{{ route('usuarios.index') }}" class="nav-link submenu">
                        <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">Usuarios</span></a>
                </li>
                <li class="w-100 li-item {{ active_route('trazas*') }}">
                    <a href="{{ route('trazas.index') }}" class="nav-link submenu">
                        <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">Trazas</span></a>
                </li>
            </ul>
        </div>
    </div>
</nav>
