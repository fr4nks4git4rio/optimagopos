<div class="row justify-content-start px-3 gap-4 mb-3">
    <div
        class="card col-6 col-md-2 border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <p class="fs-5 fw-bold">OPERACIONES</p>
            <p class="fs-3 text-primary m-auto">{{ max($operacionesData['operaciones'], 0) }}</p>
        </div>
    </div>
    <div
        class="card col-6 col-md-2 border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <p class="fs-5 fw-bold">TICKET PROMEDIO</p>
            <p class="fs-3 text-primary m-auto">
                ${{ number_format(max($operacionesData['ticket_promedio'], 0), 2) }}</p>
        </div>
    </div>
    <div
        class="card col-6 col-md-2 border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <p class="fs-5 fw-bold">MAYOR TICKET</p>
            <p class="fs-3 text-primary m-auto">
                ${{ number_format(max($operacionesData['mayor_ticket'], 0), 2) }}</p>
        </div>
    </div>
    <div
        class="card col-6 col-md-2 border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <p class="fs-5 fw-bold">MENOR TICKET</p>
            <p class="fs-3 text-primary m-auto">
                ${{ number_format(max($operacionesData['menor_ticket'], 0), 2) }}</p>
        </div>
    </div>
    <div
        class="card col-6 col-md-2 border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center">
        <div class="card-body align-items-center d-flex flex-column">
            <p class="fs-5 fw-bold">MULTIMONEDA</p>
            <p class="fs-3 text-primary m-auto">{{ $operacionesData['multimoneda'] }}</p>
        </div>
    </div>
    <div class="card col-6 col-md-2 border-0 border-start border-danger bg-dark-subtle shadow-sm border-4 text-center">
        <div class="card-body align-items-center d-flex flex-column">
            <p class="fs-5 fw-bold">CORRECCIONES</p>
            <p class="fs-3 text-danger m-auto">{{ $operacionesData['correcciones'] }}</p>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12 col-md-4 mb-3">
        <div x-data="{
            datosVentasHora: @entangle('operacionesData.grafica_ventas_hora'),
            chart: null,

            init() {
                const horasDelDia = Array.from({ length: 24 }, (_, i) => {
                    return i.toString().padStart(2, '0') + ':00'; // Genera ['00:00', '01:00', ..., '23:00']
                });
                // Escuchamos los cambios en los datos que vienen de Livewire
                this.$watch('datosVentasHora', value => {

                    if (value) {
                        let serie24Horas = horasDelDia.map((hora, index) => {
                            let claveSimple = index.toString();
                            let claveFormateada = hora;

                            if (value[claveFormateada] !== undefined) {
                                return Number(value[claveFormateada]);
                            } else if (value[claveSimple] !== undefined) {
                                return Number(value[claveSimple]);
                            }
                            return 0; // Si la hora no viene en tu objeto, va un cero
                        });

                        // Buscamos el elemento de forma nativa e inequívoca
                        let el = document.getElementById('mi-canvas-grafica-ventas-hora');

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
                                    series: [{ name: 'Ventas por hora', data: serie24Horas }],
                                    colors: ['#065F46'],
                                    xaxis: {
                                        type: 'category',
                                        categories: horasDelDia, // Forzamos a que siempre muestre las 24 marcas
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
                <span class="fs-5 fw-bold">Ventas por hora</span>
                <template x-if="!chart">
                    <div class="text-center py-3 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        Cargando datos...
                    </div>
                </template>
                <div id="contenedor-grafica-ventas-hora" wire:ignore>
                    <div id="mi-canvas-grafica-ventas-hora"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <div x-data="{
            datosOperacionesHora: @entangle('operacionesData.grafica_operaciones_hora'),
            chart: null,

            init() {
                const horasDelDia = Array.from({ length: 24 }, (_, i) => {
                    return i.toString().padStart(2, '0') + ':00'; // Genera ['00:00', '01:00', ..., '23:00']
                });
                // Escuchamos los cambios en los datos que vienen de Livewire
                this.$watch('datosOperacionesHora', value => {

                    if (value) {
                        let serie24Horas = horasDelDia.map((hora, index) => {
                            let claveSimple = index.toString();
                            let claveFormateada = hora;

                            if (value[claveFormateada] !== undefined) {
                                return Number(value[claveFormateada]);
                            } else if (value[claveSimple] !== undefined) {
                                return Number(value[claveSimple]);
                            }
                            return 0; // Si la hora no viene en tu objeto, va un cero
                        });

                        // Buscamos el elemento de forma nativa e inequívoca
                        let el = document.getElementById('mi-canvas-grafica-operaciones-hora');

                        if (el) {
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
                                    series: [{ name: 'Operaciones por hora', data: serie24Horas }],
                                    colors: ['#065F46'],
                                    xaxis: {
                                        type: 'category',
                                        categories: horasDelDia, // Forzamos a que siempre muestre las 24 marcas
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
                                        line: {
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
                <span class="fs-5 fw-bold">Operaciones por hora</span>
                <template x-if="!chart">
                    <div class="text-center py-3 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        Cargando datos...
                    </div>
                </template>
                <div id="contenedor-grafica-operaciones-hora" wire:ignore>
                    <div id="mi-canvas-grafica-operaciones-hora"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <div x-data="{
            datosTopTickets: @entangle('operacionesData.top_tickets'),
            chart: null,

            init() {
                // Escuchamos los cambios en los datos que vienen de Livewire
                this.$watch('datosTopTickets', value => {
                    if (value && Object.keys(value).length > 0) {

                        // 1. Convertimos el objeto de Livewire en un array de pares [clave, valor]
                        let items = Object.entries(value);

                        // 2. Extraemos las etiquetas (nombres/folios de los tickets)
                        let nombresTickets = items.map(([clave, valor]) => valor.id_transaccion);

                        // 3. Extraemos los importes (los números)
                        let importesTickets = items.map(([clave, valor]) => Number(valor.importe));

                        // Buscamos el elemento de forma nativa e inequívoca
                        let el = document.getElementById('mi-canvas-grafica-top-tickets');

                        if (el) {
                            if (!this.chart) {
                                // 1. Si el elemento existe y la gráfica NO se ha creado, la inicializamos
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
                                    series: [{ name: 'Importe', data: importesTickets }],
                                    plotOptions: {
                                        bar: {
                                            horizontal: true, // Barras de izquierda a derecha 👈 👉
                                            barHeight: '60%', // Grosor elegante para las 5 barras
                                            dataLabels: { position: 'right' }
                                        }
                                    },

                                    // EL TRUCO: Las categorías se quedan en XAXIS aunque se grafiquen en vertical
                                    xaxis: {
                                        type: 'category',
                                        categories: nombresTickets,
                                        min: 0,
                                        forceNiceScale: true,
                                        labels: {
                                            style: { fontSize: '11px' },
                                            formatter: function(val) {
                                                // Evitamos que intente ponerle signo de pesos a los nombres de los tickets
                                                return typeof val === 'number' ? '$' + val : val;
                                            }
                                        }
                                    },

                                    // El eje Y se queda meramente para dar estilo visual a los textos laterales
                                    yaxis: {
                                        labels: {
                                            style: { fontSize: '12px', fontWeight: 'bold', colors: ['#2D3142'] }
                                        }
                                    },

                                    // Si pusiste 'distributed: true', puedes pasar un array de 5 colores para tu top
                                    // Ejemplo con degradados sutiles de tu paleta Chic/Rosa:
                                    colors: ['#065F46'],

                                    dataLabels: {
                                        enabled: true,
                                        offsetX: 10,
                                        style: {
                                            fontSize: '11px',
                                            colors: ['#2D3142']
                                        },
                                        // Formateador para que el número flotante se vea como dinero (Ej: $1,250)
                                        formatter: function(val) {
                                            return '$' + val.toLocaleString();
                                        }
                                    },
                                    legend: { show: false } // Ocultamos la leyenda ya que el eje Y dice de quién es cada barra
                                };

                                this.chart = new ApexCharts(el, options);
                                this.chart.render();
                            } else {
                                // ========================================================
                                // SOLUCIÓN MAESTRA: ACTUALIZACIÓN EN UN SOLO BLOQUE UNIFICADO
                                // ========================================================
                                this.chart.updateOptions({
                                    // 1. Modificamos el eje Y (vertical) porque es horizontal: true
                                    yaxis: {
                                        categories: nombresTickets
                                    },
                                    // 2. Inyectamos los nuevos datos en la misma llamada para evitar conflictos de hilos
                                    series: [{ name: 'Importe', data: importesTickets }]
                                }, false, true); // false = no recrear el gráfico desde cero, true = animar el cambio
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
                    Top tickets
                </span>
                <template x-if="!chart">
                    <div class="text-center py-3 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        Cargando datos...
                    </div>
                </template>
                <div id="contenedor-grafica-top-tickets" wire:ignore>
                    <div id="mi-canvas-grafica-top-tickets"></div>
                </div>
            </div>
        </div>
    </div>
</div>
