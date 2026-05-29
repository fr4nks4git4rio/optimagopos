<div class="row justify-content-start px-3 gap-4 mb-3">
    <div
        class="card col-6 col-md-2 border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <span class="fs-5 fw-bold">VENTA NETA</span>
            @foreach ($pagosData['ventas_netas'] as $venta_neta)
                <span class="fs-3  text-primary">${{ number_format(max($venta_neta['monto'], 0), 2) }}
                    {{ $venta_neta['moneda'] }}</span>
            @endforeach
        </div>
    </div>
    <div
        class="card col-6 col-md-2 border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <span class="fs-5 fw-bold">VENTA TOTAL</span>
            @foreach ($pagosData['ventas_totales'] as $venta_total)
                <span class="fs-3 text-primary">${{ number_format(max($venta_total['monto'], 0), 2) }}
                    {{ $venta_total['moneda'] }}</span>
            @endforeach
        </div>
    </div>
    <div
        class="card col-6 col-md-2 border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <span class="fs-5 fw-bold">MÉTODO DOMINANTE</span>
            <span class="fs-3 text-primary m-auto">{{ $pagosData['metodo_pago_dominante'] }}</span>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12 col-md-6">
        <div x-data="{
            datosMetodosPagos: @entangle('pagosData.grafica_metodos_pago'), // Tu objeto de Livewire con los datos
            chart: null,

            init() {
                this.$watch('datosMetodosPagos', value => {
                    if (value && Object.keys(value).length > 0) {

                        let items = Object.entries(value);
                        // Las llaves del objeto serán los nombres de los productos
                        let nombresProductos = items.map(([clave, valor]) => clave);
                        // Los valores serán los números de presencia (frecuencia)
                        let presenciaValores = items.map(([clave, valor]) => Number(valor));

                        let el = document.getElementById('mi-canvas-grafica-metodos-pagos');

                        if (el) {
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
                                                        label: 'Formas de pago',
                                                        color: '#2D3142',
                                                        formatter: function(w) {
                                                            // Suma todos los valores para mostrar el total en el centro
                                                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
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
                                            return opts.w.globals.series[opts.seriesIndex];
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
                        }
                    }
                });
            },
            destroy() {
                if (this.chart) this.chart.destroy();
            }
        }" class="card shadow-sm bg-site-primary-subtle">
            <div class="card-body">
                <span class="fs-5 fw-bold">Actividad</span>
                <template x-if="!chart">
                    <div class="text-center py-3 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        Cargando datos...
                    </div>
                </template>
                <div id="contenedor-metodos-pagos" wire:ignore>
                    <div id="mi-canvas-grafica-metodos-pagos"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 mb-3">
        <div x-data="{
            datosComportamientoPagos: @entangle('pagosData.grafica_comportamiento_pagos'),
            chart: null,

            init() {
                // Escuchamos los cambios en los datos que vienen de Livewire
                this.$watch('datosComportamientoPagos', value => {
                    if (value && Object.keys(value).length > 0) {

                        let datosFormateados = Object.entries(value).map(([key, num], index) => {
                            return { x: key, y: num };
                        });

                        // Buscamos el elemento de forma nativa e inequívoca
                        let el = document.getElementById('mi-canvas-grafica-comportamiento-pagos');

                        if (el) {
                            if (!this.chart) {
                                // 1. Si el elemento existe y la gráfica NO se ha creado, la inicializamos
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
                                    series: [{ name: 'Métrica', data: datosFormateados }],
                                    xaxis: {
                                        type: 'category',
                                    },
                                    colors: ['#065F46'],
                                    // 2. CONFIGURAR EL COMPORTAMIENTO DE LAS BARRAS
                                    plotOptions: {
                                        bar: {
                                            // Controla el ancho máximo de la barra para que no ocupe toda la pantalla si está sola
                                            columnWidth: '90%',
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
                                this.chart.updateSeries([{ data: datosFormateados }]);
                            }
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
                    Comportamiento de pagos
                </span>
                <template x-if="!chart">
                    <div class="text-center py-3 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        Cargando datos...
                    </div>
                </template>
                <div id="contenedor-grafica-comportamiento-pagos" wire:ignore>
                    <div id="mi-canvas-grafica-comportamiento-pagos"></div>
                </div>
            </div>
        </div>
    </div>
</div>
