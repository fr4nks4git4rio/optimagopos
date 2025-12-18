{{--PC MENU--}}
{{--PC MENU--}}
{{--{{$sidebar_with}}--}}
<div class="col-auto px-0 sidebar-menu no-padding hidden-xs border-end border-2 border-top-0"
    :class="sidebar_with" id="sidebar-menu" wire:ignore>
    <div class="d-flex flex-column align-items-center align-items-sm-start pt-2 text-white min-vh-100 bg-custom-light"
        style="min-height: 100vh !important;overflow-y: auto; overflow-x: hidden; height: 100%">
        {{--<a href="/" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none">--}}
        {{--<span class="fs-5 d-none d-sm-inline">Menu</span>--}}
        {{--</a>--}}
        <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100"
            id="menu_principal">
            @if(user()->is_super_admin || user()->is_admin)
            <li class="w-100 pb-2" :class="menu_absolute">
                <a href="#submenu_admin" data-bs-toggle="collapse"
                    class="nav-link align-middle"
                    @if($this->admin_routes_active) aria-expanded="true" @endif>
                    <i class="bi bi-gear fs-6 float-end border border-2 border-dark"
                        title="Administración"></i>
                    <span class="ms-1 d-none text-uppercase fw-semibold fs-6"
                        :class="display"> Administración </span>
                </a>
                <ul class="collapse nav flex-column ms-1 @if($this->admin_routes_active) show @endif"
                    :class="submenu_absolute"
                    id="submenu_admin"
                    data-bs-parent="#submenu_admin">
                    <li class="w-100 li-item {{ active_route('usuarios*') }}">
                        <a href="{{route('usuarios.index')}}"
                            class="nav-link submenu">
                            <i class="bi bi-person fs-6"></i> <span
                                class="d-sm-inline px-2">Usuarios</span></a>
                    </li>
                    @if (user()->is_super_admin)
                    <li class="w-100 li-item {{ active_route('clientes*') }}">
                        <a href="{{route('clientes.index')}}"
                            class="nav-link submenu">
                            <i class="bi bi-people fs-6"></i> <span
                                class="d-sm-inline px-2">Clientes</span></a>
                    </li>
                    @endif
                    @if (user()->is_admin)
                    <li class="w-100 li-item {{ active_route('comensales*') }}">
                        <a href="{{route('comensales.index')}}"
                            class="nav-link submenu">
                            <i class="bi bi-people fs-6"></i> <span
                                class="d-sm-inline px-2">Clientes</span></a>
                    </li>
                    @endif
                    <li class="w-100 li-item {{ active_route('sucursales*') }}">
                        <a href="{{route('sucursales.index')}}"
                            class="nav-link submenu">
                            <i class="bi bi-building fs-6"></i> <span
                                class="d-sm-inline px-2">Sucursales</span></a>
                    </li>
                    @if (user()->is_admin)
                    <li class="w-100 li-item {{ active_route('terminales*') }}">
                        <a href="{{route('terminales.index')}}"
                            class="nav-link submenu">
                            <i class="bi bi-pc-display-horizontal fs-6"></i> <span
                                class="d-sm-inline px-2">Terminales</span></a>
                    </li>
                    @endif
                    <li class="w-100 li-item {{ active_route('trazas*') }}">
                        <a href="{{route('trazas.index')}}"
                            class="nav-link submenu">
                            <i class="bi bi-fingerprint fs-6"></i> <span
                                class="d-sm-inline px-2">Trazas</span></a>
                    </li>
                </ul>
            </li>
            @endif
            @if(user()->is_admin)
            <li class="w-100 pb-2" :class="menu_absolute">
                <a href="#submenu_facturacion" data-bs-toggle="collapse"
                    class="nav-link align-middle"
                    @if($this->facturacion_routes_active) aria-expanded="true" @endif>
                    <i class="bi bi-file-earmark-code fs-6 float-end border border-2 border-dark"
                        title="Administración"></i>
                    <span class="ms-1 d-none text-uppercase fw-semibold fs-6"
                        :class="display"> Facturación </span>
                </a>
                <ul class="collapse nav flex-column ms-1 @if($this->facturacion_routes_active) show @endif"
                    :class="submenu_absolute"
                    id="submenu_facturacion"
                    data-bs-parent="#submenu_facturacion">
                    <li class="w-100 li-item">
                        <a href="javascript:void(0)"
                            wire:click="$emit('openModal', 'panel-pac')"
                            class="nav-link submenu">
                            <i class="bi bi-cart fs-6"></i> <span
                                class="d-sm-inline px-2">Panel PAC</span></a>
                    </li>
                    <li class="w-100 li-item {{ active_route('cabecera-factura*') }}">
                        <a href="{{route('cabecera-factura')}}"
                            class="nav-link submenu">
                            <i class="bi bi-gear fs-6"></i> <span
                                class="d-sm-inline px-2">Cabecera de Facturas</span></a>
                    </li>
                    <li class="w-100 li-item {{ active_route('pre-facturas*') }}">
                        <a href="{{route('pre-facturas.index')}}"
                            class="nav-link submenu">
                            <i class="bi bi-database fs-6"></i> <span
                                class="d-sm-inline px-2">Facturas</span></a>
                    </li>
                    <li class="w-100 li-item {{ active_route('almacen-facturas*') }}">
                        <a href="{{route('almacen-facturas.index')}}"
                            class="nav-link submenu">
                            <i class="bi bi-database-check fs-6"></i> <span
                                class="d-sm-inline px-2">Almacén de Facturas</span></a>
                    </li>
                </ul>
            </li>
            <li class="w-100 pb-2" :class="menu_absolute">
                <a href="#submenu_reportes" data-bs-toggle="collapse"
                    class="nav-link align-middle"
                    @if($this->reportes_routes_active) aria-expanded="true" @endif>
                    <i class="bi bi-file-earmark-code fs-6 float-end border border-2 border-dark"
                        title="Reportes"></i>
                    <span class="ms-1 d-none text-uppercase fw-semibold fs-6"
                        :class="display"> Reportes </span>
                </a>
                <ul class="collapse nav flex-column ms-1 @if($this->reportes_routes_active) show @endif"
                    :class="submenu_absolute"
                    id="submenu_reportes"
                    data-bs-parent="#submenu_reportes">
                    <li class="w-100 li-item {{ active_route('reportes/tickets*') }}">
                        <a href="{{route('reportes.tickets')}}"
                            class="nav-link submenu">
                            <i class="bi bi-cart fs-6"></i> <span
                                class="d-sm-inline px-2">Tickets</span></a>
                    </li>
                </ul>
            </li>
            @endif
        </ul>
        {{--<hr>--}}
        {{--<div class="dropdown pb-4">--}}
        {{--<a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"--}}
        {{--id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">--}}
        {{--<img src="https://github.com/mdo.png" alt="hugenerd" width="30" height="30" class="rounded-circle">--}}
        {{--<span class="d-none {{$display}} mx-1">loser</span>--}}
        {{--</a>--}}
        {{--<ul class="dropdown-menu dropdown-menu-dark text-small shadow">--}}
        {{--<li><a class="dropdown-item" href="#">New project...</a></li>--}}
        {{--<li><a class="dropdown-item" href="#">Settings</a></li>--}}
        {{--<li><a class="dropdown-item" href="#">Profile</a></li>--}}
        {{--<li>--}}
        {{--<hr class="dropdown-divider">--}}
        {{--</li>--}}
        {{--<li><a class="dropdown-item" href="#">Sign out</a></li>--}}
        {{--</ul>--}}
        {{--</div>--}}
    </div>
</div>
