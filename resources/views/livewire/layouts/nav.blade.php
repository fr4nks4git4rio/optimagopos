<nav class="navbar navbar-expand-lg navbar-light py-2">
    <div class="container-fluid">
        <a href="{{ url('/') }}" class="">
            <!-- LOGO -->
            <div class="topbar-left hidden-xs " :class="class_logo">
                <div class="text-center">
                    <a href="{{ url('/') }}" class="logo">
                        <h1 class="fs-3">{{ config('app.name') }}</h1>
                    </a>
                </div>
            </div>
        </a>
        <!-- Mobile Menu Toggle Button -->
        <button @click="toggleClicked()" class="navbar-toggler d-block" type="button" aria-label="Toggle navigation"
            style="margin-left: 7px">
            <div style="transform: rotate(90deg)">
                <span class="bi bi-bar-chart text-white"></span>
            </div>
        </button>

        <div id="nav" class="navbar-collapse">
            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 gap-lg-3 w-100"
                :class="appbar_user_menu">
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
                    @if (user()->hasAnyRole(['SuperAdmin', 'Accountant']))
                        <div class="nav-item flex-shrink-0 mr-2">
                            <livewire:layouts.tipo-cambio-sistema />
                        </div>
                    @endif
                    <div class="nav-item flex-shrink-0 dropdown notifications-dropdown mr-2">
                        <a class="nav-link position-relative px-2" data-bs-toggle="dropdown">

                            <i class="bi bi-bell fs-4"></i>

                            @if (count($notifications))
                                <span
                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ count($notifications) }}
                                </span>
                            @endif
                        </a>
                        @if (count($notifications) > 0)
                            <ul class="dropdown-menu dropdown-menu-end shadow notification-menu">
                                <li class="head text-light bg-site-primary">
                                    <div class="row">
                                        <div class="col-lg-12 col-sm-12 col-12">
                                            <span>{{ __('nav.notifications') }} {{ count($notifications) }}</span>
                                            <a href="javascript:void(0)"
                                                wire:click="$dispatch('markNotificationsAllAsRead')"
                                                class="float-end text-light">{{ __('site.nav.mark-as-reads') }}</a>
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
                                                    class="rounded-circle img-thumbnail notification-avatar" loading="lazy"
                                                    decoding="async">
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
                                                            class="text-danger">{{ __('site.nav.see') }}</small></a>
                                                @endif
                                                <a href="javascript:void(0)" class="float-end mr-3"
                                                    style="text-decoration: none"
                                                    wire:click="$dispatch('markNotificationAsRead', '{{ $notification->id }}')"><small
                                                        class="text-danger">{{ __('site.nav.mark-as-read') }}</small></a>
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

                    <div class="nav-item flex-shrink-0">
                        <a href="javascript:void(0)" class="nav-link position-relative" @click="openHelp = !openHelp">
                            <span class="bi bi-book text-white fs-4"></span>
                        </a>
                    </div>

                    <div class="nav-item flex-shrink-0 dropdown">
                        <a class="nav-link dropdown-toggle px-2 cursor-pointer" data-bs-toggle="dropdown">
                            {{ strtoupper(user()->lang ?? config('app.locale')) }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            @foreach ($langs as $index => $lang)
                                <x-dropdown-item label="{{ $lang }}" click="changeLang('{{ $index }}')" />
                            @endforeach
                        </div>
                    </div>
                    <div class="nav-item flex-shrink-0 dropdown">
                        <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2 px-2"
                            data-bs-toggle="dropdown">

                            <img src="{{ user()->avatar_uri ? asset(user()->avatar_uri) : '/img/avatars/no_avatar.png' }}"
                                class="rounded-circle" width="36" height="36" style="object-fit:cover">

                            <span class="d-none d-lg-inline text-truncate nav-user-name" style="max-width:160px;">
                                {{ user()->nombre_completo }}
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <x-dropdown-item label="{{ __('site.nav.update-profile') }}"
                                click="$dispatch('openModal', { component: 'auth.update-profile' })" />

                            <x-dropdown-item label="{{ __('site.nav.change-password') }}"
                                click="$dispatch('openModal', { component: 'auth.change-password' })" />

                            @can('myCompany-view')
                                <x-dropdown-item label="{{ __('site.nav.my-company') }}"
                                    click="$dispatch('openModal', { component: 'auth.my-company'})" />
                            @endcan

                            <x-dropdown-item :label="__('Logout')" click="logout" />
                        </div>
                    </div>
                @endguest
            </div>
        </div>
    </div>
</nav>
