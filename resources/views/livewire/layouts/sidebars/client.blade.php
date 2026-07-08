<li class="w-100 pb-2" :class="menu_absolute">
    <a href="#submenu_admin" data-bs-toggle="collapse" class="nav-link align-middle"
        @if ($this->admin_routes_active) aria-expanded="true" @endif>
        <i class="bi bi-gear fs-6 float-end border border-2 border-dark" title="{{ __('site.sidebar.administration') }}"></i>
        <span class="ms-1 d-none text-uppercase fw-semibold fs-6" :class="display">
            {{ __('site.sidebar.administration') }}
        </span>
    </a>
    <ul class="collapse nav flex-column ms-1 @if ($this->admin_routes_active) show @endif" :class="submenu_absolute"
        id="submenu_admin" data-bs-parent="#submenu_admin">
        @can('viewAny', [App\Models\User::class])
            <li class="w-100 li-item {{ active_route('cliente/usuarios*') }}">
                <a href="{{ route('cliente.usuarios.index') }}" class="nav-link submenu">
                    <i class="bi bi-person fs-6"></i> <span class="d-sm-inline px-2">{{ __('site.sidebar.users') }}</span></a>
            </li>
        @endcan
        @can('viewAnyComensal', [App\Models\Cliente::class])
            <li class="w-100 li-item {{ active_route('cliente/comensales*') }}">
                <a href="{{ route('cliente.comensales.index') }}" class="nav-link submenu">
                    <i class="bi bi-people fs-6"></i> <span class="d-sm-inline px-2">{{ __('site.sidebar.clients') }}</span></a>
            </li>
        @endcan
        @can('viewAny', [App\Models\Sucursal::class])
            <li class="w-100 li-item {{ active_route('cliente/sucursales*') }}">
                <a href="{{ route('cliente.sucursales.index') }}" class="nav-link submenu">
                    <i class="bi bi-building fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.branches') }}</span></a>
            </li>
        @endcan
        @can('viewAny', [App\Models\Terminal::class])
            <li class="w-100 li-item {{ active_route('cliente/terminales*') }}">
                <a href="{{ route('cliente.terminales.index') }}" class="nav-link submenu">
                    <i class="bi bi-pc-display-horizontal fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.terminals') }}</span></a>
            </li>
        @endcan
        @if (user()->is_admin)
            <li class="w-100 li-item {{ active_route('cliente/trazas*') }}">
                <a href="{{ route('cliente.trazas.index') }}" class="nav-link submenu">
                    <i class="bi bi-fingerprint fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.traces') }}</span></a>
            </li>
        @endif
    </ul>
</li>
@if (user()->cliente->con_facturacion)
    <li class="w-100 pb-2" :class="menu_absolute">
        <a href="#submenu_facturacion" data-bs-toggle="collapse" class="nav-link align-middle"
            @if ($this->facturacion_routes_active) aria-expanded="true" @endif>
            <i class="bi bi-file-earmark-code fs-6 float-end border border-2 border-dark"
                title="{{ __('site.sidebar.billing') }}"></i>
            <span class="ms-1 d-none text-uppercase fw-semibold fs-6" :class="display">
                {{ __('site.sidebar.billing') }}
            </span>
        </a>
        <ul class="collapse nav flex-column ms-1 @if ($this->facturacion_routes_active) show @endif"
            :class="submenu_absolute" id="submenu_facturacion" data-bs-parent="#submenu_facturacion">
            @can('setPanelPac', [App\Models\Factura::class])
                <li class="w-100 li-item">
                    <a href="javascript:void(0)" wire:click="$emit('openModal', 'panel-pac')" class="nav-link submenu">
                        <i class="bi bi-cart fs-6"></i> <span
                            class="d-sm-inline px-2">{{ __('site.sidebar.panel-pac') }}</span></a>
                </li>
            @endcan
            @can('setCabeceraFactura', [App\Models\Factura::class])
                <li class="w-100 li-item {{ active_route('cliente/cabecera-factura*') }}">
                    <a href="{{ route('cliente.cabecera-factura') }}" class="nav-link submenu">
                        <i class="bi bi-gear fs-6"></i> <span class="d-sm-inline px-2">
                            {{ __('site.sidebar.invoice-header') }}</span></a>
                </li>
            @endcan
            @can('viewAny', [App\Models\Factura::class])
                <li class="w-100 li-item {{ active_route('cliente/pre-facturas*') }}">
                    <a href="{{ route('cliente.pre-facturas.index') }}" class="nav-link submenu">
                        <i class="bi bi-database fs-6"></i> <span
                            class="d-sm-inline px-2">{{ __('site.sidebar.invoices') }}</span></a>
                </li>
                <li class="w-100 li-item {{ active_route('cliente/almacen-facturas*') }}">
                    <a href="{{ route('cliente.almacen-facturas.index') }}" class="nav-link submenu">
                        <i class="bi bi-database-check fs-6"></i> <span class="d-sm-inline px-2">
                            {{ __('site.sidebar.invoice-storage') }}</span></a>
                </li>
            @endcan
        </ul>
    </li>
@endif
<li class="w-100 pb-2" :class="menu_absolute">
    <a href="#submenu_reportes" data-bs-toggle="collapse" class="nav-link align-middle"
        @if ($this->reportes_routes_active) aria-expanded="true" @endif>
        <i class="bi bi-file-earmark-code fs-6 float-end border border-2 border-dark" title="Reportes"></i>
        <span class="ms-1 d-none text-uppercase fw-semibold fs-6" :class="display"> Reportes
        </span>
    </a>
    <ul class="collapse nav flex-column ms-1 @if ($this->reportes_routes_active) show @endif" :class="submenu_absolute"
        id="submenu_reportes" data-bs-parent="#submenu_reportes">
        <li class="w-100 li-item {{ active_route('cliente/reportes/ventas-periodo*') }}">
            <a href="{{ route('cliente.reportes.ventas-periodo') }}" class="nav-link submenu">
                <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">Ventas por
                    Período</span></a>
        </li>
        <li class="w-100 li-item {{ active_route('cliente/reportes/productos-mas-vendidos*') }}">
            <a href="{{ route('cliente.reportes.productos-mas-vendidos') }}" class="nav-link submenu">
                <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">Productos más
                    Vendidos</span></a>
        </li>
        <li class="w-100 li-item {{ active_route('cliente/reportes/historico-operaciones*') }}">
            <a href="{{ route('cliente.reportes.historico-operaciones') }}" class="nav-link submenu">
                <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">Histórico de Operaciones</span></a>
        </li>
        <li class="w-100 li-item {{ active_route('cliente/reportes/logs*') }}">
            <a href="{{ route('cliente.reportes.logs') }}" class="nav-link submenu">
                <i class="bi bi-fingerprint fs-6"></i> <span class="d-sm-inline px-2">Logs</span></a>
        </li>
    </ul>
</li>
