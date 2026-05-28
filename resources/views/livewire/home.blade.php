@section('title', 'Dashboard')
<div wire:poll.3000ms="loadData" wire:init="init" class="row">
    <div wire:loading.delay.longer>
        <div class="loading">
            <img src="{{ asset('img/loading.gif') }}" />
        </div>
    </div>

    <h1 class="fs-1 mb-2">@yield('title')</h1>
    <div class="col-12">
        <div class="row justify-content-start gap-3 px-3 mb-3">
            <button wire:click="loadData('resumen')"
                class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'resumen') active @endif">
                Resumen
            </button>
            <button wire:click="loadData('operaciones')"
                class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'operaciones') active @endif">
                Operaciones
            </button>
            <button wire:click="loadData('productos')"
                class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'productos') active @endif">
                Productos
            </button>
            <button wire:click="loadData('pagos')"
                class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'pagos') active @endif">
                Pagos
            </button>
            <button wire:click="loadData('correcciones')"
                class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'correcciones') active @endif">
                Correcciones
            </button>
            <button wire:click="loadData('cuarentena')"
                class="btn btn-outline-site-primary bt-lg w-auto @if ($seccion == 'cuarentena') active @endif">
                Cuarentena
            </button>
        </div>
        @if ($seccion == 'resumen')
            <div class="row justify-content-start px-3 gap-4 mb-3">
                <div
                    class="card col-6 col-md-2 border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
                    <div class="card-body">
                        <p class="fs-6">OPERACIONES</p>
                        <p class="fs-1 text-primary">{{ max($resumenData['operaciones'], 0) }}</p>
                    </div>
                </div>
                @foreach ($resumenData['ventas_netas'] as $venta_neta)
                    <div
                        class="card col-6 col-md-2 border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray py-2">
                        <span class="fs-6">VENTA NETA {{ $venta_neta['moneda'] }}</span>
                        <span class="fs-1  text-primary">${{ number_format(max($venta_neta['monto'], 0), 2) }}</span>
                    </div>
                @endforeach
                @foreach ($resumenData['ventas_totales'] as $venta_total)
                    <div
                        class="card col-6 col-md-2 border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
                        <div class="card-body">
                            <p class="fs-6">VENTA TOTAL {{ $venta_total['moneda'] }}</p>
                            <p class="fs-1  text-primary">${{ number_format(max($venta_total['monto'], 0), 2) }}</p>
                        </div>
                    </div>
                @endforeach
                <div
                    class="card col-6 col-md-2 border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
                    <div class="card-body">
                        <p class="fs-6">MULTIMONEDA</p>
                        <p class="fs-1 text-primary">{{ max($resumenData['multimoneda'], 0) }}</p>
                    </div>
                </div>
                <div
                    class="card col-6 col-md-2 border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center bg-gray">
                    <div class="card-body">
                        <p class="fs-6">ARTICULOS VENDIDOS</p>
                        <p class="fs-1 text-primary">{{ max($resumenData['articulos_vendidos'], 0) }}</p>
                    </div>
                </div>
                @foreach ($resumenData['importes_devueltos'] as $importe_devuelto)
                    <div
                        class="card col-6 col-md-2 border-0 border-start border-danger bg-danger-subtle shadow-sm border-4 text-center bg-gray">
                        <div class="card-body">
                            <p class="fs-6">IMPORTE DEVUELTO {{ $importe_devuelto['moneda'] }}</p>
                            <p class="fs-1 text-danger">${{ number_format(max($importe_devuelto['monto'], 0), 2) }}</p>
                        </div>
                    </div>
                @endforeach
                <div
                    class="card col-6 col-md-2 border-0 border-start border-danger bg-danger-subtle shadow-sm border-4 text-center bg-gray">
                    <div class="card-body">
                        <p class="fs-6">DELETES</p>
                        <p class="fs-1 text-danger">{{ max($resumenData['deletes'], 0) }}</p>
                    </div>
                </div>
                <div
                    class="card col-6 col-md-2 border-0 border-start border-danger bg-danger-subtle shadow-sm border-4 text-center bg-gray">
                    <div class="card-body">
                        <p class="fs-6">CANCELS</p>
                        <p class="fs-1 text-danger">{{ max($resumenData['cancels'], 0) }}</p>
                    </div>
                </div>
                <div
                    class="card col-6 col-md-2 border-0 border-start border-danger bg-danger-subtle shadow-sm border-4 text-center bg-gray">
                    <div class="card-body">
                        <p class="fs-6">CUARENTENA</p>
                        <p class="fs-1 text-danger">{{ max($resumenData['cuarentena'], 0) }}</p>
                    </div>
                </div>
            </div>
            <div class="row">
                @foreach ($resumenData['ventas_netas_operacion'] as $index => $ventas_neta_moneda)
                    <div class="col-12 col-md-4 mb-3" wire:key="grafica-{{ $index }}">
                        <div x-data="{
                            datosServidor: @entangle('resumenData.ventas_netas_operacion.' . $index . '.montos'),
                            chart: null,

                            init() {
                                // Escuchamos los cambios en los datos que vienen de Livewire
                                this.$watch('datosServidor', value => {
                                    if (value && value.length > 0) {

                                        let datosFormateados = value.map((num, index) => {
                                            return { x: (index + 1).toString(), y: num };
                                        });

                                        // Buscamos el elemento de forma nativa e inequívoca
                                        let el = document.getElementById('mi-canvas-grafica-{{ $index }}');

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
                                                        }
                                                    },
                                                    series: [{ name: 'Métrica', data: datosFormateados }],
                                                    xaxis: {
                                                        type: 'category',
                                                    },
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
                        }" class="card shadow-sm bg-info-subtle">
                            <div class="card-body">
                                <h5 class="card-title">Venta neta {{ $ventas_neta_moneda['moneda'] }} por operación
                                </h5>

                                <div id="contenedor-grafica-{{ $index }}" wire:ignore>
                                    <div id="mi-canvas-grafica-{{ $index }}"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="col-12 col-md-4 mb-3">
                    <div x-data="{
                        datosActividad: @entangle('resumenData.grafica_actividad'),
                        chart: null,

                        init() {
                            const horasDelDia = Array.from({ length: 24 }, (_, i) => {
                                return i.toString().padStart(2, '0') + ':00'; // Genera ['00:00', '01:00', ..., '23:00']
                            });
                            // Escuchamos los cambios en los datos que vienen de Livewire
                            this.$watch('datosActividad', value => {

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
                                    let el = document.getElementById('mi-canvas-grafica-actividad');

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
                                                    }
                                                },
                                                series: [{ name: 'Operaciones', data: serie24Horas }],
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
                    }" class="card shadow-sm bg-info-subtle">
                        <div class="card-body">
                            <h5 class="card-title">Actividad</h5>

                            <div id="contenedor-grafica-actividad" wire:ignore>
                                <div id="mi-canvas-grafica-actividad"></div>
                            </div>
                        </div>
                    </div>
                </div>
                @if ($resumenData['ultimo_ticket'])
                    <div class="col-12 col-md-4 mb-3">
                        <div class="card shadow-sm bg-info-subtle">
                            <div class="card-body">
                                <h5 class="card-title">Ultima operación recibida</h5>
                                <p class="fs-4 text-center">Ticket: {{ $resumenData['ultimo_ticket']->id_transaccion }}
                                </p>
                                <p class="fs-4 text-center">Fecha:
                                    {{ Illuminate\Support\Carbon::parse($resumenData['ultimo_ticket']->fecha_transaccion)->format('d/m/Y') }}
                                    - Hora:
                                    {{ Illuminate\Support\Carbon::parse($resumenData['ultimo_ticket']->fecha_transaccion)->format('H:i:s') }}
                                </p>
                                <p class="fs-4 text-center">DLPos: {{ $resumenData['ultimo_ticket']->id_pos }}
                                    - Cajero:
                                    {{ $resumenData['ultimo_ticket']->empleado ? Illuminate\Support\Facades\Crypt::decrypt($resumenData['ultimo_ticket']->empleado) : '' }}
                                </p>
                                <p class="fs-4 text-center">
                                    Estado: <span class="badge bg-success-subtle text-success">OK</span>
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
