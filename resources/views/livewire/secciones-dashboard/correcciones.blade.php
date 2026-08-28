@can('dashboardResume-viewCorrections')
    <div class="row g-3 mb-3 px-1">
        @foreach ($correccionesData['correcciones'] as $correction)
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card border-0 border-start border-danger bg-dark-subtle shadow-sm border-4 text-center h-100">
                    <div class="card-body align-items-center d-flex flex-column">
                        <span class="fs-5 fw-bold text-uppercase">{{ __('site.corrections.' . $correction->nombre) }}</span>
                        <span class="fs-3 text-danger m-auto">{{ $correction->cantidad }} ->
                            {{ $correction->total }}</span>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-12 col-sm-6 col-md-3">
            <div class="card border-0 border-start border-danger bg-dark-subtle shadow-sm border-4 text-center h-100">
                <div class="card-body align-items-center d-flex flex-column">
                    <span class="fs-5 fw-bold text-uppercase">{{ __('site.dashboard.influence') }}</span>
                    <span class="fs-3 text-danger m-auto">{{ $correccionesData['influencia_correcciones'] }}%</span>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-lg-6 mb-3">
            <div x-data="{
                datosCorreccionesOperador: @entangle('correccionesData.grafica_correcciones_operador').live,
                chart: null,
                sinDatos: false,

                init() {
                    this.$watch('datosCorreccionesOperador', value => {
                        const hayDatos = value && Object.keys(value).length > 0;

                        if (hayDatos) {
                            this.sinDatos = false;

                            let items = Object.entries(value);
                            let nombresOperadores = items.map(([clave, valor]) => clave);
                            let importesCorrecciones = items.map(([clave, valor]) => Number(valor));

                            this.$nextTick(() => {
                                let el = document.getElementById('mi-canvas-grafica-top-correcciones-operador');
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
                                            // Fuerza el recálculo de tamaño justo tras montar —
                                            // esto es lo que antes lograbas manualmente con la interacción.
                                            events: {
                                                mounted: function(chartContext) {
                                                    setTimeout(() => {
                                                        chartContext.windowResizeHandler();
                                                    }, 50);
                                                }
                                            }
                                        },
                                        series: [{ name: '{{ __('site.dashboard.quantity') }}', data: importesCorrecciones }],
                                        plotOptions: {
                                            bar: {
                                                horizontal: true,
                                                barHeight: '60%',
                                                distributed: true
                                            }
                                        },
                                        xaxis: {
                                            type: 'category',
                                            categories: nombresOperadores,
                                            min: 0,
                                            forceNiceScale: true,
                                            labels: {
                                                style: { fontSize: '11px' },
                                                formatter: function(val) {
                                                    return val;
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
                                        },
                                        legend: { show: false }
                                    };

                                    this.chart = new ApexCharts(el, options);
                                    this.chart.render();
                                } else {
                                    this.chart.updateOptions({
                                        xaxis: {
                                            categories: nombresOperadores,
                                            labels: {
                                                style: { fontSize: '11px' },
                                                formatter: function(val) {
                                                    return val;
                                                }
                                            }
                                        },
                                        series: [{ name: '{{ __('site.dashboard.quantity') }}', data: importesCorrecciones }]
                                    }, false, true);

                                    // Recalcula el tamaño tras la actualización también.
                                    this.$nextTick(() => this.chart.windowResizeHandler());
                                }
                            });
                        } else {
                            this.sinDatos = true;

                            if (this.chart) {
                                this.chart.updateOptions({
                                    xaxis: { categories: [] },
                                    series: [{ name: '{{ __('site.dashboard.quantity') }}', data: [] }]
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
                        {{ __('site.dashboard.operator_corrections') }}
                    </span>
                    <template x-if="!chart && !sinDatos">
                        <div class="text-center py-3 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            {{ __('site.dashboard.loading_data') }}...
                        </div>
                    </template>
                    <template x-if="sinDatos">
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-tools fs-3 d-block mb-1"></i>
                            {{ __('site.dashboard.no_data') }}
                        </div>
                    </template>
                    <div id="contenedor-grafica-top-correcciones-operador" wire:ignore x-show="!sinDatos">
                        <div id="mi-canvas-grafica-top-correcciones-operador"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6 mb-3">
            <div x-data="{
                datosCorreccionesHora: @entangle('correccionesData.grafica_correcciones_hora').live,
                chart: null,
                sinDatos: false,
                horasDelDia: [],

                init() {
                    this.horasDelDia = Array.from({ length: 24 }, (_, i) => {
                        return i.toString().padStart(2, '0') + ':00';
                    });

                    this.$watch('datosCorreccionesHora', value => {
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
                                let el = document.getElementById('mi-canvas-grafica-correcciones-hora');
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
                                        series: [{ name: '{{ __('site.dashboard.hourly_corrections') }}', data: serie24Horas }],
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
                    <span class="fs-5 fw-bold">{{ __('site.dashboard.hourly_corrections') }}</span>
                    <template x-if="!chart && !sinDatos">
                        <div class="text-center py-3 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            {{ __('site.dashboard.loading_data') }}...
                        </div>
                    </template>
                    <template x-if="sinDatos">
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-tools fs-3 d-block mb-1"></i>
                            {{ __('site.dashboard.no_data') }}
                        </div>
                    </template>
                    <div id="contenedor-grafica-correcciones-hora" wire:ignore x-show="!sinDatos">
                        <div id="mi-canvas-grafica-correcciones-hora"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endcan
