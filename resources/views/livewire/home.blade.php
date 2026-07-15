@section('title', __('site.dashboard.dashboard'))
<div wire:init="init" class="row" x-data="{
    mostrarFiltros: true,
    init() {
        this.loadData();
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
        <h1 class="fs-1 mb-3">@yield('title')</h1>

        <div class="d-flex align-items-start">

            {{-- COLUMNA DE FILTROS --}}
            <div x-show="mostrarFiltros" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" x-cloak class="flex-shrink-0 me-3 mb-3" style="width: 260px;">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
                        <i class="bi bi-funnel-fill me-2 text-primary"></i>
                        <h6 class="mb-0 fw-bold text-dark">{{ __('site.dashboard.filters') }}</h6>
                    </div>
                    <div class="card-body p-3">
                        <x-input label="{{ __('site.dashboard.start_date') }}" type="date" :debounce="200" :lazy="true"
                            model="fecha_inicio" />
                        <x-input label="{{ __('site.dashboard.end_date') }}" type="date" :debounce="200" :lazy="true"
                            model="fecha_fin" />
                        <x-select2-multiple class="form-control" label="{{ __('site.dashboard.branches') }}" :lazy="true" model="sucursales"
                            :options="$sucursalesDisponibles" />
                        <x-select2-multiple class="form-control" label="{{ __('site.dashboard.terminals') }}" :dynamic="true" :lazy="true"
                            model="terminales" :options="$terminalesDisponibles" />
                        @if ($terminales && count($terminales) > 0 && $tab == 'foh')
                            <a href="{{ route('cliente.reportes.historico-operaciones', ['fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                                class="btn btn-primary w-100 mt-2">
                                {{ __('site.dashboard.view_details') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- PESTAÑA/MANIJA: siempre visible, pegada al costado del panel --}}
            <button type="button"
                class="btn btn-primary d-flex align-items-center justify-content-center shadow-sm flex-shrink-0 me-3 mb-3"
                @click="mostrarFiltros = !mostrarFiltros"
                :title="mostrarFiltros ? '{{ __('site.dashboard.hide_filters') }}' : '{{ __('site.dashboard.show_filters') }}'"
                style="width: 24px; height: 60px; padding: 0; border-radius: 6px; align-self: flex-start; margin-top: 42px;">
                <i class="bi" :class="mostrarFiltros ? 'bi-chevron-left' : 'bi-chevron-right'"></i>
            </button>

            {{-- CONTENIDO PRINCIPAL --}}
            <div class="flex-grow-1" style="min-width: 0;">

                <ul class="nav nav-tabs nav-pills justify-center border-0" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button wire:click="$set('tab', 'foh')"
                            class="nav-link border me-2 @if ($tab == 'foh') active @else text-success @endif"
                            id="foh-tab" data-bs-toggle="tab" data-bs-target="#foh-tab-pane" type="button"
                            role="tab" aria-controls="foh-tab-pane" aria-selected="true"><span
                                class="fs-3">{{ __('site.dashboard.front_of_house') }}</span></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button wire:click="$set('tab', 'boh')"
                            class="nav-link border me-2 @if ($tab == 'boh') active @else text-success @endif"
                            id="boh-tab" data-bs-toggle="tab" data-bs-target="#boh-tab-pane" type="button"
                            role="tab" aria-controls="boh-tab-pane" aria-selected="false"><span
                                class="fs-3">{{ __('site.dashboard.video_kitchen') }}</span></button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade @if ($tab == 'foh') show active @endif p-2"
                        id="foh-tab-pane" role="tabpanel" aria-labelledby="foh-tab" tabindex="0">
                        <div class="row justify-content-start gap-3 px-3 mb-3">
                            <a href="{{ route('home', ['seccion' => 'resumen', 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                                class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'resumen') active @endif">
                                {{ __('site.dashboard.summary') }}
                            </a>
                            <a href="{{ route('home', ['seccion' => 'operaciones', 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                                class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'operaciones') active @endif">
                                {{ __('site.dashboard.operations') }}
                            </a>
                            <a href="{{ route('home', ['seccion' => 'productos', 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                                class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'productos') active @endif">
                                {{ __('site.dashboard.products') }}
                            </a>
                            <a href="{{ route('home', ['seccion' => 'pagos', 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                                class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'pagos') active @endif">
                                {{ __('site.dashboard.payments') }}
                            </a>
                            <a href="{{ route('home', ['seccion' => 'correcciones', 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                                class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'correcciones') active @endif">
                                {{ __('site.dashboard.corrections') }}
                            </a>
                            {{-- <a href="{{ route('home', ['seccion' => 'cuarentena', 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin, 'sucursales_query' => implode(',', $sucursales), 'terminales_query' => implode(',', $terminales)]) }}"
                        class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'cuarentena') active @endif">
                        Cuarentena
                    </a> --}}
                        </div>

                        @if ($seccion)
                            @include("livewire.secciones-dashboard.$seccion")
                        @endif
                    </div>
                    <div class="tab-pane fade @if ($tab == 'boh') show active @endif p-2"
                        id="boh-tab-pane" role="tabpanel" aria-labelledby="boh-tab" tabindex="0">
                        <div class="row px-1 mb-4">
                            <div class="row col-12 col-md-3">
                                <div class="col-12 mb-2">
                                    <div
                                        class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center">
                                        <div class="card-body align-items-center d-flex flex-column">
                                            <span class="fs-5 fw-bold text-uppercase">{{ __('site.statuses.tickets_vk.Open') }}</span>
                                            <span
                                                class="fs-3 text-primary m-auto fw-bold">{{ max($videoKitchenData['cantidadOrdenesAbiertas'], 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-2">
                                    <div
                                        class="card border-0 border-start border-warning bg-warning-subtle shadow-sm border-4 text-center">
                                        <div class="card-body align-items-center d-flex flex-column">
                                            <span
                                                class="fs-5 fw-bold text-uppercase">{{ __('site.statuses.tickets_vk.InProcess') }}</span>
                                            <span
                                                class="fs-3 text-warning m-auto fw-bold">{{ max($videoKitchenData['cantidadOrdenesProcesando'], 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-2">
                                    <div
                                        class="card border-0 border-start border-danger bg-dark-subtle shadow-sm border-4 text-center">
                                        <div class="card-body align-items-center d-flex flex-column">
                                            <span
                                                class="fs-5 fw-bold text-uppercase">{{ __('site.statuses.tickets_vk.Delayed') }}</span>
                                            <span
                                                class="fs-3 text-danger m-auto fw-bold">{{ max($videoKitchenData['cantidadOrdenesDemoradas'], 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div
                                        class="card border-0 border-start border-success bg-success-subtle shadow-sm border-4 text-center">
                                        <div class="card-body align-items-center d-flex flex-column">
                                            <span
                                                class="fs-5 fw-bold text-uppercase">{{ __('site.statuses.tickets_vk.Done') }}</span>
                                            <span
                                                class="fs-3 text-success m-auto fw-bold">{{ max($videoKitchenData['cantidadOrdenesTerminadas'], 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-9 mb-3">
                                <div x-data="{
                                    datosActividad: @entangle('videoKitchenData.graficaActividad'),
                                    chart: null,
                                    sinDatos: false,
                                    horasDelDia: [],

                                    init() {
                                        this.horasDelDia = Array.from({ length: 24 }, (_, i) => {
                                            return i.toString().padStart(2, '0') + ':00'; // Genera ['00:00', '01:00', ..., '23:00']
                                        });

                                        // Escuchamos los cambios en los datos que vienen de Livewire
                                        this.$watch('datosActividad', value => {
                                            let el = document.getElementById('mi-canvas-grafica-actividad-vk');
                                            if (!el) return;

                                            const hayDatos = value && Object.keys(value).length > 0;

                                            if (hayDatos) {
                                                this.sinDatos = false;

                                                let serie24Horas = this.horasDelDia.map((hora, index) => {
                                                    let claveSimple = index.toString();
                                                    let claveFormateada = hora;

                                                    if (value[claveFormateada] !== undefined) {
                                                        return Number(value[claveFormateada]);
                                                    } else if (value[claveSimple] !== undefined) {
                                                        return Number(value[claveSimple]);
                                                    }
                                                    return 0; // Si la hora no viene en tu objeto, va un cero
                                                });

                                                if (!this.chart) {
                                                    // 1. Si el elemento existe y la gráfica NO se ha creado, la inicializamos
                                                    let options = {
                                                        chart: {
                                                            type: 'line',
                                                            height: 280,
                                                            animations: {
                                                                enabled: true,
                                                                easing: 'smooth',
                                                                dynamicAnimation: { speed: 500 }
                                                            },
                                                            toolbar: {
                                                                show: true,
                                                                offsetY: -30,
                                                                tools: {
                                                                    download: true, // Deja el menú de las 3 líneas para descargar PNG/SVG/CSV
                                                                    selection: false,
                                                                    zoom: false, // Quita la lupa de zoom
                                                                    zoomin: false, // Quita el botón +
                                                                    zoomout: false, // Quita el botón -
                                                                    pan: false, // Quita la mano de paneo
                                                                    reset: false // Quita el botón de resetear vista
                                                                }
                                                            },
                                                        },
                                                        series: [{ name: '{{ __('site.dashboard.operations') }}', data: serie24Horas }],
                                                        colors: ['#065F46'],
                                                        xaxis: {
                                                            type: 'category',
                                                            categories: this.horasDelDia, // Forzamos a que siempre muestre las 24 marcas
                                                            labels: {
                                                                rotate: -45, // Rota las horas un poco para que no se encimen en pantallas chicas
                                                                style: { fontSize: '10px' }
                                                            }
                                                        },
                                                        yaxis: {
                                                            min: 0,
                                                            forceNiceScale: true
                                                        },
                                                        plotOptions: {
                                                            bar: {
                                                                columnWidth: '75%', // Un ancho cómodo para que quepan 24 barras
                                                                dataLabels: { position: 'top' }
                                                            }
                                                        },
                                                        dataLabels: {
                                                            enabled: true,
                                                            offsetY: -20,
                                                            style: { fontSize: '9px', colors: ['#304758'] },
                                                            // Opcional: Oculta el número cero para que la gráfica no se llene de '0' flotantes
                                                            formatter: function(val) {
                                                                return val > 0 ? val : '';
                                                            }
                                                        }
                                                    };

                                                    this.chart = new ApexCharts(el, options);
                                                    this.chart.render();
                                                } else {
                                                    // 2. Si ya existe, solo actualizamos los datos
                                                    this.chart.updateSeries([{ data: serie24Horas }]);
                                                }
                                            } else {
                                                // 3. Resultado vacío/null: limpiamos la serie en vez de dejar la anterior.
                                                // Mantenemos las 24 categorías en cero para no perder el eje X si luego vuelve a haber datos.
                                                this.sinDatos = true;

                                                if (this.chart) {
                                                    let serieVacia = this.horasDelDia.map(() => 0);
                                                    this.chart.updateSeries([{ data: serieVacia }]);
                                                }
                                            }
                                        });
                                    },

                                    destroy() {
                                        if (this.chart) {
                                            this.chart.destroy();
                                        }
                                    }
                                }" class="card shadow-sm bg-site-primary-subtle">
                                    <div class="card-body">
                                        <span class="fs-5 fw-bold">{{ __('site.dashboard.activity') }}</span>
                                        <template x-if="!chart && !sinDatos">
                                            <div class="text-center py-3 text-muted">
                                                <div class="spinner-border spinner-border-sm text-primary"
                                                    role="status">
                                                </div>
                                                {{ __('site.dashboard.loading_data') }}...
                                            </div>
                                        </template>
                                        <template x-if="sinDatos">
                                            <div class="text-center py-4 text-muted">
                                                <i class="bi bi-graph-down fs-3 d-block mb-1"></i>
                                                {{ __('site.dashboard.no_data') }}
                                            </div>
                                        </template>
                                        <div id="contenedor-grafica-actividad-vk" wire:ignore x-show="!sinDatos">
                                            <div id="mi-canvas-grafica-actividad-vk"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @foreach ($videoKitchenData['ordenes'] as $sucursal)
                            <div class="card shadow-sm">
                                <div class="card-body p-4">
                                    <span class="h5 position-absolute px-3"
                                        style="background: ghostwhite; top: -12px">{{ $sucursal['label'] }}</span>
                                    <div class="row">
                                        @foreach ($sucursal['data'] as $orden)
                                            @php
                                                $primaryClassOrdenEstado = 'info';
                                                $secondaryClassOrdenEstado = 'info';
                                                $textClassOrdenEstado = 'info';
                                                switch ($orden['estado']) {
                                                    case 1: //ABIERTA
                                                        $primaryClassOrdenEstado = 'primary';
                                                        $secondaryClassOrdenEstado = 'primary';
                                                        $textColor = '#fff';
                                                        break;
                                                    case 2: // EN PROCESO
                                                        $primaryClassOrdenEstado = 'warning';
                                                        $secondaryClassOrdenEstado = 'warning';
                                                        $textColor = '#000';
                                                        break;
                                                    case 3: // TERMINADA
                                                        $primaryClassOrdenEstado = 'success';
                                                        $secondaryClassOrdenEstado = 'success';
                                                        $textColor = '#fff';
                                                        break;
                                                    case 4: // DEMORADA
                                                        $primaryClassOrdenEstado = 'danger';
                                                        $secondaryClassOrdenEstado = 'dark';
                                                        $textColor = '#fff';
                                                        break;
                                                }
                                            @endphp
                                            <div class="col-auto">
                                                <div class="card border-{{ $primaryClassOrdenEstado }} shadow-sm">
                                                    <div class="card-header py-0 fs-4 bg-{{ $primaryClassOrdenEstado }}"
                                                        style="color: {{ $textColor }}">
                                                        <span>{{ __('site.dashboard.order') }}</span>: {{ $orden['id_transaccion'] }}
                                                    </div>
                                                    <div class="card-body bg-{{ $secondaryClassOrdenEstado }}-subtle">
                                                        @if ($orden['mesa'])
                                                            <p class="mb-2"><strong>{{ __('site.dashboard.table') }}:</strong>
                                                                {{ $orden['mesa'] }}</p>
                                                        @endif
                                                        @if ($orden['asiento'])
                                                            <p class="mb-2"><strong>{{ __('site.dashboard.seat') }}:</strong>
                                                                {{ $orden['asiento'] }}</p>
                                                        @endif
                                                        <p class="mb-2"><strong>{{ __('site.dashboard.terminal') }}:
                                                            </strong>{{ $orden['terminal'] }}</p>
                                                        <p class="mb-2"><strong>{{ __('site.dashboard.status') }}:
                                                            </strong>{{ $orden['estado'] }}</p>
                                                        @if ($orden['departamento'])
                                                            <p class="mb-2"><strong>{{ __('site.dashboard.location') }}:
                                                                </strong>{{ $orden['departamento'] }}</p>
                                                        @endif
                                                        <p class="mb-2"><strong>{{ __('site.dashboard.emitted') }}:
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
