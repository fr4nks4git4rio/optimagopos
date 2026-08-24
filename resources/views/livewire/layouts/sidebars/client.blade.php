<li class="w-100 pb-2" :class="menu_absolute">
    <a href="#submenu_admin" data-bs-toggle="collapse" class="nav-link align-middle"
        @if ($this->admin_routes_active) aria-expanded="true" @endif>
        <i class="bi bi-gear fs-6 float-end border border-2 border-dark"
            title="{{ __('site.sidebar.administration') }}"></i>
        <span class="ms-1 d-none text-uppercase fw-semibold fs-6" :class="display">
            {{ __('site.sidebar.administration') }}
        </span>
    </a>
    <ul class="collapse nav flex-column ms-1 @if ($this->admin_routes_active) show @endif" :class="submenu_absolute"
        id="submenu_admin" data-bs-parent="#submenu_admin">
        @can('viewAny', [App\Models\Sucursal::class])
            <li class="w-100 li-item {{ active_route('cliente/sucursales*') }}">
                <a href="{{ route('cliente.sucursales.index') }}" class="nav-link submenu">
                    <i class="bi bi-building fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.branches') }}</span></a>
            </li>
        @endcan
        @can('roles-viewAny')
            <li class="w-100 li-item {{ active_route('cliente/roles*') }}">
                <a href="{{ route('cliente.roles.index') }}" class="nav-link submenu">
                    <i class="bi bi-person-badge fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.roles') }}</span></a>
            </li>
        @endcan
        @can('viewAny', [App\Models\User::class])
            <li class="w-100 li-item {{ active_route('cliente/usuarios*') }}">
                <a href="{{ route('cliente.usuarios.index') }}" class="nav-link submenu">
                    <i class="bi bi-person fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.users') }}</span></a>
            </li>
        @endcan
        @can('viewAny', [App\Models\Terminal::class])
            <li class="w-100 li-item {{ active_route('cliente/terminales*') }}">
                <a href="{{ route('cliente.terminales.index') }}" class="nav-link submenu">
                    <i class="bi bi-pc-display-horizontal fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.terminals') }}</span></a>
            </li>
        @endcan
        @can('logs-viewAny')
            <li class="w-100 li-item {{ active_route('cliente/trazas*') }}">
                <a href="{{ route('cliente.trazas.index') }}" class="nav-link submenu">
                    <i class="bi bi-fingerprint fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.traces') }}</span></a>
            </li>
        @endcan
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
            @can('panelPac-view')
                <li class="w-100 li-item">
                    <a href="javascript:void(0)" wire:click="$dispatch('openModal', { component: 'facturas.panel-pac' })"
                        class="nav-link submenu">
                        <i class="bi bi-cart fs-6"></i> <span
                            class="d-sm-inline px-2">{{ __('site.sidebar.panel-pac') }}</span></a>
                </li>
            @endcan
            @can('invoiceHeader-view')
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
        <i class="bi bi-file-earmark-code fs-6 float-end border border-2 border-dark"
            title="{{ __('site.sidebar.reports') }}"></i>
        <span class="ms-1 d-none text-uppercase fw-semibold fs-6" :class="display">
            {{ __('site.sidebar.reports') }}
        </span>
    </a>
    <ul class="collapse nav flex-column ms-1 @if ($this->reportes_routes_active) show @endif" :class="submenu_absolute"
        id="submenu_reportes" data-bs-parent="#submenu_reportes">
        @can('reportsArticlesSold-viewAny')
            <li class="w-100 li-item {{ active_route('cliente/reportes/articulos-vendidos*') }}">
                <a href="{{ route('cliente.reportes.articulos-vendidos') }}" class="nav-link submenu">
                    <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('site.sidebar.articles_sold') }}
                    </span></a>
            </li>
        @endcan
        @can('reportsBestSellingProducts-viewAny')
            <li class="w-100 li-item {{ active_route('cliente/reportes/productos-mas-vendidos*') }}">
                <a href="{{ route('cliente.reportes.productos-mas-vendidos') }}" class="nav-link submenu">
                    <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('site.sidebar.best_selling_products') }}
                    </span></a>
            </li>
        @endcan
        @can('reportsOperationsHistory-viewAny')
            <li class="w-100 li-item {{ active_route('cliente/reportes/historico-operaciones*') }}">
                <a href="{{ route('cliente.reportes.historico-operaciones') }}" class="nav-link submenu">
                    <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('site.sidebar.operations_history') }}
                    </span></a>
            </li>
        @endcan
        @can('reportsVKTicketHistory-viewAny')
            <li class="w-100 li-item {{ active_route('cliente/reportes/historico-tickets-vk*') }}">
                <a href="{{ route('cliente.reportes.historico-tickets-vk') }}" class="nav-link submenu">
                    <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('site.sidebar.vk_ticket_history') }}
                    </span></a>
            </li>
        @endcan
        @can('reportsDailySales-viewAny')
            <li class="w-100 li-item {{ active_route('cliente/reportes/ventas-diarias*') }}">
                <a href="{{ route('cliente.reportes.ventas-diarias') }}" class="nav-link submenu">
                    <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('site.sidebar.daily_sales') }}
                    </span></a>
            </li>
        @endcan
        @can('reportsSalesByOperator-viewAny')
            <li class="w-100 li-item {{ active_route('cliente/reportes/ventas-operador*') }}">
                <a href="{{ route('cliente.reportes.ventas-operador') }}" class="nav-link submenu">
                    <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('site.sidebar.sales_by_operator') }}
                    </span></a>
            </li>
        @endcan
        @can('reportsTestingOperationsHistory-viewAny')
            <li class="w-100 li-item {{ active_route('cliente/reportes/testing-historico-operaciones*') }}">
                <a href="{{ route('cliente.reportes.testing-historico-operaciones') }}" class="nav-link submenu">
                    <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('site.sidebar.operations_history_testing') }}
                    </span></a>
            </li>
        @endcan
        @can('reportsDataReceived-viewAny')
            <li class="w-100 li-item {{ active_route('cliente/reportes/logs*') }}">
                <a href="{{ route('cliente.reportes.logs') }}" class="nav-link submenu">
                    <i class="bi bi-fingerprint fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('site.sidebar.data_received') }}
                    </span></a>
            </li>
        @endcan
    </ul>
</li>
