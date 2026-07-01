<li class="w-100 pb-2" :class="menu_absolute">
    <a href="#submenu_admin" data-bs-toggle="collapse" class="nav-link align-middle"
        @if ($this->admin_routes_active) aria-expanded="true" @endif>
        <i class="bi bi-gear fs-6 float-end border border-2 border-dark" title="{{ __('sidebar.administration') }}"></i>
        <span class="ms-1 d-none text-uppercase fw-semibold fs-6" :class="display">
            {{ __('sidebar.administration') }}
        </span>
    </a>
    <ul class="collapse nav flex-column ms-1 @if ($this->admin_routes_active) show @endif" :class="submenu_absolute"
        id="submenu_admin" data-bs-parent="#submenu_admin">
        @can('viewAny', [App\Models\User::class])
            <li class="w-100 li-item {{ active_route('admin/usuarios*') }}">
                <a href="{{ route('admin.usuarios.index') }}" class="nav-link submenu">
                    <i class="bi bi-person fs-6"></i> <span class="d-sm-inline px-2">{{ __('sidebar.users') }}</span></a>
            </li>
        @endcan
        @can('viewAny', [App\Models\Modulo::class])
            <li class="w-100 li-item {{ active_route('admin/modulos*') }}">
                <a href="{{ route('admin.modulos.index') }}" class="nav-link submenu">
                    <i class="bi bi-box fs-6"></i> <span class="d-sm-inline px-2">{{ __('sidebar.modules') }}</span></a>
            </li>
        @endcan
        @can('viewAny', [App\Models\Paquete::class])
            <li class="w-100 li-item {{ active_route('admin/paquetes*') }}">
                <a href="{{ route('admin.paquetes.index') }}" class="nav-link submenu">
                    <i class="bi bi-bounding-box fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('sidebar.packages') }}</span></a>
            </li>
        @endcan
        @can('viewAnyCliente', [App\Models\Cliente::class])
            <li class="w-100 li-item {{ active_route('admin/clientes*') }}">
                <a href="{{ route('admin.clientes.index') }}" class="nav-link submenu">
                    <i class="bi bi-people fs-6"></i> <span class="d-sm-inline px-2">{{ __('sidebar.clients') }}</span></a>
            </li>
        @endcan
        @can('viewAny', [App\Models\Sucursal::class])
            <li class="w-100 li-item {{ active_route('admin/sucursales*') }}">
                <a href="{{ route('admin.sucursales.index') }}" class="nav-link submenu">
                    <i class="bi bi-building fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('sidebar.branches') }}</span></a>
            </li>
        @endcan
        @can('viewAny', [App\Models\Terminal::class])
            <li class="w-100 li-item {{ active_route('admin/terminales*') }}">
                <a href="{{ route('admin.terminales.index') }}" class="nav-link submenu">
                    <i class="bi bi-pc-display-horizontal fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('sidebar.terminals') }}</span></a>
            </li>
        @endcan
        @can('viewAny', [App\Models\Suscripcion::class])
            <li class="w-100 li-item {{ active_route('admin/suscripciones*') }}">
                <a href="{{ route('admin.suscripciones.index') }}" class="nav-link submenu">
                    <i class="bi bi-bag-check fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('sidebar.subscriptions') }}</span></a>
            </li>
        @endcan
        @if (user()->is_super_admin)
            <li class="w-100 li-item {{ active_route('admin/configuraciones*') }}">
                <a href="{{ route('admin.configuraciones.index') }}" class="nav-link submenu">
                    <i class="bi bi-gear fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('sidebar.configs') }}</span></a>
            </li>
            <li class="w-100 li-item {{ active_route('admin/trazas*') }}">
                <a href="{{ route('admin.trazas.index') }}" class="nav-link submenu">
                    <i class="bi bi-fingerprint fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('sidebar.traces') }}</span></a>
            </li>
        @endif
    </ul>
