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
        @can('viewAnyCliente', [App\Models\Cliente::class])
            <li class="w-100 li-item {{ active_route('admin/clientes*') }}">
                <a href="{{ route('admin.clientes.index') }}" class="nav-link submenu">
                    <i class="bi bi-people fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.clients') }}</span></a>
            </li>
        @endcan
        @can('settings-viewAny')
            <li class="w-100 li-item {{ active_route('admin/configuraciones*') }}">
                <a href="{{ route('admin.configuraciones.index') }}" class="nav-link submenu">
                    <i class="bi bi-gear fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.configs') }}</span></a>
            </li>
        @endcan
        @can('quarantine-viewAny')
            <li class="w-100 li-item {{ active_route('admin/cuarentena*') }}">
                <a href="{{ route('admin.cuarentena.index') }}" class="nav-link submenu">
                    <i class="bi bi-tools fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.quarantine') }}</span></a>
            </li>
        @endcan
        @can('viewAny', [App\Models\Modulo::class])
            <li class="w-100 li-item {{ active_route('admin/modulos*') }}">
                <a href="{{ route('admin.modulos.index') }}" class="nav-link submenu">
                    <i class="bi bi-box fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.modules') }}</span></a>
            </li>
        @endcan
        @can('viewAny', [App\Models\Paquete::class])
            <li class="w-100 li-item {{ active_route('admin/paquetes*') }}">
                <a href="{{ route('admin.paquetes.index') }}" class="nav-link submenu">
                    <i class="bi bi-bounding-box fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.packages') }}</span></a>
            </li>
        @endcan
        @can('viewAny', [App\Models\Sucursal::class])
            <li class="w-100 li-item {{ active_route('admin/sucursales*') }}">
                <a href="{{ route('admin.sucursales.index') }}" class="nav-link submenu">
                    <i class="bi bi-building fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.branches') }}</span></a>
            </li>
        @endcan
        @can('viewAny', [App\Models\Suscripcion::class])
            <li class="w-100 li-item {{ active_route('admin/suscripciones*') }}">
                <a href="{{ route('admin.suscripciones.index') }}" class="nav-link submenu">
                    <i class="bi bi-bag-check fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.subscriptions') }}</span></a>
            </li>
        @endcan
        @can('roles-viewAny')
            <li class="w-100 li-item {{ active_route('admin/roles*') }}">
                <a href="{{ route('admin.roles.index') }}" class="nav-link submenu">
                    <i class="bi bi-person-badge fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.roles') }}</span></a>
            </li>
        @endcan
        @can('viewAny', [App\Models\User::class])
            <li class="w-100 li-item {{ active_route('admin/usuarios*') }}">
                <a href="{{ route('admin.usuarios.index') }}" class="nav-link submenu">
                    <i class="bi bi-person fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.users') }}</span></a>
            </li>
        @endcan
        @can('viewAny', [App\Models\Terminal::class])
            <li class="w-100 li-item {{ active_route('admin/terminales*') }}">
                <a href="{{ route('admin.terminales.index') }}" class="nav-link submenu">
                    <i class="bi bi-pc-display-horizontal fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.terminals') }}</span></a>
            </li>
        @endcan
        @can('logs-viewAny')
            <li class="w-100 li-item {{ active_route('admin/trazas*') }}">
                <a href="{{ route('admin.trazas.index') }}" class="nav-link submenu">
                    <i class="bi bi-fingerprint fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.traces') }}</span></a>
            </li>
        @endcan
    </ul>
</li>
<li class="w-100 pb-2" :class="menu_absolute">
    <a href="#submenu_facturacion" data-bs-toggle="collapse" class="nav-link align-middle"
        @if ($this->facturacion_routes_active) aria-expanded="true" @endif>
        <i class="bi bi-file-earmark-code fs-6 float-end border border-2 border-dark"
            title="{{ __('site.sidebar.billing') }}"></i>
        <span class="ms-1 d-none text-uppercase fw-semibold fs-6" :class="display">
            {{ __('site.sidebar.billing') }}
        </span>
    </a>
    <ul class="collapse nav flex-column ms-1 @if ($this->facturacion_routes_active) show @endif" :class="submenu_absolute"
        id="submenu_facturacion" data-bs-parent="#submenu_facturacion">
        @can('panelPac-view')
            <li class="w-100 li-item">
                <a href="javascript:void(0)"
                    wire:click="$dispatch('openModal', { component: 'facturas-sistema.panel-pac' })"
                    class="nav-link submenu">
                    <i class="bi bi-cart fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.panel-pac') }}</span></a>
            </li>
        @endcan
        @can('invoiceHeader-view')
            <li class="w-100 li-item {{ active_route('admin/cabecera-factura*') }}">
                <a href="{{ route('admin.cabecera-factura') }}" class="nav-link submenu">
                    <i class="bi bi-gear fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('site.sidebar.invoice-header') }}</span></a>
            </li>
        @endcan
        @can('viewAnyFacturaSistema', [App\Models\Factura::class])
            <li
                class="w-100 li-item {{ active_route(['admin/pre-facturas*', 'admin/complementos*', 'admin/notas-credito*']) }}">
                <a href="{{ route('admin.pre-facturas.index') }}" class="nav-link submenu">
                    <i class="bi bi-database fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.invoices') }}</span></a>
            </li>
            <li class="w-100 li-item {{ active_route('admin/almacen-facturas*') }}">
                <a href="{{ route('admin.almacen-facturas.index') }}" class="nav-link submenu">
                    <i class="bi bi-database-check fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('site.sidebar.invoice-storage') }}</span></a>
            </li>
        @endcan
        @can('accountsReceivable-viewAny')
            <li class="w-100 li-item {{ active_route('admin/cuentas-cobrar*') }}">
                <a href="{{ route('admin.cuentas-cobrar.index') }}" class="nav-link submenu">
                    <i class="bi bi-currency-exchange fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('site.sidebar.accounts_receivable') }}</span></a>
            </li>
        @endcan
    </ul>
