@section('title', __('site.accounts_receivable.index.title'))

<div x-data="{
    page: 1,
    perPage: 10,
    pagination: {
        current_page: 1,
        last_page: 1,
        total: 0
    },
    facturas: [],
    loading: false,
    sort: 'F. Int.',
    fecha_inicio: '',
    fecha_fin: '',
    tipo: '',
    cliente: '',
    folio_interno: '',
    moneda: '',
    importe: '',
    init() {
        $el.addEventListener('cambio-cliente', e => this.cliente = e.detail);

        this.$watch('perPage', (nuevo, anterior) => {
            this.page = 1;
            this.loadCuentasCobrar();
        });
        this.$watch('sort', (nuevo, anterior) => {
            this.page = 1;
            this.loadCuentasCobrar();
        });
        this.$watch('fecha_inicio', (nuevo, anterior) => {
            this.page = 1;
            this.loadCuentasCobrar();
        });
        this.$watch('fecha_fin', (nuevo, anterior) => {
            this.page = 1;
            this.loadCuentasCobrar();
        });
        this.$watch('tipo', (nuevo, anterior) => {
            this.page = 1;
            this.loadCuentasCobrar();
        });
        this.$watch('cliente', (nuevo, anterior) => {
            this.page = 1;
            this.loadCuentasCobrar();
        });
        this.$watch('folio_interno', (nuevo, anterior) => {
            this.page = 1;
            this.loadCuentasCobrar();
        });
        this.$watch('moneda', (nuevo, anterior) => {
            this.page = 1;
            this.loadCuentasCobrar();
        });
        this.$watch('importe', (nuevo, anterior) => {
            this.page = 1;
            this.loadCuentasCobrar();
        });
        this.loadCuentasCobrar();
    },
    getParams() {
        return `page=${this.page}&perPage=${this.perPage}&fecha_inicio=${this.fecha_inicio}&fecha_fin=${this.fecha_fin}&tipo=${this.tipo}&cliente=${this.cliente}&folio_interno=${this.folio_interno}&moneda=${this.moneda}&importe=${this.importe}&sort=${this.sort}`;
    },
    loadCuentasCobrar() {
        this.loading = true;
        let params = this.getParams();
        let el = this;
        for (let i = 0; i < document.getElementsByClassName('checkbox toggler').length; i++) {
            element = document.getElementsByClassName('checkbox toggler')[i];
            element.checked = false;
        }
        $.get('/admin/load-cuentas-cobrar?' + params, function(data) {
            el.loading = false;
            if (data.success) {
                el.facturas = Object.values(data.data.data);
                console.log(data.data);
                el.pagination = {
                    from: data.data.from,
                    to: data.data.to,
                    current_page: data.data.current_page,
                    last_page: data.data.last_page,
                    total: data.data.total
                };
            }
        }, 'json');
    },
    changePage(p) {
        this.page = p;
        this.loadCuentasCobrar();
    },
    printListado() {
        let params = this.getParams();
        let el = this;
        $.get('/admin/print-listado-cuentas-cobrar?' + params, function(data) {
            if (data.success) {
                $wire.iframeContainerClass = 'show';
                $wire.iframeSrc = data.report;
            }
        }, 'json');
    },
    facturasSeleccionadas() {
        return this.facturas.filter(function(element) { return element.seleccionado == true; });
    },
    facturasMxn(seleccionadas = false) {
        if (seleccionadas)
            return this.facturasSeleccionadas().filter(function(element) { return element.moneda == 'MXN'; });
        return this.facturas.filter(function(element) { return element.moneda == 'MXN'; });
    },
    facturasUsd(seleccionadas = false) {
        if (seleccionadas)
            return this.facturasSeleccionadas().filter(function(element) { return element.moneda == 'USD'; });
        return this.facturas.filter(function(element) { return element.moneda == 'USD'; });
    },
    totalMxn(seleccionadas = false) {
        return this.facturasMxn(seleccionadas).reduce(function(acumulador, element) { return acumulador + parseFloat(element.total); }, 0);
    },
    totalUsd(seleccionadas = false) {
        return this.facturasUsd(seleccionadas).reduce(function(acumulador, element) { return acumulador + parseFloat(element.total); }, 0);
    },
    pendienteMxn(seleccionadas = false) {
        return this.facturasMxn(seleccionadas).reduce(function(acumulador, element) { return acumulador + parseFloat(element.pendiente_ingresar); }, 0);
    },
    pendienteUsd(seleccionadas = false) {
        return this.facturasUsd(seleccionadas).reduce(function(acumulador, element) { return acumulador + parseFloat(element.pendiente_ingresar); }, 0);
    },
    checkClienteFacturasSeleccionadas() {
        let facturas = this.facturasSeleccionadas();
        if (facturas.length <= 1)
            return true;
        return facturas.every(function(element) { return element.cliente_id == facturas[0].cliente_id });
    },
    ingresarFacturas() {
        if (!this.checkClienteFacturasSeleccionadas())
            $wire.emit('show-modal-alert', 'Las facturas seleccionadas deben pertenecer a un único cliente.', 'warning');
        else
            $wire.emit('openModal', 'facturacion.cuentas-cobrar.ingresar', { facturas_ids: this.facturasSeleccionadas().reduce(function(acumulador, element) { return acumulador + element.id + ','; }, '') });
    },
    toggleSeleccion(id) {
        // Copia el array para forzar cambio de referencia
        let index = this.facturas.findIndex(function(element) { return element.id == id });
        if (this.facturasSeleccionadas().length > 0 && this.facturasSeleccionadas()[0]['cliente_id'] != this.facturas[index]['cliente_id']) {
            document.getElementById('factura_' + id + '_seleccionado').checked = false;
            $wire.emit('show-toast', 'Las facturas seleccionadas deben pertenecer al mismo Cliente.', 'danger');
            return;
        }
        let updatedFacturas = [...this.facturas];
        updatedFacturas[index].seleccionado = !updatedFacturas[index].seleccionado;
        this.facturas = updatedFacturas; // Reasignación para que Alpine reactive
    },
    parseFloat(text) {
        return parseFloat(text);
    }
}">
    <template x-if="loading">
        <div>
            <div class="loading">
                <img src="{{ asset('img/loading.gif') }}" />
            </div>
        </div>
    </template>
    <h1 class="fw-bold h2">@yield('title')</h1>

    <div class="row justify-content-between">
        <div class="col-sm-12 mb-1">
            <div class="row">
                <div class="col-sm-2">
                    <div class="mb-1">
                        <label for="">{{ __('site.accounts_receivable.index.start_date') }}:</label>
                        <input x-model="fecha_inicio" type="date" class="form-control form-control">
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="mb-1">
                        <label for="">{{ __('site.accounts_receivable.index.end_date') }}:</label>
                        <input x-model="fecha_fin" type="date" class="form-control form-control">
                    </div>
                </div>
                <div class="col-sm-2">
                    <label for="">{{ __('site.accounts_receivable.index.type') }}:</label>
                    <select class="form-control form-control" x-model="tipo">
                        <option value="-1">{{ __('site.common.all') }}</option>
                        @foreach ($tipos as $index => $tipo)
                            <option value="{{ $index }}">{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-4">
                    <x-select2-ajax label="{{ __('site.accounts_receivable.index.client') }}" placeholder="Seleccione..." class="form-control" :is_alpine="true"
                        model="cliente" url="{{ route('clientes.load-clientes') }}" />
                </div>
                <div class="col-sm-2">
                    <div class="mb-1">
                        <label for="">{{ __('site.accounts_receivable.index.internal_folio') }}:</label>
                        <input x-model="folio_interno" class="form-control form-control" type="text">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-2">
                    <label for="">{{ __('site.accounts_receivable.index.currency') }}:</label>
                    <select class="form-control form-control" x-model="moneda">
                        <option value="-1">{{ __('site.common.all') }}</option>
                        @foreach ($monedas as $moneda)
                            <option value="{{ $moneda }}">{{ $moneda }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3">
                    <div class="mb-1">
                        <label for="">{{ __('site.accounts_receivable.index.total') }}</label>
                        <input x-model="importe" class="form-control form-control" type="number">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12 mb-3 d-flex justify-content-between">
            <button type="button" class="btn btn-site-primary mr-1" @click="ingresarFacturas()"
                x-bind:disabled="facturasSeleccionadas().length < 2">
                {{__('site.accounts_receivable.index.enter_invoices')}}
            </button>
            <div>
                <button type="button" class="btn btn-site-primary mr-1" @click="printListado()">
                    {{ __('site.common.print') }}
                </button>
                <x-dropdown icon="eye">
                    <x-slot name="slot_label">
                        <span x-text="perPage"></span>
                    </x-slot>

                    <x-dropdown-item label="10" @click="perPage = 10" />
                    <x-dropdown-item label="25" @click="perPage = 25" />
                    <x-dropdown-item label="50" @click="perPage = 50" />
                    <x-dropdown-item label="100" @click="perPage = 100" />
                </x-dropdown>

            </div>
        </div>
    </div>

    <div class="list-group mb-3 table-responsive">

        <table class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th class="text-center">Sel.</th>
                    <th class="text-center">F. Int.</th>
                    <th class="text-center">Fecha Factura</th>
                    <th class="text-center">Receptor</th>
                    <th class="text-center">Tipo</th>
                    <th class="text-center">Moneda</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Pendiente</th>
                    <th class="text-center" style="min-width: 150px">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(factura, index) in facturas">
                    <tr>
                        <td>
                            <div class="form-check form-switch align-content-center">
                                <input class="form-check-input m-auto" type="checkbox" role="switch"
                                    x-bind:id="'factura_' + factura.id + '_seleccionado'"
                                    @click="toggleSeleccion(factura.id)">
                            </div>
                        </td>
                        <td class="text-center"><span x-html="factura.folio_interno"></span></td>
                        <td class="text-center"><span x-html="factura.fecha_certificacion"></span></td>
                        <td class="text-center"><span x-html="factura.cliente_receptor"></span></td>
                        <td class="text-center">
                            <span class="badge" x-bind:class="factura.es_nota_venta ? 'bg-warning' : 'bg-primary'"
                                x-html="factura.tipo"></span>
                        </td>
                        <td class="text-center"><span x-html="factura.moneda"></span></td>
                        <td class="text-center">$<span x-html="parseFloat(factura.total).toLocaleString()"></span>
                        </td>
                        <td class="text-center">
                            $<span x-html="parseFloat(factura.pendiente_ingresar).toLocaleString()"></span>
                        </td>
                        <td class="text-center">
                            <ul class="list-unstyled mb-0">
                                <li class="list-inline-item mb-1">
                                    <x-action icon="eye" title="Detalles"
                                        @click="$wire.emit('openModal', 'cuentas-cobrar.detalles-pago-factura', {factura: factura.id})" />
                                </li>
                                <li class="list-inline-item mb-1">
                                    <x-action icon="file-pdf" title="Mostrar PDF"
                                        @click="$wire.showPdf(factura.id)" />
                                </li>
                                <template x-if="factura.estado != 'COBRADA'">
                                    <li class="list-inline-item mb-1">
                                        <x-action icon="download" title="Ingresar"
                                            @click="$wire.emit('openModal', 'cuentas-cobrar.ingresar', {facturas_ids: factura.id})" />
                                    </li>
                                </template>
                            </ul>
                        </td>
                    </tr>
                </template>
                <template x-if="facturas.length == 0">
                    <tr>
                        <td colspan="10">
                            <div class="list-group-item">
                                No se encontraron resultados...
                            </div>
                        </td>
                    </tr>
                </template>
                <template x-if="facturas.length > 0">
                    <tr>
                        <td colspan="6" rowspan="2" class="text-end align-middle fw-bold"
                            style="background: aliceblue !important; --bs-table-bg-type: rgb(0,0,0,0)">
                            Totales:
                        </td>
                        <td class="fw-bold text-end"
                            style="background: aliceblue !important; --bs-table-bg-type: rgb(0,0,0,0)">
                            MXN:
                        </td>
                        <td class="text-center fw-bold"
                            style="background: aliceblue !important; --bs-table-bg-type: rgb(0,0,0,0)">
                            $<span x-html="totalMxn().toLocaleString()"></span>
                        </td>
                        <td class="text-center fw-bold"
                            style="background: aliceblue !important; --bs-table-bg-type: rgb(0,0,0,0)">
                            $<span x-html="pendienteMxn().toLocaleString()"></span>
                        </td>
                        <td style="background: aliceblue !important; --bs-table-bg-type: rgb(0,0,0,0)"></td>
                    </tr>
                </template>
                <template x-if="facturas.length > 0">
                    <tr>
                        <td class="fw-bold text-end"
                            style="background: aliceblue !important; --bs-table-bg-type: rgb(0,0,0,0)">
                            USD:
                        </td>
                        <td class="text-center fw-bold"
                            style="background: aliceblue !important; --bs-table-bg-type: rgb(0,0,0,0)">
                            $<span x-html="totalUsd().toLocaleString()"></span>
                        </td>
                        <td class="text-center fw-bold"
                            style="background: aliceblue !important; --bs-table-bg-type: rgb(0,0,0,0)">
                            $<span x-html="pendienteUsd().toLocaleString()"></span>
                        </td>
                        <td style="background: aliceblue !important; --bs-table-bg-type: rgb(0,0,0,0)"></td>
                    </tr>

                </template>
                <template x-if="facturasSeleccionadas().length > 0">
                    <tr>
                        <td colspan="6" rowspan="2" class="text-end align-middle fw-bold"
                            style="background: aliceblue !important; --bs-table-bg-type: rgb(0,0,0,0)">
                            Totales Seleccionados:
                        </td>
                        <td class="fw-bold text-end"
                            style="background: aliceblue !important; --bs-table-bg-type: rgb(0,0,0,0)">
                            MXN:
                        </td>
                        <td class="text-center fw-bold"
                            style="background: aliceblue !important; --bs-table-bg-type: rgb(0,0,0,0)">
                            $<span x-html="totalMxn(true).toLocaleString()"></span>
                        </td>
                        <td class="text-center fw-bold"
                            style="background: aliceblue !important; --bs-table-bg-type: rgb(0,0,0,0)">
                            $<span x-html="pendienteMxn(true).toLocaleString()"></span>
                        </td>
                        <td style="background: aliceblue !important; --bs-table-bg-type: rgb(0,0,0,0)"></td>
                    </tr>
                </template>
                <template x-if="facturasSeleccionadas().length > 0">
                    <tr>
                        <td class="fw-bold text-end"
                            style="background: aliceblue !important; --bs-table-bg-type: rgb(0,0,0,0)">
                            USD:
                        </td>
                        <td class="text-center fw-bold"
                            style="background: aliceblue !important; --bs-table-bg-type: rgb(0,0,0,0)">
                            $<span x-html="totalUsd(true).toLocaleString()"></span>
                        </td>
                        <td class="text-center fw-bold"
                            style="background: aliceblue !important; --bs-table-bg-type: rgb(0,0,0,0)">
                            $<span x-html="pendienteUsd(true).toLocaleString()"></span>
                        </td>
                        <td style="background: aliceblue !important; --bs-table-bg-type: rgb(0,0,0,0)"></td>
                    </tr>
                </template>
            </tbody>
        </table>

        <x-pagination-alpine :pagination="'pagination'" :onChange="'changePage'" />

    </div>

    <div class="modal {{ $iframeContainerClass }}">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cuentas por Cobrar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        wire:click="$set('iframeContainerClass', '')"></button>
                </div>
                <div class="modal-body pb-0 text-center">
                    <div class="row">
                        <iframe src="{{ $iframeSrc }}" frameborder="0" id="frame-death-file"
                            style="height: 80dvh"></iframe>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        wire:click="$set('iframeContainerClass', '')">{{ __('Cerrar') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
