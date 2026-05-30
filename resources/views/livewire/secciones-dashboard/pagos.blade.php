<div class="grid-cols-4 px-1 mb-3">
    <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <span class="fs-5 fw-bold">VENTA NETA</span>
            <span class="fs-3 text-primary m-auto">${{ number_format(max($pagosData['ventas_netas'], 0), 2) }}</span>
        </div>
    </div>
    <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <span class="fs-5 fw-bold">VENTAS POR FORMA DE PAGO</span>
            @foreach ($pagosData['ventas_formas_pago'] as $index => $venta_forma_pago)
                <span class="fs-3 text-primary">${{ number_format(max($venta_forma_pago, 0), 2) }} -
                    {{ $index }}</span>
            @endforeach
        </div>
    </div>
    <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <span class="fs-5 fw-bold">CANTIDAD POR FORMA DE PAGO</span>
            @foreach ($pagosData['cantidad_formas_pago'] as $index => $cantidad_forma_pago)
                <span class="fs-3 text-primary">{{ max($cantidad_forma_pago, 0) }} - {{ $index }}</span>
            @endforeach
        </div>
    </div>
    <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
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
                        }
                    }
                });
            },
            destroy() {
                if (this.chart) this.chart.destroy();
            }
        }" class="card shadow-sm bg-site-primary-subtle">
            <div class="card-body">
                <span class="fs-5 fw-bold">Comportamiento de Pagos</span>
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
</div>