</li>
<li class="w-100 pb-2" :class="menu_absolute">
    <a href="#submenu_facturacion" data-bs-toggle="collapse" class="nav-link align-middle"
        @if ($this->facturacion_routes_active) aria-expanded="true" @endif>
        <i class="bi bi-file-earmark-code fs-6 float-end border border-2 border-dark"
            title="{{ __('sidebar.billing') }}"></i>
        <span class="ms-1 d-none text-uppercase fw-semibold fs-6" :class="display">
            {{ __('sidebar.billing') }}
        </span>
    </a>
    <ul class="collapse nav flex-column ms-1 @if ($this->facturacion_routes_active) show @endif" :class="submenu_absolute"
        id="submenu_facturacion" data-bs-parent="#submenu_facturacion">
        @can('setPanelPacFacturaSistema', [App\Models\Factura::class])
            <li class="w-100 li-item">
                <a href="javascript:void(0)" wire:click="$emit('openModal', 'facturas-sistema.panel-pac')"
                    class="nav-link submenu">
                    <i class="bi bi-cart fs-6"></i> <span class="d-sm-inline px-2">{{ __('sidebar.panel-pac') }}</span></a>
            </li>
        @endcan
        @can('setCabeceraFacturaFacturaSistema', [App\Models\Factura::class])
            <li class="w-100 li-item {{ active_route('admin/cabecera-factura*') }}">
                <a href="{{ route('admin.cabecera-factura') }}" class="nav-link submenu">
                    <i class="bi bi-gear fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('sidebar.invoice-header') }}</span></a>
            </li>
        @endcan
        @can('viewAnyFacturaSistema', [App\Models\Factura::class])
            <li
                class="w-100 li-item {{ active_route(['admin/pre-facturas*', 'admin/complementos*', 'admin/notas-credito*']) }}">
                <a href="{{ route('admin.pre-facturas.index') }}" class="nav-link submenu">
                    <i class="bi bi-database fs-6"></i> <span
                        class="d-sm-inline px-2">{{ __('sidebar.invoices') }}</span></a>
            </li>
            <li class="w-100 li-item {{ active_route('admin/almacen-facturas*') }}">
                <a href="{{ route('admin.almacen-facturas.index') }}" class="nav-link submenu">
                    <i class="bi bi-database-check fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('sidebar.invoice-storage') }}</span></a>
            </li>
        @endcan
        @can('setCabeceraFacturaFacturaSistema', [App\Models\Factura::class])
            <li class="w-100 li-item {{ active_route('admin/cuentas-cobrar*') }}">
                <a href="{{ route('admin.cuentas-cobrar.index') }}" class="nav-link submenu">
                    <i class="bi bi-currency-exchange fs-6"></i> <span class="d-sm-inline px-2">
                        {{ __('sidebar.accounts-receivable') }}</span></a>
            </li>
        @endcan
    </ul>
</li>
<li class="w-100 pb-2" :class="menu_absolute">
    <a href="#submenu_reportes" data-bs-toggle="collapse" class="nav-link align-middle"
        @if ($this->reportes_routes_active) aria-expanded="true" @endif>
        <i class="bi bi-file-earmark-code fs-6 float-end border border-2 border-dark" title="Reportes"></i>
        <span class="ms-1 d-none text-uppercase fw-semibold fs-6" :class="display"> {{__('sidebar.reports')}}
        </span>
    </a>
    <ul class="collapse nav flex-column ms-1 @if ($this->reportes_routes_active) show @endif" :class="submenu_absolute"
        id="submenu_reportes" data-bs-parent="#submenu_reportes">
        <li class="w-100 li-item {{ active_route('admin/reportes/ingresos*') }}">
            <a href="{{ route('admin.reportes.ingresos') }}" class="nav-link submenu">
                <i class="bi bi-graph-up fs-6"></i> <span class="d-sm-inline px-2">{{ __('sidebar.incomes') }}</span></a>
        </li>
        @if (user()->is_super_admin)
            <li class="w-100 li-item {{ active_route('admin/reportes/logs*') }}">
                <a href="{{ route('admin.reportes.logs') }}" class="nav-link submenu">
                    <i class="bi bi-fingerprint fs-6"></i> <span class="d-sm-inline px-2">{{ __('sidebar.logs') }}</span></a>
            </li>
        @endif
    </ul>
</li>