</li>
<li class="w-100 pb-2" :class="menu_absolute">
    <a href="#submenu_reportes" data-bs-toggle="collapse" class="nav-link align-middle"
        @if ($this->reportes_routes_active) aria-expanded="true" @endif>
        <i class="bi bi-file-earmark-code fs-6 float-end border border-2 border-dark" title="Reportes"></i>
        <span class="ms-1 d-none text-uppercase fw-semibold fs-6" :class="display">
            {{ __('site.sidebar.reports') }}
        </span>
    </a>
    <ul class="collapse nav flex-column ms-1 @if ($this->reportes_routes_active) show @endif" :class="submenu_absolute"
        id="submenu_reportes" data-bs-parent="#submenu_reportes">
        @can('reportsIncome-viewAny')
            <li class="w-100 li-item {{ active_route('admin/reportes/ingresos*') }}">
                <a href="{{ route('admin.reportes.ingresos') }}" class="nav-link submenu">
                    <i class="bi bi-graph-up fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.income') }}</span></a>
            </li>
        @endcan
        @can('reportsArticlesSold-viewAny')
            <li class="w-100 li-item {{ active_route('admin/reportes/articulos-vendidos*') }}">
                <a href="{{ route('admin.reportes.articulos-vendidos') }}" class="nav-link submenu">
                    <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('site.sidebar.articles_sold') }}
                    </span></a>
            </li>
        @endcan
        @can('reportsBestSellingProducts-viewAny')
            <li class="w-100 li-item {{ active_route('admin/reportes/productos-mas-vendidos*') }}">
                <a href="{{ route('admin.reportes.productos-mas-vendidos') }}" class="nav-link submenu">
                    <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('site.sidebar.best_selling_products') }}
                    </span></a>
            </li>
        @endcan
        @can('reportsOperationsHistory-viewAny')
            <li class="w-100 li-item {{ active_route('admin/reportes/historico-operaciones*') }}">
                <a href="{{ route('admin.reportes.historico-operaciones') }}" class="nav-link submenu">
                    <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('site.sidebar.operations_history') }}
                    </span></a>
            </li>
        @endcan
        @can('reportsVKTicketHistory-viewAny')
            <li class="w-100 li-item {{ active_route('admin/reportes/historico-tickets-vk*') }}">
                <a href="{{ route('admin.reportes.historico-tickets-vk') }}" class="nav-link submenu">
                    <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('site.sidebar.vk_ticket_history') }}
                    </span></a>
            </li>
        @endcan
        @can('reportsDailySales-viewAny')
            <li class="w-100 li-item {{ active_route('admin/reportes/ventas-diarias*') }}">
                <a href="{{ route('admin.reportes.ventas-diarias') }}" class="nav-link submenu">
                    <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('site.sidebar.daily_sales') }}
                    </span></a>
            </li>
        @endcan
        @can('reportsSalesByOperator-viewAny')
            <li class="w-100 li-item {{ active_route('admin/reportes/ventas-operador*') }}">
                <a href="{{ route('admin.reportes.ventas-operador') }}" class="nav-link submenu">
                    <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('site.sidebar.sales_by_operator') }}
                    </span></a>
            </li>
        @endcan
        @can('reportsTestingOperationsHistory-viewAny')
            <li class="w-100 li-item {{ active_route('admin/reportes/testing-historico-operaciones*') }}">
                <a href="{{ route('admin.reportes.testing-historico-operaciones') }}" class="nav-link submenu">
                    <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('site.sidebar.operations_history_testing') }}
                    </span></a>
            </li>
        @endcan
        @can('reportsDataReceived-viewAny')
            <li class="w-100 li-item {{ active_route('admin/reportes/logs*') }}">
                <a href="{{ route('admin.reportes.logs') }}" class="nav-link submenu">
                    <i class="bi bi-fingerprint fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('site.sidebar.data_received') }}</span></a>
            </li>
        @endcan
    </ul>
</li>
