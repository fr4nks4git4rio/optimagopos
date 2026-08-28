@can('dashboardResume-viewPayments')
    <div class="row g-3 mb-3 px-1">
        <div class="col-12 col-sm-6 col-lg">
            <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center h-100">
                <div class="card-body align-items-center d-flex flex-column">
                    <span class="fs-5 fw-bold text-uppercase">{{ __('site.dashboard.incomes') }}</span>
                    <span class="fs-3 text-primary m-auto">${{ number_format(max($pagosData['ingresos'], 0), 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg">
            <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center h-100">
                <div class="card-body align-items-center d-flex flex-column">
                    <span class="fs-5 fw-bold text-uppercase">{{ __('site.dashboard.incomes_by_payment_form') }}</span>
                    @if (count($pagosData['ventas_formas_pago']) == 0)
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-credit-card fs-3 d-block mb-1"></i>
                            {{ __('site.dashboard.no_data') }}
                        </div>
                    @endif
                    @foreach ($pagosData['ventas_formas_pago'] as $index => $venta_forma_pago)
                        <span class="fs-3 text-primary">${{ number_format(max($venta_forma_pago, 0), 2) }} -
                            {{ $index }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg">
            <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center h-100">
                <div class="card-body align-items-center d-flex flex-column">
                    <span class="fs-5 fw-bold text-uppercase">{{ __('site.dashboard.quantity_by_payment_form') }}</span>
                    @if (count($pagosData['cantidad_formas_pago']) == 0)
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-credit-card fs-3 d-block mb-1"></i>
                            {{ __('site.dashboard.no_data') }}
                        </div>
                    @endif
                    @foreach ($pagosData['cantidad_formas_pago'] as $index => $cantidad_forma_pago)
                        <span class="fs-3 text-primary">{{ max($cantidad_forma_pago, 0) }} - {{ $index }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg">
            <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center h-100">
                <div class="card-body align-items-center d-flex flex-column">
                    <span class="fs-5 fw-bold text-uppercase">{{ __('site.dashboard.dominant_method') }}</span>
                    @if (!$pagosData['metodo_pago_dominante'])
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-credit-card fs-3 d-block mb-1"></i>
                            {{ __('site.dashboard.no_data') }}
                        </div>
                    @else
                        <span class="fs-3 text-primary m-auto">{{ $pagosData['metodo_pago_dominante'] }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg">
            <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center h-100">
                <div class="card-body align-items-center d-flex flex-column">
                    <p class="fs-5 fw-bold text-uppercase">{{ __('site.dashboard.multi_currency') }}</p>
                    <p class="fs-3 text-primary m-auto">{{ $pagosData['multimoneda'] }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-lg-6">
            <div x-data="{
                datosMetodosPagos: @entangle('pagosData.grafica_metodos_pago').live, // Tu objeto de Livewire con los datos
                chart: null,
                sinDatos: false,

                init() {
                    this.$watch('datosMetodosPagos', value => {
                        const hayDatos = value && Object.keys(value).length > 0;

                        if (hayDatos) {
                            this.sinDatos = false;

                            let items = Object.entries(value);
                            let nombresProductos = items.map(([clave, valor]) => clave);
                            let presenciaValores = items.map(([clave, valor]) => Number(valor));

                            this.$nextTick(() => {
                                let el = document.getElementById('mi-canvas-grafica-metodos-pagos');
                                if (!el) return;

                                if (!this.chart) {
                                    let options = {
                                        chart: {
                                            type: 'donut',
                                            height: 320,
                                            animations: {
                                                enabled: true,
                                                easing: 'smooth',
                                                dynamicAnimation: { speed: 500 }
                                            },
                                            toolbar: {
                                                show: true,
                                                offsetY: -30,
                                                tools: {
                                                    download: true,
                                                    selection: false,
                                                    zoom: false,
                                                    zoomin: false,
                                                    zoomout: false,
                                                    pan: false,
                                                    reset: false
                                                }
                                            },
                                            events: {
                                                mounted: function(chartContext) {
                                                    setTimeout(() => {
                                                        chartContext.windowResizeHandler();
                                                    }, 50);
                                                }
                                            }
                                        },
                                        series: presenciaValores,
                                        labels: nombresProductos,
                                        colors: [
                                            '#E6194B', '#7FB98E', '#06524B', '#FFE220', '#B77A8C',
                                            '#E69414', '#67CECE', '#008E50', '#FF19CB', '#C4E900'
                                        ],
                                        plotOptions: {
                                            pie: {
                                                donut: {
                                                    size: '50%',
                                                    labels: {
                                                        show: true,
                                                        total: {
                                                            show: true,
                                                            label: '{{ __('site.dashboard.payment_forms') }}',
                                                            color: '#2D3142',
                                                            formatter: function(w) {
                                                                return Math.round(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) + '%';
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        },
                                        dataLabels: {
                                            enabled: true,
                                            formatter: function(val, opts) {
                                                return opts.w.globals.series[opts.seriesIndex] + '%';
                                            },
                                            style: { fontSize: '11px', colors: ['#fff'] }
                                        },
                                        legend: {
                                            position: 'bottom',
                                            fontFamily: 'Helvetica, Arial',
                                            fontSize: '12px',
                                            labels: { colors: '#2D3142' }
                                        }
                                    };

                                    this.chart = new ApexCharts(el, options);
                                    this.chart.render();
                                } else {
                                    this.chart.updateOptions({
                                        labels: nombresProductos
                                    }, false, true);

                                    this.chart.updateSeries(presenciaValores);

                                    this.$nextTick(() => this.chart.windowResizeHandler());
                                }
                            });
                        } else {
                            this.sinDatos = true;

                            if (this.chart) {
                                this.chart.updateOptions({
                                    labels: []
                                }, false, true);
                                this.chart.updateSeries([]);
                            }
                        }
                    });
                },
                destroy() {
                    if (this.chart) this.chart.destroy();
                }
            }" class="card shadow-sm bg-site-primary-subtle">
                <div class="card-body">
                    <span class="fs-5 fw-bold">{{ __('site.dashboard.sales_behavior') }}</span>
                    <template x-if="!chart && !sinDatos">
                        <div class="text-center py-3 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            {{ __('site.dashboard.loading_data') }}...
                        </div>
                    </template>
                    <template x-if="sinDatos">
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-credit-card fs-3 d-block mb-1"></i>
                            {{ __('site.dashboard.no_data') }}
                        </div>
                    </template>
                    <div id="contenedor-metodos-pagos" wire:ignore x-show="!sinDatos">
                        <div id="mi-canvas-grafica-metodos-pagos"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6 mb-3">
            <div x-data="{
                datosPagosHora: @entangle('pagosData.grafica_comportamiento_pagos_hora').live, // Tu objeto de Livewire con los datos
                chart: null,
                sinDatos: false,
                horasDelDia: [],

                init() {
                    this.horasDelDia = Array.from({ length: 24 }, (_, i) => {
                        return i.toString().padStart(2, '0') + ':00';
                    });

                    this.$watch('datosPagosHora', value => {
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
                                return 0;
                            });

                            this.$nextTick(() => {
                                let el = document.getElementById('mi-canvas-grafica-pagos-hora');
                                if (!el) return;

                                if (!this.chart) {
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
                                                    download: true,
                                                    selection: false,
                                                    zoom: false,
                                                    zoomin: false,
                                                    zoomout: false,
                                                    pan: false,
                                                    reset: false
                                                }
                                            },
                                            events: {
                                                mounted: function(chartContext) {
                                                    setTimeout(() => {
                                                        chartContext.windowResizeHandler();
                                                    }, 50);
                                                }
                                            }
                                        },
                                        series: [{ name: '{{ __('site.dashboard.operations') }}', data: serie24Horas }],
                                        colors: ['#065F46'],
                                        xaxis: {
                                            type: 'category',
                                            categories: this.horasDelDia,
                                            labels: {
                                                rotate: -45,
                                                style: { fontSize: '10px' }
                                            }
                                        },
                                        yaxis: {
                                            min: 0,
                                            forceNiceScale: true
                                        },
                                        dataLabels: {
                                            enabled: true,
                                            offsetY: -20,
                                            style: { fontSize: '9px', colors: ['#304758'] },
                                            formatter: function(val) {
                                                return val > 0 ? val : '';
                                            }
                                        }
                                    };

                                    this.chart = new ApexCharts(el, options);
                                    this.chart.render();
                                } else {
                                    this.chart.updateSeries([{ data: serie24Horas }]);
                                    this.$nextTick(() => this.chart.windowResizeHandler());
                                }
                            });
                        } else {
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
                    <span class="fs-5 fw-bold">{{ __('site.dashboard.hourly_payments') }}</span>
                    <template x-if="!chart && !sinDatos">
                        <div class="text-center py-3 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            {{ __('site.dashboard.loading_data') }}...
                        </div>
                    </template>
                    <template x-if="sinDatos">
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-graph-down fs-3 d-block mb-1"></i>
                            {{ __('site.dashboard.no_data') }}
                        </div>
                    </template>
                    <div id="contenedor-grafica-pagos-hora" wire:ignore x-show="!sinDatos">
                        <div id="mi-canvas-grafica-pagos-hora"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endcan
