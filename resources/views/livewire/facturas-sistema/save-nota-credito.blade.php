@section('title', ($notaCredito->exists ? 'Editar ' : 'Crear ') . 'Nota de Crédito')

<div class="container-fluid py-4 position-relative">
    <div wire:loading.delay.longer>
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
            style="background: rgba(255,255,255,0.7); backdrop-filter: blur(4px); z-index: 1060;">
            <div class="text-center">
                <img src="{{ asset('img/loading.gif') }}" alt="Cargando..." class="mb-2" style="width: 50px;">
                <p class="text-muted fw-bold small">Procesando solicitud...</p>
            </div>
        </div>
    </div>

    <div
        class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.pre-facturas.index') }}"
                            class="text-decoration-none">Pre-Facturas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $notaCredito->id ? 'Edición' : 'Nuevo' }}
                    </li>
                </ol>
            </nav>
            <h1 class="h2 fw-bold text-dark mb-0">@yield('title')</h1>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="javascript:void(0)" href="{{ route('admin.pre-facturas.index') }}"
                class="btn btn-outline-secondary px-3">
                <i class="bi bi-arrow-left me-1"></i> Atrás
            </a>
            <button wire:click="saveNotaCredito()" class="btn btn-primary px-4 fw-bold">
                <i class="bi bi-download me-1"></i> Guardar
            </button>
            @if ($notaCredito->exists)
                <button wire:click="showPdf()" class="btn btn-outline-danger px-3">
                    <i class="bi bi-file-pdf me-1"></i> Ver PDF
                </button>
                <button wire:click="timbrarFactura({{ $notaCredito->id }})" class="btn btn-success px-4 fw-bold">
                    <i class="bi bi-lightning-charge-fill me-1"></i> Timbrar CFDI
                </button>
            @endif
        </div>
    </div>

    <div wire:init="loadInitialData" class="row g-4">

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-secondary text-uppercase small tracking-wide">
                        <i class="bi bi-person-check-fill text-primary me-2"></i>1. Selección de Cliente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <x-select2-ajax label="Cliente / Empresa" placeholder="Seleccione un cliente..."
                            class="form-control" url="{{ route('clientes.load-clientes') }}" model="cliente_id" />
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-4">
                            <x-select2-ajax label="Serie" placeholder="S/S" class="form-control"
                                url="{{ route('series.load-series') }}" model="serie_id" />
                        </div>
                        <div class="col-sm-4">
                            <label class="text-muted fw-bold small">Fecha Emisión</label>
                            <input type="text" class="form-control bg-light" wire:model="fecha_emision_str" disabled>
                        </div>
                        <div class="col-sm-4">
                            <label class="text-muted fw-bold small">Tipo de Relación</label>
                            <input type="text" class="form-control bg-light text-truncate"
                                value="01 | Nota de Crédito" title="01 | Nota de Crédito de los documentos relacionados"
                                disabled>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light py-3 border-0">
                    <h5 class="mb-0 fw-bold text-secondary text-uppercase small tracking-wide">
                        <i class="bi bi-credit-card-2-front-fill text-primary me-2"></i>2. Detalles del Pago & Impuestos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <x-select2-ajax label="Uso CFDI" placeholder="Seleccione..." class="form-control"
                                url="{{ route('cfdis.load-cfdis') }}" model="cfdi_id" />
                        </div>
                        <div class="col-sm-6">
                            <label class="text-muted fw-bold small">Tipo de Comprobante</label>
                            <input type="text" class="form-control bg-light" value="E | Egreso" disabled>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <x-select2-ajax label="Método de Pago" placeholder="Seleccione..." class="form-control"
                                url="{{ route('metodos-pagos.load-metodos-pagos') }}" model="metodo_pago_id" />
                        </div>
                        <div class="col-sm-6">
                            <x-select2-ajax label="Forma de Pago" placeholder="Seleccione..." class="form-control"
                                url="{{ route('formas-pagos.load-formas-pagos') }}" model="forma_pago_id" />
                        </div>
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    <div class="row g-3 align-items-end">
                        <div class="col-sm-4">
                            <label class="text-muted fw-bold small">Moneda</label>
                            <select class="form-select @error('moneda') is-invalid @enderror" wire:model.lazy="moneda"
                                id="id_moneda">
                                <option value="MXN">MXN (Peso Mexicano)</option>
                                <option value="USD">USD (Dólar Americano)</option>
                            </select>
                            @error('moneda')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <label class="text-muted fw-bold small">Tipo de Cambio</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">$</span>
                                <input type="number" step="0.0001"
                                    class="form-control @error('tipo_cambio') is-invalid @enderror" disabled
                                    wire:model="tipo_cambio">
                            </div>
                            @error('tipo_cambio')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-4">
                            <label class="text-primary fw-bold small">Importe del Pago</label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white border-primary">$</span>
                                <input type="text"
                                    class="form-control bg-white border-primary fw-bold text-primary"
                                    value="{{ number_format($this->total, 2) }}" disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light py-3 border-0">
                    <h5 class="mb-0 fw-bold text-secondary text-uppercase small tracking-wide">
                        <i class="bi bi-file-earmark-check-fill text-primary me-2"></i>3. Vincular Facturas Disponibles
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 290px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light position-sticky top-0 shadow-sm" style="z-index: 1;">
                                <tr>
                                    <th class="text-center py-3 text-muted small" style="width: 12%">Sel.</th>
                                    <th class="py-3 text-muted small">Folio Interno</th>
                                    <th class="py-3 text-muted small text-center">Moneda</th>
                                    <th class="py-3 text-muted small text-end pe-3">Fecha Certificación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (!$this->client_is_selected)
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-info-circle fs-4 d-block mb-2 text-warning"></i>
                                            Debe seleccionar un Cliente primero para buscar documentos.
                                        </td>
                                    </tr>
                                @else
                                    @if (count($facturasRelacionadasAll) > 0)
                                        @foreach ($facturasRelacionadasAll as $index => $factura)
                                            <tr x-data="{
                                                seleccionada: {{ $factura['seleccionada'] ? 'true' : 'false' }},
                                                id_factura: '{{ $factura['id'] }}',
                                                checkFactura(index, id, subtotal, moneda, tipo_cambio) {
                                                    this.seleccionada = !this.seleccionada;
                                                    setTimeout(() => {
                                                        @this.checkFactura(index, id, subtotal, moneda, tipo_cambio);
                                                    }, 100);
                                                }
                                            }" x-init="Livewire.on('unselect-factura', id => {
                                                if (id_factura == id) { seleccionada = false; }
                                            });"
                                                wire:key="factura-relacionadas-{{ $factura['id'] }}">
                                                <td class="text-center">
                                                    <input type="checkbox" class="form-check-input"
                                                        x-bind:checked="seleccionada == true"
                                                        @click="checkFactura('{{ $index }}', '{{ $factura['id'] }}', '{{ $factura['subtotal'] }}', '{{ $factura['moneda'] }}', '{{ $factura['tipo_cambio'] }}')">
                                                </td>
                                                <td><span
                                                        class="fw-bold text-dark">{{ $factura['folio_interno'] }}</span>
                                                </td>
                                                <td class="text-center"><span
                                                        class="badge bg-secondary-subtle text-secondary px-2 py-1">{{ $factura['moneda'] }}</span>
                                                </td>
                                                <td class="text-end pe-3 text-muted small">
                                                    {{ $factura['fecha_certificacion_str'] }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="bi bi-exclamation-triangle fs-4 d-block mb-2 text-muted"></i>
                                                El cliente seleccionado no cuenta con facturas vigentes.
                                            </td>
                                        </tr>
                                    @endif
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light py-3 border-0">
                    <h5 class="mb-0 fw-bold text-secondary text-uppercase small tracking-wide">
                        <i class="bi bi-list-stars text-primary me-2"></i>4. Conceptos de Facturación
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small">
                                    <th class="ps-3 py-2">Cant. / Claves</th>
                                    <th class="py-2" style="width: 45%;">Descripción</th>
                                    <th class="text-end py-2">Precio U.</th>
                                    <th class="text-end pe-3 py-2">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                @error('facturas_relacionadas')
                                    <tr>
                                        <td colspan="4" class="p-0">
                                            <div class="alert alert-danger m-2 py-2 small">{{ $message }}</div>
                                        </td>
                                    </tr>
                                @enderror
                                @if ($factura_conceptos && count($factura_conceptos) > 0)
                                    @foreach ($factura_conceptos as $index => $concepto)
                                        <tr class="border-bottom-0">
                                            <td class="ps-3 py-3">
                                                <span class="badge bg-primary px-2 mb-1">{{ $concepto['cantidad'] }}
                                                    Unidades</span>
                                                <div class="text-muted" style="font-size: 11px;">
                                                    <strong>SAT:</strong>
                                                    {{ $concepto['clave_prod_serv']['nombre'] }}<br>
                                                    <strong>UM:</strong> {{ $concepto['clave_unidad']['codigo'] }}
                                                </div>
                                            </td>
                                            <td>
                                                <x-textarea rows="2" class="form-control form-control-sm"
                                                    model="factura_conceptos.{{ $index }}.descripcion"
                                                    :lazy="true" />
                                            </td>
                                            <td class="text-end fw-semibold text-secondary">
                                                ${{ number_format($concepto['precio_unitario'], 2) }}</td>
                                            <td class="text-end pe-3 fw-bold text-dark">
                                                ${{ number_format($concepto['cantidad'] * $concepto['precio_unitario'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="bg-light-subtle">
                                        <td colspan="2" class="border-0"></td>
                                        <td class="text-end text-muted py-1 small">Subtotal:</td>
                                        <td class="text-end pe-3 text-secondary fw-semibold py-1">
                                            ${{ number_format($this->subtotal, 2) }}</td>
                                    </tr>
                                    <tr class="bg-light-subtle">
                                        <td colspan="2" class="border-0"></td>
                                        <td class="text-end text-muted py-1 small">IVA (16%):</td>
                                        <td class="text-end pe-3 text-secondary fw-semibold py-1">
                                            ${{ number_format($this->iva, 2) }}</td>
                                    </tr>
                                    <tr class="bg-light border-top">
                                        <td colspan="2" class="border-0"></td>
                                        <td class="text-end fw-bold text-dark py-2">Total M.N:</td>
                                        <td class="text-end pe-3 fw-black text-primary fs-5 py-2">
                                            ${{ number_format($this->total, 2) }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">
                                            Ningún concepto cargado. Vincule una factura para desglosar importes.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted fw-bold small">Importe total con letra</label>
                        <input type="text" class="form-control bg-light fw-medium text-secondary"
                            wire:model="cantidad_letras" disabled>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" id="asociar-facturas" class="form-check-input" role="switch"
                            wire:model="asociar_facturas">
                        <label for="asociar-facturas" class="form-check-label fw-bold text-dark small">Asociar
                            Relación CFDI de forma explícita</label>
                    </div>

                    @if ($asociar_facturas)
                        <div
                            class="table-responsive border rounded mb-3 bg-white p-2 animate__animated animate__fadeIn">
                            <label class="text-primary fw-bold small mb-2"><i class="bi bi-link-45deg"></i>
                                Facturas Timbradas Disponibles para Relacionar</label>
                            <table class="table table-striped table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr class="small text-muted">
                                        <th>Sel.</th>
                                        <th>Fecha Emisión</th>
                                        <th>Folio</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th class="text-end">Subtotal</th>
                                        <th class="text-end pe-2">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($facturasFiscalesAll as $index => $factura)
                                        <tr>
                                            <td><input type="checkbox" class="form-check-input"
                                                    wire:model="facturasFiscalesAll.{{ $index }}.seleccionada">
                                            </td>
                                            <td class="small">{{ $factura['fecha_emision_str'] }}</td>
                                            <td class="fw-bold">{{ $factura['folio_interno'] }}</td>
                                            <td><span
                                                    class="badge bg-light text-dark border">{{ $factura['tipo'] }}</span>
                                            </td>
                                            <td><span
                                                    class="badge bg-success-subtle text-success">{{ $factura['estado'] }}</span>
                                            </td>
                                            <td class="text-end">${{ number_format($factura['subtotal'], 2) }}</td>
                                            <td class="text-end pe-2 fw-semibold">
                                                ${{ number_format($factura['total'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div>
                        <x-textarea label="Comentarios u Observaciones internas" model="comentarios"
                            placeholder="Escriba comentarios que aparecerán en la representación impresa..."
                            rows="3" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade {{ $iframeContainerClass ? 'show d-block' : '' }}" tabindex="-1"
        style="background: {{ $iframeContainerClass ? 'rgba(0,0,0,0.5)' : 'none' }}">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold"><i
                            class="bi bi-file-earmark-pdf me-2 text-danger"></i>Previsualización de Comprobante</h5>
                    <button type="button" class="btn-close btn-close-white"
                        wire:click="$set('iframeContainerClass', '')"></button>
                </div>
                <div class="modal-body p-0 bg-secondary-subtle">
                    @if ($iframeContainerClass)
                        <iframe src="{{ $iframeSrc }}" frameborder="0" id="frame-death-file" class="w-100"
                            style="height: 75vh;"></iframe>
                    @endif
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4"
                        wire:click="$set('iframeContainerClass', '')">Cerrar Ventana</button>
                </div>
            </div>
        </div>
    </div>

    @livewire('facturas-sistema.consecutivo-factura')
</div>
