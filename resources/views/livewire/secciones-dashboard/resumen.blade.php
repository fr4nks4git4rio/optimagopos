@can('dashboardResume-viewSummary')
    <div class="row g-3 mb-3 px-1">
        <div class="col-12 col-sm-6 col-lg">
            <div
                class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray h-100">
                <div class="card-body align-items-center d-flex flex-column">
                    <p class="fw-bold text-uppercase fs-5 dashboard-card-title">{{ __('site.dashboard.sale_operations') }}</p>
                    <p class="fs-3 text-primary m-auto  dashboard-card-value">{{ max($resumenData['operaciones'], 0) }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg">
            <div
                class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray h-100">
                <div class="card-body align-items-center d-flex flex-column">
                    <span
                        class="fs-5 fw-bold text-uppercase dashboard-card-title">{{ __('site.dashboard.total_sale') }}</span>
                    <span
                        class="fs-3 text-primary m-auto  dashboard-card-value">${{ number_format(max($resumenData['ventas_totales'], 0), 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg">
            <div
                class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray h-100">
                <div class="card-body align-items-center d-flex flex-column">
                    <span
                        class="fs-5 fw-bold text-uppercase dashboard-card-title">{{ __('site.dashboard.incomes') }}</span>
                    <span
                        class="fs-3  text-primary m-auto  dashboard-card-value">${{ number_format(max($resumenData['ingresos'], 0), 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg">
            <div
                class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray h-100">
                <div class="card-body align-items-center d-flex flex-column">
                    <p class="fs-5 fw-bold text-uppercase dashboard-card-title">{{ __('site.dashboard.multi_currency') }}
                    </p>
                    <p class="fs-3 text-primary m-auto  dashboard-card-value">{{ max($resumenData['multimoneda'], 0) }}</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg">
            <div
                class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray h-100">
                <div class="card-body align-items-center d-flex flex-column">
                    <p class="fs-5 fw-bold text-uppercase dashboard-card-title">{{ __('site.dashboard.items_sold') }}</p>
                    <p class="fs-3 text-primary m-auto  dashboard-card-value">
                        {{ $resumenData['articulos_vendidos'] ?: 0 }}</p>
                </div>
            </div>
        </div>
        <div class="row g-3 px-1 mb-3">
            <div class="col-12 col-md-4">
                <div class="card border-0 border-start border-danger bg-dark-subtle shadow-sm border-4 text-center bg-gray">
                    <div class="card-body align-items-center d-flex flex-column">
                        <p class="fs-5 fw-bold text-uppercase dashboard-card-title">
                            {{ __('site.dashboard.amount_refunded') }}</p>
                        <p class="fs-3 text-danger m-auto  dashboard-card-value">
                            ${{ number_format(max($resumenData['importes_devueltos'], 0), 2) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 border-start border-danger bg-dark-subtle shadow-sm border-4 text-center bg-gray">
                    <div class="card-body align-items-center d-flex flex-column">
                        <p class="fs-5 fw-bold text-uppercase dashboard-card-title">{{ __('site.dashboard.deletes') }}</p>
                        <p class="fs-3 text-danger m-auto  dashboard-card-value">{{ max($resumenData['deletes'], 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 border-start border-danger bg-dark-subtle shadow-sm border-4 text-center bg-gray">
                    <div class="card-body align-items-center d-flex flex-column">
                        <p class="fs-5 fw-bold text-uppercase dashboard-card-title">{{ __('site.dashboard.cancels') }}</p>
                        <p class="fs-3 text-danger m-auto  dashboard-card-value">{{ max($resumenData['cancels'], 0) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-lg-4 mb-3">
            <div x-data="{
                datosServidor: @entangle('resumenData.ventas_netas_operacion').live,
                chart: null,
                sinDatos: false,

                init() {
                    this.$watch('datosServidor', value => {
                        const hayDatos = value && Object.keys(value).length > 0;

                        if (hayDatos) {
                            this.sinDatos = false;

                            let datosFormateados = Object.values(value).map((num, index) => {
                                return { x: (index + 1).toString(), y: num };
                            });

                            // Esperamos a que Alpine aplique x-show y el layout se estabilice
                            // ANTES de medir el contenedor o crear/actualizar el chart.
                            this.$nextTick(() => {
                                let el = document.getElementById('mi-canvas-grafica-venta-neta');
                                if (!el) return;

                                if (!this.chart) {
                                    let options = {
                                        chart: {
                                            type: 'bar',
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
                                            // Fuerza un recálculo de tamaño justo después de montar el SVG,
                                            // que es exactamente lo que hace tu interacción manual (resize/scroll).
                                            events: {
                                                mounted: function(chartContext) {
                                                    setTimeout(() => {
                                                        chartContext.windowResizeHandler();
                                                    }, 50);
                                                }
                                            }
                                        },
                                        series: [{ name: '{{ __('site.dashboard.metrics') }}', data: datosFormateados }],
                                        xaxis: { type: 'category' },
                                        colors: ['#065F46'],
                                        plotOptions: {
                                            bar: {
                                                columnWidth: '90%',
                                                dataLabels: { position: 'top' }
                                            }
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
                                    this.chart.updateSeries([{ data: datosFormateados }]);
                                    // También forzamos recálculo en actualizaciones, por si el contenedor
                                    // cambió de tamaño mientras estaba oculto (sinDatos true → false).
                                    this.$nextTick(() => this.chart.windowResizeHandler());
                                }
                            });
                        } else {
                            this.sinDatos = true;
                            if (this.chart) {
                                this.chart.updateSeries([{ data: [] }]);
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
                        {{ __('site.dashboard.net_sale_by_operation') }}
                    </span>
                    <template x-if="!chart && !sinDatos">
                        <div class="text-center py-3 text-muted">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            {{ __('site.dashboard.loading_data') }}...
                        </div>
                    </template>
                    <template x-if="sinDatos">
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-bar-chart-line fs-3 d-block mb-1"></i>
                            {{ __('site.dashboard.no_data') }}
                        </div>
                    </template>
                    <div id="contenedor-grafica-venta-neta" wire:ignore x-show="!sinDatos">
                        <div id="mi-canvas-grafica-venta-neta"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4 mb-3">
            <div x-data="{
                datosActividad: @entangle('resumenData.grafica_actividad').live,
                chart: null,
                sinDatos: false,
                horasDelDia: [],

                init() {
                    this.horasDelDia = Array.from({ length: 24 }, (_, i) => {
                        return i.toString().padStart(2, '0') + ':00';
                    });

                    this.$watch('datosActividad', value => {
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
                                let el = document.getElementById('mi-canvas-grafica-actividad');
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
                                        plotOptions: {
                                            bar: {
                                                columnWidth: '75%',
                                                dataLabels: { position: 'top' }
                                            }
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
                    <span class="fs-5 fw-bold">{{ __('site.dashboard.activity') }}</span>
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
                    <div id="contenedor-grafica-actividad" wire:ignore x-show="!sinDatos">
                        <div id="mi-canvas-grafica-actividad"></div>
                    </div>
                </div>
            </div>
        </div>
        @if ($resumenData['ultimo_ticket'])
            <div class="col-12 col-md-4 mb-3">
                <div class="card shadow-sm bg-site-primary-subtle">
                    <div class="card-body">
                        <span class="fs-5 fw-bold">{{ __('site.dashboard.last_operation') }}</span>
                        <p class="fs-5 text-center">{{ __('site.dashboard.ticket') }}:
                            {{ $resumenData['ultimo_ticket']['id_transaccion'] }}
                        </p>
                        <p class="fs-5 text-center">{{ __('site.dashboard.date') }}:
                            {{ Illuminate\Support\Carbon::parse($resumenData['ultimo_ticket']['fecha_transaccion'])->format('d/m/Y') }}
                            - {{ __('site.dashboard.time') }}:
                            {{ Illuminate\Support\Carbon::parse($resumenData['ultimo_ticket']['fecha_transaccion'])->format('H:i:s') }}
                        </p>
                        <p class="fs-5 text-center">{{ __('site.dashboard.dlpos') }}:
                            {{ $resumenData['ultimo_ticket']['nombre'] }}
                            - {{ __('site.dashboard.cashier') }}:
                            {{ $resumenData['ultimo_ticket']['empleado'] ? Illuminate\Support\Facades\Crypt::decrypt($resumenData['ultimo_ticket']['empleado']) : '' }}
                        </p>
                        <p class="fs-5 text-center">
                            {{ __('site.dashboard.status') }}: <span
                                class="badge bg-success-subtle text-success">{{ __('site.dashboard.ok') }}</span>
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endcan
