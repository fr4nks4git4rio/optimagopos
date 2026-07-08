<div class="grid-cols-4 px-1 mb-3">
    <div class="card border-0 border-start border-danger bg-dark-subtle shadow-sm border-4 text-center">
        <div class="card-body align-items-center d-flex flex-column">
            <span class="fs-5 fw-bold">CORRECCIONES</span>
            <span class="fs-3 text-danger m-auto">{{ max($correccionesData['correcciones'], 0) }}</span>
        </div>
    </div>
    <div class="card border-0 border-start border-danger bg-dark-subtle shadow-sm border-4 text-center">
        <div class="card-body align-items-center d-flex flex-column">
            <span class="fs-5 fw-bold">DELETES</span>
            <span class="fs-3 text-danger m-auto">{{ max($correccionesData['deletes'], 0) }}</span>
        </div>
    </div>
    <div class="card border-0 border-start border-danger bg-dark-subtle shadow-sm border-4 text-center">
        <div class="card-body align-items-center d-flex flex-column">
            <span class="fs-5 fw-bold">CANCELS</span>
            <span class="fs-3 text-danger m-auto">{{ max($correccionesData['cancels'], 0) }}</span>
        </div>
    </div>
    <div class="card border-0 border-start border-danger bg-dark-subtle shadow-sm border-4 text-center">
        <div class="card-body align-items-center d-flex flex-column">
            <span class="fs-5 fw-bold">INFLUENCIA</span>
            <span class="fs-3 text-danger m-auto">{{ $correccionesData['influencia_correcciones'] }}%</span>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12 col-md-6 mb-3">
        <div x-data="{
            datosCorreccionesOperador: @entangle('correccionesData.grafica_correcciones_operador'),
            chart: null,
            sinDatos: false,

            init() {
                // Escuchamos los cambios en los datos que vienen de Livewire
                this.$watch('datosCorreccionesOperador', value => {
                    let el = document.getElementById('mi-canvas-grafica-top-correcciones-operador');
                    if (!el) return;

                    const hayDatos = value && Object.keys(value).length > 0;

                    if (hayDatos) {
                        this.sinDatos = false;

                        // 1. Convertimos el objeto de Livewire en un array de pares [clave, valor]
                        let items = Object.entries(value);

                        // 2. Extraemos las etiquetas (nombres/folios de los tickets)
                        let nombresOperadores = items.map(([clave, valor]) => clave);

                        // 3. Extraemos los importes (los números)
                        let importesCorrecciones = items.map(([clave, valor]) => Number(valor));

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
                                series: [{ name: 'Cantidad', data: importesCorrecciones }],
                                plotOptions: {
                                    bar: {
                                        horizontal: true, // Barras de izquierda a derecha 👈 👉
                                        barHeight: '60%', // Grosor elegante para las 5 barras
                                        distributed: true, // ¡Opcional! Hace que cada una de las 5 barras tenga un tono distinto si quieres
                                        {{-- dataLabels: { position: 'right' } --}}
                                    }
                                },

                                // EL TRUCO: Las categorías se quedan en XAXIS aunque se grafiquen en vertical
                                xaxis: {
                                    type: 'category',
                                    categories: nombresOperadores,
                                    min: 0,
                                    forceNiceScale: true,
                                    labels: {
                                        style: { fontSize: '11px' },
                                        formatter: function(val) {
                                            // Evitamos que intente ponerle signo de pesos a los nombres de los productos
                                            return val;
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
                                    style: {
                                        fontSize: '16px',
                                        colors: ['#C29A6B']
                                    },
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
                                xaxis: {
                                    categories: nombresOperadores,
                                    labels: {
                                        style: { fontSize: '11px' },
                                        formatter: function(val) {
                                            return val;
                                        }
                                    }
                                },
                                // 2. Inyectamos los nuevos datos en la misma llamada para evitar conflictos de hilos
                                series: [{ name: 'Cantidad', data: importesCorrecciones }]
                            }, false, true); // false = no recrear el gráfico desde cero, true = animar el cambio
                        }
                    } else {
                        // Resultado vacío/null: limpiamos la serie y las categorías en vez de dejar las anteriores.
                        this.sinDatos = true;

                        if (this.chart) {
                            this.chart.updateOptions({
                                xaxis: { categories: [] },
                                series: [{ name: 'Cantidad', data: [] }]
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
                    Correcciones por operador
                </span>
                <template x-if="!chart && !sinDatos">
                    <div class="text-center py-3 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        Cargando datos...
                    </div>
                </template>
                <template x-if="sinDatos">
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-tools fs-3 d-block mb-1"></i>
                        Sin correcciones registradas para los filtros seleccionados
                    </div>
                </template>
                <div id="contenedor-grafica-top-correcciones-operador" wire:ignore x-show="!sinDatos">
                    <div id="mi-canvas-grafica-top-correcciones-operador"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 mb-3">
        <div x-data="{
            datosCorreccionesHora: @entangle('correccionesData.grafica_correcciones_hora'),
            chart: null,
            sinDatos: false,
            horasDelDia: [],

            init() {
                this.horasDelDia = Array.from({ length: 24 }, (_, i) => {
                    return i.toString().padStart(2, '0') + ':00'; // Genera ['00:00', '01:00', ..., '23:00']
                });

                // Escuchamos los cambios en los datos que vienen de Livewire
                this.$watch('datosCorreccionesHora', value => {
                    let el = document.getElementById('mi-canvas-grafica-correcciones-hora');
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
                                series: [{ name: 'Correcciones por hora', data: serie24Horas }],
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
                <span class="fs-5 fw-bold">Correcciones por hora</span>
                <template x-if="!chart && !sinDatos">
                    <div class="text-center py-3 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        Cargando datos...
                    </div>
                </template>
                <template x-if="sinDatos">
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-tools fs-3 d-block mb-1"></i>
                        Sin correcciones registradas para los filtros seleccionados
                    </div>
                </template>
                <div id="contenedor-grafica-correcciones-hora" wire:ignore x-show="!sinDatos">
                    <div id="mi-canvas-grafica-correcciones-hora"></div>
                </div>
            </div>
        </div>
    </div>
</div>
