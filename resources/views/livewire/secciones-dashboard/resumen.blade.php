<div class="mb-3 grid-cols-5 px-1">
    <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <p class="fs-5 fw-bold">OPERACIONES</p>
            <p class="fs-3 text-primary m-auto">{{ max($resumenData['operaciones'], 0) }}</p>
        </div>
    </div>
    <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <span class="fs-5 fw-bold">VENTA NETA</span>
            <span class="fs-3  text-primary">${{ number_format(max($resumenData['ventas_netas'], 0), 2) }}</span>
        </div>
    </div>
    <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <span class="fs-5 fw-bold">VENTA TOTAL</span>
            <span class="fs-3 text-primary">${{ number_format(max($resumenData['ventas_totales'], 0), 2) }}</span>
        </div>
    </div>
    <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <p class="fs-5 fw-bold">MULTIMONEDA</p>
            <p class="fs-3 text-primary m-auto">{{ max($resumenData['multimoneda'], 0) }}</p>
        </div>
    </div>
    <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <p class="fs-5 fw-bold">ARTICULOS VENDIDOS</p>
            <p class="fs-3 text-primary m-auto">{{ max($resumenData['articulos_vendidos'], 0) }}</p>
        </div>
    </div>
</div>
<div class="grid-cols-3 px-1 mb-3">
    <div class="card border-0 border-start border-danger bg-dark-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <p class="fs-5 fw-bold">IMPORTE DEVUELTO</p>
            <p class="fs-3 text-danger">${{ number_format(max($resumenData['importes_devueltos'], 0), 2) }}</p>
        </div>
    </div>
    <div class="card border-0 border-start border-danger bg-dark-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <p class="fs-5 fw-bold">DELETES</p>
            <p class="fs-3 text-danger m-auto">{{ max($resumenData['deletes'], 0) }}</p>
        </div>
    </div>
    <div class="card border-0 border-start border-danger bg-dark-subtle shadow-sm border-4 text-center bg-gray">
        <div class="card-body align-items-center d-flex flex-column">
            <p class="fs-5 fw-bold">CANCELS</p>
            <p class="fs-3 text-danger m-auto">{{ max($resumenData['cancels'], 0) }}</p>
        </div>
    </div>
    {{-- <div
                    class="card col-6 col-md-2 border-0 border-start border-danger bg-dark-subtle shadow-sm border-4 text-center bg-gray">
                    <div class="card-body align-items-center d-flex flex-column">
                        <p class="fs-5 fw-bold">CUARENTENA</p>
                        <p class="fs-3 text-danger m-auto">{{ max($resumenData['cuarentena'], 0) }}</p>
                    </div>
                </div> --}}
</div>
<div class="row">
    <div class="col-12 col-md-4 mb-3">
        <div x-data="{
            datosServidor: @entangle('resumenData.ventas_netas_operacion'),
            chart: null,
            sinDatos: false,
        
            init() {
                // Escuchamos los cambios en los datos que vienen de Livewire
                this.$watch('datosServidor', value => {
                    let el = document.getElementById('mi-canvas-grafica-venta-neta');
                    if (!el) return;
        
                    const hayDatos = value && Object.keys(value).length > 0;
        
                    if (hayDatos) {
                        this.sinDatos = false;
        
                        let datosFormateados = Object.values(value).map((num, index) => {
                            return { x: (index + 1).toString(), y: num };
                        });
        
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
                                            download: true,
                                            selection: false,
                                            zoom: false,
                                            zoomin: false,
                                            zoomout: false,
                                            pan: false,
                                            reset: false
                                        }
                                    },
                                },
                                series: [{ name: 'Métrica', data: datosFormateados }],
                                xaxis: {
                                    type: 'category',
                                },
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
                            // 2. Si ya existe, solo actualizamos los datos
                            this.chart.updateSeries([{ data: datosFormateados }]);
                        }
                    } else {
                        // 3. Resultado vacío/null: limpiamos la serie en vez de dejar la anterior
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
                    Venta neta por operación
                </span>
                <template x-if="!chart && !sinDatos">
                    <div class="text-center py-3 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        Cargando datos...
                    </div>
                </template>
                <template x-if="sinDatos">
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-bar-chart-line fs-3 d-block mb-1"></i>
                        Sin datos para los filtros seleccionados
                    </div>
                </template>
                <div id="contenedor-grafica-venta-neta" wire:ignore x-show="!sinDatos">
                    <div id="mi-canvas-grafica-venta-neta"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <div x-data="{
            datosActividad: @entangle('resumenData.grafica_actividad'),
            chart: null,
            sinDatos: false,
            horasDelDia: [],
        
            init() {
                this.horasDelDia = Array.from({ length: 24 }, (_, i) => {
                    return i.toString().padStart(2, '0') + ':00'; // Genera ['00:00', '01:00', ..., '23:00']
                });
        
                // Escuchamos los cambios en los datos que vienen de Livewire
                this.$watch('datosActividad', value => {
                    let el = document.getElementById('mi-canvas-grafica-actividad');
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
                                series: [{ name: 'Operaciones', data: serie24Horas }],
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
                <span class="fs-5 fw-bold">Actividad</span>
                <template x-if="!chart && !sinDatos">
                    <div class="text-center py-3 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        Cargando datos...
                    </div>
                </template>
                <template x-if="sinDatos">
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-graph-down fs-3 d-block mb-1"></i>
                        Sin actividad registrada para los filtros seleccionados
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
                    <span class="fs-5 fw-bold">Ultima operación recibida</span>
                    <p class="fs-5 text-center">Ticket: {{ $resumenData['ultimo_ticket']['id_transaccion'] }}
                    </p>
                    <p class="fs-5 text-center">Fecha:
                        {{ Illuminate\Support\Carbon::parse($resumenData['ultimo_ticket']['fecha_transaccion'])->format('d/m/Y') }}
                        - Hora:
                        {{ Illuminate\Support\Carbon::parse($resumenData['ultimo_ticket']['fecha_transaccion'])->format('H:i:s') }}
                    </p>
                    <p class="fs-5 text-center">DLPos: {{ $resumenData['ultimo_ticket']['id_pos'] }}
                        - Cajero:
                        {{ $resumenData['ultimo_ticket']['empleado'] ? Illuminate\Support\Facades\Crypt::decrypt($resumenData['ultimo_ticket']['empleado']) : '' }}
                    </p>
                    <p class="fs-5 text-center">
                        Estado: <span class="badge bg-success-subtle text-success">OK</span>
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
