@section('title', __('site.dashboard.dashboard'))
<div wire:init="init" class="row" x-data="{
    mostrarFiltros: true,
    init() {
        {{-- this.loadData(); --}}
        // En móvil arrancamos con filtros ocultos para priorizar contenido
        if (window.innerWidth < 768) {
            this.mostrarFiltros = false;
        }
    },
    loadData() {
        @this.loadData();
        let el = this;
        setTimeout(() => {
            el.loadData();
        }, 5000);
    }
}">
    <div class="col-12">
        <h1 class="fs-2 fs-md-1 mb-3">@yield('title')</h1>

        <div class="row g-0 flex-md-nowrap">

            {{-- BOTÓN TOGGLE MÓVIL: barra completa arriba --}}
            <div class="col-12 d-md-none mb-2">
                <button type="button"
                    class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-between"
                    @click="mostrarFiltros = !mostrarFiltros">
                    <span><i class="bi bi-funnel-fill me-2"></i>{{ __('site.dashboard.filters') }}</span>
                    <i class="bi" :class="mostrarFiltros ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                </button>
            </div>

            {{-- COLUMNA DE FILTROS --}}
            <div x-show="mostrarFiltros" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" x-cloak
                class="col-12 col-md-4 col-lg-3 col-xl-2 mb-3 mb-md-0 flex-shrink-md-0 pe-md-3"
                style="max-width: 100%;">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
                        <i class="bi bi-funnel-fill me-2 text-primary"></i>
                        <h6 class="mb-0 fw-bold text-dark">{{ __('site.dashboard.filters') }}</h6>
                    </div>
                    <div class="card-body p-3">
                        @if ($tab == 'foh')
                            <x-input label="{{ __('site.dashboard.start_date') }}" type="date" :debounce="200"
                                :lazy="true" model="fecha_inicio" />
                            <x-input label="{{ __('site.dashboard.end_date') }}" type="date" :debounce="200"
                                :lazy="true" model="fecha_fin" />
                        @endif
                        <x-select2-multiple class="form-control" label="{{ __('site.dashboard.branches') }}"
                            :lazy="true" model="sucursales" :options="$sucursalesDisponibles" />
                        <x-select2-multiple class="form-control" label="{{ __('site.dashboard.terminals') }}"
                            :dynamic="true" :lazy="true" model="terminales" :options="$terminalesDisponibles" />
                        @if ($terminales && count($terminales) > 0 && $tab == 'foh')
                            <a href="{{ route('cliente.reportes.historico-operaciones', ['fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                                class="btn btn-primary w-100 mt-2">
                                {{ __('site.dashboard.view_details') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- PESTAÑA/MANIJA: solo visible en desktop --}}
            <div class="col-auto d-none d-md-flex align-items-start pe-2">
                <button type="button"
                    class="btn btn-primary d-flex align-items-center justify-content-center shadow-sm"
                    @click="mostrarFiltros = !mostrarFiltros"
                    :title="mostrarFiltros ? '{{ __('site.dashboard.hide_filters') }}' :
                        '{{ __('site.dashboard.show_filters') }}'"
                    style="width: 24px; height: 60px; padding: 0; border-radius: 6px; margin-top: 42px;">
                    <i class="bi" :class="mostrarFiltros ? 'bi-chevron-left' : 'bi-chevron-right'"></i>
                </button>
            </div>

            {{-- CONTENIDO PRINCIPAL --}}
            <div class="col-12 col-md" style="min-width: 0;">

                <ul class="nav nav-tabs nav-pills justify-content-center border-0 flex-nowrap overflow-auto"
                    id="myTab" role="tablist" style="-webkit-overflow-scrolling: touch;">
                    <li class="nav-item flex-shrink-0" role="presentation">
                        <button wire:click="$set('tab', 'foh')"
                            class="nav-link border me-2 @if ($tab == 'foh') active @else text-success @endif"
                            id="foh-tab" data-bs-toggle="tab" data-bs-target="#foh-tab-pane" type="button"
                            role="tab" aria-controls="foh-tab-pane" aria-selected="true"><span
                                class="fs-5 fs-md-3">{{ __('site.dashboard.front_of_house') }}</span></button>
                    </li>
                    <li class="nav-item flex-shrink-0" role="presentation">
                        <button wire:click="$set('tab', 'boh')"
                            class="nav-link border me-2 @if ($tab == 'boh') active @else text-success @endif"
                            id="boh-tab" data-bs-toggle="tab" data-bs-target="#boh-tab-pane" type="button"
                            role="tab" aria-controls="boh-tab-pane" aria-selected="false"><span
                                class="fs-5 fs-md-3">{{ __('site.dashboard.video_kitchen') }}</span></button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade @if ($tab == 'foh') show active @endif p-2"
                        id="foh-tab-pane" role="tabpanel" aria-labelledby="foh-tab" tabindex="0">

                        {{-- Fila de botones de sección: scroll horizontal en móvil --}}
                        <div class="d-flex flex-nowrap flex-md-wrap overflow-auto gap-2 px-1 px-md-3 mb-3 pb-2 pb-md-0"
                            style="-webkit-overflow-scrolling: touch;">
                            <a href="{{ route('home', ['seccion' => 'resumen', 'fecha_inicio' => $fecha_inicio ?? '', 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                                class="btn btn-outline-site-primary btn-sm btn-md-lg text-nowrap @if ($seccion == 'resumen') active @endif">
                                {{ __('site.dashboard.summary') }}
                            </a>
                            <a href="{{ route('home', ['seccion' => 'operaciones', 'fecha_inicio' => $fecha_inicio ?? '', 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                                class="btn btn-outline-site-primary btn-sm btn-md-lg text-nowrap @if ($seccion == 'operaciones') active @endif">
                                {{ __('site.dashboard.operations') }}
                            </a>
                            <a href="{{ route('home', ['seccion' => 'productos', 'fecha_inicio' => $fecha_inicio ?? '', 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                                class="btn btn-outline-site-primary btn-sm btn-md-lg text-nowrap @if ($seccion == 'productos') active @endif">
                                {{ __('site.dashboard.products') }}
                            </a>
                            <a href="{{ route('home', ['seccion' => 'pagos', 'fecha_inicio' => $fecha_inicio ?? '', 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                                class="btn btn-outline-site-primary btn-sm btn-md-lg text-nowrap @if ($seccion == 'pagos') active @endif">
                                {{ __('site.dashboard.payments') }}
                            </a>
                            <a href="{{ route('home', ['seccion' => 'correcciones', 'fecha_inicio' => $fecha_inicio ?? '', 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                                class="btn btn-outline-site-primary btn-sm btn-md-lg text-nowrap @if ($seccion == 'correcciones') active @endif">
                                {{ __('site.dashboard.corrections') }}
                            </a>
                        </div>

                        @if ($seccion)
                            @include("livewire.secciones-dashboard.$seccion")
                        @endif
                    </div>
                    <div class="tab-pane fade @if ($tab == 'boh') show active @endif p-2"
                        id="boh-tab-pane" role="tabpanel" aria-labelledby="boh-tab" tabindex="0">
                        <div class="row px-1 mb-4 g-2">
                            <div class="col-12 col-sm-6 col-md-3">
                                <div
                                    class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center">
                                    <div class="card-body align-items-center d-flex flex-column p-2 p-md-3">
                                        <span
                                            class="fs-6 fs-md-5 fw-bold text-uppercase">{{ __('site.statuses.tickets_vk.Open') }}</span>
                                        <span
                                            class="fs-4 fs-md-3 text-primary m-auto fw-bold">{{ max($videoKitchenData['cantidadOrdenesAbiertas'], 0) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div
                                    class="card border-0 border-start border-warning bg-warning-subtle shadow-sm border-4 text-center">
                                    <div class="card-body align-items-center d-flex flex-column p-2 p-md-3">
                                        <span
                                            class="fs-6 fs-md-5 fw-bold text-uppercase">{{ __('site.statuses.tickets_vk.InProcess') }}</span>
                                        <span
                                            class="fs-4 fs-md-3 text-warning m-auto fw-bold">{{ max($videoKitchenData['cantidadOrdenesProcesando'], 0) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div
                                    class="card border-0 border-start border-danger bg-dark-subtle shadow-sm border-4 text-center">
                                    <div class="card-body align-items-center d-flex flex-column p-2 p-md-3">
                                        <span
                                            class="fs-6 fs-md-5 fw-bold text-uppercase">{{ __('site.statuses.tickets_vk.Delayed') }}</span>
                                        <span
                                            class="fs-4 fs-md-3 text-danger m-auto fw-bold">{{ max($videoKitchenData['cantidadOrdenesDemoradas'], 0) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div
                                    class="card border-0 border-start border-success bg-success-subtle shadow-sm border-4 text-center">
                                    <div class="card-body align-items-center d-flex flex-column p-2 p-md-3">
                                        <span
                                            class="fs-6 fs-md-5 fw-bold text-uppercase">{{ __('site.statuses.tickets_vk.Done') }}</span>
                                        <span
                                            class="fs-4 fs-md-3 text-success m-auto fw-bold">{{ max($videoKitchenData['cantidadOrdenesTerminadas'], 0) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @foreach ($videoKitchenData['ordenes'] as $sucursal)
                            <div class="card shadow-sm">
                                <div class="card-body p-3 p-md-4">
                                    <span class="h6 h5-md position-absolute px-3"
                                        style="background: ghostwhite; top: -12px">{{ $sucursal['label'] }}</span>
                                    <div class="row g-2">
                                        @foreach ($sucursal['data'] as $orden)
                                            @php
                                                $primaryClassOrdenEstado = 'info';
                                                $secondaryClassOrdenEstado = 'info';
                                                $textColor = '#000';
                                                switch ($orden['estado']) {
                                                    case 1:
                                                        $primaryClassOrdenEstado = 'primary';
                                                        $secondaryClassOrdenEstado = 'primary';
                                                        $textColor = '#fff';
                                                        break;
                                                    case 2:
                                                        $primaryClassOrdenEstado = 'warning';
                                                        $secondaryClassOrdenEstado = 'warning';
                                                        $textColor = '#000';
                                                        break;
                                                    case 3:
                                                        $primaryClassOrdenEstado = 'success';
                                                        $secondaryClassOrdenEstado = 'success';
                                                        $textColor = '#fff';
                                                        break;
                                                    case 4:
                                                        $primaryClassOrdenEstado = 'danger';
                                                        $secondaryClassOrdenEstado = 'dark';
                                                        $textColor = '#fff';
                                                        break;
                                                }
                                            @endphp
                                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-2">
                                                <div
                                                    class="card border-{{ $primaryClassOrdenEstado }} shadow-sm h-100">
                                                    <div class="card-header py-1 fs-6 fs-md-4 bg-{{ $primaryClassOrdenEstado }}"
                                                        style="color: {{ $textColor }}">
                                                        <span>{{ __('site.dashboard.order') }}</span>:
                                                        {{ $orden['id_transaccion'] }}
                                                    </div>
                                                    <div
                                                        class="card-body bg-{{ $secondaryClassOrdenEstado }}-subtle p-2">
                                                        @if ($orden['mesa'])
                                                            <p class="mb-1 small">
                                                                <strong>{{ __('site.dashboard.table') }}:</strong>
                                                                {{ $orden['mesa'] }}
                                                            </p>
                                                        @endif
                                                        @if ($orden['asiento'])
                                                            <p class="mb-1 small">
                                                                <strong>{{ __('site.dashboard.seat') }}:</strong>
                                                                {{ $orden['asiento'] }}
                                                            </p>
                                                        @endif
                                                        <p class="mb-1 small">
                                                            <strong>{{ __('site.dashboard.terminal') }}:
                                                            </strong>{{ $orden['terminal'] }}
                                                        </p>
                                                        <p class="mb-1 small">
                                                            <strong>{{ __('site.dashboard.status') }}:
                                                            </strong>{{ $orden['estado'] }}
                                                        </p>
                                                        @if ($orden['departamento'])
                                                            <p class="mb-1 small">
                                                                <strong>{{ __('site.dashboard.location') }}:
                                                                </strong>{{ $orden['departamento'] }}
                                                            </p>
                                                        @endif
                                                        <p class="mb-1 small">
                                                            <strong>{{ __('site.dashboard.emitted') }}:
                                                            </strong>{{ $orden['fecha_transaccion'] }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
