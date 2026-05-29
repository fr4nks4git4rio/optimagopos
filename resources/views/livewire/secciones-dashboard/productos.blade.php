<div class="row justify-content-start px-3 gap-4 mb-3">
    <div
        class="card col-6 col-md-2 border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <p class="fs-5 fw-bold">ARTICULOS VENDIDOS</p>
            <p class="fs-3 text-primary m-auto">{{ max($productosData['articulos_vendidos'], 0) }}</p>
        </div>
    </div>
    <div
        class="card col-6 col-md-2 border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <p class="fs-5 fw-bold">PRODUCTO ESTRELLA</p>
            <p class="fs-3 text-primary m-auto">{{ $productosData['producto_estrella'] }}</p>
        </div>
    </div>
    <div
        class="card col-6 col-md-2 border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <p class="fs-5 fw-bold">MAYOR INGRESO</p>
            <p class="fs-3 text-primary m-auto">{{ $productosData['mayor_ingreso'] }}</p>
        </div>
    </div>
    <div
        class="card col-6 col-md-2 border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <p class="fs-5 fw-bold">MAS VENDIDO</p>
            <p class="fs-3 text-primary m-auto">{{ $productosData['mas_vendido'] }}</p>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12 col-md-6 mb-3">
        <div x-data="{
            datosProductosCantidad: @entangle('productosData.top_productos_cantidad'),
            chart: null,

            init() {
                // Escuchamos los cambios en los datos que vienen de Livewire
                this.$watch('datosProductosCantidad', value => {
                    if (value && Object.keys(value).length > 0) {

                        // 1. Convertimos el objeto de Livewire en un array de pares [clave, valor]
                        let items = Object.entries(value);

                        // 2. Extraemos las etiquetas (nombres/folios de los tickets)
                        let nombresProductos = items.map(([clave, valor]) => clave);

                        // 3. Extraemos los importes (los números)
                        let importesProductos = items.map(([clave, valor]) => Number(valor));

                        // Buscamos el elemento de forma nativa e inequívoca
                        let el = document.getElementById('mi-canvas-grafica-top-productos-cantidad');

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
                                    series: [{ name: 'Cantidad', data: importesProductos }],
                                    plotOptions: {
                                        bar: {
                                            horizontal: true, // Barras de izquierda a derecha 👈 👉
                                            barHeight: '60%', // Grosor elegante para las 5 barras
                                            distributed: true, // ¡Opcional! Hace que cada una de las 5 barras tenga un tono distinto si quieres
                                            dataLabels: { position: 'right' }
                                        }
                                    },

                                    // EL TRUCO: Las categorías se quedan en XAXIS aunque se grafiquen en vertical
                                    xaxis: {
                                        type: 'category',
                                        categories: nombresProductos,
                                        min: 0,
                                        forceNiceScale: true,
                                        labels: {
                                            style: { fontSize: '11px' },
                                            formatter: function(val) {
                                                // Evitamos que intente ponerle signo de pesos a los nombres de los productos
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
                                        style: {
                                            fontSize: '11px',
                                            colors: ['#2D3142']
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
                                    yaxis: {
                                        categories: nombresProductos
                                    },
                                    // 2. Inyectamos los nuevos datos en la misma llamada para evitar conflictos de hilos
                                    series: [{ name: 'Cantidad', data: importesProductos }]
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
                    Top productos por cantidad
                </span>
                <template x-if="!chart">
                    <div class="text-center py-3 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        Cargando datos...
                    </div>
                </template>
                <div id="contenedor-grafica-top-productos-cantidad" wire:ignore>
                    <div id="mi-canvas-grafica-top-productos-cantidad"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 mb-3">
        <div x-data="{
            datosProductosImporte: @entangle('productosData.top_productos_ingreso'),
            chart: null,

            init() {
                // Escuchamos los cambios en los datos que vienen de Livewire
                this.$watch('datosProductosImporte', value => {
                    if (value && Object.keys(value).length > 0) {

                        // 1. Convertimos el objeto de Livewire en un array de pares [clave, valor]
                        let items = Object.entries(value);

                        // 2. Extraemos las etiquetas (nombres/folios de los tickets)
                        let nombresProductos = items.map(([clave, valor]) => clave);

                        // 3. Extraemos los importes (los números)
                        let importesProductos = items.map(([clave, valor]) => Number(valor));

                        // Buscamos el elemento de forma nativa e inequívoca
                        let el = document.getElementById('mi-canvas-grafica-top-productos-importe');

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
                                    series: [{ name: 'Importe', data: importesProductos }],
                                    plotOptions: {
                                        bar: {
                                            horizontal: true, // Barras de izquierda a derecha 👈 👉
                                            barHeight: '60%', // Grosor elegante para las 5 barras
                                            distributed: true, // ¡Opcional! Hace que cada una de las 5 barras tenga un tono distinto si quieres
                                            dataLabels: { position: 'right' }
                                        }
                                    },

                                    // EL TRUCO: Las categorías se quedan en XAXIS aunque se grafiquen en vertical
                                    xaxis: {
                                        type: 'category',
                                        categories: nombresProductos,
                                        min: 0,
                                        forceNiceScale: true,
                                        labels: {
                                            style: { fontSize: '11px' },
                                            formatter: function(val) {
                                                // Evitamos que intente ponerle signo de pesos a los nombres de los productos
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
                                        style: {
                                            fontSize: '11px',
                                            colors: ['#2D3142']
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
                                    yaxis: {
                                        categories: nombresProductos
                                    },
                                    // 2. Inyectamos los nuevos datos en la misma llamada para evitar conflictos de hilos
                                    series: [{ name: 'Importe', data: importesProductos }]
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
                    Top productos por importe
                </span>
                <template x-if="!chart">
                    <div class="text-center py-3 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        Cargando datos...
                    </div>
                </template>
                <div id="contenedor-grafica-top-productos-importe" wire:ignore>
                    <div id="mi-canvas-grafica-top-productos-importe"></div>
                </div>
            </div>
        </div>
    </div>
</div>
