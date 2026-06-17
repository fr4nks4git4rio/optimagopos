{{-- PC MENU --}}
{{-- PC MENU --}}
{{-- {{$sidebar_with}} --}}
<div class="col-auto px-0 sidebar-menu no-padding hidden-xs border-end border-2 border-top-0" :class="sidebar_with"
    id="sidebar-menu" wire:ignore>
    <div class="d-flex flex-column align-items-center align-items-sm-start pt-2 text-white min-vh-100 bg-custom-light"
        style="min-height: 100vh !important;overflow-y: auto; overflow-x: hidden; height: 100%">
        {{-- <a href="/" class="d-flex align-items-center pb-3 mb-md-0 me-md-auto text-white text-decoration-none"> --}}
        {{-- <span class="fs-5 d-none d-sm-inline">Menu</span> --}}
        {{-- </a> --}}
        <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100"
            id="menu_principal">
            @if (user()->is_super_admin)
                <li class="w-100 pb-2" :class="menu_absolute">
                    <a href="#submenu_admin" data-bs-toggle="collapse" class="nav-link align-middle"
                        @if ($this->admin_routes_active) aria-expanded="true" @endif>
                        <i class="bi bi-gear fs-6 float-end border border-2 border-dark" title="Administración"></i>
                        <span class="ms-1 d-none text-uppercase fw-semibold fs-6" :class="display"> Administración
                        </span>
                    </a>
                    <ul class="collapse nav flex-column ms-1 @if ($this->admin_routes_active) show @endif"
                        :class="submenu_absolute" id="submenu_admin" data-bs-parent="#submenu_admin">
                        @can('viewAny', [App\Models\User::class])
                            <li class="w-100 li-item {{ active_route('admin/usuarios*') }}">
                                <a href="{{ route('admin.usuarios.index') }}" class="nav-link submenu">
                                    <i class="bi bi-person fs-6"></i> <span class="d-sm-inline px-2">Usuarios</span></a>
                            </li>
                        @endcan
                        @can('viewAnyCliente', [App\Models\Cliente::class])
                            <li class="w-100 li-item {{ active_route('admin/clientes*') }}">
                                <a href="{{ route('admin.clientes.index') }}" class="nav-link submenu">
                                    <i class="bi bi-people fs-6"></i> <span class="d-sm-inline px-2">Clientes</span></a>
                            </li>
                        @endcan
                        @can('viewAny', [App\Models\Sucursal::class])
                            <li class="w-100 li-item {{ active_route('admin/sucursales*') }}">
                                <a href="{{ route('admin.sucursales.index') }}" class="nav-link submenu">
                                    <i class="bi bi-building fs-6"></i> <span class="d-sm-inline px-2">Sucursales</span></a>
                            </li>
                        @endcan
                        @can('viewAny', [App\Models\Terminal::class])
                            <li class="w-100 li-item {{ active_route('admin/terminales*') }}">
                                <a href="{{ route('admin.terminales.index') }}" class="nav-link submenu">
                                    <i class="bi bi-pc-display-horizontal fs-6"></i> <span
                                        class="d-sm-inline px-2">Terminales</span></a>
                            </li>
                        @endcan
                        <li class="w-100 li-item {{ active_route('admin/trazas*') }}">
                            <a href="{{ route('admin.trazas.index') }}" class="nav-link submenu">
                                <i class="bi bi-fingerprint fs-6"></i> <span class="d-sm-inline px-2">Trazas</span></a>
                        </li>
                    </ul>
                </li>
            @else
                <li class="w-100 pb-2" :class="menu_absolute">
                    <a href="#submenu_admin" data-bs-toggle="collapse" class="nav-link align-middle"
                        @if ($this->admin_routes_active) aria-expanded="true" @endif>
                        <i class="bi bi-gear fs-6 float-end border border-2 border-dark" title="Administración"></i>
                        <span class="ms-1 d-none text-uppercase fw-semibold fs-6" :class="display"> Administración
                        </span>
                    </a>
                    <ul class="collapse nav flex-column ms-1 @if ($this->admin_routes_active) show @endif"
                        :class="submenu_absolute" id="submenu_admin" data-bs-parent="#submenu_admin">
                        @can('viewAny', [App\Models\User::class])
                            <li class="w-100 li-item {{ active_route('cliente/usuarios*') }}">
                                <a href="{{ route('cliente.usuarios.index') }}" class="nav-link submenu">
                                    <i class="bi bi-person fs-6"></i> <span class="d-sm-inline px-2">Usuarios</span></a>
                            </li>
                        @endcan
                        @can('viewAnyComensal', [App\Models\Cliente::class])
                            <li class="w-100 li-item {{ active_route('cliente/comensales*') }}">
                                <a href="{{ route('cliente.comensales.index') }}" class="nav-link submenu">
                                    <i class="bi bi-people fs-6"></i> <span class="d-sm-inline px-2">Clientes</span></a>
                            </li>
                        @endcan
                        @can('viewAny', [App\Models\Sucursal::class])
                            <li class="w-100 li-item {{ active_route('cliente/sucursales*') }}">
                                <a href="{{ route('cliente.sucursales.index') }}" class="nav-link submenu">
                                    <i class="bi bi-building fs-6"></i> <span class="d-sm-inline px-2">Sucursales</span></a>
                            </li>
                        @endcan
                        @can('viewAny', [App\Models\Terminal::class])
                            <li class="w-100 li-item {{ active_route('cliente/terminales*') }}">
                                <a href="{{ route('cliente.terminales.index') }}" class="nav-link submenu">
                                    <i class="bi bi-pc-display-horizontal fs-6"></i> <span
                                        class="d-sm-inline px-2">Terminales</span></a>
                            </li>
                        @endcan
                        @if (user()->is_admin)
                            <li class="w-100 li-item {{ active_route('cliente/trazas*') }}">
                                <a href="{{ route('cliente.trazas.index') }}" class="nav-link submenu">
                                    <i class="bi bi-fingerprint fs-6"></i> <span
                                        class="d-sm-inline px-2">Trazas</span></a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            @if (user()->cliente?->con_facturacion)
                <li class="w-100 pb-2" :class="menu_absolute">
                    <a href="#submenu_facturacion" data-bs-toggle="collapse" class="nav-link align-middle"
                        @if ($this->facturacion_routes_active) aria-expanded="true" @endif>
                        <i class="bi bi-file-earmark-code fs-6 float-end border border-2 border-dark"
                            title="Administración"></i>
                        <span class="ms-1 d-none text-uppercase fw-semibold fs-6" :class="display"> Facturación
                        </span>
                    </a>
                    <ul class="collapse nav flex-column ms-1 @if ($this->facturacion_routes_active) show @endif"
                        :class="submenu_absolute" id="submenu_facturacion" data-bs-parent="#submenu_facturacion">
                        @can('viewPanelPac', [App\Models\Factura::class])
                            <li class="w-100 li-item">
                                <a href="javascript:void(0)" wire:click="$emit('openModal', 'panel-pac')"
                                    class="nav-link submenu">
                                    <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">Panel PAC</span></a>
                            </li>
                        @endcan
                        @can('viewCabeceraFactura', [App\Models\Factura::class])
                            <li class="w-100 li-item {{ active_route('cliente/cabecera-factura*') }}">
                                <a href="{{ route('cliente.cabecera-factura') }}" class="nav-link submenu">
                                    <i class="bi bi-gear fs-6"></i> <span class="d-sm-inline px-2">Cabecera de
                                        Facturas</span></a>
                            </li>
                        @endcan
                        @can('viewAny', [App\Models\Factura::class])
                            <li class="w-100 li-item {{ active_route('cliente/pre-facturas*') }}">
                                <a href="{{ route('cliente.pre-facturas.index') }}" class="nav-link submenu">
                                    <i class="bi bi-database fs-6"></i> <span class="d-sm-inline px-2">Facturas</span></a>
                            </li>
                            <li class="w-100 li-item {{ active_route('cliente/almacen-facturas*') }}">
                                <a href="{{ route('cliente.almacen-facturas.index') }}" class="nav-link submenu">
                                    <i class="bi bi-database-check fs-6"></i> <span class="d-sm-inline px-2">Almacén de
                                        Facturas</span></a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endif
            @if (user()->is_super_admin)
                <li class="w-100 pb-2" :class="menu_absolute">
                    <a href="#submenu_reportes" data-bs-toggle="collapse" class="nav-link align-middle"
                        @if ($this->reportes_routes_active) aria-expanded="true" @endif>
                        <i class="bi bi-file-earmark-code fs-6 float-end border border-2 border-dark"
                            title="Reportes"></i>
                        <span class="ms-1 d-none text-uppercase fw-semibold fs-6" :class="display"> Reportes
                        </span>
                    </a>
                    <ul class="collapse nav flex-column ms-1 @if ($this->reportes_routes_active) show @endif"
                        :class="submenu_absolute" id="submenu_reportes" data-bs-parent="#submenu_reportes">
                        <li class="w-100 li-item {{ active_route('admin/reportes/logs*') }}">
                            <a href="{{ route('admin.reportes.logs') }}" class="nav-link submenu">
                                <i class="bi bi-fingerprint fs-6"></i> <span class="d-sm-inline px-2">Logs</span></a>
                        </li>
                    </ul>
                </li>
            @else
                <li class="w-100 pb-2" :class="menu_absolute">
                    <a href="#submenu_reportes" data-bs-toggle="collapse" class="nav-link align-middle"
                        @if ($this->reportes_routes_active) aria-expanded="true" @endif>
                        <i class="bi bi-file-earmark-code fs-6 float-end border border-2 border-dark"
                            title="Reportes"></i>
                        <span class="ms-1 d-none text-uppercase fw-semibold fs-6" :class="display"> Reportes
                        </span>
                    </a>
                    <ul class="collapse nav flex-column ms-1 @if ($this->reportes_routes_active) show @endif"
                        :class="submenu_absolute" id="submenu_reportes" data-bs-parent="#submenu_reportes">
                        <li class="w-100 li-item {{ active_route('cliente/reportes/ventas-periodo*') }}">
                            <a href="{{ route('cliente.reportes.ventas-periodo') }}" class="nav-link submenu">
                                <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">Ventas por
                                    Período</span></a>
                        </li>
                        <li class="w-100 li-item {{ active_route('cliente/reportes/productos-mas-vendidos*') }}">
                            <a href="{{ route('cliente.reportes.productos-mas-vendidos') }}"
                                class="nav-link submenu">
                                <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">Productos más
                                    Vendidos</span></a>
                        </li>
                        <li class="w-100 li-item {{ active_route('cliente/reportes/tickets*') }}">
                            <a href="{{ route('cliente.reportes.tickets') }}" class="nav-link submenu">
                                <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">Tickets</span></a>
                        </li>
                        <li class="w-100 li-item {{ active_route('cliente/reportes/logs*') }}">
                            <a href="{{ route('cliente.reportes.logs') }}" class="nav-link submenu">
                                <i class="bi bi-fingerprint fs-6"></i> <span class="d-sm-inline px-2">Logs</span></a>
                        </li>
                    </ul>
                </li>
            @endif
        </ul>
        {{-- <hr> --}}
        {{-- <div class="dropdown pb-4"> --}}
        {{-- <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" --}}
        {{-- id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false"> --}}
        {{-- <img src="https://github.com/mdo.png" alt="hugenerd" width="30" height="30" class="rounded-circle"> --}}
        {{-- <span class="d-none {{$display}} mx-1">loser</span> --}}
        {{-- </a> --}}
        {{-- <ul class="dropdown-menu dropdown-menu-dark text-small shadow"> --}}
        {{-- <li><a class="dropdown-item" href="#">New project...</a></li> --}}
        {{-- <li><a class="dropdown-item" href="#">Settings</a></li> --}}
        {{-- <li><a class="dropdown-item" href="#">Profile</a></li> --}}
        {{-- <li> --}}
        {{-- <hr class="dropdown-divider"> --}}
        {{-- </li> --}}
        {{-- <li><a class="dropdown-item" href="#">Sign out</a></li> --}}
        {{-- </ul> --}}
        {{-- </div> --}}
    </div>
</div>
