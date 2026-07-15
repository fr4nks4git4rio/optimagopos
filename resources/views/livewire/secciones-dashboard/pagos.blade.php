<div class="grid-cols-4 px-1 mb-3">
    <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <span class="fs-5 fw-bold text-uppercase">{{ __('site.dashboard.net_sale') }}</span>
            <span class="fs-3 text-primary m-auto">${{ number_format(max($pagosData['ventas_netas'], 0), 2) }}</span>
        </div>
    </div>
    <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <span class="fs-5 fw-bold text-uppercase">{{ __('site.dashboard.net_sale_by_payment_form') }}</span>
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
    <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
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
    <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <span class="fs-5 fw-bold text-uppercase">{{ __('site.dashboard.dominant_method') }}</span>
            <span class="fs-3 text-primary m-auto">{{ $pagosData['metodo_pago_dominante'] }}</span>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12 col-md-6">
        <div x-data="{
            datosMetodosPagos: @entangle('pagosData.grafica_metodos_pago'), // Tu objeto de Livewire con los datos
            chart: null,
            sinDatos: false,

            init() {
                this.$watch('datosMetodosPagos', value => {
                    let el = document.getElementById('mi-canvas-grafica-metodos-pagos');
                    if (!el) return;

                    const hayDatos = value && Object.keys(value).length > 0;

                    if (hayDatos) {
                        this.sinDatos = false;

                        let items = Object.entries(value);
                        // Las llaves del objeto serán los nombres de los productos
                        let nombresProductos = items.map(([clave, valor]) => clave);
                        // Los valores serán los números de presencia (frecuencia)
                        let presenciaValores = items.map(([clave, valor]) => Number(valor));

                        if (!this.chart) {
                            let options = {
                                // Definimos el tipo de gráfica como 'donut' (o 'pie' si la prefieres cerrada)
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
                                // En las gráficas de pastel, la serie es un ARRAY PLANO DE NÚMEROS
                                series: presenciaValores,

                                // Los nombres de los productos se asignan en la propiedad 'labels'
                                labels: nombresProductos,

                                // Tu paleta estética
                                colors: [
                                    '#E6194B', // Rojo Intenso
                                    '#7FB98E', // Verde Esmeralda
                                    '#06524B', // Verde Azulado Oscuro
                                    '#FFE220', // Amarillo Vibrante
                                    '#B77A8C', // Malva Profundo
                                    '#E69414', // Naranja Dorado
                                    '#67CECE', // Cian Pastel
                                    '#008E50', // Verde Bosque
                                    '#FF19CB', // Rosa Fuchsia
                                    '#C4E900' // Lima Eléctrico
                                ],

                                plotOptions: {
                                    pie: {
                                        donut: {
                                            size: '50%', // Grosor de la dona
                                            labels: {
                                                show: true,
                                                total: {
                                                    show: true,
                                                    label: '{{ __('site.dashboard.payment_forms') }}',
                                                    color: '#2D3142',
                                                    formatter: function(w) {
                                                        // Suma todos los valores para mostrar el total en el centro
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
                                        // Muestra el porcentaje que representa cada producto en la dona
                                        return opts.w.globals.series[opts.seriesIndex] + '%';
                                    },
                                    style: { fontSize: '11px', colors: ['#fff'] }
                                },
                                legend: {
                                    position: 'bottom', // Leyendas abajo para que quepa bien en tarjetas responsivas
                                    fontFamily: 'Helvetica, Arial',
                                    fontSize: '12px',
                                    labels: { colors: '#2D3142' }
                                }
                            };
                            this.chart = new ApexCharts(el, options);
                            this.chart.render();
                        } else {
                            // ACTUALIZACIÓN SÍNCRONA SEGUNDO A SEGUNDO
                            this.chart.updateOptions({
                                labels: nombresProductos
                            }, false, true);

                            this.chart.updateSeries(presenciaValores);
                        }
                    } else {
                        // Resultado vacío/null: limpiamos la serie y las etiquetas en vez de dejar las anteriores.
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
    <div class="col-12 col-md-6 mb-3">
        <div x-data="{
            datosPagosHora: @entangle('pagosData.grafica_comportamiento_pagos_hora'), // Tu objeto de Livewire con los datos
            chart: null,
            sinDatos: false,
            horasDelDia: [],

            init() {
                this.horasDelDia = Array.from({ length: 24 }, (_, i) => {
                    return i.toString().padStart(2, '0') + ':00'; // Genera ['00:00', '01:00', ..., '23:00']
                });

                // Escuchamos los cambios en los datos que vienen de Livewire
                this.$watch('datosPagosHora', value => {
                    let el = document.getElementById('mi-canvas-grafica-pagos-hora');
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
