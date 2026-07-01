@section('title', ($factura_id ? 'Editar ' : 'Crear ') . 'Pre-Factura')

<div class="container-fluid py-4 position-relative">

    <div wire:loading.delay.longer>
        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
            style="background: rgba(255,255,255,0.7); z-index: 1050; min-height: 400px;">
            <div class="text-center">
                <div class="spinner-border text-primary mb-2" role="status" style="width: 3rem; height: 3rem;"></div>
                <p class="text-muted fw-bold small">Procesando información...</p>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.pre-facturas.index') }}"
                            class="text-decoration-none">Pre-Facturas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $factura_id ? 'Edición' : 'Nueva' }}</li>
                </ol>
            </nav>
            <h1 class="h2 fw-bold text-dark mb-0">@yield('title')</h1>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <a href="{{ route('admin.pre-facturas.index') }}"
                class="btn btn-outline-secondary d-inline-flex align-items-center">
                <i class="bi bi-arrow-left me-1"></i> Atrás
            </a>
            <button wire:click="saveFactura()"
                class="btn btn-primary d-inline-flex align-items-center fw-bold shadow-sm">
                <i class="bi bi-download me-1"></i> Guardar
            </button>
            @if ($factura_id)
                <button wire:click="showPdf()" class="btn btn-outline-primary d-inline-flex align-items-center">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Ver PDF
                </button>
                <button wire:click="timbrarFactura({{ $factura_id }})"
                    class="btn btn-success d-inline-flex align-items-center fw-bold shadow-sm">
                    <i class="bi bi-lightning-fill me-1"></i> Timbrar
                </button>
            @endif
        </div>
    </div>

    <div wire:init="loadInitialData">
        <span style="display: none">{{ $this->form_with_errors }}</span>

        <div class="row g-4">

            <div class="col-xl-8">

                <div class="row g-2">
                    <div class="col-md-6 col-12">
                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body p-4">
                                <h5 class="card-title fw-bold text-dark mb-3 d-flex align-items-center">
                                    <i class="bi bi-person-badge text-primary me-2"></i> Selección de Cliente y Divisa
                                </h5>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <x-select2-ajax label="Cliente / Razón Social *" placeholder="Buscar cliente..."
                                            class="form-control" url="{{ route('clientes.load-clientes') }}"
                                            model="cliente_id" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold small text-muted">Moneda *</label>
                                        <select class="form-select @error('moneda') is-invalid @enderror"
                                            wire:model="moneda" id="id_moneda">
                                            <option value="MXN">MXN - Peso Mexicano</option>
                                            <option value="USD">USD - Dólar Americano</option>
                                        </select>
                                        @error('moneda')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold small text-muted">Tipo de Cambio</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i
                                                    class="bi bi-currency-exchange"></i></span>
                                            <input type="number" step="0.0001"
                                                class="form-control @error('tipo_cambio') is-invalid @enderror" disabled
                                                wire:model="tipo_cambio">
                                        </div>
                                        @error('tipo_cambio')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-12">
                        <div class="mt-3">
                            <label class="form-label fw-bold text-secondary text-uppercase tracking-wider small mb-2">
                                <i class="bi bi-arrow-repeat me-1"></i> Suscripciones Disponibles
                            </label>

                            <div class="card shadow-sm border rounded-3 overflow-hidden">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light text-uppercase tracking-wider small"
                                            style="font-size: 0.75rem;">
                                            <tr>
                                                <th class="text-center" style="width: 50px;"></th>
                                                <th style="width: 100px;">No.</th>
                                                <th>Consecutivo / Plan</th>
                                                <th class="text-center" style="width: 150px;">Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody style="font-size: 0.9rem;">

                                            @if (!$this->client_is_selected)
                                                <tr>
                                                    <td colspan="4" class="py-4">
                                                        <div class="text-center text-muted">
                                                            <i
                                                                class="bi bi-person-x fs-4 d-block mb-1 text-secondary"></i>
                                                            <span>Por favor, seleccione un Cliente para cargar sus
                                                                suscripciones.</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @else
                                                @if (count($suscripciones) > 0)
                                                    @foreach ($suscripciones as $index => $suscripcion)
                                                        <tr x-data="{
                                                            seleccionada: {{ $suscripcion['seleccionada'] ? 'true' : 'false' }},
                                                            id_suscripcion: '{{ $suscripcion['id'] }}',
                                                            checkSuscripcion(index) {
                                                                this.seleccionada = !this.seleccionada;
                                                                setTimeout(() => {
                                                                    @this.checkSuscripcion(index);
                                                                }, 100);
                                                            }
                                                        }" x-init="Livewire.on('unselect-suscripcion', id => {
                                                            if (id_suscripcion == id) {
                                                                seleccionada = false;
                                                            }
                                                        })"
                                                            :class="seleccionada ? 'table-primary-subtle' : ''"
                                                            style="transition: background-color 0.2s ease;">

                                                            <td class="text-center ps-3">
                                                                <div
                                                                    class="form-check d-flex justify-content-center align-items-center m-0">
                                                                    <input type="checkbox"
                                                                        class="form-check-input cursor-pointer"
                                                                        style="width: 1.15rem; height: 1.15rem;"
                                                                        x-bind:checked="seleccionada"
                                                                        @click="checkSuscripcion('{{ $index }}')">
                                                                </div>
                                                            </td>

                                                            <td class="fw-semibold text-secondary font-monospace">
                                                                #{{ $suscripcion['id'] }}
                                                            </td>

                                                            <td>
                                                                <div class="fw-bold text-dark">
                                                                    {{ $suscripcion['paquete'] }}</div>
                                                            </td>

                                                            <td class="text-center pe-3">
                                                                @switch(strtolower($suscripcion['estado']))
                                                                    @case('activa')
                                                                        <span
                                                                            class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-bold">Activa</span>
                                                                    @break

                                                                    @case('pendiente')
                                                                        <span
                                                                            class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 small fw-bold">Pendiente</span>
                                                                    @break

                                                                    @default
                                                                        <span
                                                                            class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 small fw-bold">{{ $suscripcion['estado'] }}</span>
                                                                @endswitch
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="4" class="py-4">
                                                            <div class="text-center text-muted">
                                                                <i
                                                                    class="bi bi-folder-x fs-4 d-block mb-1 text-secondary"></i>
                                                                <span>El cliente seleccionado no tiene Suscripciones a
                                                                    facturar.</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endif

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold text-dark mb-3 d-flex align-items-center">
                            <i class="bi bi-file-earmark-ruled text-primary me-2"></i> Datos Fiscales del Encabezado
                        </h5>

                        <div class="row p-3 bg-light rounded border mx-0 mb-4 g-2">
                            <div class="col-md-5">
                                <span class="d-block text-muted small fw-semibold text-uppercase"
                                    style="font-size:0.7rem;">Receptor Fiscal</span>
                                <span
                                    class="text-dark fw-bold">{{ $this->razon_social_receptor ?: 'No seleccionado' }}</span>
                            </div>
                            <div class="col-md-4">
                                <span class="d-block text-muted small fw-semibold text-uppercase"
                                    style="font-size:0.7rem;">RFC</span>
                                <code class="text-primary fw-bold fs-6">{{ $this->rfc_receptor ?: 'N/A' }}</code>
                            </div>
                            <div class="col-md-3">
                                <span class="d-block text-muted small fw-semibold text-uppercase"
                                    style="font-size:0.7rem;">C.P. Expedición</span>
                                <span class="text-dark fw-bold">{{ $lugar_expedicion ?: 'N/A' }}</span>
                                @error('lugar_expedicion')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <x-select2 label="Serie Fiscal" placeholder="Seleccione..." class="form-control"
                                    :options="$series" model="serie_id" />
                            </div>
                            <div class="col-md-4">
                                <label class="fw-semibold small text-muted">Fecha Emisión</label>
                                <input type="text" class="form-control" wire:model="fecha_emision_str" disabled>
                            </div>
                            <div class="col-md-4">
                                <x-select2 label="Uso de CFDI" placeholder="Seleccione..." class="form-control"
                                    :options="$usosCfdi" model="cfdi_id" />
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <x-select2 label="Forma de Pago" placeholder="Seleccione..." class="form-control"
                                    :options="$formasPago" model="forma_pago_id" />
                            </div>
                            <div class="col-md-4">
                                <x-select2 label="Método de Pago" placeholder="Seleccione..." class="form-control"
                                    :options="$metodosPago" model="metodo_pago_id" />
                            </div>
                            <div class="col-md-4">
                                <x-select2 label="Tipo de Comprobante" placeholder="Seleccione..."
                                    class="form-control" :options="$tiposComprobante" model="tipo_comprobante_id" />
                            </div>
                        </div>

                        @if ($this->client_is_publico_general)
                            <div class="row g-3 mt-2 p-3 bg-warning-subtle rounded border border-warning-subtle mx-0">
                                <div class="col-md-4 mt-0">
                                    <x-select2 label="Periodicidad Global" placeholder="Seleccione..."
                                        class="form-control" :options="$periodicidades" model="periodicidad_id" />
                                </div>
                                <div class="col-md-4 mt-0">
                                    <x-select2 label="Mes del Periodo" placeholder="Seleccione..."
                                        class="form-control" :options="$meses" model="mes_id" />
                                </div>
                                <div class="col-md-4 mt-0">
                                    <label class=form-label fw-semibold small text-muted">Año</label>
                                    <select class="form-select @error('anio') is-invalid @enderror" wire:model="anio">
                                        @foreach ($anios as $a)
                                            <option value="{{ $a }}">{{ $a }}</option>
                                        @endforeach
                                    </select>
                                    @error('anio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div
                        class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title fw-bold text-dark mb-0 d-flex align-items-center">
                            <i class="bi bi-list-stars text-primary me-2"></i> Conceptos de la Pre-Factura
                        </h5>
                        <button type="button"
                            class="btn btn-success btn-sm d-inline-flex align-items-center fw-semibold"
                            wire:click="showConceptoFacturaModal">
                            <i class="bi bi-plus-circle me-1"></i> Agregar Concepto
                        </button>
                    </div>
                    <div class="card-body p-0">
                        @error('factura_conceptos')
                            <div class="alert alert-danger rounded-0 border-0 m-0 py-2 small shadow-sm">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> Error general en conceptos:
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-nowrap">
                                <thead class="table-light text-uppercase text-muted"
                                    style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                    <tr>
                                        <th class="text-center px-4">Cant.</th>
                                        <th>Prod. / Serv. SAT</th>
                                        <th class="text-center">Clave Unidad</th>
                                        <th>Descripción</th>
                                        <th class="text-end">Valor Unitario</th>
                                        <th class="text-end px-4">Importe</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="border-top-0">
                                    @if ($factura_conceptos && count($factura_conceptos) > 0)
                                        @foreach ($factura_conceptos as $index => $concept)
                                            <tr>
                                                <td class="text-center fw-bold px-4 text-dark">
                                                    {{ $concept['cantidad'] }}
                                                    @error("factura_conceptos.{$index}.cantidad")
                                                        <div class="text-danger x-small d-block fw-normal">
                                                            {{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-light text-secondary border fw-mono fs-7">{{ $concept['clave_prod_serv'] ? $concept['clave_prod_serv']['codigo'] : '---' }}</span>
                                                    @error("factura_conceptos.{$index}.clave_prod_serv_id")
                                                        <div class="text-danger x-small d-block">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td class="text-center text-muted fw-semibold">
                                                    {{ $concept['clave_unidad'] ? $concept['clave_unidad']['codigo'] : '---' }}
                                                    @error("factura_conceptos.{$index}.clave_unidad_id")
                                                        <div class="text-danger x-small d-block">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <div class="text-wrap text-dark"
                                                        style="max-width: 260px; font-size: 0.85rem;">
                                                        {{ $concept['descripcion'] }}
                                                    </div>
                                                    <small class="text-muted d-block fs-7">Objeto Imp:
                                                        {{ $concept['objeto_impuesto'] ? Str::limit($concept['objeto_impuesto']['nombre'], 20) : 'No objeto' }}</small>
                                                </td>
                                                <td class="text-end font-monospace text-secondary">
                                                    ${{ number_format($concept['precio_unitario'], 2) }}</td>
                                                <td class="text-end font-monospace fw-bold text-dark px-4">
                                                    ${{ number_format($concept['precio_unitario'] * $concept['cantidad'], 2) }}
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-secondary"
                                                            wire:click="showConceptoFacturaModal({{ $index }})"
                                                            title="Modificar">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger"
                                                            wire:click="eliminarConceptoFactura({{ $index }})"
                                                            title="Eliminar">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                                No hay conceptos agregados a esta pre-factura.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="sticky-top" style="top: 24px; z-index: 10;">

                    <div class="card shadow-sm border-0 mb-4 bg-dark text-white">
                        <div class="card-header border-bottom border-secondary bg-transparent py-3">
                            <h6 class="mb-0 text-uppercase fw-bold text-muted-light"
                                style="font-size:0.75rem; letter-spacing:1px;">Resumen Económico</h6>
                        </div>
                        <div class="card-body p-4">
                            @if ($factura_conceptos && count($factura_conceptos) > 0)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted-light small">Subtotal</span>
                                    <span
                                        class="font-monospace fw-semibold">${{ number_format($this->subtotal, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted-light small">IVA Trasladado</span>
                                    <span class="font-monospace text-warning-subtle">+
                                        ${{ number_format($this->iva, 2) }}</span>
                                </div>
                                <hr class="border-secondary my-3">
                                <div class="d-flex justify-content-between align-items-center mb-0">
                                    <span class="fw-bold fs-5">Total Geral</span>
                                    <span
                                        class="font-monospace fw-bold fs-3 text-success">${{ number_format($this->total, 2) }}
                                        <small
                                            class="fs-6 text-muted-light font-sans fw-normal">{{ $moneda }}</small></span>
                                </div>
                            @else
                                <div class="text-center py-4 text-muted-light small">
                                    Calculando montos al añadir conceptos...
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="fw-semibold small text-muted">Importe con Letra</label>
                                <textarea class="form-control text-dark small bg-light" wire:model="cantidad_letras" rows="2"
                                    placeholder="Monto con letra automático o customizado..."></textarea>
                            </div>

                            <div class="mb-0">
                                <x-textarea label="Comentarios / Adenda Interna" model="comentarios"
                                    placeholder="Escriba anotaciones internas aquí..." rows="3" />
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="mb-2">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="chkAsociar"
                            wire:model.lazy="con_facturas_relacionadas">
                        <label class="form-check-label fw-semibold text-dark small" style="cursor:pointer;"
                            for="chkAsociar">Asociar CFDI / Facturas
                            Relacionadas</label>
                    </div>
                </div>
                @if ($con_facturas_relacionadas)
                    <div class="card shadow-sm border-0 mt-4 animate__animated animate__fadeIn">
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold text-dark mb-3 d-flex align-items-center">
                                <i class="bi bi-link-45deg text-primary me-1"></i> CFDI Vinculados y Relaciones SAT
                            </h5>
                            <div class="mb-4">
                                <x-select2 label="Tipo de Relación Fiscal (SAT) *"
                                    placeholder="Seleccione la clave de relación..." :options="$tipoRelacionesFacturas"
                                    model="tipo_relacion_factura_id" class="form-control" />
                            </div>

                            <h6 class="fw-bold text-secondary small text-uppercase mb-2"
                                style="letter-spacing:0.5px;">
                                Facturas Timbradas Disponibles</h6>
                            <div class="table-responsive border rounded">
                                <table class="table table-striped table-hover align-middle mb-0 text-nowrap">
                                    <thead class="table-light small text-uppercase text-muted">
                                        <tr>
                                            <th class="text-center" width="60">Sel.</th>
                                            <th>Fecha de Emisión</th>
                                            <th>Folio Interno</th>
                                            <th>Tipo CFDI</th>
                                            <th>Estado SAT</th>
                                            <th class="text-end">Subtotal</th>
                                            <th class="text-end px-4">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($facturasTimbradas) > 0)
                                            @foreach ($facturasTimbradas as $index => $factura)
                                                <tr x-data="{ seleccionada: {{ $factura['seleccionada'] ? 'true' : 'false' }} }">
                                                    <td class="text-center">
                                                        <input type="checkbox" class="form-check-input"
                                                            x-model="seleccionada"
                                                            @change="@this.checkFacturaTimbrada('{{ $index }}')">
                                                    </td>
                                                    <td>{{ $factura['fecha_emision_str'] }}</td>
                                                    <td class="fw-bold text-dark">{{ $factura['folio_interno'] }}</td>
                                                    <td>
                                                        @if ($factura['es_nota_credito'])
                                                            <span
                                                                class="badge bg-info-subtle text-info border border-info-subtle">EGRESO
                                                                (E)
                                                            </span>
                                                        @elseif($factura['es_complemento'])
                                                            <span
                                                                class="badge bg-primary-subtle text-primary border border-primary-subtle">PAGO
                                                                (P)</span>
                                                        @else
                                                            <span
                                                                class="badge bg-success-subtle text-success border border-success-subtle">INGRESO
                                                                (I)</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge {{ $factura['estado'] == 'TIMBRADA' ? 'bg-success' : 'bg-primary' }}">{{ $factura['estado'] }}</span>
                                                    </td>
                                                    <td class="text-end font-monospace">
                                                        ${{ number_format($factura['subtotal'], 2) }}</td>
                                                    <td class="text-end font-monospace fw-bold text-dark px-4">
                                                        ${{ number_format($factura['total'], 2) }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted small">No se
                                                    encontraron comprobantes emitidos para asociar.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @livewire('facturas-sistema.save-factura-concepto')

    <div class="modal fade {{ $iframeContainerClass ? 'show d-block bg-dark bg-opacity-50' : '' }}" tabindex="-1"
        role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark"><i
                            class="bi bi-file-pdf text-danger me-2"></i>Previsualización del Comprobante Fiscal</h5>
                    <button type="button" class="btn-close" wire:click="$set('iframeContainerClass', '')"></button>
                </div>
                <div class="modal-body p-0 bg-secondary">
                    @if ($iframeSrc)
                        <iframe src="{{ $iframeSrc }}" frameborder="0" class="w-100"
                            style="height: 75vh; display: block;"></iframe>
                    @else
                        <div class="text-center py-5 text-white">
                            <div class="spinner-border text-light mb-2" role="status"></div>
                            <p class="mb-0">Renderizando documento PDF...</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary px-4 fw-semibold"
                        wire:click="$set('iframeContainerClass', '')">Cerrar Visor</button>
                </div>
            </div>
        </div>
    </div>

    @livewire('facturas-sistema.consecutivo-factura')
</div>
