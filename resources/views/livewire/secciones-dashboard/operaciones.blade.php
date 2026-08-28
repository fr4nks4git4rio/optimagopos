@can('dashboardResume-viewTransactions')
    <div class="row g-3 mb-3 px-1">
        <div class="col-12 col-sm-6 col-lg">
            <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center h-100">
                <div class="card-body align-items-center d-flex flex-column">
                    <p class="fs-5 fw-bold text-uppercase">{{ __('site.dashboard.operations') }}</p>
                    <p class="fs-3 text-primary m-auto">{{ max($operacionesData['operaciones'], 0) }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg">
            <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center h-100">
                <div class="card-body align-items-center d-flex flex-column">
                    <p class="fs-5 fw-bold text-uppercase">{{ __('site.dashboard.average_ticket') }}</p>
                    <p class="fs-3 text-primary m-auto">
                        ${{ number_format(max($operacionesData['ticket_promedio'], 0), 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg">
            <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center h-100">
                <div class="card-body align-items-center d-flex flex-column">
                    <p class="fs-5 fw-bold text-uppercase">{{ __('site.dashboard.higher_ticket') }}</p>
                    <p class="fs-3 text-primary m-auto">
                        ${{ number_format(max($operacionesData['mayor_ticket'], 0), 2) }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg">
            <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center h-100">
                <div class="card-body align-items-center d-flex flex-column">
                    <p class="fs-5 fw-bold text-uppercase">{{ __('site.dashboard.lower_ticket') }}</p>
                    <p class="fs-3 text-primary m-auto">
                        ${{ number_format(max($operacionesData['menor_ticket'], 0), 2) }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mb-3 px-1">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card border-0 border-start border-danger bg-dark-subtle shadow-sm border-4 text-center h-100">
                <div class="card-body align-items-center d-flex flex-column">
                    <p class="fs-5 fw-bold text-uppercase">{{ __('site.dashboard.corrections') }}</p>
                    <p class="fs-3 text-danger m-auto">{{ $operacionesData['correcciones'] }}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-lg-6 mb-3">
            <div x-data="{
                datosOperacionesHora: @entangle('operacionesData.grafica_operaciones_hora').live,
                chart: null,
                sinDatos: false,
                horasDelDia: [],

                init() {
                    this.horasDelDia = Array.from({ length: 24 }, (_, i) => {
                        return i.toString().padStart(2, '0') + ':00';
                    });

                    this.$watch('datosOperacionesHora', value => {
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
                                let el = document.getElementById('mi-canvas-grafica-operaciones-hora');
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
                                        series: [{ name: '{{ __('site.dashboard.hourly_operations') }}', data: serie24Horas }],
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
                    <span class="fs-5 fw-bold">{{ __('site.dashboard.hourly_operations') }}</span>
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
                    <div id="contenedor-grafica-operaciones-hora" wire:ignore x-show="!sinDatos">
                        <div id="mi-canvas-grafica-operaciones-hora"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6 mb-3">
            <div x-data="{
                datosTopTickets: @entangle('operacionesData.top_tickets').live,
                chart: null,
                sinDatos: false,

                init() {
                    this.$watch('datosTopTickets', value => {
                        const hayDatos = value && Object.keys(value).length > 0;

                        if (hayDatos) {
                            this.sinDatos = false;

                            let items = Object.entries(value);
                            let nombresTickets = items.map(([clave, valor]) => valor.id_transaccion);
                            let importesTickets = items.map(([clave, valor]) => Number(valor.importe));

                            this.$nextTick(() => {
                                let el = document.getElementById('mi-canvas-grafica-top-tickets');
                                if (!el) return;

                                if (!this.chart) {
                                    let options = {
                                        chart: {
                                            type: 'bar',
                                            height: 400,
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
                                        series: [{ name: 'Importe', data: importesTickets }],
                                        plotOptions: {
                                            bar: {
                                                horizontal: true,
                                                barHeight: '60%'
                                            }
                                        },
                                        xaxis: {
                                            type: 'category',
                                            categories: nombresTickets,
                                            min: 0,
                                            forceNiceScale: true,
                                            labels: {
                                                style: { fontSize: '11px' },
                                                formatter: function(val) {
                                                    return typeof val === 'number' ? '$' + val : val;
                                                }
                                            }
                                        },
                                        yaxis: {
                                            labels: {
                                                style: { fontSize: '12px', fontWeight: 'bold', colors: ['#2D3142'] }
                                            }
                                        },
                                        colors: ['#065F46'],
                                        dataLabels: {
                                            enabled: true,
                                            style: {
                                                fontSize: '16px',
                                                colors: ['#C29A6B']
                                            },
                                            formatter: function(val) {
                                                return '$' + val.toLocaleString();
                                            }
                                        },
                                        legend: { show: false }
                                    };

                                    this.chart = new ApexCharts(el, options);
                                    this.chart.render();
                                } else {
                                    this.chart.updateOptions({
                                        xaxis: {
                                            categories: nombresTickets,
                                            labels: {
                                                style: { fontSize: '11px' },
                                                formatter: function(val) {
                                                    return typeof val === 'number' ? '$' + val : val;
                                                }
                                            }
                                        },
                                        series: [{ name: 'Importe', data: importesTickets }]
                                    }, false, true);

                                    this.$nextTick(() => this.chart.windowResizeHandler());
                                }
                            });
                        } else {
                            this.sinDatos = true;

                            if (this.chart) {
                                this.chart.updateOptions({
                                    xaxis: { categories: [] },
                                    series: [{ name: 'Importe', data: [] }]
                                }, false, true);
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
                    <span class="fs-5 fw-bold">
                        {{ __('site.dashboard.top_tickets') }}
                    </span>
                    <template x-if="!chart && !sinDatos">
                        <div class="text-center py-3 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            {{ __('site.dashboard.loading_data') }}...
                        </div>
                    </template>
                    <template x-if="sinDatos">
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-receipt fs-3 d-block mb-1"></i>
                            {{ __('site.dashboard.no_data') }}
                        </div>
                    </template>
                    <div id="contenedor-grafica-top-tickets" wire:ignore x-show="!sinDatos">
                        <div id="mi-canvas-grafica-top-tickets"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endcan
