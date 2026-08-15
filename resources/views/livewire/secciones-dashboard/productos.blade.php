<div class="row g-3 mb-3 px-1">
    <div class="col-12 col-sm-6 col-lg">
        <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center h-100">
            <div class="card-body align-items-center d-flex flex-column">
                <p class="fs-5 fw-bold text-uppercase">{{ __('site.dashboard.items_sold') }}</p>
                <p class="fs-3 text-primary m-auto">{{ $productosData['articulos_vendidos'] ?? 0 }}</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg">
        <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center h-100">
            <div class="card-body align-items-center d-flex flex-column">
                <p class="fs-5 fw-bold text-uppercase">{{ __('site.dashboard.flagship_product') }}</p>
                @if (!$productosData['producto_estrella'])
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-box-seam fs-3 d-block mb-1"></i>
                        {{ __('site.dashboard.no_data') }}
                    </div>
                @else
                    <p class="fs-3 text-primary m-auto">{{ $productosData['producto_estrella'] }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg">
        <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center h-100">
            <div class="card-body align-items-center d-flex flex-column">
                <p class="fs-5 fw-bold text-uppercase">{{ __('site.dashboard.most_popular') }}</p>
                @if (!$productosData['mas_popular'])
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-box-seam fs-3 d-block mb-1"></i>
                        {{ __('site.dashboard.no_data') }}
                    </div>
                @else
                    <p class="fs-3 text-primary m-auto">{{ $productosData['mas_popular'] }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg">
        <div class="card border-0 border-start border-primary bg-primary-subtle shadow-sm border-4 text-center h-100">
            <div class="card-body align-items-center d-flex flex-column">
                <p class="fs-5 fw-bold text-uppercase">{{ __('site.dashboard.higher_income') }}</p>
                @if (!$productosData['mayor_ingreso'])
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-box-seam fs-3 d-block mb-1"></i>
                        {{ __('site.dashboard.no_data') }}
                    </div>
                @else
                    <p class="fs-3 text-primary m-auto">{{ $productosData['mayor_ingreso'] ?: 'N/A' }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12 col-lg-6 mb-3">
        <div x-data="{
            datosProductosCantidad: @entangle('productosData.top_productos_cantidad').live,
            chart: null,
            sinDatos: false,
        
            init() {
                this.$watch('datosProductosCantidad', value => {
                    const hayDatos = value && Object.keys(value).length > 0;
        
                    if (hayDatos) {
                        this.sinDatos = false;
        
                        let items = Object.entries(value);
                        let nombresProductos = items.map(([clave, valor]) => clave);
                        let importesProductos = items.map(([clave, valor]) => Number(valor));
        
                        this.$nextTick(() => {
                            let el = document.getElementById('mi-canvas-grafica-top-productos-cantidad');
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
                                        events: {
                                            mounted: function(chartContext) {
                                                setTimeout(() => {
                                                    chartContext.windowResizeHandler();
                                                }, 50);
                                            }
                                        }
                                    },
                                    series: [{ name: '{{ __('site.dashboard.quantity') }}', data: importesProductos }],
                                    plotOptions: {
                                        bar: {
                                            horizontal: true,
                                            barHeight: '60%',
                                            distributed: true
                                        }
                                    },
                                    xaxis: {
                                        type: 'category',
                                        categories: nombresProductos,
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
                                        categories: nombresProductos,
                                        labels: {
                                            style: { fontSize: '11px' },
                                            formatter: function(val) {
                                                return val;
                                            }
                                        }
                                    },
                                    series: [{ name: 'Cantidad', data: importesProductos }]
                                }, false, true);
        
                                this.$nextTick(() => this.chart.windowResizeHandler());
                            }
                        });
                    } else {
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
                    {{ __('site.dashboard.top_products_by_quantity') }}
                </span>
                <template x-if="!chart && !sinDatos">
                    <div class="text-center py-3 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        {{ __('site.dashboard.loading_data') }}...
                    </div>
                </template>
                <template x-if="sinDatos">
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-box-seam fs-3 d-block mb-1"></i>
                        {{ __('site.dashboard.no_data') }}
                    </div>
                </template>
                <div id="contenedor-grafica-top-productos-cantidad" wire:ignore x-show="!sinDatos">
                    <div id="mi-canvas-grafica-top-productos-cantidad"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 mb-3">
        <div x-data="{
            datosProductosImporte: @entangle('productosData.top_productos_ingreso').live,
            chart: null,
            sinDatos: false,
        
            init() {
                this.$watch('datosProductosImporte', value => {
                    const hayDatos = value && Object.keys(value).length > 0;
        
                    if (hayDatos) {
                        this.sinDatos = false;
        
                        let items = Object.entries(value);
                        let nombresProductos = items.map(([clave, valor]) => clave);
                        let importesProductos = items.map(([clave, valor]) => Number(valor));
        
                        this.$nextTick(() => {
                            let el = document.getElementById('mi-canvas-grafica-top-productos-importe');
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
                                        events: {
                                            mounted: function(chartContext) {
                                                setTimeout(() => {
                                                    chartContext.windowResizeHandler();
                                                }, 50);
                                            }
                                        }
                                    },
                                    series: [{ name: '{{ __('site.dashboard.import') }}', data: importesProductos }],
                                    plotOptions: {
                                        bar: {
                                            horizontal: true,
                                            barHeight: '60%',
                                            distributed: true
                                        }
                                    },
                                    xaxis: {
                                        type: 'category',
                                        categories: nombresProductos,
                                        min: 0,
                                        forceNiceScale: true,
                                        labels: {
                                            style: { fontSize: '11px' },
                                            formatter: function(val) {
                                                return '$' + val;
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
                                        formatter: function(val) {
                                            return '$' + val;
                                        }
                                    },
                                    legend: { show: false }
                                };
        
                                this.chart = new ApexCharts(el, options);
                                this.chart.render();
                            } else {
                                this.chart.updateOptions({
                                    xaxis: {
                                        categories: nombresProductos,
                                        labels: {
                                            style: { fontSize: '11px' },
                                            formatter: function(val) {
                                                return '$' + val;
                                            }
                                        }
                                    },
                                    series: [{ name: 'Importe', data: importesProductos }]
                                }, false, true);
        
                                this.$nextTick(() => this.chart.windowResizeHandler());
                            }
                        });
                    } else {
                        this.sinDatos = true;
        
                        if (this.chart) {
                            this.chart.updateOptions({
                                xaxis: { categories: [] },
                                series: [{ name: 'Importe', data: [] }]
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
                    {{ __('site.dashboard.top_products_by_import') }}
                </span>
                <template x-if="!chart && !sinDatos">
                    <div class="text-center py-3 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        {{ __('site.dashboard.loading_data') }}...
                    </div>
                </template>
                <template x-if="sinDatos">
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-box-seam fs-3 d-block mb-1"></i>
                        {{ __('site.dashboard.no_data') }}
                    </div>
                </template>
                <div id="contenedor-grafica-top-productos-importe" wire:ignore x-show="!sinDatos">
                    <div id="mi-canvas-grafica-top-productos-importe"></div>
                </div>
            </div>
        </div>
    </div>
</div>
